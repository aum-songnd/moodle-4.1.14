<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class edit_url_form extends moodleform {

	function definition() {

		$mform = $this->_form;

		// if(isset($_GET['cm'])) {
		// 	$context = context_module::instance($_GET['cm']);
		// } else if (isset($_POST['cm_id'])) {
		// 	$context = context_module::instance($_POST['cm_id']);
		// } else {
		// 	$context = null;
		// }

		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'cm_id');
		$mform->setType('cm_id', PARAM_INT);

		$mform->addElement('text', 'name', get_string('field_name', 'thurl'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', get_string('error_empty_name', 'thurl'), 'required', null, 'server');

		$mform->addElement('text', 'url', get_string('field_url', 'thurl'));
		$mform->setType('url', PARAM_TEXT);
		$mform->addRule('url', get_string('error_empty_url', 'thurl'), 'required', null, 'server');

		$mform->addElement('editor', 'desc', get_string('field_desc', 'thurl'), null, array(
			'subdirs' => null,
			'maxbytes' => 0,
			'maxfiles' => -1,
			'context' => null,
			'noclean' => 1,
			'trusttext' => 1));
		$mform->setType('desc', PARAM_RAW);

		$mform->addElement('checkbox', 'display', get_string('field_display', 'thurl'));
		$mform->setDefault('display', 0);

		$objs = array();

		$objs['time_open'] = $mform->createElement('date_time_selector', 'time_open', get_string('time_open', 'thurl'));
		$objs['time_close'] = $mform->createElement('date_time_selector', 'time_close', get_string('time_close', 'thurl'));

		$grp = $mform->addElement('group', 'han_che', get_string('han_che', 'thurl'), $objs, '', false);

		$this->add_action_buttons(true, get_string('submit'));
	}

	function validation($data, $files) {
		$retval = array();

		if ($data['time_open'] > $data['time_close']) {
			$retval['han_che'] = get_string('han_che_time', 'thurl');
		}

		return $retval;
	}
}
