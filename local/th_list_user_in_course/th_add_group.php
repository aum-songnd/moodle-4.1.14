<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once $CFG->dirroot . '/local/th_list_user_in_course/lib.php';
require_once $CFG->dirroot . '/local/th_list_user_in_course/th_list_user_in_course_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'local_th_list_user_in_course', $courseid);
}

require_login($courseid);
require_capability('local/th_list_user_in_course:view', context_course::instance($COURSE->id));

$context = context_system::instance();
$pageurl = '/local/th_list_user_in_course/th_add_group.php';
$PAGE->set_url('/local/th_list_user_in_course/th_add_group.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('Tạo nhóm mới');
$PAGE->set_title($SITE->fullname . ': ' . 'Tạo nhóm mới');

$editurl = new moodle_url('/local/th_list_user_in_course/th_add_group.php');
$settingsnode = $PAGE->navbar->add('Tạo nhóm mới', $editurl);
$settingsnode->make_active();

if (empty($id)){
    echo $OUTPUT->header();
    echo "Không tìm thấy id khóa học nào. Vui lòng kiểm tra lại !";
    echo $OUTPUT->footer();
    exit;
}

$th_add_group = new th_add_group_form();

$th_add_group->set_data(array('id' => $id, 'ten_nhom' => '', 'mo_ta' => ''));

if ($th_add_group->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    redirect(new moodle_url('/local/th_list_user_in_course/th_list_group.php', array('id' => $id)));
} else if ($fromform = $th_add_group->get_data()) {

	$record = new stdClass();
    $record->ten_nhom = $fromform->ten_nhom;
    $record->mo_ta = $fromform->mo_ta;
    $record->course_id = $fromform->id;
    $record->ngay_tao = time();
    $DB->insert_record('th_list_user_in_course_group', $record, false);
    
    redirect($CFG->wwwroot . "/local/th_list_user_in_course/th_list_group.php?id=$fromform->id", get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
	
	
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
	echo $OUTPUT->heading('Tạo nhóm mới');
    $th_add_group->display();
    echo $OUTPUT->footer();
}