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
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', 0, PARAM_URL);

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
    echo "Không tìm thấy id nhóm nào. Vui lòng kiểm tra lại !";
    echo $OUTPUT->footer();
    exit;
}

if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $DB->delete_records('th_list_user_in_course_group', ['id' => $id]);
        $DB->delete_records('th_user_in_group', ['group_id' => $id]);

        redirect($returnurl, 'Xóa nhóm thành công', null, \core\output\notification::NOTIFY_SUCCESS);
    }
    $strheading = get_string('delete');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $record = $DB->get_record('th_list_user_in_course_group', array('id' => $id));

    $returnurl = new moodle_url('/local/th_list_user_in_course/th_list_group.php', array('id' => $record->course_id));
    $yesurl = new moodle_url('/local/th_list_user_in_course/th_edit_group.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl));
    $message = "Bạn có chắc chắn muốn xóa Nhóm " . html_writer::tag('b', $record->ten_nhom);
    echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_list_user_in_course/th_list_group.php', array('id' => $record->course_id)));
    echo $OUTPUT->footer();
    die;
}

$th_add_group = new th_add_group_form();

$group_sql = "SELECT * FROM {th_list_user_in_course_group} WHERE id = $id";
$group = $DB->get_record_sql($group_sql);
$th_add_group->set_data(array('id' => $id, 'ten_nhom' => $group->ten_nhom, 'mo_ta' => $group->mo_ta, 'course_id' => $group->course_id));

if ($th_add_group->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    redirect(new moodle_url('/local/th_list_user_in_course/th_list_group.php', array('id' => $group->course_id)));
} else if ($fromform = $th_add_group->get_data()) {

	$record = new stdClass();
	$record->id = $fromform->id;
    $record->ten_nhom = $fromform->ten_nhom;
    $record->mo_ta = $fromform->mo_ta;
    $record->course_id = $fromform->course_id;
    $record->ngay_tao = time();
    $DB->update_record('th_list_user_in_course_group', $record, false);
    
    redirect($CFG->wwwroot . "/local/th_list_user_in_course/th_list_group.php?id=$fromform->course_id", 'Sửa nhóm thành công', null, \core\output\notification::NOTIFY_SUCCESS);
	
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
	echo $OUTPUT->heading('Tạo nhóm mới');
    $th_add_group->display();
    echo $OUTPUT->footer();
}