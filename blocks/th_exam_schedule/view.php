<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/view.php'));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('viewmyschedule', 'block_th_exam_schedule'));
$PAGE->set_heading(get_string('viewmyschedule', 'block_th_exam_schedule'));
$PAGE->navbar->add(get_string('viewmyschedule', 'block_th_exam_schedule'),
    new moodle_url('/block/th_exam_schedule/view.php'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('myschedule', 'block_th_exam_schedule'));
$table = new \block_th_exam_schedule\output\table($USER->id, null, true);

echo $table->render();

echo $OUTPUT->footer();
