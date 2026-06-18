<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once 'th_question_number_report_form.php';
require_once($CFG->dirroot . '/blocks/th_question_number_report/lib.php');
require_once($CFG->dirroot . '/blocks/th_question_number_report/th_get_question_count.php');

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_question_number_report', $courseid);
}

require_login($courseid);
require_capability('block/th_question_number_report:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_question_number_report/view.php';
$title = get_string('title', 'block_th_question_number_report');
$PAGE->set_url('/blocks/th_question_number_report/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_question_number_report', 'block_th_question_number_report'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_question_number_report'));

$editurl = new moodle_url('/blocks/th_question_number_report/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_question_number_report'), $editurl);
$settingsnode->make_active();

$th_question_number_report = new th_question_number_report_form();

if ($th_question_number_report->is_cancelled()) {
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $th_question_number_report->get_data()) {
	$list_course = $fromform->course_id;
	if(empty($list_course)) {
		$ds_course = th_question_number_report_ds_course();
		$list_shortname = [];
		
		foreach($ds_course as $k => $shortname){
			$pos = strpos($shortname, '-');

			if ($pos !== false) {
				$shortname_arr = explode('-', $shortname);
				$shortname = $shortname_arr[0];
			}

			if (!in_array($shortname, $list_shortname)){
				$couse_latest = $DB->get_record_sql("SELECT * FROM {course} WHERE NOT id = 1 AND visible <> 0 AND fullname NOT LIKE '% - mẫu' AND shortname LIKE '$shortname%' AND startdate = (SELECT MAX(startdate) FROM {course} WHERE NOT id = 1 AND fullname NOT LIKE '% - mẫu' AND shortname LIKE '$shortname%' AND visible <> 0)");
				$list_shortname[$couse_latest->id] = $couse_latest->fullname;
			}
		}
		$list_course = [];
		foreach ($list_shortname as $k => $shortname) {
			$list_course[] = $k;
		}
	}

	$table = new html_table();
	$table->head = array(
		get_string('stt', 'block_th_question_number_report'),
		get_string('course_name', 'block_th_question_number_report'),
		get_string('template_question_count', 'block_th_question_number_report'),
		get_string('latest_course_question_count', 'block_th_question_number_report')
	);

	$stt = 0;

	switch ($fromform->option) {
	    case 0:
	        foreach ($list_course as $course_id){
	        	$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$course_id'");
                $so_luong = th_get_question_count($course_id);
	        	if($so_luong > $fromform->so_luong) {

		        	$pos2 = strrpos($course->fullname, ' - ');

					if ($pos2 !== false) {
						$fullname_mau = substr($course->fullname, 0, $pos2) . ' - mẫu';
					} else {
						$fullname_mau = $course->fullname . ' - mẫu';
					}

					$course_mau = $DB->get_record_sql("SELECT * FROM {course} WHERE fullname = '$fullname_mau'");

					if(!empty($course_mau)){
						$course_mau_id = $course_mau->id;
						$so_luong_course_mau = th_get_question_count($course_mau_id);
						$link = new moodle_url('/course/view.php', ['id' => $course_mau_id]);
						$link_course_mau = html_writer::link($link, $so_luong_course_mau);
					} else {
						$a = $course->fullname;
						$link_course_mau = get_string('link_course_mau_not_found', 'block_th_question_number_report', $a);
					}

					$stt++;
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$link = new moodle_url('/course/view.php', ['id' => $course_id]);
					$link_course = html_writer::link($link, $course->fullname);
					$cell = new html_table_cell($link_course);
					$row->cells[] = $cell;
					$cell = new html_table_cell($link_course_mau);
					$row->cells[] = $cell;
					$cell = new html_table_cell($so_luong);
					$row->cells[] = $cell;
					$table->data[] = $row;
				}
	        }
	        
	        break;
	    case 1:
	        foreach ($list_course as $course_id){
	        	$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$course_id'");
				$so_luong = th_get_question_count($course_id);
	        	if($so_luong < $fromform->so_luong) {

		        	$pos2 = strrpos($course->fullname, ' - ');

					if ($pos2 !== false) {
						$fullname_mau = substr($course->fullname, 0, $pos2) . ' - mẫu';
					} else {
						$fullname_mau = $course->fullname . ' - mẫu';
					}

					$course_mau = $DB->get_record_sql("SELECT * FROM {course} WHERE fullname = '$fullname_mau'");

					if(!empty($course_mau)){
						$course_mau_id = $course_mau->id;
						$so_luong_course_mau = th_get_question_count($course_mau_id);
						$link = new moodle_url('/course/view.php', ['id' => $course_mau_id]);
						$link_course_mau = html_writer::link($link, $so_luong_course_mau);
					} else {
						$a = $course->fullname;
						$link_course_mau = get_string('link_course_mau_not_found', 'block_th_question_number_report', $a);
					}

					$stt++;
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$link = new moodle_url('/course/view.php', ['id' => $course_id]);
					$link_course = html_writer::link($link, $course->fullname);
					$cell = new html_table_cell($link_course);
					$row->cells[] = $cell;
					$cell = new html_table_cell($link_course_mau);
					$row->cells[] = $cell;
					$cell = new html_table_cell($so_luong);
					$row->cells[] = $cell;
					$table->data[] = $row;
				}
	        }

	        break;
	    case 2:
	        foreach ($list_course as $course_id){
	        	$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = '$course_id'");
                $so_luong = th_get_question_count($course_id);
	        	if($so_luong == $fromform->so_luong) {

		        	$pos2 = strrpos($course->fullname, ' - ');

					if ($pos2 !== false) {
						$fullname_mau = substr($course->fullname, 0, $pos2) . ' - mẫu';
					} else {
						$fullname_mau = $course->fullname . ' - mẫu';
					}

					$course_mau = $DB->get_record_sql("SELECT * FROM {course} WHERE fullname = '$fullname_mau'");

					if(!empty($course_mau)){
						$course_mau_id = $course_mau->id;
						$so_luong_course_mau = th_get_question_count($course_mau_id);
						$link = new moodle_url('/course/view.php', ['id' => $course_mau_id]);
						$link_course_mau = html_writer::link($link, $so_luong_course_mau);
					} else {
						$a = $course->fullname;
						$link_course_mau = get_string('link_course_mau_not_found', 'block_th_question_number_report', $a);
					}

					$stt++;
					$row = new html_table_row();
					$cell = new html_table_cell($stt);
					$row->cells[] = $cell;
					$link = new moodle_url('/course/view.php', ['id' => $course_id]);
					$link_course = html_writer::link($link, $course->fullname);
					$cell = new html_table_cell($link_course);
					$row->cells[] = $cell;
					$cell = new html_table_cell($link_course_mau);
					$row->cells[] = $cell;
					$cell = new html_table_cell($so_luong);
					$row->cells[] = $cell;
					$table->data[] = $row;
				}
	        }

	        break;
	}

	$table->attributes = array('class' => 'th_question_number_report_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);

	echo $OUTPUT->header();
	echo $OUTPUT->heading("<center>$title</center>");
	$th_question_number_report->display();
	echo $OUTPUT->heading("<center>" . get_string('question_bank_count', 'block_th_question_number_report') . "</center>");
	echo $html;
	$lang = current_language();
	echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
	$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_question_number_report_table', get_string('question_bank_count', 'block_th_question_number_report'), $lang));
	echo $OUTPUT->footer();

} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();
	echo $OUTPUT->heading("<center>$title</center>");
	echo "</br>";
	$th_question_number_report->display();
	echo $OUTPUT->footer();
}

?>

