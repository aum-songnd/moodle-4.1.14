<?php

require('../../config.php');

global $DB, $OUTPUT, $PAGE, $COURSE;

$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_plugin01', $courseid);
}

require_login($courseid);

$PAGE->set_url('/blocks/plugin01/view3.php');
$PAGE->set_pagelayout('standard');

$strviewfeed = get_string('pluginname', 'block_plugin01');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

echo $OUTPUT->header();

$rec = $DB->get_records_sql(
    'SELECT u.id AS user_id, u.username, u.firstname, u.lastname, u.email, c.fullname AS coursename
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {course} c ON c.id = e.courseid
        WHERE u.deleted = 0 AND u.id <> :excludedid AND c.id = :courseid 
        ORDER BY user_id ASC',
    ['excludedid' => 2 , 
    'courseid' => 8
    ],
);
print_object($rec);
$coursename = '';
if (!empty($rec)) {
    $first = reset($rec);
    $coursename = $first->coursename;
}
echo html_writer::tag('h3', 'Danh sách học viên trong khoá: ' . format_string($coursename));

// Tạo bảng
$table = new html_table();

$headerrow = new html_table_row();

$cell = new html_table_cell('STT');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Userid');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Username');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;



$cell = new html_table_cell('Full name');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Email');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$table->data[] = $headerrow;

// Dữ liệu
$stt = 1;
foreach ($rec as $item) {
    $row = new html_table_row();

    $row->cells[] = new html_table_cell($stt++);
    $row->cells[] = new html_table_cell(s($item->user_id));
    $row->cells[] = new html_table_cell(s($item->username));
    $row->cells[] = new html_table_cell(s($item->firstname . ' ' . $item->lastname));
    $row->cells[] = new html_table_cell(s($item->email));

    $table->data[] = $row;
}

echo html_writer::table($table);
echo $OUTPUT->footer();


