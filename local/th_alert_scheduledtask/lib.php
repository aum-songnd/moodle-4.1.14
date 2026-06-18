<?php
require_once $CFG->dirroot . '/config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

// const th_alert_scheduledtask::ONE_DAY = 24 * 60 * 60;
class th_alert_scheduledtask {
	const ONE_DAY = 24 * 60 * 60;
}

//done
function alert_answer_question() {
	print_object(get_string('alert_answer_question', 'local_th_alert_scheduledtask'));
	mtrace(get_string('alert_answer_question', 'local_th_alert_scheduledtask'));
	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_alert_scheduledtask");
	$stringparams = thlib::get_string_params();

	$daysend = (int) $config->dayalertanswerquestion;
	print_object(get_string('daysend', 'local_th_alert_scheduledtask', $daysend));
	mtrace(get_string('daysend', 'local_th_alert_scheduledtask', $daysend));


	$params = array('daythreshold1' => th_alert_scheduledtask::ONE_DAY * ($daysend), 'daythreshold2' => th_alert_scheduledtask::ONE_DAY * ($daysend + 1));

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
		mtrace(get_datetime($now - $daythres));
	}

	$sql = "SELECT  {course}.id, {course}.fullname
	FROM {course}
	WHERE ($now - {course}.startdate)>:daythreshold1  and ($now - {course}.startdate)<=:daythreshold2";

	$records = $DB->get_records_sql($sql, $params);

	foreach ($records as $key => $course) {
		print_object($course);
		mtrace(get_string('coursefullname', 'local_th_alert_scheduledtask', $course->fullname));
		$courseid = $course->id;

		$context_course = \context_course::instance($courseid);
		$teachers = get_role_users(array(3), $context_course, false, 'ra.id,u.*', null);

		$subject = get_string('subjectalertanswerquestion', 'local_th_alert_scheduledtask');
		$message = get_string('emailanswerquestion', 'local_th_alert_scheduledtask', $stringparams);

		print_object($subject);
		mtrace($subject);
		print_object($message);
		mtrace($message);

		foreach ($teachers as $key => $teacher) {
			$record = new \stdClass();
			$record->component = 'local_th_alert_scheduledtask';
			$record->classname = '\local_th_alert_scheduledtask\task\alert_answer_question';
			$record->lastruntime = time();
			$record->courseid = $courseid;
			$record->customdata = json_encode(array('teacherid' => (int) ($teacher->id)));

			$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
			$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
			$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

			$alerttask = $DB->get_record_select('local_th_alert_scheduledtask', $sql, $params);
			if ($alerttask == "" || $alerttask == null) {
				print_object(get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email));
				mtrace(get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email));
				$send_ok = email_to_user($teacher, null, $subject, $message);
				print_object(get_string('sendok', 'local_th_alert_scheduledtask', $send_ok));
				mtrace(get_string('sendok', 'local_th_alert_scheduledtask', $send_ok));
				if ($send_ok) {
					$DB->insert_record('local_th_alert_scheduledtask', $record);
				}
			} else {
				print_object(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
				mtrace(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
			}
		}
	}
}

//done
function alert_approve_course() {
	print_object(get_string('alert_approve_course', 'local_th_alert_scheduledtask'));
	mtrace(json_encode(get_string('alert_approve_course', 'local_th_alert_scheduledtask')));

	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_alert_scheduledtask");
	$stringparams = thlib::get_string_params();

	$daysend = (int) $config->dayalertapprovecourse;
	print_object(get_string('daysend', 'local_th_alert_scheduledtask', $daysend));
	mtrace(json_encode(get_string('daysend', 'local_th_alert_scheduledtask', $daysend)));

	$params = array('daythreshold1' => th_alert_scheduledtask::ONE_DAY * ($daysend), 'daythreshold2' => th_alert_scheduledtask::ONE_DAY * ($daysend + 1));

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
		mtrace(get_datetime($now - $daythres));
	}

	$sql = "SELECT  {course}.id, {course}.fullname,{course}.startdate
			FROM {course}
			WHERE ($now - {course}.startdate)>:daythreshold1  and ($now - {course}.startdate)<=:daythreshold2";

	$records = $DB->get_records_sql($sql, $params);
	$timeformat = get_string('timedaymonthyear', 'local_th_alert_scheduledtask');

	foreach ($records as $key => $course) {
		print_object($course);
		mtrace(get_string('coursefullname', 'local_th_alert_scheduledtask', $course->fullname));
		$courseid = $course->id;

		$context_course = \context_course::instance($courseid);
		$teachers = get_role_users(array(3), $context_course, false, 'ra.id,u.*', null);

		$startdate = userdate($course->startdate, $timeformat);

		print_object($startdate);

		$stringparams['startdate'] = $startdate;

		$subject = get_string_manager()->get_string('subjectalertapprovecourse', 'local_th_alert_scheduledtask');
		$message = get_string_manager()->get_string('emailapprovecourse', 'local_th_alert_scheduledtask', $stringparams);

		print_object($subject);
		mtrace($subject);
		print_object($message);
		mtrace($message);

		foreach ($teachers as $key => $teacher) {
			$record = new \stdClass();
			$record->component = 'local_th_alert_scheduledtask';
			$record->classname = '\local_th_alert_scheduledtask\task\alert_approve_course';
			$record->lastruntime = time();
			$record->courseid = $courseid;
			$record->customdata = json_encode(array('teacherid' => (int) ($teacher->id)));

			$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
			$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
			$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

			$alerttask = $DB->get_record_select('local_th_alert_scheduledtask', $sql, $params);
			if ($alerttask == "" || $alerttask == null) {
				$emailMessage = get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email);
				print_object($emailMessage);
				mtrace($emailMessage);
				$send_ok = email_to_user($teacher, null, $subject, $message);
				$sendOk = get_string('sendok', 'local_th_alert_scheduledtask', $send_ok);
				print_object($sendOk);
				mtrace($sendOk);
				if ($send_ok) {
					$DB->insert_record('local_th_alert_scheduledtask', $record);
				}
			} else {
				print_object(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
				mtrace(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
			}
		}
	}

}

//done
function alert_chot_bang_diem() {
	$alertMessage = get_string('alert_chot_bang_diem', 'local_th_alert_scheduledtask');
	print_object($alertMessage);
	mtrace($alertMessage);
	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_alert_scheduledtask");
	$stringparams = thlib::get_string_params();

	$daysend = (int) $config->dayalertchotbangdiem;
	$daysendMessage = get_string('daysend', 'local_th_alert_scheduledtask', $daysend);
	print_object($daysendMessage);
	mtrace($daysendMessage);

	$dayincontent = $config->day_in_content6;
	$timeformat = get_string('timedaymonthyear', 'local_th_alert_scheduledtask');

	$params = array('daythreshold1' => th_alert_scheduledtask::ONE_DAY * ($daysend), 'daythreshold2' => th_alert_scheduledtask::ONE_DAY * ($daysend + 1));

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
		mtrace(get_datetime($now - $daythres));
	}

	$sql = "SELECT  {course}.id, {course}.fullname, {course}.startdate
	FROM {course}
	WHERE ($now - {course}.startdate)>:daythreshold1  and ($now - {course}.startdate)<=:daythreshold2";

	$records = $DB->get_records_sql($sql, $params);

	foreach ($records as $key => $course) {
		$startdate = $course->startdate;
		$duedate = userdate($startdate + $dayincontent * th_alert_scheduledtask::ONE_DAY, $timeformat);
		print_object($course);
		mtrace(get_string('coursefullname', 'local_th_alert_scheduledtask', $course->fullname));
		$courseid = $course->id;

		$context_course = \context_course::instance($courseid);
		$teachers = get_role_users(array(3), $context_course, false, 'ra.id,u.*', null);

		$subject = get_string('subjectalertchotbangdiem', 'local_th_alert_scheduledtask');
		$message = get_string('emailchotbangdiem',
			'local_th_alert_scheduledtask',
			array_merge($stringparams, ['duedate' => $duedate]));

		print_object($subject);
		mtrace($subject);
		print_object($message);
		mtrace($message);

		foreach ($teachers as $key => $teacher) {
			$record = new \stdClass();
			$record->component = 'local_th_alert_scheduledtask';
			$record->classname = '\local_th_alert_scheduledtask\task\alert_chot_bang_diem';
			$record->lastruntime = time();
			$record->courseid = $courseid;
			$record->customdata = json_encode(array('teacherid' => (int) ($teacher->id)));

			$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
			$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
			$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

			$alerttask = $DB->get_record_select('local_th_alert_scheduledtask', $sql, $params);
			if ($alerttask == "" || $alerttask == null) {
				print_object(get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email));
				mtrace(get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email));

				$send_ok = email_to_user($teacher, null, $subject, $message);
				if ($send_ok) {
					$DB->insert_record('local_th_alert_scheduledtask', $record);
				}
			} else {
				print_object(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
				mtrace(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
			}
		}
	}
}

//dang lam
function alert_viet_nhanxetdanhgia_hangtuan() {
	$alertMessage = get_string('alert_viet_nhanxetdanhgia_hangtuan', 'local_th_alert_scheduledtask');
	print_object($alertMessage);
	mtrace($alertMessage);
	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_alert_scheduledtask");
	$stringparams = thlib::get_string_params();

	$cf_dayalertnhanxetdanhgiahangtuan = (string) $config->dayalertnhanxetdanhgiahangtuan;
	$daysend_array = array_map('trim', explode(",", $cf_dayalertnhanxetdanhgiahangtuan));
	$daysend_array = array_map('intval', $daysend_array);
	print_object($daysend_array);
	mtrace(json_encode($daysend_array));

	$timeformat = get_string('timedaymonthyear', 'local_th_alert_scheduledtask');
	$dayincontent_array = $config->day_in_content5;

	$dayincontent_array = array_map('trim', explode(",", $dayincontent_array));
	$dayincontent_array = array_map('intval', $dayincontent_array);

	print_object($dayincontent_array);
	mtrace(json_encode($dayincontent_array));

	foreach ($daysend_array as $k => $daysend) {

		$dayincontent = $dayincontent_array[$k];
		$daysendMessage = get_string('daysend', 'local_th_alert_scheduledtask', $daysend);
		print_object($daysendMessage);
		mtrace($daysendMessage);

		$dayincontentMessage = get_string('dayincontent', 'local_th_alert_scheduledtask', $dayincontent);
		print_object($dayincontentMessage);
		mtrace($dayincontentMessage);

		$lanthu = $k + 1;
		print_object($lanthu);
		mtrace($lanthu);
		$params = array('daythreshold1' => th_alert_scheduledtask::ONE_DAY * ($daysend), 'daythreshold2' => th_alert_scheduledtask::ONE_DAY * ($daysend + 1));

		foreach ($params as $key => $daythres) {
			print_object(get_datetime($now - $daythres));
			mtrace(get_datetime($now - $daythres));
		}

		$sql = "SELECT  {course}.id, {course}.fullname, {course}.startdate
			FROM {course}
			WHERE ($now - {course}.startdate)>:daythreshold1  and ($now - {course}.startdate)<=:daythreshold2";

		$records = $DB->get_records_sql($sql, $params);

		foreach ($records as $key => $course) {
			$startdate = $course->startdate;
			$duedate = userdate($startdate + ($dayincontent) * th_alert_scheduledtask::ONE_DAY, $timeformat);

			print_object($course);
			mtrace(get_string('coursefullname', 'local_th_alert_scheduledtask', $course->fullname));
			$courseid = $course->id;

			$context_course = \context_course::instance($courseid);
			$teachers = get_role_users(array(3), $context_course, false, 'ra.id,u.*', null);

			$subject = get_string('subjectalertnhanxetdanhgiahangtuan', 'local_th_alert_scheduledtask');
			$message = get_string('emailnhanxetdanhgiahangtuan',
				'local_th_alert_scheduledtask',
				array_merge($stringparams, ['duedate' => $duedate]));

			print_object($subject);
			mtrace($subject);
			print_object($message);
			mtrace($message);

			foreach ($teachers as $key => $teacher) {
				$record = new \stdClass();
				$record->component = 'local_th_alert_scheduledtask';
				$record->classname = '\local_th_alert_scheduledtask\task\alert_viet_nhanxetdanhgia_hangtuan';
				$record->lastruntime = time();
				$record->courseid = $courseid;
				$record->customdata = json_encode(array('teacherid' => (int) ($teacher->id), 'count' => (int) $lanthu));

				$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
				$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
				$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

				$alerttask = $DB->get_record_select('local_th_alert_scheduledtask', $sql, $params);
				if ($alerttask == "" || $alerttask == null) {
					$emailMessage = get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email);
					print_object($emailMessage);
					mtrace($emailMessage);
					$send_ok = email_to_user($teacher, null, $subject, $message);
					$sendOk = get_string('sendok', 'local_th_alert_scheduledtask', $send_ok);
					print_object($sendOk);
					mtrace($sendOk);
					if ($send_ok) {
						$DB->insert_record('local_th_alert_scheduledtask', $record);
					}
				} else {
					print_object(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
					mtrace(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
				}
			}

		}
	}

}

function alert_post_schedule_online_course() {

}

//done
function alert_write_welcome_to_course_forum() {
	$alertMessage = get_string('alert_write_welcome_to_course_forum', 'local_th_alert');
	print_object($alertMessage);
	mtrace($alertMessage);
	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_alert_scheduledtask");
	$stringparams = thlib::get_string_params();

	$daysend = (int) $config->dayalertwritewelcomtocourseforum;
	$daysendMessage = get_string('daysend', 'local_th_alert', $daysend);
	print_object($daysendMessage);
	mtrace($daysendMessage);

	$params = array('daythreshold1' => th_alert_scheduledtask::ONE_DAY * ($daysend), 'daythreshold2' => th_alert_scheduledtask::ONE_DAY * ($daysend + 1));

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
		mtrace(get_datetime($now - $daythres));
	}

	$sql = "SELECT  {course}.id, {course}.fullname, {course}.startdate
	FROM {course}
	WHERE ($now - {course}.startdate)>:daythreshold1  and ($now - {course}.startdate)<=:daythreshold2";

	$timeformat = get_string('timedaymonthyear', 'local_th_alert_scheduledtask');
	$records = $DB->get_records_sql($sql, $params);
	$dayincontent = (int) $config->day_in_content2;

	foreach ($records as $key => $course) {
		print_object($course);
		mtrace(get_string('coursefullname', 'local_th_alert', $course->fullname));
		$courseid = $course->id;

		$context_course = \context_course::instance($courseid);
		$teachers = get_role_users(array(3), $context_course, false, 'ra.id,u.*', null);

		$startdate = $course->startdate;
		$duedate = userdate($startdate + $dayincontent * th_alert_scheduledtask::ONE_DAY, $timeformat);
		$stringparams['duedate'] = $duedate;

		$subject = get_string('subject_alert_write_welcome_to_courseforum', 'local_th_alert_scheduledtask');
		$message = get_string('emailwritewelcomtocourseforum', 'local_th_alert_scheduledtask', $stringparams);

		print_object($stringparams);

		print_object($subject);
		mtrace($subject);
		print_object($message);
		mtrace($message);

		foreach ($teachers as $key => $teacher) {
			$record = new \stdClass();
			$record->component = 'local_th_alert_scheduledtask';
			$record->classname = '\local_th_alert_scheduledtask\task\alert_write_welcome_to_course_forum';
			$record->lastruntime = time();
			$record->courseid = $courseid;
			$record->customdata = json_encode(array('teacherid' => (int) ($teacher->id)));

			$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
			$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
			$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

			$alerttask = $DB->get_record_select('local_th_alert_scheduledtask', $sql, $params);
			if ($alerttask == "" || $alerttask == null) {
				$emailMessage = get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email);
				print_object($emailMessage);
				mtrace($emailMessage);
				$send_ok = email_to_user($teacher, null, $subject, $message);
				$sendOk = get_string('sendok', 'local_th_alert_scheduledtask', $send_ok);
				print_object($sendOk);
				mtrace($sendOk);
				if ($send_ok) {
					$DB->insert_record('local_th_alert_scheduledtask', $record);
				}
			} else {
				print_object(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
				mtrace(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
			}
		}

	}
}

//dang lam
function alert_cham_diem_bai_tu_luan() {
	$alertMessage = get_string('alert_cham_diem_bai_tu_luan', 'local_th_alert');
	print_object($alertMessage);
	mtrace($alertMessage);
	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_alert_scheduledtask");
	$stringparams = thlib::get_string_params();

	$cf_dayalertchamdiembaituluan = (string) $config->dayalertchamdiembaituluan;
	$daysend_array = array_map('trim', explode(",", $cf_dayalertchamdiembaituluan));
	$daysend_array = array_map('intval', $daysend_array);
	print_object($daysend_array);
	mtrace(json_encode($daysend_array));

	$timeformat = get_string('timedaymonthyear', 'local_th_alert_scheduledtask');
	$dayincontent_array = $config->day_in_content7;

	$dayincontent_array = array_map('trim', explode(",", $dayincontent_array));
	$dayincontent_array = array_map('intval', $dayincontent_array);

	print_object($dayincontent_array);
	mtrace(json_encode($dayincontent_array));

	foreach ($daysend_array as $k => $daysend) {

		$dayincontent = $dayincontent_array[$k];
		$daysendMessage = get_string('daysend', 'local_th_alert_scheduledtask', $daysend);
		print_object($daysendMessage);
		mtrace($daysendMessage);

		$dayLabel = get_string('dayincontent', 'local_th_alert_scheduledtask') . $dayincontent;
		print_object($dayLabel);
		mtrace($dayLabel);

		$lanthu = $k + 1;
		print_object($lanthu);
		mtrace($lanthu);
		$params = array('daythreshold1' => th_alert_scheduledtask::ONE_DAY * ($daysend), 'daythreshold2' => th_alert_scheduledtask::ONE_DAY * ($daysend + 1));

		foreach ($params as $key => $daythres) {
			print_object(get_datetime($now - $daythres));
			mtrace(get_datetime($now - $daythres));
		}

		$sql = "SELECT  {course}.id, {course}.fullname, {course}.startdate
			FROM {course}
			WHERE ($now - {course}.startdate)>:daythreshold1  and ($now - {course}.startdate)<=:daythreshold2";

		$records = $DB->get_records_sql($sql, $params);

		foreach ($records as $key => $course) {
			$startdate = $course->startdate;
			$duedate = userdate($startdate + ($dayincontent) * th_alert_scheduledtask::ONE_DAY, $timeformat);

			print_object($course);
			mtrace(get_string('coursefullname', 'local_th_alert_scheduledtask', $course->fullname));
			$courseid = $course->id;

			$context_course = \context_course::instance($courseid);
			$teachers = get_role_users(array(3), $context_course, false, 'ra.id,u.*', null);

			$subject = get_string('subjectalertchamdiembaituluan', 'local_th_alert_scheduledtask');
			$message = get_string('emailchamdiembaituluan',
				'local_th_alert_scheduledtask',
				array_merge($stringparams, ['duedate' => $duedate]));

			print_object($subject);
			mtrace($subject);
			print_object($message);
			mtrace($message);

			foreach ($teachers as $key => $teacher) {
				$record = new \stdClass();
				$record->component = 'local_th_alert_scheduledtask';
				$record->classname = '\local_th_alert_scheduledtask\task\alert_cham_diem_bai_tu_luan';
				$record->lastruntime = time();
				$record->courseid = $courseid;
				$record->customdata = json_encode(array('teacherid' => (int) ($teacher->id), 'count' => (int) $lanthu));

				$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
				$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
				$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

				$alerttask = $DB->get_record_select('local_th_alert_scheduledtask', $sql, $params);
				if ($alerttask == "" || $alerttask == null) {
					$emailMessage = get_string('emailsendingto', 'local_th_alert_scheduledtask', $teacher->email);
					print_object($emailMessage);
					mtrace($emailMessage);
					$send_ok = email_to_user($teacher, null, $subject, $message);
					$sendOk = get_string('sendok', 'local_th_alert_scheduledtask', $send_ok);
					print_object($sendOk);
					mtrace($sendOk);
					if ($send_ok) {
						$DB->insert_record('local_th_alert_scheduledtask', $record);
					}
				} else {
					print_object(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
					mtrace(get_string('email_already_sent', 'local_th_alert_scheduledtask', $teacher->email));
				}
			}

		}
	}

}