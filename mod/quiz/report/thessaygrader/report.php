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
 * This file defines the quiz thessaygrader report class.
 *
 * @package   quiz_thessaygrader
 * @copyright 2020 CBlue Srl
 * @copyright based on work by 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/thessaygrader_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/thessaygrader_options.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/thessaygrader_table.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/thessaygrader.php');
require_once($CFG->dirroot . '/local/th_exam_monitor/lib.php');

/**
 * Quiz report subclass for the thessaygrader report.
 *
 * @package   quiz_thessaygrader
 * @copyright 2020 CBlue Srl
 * @copyright based on work by 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_thessaygrader_report extends quiz_attempts_report
{

    /** @var object Store options for the quiz thessaygrader report (page mode, etc.) */
    private $options;

    public function display($quiz, $cm, $course)
    {
        global $OUTPUT, $USER, $COURSE, $DB;

        // This inits the quiz_attempts_report (parent class) functionality
        list($currentgroup, $students, $groupstudents, $allowed) =
            $this->init('thessaygrader', 'quiz_thessaygrader_settings_form', $quiz, $cm, $course);

        // This creates a new options object and ...
        $this->options = new quiz_thessaygrader_options('thessaygrader', $quiz, $cm, $course);
        // ... takes the information from the form object
        if ($fromform = $this->form->get_data()) {
            $this->options->process_settings_from_form($fromform);
        } else {
            $this->options->process_settings_from_params();
        }
        // write the information from options back to form (in case options changed due to params)
        $this->form->set_data($this->options->get_initial_form_data());

        $questions = quiz_report_get_significant_questions($quiz);

        $table = new quiz_thessaygrader_table($quiz, $this->context, $this->qmsubselect,
        $this->options, $groupstudents, $students, $questions, $this->options->get_url());

        // Downloading?
        // $table->is_downloading('csv', 'filename', 'Sheettitle');

        // Set layout e.g. for hiding navigation
        // Nothing but content
        // $PAGE->set_pagelayout('embedded');
        // Just breadcrump bar and title
        // $PAGE->set_pagelayout('print');

        // Process actions
        $this->process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $this->options->get_url());

        // Start output.

        // Print moodle headers (header, navigation, etc.) only if not downloading
        if (!$table->is_downloading()) {
            $this->print_header_and_tabs($cm, $course, $quiz, $this->mode);
        }

        // No idea what this operated
        if ($groupmode = groups_get_activity_groupmode($cm)) {
            // Groups are being used, so output the group selector
            groups_print_activity_menu($cm, $this->options->get_url());
        }

        $hasquestions = quiz_has_questions($quiz->id);
        if (!$hasquestions) {
            echo quiz_no_questions_message($quiz, $cm, $this->context);
        } else if (!$students) {
            echo $OUTPUT->notification(get_string('nostudentsyet'));
        } else if ($currentgroup && !$groupstudents) {
            echo $OUTPUT->notification(get_string('nostudentsingroup'));
        }

        $this->form->display();

        $hasstudents = $students && (!$currentgroup || $groupstudents);
        $excludedstatuses = [
            30
        ];
        
        if ($hasquestions && ($hasstudents || $this->options->attempts == self::ALL_WITH)) {
            list($fields, $from, $where, $params) = $table->base_sql($allowed);
            // Function documentation says we don't need to do this
            // $table->set_count_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
            list($in_sql, $in_params) = $DB->get_in_or_equal($excludedstatuses, SQL_PARAMS_NAMED, 'status');

            $where .= " AND u.id NOT IN (
                SELECT userid FROM {local_th_exam_monitor} exam
                JOIN {course} c ON c.id = exam.courseid 
                JOIN {quiz} q ON q.course = c.id
                WHERE q.id = :quizid_exclude AND action_status $in_sql
            )";
            $params['quizid_exclude'] = $params['quizid'] ?? $quiz->id;
            $params += $in_params;

            $table->set_sql($fields, $from, $where, $params);

            $columns = array();
            $headers = array();

            if (!$table->is_downloading() && $this->options->checkboxcolumn) {
                $columnname = 'checkbox';
                $columns[] = $columnname;
                $headers[] = $table->checkbox_col_header($columnname);
            }
            $role_id = get_config('quiz_thessaygrader', 'role_hidden');
            if (!empty($role_id)) {
            $context = context_course::instance($COURSE->id);
            $isgrader = user_has_role_assignment($USER->id, $role_id, $context->id);
            }
            if(!$isgrader){
                $this->add_user_columns($table, $columns, $headers);
            } else {
                $this->add_student_code($columns, $headers);
            }
            $this->add_state_column($columns, $headers);
            $this->add_time_columns($columns, $headers);

            $this->add_grade_columns($quiz, $this->options->usercanseegrades, $columns, $headers, false);
  
            $this->set_up_table_columns($table, $columns, $headers, $this->get_base_url(), $this->options, false);
            $table->set_attribute('class', 'generaltable generalbox grades');

            $table->out($this->options->pagesize, true);
        }
    }
    
    protected function has_regraded_questions($from, $where, $params) {
        global $DB;
        return $DB->record_exists_sql("
                SELECT 1
                  FROM {$from}
                  JOIN {quiz_overview_regrades} qor ON qor.questionusageid = quiza.uniqueid
                 WHERE {$where}", $params);
    }

    
    /**
     * Process any submitted actions.
     * @param object $quiz the quiz settings.
     * @param object $cm the cm object for the quiz.
     * @param int $currentgroup the currently selected group.
     * @param array $groupstudents the students in the current group.
     * @param array $allowed the users whose attempt this user is allowed to modify.
     * @param moodle_url $redirecturl where to redircet to after a successful action.
     */
    protected function process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $redirecturl)
    {
        // parent::process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $redirecturl);

        if (empty($currentgroup) || $groupstudents) {
            if (optional_param('thessaygrader', 0, PARAM_BOOL) && confirm_sesskey()) {
                raise_memory_limit(MEMORY_HUGE);
                set_time_limit(600);
                if ($attemptids = optional_param_array('attemptid', array(), PARAM_INT)) {
                    // require_capability('mod/quiz:deleteattempts', $this->context);
                    redirect($redirecturl);
                }
            }
        }
    }

    /**
     * Thessaygrader the quiz attempts
     * @param object $quiz the quiz settings.
     * @param object $cm the course_module object.
     * @param array $attemptids the list of attempt ids to thessaygrader.
     * @param array $allowed This list of userids that are visible in the report.
     *      Users can only thessaygrader attempts that they are allowed to see in the report.
     *      Empty means all users.
     */
    protected function add_student_code(&$columns, &$headers){
        $columns[] = 'studentcode';
        $headers[] = get_string('identity', 'quiz_thessaygrader');
    }
}
