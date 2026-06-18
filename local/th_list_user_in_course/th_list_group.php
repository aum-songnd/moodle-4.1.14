<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once $CFG->dirroot . '/local/th_list_user_in_course/lib.php';

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
$pageurl = '/local/th_list_user_in_course/th_list_group.php';
$PAGE->set_url('/local/th_list_user_in_course/th_list_group.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading('Danh sách nhóm');

$editurl = new moodle_url('/local/th_list_user_in_course/th_list_group.php');
$settingsnode = $PAGE->navbar->add('Danh sách nhóm', $editurl);
$settingsnode->make_active();

if (empty($id)){
    echo $OUTPUT->header();
    echo "Không tìm thấy id khóa học nào. Vui lòng kiểm tra lại !";
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
$baseurl = new moodle_url('/local/th_list_user_in_course/th_list_group.php');
if ($editcontrols = local_th_list_user_in_course_controls($context, $baseurl, $id)) {
    echo $OUTPUT->render($editcontrols);
}

$add_url = new moodle_url('/local/th_list_user_in_course/th_add_group.php', array('id' => $id));
echo $OUTPUT->heading('Danh sách nhóm');
echo "<div style='text-align: right;'><a href='$add_url' style='color:#ffff;background-color:#8ec63f;padding:7px 18px;position: relative;top: -3px;margin-left: 5px;''  id='cancel1'>Tạo nhóm</a></div>";

$list_groups_sql = "SELECT * FROM {th_list_user_in_course_group} WHERE course_id = $id";
$list_groups = $DB->get_records_sql($list_groups_sql);

$table = new html_table();
$table->head = array('Tên nhóm', 'Mô tả', 'Ngày tạo', 'Thao tác');

if(!empty($list_groups)) {
    foreach($list_groups as $group) {

        $row = new html_table_row();
        $cell = new html_table_cell("<strong>$group->ten_nhom</strong>");
        $row->cells[] = $cell;
        $cell = new html_table_cell($group->mo_ta);
        $row->cells[] = $cell;
        $cell = new html_table_cell(date("Y-m-d H:i:s", $group->timecreated));
        $row->cells[] = $cell;
        $urlparams = array('id' => $group->id);
        $edit = html_writer::link(new moodle_url('/local/th_list_user_in_course/th_edit_group.php', $urlparams),
            $OUTPUT->pix_icon('t/editstring', get_string('edit')),
            array('title' => get_string('edit')));
        $delete = html_writer::link(new moodle_url('/local/th_list_user_in_course/th_edit_group.php', $urlparams + array('delete' => 1)),
            $OUTPUT->pix_icon('t/delete', get_string('delete')),
            array('title' => get_string('delete')));

        $cell = new html_table_cell($edit . $delete);
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
}

echo "<style>/* Style the table */
table {
  border-collapse: collapse !important;
  border-spacing: 0 !important;
  width: 100% !important;
  border: 1px solid #ddd !important;
}

/* Style table headers and table data */
th, td {
  text-align: left !important;
}

th:first-child, td:first-child {
  text-align: left !important;
}

tr {
    border-bottom: 1px solid #ddd;
}

thead {
    background: #f2f2f2;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 18px;
    border-bottom: 1px solid #ddd !important;
}

/* Zebra-striped table rows */
tr:nth-child(even) {
  background-color: #f2f2f2 !important
}
</style>";

$table->attributes = array('class' => 'th_list_group');
$html = html_writer::table($table);
echo $html;

$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_list_group', 'Danh sách nhóm', $lang));

echo $OUTPUT->footer();
?>

