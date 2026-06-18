<?php

defined('MOODLE_INTERNAL') || die();
require_once $CFG->dirroot . '/local/th_activity_report_by_email/th_activity_report_by_email.php';
require_once $CFG->dirroot . '/local/th_activity_report_by_email/classes/local_th_activity_report_by_email.php';

/**
 * th_activity_report_by_email function gửi nhật ký hoạt động của 1 tài khoản đến 1 danh sách các email
 *
 * @return void
 */
function th_activity_report_by_email() {

	global $DB, $CFG;

	$config = get_config('local_th_activity_report_by_email');
	$email = trim($config->email);
	mtrace('Email: ' . $email);
	print_object('Email: ' . $email);

	$str_email = $config->list_email;
	$list_email = array_map('trim', explode(",", $str_email));
	$email_list_text = get_string('email_list_report', 'local_th_activity_report_by_email');
	mtrace($email_list_text . ' ' . json_encode($list_email));
	print_object($email_list_text . ' ' . json_encode($list_email));

	if ($user = $DB->get_record('user', array('email' => $email))) {
		$userid_tracking_text = get_string('userid_tracking', 'local_th_activity_report_by_email');
	
		mtrace($userid_tracking_text . ' ' . $user->id);
		print_object($userid_tracking_text . ' ' . $user->id);
		
		$userid = $user->id;
		$username = $user->username;
	
		$from = date("d-m-Y", strtotime("last Monday this week"));
		$from = strtotime($from);	
		// $to = date("d-m-Y", strtotime("last Sunday this week"));
		// $to = strtotime($to) + strtotime("+23 hours + 59 minutes + 59 seconds", 0);

		$to = $from + DAYSECS * 7 - 1;

		mtrace('from: ' . $from . ' ' . date('d/m/Y H:i:s', $from));
		print_object('from: ' . $from . ' ' . date('d/m/Y H:i:s', $from));
		mtrace('to: ' . $to . ' ' . date('d/m/Y H:i:s', $to));
		print_object('to: ' . $to . ' ' . date('d/m/Y H:i:s', $to));

		$fromto = date('d/m/Y', $from) . ' - ' . date('d/m/Y', $to);
		mtrace($fromto);
		print_object($fromto);
		// exit;
		//
		// $from = date("d-m-Y", strtotime("today"));
		// $from = strtotime($from);
		// $to = date("d-m-Y", strtotime("today"));
		// $to = strtotime($to) + strtotime("+23 hours + 59 minutes + 59 seconds", 0);

		$filter = new stdClass();
		$filter->courseid = 1;
		$filter->userid = $userid;
		$filter->modid = '';
		$filter->groupid = 0;
		$filter->edulevel = -1;
		$filter->date = $from;

		mtrace(date('d/m/Y H:i:s', $from));
		print_object($fromto);

		mtrace(date('d/m/Y H:i:s', $to));
		print_object($fromto);
		$filter->logreader = new logstore_standard\log\store(new tool_log\log\manager());
		$filter->orderby = 'timecreated DESC';

		$tablelog = new th_activity_report_by_email('report_log', $filter);
		$tablelog->download = 1;
		$tablelog->query_db(1);

		$workbook = new local_th_activity_report_by_email("-");
		$workbook->send('Nhật kí hoạt động.xlsx');
		$activity_log_title = get_string('activity_log', 'local_th_activity_report_by_email');
		$myxls = $workbook->add_worksheet($activity_log_title);

		$head = array(
			get_string('time', 'local_th_activity_report_by_email'),
			get_string('fullname', 'local_th_activity_report_by_email'),
			get_string('affected_user', 'local_th_activity_report_by_email'),
			get_string('event_context', 'local_th_activity_report_by_email'),
			get_string('component', 'local_th_activity_report_by_email'),
			get_string('event_name', 'local_th_activity_report_by_email'),
			get_string('description', 'local_th_activity_report_by_email'),
			get_string('origin', 'local_th_activity_report_by_email'),
			get_string('ip_address', 'local_th_activity_report_by_email'),
		);		
		$pos = 0;
		foreach ($head as $h) {
			$myxls->write_string(0, $pos++, $h);
		}

		$i = 1;
		foreach ($tablelog->rawdata as $row) {
			$formattedrow = $tablelog->format_row($row);
			$myxls->write_string($i, 0, $formattedrow['time']);
			$myxls->write_string($i, 1, $formattedrow['fullnameuser']);
			$myxls->write_string($i, 2, $formattedrow['relatedfullnameuser']);
			$myxls->write_string($i, 3, $formattedrow['context']);
			$myxls->write_string($i, 4, $formattedrow['component']);
			$myxls->write_string($i, 5, $formattedrow['eventname']);
			$myxls->write_string($i, 6, $formattedrow['description']);
			$myxls->write_string($i, 7, $formattedrow['origin']);
			$myxls->write_string($i, 8, $formattedrow['ip']);
			$i++;
		}

		//save file
		try {
			$filepath = make_request_directory() . "/Nhật kí hoạt động.xlsx";
			$workbook->close1($filepath);
		} catch (Exception $e) {
			print_object($e);
			mtrace($e);
			die;
		}

		$a = new stdClass();
		$a->time = $fromto;
		$a->username = $username;
		// $subject = '';
		// if (isset($CFG->supportname)) {
		// 	$subject = "[" . $CFG->supportname . "] ";
		// }
		$subject = get_string('subject_email', 'local_th_activity_report_by_email', $a);
		$message = get_string('message_email', 'local_th_activity_report_by_email', $a);

		mtrace($subject);
		print_object($subject);
		mtrace($message);
		print_object($message);

		$from = core_user::get_support_user();
		foreach ($list_email as $key => $email) {

			$user = $DB->get_record('user', array('email' => $email));
			if ($user) {
				$result = email_to_user($user, $from, $subject, $message, '', $filepath, basename($filepath));
				if ($result) {
					mtrace(get_string('email_send_success', 'local_th_activity_report_by_email', $email));
				} else {
					mtrace(get_string('email_send_fail', 'local_th_activity_report_by_email', $email));
				}
			} else {
				mtrace(get_string('email_not_found', 'local_th_activity_report_by_email', $email));
			}			
		}
	} else {
		mtrace(get_string('no_account', 'local_th_activity_report_by_email', $email));
		print_object(get_string('no_account', 'local_th_activity_report_by_email', $email));
	}	
}