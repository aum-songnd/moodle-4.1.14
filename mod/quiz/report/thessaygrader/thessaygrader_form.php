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
 * This file defines the setting form for the quiz thessaygrader report.
 *
 * @package   quiz_thessaygrader
 * @copyright 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_form.php');


/**
 * Quiz thessaygrader report settings form.
 *
 * @package   quiz_thessaygrader
 * @copyright 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_thessaygrader_settings_form extends mod_quiz_attempts_report_form
{

    protected function other_preference_fields(MoodleQuickForm $mform)
    {
        $mform->addElement('header', 'thessaygradersettings',
            get_string('thessaygradersettings', 'quiz_thessaygrader'));

        $mform->addElement('select', 'pagemode', get_string('pagemode', 'quiz_thessaygrader'), array(
            quiz_thessaygrader_engine::PAGEMODE_TRUEPAGE => get_string('thessaygradermodetruepage', 'quiz_thessaygrader'),
            quiz_thessaygrader_engine::PAGEMODE_QUESTIONPERPAGE => get_string('thessaygradermodequestionperpage', 'quiz_thessaygrader'),
            quiz_thessaygrader_engine::PAGEMODE_SINGLEPAGE => get_string('thessaygradermodesinglepage', 'quiz_thessaygrader'),
        ));
    }
}
