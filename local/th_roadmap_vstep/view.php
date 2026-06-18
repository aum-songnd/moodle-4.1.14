<?php

require_once '../../config.php';
require_once 'lib.php';
require_once 'th_roadmap_vstep_form.php';
require_once 'confirm_form.php';
require_once $CFG->libdir . '/adminlib.php';
require_once($CFG->libdir.'/csvlib.class.php');

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$th_roadmap_vstep_csvkey = optional_param('key', 0, PARAM_ALPHANUMEXT);
$listening = optional_param('l', 0, PARAM_INT);
$reading = optional_param('r', 0, PARAM_INT);
$speaking = optional_param('s', 0, PARAM_INT);
$writing = optional_param('w', 0, PARAM_INT);
$target = optional_param('target', 0, PARAM_INT);

// Check for all required variables.
$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_roadmap_vstep', $COURSE->id);
}
$context = context_system::instance();
require_login($COURSE->id);
require_capability('local/th_roadmap_vstep:import', $context);

$pageurl = '/local/th_roadmap_vstep/view.php';
$title = get_string('title', 'local_th_roadmap_vstep');
$PAGE->set_url('/local/th_roadmap_vstep/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_roadmap_vstep', 'local_th_roadmap_vstep'));
$PAGE->set_title(get_string('title', 'local_th_roadmap_vstep'));
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_roadmap_vstep', get_string('title', 'local_th_roadmap_vstep'), current_language()));

$editurl = new moodle_url('/local/th_roadmap_vstep/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'local_th_roadmap_vstep'), $editurl);
$settingsnode->make_active();
// admin_externalpage_setup('th_roadmap_vstep');

if (empty($th_roadmap_vstep_csvkey)) {

	$th_roadmap_vstep = new th_roadmap_vstep_form();

	if ($th_roadmap_vstep->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $th_roadmap_vstep->get_data()) {

		$content = $th_roadmap_vstep->get_file_content('roadmap');
		$contents = th_roadmap_vstep_csv_parse($content);
		// $contents = th_roadmap_vstep_check_data($contents);

		$th_roadmap_vstep_csvkey = $courseid . '_' . time();
		$SESSION->local_th_roadmap_vstep[$th_roadmap_vstep_csvkey] = $contents;
		// print_object($contents);
		// exit;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading($title);
		$th_roadmap_vstep->display();
		$records = $DB->get_records('local_th_roadmap_vstep', null, '', 'coursecode,knlnn,listening,reading,speaking,writing,dktq,groups');

		if ($records) {
			echo th_roadmap_vstep_display_table($records);
		}
		echo $OUTPUT->footer();
	}
}

if ($th_roadmap_vstep_csvkey) {
	$form2 = new confirm_form(null, array('th_roadmap_vstep_csvkey' => $th_roadmap_vstep_csvkey));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/local/th_roadmap_vstep/view.php');
		redirect($courseurl);

	} else if ($formdata = $form2->get_data()) {
		if (!empty($th_roadmap_vstep_csvkey) && !empty($SESSION->local_th_roadmap_vstep) &&
			array_key_exists($th_roadmap_vstep_csvkey, $SESSION->local_th_roadmap_vstep)) {

			$data = $SESSION->local_th_roadmap_vstep[$th_roadmap_vstep_csvkey];

			$DB->delete_records('local_th_roadmap_vstep');
			
			$time = time();
			foreach ($data as $k => $record) {
				$record = (object) $record;
				$record->timecreated = $time;
				$DB->insert_record('local_th_roadmap_vstep', $record);
			}
			$courseurl = new moodle_url('/local/th_roadmap_vstep/view.php');
			
			redirect($courseurl,'Thêm thành công',null,\core\output\notification::NOTIFY_SUCCESS);
		}

	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('pluginname', 'local_th_roadmap_vstep'));
		if (!empty($th_roadmap_vstep_csvkey) && !empty($SESSION->local_th_roadmap_vstep) &&
			array_key_exists($th_roadmap_vstep_csvkey, $SESSION->local_th_roadmap_vstep)) {

			$localth_roadmap_vstep_csvdata = $SESSION->local_th_roadmap_vstep[$th_roadmap_vstep_csvkey];

			if (!empty($localth_roadmap_vstep_csvdata)) {

				$ok = $localth_roadmap_vstep_csvdata;
				// print_object($ok);

				$html = th_roadmap_vstep_display_table($ok);
				// echo $OUTPUT->heading(get_string('pluginname', 'local_th_roadmap_vstep'));
				echo $html;
			}
			echo $form2->display();  
			echo $OUTPUT->footer();
		}	
	}
}
