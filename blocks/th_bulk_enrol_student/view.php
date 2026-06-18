<?php

require_once '../../config.php';
require_once 'blocklib.php';
require_once $CFG->libdir . '/csvlib.class.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';
require_once $CFG->dirroot . '/local/thlib/classes/PHPExcel/IOFactory.php';
require_once $CFG->dirroot . '/local/thlib/classes/PHPExcel.php';
require_once 'th_bulk_enrol_student_form.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $_FILES;

$th_bulkenrol_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_bulk_enrol_student', $courseid);
}

require_login($courseid);
require_capability('block/th_bulk_enrol_student:view', context_course::instance($COURSE->id));
$context = context_system::instance();
$pageurl = '/blocks/th_bulk_enrol_student/view.php';
$title = get_string('enrolcoursetitle', 'block_th_bulk_enrol_student');
$PAGE->set_url('/blocks/th_bulk_enrol_student/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_bulk_enrol_student', 'block_th_bulk_enrol_student'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_bulk_enrol_student'));

$editurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'block_th_bulk_enrol_student'), $editurl);
$settingsnode->make_active();

if (!$enrol_manual = enrol_get_plugin('manual')) {
	throw new coding_exception('Can not instantiate enrol_manual');
}

if (empty($th_bulkenrol_csvkey)) {

	$th_excel_bulk_enrol_student_form = new th_excel_bulk_enrol_student_form();

	if ($th_excel_bulk_enrol_student_form->is_cancelled()) {

		if (!file_exists($CFG->dataroot . '/th_bulk_enrol_student_uploads')) {
			mkdir($CFG->dataroot . '/th_bulk_enrol_student_uploads', 0744);
		}

		//delete all file upload
		$files = glob("$CFG->dataroot/th_bulk_enrol_student_uploads/*"); // get all file names
		foreach ($files as $file) {
			// iterate files
			if (is_file($file)) {
				unlink($file); // delete file
			}
		}
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_excel_bulk_enrol_student_form->get_data()) {
		
		$filename = $th_excel_bulk_enrol_student_form->get_new_filename('file');

		if (!file_exists($CFG->dataroot . '/th_bulk_enrol_student_uploads')) {
			mkdir($CFG->dataroot . '/th_bulk_enrol_student_uploads', 0744);
		}
		$inputFileName = $CFG->dataroot . '/th_bulk_enrol_student_uploads/' . $filename;
		$th_excel_bulk_enrol_student_form->save_file('file', $inputFileName, true);

		$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objPHPExcel = $objReader->load($inputFileName);

		$allsheet = $objPHPExcel->getAllSheets();
		$a = $objPHPExcel->getActiveSheet();

		//Dem so luong sheet.
		$count_sheet = count($allsheet);
		$data_file = new stdClass();
		$data_file->option = $fromform->option;
		$data = new stdClass();
		for ($i = 0; $i < $count_sheet; ++$i) {

			$sheet = $objPHPExcel->getSheet($i);
			$highestRow = $sheet->getHighestRow();

			$allsheet_name = $objPHPExcel->getSheetNames();
			$sheet_name = $allsheet_name[$i];

			// Lấy tổng số cột của file.
			$highestColumn = $sheet->getHighestColumn();

			//  Thực hiện việc lặp qua từng dòng của file, để lấy thông tin
			$emails = [];
			for ($row = 1; $row <= $highestRow; $row++) {
				// Lấy dữ liệu từng dòng và đưa vào mảng $rowData
				$rowData[$i] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
				$emails[] = [$rowData[$i][0][0], $row];
			}

			$data->$sheet_name = $emails;
		}
		$data_file->data = $data;

		if($fromform->option_2){
			$data_file->userid = $USER->id;
			$data_file->ma_ghi_danh = time();
			$task = new block_th_bulk_enrol_student\task\th_bulk_enrol_student_adhoc_task();
			$task->set_custom_data($data_file);

			\core\task\manager::queue_adhoc_task($task);

			$link = "<a href='view.php'>" . get_string('continue_enrolment', 'block_th_bulk_enrol_student') . "</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>" . get_string('homepage', 'block_th_bulk_enrol_student') . "</a>";

			$wn = get_string('enrolment_success', 'block_th_bulk_enrol_student', ['link' => $link, 'home' => $home]);


			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('bulk_enrolment', 'block_th_bulk_enrol_student'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();

		} else {
			$checkedemails = th_bulkenrol_excel_check_user_mails($data_file);

			//delete all file upload
			$files = glob("$CFG->dataroot/th_bulk_enrol_student_uploads/*"); // get all file names
			foreach ($files as $file) {
				// iterate files
				if (is_file($file)) {
					unlink($file); // delete file
				}
			}

			// Save data in Session.
			$th_bulkenrol_csvkey = $courseid . '_' . time();
			$SESSION->th_bulkenrol_csv[$th_bulkenrol_csvkey] = $checkedemails;
		}	

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		$context = context_system::instance();
		$baseurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
		if ($editcontrols = local_th_bulk_enrol_student_controls($context, $baseurl)) {
		    echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading("<center>$title</center>");
		$th_excel_bulk_enrol_student_form->display();
		echo $OUTPUT->footer();
	}
}

if ($th_bulkenrol_csvkey) {
	$form2 = new confirm_form(null, array('th_bulkenrol_csv_key' => $th_bulkenrol_csvkey));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_bulkenrol_csvkey) && !empty($SESSION->th_bulkenrol_csv) &&
			array_key_exists($th_bulkenrol_csvkey, $SESSION->th_bulkenrol_csv)) {
			set_time_limit(600);
			$data = $SESSION->th_bulkenrol_csv[$th_bulkenrol_csvkey];
			$no_enroleds = $data->moodleusers_for_email;

			foreach ($no_enroleds as $k => $no_enroled) {
				$courseid = $no_enroled->courseid;
				$userid = $no_enroled->id;
				$roleid = 5;
				$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
				$timestart = time();
				$timeend = 0;
				$enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
			}

			$link = "<a href='view.php'>" . get_string('continue_enrolment', 'block_th_bulk_enrol_student') . "</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>" . get_string('homepage', 'block_th_bulk_enrol_student') . "</a>";

			$wn = get_string('enrolment_success', 'block_th_bulk_enrol_student', ['link' => $link, 'home' => $home]);


			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('pluginname', 'block_th_bulk_enrol_student'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	} else {
		echo $OUTPUT->header();
		$context = context_system::instance();
		$baseurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
		if ($editcontrols = local_th_bulk_enrol_student_controls($context, $baseurl)) {
		    echo $OUTPUT->render($editcontrols);
		}
		echo "<h2 class = 'title'><center>$title</center></h2>";
		if (!empty($th_bulkenrol_csvkey) && !empty($checkedemails)) {
			if (!empty($checkedemails->error_messages)) {
				$html_error = th_display_table_error($checkedemails->error_messages);
				echo $html_error;
			}

			if (!empty($checkedemails->moodleusers_for_email)) {
				$no_enroleds = $checkedemails->moodleusers_for_email;
				$html = th_display_table_enrol($no_enroleds);
				
				echo '<h2 class="title"><center>' . get_string('student_enrolment_list', 'block_th_bulk_enrol_student') . '</center></h2>';
				echo $html;
				
				$lang = current_language();
				
				echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
				$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_enrol_table', get_string('bulk_enrolment_report', 'block_th_bulk_enrol_student'), $lang));
			}			
		}

		// Show notification if there aren't any valid email addresses to enrol.
		if (!empty($checkedemails) && isset($checkedemails->validemailfound) &&
			empty($checkedemails->validemailfound)) {
			$a = new stdClass();
			$url = new moodle_url('/blocks/th_bulk_enrol_student/view.php', array('id' => $courseid, 'editlist' => $th_bulkenrol_csvkey));
			$a->url = $url->out();

			$wn = get_string('error_no_valid_email_in_list', 'block_th_bulk_enrol_student', $a);

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		} else {
			echo $form2->display();
		}

		echo $OUTPUT->footer();
	}
}
