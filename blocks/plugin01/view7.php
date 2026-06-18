<?php
require('../../config.php');
require_once('classes/form/myform.php');

global $DB , $OUTPUT , $PAGE , $COURSE;
$courseid = $COURSE->id;
require_login($courseid);
// $strviewfeed = get_string('pluginname', 'block_plugin01');
// $PAGE->set_title($strviewfeed);
$PAGE->set_heading('Test view 7 ');

$PAGE->set_url('/blocks/plugin01/view7.php');
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

$user = $DB->get_records_sql(
    'SELECT id, username , firstname FROM {user} WHERE id <=30 AND id <> 1 AND deleted = 0' 
);
print_object($user);

$username1= '';

if (!empty($user)) {
    $first = reset($user);
    $username1 = $first->firstname;
}
echo html_writer::tag('h3', 'Danh sách thành viên: ' . format_string($username1));

$table = new html_table();
$headerrow = new html_table_row();

foreach (['STT', 'Userid', 'Username', 'Firstname'] as $heading) {
    $cell = new html_table_cell($heading);
    $cell->header = true;
    $cell->attributes['class'] = 'cell headingcell';
    $headerrow->cells[] = $cell;
}
$table->data[] = $headerrow;

$stt=1;
foreach ($user as $item) {
    $row = new html_table_row();
    $row->cells[] = new html_table_cell($stt++);
    $row->cells[] = new html_table_cell(s($item->id));
    $row->cells[] = new html_table_cell(s($item->username));
    $row->cells[] = new html_table_cell(s($item->firstname));
    $table->data[] = $row;
}

echo html_writer::table($table);

// Instantiate the myform form from within the plugin.
// $mform = new \block_plugin01\form\myform();

// // Form processing and displaying is done here.
// if ($mform->is_cancelled()) {
    
// } else if ($fromform = $mform->get_data()) {
//    if (!empty($fromform->intro)) {
//         $mform->display();
//         // Lấy danh sách courseid từ form
//         if (!empty($fromform->courseids) && is_array($fromform->courseids)) {
//             foreach ($fromform->courseids as $courseid) {
//                 $rec = $DB->get_records_sql(
//                     "SELECT 
//                         u.id AS user_id,
//                         u.username,
//                         u.firstname,
//                         u.lastname,
//                         u.email,
//                         c.fullname AS coursename,
//                         GROUP_CONCAT(DISTINCT r.shortname ORDER BY r.shortname SEPARATOR ', ') AS roles
//                     FROM {user} u
//                     JOIN {user_enrolments} ue ON ue.userid = u.id
//                     JOIN {enrol} e ON e.id = ue.enrolid
//                     JOIN {course} c ON c.id = e.courseid
//                     JOIN {role_assignments} ra ON ra.userid = u.id 
//                     JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50 AND ctx.instanceid = c.id
//                     JOIN {role} r ON r.id = ra.roleid
//                     WHERE u.deleted = 0 AND c.id = :courseid
//                     GROUP BY u.id, u.username, u.firstname, u.lastname, u.email, c.fullname
//                     ORDER BY u.id ASC",
//                     ['courseid' => $courseid],
//                 );

//                 $coursename = '';
//                 if (!empty($rec)) {
//                     $first = reset($rec);
//                     $coursename = $first->coursename;
//                 }
//                 echo html_writer::tag('h3', 'Danh sách thành viên trong khoá: ' . format_string($coursename));

//                 $table = new html_table();
//                 $headerrow = new html_table_row();

//                 foreach (['STT', 'Userid', 'Username', 'Full name', 'Role', 'Email'] as $heading) {
//                     $cell = new html_table_cell($heading);
//                     $cell->header = true;
//                     $cell->attributes['class'] = 'cell headingcell';
//                     $headerrow->cells[] = $cell;
//                 }
//                 $table->data[] = $headerrow;

//                 $stt = 1;
//                 foreach ($rec as $item) {
//                     $row = new html_table_row();
//                     $row->cells[] = new html_table_cell($stt++);
//                     $row->cells[] = new html_table_cell(s($item->user_id));
//                     $row->cells[] = new html_table_cell(s($item->username));
//                     $row->cells[] = new html_table_cell(s($item->firstname . ' ' . $item->lastname));
//                     $row->cells[] = new html_table_cell(s($item->roles));
//                     $row->cells[] = new html_table_cell(s($item->email));
//                     $table->data[] = $row;
//                 }

//                 echo html_writer::table($table);
//                  echo html_writer::table($table);
                
//             }
//         } else {
//             echo html_writer::div('Vui lòng chọn ít nhất một khoá học.', 'alert alert-warning');
//         }

//     }
                
// } else {
  
//     $mform->display();
// }



echo $OUTPUT->footer();