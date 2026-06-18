<?php

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_bulk_override_form extends moodleform {

	function definition() {
		global $DB;
		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));
		$radioarray = array();
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('extend_by_quiz', 'block_th_bulk_override'), 0);
		$radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('extend_all_quizzes', 'block_th_bulk_override'), 1);
		$mform->addGroup($radioarray, 'show_option', get_string('extend_course_by', 'block_th_bulk_override'), array(''), false);
		$mform->setDefault('show_option', 0);
        $filename = get_string('filename', 'block_th_bulk_override'); 
		$link = "<a href='example.csv'>$filename</a>";
		$link1 = "<a href='example1.csv'>$filename</a>";
		$mform->addElement('static', 'example', get_string('examplecsv', 'block_th_bulk_override'), $link);
		$mform->addElement('static', 'example1', get_string('examplecsv', 'block_th_bulk_override'), $link1);
		$mform->addElement('filepicker', 'list_time', get_string('file'));
		$this->add_action_buttons(true, get_string('submit', 'block_th_bulk_override'));
		$mform->addElement('static', 'note', get_string('note', 'block_th_bulk_override'), get_string('description', 'block_th_bulk_override'));
	}

	function validation($data, $files) {
		return array();
	}

}
