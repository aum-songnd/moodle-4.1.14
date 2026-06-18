<?php

require_once '../../config.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/th_evstep_productivity_report/th_evstep_productivity_report_form.php';
require_once $CFG->dirroot . '/local/th_evstep_productivity_report/locallib.php';
require_once $CFG->dirroot . '/lib/excellib.class.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'error');
}

require_login($COURSE->id);
require_capability('local/th_evstep_productivity_report:view', context_system::instance());

$title = get_string('pluginname', 'local_th_evstep_productivity_report');
$url = new moodle_url('/local/th_evstep_productivity_report/view.php');
$context = context_system::instance();
$lang = current_language();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', $title, $lang));
$settingsnode = $PAGE->navbar->add($title, $url);
$settingsnode->make_active();

$mform = new th_evstep_productivity_report_form();

if ($mform->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	redirect($url);
} else if ($fromform = $mform->get_data()) {

	$from = $fromform->from;
	$to = $fromform->to + strtotime("+23 hours 59 minutes 59 seconds", 0);

	$config = get_config('local_th_evstep_productivity_report');
	$th_crm_code = $config->th_crm_code;

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

	$sql = "SELECT b.*, a.sl
    FROM
    (SELECT ue.id, e.courseid, count(ue.id) as sl, u.id as userid
        FROM {user_enrolments} ue
        JOIN {user} u ON ue.userid = u.id
        JOIN {enrol} e ON ue.enrolid = e.id
        JOIN {course} c ON e.courseid = c.id
        JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = 50
        JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
        WHERE u.deleted = 0 AND ue.timecreated BETWEEN :from1 AND :to1 AND ra.roleid = 5
        GROUP BY e.courseid
    ) a
    RIGHT JOIN
    (SELECT ue.id, u.firstname, u.lastname, c.fullname, e.courseid, ue.timecreated, c.startdate, uid.data as crm_code, u.id as userid
        FROM {user_enrolments} ue
        JOIN {user} u ON ue.userid = u.id
        JOIN {enrol} e ON ue.enrolid = e.id
        JOIN {course} c ON e.courseid = c.id
        JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = 50
        JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
        LEFT JOIN {user_info_data} uid ON ue.userid = uid.userid AND uid.fieldid = (
            SELECT id FROM {user_info_field} WHERE shortname = :th_crm_code)
        WHERE u.deleted = 0 AND ue.timecreated BETWEEN :from2 AND :to2 AND ra.roleid = 5
        ORDER BY c.startdate DESC, ue.timecreated DESC) b
    ON a.courseid = b.courseid AND a.id = b.id AND a.userid = b.userid
    ORDER BY b.courseid DESC, b.startdate DESC, b.timecreated";

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
	// mtrace($time);

	$workbook = new MoodleExcelWorkbook("-");
	$workbook->send('Báo cáo năng suất EVSTEP.xlsx');
	$myxls = $workbook->add_worksheet('Báo cáo');

	$row = 0;
	$myxls->write_string($row++, 2, 'Báo cáo số lượng học viên được tạo tài khoản, gán khóa học từ: ' . $time);
	$myxls->write_string($row++, 0, '1. Số Học viên được gán: ' . $totalenrol);
	$head = array(
		'STT',
		'Tên khoá học',
		'Số học viên gán',
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
	$myxls->write_string($row++, 0, '2. Số tài khoản được tạo: ' . $totaluser);
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
	$workbook->close();
} else {
	echo $OUTPUT->header($title);
	echo $OUTPUT->heading($title);
	$mform->display();
	if (isset($html)) {
		echo $html;
	}
	echo $OUTPUT->footer();
}