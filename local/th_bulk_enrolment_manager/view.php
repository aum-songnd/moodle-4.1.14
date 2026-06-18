<?php

	use \local_th_bulk_enrolment_manager\libs;
	require_once '../../config.php';
	require_once 'lib.php';
	require_once 'th_bulk_enrolment_manager_form.php';
	require_once($CFG->dirroot.'/enrol/manual/locallib.php');

	global $DB, $OUTPUT, $PAGE, $COURSE, $SITE;

	// Check for all required variables.
	$courseid = $COURSE->id;

	if (!$course = $DB->get_record('course', array('id' => $courseid))) {
		print_error('invalidcourse', 'local_th_bulk_enrolment_manager', $courseid);
	}

	require_login($courseid);
	require_capability('local/th_bulk_enrolment_manager:view', context_course::instance($COURSE->id));

	$title = get_string('enrol', 'local_th_bulk_enrolment_manager');
	$PAGE->set_url('/local/th_bulk_enrolment_manager/view.php');
	$PAGE->set_pagelayout(get_string('standard', 'local_th_bulk_enrolment_manager'));
	$PAGE->set_heading(get_string('th_bulk_enrolment_manager', 'local_th_bulk_enrolment_manager'));
	$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'local_th_bulk_enrolment_manager'));
	$context_sys = context_system::instance();
	$baseurl = new moodle_url('/local/th_bulk_enrolment_manager/view.php');
	$settingsnode = $PAGE->settingsnav->add(get_string('breadcrumb', 'local_th_bulk_enrolment_manager'), $baseurl);
	$settingsnode->make_active();

	if (!$enrol_manual = enrol_get_plugin('manual')) {
		throw new coding_exception('Can not instantiate enrol_manual');
	}

	$th_bulk_enrolment_manager = new th_bulk_enrolment_manager_form();

	if ($th_bulk_enrolment_manager->is_cancelled()) {
		redirect(new moodle_url('/my'));
	} else if ($fromform = $th_bulk_enrolment_manager->get_data()) {
		$libs = new libs();
		$course_ids = $fromform->course_id ?: $libs->get_course_date($fromform->date);
		$user_ids = $fromform->userid;

		if(isset($fromform->role)) {
			$role_ids = array($fromform->role);
		} else {
			$role_ids = array();
		}
		
		$action_type = $fromform->action_type;
	
		$timestart = $fromform->startdate ?? time();
		$timeend = $fromform->enddate ?? 0;

		$hasrole = !empty($role_ids);

		// Tạo bảng phù hợp với từng trường hợp
		$table = new html_table();

		if ($hasrole) {
			$table->head = [
				get_string('STT', 'local_th_bulk_enrolment_manager'),
				get_string('Fullname', 'local_th_bulk_enrolment_manager'),
				get_string('Username', 'local_th_bulk_enrolment_manager'),
				get_string('Coursefullname', 'local_th_bulk_enrolment_manager'),
				get_string('Courseidnumber', 'local_th_bulk_enrolment_manager'),
				get_string('role_col', 'local_th_bulk_enrolment_manager'),
				get_string('Accesslimit', 'local_th_bulk_enrolment_manager'),
				get_string('Status', 'local_th_bulk_enrolment_manager'),
			];
		} else {
			$table->head = [
				get_string('STT', 'local_th_bulk_enrolment_manager'),
				get_string('Fullname', 'local_th_bulk_enrolment_manager'),
				get_string('Username', 'local_th_bulk_enrolment_manager'),
				get_string('Coursefullname', 'local_th_bulk_enrolment_manager'),
				get_string('Courseidnumber', 'local_th_bulk_enrolment_manager'),
				get_string('Status', 'local_th_bulk_enrolment_manager'),
			];
		}
	
		$table->attributes = ['class' => 'th-table', 'border' => '1', 'style' => 'width: 100%; text-align:center;'];
	
		$stt = 0;
		foreach ($course_ids as $courseid) {
			$course_data = $libs->get_course($courseid);
			$coursename = $course_data->fullname;
			$shortname = $course_data->shortname;
			$instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual'], '*', MUST_EXIST);
			$context = $DB->get_record('context', ['instanceid' => $courseid, 'contextlevel' => CONTEXT_COURSE], '*', MUST_EXIST);
		
			foreach ($user_ids as $userid) {
				$user_data = $libs->get_user($userid);
				$fullname = $user_data->fullname;
				$username = $user_data->username;
				$enrolled = $DB->record_exists('user_enrolments', ['userid' => $userid, 'enrolid' => $instance->id]);
				$statuscode = 0;
		
				if (!empty($role_ids) && $action_type === 'enrol') {
					foreach ($role_ids as $roleid) {
						$enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend, ENROL_USER_ACTIVE);
						$statuscode = 1;
						$rolename = $DB->get_field('role', 'shortname', ['id' => $roleid]);
		
						$datestart = date('d/m/Y, H:i', $timestart);
						$dateend = date('d/m/Y, H:i', $timeend);
						$timeinfo = "Từ $datestart<br>Đến $dateend";

						if($timeend == 0) {
							$timeinfo = get_string('timeperiod_start', 'local_th_bulk_enrolment_manager', ['timestart' => $datestart]);
						} else {
							$timeinfo = get_string('timeperiod_start', 'local_th_bulk_enrolment_manager', ['timestart' => $datestart]) . get_string('timeperiod_end', 'local_th_bulk_enrolment_manager', ['timeend' => $dateend]);
						}
		
						$row = new html_table_row([
							new html_table_cell(++$stt),
							new html_table_cell($fullname),
							new html_table_cell($username),
							new html_table_cell($coursename),
							new html_table_cell($shortname),
							new html_table_cell($rolename),
							new html_table_cell($timeinfo),
							new html_table_cell(get_string('user_enrolled_success', 'local_th_bulk_enrolment_manager'))
						]);
						$row->cells[7]->attributes = ['class' => 'bg-success'];
						$table->data[] = $row;
					}
				} else {
					switch ($action_type) {
						case 'unenrol':
							if ($enrolled) {
								$enrol_manual->unenrol_user($instance, $userid);
								$statuscode = 2;
							} else {
								$statuscode = 5;
							}
							break;
						case 'suspend':
							if ($enrolled) {
								// $DB->set_field('user_enrolments', 'status', 1, ['userid' => $userid, 'enrolid' => $instance->id]);
								$enrolplugin = enrol_get_plugin('manual');
								$manualinstance = $DB->get_record('enrol', [
									'courseid' => $courseid,
									'enrol' => 'manual'
								], '*', MUST_EXIST);
							
								$enrolplugin->update_user_enrol($manualinstance, $userid, 1);

								$statuscode = 3;
							} else {
								$statuscode = 5;
							}
							break;
					}
		
					switch ($statuscode) {
						case 2:
							$status = get_string('user_unenrolled_success', 'local_th_bulk_enrolment_manager');
							$class = 'bg-success';
							break;
						case 3:
							$status = get_string('user_suspended_success', 'local_th_bulk_enrolment_manager');
							$class = 'bg-success';
							break;
						case 5:
						default:
							$status = get_string('usernotenrolled', 'local_th_bulk_enrolment_manager');
							$class = 'bg-warning';
							break;
					}
		
					$row = new html_table_row([
						new html_table_cell(++$stt),
						new html_table_cell($fullname),
						new html_table_cell($username),
						new html_table_cell($coursename),
						new html_table_cell($shortname),
						new html_table_cell($status)
					]);
					$row->cells[5]->attributes = ['class' => $class];
					$table->data[] = $row;
				}
			}
		}		
	
		// Output
		echo $OUTPUT->header();
		echo $OUTPUT->heading($title);
		if ($editcontrols = local_th_bulk_enrolment_manager_controls($context_sys, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo "<br>";
		$th_bulk_enrolment_manager->display();
		echo "<br>";

		if($action_type == 'enrol'){
			echo $OUTPUT->heading(get_string('user_enrolled_success', 'local_th_bulk_enrolment_manager'));
		} else if ($action_type == 'unenrol'){
			echo $OUTPUT->heading(get_string('user_unenrolled_success', 'local_th_bulk_enrolment_manager'));
		} else {
			echo $OUTPUT->heading(get_string('user_suspended_success', 'local_th_bulk_enrolment_manager'));
		}
		
		echo "<br>";
		echo html_writer::table($table);
		echo "<br>";
		echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
		$PAGE->requires->js_call_amd('local_thlib/main', 'init', ['.th-table', get_string('th_bulk_enrolment_manager', 'local_th_bulk_enrolment_manager'), current_language()]);
		echo $OUTPUT->footer();
	} else {
		echo $OUTPUT->header();
		if ($editcontrols = local_th_bulk_enrolment_manager_controls($context_sys, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading($title);
		echo "<br>";
		$th_bulk_enrolment_manager->display();
		echo $OUTPUT->footer();
	}
?>
<script type="text/javascript">
    $(document).ready(function() {
    $('input[type=radio][name=show_option]').change(function() {
		$('#fitem_id_course_id .col-form-label').removeAttr('hidden');
		$('#fitem_id_course_id .col-form-label .word-break').removeAttr('hidden');
		$('#fitem_id_date .col-form-label').removeAttr('hidden');
		$('#fitem_id_date .col-form-label .word-break').removeAttr('hidden');
    });
});
</script>

