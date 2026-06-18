<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'th_report_hide_course_form.php';
require_once $CFG->dirroot . '/blocks/th_report_hide_course/classes/libs.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_report_hide_course', $courseid);
}

if(!has_capability('block/th_report_hide_course:view', context_course::instance($courseid))){
	$is_teacher = $DB->get_record_sql("SELECT c.id AS course_id, c.fullname AS course_name
									FROM {role_assignments} ra
									JOIN {context} ctx ON ra.contextid = ctx.id
									JOIN {course} c ON ctx.instanceid = c.id
									JOIN {role} r ON ra.roleid = r.id
									JOIN {user} u ON ra.userid = u.id
									WHERE u.id = $USER->id
									AND r.shortname = 'teacher'
									AND ctx.contextlevel = 50 
									LIMIT 1;");
	require_login($is_teacher->course_id);
	require_capability('block/th_report_hide_course:view', context_course::instance($is_teacher->course_id));
} else {
	$is_teacher = null;
	require_login($courseid);
	require_capability('block/th_report_hide_course:view', context_course::instance($courseid));
}

// Check for all required variables.
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$pageurl = '/blocks/th_report_hide_course/view_detail.php';
$title = get_string('title', 'block_th_report_hide_course');
$PAGE->set_url('/blocks/th_report_hide_course/view_detail.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('title', 'block_th_report_hide_course'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_report_hide_course'));

$editurl = new moodle_url('/blocks/th_report_hide_course/view_detail.php');
$settingsnode = $PAGE->navbar->add(get_string('title', 'block_th_report_hide_course'), $editurl);
$settingsnode->make_active();

$th_report_hide_course = new th_report_hide_course_form();

if ($th_report_hide_course->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $th_report_hide_course->get_data()) {

	$startdate = $fromform->startdate;
	$enddate = strtotime("+23 hours 59 minutes 59 seconds", $startdate);

	if($is_teacher){
		$listcourses = get_list_courses_teacher_status($startdate, $enddate);
	} else {
		$listcourses = get_list_courses_status($startdate, $enddate);
	}

	$table = new html_table();
	$table->head = array(get_string('stt', 'block_th_report_hide_course')
						, get_string('course_name', 'block_th_report_hide_course')
						, get_string('course_shortname', 'block_th_report_hide_course')
						, get_string('teacher', 'block_th_report_hide_course')
						, get_string('sdt', 'block_th_report_hide_course')
						, get_string('email', 'block_th_report_hide_course')
						, get_string('start_date', 'block_th_report_hide_course')
						, get_string('status', 'block_th_report_hide_course')
						, get_string('date_approval', 'block_th_report_hide_course'));
	$stt = 0;

	foreach ($listcourses as $k => $courses) {

		$stt++;

		$link_course = new moodle_url('/course/view.php', ['id' => $courses->id]);
		$link = html_writer::link($link_course, $courses->fullname);

		$role_teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));
		$role_qlht_aum = $DB->get_record('role', array('shortname' => 'qlht_aum'));
		$role_gvcn = $DB->get_record('role', array('shortname' => 'teacher'));
		$context = context_course::instance($courses->id);
		
		$teachers = get_role_users($role_teacher->id, $context);

		$giang_vien = '';
		$ds_sdt2 = '';
		$ds_email = '';

		if (!empty($teachers)){
		
			foreach ($teachers as $k => $teacher) {

				$teacher_id = $teacher->id;
				$teacher = $DB->get_record_sql("SELECT * FROM {user} WHERE id = '$teacher_id'");

				$name = $teacher->firstname . ' ' . $teacher->lastname;
				$giang_vien = $giang_vien . $name . '<br>';
				
				$sdt2 = $teacher->phone2;
				$ds_sdt2 = $ds_sdt2 . $sdt2 . '<br>';

				$email = $teacher->email;
				$ds_email = $ds_email . $email . '<br>';
			}
		}
		
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($link);
		$row->cells[] = $cell;
		$cell = new html_table_cell($courses->shortname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($giang_vien);
		$row->cells[] = $cell;
		$cell = new html_table_cell($ds_sdt2);
		$row->cells[] = $cell;
		$cell = new html_table_cell($ds_email);
		$row->cells[] = $cell;
		$cell = new html_table_cell(date("H:i:s d-m-Y", $courses->startdate));
		$row->cells[] = $cell;
		$cell = new html_table_cell();

		if($courses->ishidden == 0 && $courses->ishidden != null){
			if($courses->timecreated_status > $courses->startdate){
				$cell->text = html_writer::tag('span', get_string('approval_late', 'block_th_report_hide_course'),
    array('class' => 'badge badge-warning'));
			} else {
				$cell->text = html_writer::tag('span', get_string('approved', 'block_th_report_hide_course'),
    array('class' => 'badge badge-success'));
			}
		} else {
			$cell->text = html_writer::tag('span', get_string('not_approved', 'block_th_report_hide_course'),
    array('class' => 'badge badge-danger'));
		}
		
		if($courses->timecreated_status != null){
			$row->cells[] = $cell;
			$cell = new html_table_cell(date("H:i:s d-m-Y", $courses->timecreated_status));
		} else {
			$row->cells[] = $cell;
			$cell = new html_table_cell('');
		}
		
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	$table->attributes = array('class' => 'th_hide_course_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);

	echo $OUTPUT->header();
	echo $OUTPUT->heading("<center>$title</center>");
	$th_report_hide_course->display();
	echo $html;
	$lang = current_language();
	echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_hide_course_table', 'LIST HIDE COURSE', $lang));
	echo $OUTPUT->footer();

} else {
	echo $OUTPUT->header();
	echo $OUTPUT->heading("<center>$title</center>");
	$th_report_hide_course->display();
	echo $OUTPUT->footer();
}

?>

