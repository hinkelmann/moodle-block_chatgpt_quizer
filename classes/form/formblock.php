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

namespace block_chatgpt_quizer\form;

use moodle_exception;
use coding_exception;

require_once($CFG->libdir.'/formslib.php');

class formblock extends \moodleform {

    public function __construct()
    {
        parent::__construct(new \moodle_url("/blocks/chatgpt_quizer/view.php"));
    }

    /**
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function definition() {
        global $PAGE, $COURSE;
        $source = new \stdClass();
        $source->page = [];
        $source->quiz = [0=>get_string('quiz:addinstance', 'quiz')];
        foreach( get_fast_modinfo($COURSE)->cms as $item){
            if($item->modname=='page' or $item->modname=='quiz'){
                $source->{$item->modname}[$item->modname=='page'?$item->instance:$item->id] = $item->name;
            }
        }
        $mform = $this->_form;
        $mform->addElement('select', 'page',get_string('page'), $source->page,[ 'width' => '100%']);
        $mform->addElement('select', 'cmid',get_string('pluginname', 'quiz'), $source->quiz,[ 'width' => '100%']);
        $mform->addElement('hidden','courseid',$COURSE->id);
        $mform->addElement('hidden','edit',$PAGE->user_allowed_editing());
        $this->add_action_buttons(false, get_string('confirm'));
    }

    function validation($data, $files) {
        return array();
    }
    /**
     * @return string
     */
    public function getHTML(){
        return $this->_form->toHtml();
    }
}