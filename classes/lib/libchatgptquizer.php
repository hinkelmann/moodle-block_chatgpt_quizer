<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   block_chatgpt_quizer
 * @copyright 2023 Luiz Guilherme Dall' Acqua <luizguilherme@nte.ufsm.br>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_chatgpt_quizer\lib;

use context_module;
use enrol_self\self_test;
use moodle_exception;
use mod_quiz\quiz_settings;
use block_chatgpt_quizer\api\openai;

class libchatgptquizer
{

    /**
     * Simulate import form
     *
     * @param $cmid
     * @return array
     * @throws \coding_exception
     * @throws \require_login_exception
     * @throws moodle_exception
     */
    public static function setup_import_question_form($cmid)
    {
        global $CFG;
        require_once($CFG->dirroot . '/question/editlib.php');
        \core_question\local\bank\helper::require_plugin_enabled('qbank_importquestions');
        list($module, $cm) = get_module_from_cmid($cmid);
        require_login($cm->course, false, $cm);
        $contexts = new \core_question\local\bank\question_edit_contexts(context_module::instance($cmid));
        $category = question_make_default_categories($contexts->all());
        return [$category, $contexts, $module, $cm];
    }

    /**
     * Create temporary file with content generate via openai
     *
     * @param null $content
     * @param int $cmid
     * @return string
     */
    public static function create_tmpfile($content = null, $cmid = 0)
    {
        global $CFG;
        $f = $CFG->localrequestdir . "/$cmid.txt";
        if (file_exists($f)) {
            unlink($f);
        }
        file_put_contents($f, $content);
        return $f;
    }

    /**
     * Create new quiz in same section of page.
     *
     * @param $page
     * @return \stdClass
     */
    public function create_quiz($page)
    {
        return new \stdClass();
    }

    /**
     * Return text plain from page module
     *
     * @param $id
     * @return string
     * @throws \dml_exception
     */
    public static function get_resource_text($id)
    {
        global $DB;
        $content = "";
        $page = $DB->get_record('page', ['id' => $id]);
        if ($page) {
            $content = strip_tags(html_entity_decode($page->content));
        }
        return $content;
    }

    /**
     *  Generante questions and add in quiz module
     *
     * @param $cmid
     * @param $page
     * @return mixed
     * @throws \coding_exception
     * @throws \require_login_exception
     * @throws moodle_exception
     */
    public static function generate($cmid, $page)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/question/format.php');
        require_once($CFG->dirroot . '/question/format/aiken/format.php');
        list($category, $contexts, $module, $cm) = self::setup_import_question_form($cmid);
        $txt = libchatgptquizer::request(libchatgptquizer::get_resource_text($page));
        $file = libchatgptquizer::create_tmpfile($txt, $cmid);
        $qformat = new \qformat_aiken();
        $qformat->setCategory($category);
        $qformat->setContexts($contexts->having_one_edit_tab_cap('import'));
        $qformat->setCourse($cm->course);
        $qformat->setFilename($file);
        $qformat->setRealfilename($cmid . ".txt");
        $qformat->setMatchgrades("error");
        $qformat->setCatfromfile(false);
        $qformat->setContextfromfile(1);
        $qformat->setStoponerror(1);

        $url = new \moodle_url('/blocks/chatgpt_quizer/view.php', ['cmid' => $cmid]);
        // Do anything before that we need to.
        if (!$qformat->importpreprocess()) {
            throw new moodle_exception('cannotimport', '', $url->out());
        }

        // Process the uploaded file.
        if (!$qformat->importprocess()) {
            throw new moodle_exception('cannotimport', '', $url->out());
        }

        // In case anything needs to be done after.
        if (!$qformat->importpostprocess()) {
            throw new moodle_exception('cannotimport', '', $url->out());
        }
        $eventparams = [
            'contextid' => $qformat->category->contextid,
            'other' => ['format' => 'aiken', 'categoryid' => $qformat->category->id],
        ];
        $event = \core\event\questions_imported::create($eventparams);
        $event->trigger();

        self::add_question($module, $category->id);

        return $cm->id;
    }


    /**
     *
     * Add questions in quiz
     *
     * @param $quiz
     * @param $cat
     * @return null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function add_question($quiz, $cat)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $quizobj = new quiz_settings($quiz, $quiz->cm, $quiz->course);
        $questions = self::get_questions_cat($cat);
        foreach ($questions as $question) {
            quiz_require_question_use($question->id);
            quiz_add_quiz_question($question->id, $quiz, 0);
        }
        quiz_delete_previews($quiz);
        $quizobj->get_grade_calculator()->recompute_quiz_sumgrades();
        return null;
    }

    /**
     * @param $cat
     * @return array
     * @throws \dml_exception
     */
    public static function get_questions_cat($cat)
    {
        global $DB;
        $sql = "SELECT q.id
     ,qv.status
     , qc.id as categoryid
     , qv.version
     , qv.id as versionid
     , qbe.id as questionbankentryid
     , q.qtype
     , q.createdby
     , qc.contextid
     , q.name
     , q.questiontext
     , q.questiontextformat
     , qbe.idnumber 
FROM {question} q 
    JOIN {question_versions} qv ON qv.questionid = q.id 
    JOIN {question_bank_entries} qbe on qbe.id = qv.questionbankentryid 
    JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid 
WHERE q.parent = 0 AND qv.version = 
                       (SELECT MAX(v.version) 
                       FROM {question_versions} v 
                           JOIN {question_bank_entries} be ON be.id = v.questionbankentryid 
                       WHERE be.id = qbe.id) 
  AND qv.status = 'ready'  
  AND ((qbe.questioncategoryid = :cat)) 
  AND ((qv.status = 'ready'  OR qv.status = 'draft' )) 
ORDER BY q.qtype ASC, q.name ASC";
        return $DB->get_records_sql($sql, ['cat' => $cat]);
    }


    /**
     * Generate request openai with contet and return questions in AIKEN format
     *
     * @param $content
     * @throws \dml_exception
     */
    public static function request($content)
    {
        $tmp = [];
        if (strlen($content) > 2000) {
            $tmp[0] = substr($content, 0, 2000);
        } else {
            $tmp[0] = $content;
        }

        $config = get_config("block_chatgpt_quizer");
        $openai = new openai($config->api_key, $config->api_model, $config->api_timeout);

        $instrucao = [
            "Crie 5 perguntas sobre o texto."
            ,"Todas as perguntas devem apresentar 5 opções de escolha."
            ,"As perguntas devem ser geradas no formato AIKEN e deve conter a letra da alternativa correta na instrução ANSWER"
            ,'As perguntas são enumeradas com espaço em branco'
            ,'As alternativas são enumeradas em letras maiusculas.'
            ];

        $msg = [
            'model' => $config->api_model,
            'messages' => [
                [
                    "role" => "system",
                    "content" => "You are a helpful assistant."
                ],
                [
                    "role" => "user",
                    "content" => $tmp[0],
                ],
            ],
            'temperature' => 1.0,
            'max_tokens' => 2000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ];
        foreach ($instrucao as $item){
            $msg['messages'][] = [
                "role" => "assistant",
                "content" => $item
            ];
        }
        $request = json_decode($openai->request($msg));
        $question = "";
        if($request->choices and count($request->choices) ){
            foreach ($request->choices as $item){
                $question .= $item->message->content;
            }
        }else{
            exit("ERRO:".$request);
        }
        if($question ==""){
            exit("ERRO: REPOSTA VAZIA".$request);
        }
        return $question;
    }
}