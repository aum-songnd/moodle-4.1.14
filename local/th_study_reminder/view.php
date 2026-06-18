<?php

require_once '../../config.php';

require_once $CFG->dirroot . '/local/th_study_reminder/select_courses_to_send_notification_form.php';
require_once $CFG->dirroot . '/local/th_study_reminder/locallib.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'error');
}

require_login($COURSE->id);
require_capability('local/th_study_reminder:view', context_system::instance());

$title = get_string('pluginname', 'local_th_study_reminder');
$url = new moodle_url('/local/th_study_reminder/view.php');
$context = context_system::instance();
$lang = current_language();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);

$settingsnode = $PAGE->navbar->add($title, $url);
$settingsnode->make_active();

$mform = new select_courses_to_send_notification_form();
if ($mform->is_cancelled()) {

    redirect($url);
} else if ($mform->is_submitted() && $mform->is_validated()) {
    $data = $mform->get_data();

    $DB->delete_records('th_study_reminder'); 
    foreach ($data->courses as $courseid) {
        $DB->insert_record('th_study_reminder', ['courseid' => $courseid]);
    }

} 

echo $OUTPUT->header($title);
echo $OUTPUT->heading($title);
$mform->display();

echo $OUTPUT->footer();