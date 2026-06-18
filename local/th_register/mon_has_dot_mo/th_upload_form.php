<?php

require_once $CFG->dirroot.'/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot.'/local/th_register/mon_has_dot_mo/lib.php';

class th_upload_form extends moodleform {

	public function definition() {
		global $DB;
		$mform = $this->_form;

		$link = "<a href='example.csv'>example.csv</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'local_th_register'), $link);

		$mform->addElement('filepicker', 'list_course', get_string('file'));

		$mform->addRule('list_course', get_string('required'), 'required', null, 'client');

		$ds_dot_mo_mon = ds_dot_mo_mon();

		$mform->addElement('select', 'dot_mo_mon', get_string('batch_opening', 'local_th_register'), $ds_dot_mo_mon);
		$mform->addRule('dot_mo_mon', get_string('required'), 'required', null, 'client');

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files) {
		return array();
	}

}

class confirm_upload_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_upload_course_key = $this->_customdata['th_upload_course_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_upload_course_key);

		$showbutton   = true;
		$check_course = null;
		if (isset($SESSION->local_th_upload_course) && array_key_exists($th_upload_course_key, $SESSION->local_th_upload_course)) {
			$check_course = $SESSION->local_th_upload_course[$th_upload_course_key];
			if (isset($check_course->valid_course_found) && empty($check_course->valid_course_found)) {
				$showbutton = false;
			}
		}

		if ($showbutton) {

			$buttonstring = get_string('submit');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}

class edit_form extends moodleform {

	public function definition() {
		global $DB;
		$mform = $this->_form;

		$ds_dot_mo = ds_dot_mo_mon();
		$ds_mon    = th_ds_mon();

		$options = array(
			'multiple'          => true,
			'noselectionstring' => get_string('noselectionstring', 'local_th_register'),
		);

		$mform->addElement('autocomplete', 'dot_mo_id', get_string('batch_opening', 'local_th_register'), $ds_dot_mo, $options);
		$mform->addRule('dot_mo_id', '', 'required', null, 'client', false, false);

		$options1 = array(
			'multiple'          => true,
			'noselectionstring' => get_string('noselectionstring', 'local_th_register'),
		);

		$mform->addElement('autocomplete', 'mon_hoc_id', get_string('course', 'local_th_register'), $ds_mon, $options1);
		$mform->addRule('mon_hoc_id', '', 'required', null, 'client', false, false);

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files) {
		return array();
	}
}
