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
    "SELECT 
        u.id AS user_id,
        u.username,
        u.firstname,
        u.lastname,
        u.email,
        c.fullname AS coursename,
        GROUP_CONCAT(DISTINCT r.shortname ORDER BY r.shortname SEPARATOR ', ') AS roles
    FROM {user} u
    JOIN {user_enrolments} ue ON ue.userid = u.id
    JOIN {enrol} e ON e.id = ue.enrolid
    JOIN {course} c ON c.id = e.courseid
    
    JOIN {role_assignments} ra ON ra.userid = u.id 
    JOIN {role} r ON r.id = ra.roleid
    WHERE u.deleted = 0 AND c.id = 8 AND EXISTS (
        SELECT 1
        FROM {role_assignments} ra2
        JOIN {role} r2 ON r2.id = ra2.roleid
        WHERE ra2.userid = u.id
        AND r2.shortname = 'gvcn'
    )
    GROUP BY u.id, u.username, u.firstname, u.lastname, u.email, c.fullname
    ORDER BY u.id ASC"
   
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

$cell = new html_table_cell('Role');
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
    $row->cells[] = new html_table_cell(s($item->roles));
    $row->cells[] = new html_table_cell(s($item->email));

    $table->data[] = $row;
}

echo html_writer::table($table);

echo $OUTPUT->footer();


