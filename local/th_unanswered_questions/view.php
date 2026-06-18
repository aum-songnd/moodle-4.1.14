<?php

require_once '../../config.php';
require_once $CFG->dirroot.'/local/th_unanswered_questions/th_unanswered_questions_form.php';

global $PAGE, $COURSE, $OUTPUT;


if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_unanswered_questions', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_unanswered_questions:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_unanswered_questions/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$selectteacher  = get_string('selectteacher', 'local_th_unanswered_questions');
$PAGE->set_heading($selectteacher );
$PAGE->set_title($selectteacher );
$viewurl = new moodle_url('/local/th_unanswered_questions/view.php');
$PAGE->navbar->add($selectteacher , $viewurl);

$form = new th_unanswered_questions_form();

if ($form->is_cancelled()) {
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $form->get_data()) {
    $gv_id = $fromform->giang_vien;
    redirect($CFG->wwwroot.'/local/th_unanswered_questions/index.php?id='. $gv_id);
} else {
	echo $OUTPUT->header();
	echo $OUTPUT->heading($selectteacher );
	echo "</br>";
	$form->display();
	echo $OUTPUT->footer();
}



