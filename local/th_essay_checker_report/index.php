<?php

require_once '../../config.php';
require_once('form.php');

global $DB, $CFG, $COURSE, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_essay_checker_report', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_essay_checker_report:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_essay_checker_report/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_th_essay_checker_report'));
$PAGE->set_heading(get_string('pluginname', 'local_th_essay_checker_report'));
$PAGE->navbar->add(get_string('pluginname', 'local_th_essay_checker_report'), new moodle_url('/local/th_essay_checker_report/index.php'));
// Display form.
$form = new th_essay_checker_report_form();

if ($form->is_submitted() && $data = $form->get_data()) {
    global $DB, $OUTPUT;

    $params = [];

    // Get start date and end date.
    if (!empty($data->startdate)) {
        $params['startdate'] = $data->startdate;
    }

    if (!empty($data->enddate)) {
        $params['enddate'] = $data->enddate + 86399; // Include the entire end day.
    }

    // Get selected course IDs.
    $courseids = !empty($data->courseids) ? $data->courseids : [];

    $course_condition = '';
    $time_condition = '';

    if ($data->filter_type == 1) {
        // Build course filtering.
        if (!empty($courseids)) {
            list($course_sql, $course_params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $course_condition = "AND c.id $course_sql";
            $params = array_merge($params, $course_params);
        }
    } else if($data->filter_type == 2) {
        // Build time filtering.
        if (!empty($data->startdate)) {
            $time_condition .= " AND c.startdate >= :startdate";
        }
        if (!empty($data->enddate)) {
            $time_condition .= " AND c.startdate <= :enddate";
        }
    }

    // SQL query with filters.
    $sql = "
        SELECT DISTINCT
            qat.id, qat.responsesummary, q.name AS quizname, c.fullname AS coursename, c.shortname,
            CONCAT(u_grader.firstname, ' ', u_grader.lastname) AS grader_name,
            CONCAT(u_attempt.firstname, ' ', u_attempt.lastname) AS attempt_name,
            qas.fraction, ques.name AS questionname, qa.state AS attempt_state, qa.id as attemptid, qat.questionusageid, qat.slot
        FROM {question_attempts} AS qat
        JOIN {question_usages} AS qu ON qu.id = qat.questionusageid
        JOIN {quiz_attempts} AS qa ON qa.uniqueid = qu.id
        JOIN {context} AS ctx ON ctx.id = qu.contextid
        JOIN {course_modules} AS cm ON cm.id = ctx.instanceid
        JOIN {course} AS c ON c.id = cm.course
        JOIN {quiz} AS q ON q.id = cm.instance
        JOIN (
            SELECT MAX(id) AS max_id, questionattemptid
            FROM {question_attempt_steps}
            GROUP BY questionattemptid
        ) AS max_qas ON max_qas.questionattemptid = qat.id
        JOIN {question_attempt_steps} AS qas ON qas.id = max_qas.max_id
        JOIN {question_attempt_step_data} AS qad ON qad.attemptstepid = qas.id
        JOIN {user} AS u_grader ON u_grader.id = qas.userid
        JOIN {user} AS u_attempt ON u_attempt.id = qa.userid
        JOIN {question} AS ques ON ques.id = qat.questionid
        WHERE ques.qtype = 'essay'
        AND (qat.responsesummary IS NULL OR qat.responsesummary = '' OR qat.responsesummary LIKE 'Any text entered here will be displayed in the response input box when a new attempt at the question starts.%')
        AND qas.fraction IS NOT NULL
        AND qas.fraction > 0
        AND qat.behaviour = 'manualgraded'
        AND qa.state = 'finished'
        $course_condition
        $time_condition ORDER BY qat.questionusageid, qat.slot";

    $results = $DB->get_records_sql($sql, $params);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_th_essay_checker_report'));
    echo '</br>';
    $form->display();
    echo $OUTPUT->heading(get_string('results', 'local_th_essay_checker_report'));
    if ($results) {
        $table = new html_table();
        // Định nghĩa tiêu đề của bảng
        $table->head = [
            get_string('stt', 'local_th_essay_checker_report'),
            get_string('attemptname', 'local_th_essay_checker_report'),
            get_string('shortname', 'local_th_essay_checker_report'),
            get_string('coursename', 'local_th_essay_checker_report'),
            get_string('quizname', 'local_th_essay_checker_report'),
            get_string('grade', 'local_th_essay_checker_report'),
            get_string('gradername', 'local_th_essay_checker_report')
        ];

        $stt = 0;

        // Thêm các hàng dữ liệu vào bảng
        foreach ($results as $result) {
            if (trim($result->responsesummary) == null || iconv('UTF-8', 'ASCII//TRANSLIT', trim($result->responsesummary)) == 'Any text entered here will be displayed in the response input box when a new attempt at the question starts.') {
                $stt++;

                // Chuyển đổi sang thang điểm 10
                $score = $result->fraction * 10;

                // Định dạng kết quả nếu cần (ví dụ: làm tròn đến 2 chữ số thập phân)
                $score = round($score, 2);

                // Tạo một liên kết đến trang review
                $gradeLink = html_writer::link(
                    new moodle_url('/mod/quiz/review.php', ['attempt' => $result->attemptid]) . '#question-' . $result->questionusageid . '-' . $result->slot,
                    $score
                );

                // Thêm dữ liệu của hàng vào bảng
                $table->data[] = [
                    $stt,
                    $result->attempt_name,
                    $result->shortname,
                    $result->coursename,
                    $result->quizname,
                    $gradeLink,
                    $result->grader_name
                ];
            }
        }

        $table->attributes = array('class' => 'th_essay_checker_report', 'border' => '1');
        $table->attributes['style'] = "width: 100%; text-align:left;";
        // Hiển thị bảng
        echo html_writer::table($table);

    } else {
        echo html_writer::tag('p', get_string('noresults', 'local_th_essay_checker_report'));
    }

    $lang = current_language();
    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_essay_checker_report', get_string('pluginname', 'local_th_essay_checker_report'), $lang));

    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_th_essay_checker_report'));
    echo '</br>';
    $form->display();
    echo $OUTPUT->footer();
}