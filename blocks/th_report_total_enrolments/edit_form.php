<?php

class edit_form extends moodleform {

	public function definition() {

		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_report_total_enrolments'));

		$mform = $this->_form;

		global $CFG, $DB;

		$mform->addElement('date_selector', 'startdate', get_string('from'));
		// $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
		// $date->modify('-6 month');
		// $mform->setDefault('startdate', $date->getTimestamp());

		$mform->addElement('date_selector', 'enddate', get_string('to'));

		$radioarray = array();
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('day', 'block_th_report_total_enrolments'), 0);
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('week'), 1);
		$radioarray[] = $mform->createElement('radio', 'filter', '', get_string('month'), 2);
		$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
		$mform->setDefault('filter', 'day');

		$mform->addElement('submit', 'send', get_string('find', 'block_th_report_total_enrolments'));

	}
}
