<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once 'edit_form.php';
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$id        = optional_param('id', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);


if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse');
}

$context = context_system::instance();
require_login($COURSE->id);
require_capability('mod/thpage:viewall', $context);

$pageurl = '/mod/thpage/edit.php';
$title = get_string('pluginname', 'mod_thpage');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'mod_thpage'));
$PAGE->set_title(get_string('pluginname', 'mod_thpage'));


$editurl = new moodle_url('/mod/thpage/edit.php');
$settingsnode = $PAGE->navbar->add(get_string('pluginname', 'mod_thpage'), $editurl);
$settingsnode->make_active();

if ($id) {
	$data_edit = $DB->get_record('th_teacher_infor', array('id' => $id), '*', MUST_EXIST);
} else {
	$data_edit              = new stdClass();
	$data_edit->cohorts      = '';
	$data_edit->fullname     = '';
	$data_edit->phone  = '';
	$data_edit->email = '';
	$data_edit->role = '';
	$data_edit->gender = '';
}

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/mod/thpage/viewall.php');
}

$edit = new edit_form();

$edit->set_data(
	array('cohorts' => $data_edit->cohorts, 
		'fullname' => $data_edit->fullname, 
		'phone' => $data_edit->phone, 
		'email' => $data_edit->email, 
		'role' => $data_edit->role, 
		'gender' => $data_edit->gender, 
		'id' => $id)
);

if ($delete) {
	$PAGE->url->param('delete', 1);
	if ($confirm and confirm_sesskey()) {

		$DB->delete_records('th_teacher_infor', ['id' => $id]);
		redirect($CFG->wwwroot . '/mod/thpage/viewall.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
	}
	$strheading = get_string('delete');
	$PAGE->navbar->add($strheading);
	$PAGE->set_title($strheading);
	$PAGE->set_heading($strheading);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strheading);
	$record  = $DB->get_record('th_teacher_infor', array('id' => $id));
	$yesurl  = new moodle_url('/mod/thpage/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
	$message = get_string('message_confirm') . $record->fullname;
	echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/mod/thpage/viewall.php'));
	echo $OUTPUT->footer();
	die;
}

if ($edit->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/mod/thpage/viewall.php');
	redirect($courseurl);
} else if ($fromform = $edit->get_data()) {

	if ($id) {
		$cohorts        = $fromform->cohorts;
		$fullname = $fromform->fullname;
		$phone = $fromform->phone;
		$email = $fromform->email;
		$role = $fromform->role;
		$gender = $fromform->gender;

		$dataobjects             = new stdClass();
		$dataobjects->id     = $id;
		$dataobjects->cohorts     = $cohorts;
		$dataobjects->fullname    = $fullname;
		$dataobjects->phone = $phone;
		$dataobjects->email   = $email;
		$dataobjects->role  = $role;
		$dataobjects->gender   = $gender;
		$DB->update_record('th_teacher_infor', $dataobjects);

		redirect($returnurl, get_string('update_success', 'mod_thpage'), null, \core\output\notification::NOTIFY_SUCCESS);
	} else {
		$cohorts        = $fromform->cohorts;
		$fullname = $fromform->fullname;
		$phone = $fromform->phone;
		$email = $fromform->email;
		$role = $fromform->role;
		$gender = $fromform->gender;

		$dataobjects             = new stdClass();
		$dataobjects->cohorts     = $cohorts;
		$dataobjects->fullname    = $fullname;
		$dataobjects->phone = $phone;
		$dataobjects->email   = $email;
		$dataobjects->role  = $role;
		$dataobjects->gender   = $gender;
		$DB->insert_record('th_teacher_infor', $dataobjects, false);

		redirect($returnurl, get_string('add_success', 'mod_thpage'), null, \core\output\notification::NOTIFY_SUCCESS);
	}

} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();

	$baseurl = new moodle_url($pageurl);
	if ($editcontrols = mod_thpage_controls($context, $baseurl)) {
		echo $OUTPUT->render($editcontrols);
	}
	if ($id) {
		echo $OUTPUT->heading(get_string('edit'));
	} else {
		echo $OUTPUT->heading(get_string('add'));
	}

	echo "</br>";

	$edit->display();
	echo $OUTPUT->footer();
}
