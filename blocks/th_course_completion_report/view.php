<?php
require '../../config.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/blocks/th_course_completion_report/th_course_completion_report_form.php';
require_once $CFG->dirroot . '/blocks/th_course_completion_report/locallib.php';
require_once $CFG->dirroot . '/blocks/th_course_completion_report/classes/progress.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse');
}
require_login($COURSE->id);
require_capability('block/th_course_completion_report:view', context_course::instance($COURSE->id));

$title = get_string('pluginname', 'block_th_course_completion_report');
$url = new moodle_url('/blocks/th_course_completion_report/view.php');
$PAGE->set_url($url);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', $title, $lang));
$settingsnode = $PAGE->navbar->add($title, $url);
$settingsnode->make_active();

$mform = new th_course_completion_report_form();

if ($mform->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	redirect($url);
} else if ($fromform = $mform->get_data()) {

	$from_date = $fromform->from_date;
	$to_date = $fromform->to_date + strtotime("+23 hours 59 minutes 59 seconds", 0);
	$option = $fromform->show_option;

	if ($option == 0) {
		$courses = $fromform->courseidarr;

		if (empty($courses)) {
			$courses = th_course_completion_report_get_courseid_category();
			$courses = array_keys($courses);
		}

		if (!empty($courses)) {
			$courses = array_map('trim', $courses);
			[$insql, $inparams] = $DB->get_in_or_equal($courses);

			$sql = "SELECT CONCAT(u.id,'_',b.courseid),u.id as userid,a.timestart,a.timeend,a.roleid,a.status,b.*,c.register
		    from
		    (select distinct ra.userid,e.courseid,timestart,timeend,ue.status,ra.roleid
		    from {user_enrolments} ue
		    join {enrol} e on ue.enrolid = e.id
		    join {role_assignments} ra on ra.userid = ue.userid and ra.roleid = 5
		    where ue.status = 0  AND (
                	(ue.timestart >= $from_date AND ue.timestart <= $to_date) OR
                	(ue.timeend >= $from_date AND ue.timeend <= $to_date)
                	)
		    	) a
		    join
		    (select distinct ra.userid,c.id as courseid,c.*
		    	from {context} ctx
		    	join {course} c on c.id = ctx.instanceid and ctx.contextlevel = 50
		    	join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = 5) b
		    on a.userid = b.userid and a.courseid = b.courseid
		    join
		    {user} u on u.id = a.userid and u.suspended = 0
		    left join
		   	(SELECT distinct tr.userid,tr.courseid,MAX(tr.timecreated) as register
			   	FROM {th_registeredcourses} tr
			   	JOIN {user} us ON tr.userid = us.id
			   	JOIN {course} co ON tr.courseid = co.id
			   	WHERE tr.timeactivated <> 0
			   	GROUP BY tr.userid,tr.courseid) c
			on a.userid = c.userid and a.courseid = c.courseid
		    where b.courseid <> 1 and b.courseid $insql order by b.startdate DESC";

			$data = $DB->get_records_sql($sql, $inparams);
			$users = array();
			$u = array_keys($data);
			foreach ($u as $key => $d) {
				$t = explode('_', $d)[0];
				$users[$t] = $t;
			}
		}

	} else {
		$userid_arr = $fromform->userid;

		if (empty($userid_arr)) {
			$users = th_course_completion_report_get_userid();
		} else {
			$users = array();
			foreach ($userid_arr as $key => $userid) {
				$users[$userid] = $userid;
			}
		}

		if (!empty($users)) {
			$users = array_map('trim', $users);
			[$insql, $inparams] = $DB->get_in_or_equal($users);

			$sql = "SELECT CONCAT(a.userid,'_',b.courseid),a.userid,a.timestart,a.timeend,a.roleid,a.status,b.*,c.register
		    from
		    (select distinct ra.userid,e.courseid,timestart,timeend,ue.status,ra.roleid
		    from {user_enrolments} ue
		    join {enrol} e on ue.enrolid = e.id
		    join {role_assignments} ra on ra.userid = ue.userid and ra.roleid = 5
		    where ue.status = 0  AND (
                	(ue.timestart >= $from_date AND ue.timestart <= $to_date) OR
                	(ue.timeend >= $from_date AND ue.timeend <= $to_date)
                	)
		    	) a
		    join
		    (select distinct ra.userid,c.id as courseid,c.*
		    	from {context} ctx
		    	join {course} c on c.id = ctx.instanceid and ctx.contextlevel = 50
		    	join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = 5) b
		    on a.userid = b.userid and a.courseid = b.courseid
		    join
		    {course} c on c.id = a.courseid and c.visible = 1
		     left join
		   	(SELECT distinct tr.userid,tr.courseid,MAX(tr.timecreated) as register
			   	FROM {th_registeredcourses} tr
			   	JOIN {user} us ON tr.userid = us.id
			   	JOIN {course} co ON tr.courseid = co.id
			   	WHERE tr.timeactivated <> 0
			   	GROUP BY tr.userid,tr.courseid) c
			on a.userid = c.userid and a.courseid = c.courseid
		    where b.courseid <> 1 and b.userid $insql order by b.startdate DESC";

			$data = $DB->get_records_sql($sql, $inparams);

			$courses = th_course_completion_report_get_courseid_category();
			$courses = array_keys($courses);
			foreach ($data as $key => $d) {
				if (!in_array($d->courseid, $courses)) {
					unset($data[$key]);
				}
			}
		}
	}

	if (isset($data)) {

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

		$table = new html_table();
		$rightrows = [];

		$headrows = new html_table_row();
		$cell = new html_table_cell(get_string('course'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('shortname', 'block_th_course_completion_report'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('status'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('role'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('complete', 'block_th_course_completion_report'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('register', 'block_th_course_completion_report'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('timestart', 'block_th_course_completion_report'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;
		$cell = new html_table_cell(get_string('timeend', 'block_th_course_completion_report'));
		$cell->attributes['class'] = 'cell headingcell';
		$cell->header = true;
		$headrows->cells[] = $cell;

		$rightrows[] = $headrows;
		list($leftrows, $rows_ex_left) = get_left_rows($users, $user_arr);

		foreach ($data as $key => $dt) {

			$status = get_string('inactive', 'block_th_course_completion_report');
			if ($dt->status == 0 and ($dt->timeend == 0 or $dt->timeend > time())) {
				$status = get_string('active', 'block_th_course_completion_report');
			}
			$ty_le = th_course_completion_report\progress::get_course_progress_percentage($dt, $dt->userid);
			$register = get_string('nodate', 'block_th_course_completion_report');
			if (!empty($dt->register)) {
				$register = date('d/m/Y', $dt->register);
			}
			$timestart = get_string('nodate', 'block_th_course_completion_report');
			if ($dt->timestart != 0) {
				$timestart = date('d/m/Y', $dt->timestart);
			}
			$timeend = get_string('nolimit', 'block_th_course_completion_report');
			if ($dt->timeend != 0) {
				$timeend = date('d/m/Y', $dt->timeend);
			}

			$row = new html_table_row();
			$fullname = html_writer::link('/course/view.php?id=' . $dt->id, $dt->fullname);
			$cell = new html_table_cell($fullname);
			$row->cells[] = $cell;
			$shortname = html_writer::link('/course/view.php?id=' . $dt->id, $dt->shortname);
			$cell = new html_table_cell($shortname);
			$row->cells[] = $cell;
			$cell = new html_table_cell($status);
			$row->cells[] = $cell;
			$cell = new html_table_cell(get_string('student', 'block_th_course_completion_report'));
			$row->cells[] = $cell;
			$cell = new html_table_cell(round($ty_le));
			$cell->style = 'text-align: center';
			$row->cells[] = $cell;
			$cell = new html_table_cell($register);
			$row->cells[] = $cell;
			$cell = new html_table_cell($timestart);
			$row->cells[] = $cell;
			$cell = new html_table_cell($timeend);
			$row->cells[] = $cell;

			$rightrows[$dt->userid . "_" . $dt->id] = $row;
		}

		$stt = 0;
		foreach ($rightrows as $key => $row) {
			$userid = explode("_", $key);
			$row->cells = array_merge($leftrows[$userid[0]]->cells, $row->cells);
		}

		foreach ($rightrows as $key => $row) {
			if ($stt != 0) {
				$c = new html_table_cell($stt);
				$row->cells[0] = $c;
			}
			$stt++;
			$table->data[] = $row;
		}
		$headrows = array_shift($table->data);
		$table->head = $headrows->cells;
		$table->attributes = array('class' => 'table', 'border' => '1');
		$table->align[0] = 'center';
		$html = html_writer::table($table);
	} else {
		$html = 'Không có dữ liệu';
	}
}
echo $OUTPUT->header($title);
echo $OUTPUT->heading($title);
$mform->display();
if (isset($html)) {
	echo $html;
}
echo $OUTPUT->footer();

?>

<script type="text/javascript">
    $(document).ready(function() {
	    $('input[type=radio][name=show_option]').change(function() {
	        $('#fitem_id_userid .col-form-label').removeAttr('hidden');
	        $('#fitem_id_userid .col-form-label .word-break').removeAttr('hidden');
	        $('#fitem_id_courseidarr .col-form-label').removeAttr('hidden');
	        $('#fitem_id_courseidarr .col-form-label .word-break').removeAttr('hidden');
	    });
	});
</script>
