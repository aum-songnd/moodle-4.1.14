<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/csvlib.class.php';
require_once $CFG->dirroot . '/local/th_bulk_send_message/lib.php';
require_once $CFG->dirroot . '/local/thlib/classes/PHPExcel/IOFactory.php';
require_once $CFG->dirroot . '/local/thlib/classes/PHPExcel.php';
require_once 'th_bulk_send_message_form.php';

global $DB, $CFG, $COURSE, $USER;

$th_bulk_send_message_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'th_bulk_send_message', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_bulk_send_message:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_bulk_send_message/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('bulk_message_heading', 'local_th_bulk_send_message'));
$PAGE->set_title(get_string('bulk_message_heading', 'local_th_bulk_send_message'));
$editurl = new moodle_url('/local/th_bulk_send_message/view.php');
$PAGE->settingsnav->add(get_string('bulk_message_heading', 'local_th_bulk_send_message'), $editurl);

if (empty($th_bulk_send_message_key)) {

	$form = new th_bulk_send_message_form();

	if ($form->is_cancelled()) {

		$path = $CFG->dataroot;
		if (!file_exists($path . '/th_bulk_send_message_uploads')) {
			mkdir($path . '/th_bulk_send_message_uploads', 0744);
		}

		//delete all file upload
		$files = glob("$CFG->dataroot/th_bulk_send_message_uploads/*"); // get all file names
		foreach ($files as $file) {
			// iterate files
			if (is_file($file)) {
				unlink($file); // delete file
			}
		}

		$url = new moodle_url('/my');
		redirect($url);

	} else if ($data = $form->get_data()) {

		$filename = $form->get_new_filename('file');

		$path = $CFG->dataroot;
		if (!file_exists($path . '/th_bulk_send_message_uploads')) {
			mkdir($path . '/th_bulk_send_message_uploads', 0744);
		}
		$inputFileName = $CFG->dataroot . '/th_bulk_send_message_uploads/' . $filename;
		$form->save_file('file', $inputFileName, true);

		$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objPHPExcel = $objReader->load($inputFileName);

		$allsheet = $objPHPExcel->getAllSheets();
		$a = $objPHPExcel->getActiveSheet();

		//Dem so luong sheet.
		// $count_sheet = count($allsheet);
		$count_sheet = 1;

		for ($i = 0; $i < $count_sheet; ++$i) {

			$sheet = $objPHPExcel->getSheet($i);
			$highestRow = $sheet->getHighestRow();

			// Lấy tổng số cột của file.
			$highestColumn = $sheet->getHighestColumn();

			//  Thực hiện việc lặp qua từng dòng của file, để lấy thông tin
			for ($row = 2; $row <= $highestRow; $row++) {
				// Lấy dữ liệu từng dòng và đưa vào mảng $rowData

				$rowData[$i] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
				
				if(!empty($rowData[$i][0][0]) && !empty($rowData[$i][0][1]) && !empty($rowData[$i][0][2])){
					$users[] = [$rowData[$i][0], $row];
				} else {
					break;
				}
			}
		}

		$option = $data->option;

		$checkedusers = th_send_message_check_user($users, $option);

		// Save data in Session.
		$th_bulk_send_message_key = $course->id . '_' . time();
		$SESSION->local_th_bulk_send_message[$th_bulk_send_message_key] = $checkedusers;

		//delete all file upload
		$files = glob("$CFG->dataroot/th_bulk_send_message_uploads/*"); // get all file names
		foreach ($files as $file) {
			// iterate files
			if (is_file($file)) {
				unlink($file); // delete file
			}
		}

	} else {
		echo $OUTPUT->header();
		$baseurl = new moodle_url('/local/th_bulk_send_message/view.php');
		if ($editcontrols = local_th_send_message_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading(get_string('bulk_message_heading', 'local_th_bulk_send_message'));
		$form->display();
		echo $OUTPUT->footer();
	}
}

if ($th_bulk_send_message_key) {
	$form2 = new confirm_form(null, array('th_bulk_send_message_key' => $th_bulk_send_message_key));

	if ($form2->is_cancelled()) {

		$path = $CFG->dataroot;
		if (!file_exists($path . '/th_bulk_send_message_uploads')) {
			mkdir($path . '/th_bulk_send_message_uploads', 0744);
		}

		//delete all file upload
		$files = glob("$CFG->dataroot/th_bulk_send_message_uploads/*"); // get all file names
		foreach ($files as $file) {
			// iterate files
			if (is_file($file)) {
				unlink($file); // delete file
			}
		}

		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/local/th_bulk_send_message/view.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_bulk_send_message_key) && !empty($SESSION->local_th_bulk_send_message) &&
			array_key_exists($th_bulk_send_message_key, $SESSION->local_th_bulk_send_message)) {
			
			$data = $SESSION->local_th_bulk_send_message[$th_bulk_send_message_key];

			$user_send_message = $data->user_send_message;

			$list_message = array();

			foreach ($user_send_message as $k => $user_send) {

				$data_log = new stdClass();

				$data_message = array(
					'userfrom' => $USER->id,
					'userto' => $user_send->user->id,
					'subject' => $user_send->chu_de,
					'fullmessage' => $user_send->noi_dung,
					'data_log' => $data_log
				);

				$list_message[] = $data_message;

				// test send message
				// $message = new \core\message\message();
				// $message->courseid = 1;
				// $message->component = 'moodle';
				// $message->name = 'instantmessage';
				// $message->userfrom = $USER->id;
				// $message->userto = $user_send->user->id;
				// $message->subject = $user_send->chu_de;
				// $message->fullmessage = $user_send->noi_dung;
				// $message->fullmessageformat = FORMAT_HTML;
				// $message->fullmessagehtml = $user_send->noi_dung;
				// $message->smallmessage = $user_send->noi_dung;
				// $message->notification = 0;
				// $content = array('*' => array('header' => ' test ', 'footer' => ' test '));
				// $message->set_additional_content('email', $content);
				// $messageid = message_send1($message);
				// $result = email_to_user($user_send->user, $USER, $user_send->chu_de, $user_send->noi_dung);

			}

			$task = new local_th_bulk_send_message\task\send_message_adhoc_task();
			$task->set_custom_data($list_message);

			\core\task\manager::queue_adhoc_task($task);

			$link = "<a href='view.php'>" . get_string('continue_sending', 'local_th_bulk_send_message') . "</a>";
			$link1 = $CFG->wwwroot . '/my';
			$home = "<a href='$link1'>" . get_string('home', 'local_th_bulk_send_message') . "</a>";

			$wn = get_string('message_sent_success', 'local_th_bulk_send_message', ['link' => $link, 'home' => $home]);


			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);

			echo $OUTPUT->header();
			echo $OUTPUT->heading(get_string('bulk_message_heading', 'local_th_bulk_send_message'));
			echo $OUTPUT->render($notification);
			echo $OUTPUT->footer();
		}
	} else {

		echo $OUTPUT->header();
		$baseurl = new moodle_url('/local/th_bulk_send_message/view.php');
		if ($editcontrols = local_th_send_message_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}

		if (!empty($th_bulk_send_message_key) && !empty($checkedusers)) {

			if (!empty($checkedusers->error_messages)) {
				$errors = $checkedusers->error_messages;
				$html1 = th_send_message_display_table_error($errors);
				echo $OUTPUT->heading(get_string('suggestion', 'local_th_bulk_send_message'));
				echo $html1;
			}

			if (!empty($checkedusers->user_send_message)) {

				$user_send_message = $checkedusers->user_send_message;

				$html = th_display_table_send_message($user_send_message);
				echo html_writer::tag(
					'h2', 
					get_string('bulk_message_list', 'local_th_bulk_send_message'), 
					array('class' => 'title text-center')
				);
				
				echo $html;
				$lang = current_language();
				echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
				$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_send_message_table', get_string('bulk_message_list', 'local_th_bulk_send_message'), $lang));
			}
		}

		// Show notification if there aren't any valid email addresses to enrol.
		if (!empty($checkedusers) && isset($checkedusers->validuserfound) &&
			empty($checkedusers->validuserfound)) {
			$a = new stdClass();
			$url = new moodle_url('/local/th_bulk_send_message/view.php');
			$a->url = $url->out();

			$wn = get_string('error_no_valid_user_in_list', 'local_th_bulk_send_message', $a);

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

?>
