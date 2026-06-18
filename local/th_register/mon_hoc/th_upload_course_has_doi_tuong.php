<?php

require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/csvlib.class.php';
require_once $CFG->dirroot . '/local/th_register/mon_hoc/th_upload_course_form.php';
require_once $CFG->dirroot . '/local/th_register/mon_hoc/locallib.php';
require_once $CFG->dirroot . '/local/th_register/lib.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION;

$id = $COURSE->id;

$th_upload_course_has_doi_tuong_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

$context = context_system::instance();

if (!empty($id)) {
	$course  = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
	$context = context_course::instance($course->id, MUST_EXIST);

	$PAGE->set_context($context);
	$PAGE->set_url('/local/th_register/mon_hoc/th_upload_course.php');
	$PAGE->set_pagelayout('standard');
	$PAGE->set_heading(get_string('manage_courses', 'local_th_register'));
	$PAGE->set_title(get_string('manage_courses', 'local_th_register'));
	$editurl = new moodle_url('/local/th_register/mon_hoc/th_upload_course.php');
	$PAGE->settingsnav->add(get_string('manage_courses', 'local_th_register'), $editurl);

	require_login($course);
}

if (!has_capability('local/th_register:view', $context)) {
	print_error('nopermissions', 'error', '', 'local/th_register:view');
}

if (empty($th_upload_course_has_doi_tuong_key)) {
	$th_upload_course_has_doi_tuong = new th_upload_course_has_doi_tuong_form();
	if ($th_upload_course_has_doi_tuong->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_upload_course_has_doi_tuong->get_data()) {

		$content      = $th_upload_course_has_doi_tuong->get_file_content('list_course');
		$contents     = th_register_parse_course($content);
		$list_courses = th_register_get_content($contents);

		$th_check_add_course_has_doi_tuong = th_check_add_course_has_doi_tuong($list_courses);
		$th_upload_course_has_doi_tuong_key                                   = $id . '_' . time();
		$SESSION->local_th_upload_course[$th_upload_course_has_doi_tuong_key] = $th_check_add_course_has_doi_tuong;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();

		$baseurl = new moodle_url('/local/th_register/mon_hoc/th_upload_course_has_doi_tuong.php');
		if ($editcontrols = local_th_register_course_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading(get_string('upload_doi_tuong', 'local_th_register'));
		echo "</br>";
		$th_upload_course_has_doi_tuong->display();
		echo $OUTPUT->footer();
	}
}

if ($th_upload_course_has_doi_tuong_key) {
	$form2 = new confirm_upload_course_has_doi_tuong_form(null, array('th_upload_course_has_doi_tuong_key' => $th_upload_course_has_doi_tuong_key));
	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/local/th_register/mon_hoc/th_upload_course_has_doi_tuong.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_upload_course_has_doi_tuong_key) && !empty($SESSION->local_th_upload_course) &&
			array_key_exists($th_upload_course_has_doi_tuong_key, $SESSION->local_th_upload_course)) 
		{
			$data = $SESSION->local_th_upload_course[$th_upload_course_has_doi_tuong_key];
			$courses_has_doi_tuong = $data->courses_has_doi_tuong;
			$upload  = upload_course_has_doi_tuong($courses_has_doi_tuong);
			redirect($CFG->wwwroot . '/local/th_register/mon_hoc/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
		}
	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('upload_doi_tuong', 'local_th_register'));
		if (
			!empty($th_upload_course_has_doi_tuong_key) && !empty($SESSION->local_th_upload_course) &&
			array_key_exists($th_upload_course_has_doi_tuong_key, $SESSION->local_th_upload_course)
		) {

			$upload_course_data = $SESSION->local_th_upload_course[$th_upload_course_has_doi_tuong_key];

			if (!empty($upload_course_data->error_messages)) {

				$errors = $upload_course_data->error_messages;

				$html1 = th_display_table_error($errors);
				echo $OUTPUT->heading(get_string('hints', 'local_th_register'));
				echo $html1;
			}

			$courses_has_doi_tuong = $upload_course_data->courses_has_doi_tuong;
			$html    = th_display_table_upload_course_has_doi_tuong($courses_has_doi_tuong);

			if (!empty($courses_has_doi_tuong)) {
				echo $OUTPUT->heading(get_string('objects_will_be_added', 'local_th_register'));
				echo $html;
			}
		}

		if (
			!empty($upload_course_data) && isset($upload_course_data->valid_course_found) &&
			empty($upload_course_data->valid_course_found)
		) {
			$a      = new stdClass();
			$url    = new moodle_url('/local/th_register/mon_hoc/th_upload_course_has_doi_tuong.php');
			$a->url = $url->out();

			$wn = get_string('error_no_valid_course_in_list', 'local_th_register', $a);

			$notification = new \core\output\notification(
				$wn,
				\core\output\notification::NOTIFY_WARNING
			);

			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();
	}
}
