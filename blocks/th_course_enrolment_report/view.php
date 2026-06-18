<?php

use \block_th_course_enrolment_report\libs;
require_once '../../config.php';
require_once 'th_course_enrolment_report_form.php';
require_once 'lib.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once "{$CFG->libdir}/completionlib.php";

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_course_enrolment_report', $courseid);
}

require_login($courseid);
require_capability('block/th_course_enrolment_report:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_course_enrolment_report/view.php';
$title = get_string('report_title', 'block_th_course_enrolment_report');
$PAGE->set_url('/blocks/th_course_enrolment_report/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_course_enrolment_report', 'block_th_course_enrolment_report'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_course_enrolment_report'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', 'BÁO CÁO GÁN KHÓA HỌC', $lang));

$editurl = new moodle_url('/blocks/th_course_enrolment_report/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_course_enrolment_report'), $editurl);
$settingsnode->make_active();

if (!$enrol_manual = enrol_get_plugin('manual')) {
	throw new coding_exception('Can not instantiate enrol_manual');
}

$th_course_enrolment_report = new th_course_enrolment_report_form();

if ($th_course_enrolment_report->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $th_course_enrolment_report->get_data()) {

	$enddate = $fromform->enddate + (24 * 60 * 60 - 1);
	$startdate = $fromform->startdate;
	$list_courseid = $fromform->course_id;
	$list_user_id = $fromform->userid;
    $show_option = $fromform->show_option;
	$date_now = time();

    $table = new html_table();

	$headrows = new html_table_row();
	$cell = new html_table_cell(get_string('User_Role', 'block_th_course_enrolment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('Course', 'block_th_course_enrolment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('shortname'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('Enrolment_Date', 'block_th_course_enrolment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;

	$cell = new html_table_cell(get_string('Enrolment_Activation_Date', 'block_th_course_enrolment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('Enrolment_Expire_Date', 'block_th_course_enrolment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('Enrolment_Status', 'block_th_course_enrolment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;

	$cell = new html_table_cell(get_string('Completion_Status', 'block_th_course_enrolment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;

	$object_table = new stdClass();
	$object_table->rightrows[] = $headrows;
    $libs = new libs();
    $stt = 0;

    $extra = array_filter(explode(',', $CFG->showuseridentity));
	$userfields = array_values($extra);

	$usernamefield = get_all_user_name_fields();
	$usernamefield = implode(",", $usernamefield);
	$alluserfields = "id," . $usernamefield;

	if (count($userfields) > 0) {
		$alluserfields .= "," . implode(',', $userfields);
	}

	$alluserfields .= "," . "email";

	$user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);
    $list_user = [];

    if($show_option == 0) {
        if(!empty($list_courseid)) {

            foreach ($list_courseid as $course_id) {
                $list_active_course_sql = "SELECT DISTINCT(ue.id), u.id as userid, u.firstname, u.lastname, u.username, c.fullname, c.id as courseid, 
                ue.status, ue.timestart, ue.timeend,c.shortname
                FROM {user} u 
                JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.timecreated >= '$startdate' AND ue.timecreated <= '$enddate'
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {course} c ON e.courseid = c.id 
                JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
                JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.roleid = 5 AND ctx.id = ra.contextid
                -- LEFT JOIN {th_registeredcourses} rc ON rc.userid = u.id AND rc.courseid = c.id AND rc.timeactivated > 0
                WHERE c.visible = 1 AND ue.status = 0 AND u.id AND c.id = $course_id AND u.suspended = 0 AND u.deleted = 0
                ORDER BY c.fullname, u.lastname, u.firstname";

                $list_course_no_active_sql = "select rc.*, c.fullname, u.firstname, u.lastname, u.username, c.shortname from {th_registeredcourses} as rc, {course} as c, 
                    {user} as u where timeactivated = 0 and rc.courseid = $course_id and rc.courseid = c.id and rc.userid = u.id and rc.timecreated >= $startdate 
                    and rc.timecreated <= $enddate AND u.suspended = 0 AND u.deleted = 0";
                $list_active_course = $DB->get_records_sql($list_active_course_sql);
                $list_course_no_active = $DB->get_records_sql($list_course_no_active_sql);

                foreach($list_active_course as $k => $active_course){

                    $course_name = $active_course->fullname;
                    $shortname = $active_course->shortname;
    
                    $courseid = $active_course->courseid;
                    $userid = $active_course->userid;
    
                    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");
    
                    $info = new completion_info($course);
                    $coursecomplete = $info->is_course_complete($userid);
    
                    if ($coursecomplete) {
                        $Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
                    } else {
                        $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
                    }
    
                    $sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
                        WHERE r.userid = $userid AND r.contextid = c.id
                        AND c.instanceid = $courseid AND c.contextlevel = 50;";
                    $roles = $DB->get_records_sql($sql);
    
                    $user_role = '';
    
                    foreach ($roles as $k => $role) {
                        $str = $libs->get_role_name($role->roleid);
                        $user_role .= ' ' . $str;
                    }
    
                    // $timecreated = $active_course->timecreated;
                    $sql = "SELECT timecreated FROM {th_registeredcourses} where courseid = $courseid and userid = $userid and timeactivated <> 0 ORDER BY timecreated DESC LIMIT 1";
                    $timecreated = $DB->get_field_sql($sql);
                    $time_start = $active_course->timestart;
                    $time_end = $active_course->timeend;

                    $stt++;
                    th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                        $user_role, $timecreated, $time_start, $time_end);

                    if (!in_array($userid, $list_user)) {
                        $list_user[$userid] = $userid;
                    }
                }

                foreach($list_course_no_active as $k => $course_no_active) {

                    $course_name = $course_no_active->fullname;
                    $shortname = $course_no_active->shortname;

                    $courseid = $course_no_active->courseid;
                    $userid = $course_no_active->userid;

                    $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');

                    $user_role = 'student';

                    $timecreated = $course_no_active->timecreated;
                    $time_start = 0;
                    $time_end = -1;

                    $stt++;
                    th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                        $user_role, $timecreated, $time_start, $time_end);

                    if (!in_array($userid, $list_user)) {
                        $list_user[$userid] = $userid;
                    }
                }
            }
            
        } else {
            $list_active_course_sql = "SELECT DISTINCT(ue.id), u.id as userid, u.firstname, u.lastname, u.username, c.fullname, c.id as courseid, 
            ue.status, ue.timestart, ue.timeend, c.shortname
			FROM {user} u 
			JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.timecreated >= '$startdate' AND ue.timecreated <= '$enddate'
			JOIN {enrol} e ON ue.enrolid = e.id
			JOIN {course} c ON e.courseid = c.id 
			JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
			JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.roleid = 5 AND ctx.id = ra.contextid
            -- LEFT JOIN {th_registeredcourses} rc ON rc.userid = u.id AND rc.courseid = c.id AND rc.timeactivated > 0
			WHERE c.visible = 1 AND ue.status = 0 AND u.id AND u.suspended = 0 AND u.deleted = 0
			ORDER BY c.fullname, u.lastname, u.firstname";

            $list_course_no_active_sql = "select rc.*, c.fullname, u.firstname, u.lastname, u.username, c.shortname from {th_registeredcourses} as rc, {course} as c, 
                {user} as u where timeactivated = 0 and rc.courseid = c.id and rc.userid = u.id and rc.timecreated >= $startdate 
                and rc.timecreated <= $enddate AND u.suspended = 0 AND u.deleted = 0";
            
            $list_active_course = $DB->get_records_sql($list_active_course_sql);
            $list_course_no_active = $DB->get_records_sql($list_course_no_active_sql);

            foreach($list_active_course as $k => $active_course){

                $course_name = $active_course->fullname;
                $shortname = $active_course->shortname;

                $courseid = $active_course->courseid;
                $userid = $active_course->userid;

                $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");

                $info = new completion_info($course);
                $coursecomplete = $info->is_course_complete($userid);

                if ($coursecomplete) {
                    $Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
                } else {
                    $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
                }
                $sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
                    WHERE r.userid = $userid AND r.contextid = c.id
                    AND c.instanceid = $courseid AND c.contextlevel = 50;";
                $roles = $DB->get_records_sql($sql);

                $user_role = '';

                foreach ($roles as $k => $role) {
                    $str = $libs->get_role_name($role->roleid);
                    $user_role .= ' ' . $str;
                }

                // $timecreated = $active_course->timecreated;
                $sql = "SELECT timecreated FROM {th_registeredcourses} where courseid = $courseid and userid = $userid and timeactivated <> 0 ORDER BY timecreated DESC LIMIT 1";
                $timecreated = $DB->get_field_sql($sql);
                $time_start = $active_course->timestart;
                $time_end = $active_course->timeend;
                $stt++;
                th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                    $user_role, $timecreated, $time_start, $time_end);
                
                if (!in_array($userid, $list_user)) {
                    $list_user[$userid] = $userid;
                }
            }

            foreach($list_course_no_active as $k => $course_no_active) {
                if (!in_array($userid, $list_user)) {
                    $list_user[$userid] = $userid;
                }
                
                $course_name = $course_no_active->fullname;
                $shortname = $course_no_active->shortname;

                $courseid = $course_no_active->courseid;
                $userid = $course_no_active->userid;

                $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');

                $user_role = 'student';

                $timecreated = $course_no_active->timecreated;
                $time_start = 0;
                $time_end = -1;

                $stt++;
                th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                    $user_role, $timecreated, $time_start, $time_end);
                
                if (!in_array($userid, $list_user)) {
                    $list_user[$userid] = $userid;
                }
            }
        }
    }

    if($show_option == 1) {
        if(!empty($list_user_id)) {

            foreach ($list_user_id as $user_id) {
                $list_active_course_sql = "SELECT DISTINCT(ue.id), u.id as userid, u.firstname, u.lastname, u.username, c.fullname, c.id as courseid, 
                ue.status, ue.timestart, ue.timeend, c.shortname
                FROM {user} u 
                JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.timecreated >= '$startdate' AND ue.timecreated <= '$enddate'
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {course} c ON e.courseid = c.id 
                JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
                JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.roleid = 5 AND ctx.id = ra.contextid
                -- LEFT JOIN {th_registeredcourses} rc ON rc.userid = u.id AND rc.courseid = c.id AND rc.timeactivated > 0
                WHERE c.visible = 1 AND ue.status = 0 AND u.id = $user_id AND u.suspended = 0 AND u.deleted = 0
                ORDER BY c.fullname, u.lastname, u.firstname";

                $list_course_no_active_sql = "select rc.*, c.fullname, u.firstname, u.lastname, u.username, c.shortname from {th_registeredcourses} as rc, {course} as c, 
                    {user} as u where timeactivated = 0 and rc.userid = $user_id and rc.courseid = c.id and rc.userid = u.id and rc.timecreated >= $startdate 
                    and rc.timecreated <= $enddate AND u.suspended = 0 AND u.deleted = 0";

                $list_active_course = $DB->get_records_sql($list_active_course_sql);
                $list_course_no_active = $DB->get_records_sql($list_course_no_active_sql);

                foreach($list_active_course as $k => $active_course){

                    $course_name = $active_course->fullname;
                    $shortname = $active_course->shortname;
    
                    $courseid = $active_course->courseid;
                    $userid = $active_course->userid;
    
                    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");
    
                    $info = new completion_info($course);
                    $coursecomplete = $info->is_course_complete($userid);
    
                    if ($coursecomplete) {
                        $Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
                    } else {
                        $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
                    }
    
                    $sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
                        WHERE r.userid = $userid AND r.contextid = c.id
                        AND c.instanceid = $courseid AND c.contextlevel = 50;";
                    $roles = $DB->get_records_sql($sql);
    
                    $user_role = '';
    
                    foreach ($roles as $k => $role) {
                        $str = $libs->get_role_name($role->roleid);
                        $user_role .= ' ' . $str;
                    }
    
                    // $timecreated = $active_course->timecreated;
                    $sql = "SELECT timecreated FROM {th_registeredcourses} where courseid = $courseid and userid = $userid and timeactivated <> 0 ORDER BY timecreated DESC LIMIT 1";
                    $timecreated = $DB->get_field_sql($sql);
                    $time_start = $active_course->timestart;
                    $time_end = $active_course->timeend;
                    $stt++;
                    th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                        $user_role, $timecreated, $time_start, $time_end);

                    if (!in_array($userid, $list_user)) {
                        $list_user[$userid] = $userid;
                    }
                }

                foreach($list_course_no_active as $k => $course_no_active) {

                    $course_name = $course_no_active->fullname;
                    $shortname = $course_no_active->shortname;

                    $courseid = $course_no_active->courseid;
                    $userid = $course_no_active->userid;

                    $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');

                    $user_role = 'student';

                    $timecreated = $course_no_active->timecreated;
                    $time_start = 0;
                    $time_end = -1;

                    $stt++;
                    th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                        $user_role, $timecreated, $time_start, $time_end);

                    if (!in_array($userid, $list_user)) {
                        $list_user[$userid] = $userid;
                    }
                }
            }
            
        } else {
            $list_active_course_sql = "SELECT DISTINCT(ue.id), u.id as userid, u.firstname, u.lastname, u.username, c.fullname, c.id as courseid, 
            ue.status, ue.timestart, ue.timeend, c.shortname
			FROM {user} u 
			JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.timecreated >= '$startdate' AND ue.timecreated <= '$enddate'
			JOIN {enrol} e ON ue.enrolid = e.id
			JOIN {course} c ON e.courseid = c.id 
			JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
			JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.roleid = 5 AND ctx.id = ra.contextid
            -- LEFT JOIN {th_registeredcourses} rc ON rc.userid = u.id AND rc.courseid = c.id AND rc.timeactivated > 0
			WHERE c.visible = 1 AND ue.status = 0 AND u.id AND u.suspended = 0 AND u.deleted = 0
			ORDER BY c.fullname, u.lastname, u.firstname";

            $list_course_no_active_sql = "select rc.*, c.fullname, u.firstname, u.lastname, u.username, c.shortname from {th_registeredcourses} as rc, {course} as c, 
                {user} as u where timeactivated = 0 and rc.courseid = c.id and rc.userid = u.id and rc.timecreated >= $startdate 
                and rc.timecreated <= $enddate AND u.suspended = 0 AND u.deleted = 0";
            
            $list_active_course = $DB->get_records_sql($list_active_course_sql);
            $list_course_no_active = $DB->get_records_sql($list_course_no_active_sql);

            foreach($list_active_course as $k => $active_course){

                $course_name = $active_course->fullname;
                $shortname = $active_course->shortname;

                $courseid = $active_course->courseid;
                $userid = $active_course->userid;

                $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $courseid");

                $info = new completion_info($course);
                $coursecomplete = $info->is_course_complete($userid);

                if ($coursecomplete) {
                    $Completion_Status = get_string('completion', 'block_th_course_enrolment_report');
                } else {
                    $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');
                }

                $sql = "SELECT r.roleid FROM {role_assignments} as r, {context} as c
                    WHERE r.userid = $userid AND r.contextid = c.id
                    AND c.instanceid = $courseid AND c.contextlevel = 50;";
                $roles = $DB->get_records_sql($sql);

                $user_role = '';

                foreach ($roles as $k => $role) {
                    $str = $libs->get_role_name($role->roleid);
                    $user_role .= ' ' . $str;
                }

                // $timecreated = $active_course->timecreated;
                $sql = "SELECT timecreated FROM {th_registeredcourses} where courseid = $courseid and userid = $userid and timeactivated <> 0 ORDER BY timecreated DESC LIMIT 1";
                $timecreated = $DB->get_field_sql($sql);
                $time_start = $active_course->timestart;
                $time_end = $active_course->timeend;
                $stt++;
                th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                    $user_role, $timecreated, $time_start, $time_end);
                
                if (!in_array($userid, $list_user)) {
                    $list_user[$userid] = $userid;
                }
            }

            foreach($list_course_no_active as $k => $course_no_active) {
                if (!in_array($userid, $list_user)) {
                    $list_user[$userid] = $userid;
                }
                
                $course_name = $course_no_active->fullname;
                $shortname = $course_no_active->shortname;

                $courseid = $course_no_active->courseid;
                $userid = $course_no_active->userid;

                $Completion_Status = get_string('no_completion', 'block_th_course_enrolment_report');

                $user_role = 'student';

                $timecreated = $course_no_active->timecreated;
                $time_start = 0;
                $time_end = -1;

                $stt++;
                th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status,
                    $user_role, $timecreated, $time_start, $time_end);
                
                if (!in_array($userid, $list_user)) {
                    $list_user[$userid] = $userid;
                }
            }
        }
    }

    $rightrows = $object_table->rightrows;	

	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);
	$th_course_enrolment_report->display();

	if ($stt !== 0) {

		list($leftrows, $rows_ex_left) = get_left_rows($list_user, $user_arr);

		$stt_new = 0;
		foreach ($rightrows as $key => $row) {
			$userid = explode("_", $key);
			$row->cells = array_merge($leftrows[$userid[0]]->cells, $row->cells);
		}

		foreach ($rightrows as $key => $row) {
			if ($stt_new != 0) {
				$c = new html_table_cell($stt_new);
				$row->cells[0] = $c;
			}
			$stt_new++;
			$table->data[] = $row;
		}
		$headrows = array_shift($table->data);
		$table->head = $headrows->cells;
		$table->attributes = array('class' => 'table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";
		$html = html_writer::table($table);
		echo $html;
	} else {
		echo $OUTPUT->heading(get_string('errorprocess', 'block_th_course_enrolment_report'));
	}

	echo $OUTPUT->footer();

} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);
	$th_course_enrolment_report->display();
	echo $OUTPUT->footer();
}
?>

<script type="text/javascript">
    $(document).ready(function() {
	    $('input[type=radio][name=show_option]').change(function() {
	        $('#fitem_id_userid .col-form-label').removeAttr('hidden');
	        $('#fitem_id_userid .col-form-label .word-break').removeAttr('hidden');
	        $('#fitem_id_course_id .col-form-label').removeAttr('hidden');
	        $('#fitem_id_course_id .col-form-label .word-break').removeAttr('hidden');
	    });
	});
</script>


