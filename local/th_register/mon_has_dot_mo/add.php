<?php

require_once '../../../config.php';
require_once $CFG->libdir.'/adminlib.php';
require_once $CFG->libdir.'/csvlib.class.php';
require_once $CFG->dirroot.'/local/th_register/mon_has_dot_mo/th_upload_form.php';
require_once $CFG->dirroot.'/local/th_register/mon_has_dot_mo/lib.php';
require_once $CFG->dirroot.'/local/th_register/lib.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION;

$th_upload_course_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/mon_has_dot_mo/add.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_course_opening', 'local_th_register'));
$PAGE->set_title(get_string('manage_course_opening', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/mon_has_dot_mo/edit.php');
$PAGE->navbar->add(get_string('manage_course_opening', 'local_th_register'), $editurl);

if (empty($th_upload_course_key)) {
	$th_upload_course = new th_upload_form();
	if ($th_upload_course->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_upload_course->get_data()) {

		$content          = $th_upload_course->get_file_content('list_course');
		$contents         = th_register_parse_course($content);
		$list_courses     = th_register_get_content($contents);
		$id_dot_mo        = $fromform->dot_mo_mon;
		$ten_dot_mo       = ten_dot_mo($id_dot_mo);
		$check_add_course = th_check_add_course($list_courses, $id_dot_mo, $ten_dot_mo);

		$th_upload_course_key                                   = $COURSE->id.'_'.time();
		$SESSION->local_th_upload_course[$th_upload_course_key] = $check_add_course;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		$baseurl = new moodle_url('/local/th_register/mon_has_dot_mo/add.php');
		if ($editcontrols = local_th_mon_has_dot_mo_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading(get_string('upload_courses_to_opening_batch', 'local_th_register'));
		echo "</br>";
		$th_upload_course->display();
		echo $OUTPUT->footer();
	}
}

if ($th_upload_course_key) {
	$form2 = new confirm_upload_form(null, array('th_upload_course_key' => $th_upload_course_key));
	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/local/th_register/mon_has_dot_mo/add.php');
		redirect($courseurl);

	} else if ($formdata = $form2->get_data()) {

		if (!empty($th_upload_course_key) && !empty($SESSION->local_th_upload_course) &&
			array_key_exists($th_upload_course_key, $SESSION->local_th_upload_course)) {

			$data    = $SESSION->local_th_upload_course[$th_upload_course_key];
			$courses = $data->courses;
			$upload  = upload_add_course($courses);

			redirect($CFG->wwwroot.'/local/th_register/mon_has_dot_mo/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
		}

	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('upload_courses_to_opening_batch', 'local_th_register'));
		if (!empty($th_upload_course_key) && !empty($SESSION->local_th_upload_course) &&
			array_key_exists($th_upload_course_key, $SESSION->local_th_upload_course)) {

			$upload_course_data = $SESSION->local_th_upload_course[$th_upload_course_key];

			if (!empty($upload_course_data->error_messages)) {

				$errors = $upload_course_data->error_messages;

				$html1 = th_display_table_error($errors);
				echo $OUTPUT->heading(get_string('hints', 'local_th_register'));
				echo $html1;
			}

			$courses = $upload_course_data->courses;
			$html    = th_display_table_upload_course($courses);

			if (!empty($courses)) {
				echo $OUTPUT->heading(get_string('courses_will_be_uploaded', 'local_th_register'));
				echo $html;
			}
		}

		if (!empty($upload_course_data) && isset($upload_course_data->valid_course_found) &&
			empty($upload_course_data->valid_course_found)) {
			$a      = new stdClass();
			$url    = new moodle_url('/local/th_register/mon_has_dot_mo/add.php');
			$a->url = $url->out();

			$wn = get_string('error_no_valid_course_in_list', 'local_th_register', $a);

			$notification = new \core\output\notification($wn
				,
				\core\output\notification::NOTIFY_WARNING);

			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);

		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();
	}
}
