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


defined('MOODLE_INTERNAL') || die();
if ($hassiteconfig) {
    $ADMIN->add('blockplugins', new admin_category('block_chatgpt_quizer_settings', new lang_string('pluginname', 'block_chatgpt_quizer')));
    $settings = new admin_settingpage('managelocalvenom', new lang_string('pluginname', 'block_chatgpt_quizer'));
    if ($ADMIN->fulltree) {

        // Input API Key
        $settings->add(new admin_setting_configtext('block_chatgpt_quizer/api_key',
            get_string('apikey', 'block_chatgpt_quizer'),
            get_string('apikey_desc', 'block_chatgpt_quizer'), null));

        $settings->add(new admin_setting_configtext('block_chatgpt_quizer/api_timeout',
            get_string('apitimeout', 'block_chatgpt_quizer'),
            get_string('apitimeout_desc', 'block_chatgpt_quizer'), 0));

        $settings->add(
            new admin_setting_configselect(
                'block_chatgpt_quizer/api_model',
                new lang_string('apimodel', 'block_chatgpt_quizer'),
                new lang_string('apimodel_desc', 'block_chatgpt_quizer'),
                'gpt-3.5-turbo', [
                    'gpt-3.5-turbo' => 'gpt-3.5-turbo',
                ]
            ));
    }
}
