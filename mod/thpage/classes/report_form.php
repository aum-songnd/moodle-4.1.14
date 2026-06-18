<?php

// namespace mod_thpage\form;
require_once $CFG->dirroot . '/lib/formslib.php';

class report_form extends moodleform
{

	public function definition()
	{
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('textfields','mod_thpage'));
		$mform = $this->_form;


		$mform->addElement('date_selector', 'form_date_from', get_string('form_date_from', 'mod_thpage'));
		$date = (new DateTime())->setTimestamp(usergetmidnight(time()));
		$date->modify('-1 week');
		$mform->setDefault('form_date_from', $date->getTimestamp());

		$mform->addElement('date_selector', 'form_date_to', get_string('form_date_to', 'mod_thpage'));

		$this->add_action_buttons(true, get_string('submit'));
	}
}