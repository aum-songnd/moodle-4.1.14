<?php

require_once "{$CFG->libdir}/formslib.php";

class answer_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		if(isset($_GET['questionid']) && isset($_GET['action']) && $_GET['action'] == 'edit_answer' && $_GET['questionid']) {
			$mform->addElement('header', 'displayinfo', get_string('editanswer', 'mod_qaa'));
		} else {
			$mform->addElement('header', 'displayinfo', get_string('textfieldsanswer', 'mod_qaa'));
		}

		$id = optional_param('id', 0, PARAM_INT);

		$cm = get_coursemodule_from_id('qaa', $id, 0, false, MUST_EXIST);
		$modulecontext = context_module::instance($cm->id);
		$opt = array('maxfiles' => EDITOR_UNLIMITED_FILES,
			'noclean' => true, 'context' => $modulecontext, 'subdirs' => true);

		$mform->addElement('editor', 'answer', get_string('pagetitleanswer', 'mod_qaa'), array('rows' => 10), $opt);
		$mform->setType('answer', PARAM_RAW);
		$mform->addRule('answer', null, 'required', null, 'client');

		// $config = get_config('mod_qaa');
        // $accepted_types = explode(",", $config->typefileupload);

        // foreach ($accepted_types as $key => $typefile) {
        //     $accepted_types[$key] = '.' . trim($typefile);
        // }
        // $mform->addElement('filemanager', 'attachments', get_string('file'), null,
        //     array(
        //         'accepted_types' => $accepted_types,
        //         'areamaxbytes' => $config->maxbytes,
        //         'maxfiles' => $config->maxfiles,
        //     )
        // );

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

		$question = strip_tags(trim($data['answer']['text']));

		if (strlen($question) < $min_character) {
			return array('answer' => get_string('err_questionform', 'mod_qaa', $min_character));
		}

	}
}