<?php

require_once '../../config.php';
require_once $CFG->dirroot.'/local/th_question_bank_report/th_question_bank_report_form.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION, $DB;
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($COURSE->id);
require_capability('local/th_question_bank_report:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_question_bank_report/view.php'));
$editurl = new moodle_url('/local/th_question_bank_report/view.php');
$PAGE->settingsnav->add(get_string('title', 'local_th_question_bank_report'), $editurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('title', 'local_th_question_bank_report'));
$PAGE->set_title(get_string('title', 'local_th_question_bank_report'));

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('title', 'local_th_question_bank_report') . '</center>');

$config = get_config('local_th_question_bank_report');

$mform = new th_question_bank_report_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_question_bank_report/view.php'));
} else if ($formdata = $mform->get_data()) {
    $mform->display();

    $selectedcourses = $formdata->selectedcourses;
    $startdate = $formdata->startdate;
    $enddate = $formdata->enddate;

    $conditions = array();
    $params = array();

    if (empty($selectedcourses) && empty($startdate) && empty($enddate)) {
        $coursecategories = isset($config->coursecategories) ? explode(',', $config->coursecategories) : [];
        if (!empty($coursecategories)) {
            list($insql, $inparams) = $DB->get_in_or_equal($coursecategories, SQL_PARAMS_NAMED, 'catid');
            $conditions[] = "c.category $insql";
            $params = array_merge($params, $inparams);
        }
    } else {
        if (!empty($selectedcourses)) {
            list($insql, $inparams) = $DB->get_in_or_equal($selectedcourses, SQL_PARAMS_NAMED, 'courseid');
            $conditions[] = "c.id $insql";
            $params = array_merge($params, $inparams);
        }

        if (!empty($startdate)) {
            $conditions[] = "q.timecreated >= :startdate";
            $params['startdate'] = $startdate;
        }
        if (!empty($enddate)) {
            $conditions[] = "q.timecreated <= :enddate";
            $params['enddate'] = $enddate;
        }
    }

    $sqlcondition = empty($conditions) ? '1=1' : implode(" AND ", $conditions);

    $config = get_config('local_th_question_bank_report');
    $sql = $config->queries;
    $sql = str_replace('{sqlcondition}', $sqlcondition, $sql);

    if(!$sql){
        $notify = get_string('notify','local_th_question_bank_report');
        echo "<p style='text-align: center;'>{$notify}</p>";
        echo $OUTPUT->footer();
        exit;
    }

    $results = $DB->get_recordset_sql($sql, $params);
    
    if ($results->valid()) {
        $table = new html_table();
        $table->head = array(
            get_string('stt', 'local_th_question_bank_report'),
            get_string('course_name', 'local_th_question_bank_report'),
            get_string('shortname', 'local_th_question_bank_report'),
            get_string('author_editor', 'local_th_question_bank_report'),
            get_string('email', 'local_th_question_bank_report'),
            get_string('questions_created', 'local_th_question_bank_report'),
            get_string('questions_edited', 'local_th_question_bank_report'),
            get_string('total_questions', 'local_th_question_bank_report')
        );
        $table->attributes['class'] = 'question_report';
        $table->attributes['style'] = 'width: 100%; text-align: center;';
        $table->attributes['border'] = 1;

        $stt = 1;
        foreach ($results as $result) {
            $course_link = new moodle_url('/question/edit.php', array('courseid' => $result->courseid));
            $user_link = new moodle_url('/user/view.php', array('id' => $result->userid));
            $row = array(
                $stt++,
                html_writer::link($course_link, $result->coursename),
                $result->courseshortname,
                html_writer::link($user_link, $result->fullname),
                $result->email,
                $result->questionscreated,
                $result->questionsedited,
                $result->totalquestions
            );
            $table->data[] = $row;
        }

        echo html_writer::table($table);
        $results->close();
    } else {
        $notify = get_string('notify','local_th_question_bank_report');
        echo "<p style='text-align: center;'>{$notify}</p>";
    }
} else {
    $mform->display();
}

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.question_report', 'Report', $lang));
echo $OUTPUT->footer();

?>
