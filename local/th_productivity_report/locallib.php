<?php

require_once $CFG->dirroot . '/local/th_productivity_report/classes/local_th_productivity_report.php';

/**
 * th_send_productivity_report function gửi báo cáo hằng tuần
 *
 * @return void
 */
function th_send_productivity_report() {

	global $DB, $CFG;

	$config = get_config('local_th_productivity_report');
	$str_email = $config->list_email;
	$th_crm_code = $config->th_crm_code;
	$list_email = explode(',', $str_email);

	$from = date("d-m-Y", strtotime("last Monday this week"));
	$from = strtotime($from);
	// $from = date("d-m-Y", strtotime("Monday this week"));
	// $from = strtotime($from);

	$to = date("d-m-Y", strtotime("last Sunday this week"));
	$to = strtotime($to) + strtotime("+23 hours + 59 minutes + 59 seconds", 0);
	// $to = date("d-m-Y", strtotime("Sunday this week"));
	// $to = strtotime($to);

	$sql = "SELECT u.id,CONCAT(u.firstname,' ',u.lastname) as fullname,u.timecreated,uid.data as crm_code
	        FROM {user} u
	        LEFT JOIN {user_info_data} uid ON uid.userid = u.id and uid.fieldid = (
				Select id from {user_info_field} where shortname = :th_crm_code)
	        WHERE u.timecreated BETWEEN :from AND :to AND u.deleted = 0
	        ORDER BY u.timecreated";
	$params = array(
		'from' => $from,
		'to' => $to,
		'th_crm_code' => $th_crm_code,
	);
	$users = $DB->get_records_sql($sql, $params);
	$totaluser = count($users);

	$sql = "SELECT b.*,a.sl
	FROM
	(SELECT thr.id,thr.courseid, count(thr.id) as sl,u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			where u.deleted = 0 and thr.timecreated BETWEEN :from1 AND :to1
			GROUP BY thr.courseid
			) a
	RIGHT JOIN
	(SELECT thr.id,u.firstname,u.lastname, c.fullname,thr.courseid,thr.timecreated,c.startdate,uid.data as crm_code, u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			LEFT JOIN {user_info_data} uid ON thr.userid = uid.userid and uid.fieldid = (
				Select id from {user_info_field} where shortname = :th_crm_code)
			where u.deleted = 0 and thr.timecreated BETWEEN :from2 AND :to2
			order by c.startdate DESC, thr.timecreated DESC) b
	ON a.courseid = b.courseid AND a.id = b.id AND a.userid = b.userid
	order by b.courseid DESC, b.startdate DESC, b.timecreated";
	$params = array(
		'from1' => $from,
		'from2' => $from,
		'to1' => $to,
		'to2' => $to,
		'th_crm_code' => $th_crm_code,
	);
	$th_registeredcourses = $DB->get_records_sql($sql, $params);
	$totalenrol = count($th_registeredcourses);

	$time = date('d/m/Y', $from) . ' - ' . date('d/m/Y', $to);
	mtrace($time);

	$workbook = new local_th_productivity_report("-");
	$workbook->send('Báo cáo năng suất VMC hàng tuần.xlsx');
	$myxls = $workbook->add_worksheet('Báo cáo');

	$row = 0;
	$myxls->write_string($row++, 2, 'Báo cáo số lượng học viên được tạo tài khoản, gán khóa học trong tuần: ' . $time);
	$myxls->write_string($row++, 0, '1. Số Học viên được gán trong tuần: ' . $totalenrol);
	$head = array(
		'STT',
		'Tên khoá học',
		'Số khoá gán',
		'Họ và tên',
		'Mã AUM',
		'Ngày ghi danh',
		'Vai trò',
		'Thời gian',
	);
	$pos = 1;
	foreach ($head as $h) {
		$myxls->write_string($row, $pos++, $h);
	}

	$stt = 1;
	$row++;
	$myxls->write_string($row, 8, $time);
	foreach ($th_registeredcourses as $thr) {
		if (!$thr->sl) {
			$t = '';
		} else {
			$t = $thr->sl;
		}
		$myxls->write_string($row, 1, $stt++);
		$myxls->write_string($row, 2, $thr->fullname);
		$myxls->write_string($row, 3, $t);
		$myxls->write_string($row, 4, $thr->firstname . ' ' . $thr->lastname);
		$myxls->write_string($row, 5, $thr->crm_code);
		$myxls->write_string($row, 6, date('d/m/Y', $thr->timecreated));
		$myxls->write_string($row, 7, 'Học viên');
		$row++;
	}

	$row++;
	$myxls->write_string($row++, 0, '2. Số tài khoản được tạo trong tuần: ' . $totaluser);
	$head = array(
		'STT',
		'Họ và tên',
		'Mã AUM',
		'Ngày tạo',
		'Thời gian',
		'Tổng số tài khoản',
	);
	$pos = 1;
	foreach ($head as $h) {
		$myxls->write_string($row, $pos++, $h);
	}
	$row++;
	$stt = 1;
	$myxls->write_string($row, 5, $time);
	$myxls->write_string($row, 6, $totaluser);

	foreach ($users as $user) {
		$myxls->write_string($row, 1, $stt++);
		$myxls->write_string($row, 2, $user->fullname);
		$myxls->write_string($row, 3, $user->crm_code);
		$myxls->write_string($row, 4, date('d/m/Y', $user->timecreated));
		$row++;
	}

	//save file
	try {
		$filepath = make_request_directory() . "/Báo cáo năng suất VMC hàng tuần.xlsx";
		$workbook->close1($filepath);

		$from = core_user::get_support_user();
		$subject = '';
		if (isset($CFG->supportname)) {
			$subject = "[" . $CFG->supportname . "] ";
		}
		$a = new stdClass();
		$a->time = $time;
		$a->number_user = $totaluser;
		$a->number_userenrol = $totalenrol;
		$subject .= get_string('subject_email', 'local_th_productivity_report', $a);
		$message = get_string('message_email', 'local_th_productivity_report', $a);

		foreach ($list_email as $key => $email) {

			$user = $DB->get_record('user', array('email' => trim($email), 'deleted' => 0));
			if ($user) {
				$result = email_to_user($user, $from, $subject, $message, '', $filepath, basename($filepath));
				if ($result) {
					mtrace('send: ' . $email . ' ok');
				} else {
					mtrace('send: ' . $email . ' no ok');
				}
			} else {
				mtrace('No email: ' . $email);
			}
		}
	} catch (Exception $e) {
		print_object($e);
		mtrace($e);
		die;
	}
}

/**
 * th_send_month_productivity_report function gửi báo cáo hằng tháng
 *
 * @return void
 */
function th_send_month_productivity_report() {

	global $DB, $CFG;

	$config = get_config('local_th_productivity_report');
	$str_email = $config->list_email;
	$th_crm_code = $config->th_crm_code;
	$list_email = explode(',', $str_email);

	$from = date("d-m-Y", strtotime("first day of last month"));
	$from = strtotime($from);
	// $from = date("d-m-Y", strtotime("first day of this month"));
	// $from = strtotime($from);

	$to = date("d-m-Y", strtotime("last day of last month"));
	$to = strtotime($to) + strtotime("+23 hours + 59 minutes + 59 seconds", 0);
	// $to = date("d-m-Y", strtotime("last day of this month"));
	// $to = strtotime($to) + strtotime("+23 hours + 59 minutes + 59 seconds", 0);

	$sql = "SELECT u.id,CONCAT(u.firstname,' ',u.lastname) as fullname,u.timecreated,uid.data as crm_code
	        FROM {user} u
	        LEFT JOIN {user_info_data} uid ON uid.userid = u.id and uid.fieldid = (
				Select id from {user_info_field} where shortname = :th_crm_code)
	        WHERE u.timecreated BETWEEN :from AND :to AND u.deleted = 0
	        ORDER BY u.timecreated";
	$params = array(
		'from' => $from,
		'to' => $to,
		'th_crm_code' => $th_crm_code,
	);
	$users = $DB->get_records_sql($sql, $params);
	$totaluser = count($users);

	$sql = "SELECT b.*,a.sl
	FROM
	(SELECT thr.id,thr.courseid, count(thr.id) as sl,u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			where u.deleted = 0 and thr.timecreated BETWEEN :from1 AND :to1
			GROUP BY thr.courseid
			) a
	RIGHT JOIN
	(SELECT thr.id,u.firstname,u.lastname, c.fullname,thr.courseid,thr.timecreated,c.startdate,uid.data as crm_code, u.id as userid
			from {th_registeredcourses} thr
			join {user} u on thr.userid = u.id
			join {course} c on thr.courseid = c.id
			LEFT JOIN {user_info_data} uid ON thr.userid = uid.userid and uid.fieldid = (
				Select id from {user_info_field} where shortname = :th_crm_code)
			where u.deleted = 0 and thr.timecreated BETWEEN :from2 AND :to2
			order by c.startdate DESC, thr.timecreated DESC) b
	ON a.courseid = b.courseid AND a.id = b.id and a.userid = b.userid
	order by b.courseid DESC, b.startdate DESC, b.timecreated";

	$params = array(
		'from1' => $from,
		'from2' => $from,
		'to1' => $to,
		'to2' => $to,
		'th_crm_code' => $th_crm_code,
	);
	$th_registeredcourses = $DB->get_records_sql($sql, $params);

	$totalenrol = count($th_registeredcourses);
	$time = date('d/m/Y', $from) . ' - ' . date('d/m/Y', $to);
	mtrace($time);

	$workbook = new local_th_productivity_report("-");
	$workbook->send('Báo cáo năng suất VMC hàng tháng.xlsx');
	$myxls = $workbook->add_worksheet('Báo cáo');

	$row = 0;
	$myxls->write_string($row++, 2, 'Báo cáo số lượng học viên được tạo tài khoản, gán khóa học trong tháng: ' . $time);
	$myxls->write_string($row++, 0, '1. Số Học viên được gán trong tháng: ' . $totalenrol);
	$head = array(
		'STT',
		'Tên khoá học',
		'Số khoá gán',
		'Họ và tên',
		'Mã AUM',
		'Ngày ghi danh',
		'Vai trò',
		'Thời gian',
	);
	$pos = 1;
	foreach ($head as $h) {
		$myxls->write_string($row, $pos++, $h);
	}

	$stt = 1;
	$row++;
	$myxls->write_string($row, 8, $time);
	foreach ($th_registeredcourses as $thr) {
		if (!$thr->sl) {
			$t = '';
		} else {
			$t = $thr->sl;
		}
		$myxls->write_string($row, 1, $stt++);
		$myxls->write_string($row, 2, $thr->fullname);
		$myxls->write_string($row, 3, $t);
		$myxls->write_string($row, 4, $thr->firstname . ' ' . $thr->lastname);
		$myxls->write_string($row, 5, $thr->crm_code);
		$myxls->write_string($row, 6, date('d/m/Y', $thr->timecreated));
		$myxls->write_string($row, 7, 'Học viên');
		$row++;
	}

	$row++;
	$myxls->write_string($row++, 0, '2. Số tài khoản được tạo trong tháng: ' . $totaluser);
	$head = array(
		'STT',
		'Họ và tên',
		'Mã AUM',
		'Ngày tạo',
		'Thời gian',
		'Tổng số tài khoản',
	);
	$pos = 1;
	foreach ($head as $h) {
		$myxls->write_string($row, $pos++, $h);
	}
	$row++;
	$stt = 1;
	$myxls->write_string($row, 5, $time);
	$myxls->write_string($row, 6, $totaluser);

	foreach ($users as $user) {
		$myxls->write_string($row, 1, $stt++);
		$myxls->write_string($row, 2, $user->fullname);
		$myxls->write_string($row, 3, $user->crm_code);
		$myxls->write_string($row, 4, date('d/m/Y', $user->timecreated));
		$row++;
	}

	//save file
	try {
		$filepath = make_request_directory() . "/Báo cáo năng suất VMC hàng tháng.xlsx";
		$workbook->close1($filepath);

		$from = core_user::get_support_user();
		$subject = '';
		if (isset($CFG->supportname)) {
			$subject = "[" . $CFG->supportname . "] ";
		}
		$a = new stdClass();
		$a->time = $time;
		$a->number_user = $totaluser;
		$a->number_userenrol = $totalenrol;
		$subject .= get_string('subject_email_month', 'local_th_productivity_report', $a);
		$message = get_string('message_email', 'local_th_productivity_report', $a);

		foreach ($list_email as $key => $email) {

			$user = $DB->get_record('user', array('email' => trim($email), 'deleted' => 0));
			if ($user) {
				$result = email_to_user($user, $from, $subject, $message, '', $filepath, basename($filepath));
				if ($result) {
					mtrace('send: ' . $email . ' ok');
				} else {
					mtrace('send: ' . $email . ' no ok');
				}
			} else {
				mtrace('No email: ' . $email);
			}
		}
	} catch (Exception $e) {
		print_object($e);
		mtrace($e);
		die;
	}
}
