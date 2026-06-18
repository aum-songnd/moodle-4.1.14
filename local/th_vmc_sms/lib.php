<?php
require_once $CFG->dirroot . '/config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_vmc_sms {
	const ONE_DAY = 24 * 60 * 60;
	const NO_MAIL_SUFFIX = "@no.mail";
	const TEST_PHONE_NUMBER = "0943846035";
}

/**
 * sendSMS function gửi SMS
 *
 * @param [int] $sodienthoai
 * @param [string] $noidung
 * @param [int] $userid
 * @return void thông báo
 */
// function sendSMS($sodienthoai, $noidung, $userid = -1) {

// 	$curl = curl_init();

// 	$URL_Base = "https://api.brandsms.vn/api/SMSBrandname/SendSMS";

// 	$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c24iOiJ2bWN2aWV0bmFtIiwic2lkIjoiMzU2NDcyMGEtY2YyMS00ODdjLWI2ODAtNjJkMmNlN2M4MWIyIiwib2J0IjoiIiwib2JqIjoiIiwibmJmIjoxNjE4Mzg0MTcwLCJleHAiOjE2MTgzODc3NzAsImlhdCI6MTYxODM4NDE3MH0.vhjeYR5f-reWiPZyxnc3q-1KkLC4r4aNlFXFzaxWkqM";

// 	$SMSBrandname = "VMC VIETNAM";

// 	$arr = array(
// 		CURLOPT_URL => "$URL_Base",
// 		CURLOPT_RETURNTRANSFER => true,
// 		CURLOPT_ENCODING => "",
// 		CURLOPT_MAXREDIRS => 10,
// 		CURLOPT_TIMEOUT => 0,
// 		CURLOPT_FOLLOWLOCATION => true,
// 		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
// 		CURLOPT_CUSTOMREQUEST => "POST",
// 		CURLOPT_POSTFIELDS => "{\n \"to\": \"$sodienthoai\",\n \"from\": \"$SMSBrandname\",\n\"message\": \"$noidung\",\n \"scheduled\": \"\",\n \"requestId\": \"\",\n \"useUnicode\": 0,\n\"type\": 1\n}",
// 		CURLOPT_HTTPHEADER => array("Content-Type: application/json", "token:$token"),
// 	);

// 	curl_setopt_array($curl, $arr);

// 	$response = curl_exec($curl);

// 	$data = array('other' => ['response' => $response, 'phone2' => $sodienthoai, 'noidung' => $noidung]);

// 	if ($userid < 1) {
// 		$userid = null;
// 		$context = context_system::instance();
// 	} else {
// 		$context = context_user::instance($userid);
// 		$data['relateduserid'] = $userid;
// 	}
// 	$data['context'] = $context;
// 	$event = \local_th_vmc_sms\event\sms_sent::create($data);
// 	$event->trigger();

// 	curl_close($curl);

// 	return $response;
// }

function sendSMS($sodienthoai, $noidung, $userid = -1) {
	$curl = curl_init();

	// Lấy cấu hình từ settings
	$config = get_config('local_th_vmc_sms');
	$URL_Base = $config->sms_api_url ?? '';
	$token = $config->sms_api_token ?? '';
	$SMSBrandname = $config->sms_brandname ?? '';

	$arr = array(
		CURLOPT_URL => "$URL_Base",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => json_encode([
			"to" => $sodienthoai,
			"from" => $SMSBrandname,
			"message" => $noidung,
			"scheduled" => "",
			"requestId" => "",
			"useUnicode" => 0,
			"type" => 1,
		]),
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"token: $token"
		],
	);

	curl_setopt_array($curl, $arr);
	$response = curl_exec($curl);

	// Ghi log sự kiện gửi SMS
	$data = ['other' => ['response' => $response, 'phone2' => $sodienthoai, 'noidung' => $noidung]];
	$context = $userid > 0 ? context_user::instance($userid) : context_system::instance();
	$data['context'] = $context;
	if ($userid > 0) {
		$data['relateduserid'] = $userid;
	}
	$event = \local_th_vmc_sms\event\sms_sent::create($data);
	$event->trigger();

	curl_close($curl);
	return $response;
}


/**
 * sendSMSThongBaoTaiKhoan function
 *
 * @param [int] $user
 * @param [string] $password
 * @return void
 */
function sendSMSThongBaoTaiKhoan($user, $password) {
	global $DB;
	$phone2 = "";
	$noidung = "";
	$response = "";
	$userid = 0;

	if ($user) {
		$user = $DB->get_record('user', array('id' => $user->id));
		$username = $user->username;
		$phone2 = $user->phone2;
		$noidung = get_string('noidungtaikhoanmoi', 'local_th_vmc_sms', ['username' => $username, 'newpassword' => $password]);
		$userid = $user->id;
		$response = sendSMS($phone2, $noidung, $userid);
	}

}

/**
 * tin_nhan2 function Gửi SMS đối với học viên chưa hoàn thành 50% bài giảng
 *
 * @return void
 */
function tin_nhan2() {
	print_object("tin_nhan2");
	global $DB;
	$now = time();
	$config = get_config("local_th_vmc_sms");
	$daysend = (int) $config->songayguitinnhan2;
	print_object("daysend: " . $daysend);

	$params = array('daythreshold1' => th_vmc_sms::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_sms::ONE_DAY * $daysend);
	print_object("Ngày gán nhận được thông báo: ");
	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
	}

	//lay cac khoa hoc
	$courses = $DB->get_records_sql('SELECT id,fullname, shortname FROM {course} WHERE summaryformat=1 AND visible=1');
	print_object($courses);

	$data = array();

	foreach ($courses as $cid => $course) {
		$params = array('cid' => $cid, 'daythreshold1' => th_vmc_sms::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_sms::ONE_DAY * $daysend);
		//lay hoc vien tromg khoa hoc
		$sql = "SELECT DISTINCT u.id,u.firstname,u.lastname,u.email,u.phone2
				FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra, {context} ct
		 		WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid
		 			AND u.id=ra.userid AND ra.roleid=5 AND ct.contextlevel=50
					AND ct.id=ra.contextid AND ct.instanceid=e.courseid
					AND c.id=:cid AND ($now - ue.timecreated)>:daythreshold1
					AND ($now - ue.timecreated)<=:daythreshold2 AND u.email LIKE '%@no.mail' AND u.phone2<>''";
		$students = $DB->get_records_sql($sql, $params);
		print_object("students");
		print_object($students);
		//lay tong so cac hoat dong cua khoa hoc
		$sum = $DB->count_records('course_completion_criteria', array("course" => $cid));
		foreach ($students as $uid => $student) {
			//lay so cac hoat dong hoan thanh cua moi hoc vien trong khoa hoc
			$complete = $DB->count_records_sql('
					SELECT COUNT(mc.coursemoduleid)
					FROM {course_modules_completion} mc LEFT JOIN {course_modules} m ON mc.coursemoduleid=m.id
					WHERE mc.completionstate=1 AND mc.userid=? AND m.course=?', array($uid, $cid)
			);

			print_object("courseid: $cid");
			print_object("complete");
			print_object($complete);
			print_object("sum");
			print_object($sum);

			//kiem tra xem nhung hoc vien nao chua hoan thanh khoa hoc
			if ($sum > 0 && $complete / $sum < 0.5) {
				$data[] = array("courseid" => $cid, "shortname" => $course->shortname, "userid" => $uid);
				$noi_dung_tin_nhan2 = get_string('noi_dung_tin_nhan2', 'local_th_vmc_sms', ['courseshortname' => $course->shortname]);
				mtrace($noi_dung_tin_nhan2);
				$record = new \stdClass();
				$record->component = 'local_th_vmc_sms';
				$record->classname = '\local_th_vmc_sms\task\tin_nhan2';
				$record->lastruntime = time();
				$record->courseid = $cid;
				$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

				$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
				$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
				$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

				$smstask = $DB->get_record_select('local_th_vmc_sms', $sql, $params);
				if ($smstask == "" || $smstask == null) {
					print_object("SMS sending");

					sendSMS(th_vmc_sms::TEST_PHONE_NUMBER, $noi_dung_tin_nhan2, $uid);
					$send_ok = true;
					if ($send_ok) {
						$DB->insert_record('local_th_vmc_sms', $record);
					}
				} else {
					print_object("SMS already sended");
				}
			}
		}
	}
	print_object("data");
	print_object($data);
}

function tin_nhan3() {
	print_object("tin_nhan3");
	global $DB;
	$now = time();
	$config = get_config("local_th_vmc_sms");
	$daysend = (int) $config->songayguitinnhan3;
	$timecourse = (int) $config->timecourse;
	print_object("daysend: " . $daysend);

	$params = array('daythreshold1' => th_vmc_sms::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_sms::ONE_DAY * $daysend);
	print_object("Ngày gán nhận được thông báo: ");
	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
	}
	//lay cac khoa hoc
	$courses = $DB->get_records_sql('SELECT id,fullname,shortname FROM {course} WHERE summaryformat=1 AND visible=1');
	print_object($courses);

	$data = array();

	foreach ($courses as $cid => $course) {
		//$cinfo = new completion_info($course);
		$params = array('cid' => $cid, 'daythreshold1' => th_vmc_sms::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_sms::ONE_DAY * $daysend);

		//lay hoc vien tromg khoa hoc
		$sql = "SELECT DISTINCT u.id,u.firstname,u.lastname,u.email,u.phone2
				FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra, {context} ct
		 		WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid
		 			AND u.id=ra.userid AND ra.roleid=5 AND ct.contextlevel=50
					AND ct.id=ra.contextid AND ct.instanceid=e.courseid
					AND c.id=:cid AND ($now - ue.timecreated)>:daythreshold1
					AND ($now - ue.timecreated)<=:daythreshold2 AND u.email LIKE '%@no.mail' AND u.phone2<>''";
		$students = $DB->get_records_sql($sql, $params);

		print_object("students");
		print_object($students);
		//lay tong so cac hoat dong cua khoa hoc
		$sum = $DB->count_records('course_completion_criteria', array("course" => $cid));
		foreach ($students as $uid => $student) {
			//lay so cac hoat dong hoan thanh cua moi hoc vien trong khoa hoc
			$complete = $DB->count_records_sql('
					SELECT COUNT(mc.coursemoduleid)
					FROM {course_modules_completion} mc LEFT JOIN {course_modules} m ON mc.coursemoduleid=m.id
					WHERE mc.completionstate=1 AND mc.userid=? AND m.course=?', array($uid, $cid)
			);
			//kiem tra xem nhung hoc vien nao chua hoan thanh khoa hoc
			if ($sum - $complete != 0) {
				$data[] = array("courseid" => $cid, "shortname" => $course->shortname, "userid" => $uid);
				$noi_dung_tin_nhan3 = get_string('noi_dung_tin_nhan3', 'local_th_vmc_sms', ['courseshortname' => $course->shortname, 'day' => $timecourse - $daysend]);
				mtrace($noi_dung_tin_nhan3);
				$record = new \stdClass();
				$record->component = 'local_th_vmc_sms';
				$record->classname = '\local_th_vmc_sms\task\tin_nhan3';
				$record->lastruntime = time();
				$record->courseid = $cid;
				$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

				$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
				$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
				$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

				$smstask = $DB->get_record_select('local_th_vmc_sms', $sql, $params);
				if ($smstask == "" || $smstask == null) {
					print_object("SMS sending");

					sendSMS(th_vmc_sms::TEST_PHONE_NUMBER, $noi_dung_tin_nhan3, $uid);
					$send_ok = true;
					if ($send_ok) {
						$DB->insert_record('local_th_vmc_sms', $record);
					}
				} else {
					print_object("SMS already sended");
				}
			}
		}
	}
	print_object("data");
	print_object($data);
}

function tin_nhan4() {
	print_object("tin_nhan4");
	global $DB;
	$now = time();
	$config = get_config("local_th_vmc_sms");
	$daysend = (int) $config->songayguitinnhan4;

	print_object("Ngày gán nhận được thông báo: ");
	$params = array('daythreshold1' => th_vmc_sms::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_sms::ONE_DAY * $daysend);

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
	}

	$timecourse = (int) $config->timecourse;
	print_object("daysend: " . $daysend);
	//lay cac khoa hoc
	$courses = $DB->get_records_sql('SELECT id,fullname,shortname FROM {course} WHERE summaryformat=1 AND visible=1');
	print_object($courses);

	$data = array();

	foreach ($courses as $cid => $course) {
		//$cinfo = new completion_info($course);
		$params = array('cid' => $cid, 'daythreshold1' => th_vmc_sms::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_sms::ONE_DAY * $daysend);
		//lay hoc vien tromg khoa hoc
		$sql = "SELECT DISTINCT u.id,u.firstname,u.lastname,u.email,u.phone2
				FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra, {context} ct
		 		WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid
		 			AND u.id=ra.userid AND ra.roleid=5 AND ct.contextlevel=50
					AND ct.id=ra.contextid AND ct.instanceid=e.courseid
					AND c.id=:cid AND ($now - ue.timecreated)>:daythreshold1
					AND ($now - ue.timecreated)<=:daythreshold2 AND u.email LIKE '%@no.mail' AND u.phone2<>''";
		$students = $DB->get_records_sql($sql, $params);
		print_object("students");
		print_object($students);
		//lay tong so cac hoat dong cua khoa hoc
		$sum = $DB->count_records('course_completion_criteria', array("course" => $cid));
		foreach ($students as $uid => $student) {
			//lay so cac hoat dong hoan thanh cua moi hoc vien trong khoa hoc
			$complete = $DB->count_records_sql('
					SELECT COUNT(mc.coursemoduleid)
					FROM {course_modules_completion} mc LEFT JOIN {course_modules} m ON mc.coursemoduleid=m.id
					WHERE mc.completionstate=1 AND mc.userid=? AND m.course=?', array($uid, $cid)
			);
			//kiem tra xem nhung hoc vien nao chua hoan thanh khoa hoc
			if ($sum - $complete != 0) {
				$data[] = array("courseid" => $cid, "shortname" => $course->shortname, "userid" => $uid);
				$noi_dung_tin_nhan4 = get_string('noi_dung_tin_nhan4', 'local_th_vmc_sms', ['courseshortname' => $course->shortname, 'day' => $timecourse - $daysend]);
				mtrace($noi_dung_tin_nhan4);
				$record = new \stdClass();
				$record->component = 'local_th_vmc_sms';
				$record->classname = '\local_th_vmc_sms\task\tin_nhan4';
				$record->lastruntime = time();
				$record->courseid = $cid;
				$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

				$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
				$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
				$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

				$smstask = $DB->get_record_select('local_th_vmc_sms', $sql, $params);
				if ($smstask == "" || $smstask == null) {
					print_object("SMS sending");

					sendSMS(th_vmc_sms::TEST_PHONE_NUMBER, $noi_dung_tin_nhan4, $uid);
					$send_ok = true;
					if ($send_ok) {
						$DB->insert_record('local_th_vmc_sms', $record);
					}
				} else {
					print_object("SMS already sended");
				}
			}
		}
	}
	print_object("data");
	print_object($data);
}
