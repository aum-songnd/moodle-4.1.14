<?php

require_once "{$CFG->libdir}/formslib.php";

class question_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;
		if(isset($_GET['questionid']) && isset($_GET['action']) && $_GET['action'] == 'edit_question' && $_GET['questionid']) {
			$mform->addElement('header', 'displayinfo', get_string('editquestion', 'mod_qaa'));
		} else {
			$mform->addElement('header', 'displayinfo', get_string('textfieldsquestion', 'mod_qaa'));
		}
		
		$id = optional_param('id', 0, PARAM_INT);

		$cm = get_coursemodule_from_id('qaa', $id, 0, false, MUST_EXIST);
		$modulecontext = context_module::instance($cm->id);

		$opt = array('maxfiles' => EDITOR_UNLIMITED_FILES,
			'noclean' => true, 'context' => $modulecontext, 'subdirs' => true);

		$mform->addElement('editor', 'question', get_string('pagetitlequestion', 'mod_qaa'), array('rows' => 10), $opt);
		$mform->setType('question', PARAM_RAW);
		$mform->addRule('question', null, 'required', null, 'client');

		// hidden elements
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);

		$mform->addElement('hidden', 'questionid');
		$mform->setType('questionid', PARAM_RAW);

		$mform->addElement('hidden', 'isedit');
		$mform->setType('isedit', PARAM_RAW);

		$mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_TEXT);

		$this->add_action_buttons(true, get_string("submit", 'mod_qaa'));
	}

	function validation($data, $files) {

		$config = get_config('mod_qaa');
		$min_character = $config->min_question_character;

		$question = strip_tags(trim($data['question']['text']));

		if (strlen($question) < $min_character) {
			return array('question' => get_string('err_questionform', 'mod_qaa', $min_character));
		}
	}
}