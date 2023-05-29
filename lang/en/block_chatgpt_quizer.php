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

$string['pluginname'] = 'ChatGPT quizer';
$string['apikey'] = 'API KEY';
$string['apikey_desc'] = 'The OpenAI API uses API keys for authentication. Visit your <a href="https://platform.openai.com/account/api-keys">API Keys</a> page to retrieve the API key you\'ll use in your requests.';
$string['apitimeout'] = 'Timeout';
$string['apitimeout_desc'] = 'Request timeout';
$string['apimodel'] = 'Model';
$string['apimodel_desc'] = 'The OpenAI API is powered by a diverse set of models with different capabilities and price points. You can also make limited customizations to our original base models for your specific use case with <a href="https://platform.openai.com/docs/guides/fine-tuning">fine-tuning</a>.';