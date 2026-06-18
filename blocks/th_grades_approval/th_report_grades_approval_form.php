<?php

require_once $CFG->dirroot.'/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_report_grades_approval_form extends moodleform {

	public function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$options = array(
			'multiple'          => true,
			'noselectionstring' => get_string('noselectionstring', 'block_th_grades_approval'),
		);

		$mform->addElement('date_selector', 'startdate',  get_string('selectcoursestartdate', 'block_th_grades_approval'));
		$mform->addRule('startdate', '', 'required', null, 'client', false, false);

		$this->add_action_buttons(true, get_string('submit'));
	}

	public function validation($data, $files) {
		return array();
	}
}
