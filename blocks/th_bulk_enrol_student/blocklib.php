<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/enrol/locallib.php';

function th_bulkenrol_csv_parse_emails($emails) {
	if (empty($emails)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $emails);
		$result = array();
		foreach ($rawlines as $rawline) {
			$result[] = trim($rawline);
		}
		return $result;
	}
}

function th_bulkenrol_csv_get_content($contents) {
	$max = count($contents) - 1;
	for ($i = 1; $i < $max; ++$i) {
		$list_new[] = $contents[$i];
	}
	return $list_new;
}

function th_bulkenrol_excel_check_user_mails($data_file) {
	global $DB;

	$checkedemails = new stdClass();
	$checkedemails->error_messages = array();
	$checkedemails->moodleusers_for_email = array();
	$checkedemails->validemailfound = 0;

	$data = $data_file->data;
	$option = $data_file->option;

	$setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');

	if ($option == 0) {
		if (!empty($setting_ma_hoc_vien)) {
			$arr_mahv = explode(',', $setting_ma_hoc_vien);
	
			$ma_hoc_vien1 = $arr_mahv[0];
			$ma_hoc_vien2 = $arr_mahv[1];
		} else {
			$error = get_string('missing_student_id_setting', 'block_th_bulk_enrol_student');
			$checkedemails->error_messages[] = $error;
			return $checkedemails;
		}
	}	
	
	if (!empty($data)) {
		// Process emails from textfield.
		foreach ($data as $shortname_course => $emails) {
			foreach($emails as $email) {
				$emailline = trim($email[0]);
				$line = trim($email[1]);
				$sql = "SELECT id FROM {course} WHERE shortname = '$shortname_course'";
				$course = $DB->get_record_sql($sql);

				if ($course) {
					$courseid = $course->id;
					$roleid = 5;

					if($emailline){
						if($option == 0){
							$userrecord = $DB->get_record_sql("SELECT u.* FROM {user_info_field} as i, {user_info_data} as d, {user} as u WHERE (i.shortname = '$ma_hoc_vien1' OR i.shortname = '$ma_hoc_vien2') AND i.id = d.fieldid AND d.data = '$emailline' AND d.userid = u.id");
						} else {
							$userrecord = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '$emailline'");
						}
						
						if ($userrecord) {
							$userisenrolled = th_bulkenrol_csv_check_email($userrecord->id, $courseid, $roleid);
							if ($userisenrolled) {
								$error = get_string('user_already_enrolled', 'block_th_bulk_enrol_student', [
									'email' => $emailline,
									'shortname' => $shortname_course,
									'line' => $line
								]);
								$checkedemails->error_messages[] = $error;
							} else {
								$checkedemails->validemailfound += 1;
								$userrecord->courseid = $courseid;
								$userrecord->shortname_course = $shortname_course;
						
								if ($option == 0) {
									$userrecord->ma_hv = $emailline;
								}
								$checkedemails->moodleusers_for_email[] = $userrecord;
							}
						} else {
							$error = get_string('user_not_found', 'block_th_bulk_enrol_student', [
								'email' => $emailline,
								'shortname' => $shortname_course,
								'line' => $line
							]);
							$checkedemails->error_messages[] = $error;
						}						
					} else {
						$error = get_string('user_not_found', 'block_th_bulk_enrol_student', [
							'email' => $emailline,
							'shortname' => $shortname_course,
							'line' => $line
						]);
						$checkedemails->error_messages[] = $error;
					}					
					
				} else {
					$error = get_string('course_shortname_not_found', 'block_th_bulk_enrol_student', [
						'shortname' => $shortname_course
					]);
					$checkedemails->error_messages[] = $error;
					break;
				}				
			}
		}
	}
	return $checkedemails;
}

function th_bulkenrol_csv_check_email($userid, $courseid, $roleid) {
	global $DB;

	$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);

	$sql = "SELECT * FROM {role_assignments} as ra, {user_enrolments} as ue, {context} as ctx WHERE ra.userid = $userid AND ra.roleid = $roleid AND ra.contextid = ctx.id AND ctx.instanceid = $courseid AND ctx.contextlevel = 50 AND ra.userid = ue.userid AND ue.enrolid = $instance->id";

	if ($DB->record_exists_sql($sql)) {
		$userisenrolled = 1;
	} else {
		$userisenrolled = 0;
	}
	return $userisenrolled;
}

function th_display_table_enrol($no_enroleds) {
	global $DB;
	$table = new html_table();
	$table->head = array(
		get_string('stt', 'block_th_bulk_enrol_student'),
		get_string('identifier_field', 'block_th_bulk_enrol_student'),
		get_string('Lastname', 'block_th_bulk_enrol_student'),
		get_string('Firstname', 'block_th_bulk_enrol_student'),
		get_string('course', 'block_th_bulk_enrol_student'),
		get_string('Shortname_Course', 'block_th_bulk_enrol_student'),
		get_string('Status', 'block_th_bulk_enrol_student'),
		get_string('note', 'block_th_bulk_enrol_student')
	);
	$stt = 0;

	foreach ($no_enroleds as $k => $no_enroled) {

		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		if(isset($no_enroled->ma_hv)){
			$cell = new html_table_cell($no_enroled->ma_hv);
			$row->cells[] = $cell;
		} else {
			$cell = new html_table_cell($no_enroled->email);
			$row->cells[] = $cell;
		}
		
		$cell = new html_table_cell($no_enroled->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($no_enroled->lastname);
		$row->cells[] = $cell;

		$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$no_enroled->courseid'");
		$link = new moodle_url('/user/index.php', ['id' => $no_enroled->courseid]);
		$link_edit = html_writer::link($link, $course_name);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;

		$cell = new html_table_cell($no_enroled->shortname_course);
		$row->cells[] = $cell;

		$role_name = $DB->get_field_sql("SELECT shortname FROM {role} WHERE id = 5");
		$message = get_string('user_enroled_yes', 'block_th_bulk_enrol_student') . $role_name;
		$cell = new html_table_cell("<strong style = 'color: #fff'>$message</strong>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-success");
		$cell = new html_table_cell('');
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	$table->attributes = array('class' => 'th_enrol_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error($errors) {
	$table1 = new html_table($errors);
	$table1->head = array(get_string('STT', 'block_th_bulk_enrol_student'), get_string('Hints', 'block_th_bulk_enrol_student'));
	$stt = 0;
	foreach ($errors as $k => $error) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($error);
		$row->cells[] = $cell;
		$table1->data[] = $row;
	}
	$html1 = html_writer::table($table1);
	return $html1;
}

function local_th_bulk_enrol_student_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'add';
	$view = new moodle_url('/blocks/th_bulk_enrol_student/view.php');

	if (has_capability('block/th_bulk_enrol_student:view', $context)) {
		$addurl = new moodle_url('/blocks/th_bulk_enrol_student/view.php');
		$tabs[] = new tabobject('add', $addurl, get_string('bulk_enrolment', 'block_th_bulk_enrol_student'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'add';
		}
	}
	
	if (has_capability('block/th_bulk_enrol_student:view', $context)) {
		$logurl = new moodle_url('/blocks/th_bulk_enrol_student/th_log_bulk_enrol_student.php');
		$tabs[] = new tabobject('log', $logurl, get_string('tab_log', 'block_th_bulk_enrol_student'));
		if ($currenturl->get_path() === $logurl->get_path()) {
			$currenttab = 'log';
		}
	}	

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}
