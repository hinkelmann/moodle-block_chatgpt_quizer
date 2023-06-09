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

class block_chatgpt_quizer extends block_base
{

    function init()
    {
        $this->title = get_string('pluginname', 'block_chatgpt_quizer');
    }
    public function applicable_formats()
    {
        return [
            'course-view' => true,
        ];
    }
    function has_config()
    {
        return true;
    }
    function get_content()
    {
        global $USER, $COURSE,$PAGE;
        $this->content = new stdClass();
        if (!$PAGE->user_allowed_editing()) {
            $this->content->text =  "";
        }else{
            $mform = new block_chatgpt_quizer\form\formblock();

            $this->content->text =  $mform->getHTML();
        }

        return $this->content;
    }
}