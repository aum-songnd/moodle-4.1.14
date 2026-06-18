<?php

require_once '../../config.php';
require_once $CFG->dirroot.'/local/th_unanswered_questions/answer_form.php';
global $PAGE, $COURSE, $OUTPUT, $DB;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_unanswered_questions', $COURSE->id);
}
require_login($COURSE->id);

$questionid = optional_param('id', 0, PARAM_INT);
$qaaid = optional_param('qaaid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/th_unanswered_questions/answer.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$heading = get_string('answer', 'local_th_unanswered_questions');
$PAGE->set_heading($heading);
$PAGE->set_title($heading);
$viewurl = new moodle_url("/local/th_unanswered_questions/answer.php", array('userid' => $userid, 'id' => $questionid ,'qaaid' => $qaaid, 'courseid' => $courseid));
$PAGE->navbar->add($heading, $viewurl);
$answerform = new answer_form($PAGE->url);

$answerform->set_data(array('questionid' => $questionid , 'qaaid' => $qaaid , 'userid' => $userid, 'courseid' => $courseid));
if ($fromform = $answerform->get_data()) {
    $qaap = new stdClass();
    $qaap->id = $fromform->questionid;                                                 
    $qaap->answer = $fromform->answer["text"];
    $qaap->qaaid = $fromform->qaaid;
    $qaap->teacherid = $USER->id;
    $qaap->teacherid_modified = $USER->id;
    $qaap->timecreatedanswer = time();
    $qaap->timemodifiedanswer = time();
    $course_id = $fromform->courseid;
    $module_id = $DB->get_field_sql("SELECT id FROM {modules}
    where name = 'qaa';");
    $course_module = $DB->get_field_sql("SELECT id FROM {course_modules}
    where course = {$course_id} and module = {$module_id} and instance = {$fromform->qaaid};");
    $context = context_module::instance($course_module);
    $context_id = $context->id;
    if ($DB->update_record('qaapairs', $qaap)) {
        $draftitemid = $fromform->attachments;
        $contextid = $context_id;
        $component = 'mod_qaa';
        $filearea = 'content';
        $itemid = $fromform->questionid;
        file_save_draft_area_files($draftitemid, $contextid, $component, $filearea, $itemid);
        $url = new moodle_url('/local/th_unanswered_questions/index.php?id=' . $fromform->userid);
        redirect($url);
}
} else if ($answerform->is_cancelled()) {
    $page_url = new moodle_url('/local/th_unanswered_questions/index.php?id=' . $userid );
    redirect($page_url);
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($heading);
    $answerform->display();
    echo $OUTPUT->footer();
}
?>
