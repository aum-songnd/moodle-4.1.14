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
 * This file defines the quiz grades table.
 *
 * @package   quiz_thessaygrader
 * @copyright 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_table.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/lib.php');

/**
 * This is a table subclass for displaying the quiz thessaygrader report.
 *
 * @package   quiz_thessaygrader
 * @copyright 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_thessaygrader_table extends quiz_attempts_report_table
{

    public function __construct($quiz, $context, $qmsubselect,
                                quiz_thessaygrader_options $options, $groupstudents, $students, $questions, $reporturl)
    {
        parent::__construct('mod-quiz-report-thessaygrader-report', $quiz, $context,
            $qmsubselect, $options, $groupstudents, $students, $questions, $reporturl);
    }

    public function build_table()
    {
        // Strange: parent class quiz_attempts_report_table uses this property but doesn't define it
        // So we have to do it here... just for quiz_attempts_report::add_time_columns
        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        parent::build_table();
    }

    /**
     * Generate the display of the user's full name column.
     * Adds an thessaygrader Link
     *
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_fullname($attempt)
    {
        $html = parent::col_fullname($attempt);
        if ($this->is_downloading() || empty($attempt->attempt)) {
            return $html;
        }
        return $html;
    }

    protected function submit_buttons()
    {
        global $PAGE;
        $PAGE->requires->event_handler('#thessaygraderattemptsbutton', 'click', 'M.util.show_confirm_dialog',
            array('message' => get_string('thessaygraderattemptcheck', 'quiz_thessaygrader')));
    }



        public function col_sumgrades($attempt) {
        global $USER, $COURSE;
        if ($attempt->state != quiz_attempt::FINISHED) {
            return '-';
        }

        $grade = quiz_rescale_grade($attempt->sumgrades, $this->quiz);
        if ($this->is_downloading()) {
            return $grade;
        }

        if (isset($this->regradedqs[$attempt->usageid])) {
            $newsumgrade = 0;
            $oldsumgrade = 0;
            foreach ($this->questions as $question) {
                if (isset($this->regradedqs[$attempt->usageid][$question->slot])) {
                    $newsumgrade += $this->regradedqs[$attempt->usageid]
                            [$question->slot]->newfraction * $question->maxmark;
                    $oldsumgrade += $this->regradedqs[$attempt->usageid]
                            [$question->slot]->oldfraction * $question->maxmark;
                } else {
                    $newsumgrade += $this->lateststeps[$attempt->usageid]
                            [$question->slot]->fraction * $question->maxmark;
                    $oldsumgrade += $this->lateststeps[$attempt->usageid]
                            [$question->slot]->fraction * $question->maxmark;
                }
            }
            $newsumgrade = quiz_rescale_grade($newsumgrade, $this->quiz);
            $oldsumgrade = quiz_rescale_grade($oldsumgrade, $this->quiz);
            $grade = html_writer::tag('del', $oldsumgrade) . '/' .
                    html_writer::empty_tag('br') . $newsumgrade;
        }
        $role_id = get_config('quiz_thessaygrader', 'role_hidden');
        if (!empty($role_id)) {
        $context = context_course::instance($COURSE->id);
        $isgrader = user_has_role_assignment($USER->id, $role_id, $context->id);
        }
        if($isgrader){
            $token = encrypt_attempt_id($attempt->attempt);
            return html_writer::link(new moodle_url("/mod/quiz/report/thessaygrader/review.php",
                array('attempt_token' => $token, 'action' => 'grade')), $grade,
                array('title' => get_string('reviewattempt', 'quiz')));
            // return html_writer::link(new moodle_url("/mod/quiz/report/thessaygrader/review.php",
            //     array('attempt' => $attempt->attempt)), $grade,
            //     array('title' => get_string('reviewattempt', 'quiz')));
        } else {
            return html_writer::link(new moodle_url("/mod/quiz/review.php",
                array('attempt' => $attempt->attempt)), $grade,
                array('title' => get_string('reviewattempt', 'quiz')));
        }
    }

    public static function col_studentcode($attempt){
        return $attempt->attempt;
    }
}
