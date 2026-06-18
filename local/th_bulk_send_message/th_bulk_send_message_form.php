<?php

require_once $CFG->dirroot . '/lib/formslib.php';

class th_bulk_send_message_form extends moodleform {

	function definition() {
		global $COURSE, $CFG;
		$mform = $this->_form;
		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$radioarray=array();
		$radioarray[] = $mform->createElement('radio', 'option', '', get_string('student_id', 'local_th_bulk_send_message'), 1);
		$radioarray[] = $mform->createElement('radio', 'option', '', get_string('email', 'local_th_bulk_send_message'), 0);
		$mform->addGroup($radioarray, 'options', 'Option', array(' '), false);
		$mform->setDefault('option', 1);

		$link = "<a href='example_bulk_send_message.xlsx'>example.xlsx</a>";
		$mform->addElement('static', 'example', get_string('example_label', 'local_th_bulk_send_message'), $link);
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
}

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_bulk_send_message_key = $this->_customdata['th_bulk_send_message_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_bulk_send_message_key);

		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checkedusers = null;
		if (isset($SESSION->local_th_bulk_send_message) && array_key_exists($th_bulk_send_message_key, $SESSION->local_th_bulk_send_message)) {
			$checkedusers = $SESSION->local_th_bulk_send_message[$th_bulk_send_message_key];
			if (isset($checkedusers->validuserfound) && empty($checkedusers->validuserfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {
			$buttonstring = get_string('send', 'local_th_bulk_send_message');
			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
