<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class answer_form extends moodleform {

	function definition() {

		$mform = $this->_form;
		$mform->addElement('editor', 'answer', get_string('labeltext', 'local_th_unanswered_questions'));
		$mform->setType('answer', PARAM_RAW);
		$mform->addElement(
			'filepicker',
			'attachments',
			get_string('file'),
			null
		);
        $mform->addElement('hidden', 'questionid');
		$mform->setType('questionid', PARAM_RAW);
		$mform->addElement('hidden', 'qaaid');
		$mform->setType('qaaid', PARAM_RAW);
		$mform->addElement('hidden', 'userid');
		$mform->setType('userid', PARAM_RAW);
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_RAW);
		$this->add_action_buttons(true, get_string('submit'));
	}
}