<?php

require_once $CFG->dirroot . '/lib/formslib.php';

class th_excel_bulk_enrol_student_form extends moodleform {

	function definition() {
		global $COURSE, $CFG;
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'option', '', get_string('student_id', 'block_th_bulk_enrol_student'), 0);
		$radioarray[] = &$mform->createElement('radio', 'option', '', get_string('email', 'block_th_bulk_enrol_student'), 1);

		$mform->addGroup($radioarray, 'option', get_string('options', 'block_th_bulk_enrol_student'), array(''), false);
		$mform->setDefault('option', 0);


		$options = array(
			'0' => get_string('no', 'block_th_bulk_enrol_student'),
			'1' => get_string('yes', 'block_th_bulk_enrol_student'),
		);
		
		$select = $mform->addElement('select', 'option_2', get_string('unlimited_assignment', 'block_th_bulk_enrol_student'), $options);
		$select->setSelected('1');		

		$link = "<a href='th_bulk_enrol_student.xlsx'>example.xlsx</a>";
		$mform->addElement('static', 'example', 'Example', $link);
		// Reuse the same options.
		$mform->addElement('filepicker', 'file', get_string('file'), null,
			array(
				'accepted_types' => array('.xlsx'),
				'areamaxbytes' => 100000000,
				'maxfiles' => 1,
			)
		);

		$mform->addRule('file', null, 'required', null, 'client');
		$this->add_action_buttons(true, get_string('submit'));
	}
	function validation($data, $files) {
		return array();
	}
}

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_bulkenrol_csvkey = $this->_customdata['th_bulkenrol_csv_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_bulkenrol_csvkey);

		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checkedmails = null;
		if (isset($SESSION->th_bulkenrol_csv) && array_key_exists($th_bulkenrol_csvkey, $SESSION->th_bulkenrol_csv)) {
			$checkedmails = $SESSION->th_bulkenrol_csv[$th_bulkenrol_csvkey];
			if (isset($checkedmails->validemailfound) && empty($checkedmails->validemailfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {

			$buttonstring = get_string('enrol_users', 'block_th_bulk_enrol_student');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}

class th_excel_bulk_enrol_student1_form extends moodleform {

	function definition() {
		global $COURSE, $CFG;
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'option', '', get_string('student_id', 'block_th_bulk_enrol_student'), 0);
		$radioarray[] = &$mform->createElement('radio', 'option', '', get_string('email', 'block_th_bulk_enrol_student'), 1);

		$mform->addGroup($radioarray, 'option', get_string('options', 'block_th_bulk_enrol_student'), array(''), false);
		$mform->setDefault('option', 0);


		$options = array(
			'0' => get_string('no', 'block_th_bulk_enrol_student'),
			'1' => get_string('yes', 'block_th_bulk_enrol_student'),
		);
		
		$select = $mform->addElement('select', 'option_2', get_string('unlimited_assignment', 'block_th_bulk_enrol_student'), $options);
		$select->setSelected('1');		

		$link = "<a href='th_bulk_enrol_student.xlsx'>example.xlsx</a>";
		$mform->addElement('static', 'example', 'Example', $link);
		// Reuse the same options.
		$mform->addElement('filepicker', 'file', get_string('file'), null,
			array(
				'accepted_types' => array('.xlsx'),
				'areamaxbytes' => 100000000,
				'maxfiles' => 1,
			)
		);

		$mform->addRule('file', null, 'required', null, 'client');
		$this->add_action_buttons(true, get_string('submit'));
	}
	function validation($data, $files) {
		return array();
	}
}