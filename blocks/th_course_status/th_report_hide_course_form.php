<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_report_hide_course_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$mform->addElement('date_selector', 'startdate', get_string('select_course_startdate', 'block_th_course_status'));
		
		$this->add_action_buttons(true, 'Submit');
	}

	function validation($data, $files) {
		return array();
	}

}
