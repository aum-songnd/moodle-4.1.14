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
 * Strings for component 'quiz_thessaygrader', language 'en'
 *
 * @package   quiz_thessaygrader
 * @copyright 2020 CBlue Srl
 * @copyright based on work by 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'TH Chấm điểm thi online';
$string['thessaygrader'] = 'Chấm điểm thi online'; // displayed in the navigation menu
$string['thessaygraderreport'] = 'TH Chấm điểm thi online';

$string['thessaygraderselected'] = 'Download files attachment';

$string['thessaygraderattemptcheck'] = 'Download selected attempts?';

$string['thessaygraderattempt'] = 'Thessaygrader attempt';

// thessaygrader_form
$string['thessaygradersettings'] = 'Essaygrader Settings';
$string['pagemode'] = 'Page mode (Page break mode while rendering quiz review before converting to PDF)';
$string['thessaygradermodetruepage'] = 'Actual question page assignment';
$string['thessaygradermodequestionperpage'] = 'One question per page';
$string['thessaygradermodesinglepage'] = 'All questions on one page';

// Inside the pdf
$string['documenttitle'] = '{$a->coursename} <br> {$a->quizname} <br> - <br> Summary of {$a->firstname} {$a->lastname}\'s attempt';

$string['role_hidden'] = 'Select user role';
$string['role_hidden_desc'] = 'Select the user role with hidden information';

$string['thessaygrader:view'] = 'Allows scoring';
$string['identity'] = 'ID';

$string['customfield_shortname'] = 'Short name of the custom field student code';
$string['customfield_description'] = 'Separated by commas';
$string['attempt_secret_key'] = 'Attempt secret key';
$string['attempt_secret_key_description'] = 'The secret key used to encode or decode attempt identifiers securely.';
$string['attempt_secret_iv'] = 'Attempt secret IV';
$string['attempt_secret_iv_description'] = 'The secret initialization vector (IV) used for encrypting attempt identifiers.';
