<?php
require_once $CFG->dirroot . '/config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/user/renderer.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/grader/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

class th_vmc_alert {
	const ONE_DAY = 24 * 60 * 60;
}

//done
function alert_login() {
	print_object("alert_login");
	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_vmc_alert");
	$daysend = (int) $config->dayalertlogin;
	print_object("daysend: " . $daysend);

	$params = array('daythreshold1' => th_vmc_alert::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_alert::ONE_DAY * $daysend);

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
	}

	$sql = "SELECT *,$now-timecreated,$now-timecreated
			from {user}
			where ($now-timecreated)>:daythreshold1 and  ($now-timecreated)<:daythreshold2 and deleted = 0 and lastaccess = 0";

	$records = $DB->get_records_sql($sql, $params);
	print_object("records");
	print_object($records);
	$supportuser = core_user::get_support_user();
	print_object("supportuser");
	print_object($supportuser);
	$url = $CFG->wwwroot;

	foreach ($records as $key => $user) {
		$subject = get_string_manager()->get_string('subject_alertlogin', 'local_th_vmc_alert', null, $user->lang);
		$message = get_string_manager()->get_string('email_alertlogin', 'local_th_vmc_alert', ['userfullname' => fullname($user),'url' => $url], $user->lang);

		print_object($subject);
		print_object($message);

		$record = new \stdClass();
		$record->component = 'local_th_vmc_alert';
		$record->classname = '\local_th_vmc_alert\task\alert_login';
		$record->lastruntime = time();
		// $record->courseid = $courseid;
		$record->customdata = json_encode(array('userid' => (int) ($user->id)));

		$params = [$record->classname, $record->component, $record->customdata];
		$sql = 'classname = ? AND component = ? AND ' .
		$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

		$alerttask = $DB->get_record_select('local_th_vmc_alert', $sql, $params);
		if ($alerttask == "" || $alerttask == null) {
			$send_ok = email_to_user($user, $supportuser, $subject, $message);
			if ($send_ok == true) {
				$DB->insert_record('local_th_vmc_alert', $record);
			}
		} else {
			print_object("Email already sended");
		}
	}
}

//done
function alert_learning() {
	print_object("alert_learning");

	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_vmc_alert");
	$daysend = (int) $config->dayalertlearning;
	print_object("daysend: " . $daysend);

	$params = array('daythreshold1' => th_vmc_alert::ONE_DAY * ($daysend - 1), 'daythreshold2' => th_vmc_alert::ONE_DAY * $daysend);

	foreach ($params as $key => $daythres) {
	}

	$sql = "SELECT ue.id,courseid,ra.userid,ue.timecreated
			from {user_enrolments} ue
			join {enrol} e
			on e.id = ue.enrolid and ($now  - ue.timecreated)>:daythreshold1 and ($now  - ue.timecreated)<=:daythreshold2 and ue.status = 0
			join {role_assignments} ra
			on ue.userid = ra.userid and ra.roleid = 5
			join {context} c
			on c.contextlevel = 50 and c.id = ra.contextid and e.courseid = c.instanceid";

	$records = $DB->get_records_sql($sql, $params);
	$supportuser = core_user::get_support_user();
	$url = $CFG->wwwroot;
	foreach ($records as $key => $record) {

		$studentid = $record->userid;
		$courseid = $record->courseid;

		$courserecord = $DB->get_record('course', array('id' => $courseid));
		$coursefullname = $courserecord->fullname;
		$student = $DB->get_record('user', array('id' => $studentid));

		$subject = get_string_manager()->get_string('subject_alertlearning', 'local_th_vmc_alert', (object)['siteprefix' => $config->siteprefix], $student->lang);
		$message = get_string_manager()->get_string('email_alertlearning', 'local_th_vmc_alert', array('userfullname' => fullname($student), 'coursefullname' =>
			$coursefullname,'url' => $url), $student->lang);

		print_object($subject);
		print_object($message);

		$record = new \stdClass();
		$record->component = 'local_th_vmc_alert';
		$record->classname = '\local_th_vmc_alert\task\alert_learning';
		$record->lastruntime = time();
		$record->courseid = $courseid;
		$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

		$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
		$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
		$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

		$alerttask = $DB->get_record_select('local_th_vmc_alert', $sql, $params);
		if ($alerttask == "" || $alerttask == null) {
			print_object("Email sending");
			$send_ok = email_to_user($student, $supportuser, $subject, $message);
			if ($send_ok) {
				$DB->insert_record('local_th_vmc_alert', $record);
			}
		} else {
			print_object("Email already sended");
		}
	}

}

//done
function alert_accountduedate() {
	print_object("alert_accountduedate");

	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_vmc_alert");

	$daysend = (int) $config->dayalertaccountduedate2;
	print_object("daysend: " . $daysend);

	$params = array('daythreshold1' => th_vmc_alert::ONE_DAY * ($daysend + 1), 'daythreshold2' => th_vmc_alert::ONE_DAY * $daysend);
	$tendayperiod = th_vmc_alert::ONE_DAY * 10;

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
	}

	$sql = "SELECT ue.id, courseid, ra.userid, ue.timecreated, ue.timeend
			from {user_enrolments} ue
			join {enrol} e
			on e.id = ue.enrolid and ue.timeend > 0 and (ue.timeend-ue.timecreated)>=$tendayperiod and (ue.timeend - $now)<=:daythreshold1 and (ue.timeend-$now)>=:daythreshold2 and ue.status = 0
			join {role_assignments} ra
			on ra.userid = ue.userid and ra.roleid = 5
			join {context} c
			on c.contextlevel = 50 and c.id = ra.contextid and e.courseid = c.instanceid
			join {course} on {course}.id = e.courseid and {course}.visible = 1";

	$records = $DB->get_records_sql($sql, $params);
	print_object($records);

	$supportuser = core_user::get_support_user();

	foreach ($records as $key => $record) {

		$studentid = $record->userid;
		$courseid = $record->courseid;

		$courserecord = $DB->get_record('course', array('id' => $courseid));
		$coursefullname = $courserecord->fullname;

		$duetime = $record->timeend;
		$duedate = get_datetime($duetime);
		$dayremain = $daysend;

		$student = $DB->get_record('user', array('id' => $studentid));
		print_object(fullname($student));
		print_object("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		print_object($student->email);
		print_object($courserecord->fullname);

		mtrace(fullname($student));
		mtrace("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		mtrace($student->email);
		mtrace($courserecord->fullname);

		$subject = get_string_manager()->get_string('subject_alertaccountduedate', 'local_th_vmc_alert', (object)['siteprefix' => $config->siteprefix], $student->lang);
		$message = get_string_manager()->get_string('email_alertaccountduedate', 'local_th_vmc_alert', array('userfullname' => fullname($student),
			'coursefullname' => $coursefullname,
			'duedate' => $duedate,
			'dayremain' => $dayremain), $student->lang);

		$record = new \stdClass();
		$record->component = 'local_th_vmc_alert';
		$record->classname = '\local_th_vmc_alert\task\alert_accountduedate';
		$record->lastruntime = time();
		$record->courseid = $courseid;
		$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

		$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
		$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
		$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

		$alerttask = $DB->get_record_select('local_th_vmc_alert', $sql, $params);
		if ($alerttask == "" || $alerttask == null) {
			print_object("Email sending");
			mtrace("Email sending");
			$send_ok = email_to_user($student, $supportuser, $subject, $message);
			if ($send_ok) {
				$DB->insert_record('local_th_vmc_alert', $record);
			}
		} else {
			print_object("Email already sended");
			mtrace("Email already sended");
		}
	}
}

function alert_ketthuckhoahoc() {
	print_object("alert_ketthuckhoahoc");

	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_vmc_alert");

	$daysend = (int) $config->dayalertketthuckhoahoc2;

	$params = array('daythreshold1' => th_vmc_alert::ONE_DAY * ($daysend + 1), 'daythreshold2' => th_vmc_alert::ONE_DAY * $daysend);

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
	}

	$sql = "SELECT ue.id, courseid, ra.userid, ue.timecreated, ue.timeend
			from {user_enrolments} ue
			join {enrol} e
			on e.id = ue.enrolid and ue.timeend > 0 and (ue.timeend - $now)<=:daythreshold1 and (ue.timeend-$now)>=:daythreshold2 and ue.status = 0
			join {role_assignments} ra
			on ra.userid = ue.userid and ra.roleid = 5
			join {context} c
			on c.contextlevel = 50 and c.id = ra.contextid and e.courseid = c.instanceid
			join {course} on {course}.id = e.courseid and {course}.visible = 1";

	$records = $DB->get_records_sql($sql, $params);
	print_object($records);

	$supportuser = core_user::get_support_user();

	foreach ($records as $key => $record) {

		$studentid = $record->userid;
		$courseid = $record->courseid;

		$courserecord = $DB->get_record('course', array('id' => $courseid));
		$coursefullname = $courserecord->fullname;

		$duetime = $record->timeend;
		$duedate = get_datetime($duetime);

		$student = $DB->get_record('user', array('id' => $studentid));
		print_object(fullname($student));
		print_object($student->email);
		print_object("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		print_object($courserecord->fullname);

		mtrace(fullname($student));
		mtrace($student->email);
		mtrace("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		mtrace($courserecord->fullname);

		$subject = get_string_manager()->get_string('subject_alertketthuckhoahoc', 'local_th_vmc_alert', (object)['siteprefix' => $config->siteprefix], $student->lang);
		$message = get_string_manager()->get_string('email_alertketthuckhoahoc', 'local_th_vmc_alert', array('userfullname' => fullname($student),
			'coursefullname' => $coursefullname,
			'duedate' => $duedate), $student->lang);

		$record = new \stdClass();
		$record->component = 'local_th_vmc_alert';
		$record->classname = '\local_th_vmc_alert\task\alert_ketthuckhoahoc';
		$record->lastruntime = time();
		$record->courseid = $courseid;
		$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

		$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
		$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
		$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

		$alerttask = $DB->get_record_select('local_th_vmc_alert', $sql, $params);
		if ($alerttask == "" || $alerttask == null) {
			print_object("Email sending");
			mtrace("Email sending");
			$send_ok = email_to_user($student, $supportuser, $subject, $message);
			if ($send_ok) {
				$DB->insert_record('local_th_vmc_alert', $record);
			}
		} else {
			print_object("Email already sended");
			mtrace("Email already sended");
		}
	}
}

function alert_account_enrol_course() {
	print_object("alert_account_enrol_course");

	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_vmc_alert");

	$daysend = (int) $config->dayalertaccountenrolcourse;

	print_object("daysend: " . $daysend);

	$params = array('daythreshold1' => th_vmc_alert::ONE_DAY * ($daysend + 1), 'daythreshold2' => th_vmc_alert::ONE_DAY * $daysend);

	foreach ($params as $key => $daythres) {
		print_object(get_datetime($now - $daythres));
	}

	$sql = "SELECT rc.* FROM {th_registeredcourses} as rc, {course} as c WHERE ($now - rc.timecreated)<=:daythreshold1 
			AND ($now - rc.timecreated)>=:daythreshold2 AND timeactivated = 0 AND c.id = rc.courseid AND c.visible = 1";


	$records = $DB->get_records_sql($sql, $params);
	
	$supportuser = core_user::get_support_user();

	foreach ($records as $key => $record) {

		$studentid = $record->userid;
		$courseid = $record->courseid;

		$courserecord = $DB->get_record('course', array('id' => $courseid));
		$coursefullname = $courserecord->fullname;

		$student = $DB->get_record('user', array('id' => $studentid));
		print_object(fullname($student));
		print_object("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		print_object($student->email);
		print_object($courserecord->fullname);

		mtrace(fullname($student));
		mtrace("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		mtrace($student->email);
		mtrace($courserecord->fullname);

		$subject = get_string_manager()->get_string('subject_alert_account_enrol_course', 'local_th_vmc_alert', (object)['siteprefix' => $config->siteprefix], $student->lang);
		$message = get_string_manager()->get_string('email_alert_account_enrol_course', 'local_th_vmc_alert', array('userfullname' => fullname($student),
			'coursefullname' => $coursefullname,
			'url' => $CFG->wwwroot), $student->lang);

		$record = new \stdClass();
		$record->component = 'local_th_vmc_alert';
		$record->classname = '\local_th_vmc_alert\task\alert_account_enrol_course';
		$record->lastruntime = time();
		$record->courseid = $courseid;
		$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

		$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
		$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
		$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

		$alerttask = $DB->get_record_select('local_th_vmc_alert', $sql, $params);
		if ($alerttask == "" || $alerttask == null) {
			print_object("Email sending");
			mtrace("Email sending");
			$send_ok = email_to_user($student, $supportuser, $subject, $message);
			if ($send_ok) {
				$DB->insert_record('local_th_vmc_alert', $record);
			}
		} else {
			print_object("Email already sended");
			mtrace("Email already sended");
		}
	}
}

function alert_active_course() {
	print_object("alert_active_course");

	global $CFG, $DB;

	$now = time();
	$config = get_config("local_th_vmc_alert");

	$daysend = (int) $config->dayalertactivecourse;
	print_object("daysend: " . $daysend);

	$params = array('daythreshold1' => th_vmc_alert::ONE_DAY * ($daysend + 1), 'daythreshold2' => th_vmc_alert::ONE_DAY * $daysend);

	$sql = "SELECT DISTINCT(ue.id), u.id as userid, u.firstname, u.lastname, c.fullname, c.id as courseid, ue.timecreated, ue.timeend
			FROM {user} u
			JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.timeend > 0 AND $now <= ue.timeend AND ($now - ue.timestart)>=:daythreshold1
			JOIN {enrol} e ON ue.enrolid = e.id
			JOIN {course} c ON e.courseid = c.id
			JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
			JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.roleid = 5 AND ctx.id = ra.contextid
			WHERE c.visible = 1 AND ue.status = 0 AND u.id NOT IN (SELECT userid FROM {logstore_standard_log} WHERE courseid = c.id AND action LIKE 'viewed' AND realuserid is NULL AND timecreated >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL $daysend DAY)))
			ORDER BY c.fullname, u.lastname, u.firstname
	";

	$records = $DB->get_records_sql($sql, $params);

	$supportuser = core_user::get_support_user();

	foreach ($records as $key => $record) {

		$studentid = $record->userid;
		$courseid = $record->courseid;
		$coursefullname = $record->fullname;

		$student = $DB->get_record('user', array('id' => $studentid));
		print_object(fullname($student));
		print_object("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		print_object($student->email);

		mtrace(fullname($student));
		mtrace("$CFG->wwwroot" . "/user/profile.php?id=$student->id");
		mtrace($student->email);

		$subject = get_string_manager()->get_string('subject_alert_active_course', 'local_th_vmc_alert', (object)['siteprefix' => $config->siteprefix], $student->lang);
		$message = get_string_manager()->get_string('email_alert_active_course', 'local_th_vmc_alert', array('userfullname' => fullname($student),
			'coursefullname' => $coursefullname), $student->lang);

		$record = new \stdClass();
		$record->component = 'local_th_vmc_alert';
		$record->classname = '\local_th_vmc_alert\task\alert_active_course';
		$record->lastruntime = time();
		$record->courseid = $courseid;
		$record->customdata = json_encode(array('studentid' => (int) ($student->id)));

		$params = [$record->classname, $record->component, $record->courseid, $record->customdata];
		$sql = 'classname = ? AND component = ? AND courseid = ? AND ' .
		$DB->sql_compare_text('customdata', \core_text::strlen($record->customdata) + 1) . ' = ?';

		$alerttask = $DB->get_record_select('local_th_vmc_alert', $sql, $params);

		if ($alerttask == "" || $alerttask == null) {
			print_object("Email sending");
			mtrace("Email sending");
			$send_ok = email_to_user($student, $supportuser, $subject, $message);
			if ($send_ok) {
				$DB->insert_record('local_th_vmc_alert', $record);
			}

		} else {
			print_object("Email already sended");
			mtrace("Email already sended");
		}
	}
}