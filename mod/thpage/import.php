<?php

require_once '../../config.php';
require_once 'upload_gvcn_qlht_form.php';
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$thpage_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse');
}

$context = context_system::instance();
require_login($courseid);
require_capability('mod/thpage:viewall', $context);

$pageurl = '/mod/thpage/import.php';
$title = get_string('pluginname', 'mod_thpage');
$PAGE->set_url('/mod/thpage/import.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'mod_thpage'));
$PAGE->set_title(get_string('pluginname', 'mod_thpage'));

$editurl = new moodle_url('/mod/thpage/import.php');
$settingsnode = $PAGE->navbar->add(get_string('pluginname', 'mod_thpage'), $editurl);
$settingsnode->make_active();

if (empty($thpage_key)) {

	$upload_form = new upload_gvcn_qlht_form();

	if ($upload_form->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $upload_form->get_data()) {

		$content = $upload_form->get_file_content('data_file');
		$content_arr = thpage_export_parse($content);
		$list_data = thpage_export_get_content($content_arr);

		$checked = new stdClass();
		$checked->error_messages = array();
		$checked->support_dcct = array();
		$checked->valid_found = 0;

		foreach($list_data as $k => $data) {

			$line = $k + 2;
			$data_arr = explode(',', $data);

			$cohorts = trim($data_arr[0]);
			$cohorts = str_replace(';', ',', $cohorts);
			$fullname = trim($data_arr[1]);
			$phone = trim($data_arr[2]);
			$email = trim($data_arr[3]);
			$role = trim($data_arr[4]);
			$gender = trim($data_arr[5]);

			if (!empty($cohorts) && strlen($fullname) <= 200 && strlen($phone) <= 20 && strlen($email) <= 100 && is_numeric($phone) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($fullname) && !empty($phone) && !empty($email)) {
				if (!empty($role) || $role != 'GVCN' || $role != 'QLHT') {
					if(!empty($gender) || $gender != 'Nam' || $gender != 'Nữ') {

						if (!$DB->record_exists_sql("SELECT * FROM {th_teacher_infor} WHERE email = '$email'")) {
							$checked->valid_found ++;
							$data = new stdClass();
							$data->cohorts = $cohorts;
							$data->fullname = $fullname;
							$data->phone = $phone;
							$data->email = $email;

							if ($role == 'GVCN') {
								$data->role = 1;
							} else {
								$data->role = 0;
							}

							if ($gender == 'Nam') {
								$data->gender = 1;
							} else {
								$data->gender = 0;
							}
							
							$checked->support_dcct[] = $data;
						} else {
							$checked->error_messages[] =  get_string('error_email_exists', 'mod_thpage', ['a' => $email, 'b' => $line]);
						}
					} else {
						$checked->error_messages[] =  get_string('error_invalid_gender', 'mod_thpage', $line);
					}
				} else {
					$checked->error_messages[] = get_string('error_invalid_role', 'mod_thpage', $line);
				}		
			} else {
				$checked->error_messages[] = get_string('error_missing_info', 'mod_thpage', $line);
			}
		}

		// Save data in Session.
		$thpage_key = $courseid . '_' . time();
		$SESSION->thpage[$thpage_key] = $checked;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		$baseurl = new moodle_url('/mod/thpage/import.php');
		if ($editcontrols = mod_thpage_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading(get_string('import', 'mod_thpage'));
		echo "</br>";


		$upload_form->display();
		echo $OUTPUT->footer();
	}
}

if ($thpage_key) {

	$form2 = new confirm_form2(null, array('thpage_key' => $thpage_key));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/mod/thpage/import.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (
			!empty($thpage_key) && !empty($SESSION->thpage) &&
			array_key_exists($thpage_key, $SESSION->thpage)
		) {

			$data = $SESSION->thpage[$thpage_key];

			if (!empty($data->support_dcct)) {
				foreach ($data->support_dcct as $k => $data) {

					$dataobjects = new stdClass();
					$dataobjects->cohorts = $data->cohorts;
					$dataobjects->fullname = $data->fullname;
					$dataobjects->phone = $data->phone;
					$dataobjects->email = $data->email;
					$dataobjects->role = $data->role;
					$dataobjects->gender = $data->gender;

					$email = $data->email;

					if (!$DB->record_exists_sql("SELECT * FROM {th_teacher_infor} WHERE email = '$email'")) {
						$DB->insert_record('th_teacher_infor', $dataobjects, false);
					}
				}
				$returnurl = new moodle_url('/mod/thpage/viewall.php');
				redirect($returnurl, get_string('import_success', 'mod_thpage'), null, \core\output\notification::NOTIFY_SUCCESS);
			}
		}
	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('import', 'mod_thpage'));
		if (
			!empty($thpage_key) && !empty($SESSION->thpage) &&
			array_key_exists($thpage_key, $SESSION->thpage)
		) {

			$data = $SESSION->thpage[$thpage_key];

			if (!empty($data->error_messages)) {

				$errors = $data->error_messages;
				$html1 = thpage_display_table_export_dcct_error($errors);
				echo $OUTPUT->heading(get_string('hints', 'mod_thpage'));
				echo $html1;
			}

			if (!empty($data->support_dcct)) {
				$support_dcct = $data->support_dcct;
				$html = thpage_display_table_import_dcct($support_dcct);
				echo $OUTPUT->heading(get_string('list_add', 'mod_thpage'));
				echo $html;
			}
		}

		if (
			!empty($data) && isset($data->valid_found) &&
			empty($data->valid_found)
		) {

			$url    = new moodle_url('/mod/thpage/import.php');
			$a = new stdClass;
			$a->url = html_writer::link($url, get_string('go_back', 'mod_thpage'));
			$wn = get_string('warning', 'mod_thpage', $a);
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

?>
