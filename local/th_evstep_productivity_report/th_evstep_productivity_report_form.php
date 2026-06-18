<?php

class th_evstep_productivity_report_form extends moodleform {
	//Add elements to form
	public function definition() {
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'local_th_evstep_productivity_report'));

		$mform->addElement('date_selector', 'from', get_string('from', 'local_th_evstep_productivity_report'));
		$mform->setDefault('from', strtotime('last month'));

		$mform->addElement('date_selector', 'to', get_string('to', 'local_th_evstep_productivity_report'));

		$this->add_action_buttons(false, get_string('submit', 'local_th_evstep_productivity_report'));
	}
}