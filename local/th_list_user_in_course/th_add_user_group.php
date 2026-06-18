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
$course_id = optional_param('course_id', 0, PARAM_INT);

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
    echo "Không tìm thấy người dùng nào. Vui lòng kiểm tra lại !";
    echo $OUTPUT->footer();
    exit;
}

if (empty($course_id)){
    echo $OUTPUT->header();
    echo "Không tìm thấy khóa học nào. Vui lòng kiểm tra lại !";
    echo $OUTPUT->footer();
    exit;
}

$th_add_user_group = new th_add_user_group_form();

$groups = $DB->get_records_sql("SELECT gr.* FROM {th_user_in_group} as ug, {th_list_user_in_course_group} as gr WHERE ug.user_id = $id AND ug.group_id = gr.id AND gr.course_id = $course_id");
$list_group = [];
if (count($groups)) {
    foreach ($groups as $k => $group) {
        $list_group[] = $k;
    }
}

$th_add_user_group->set_data(array('id' => $id, 'course_id' => $course_id, 'group_id' => $list_group));

if ($th_add_user_group->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    redirect(new moodle_url('/local/th_list_user_in_course/index.php', array('id' => $course_id)));
} else if ($fromform = $th_add_user_group->get_data()) {

    $list_groups_new = $fromform->group_id;

    foreach($list_groups_new as $group) {
        $record = new stdClass();
        $record->user_id = $fromform->id;
        $record->group_id = $group;
        $record->ngay_tao = time();

        if(empty($DB->record_exists_sql("SELECT * FROM {th_user_in_group} WHERE user_id = $fromform->id AND group_id = $group"))){
            $DB->insert_record('th_user_in_group', $record, false);
        }
    }

    foreach($groups as $group){
        if(!in_array($group->id, $list_groups_new)){
            $id = $DB->get_field_sql("SELECT id FROM {th_user_in_group} WHERE user_id = $fromform->id AND group_id = $group->id");
            $DB->delete_records('th_user_in_group', ['id' => $id]);
        }
    }

    redirect(new moodle_url('/local/th_list_user_in_course/index.php', array('id' => $course_id)), get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
    
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
    echo $OUTPUT->heading('Thêm thành viên vào nhóm');
    $th_add_user_group->display();
    echo $OUTPUT->footer();
}