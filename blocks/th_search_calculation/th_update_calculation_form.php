<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->dirroot . '/lib/formslib.php';
require_once "{$CFG->libdir}/formslib.php";

class th_update_calculation_form extends moodleform {

	function definition() {

		$mform = $this->_form;

		$mform->addElement('header', 'displayinfo', get_string('filter'));

		$mform = $this->_form;
		$radioarray = array();
		$radioarray[] = $mform->createElement('radio', 'update_option', '', get_string('all_courses', 'block_th_search_calculation'), 0);
		$radioarray[] = $mform->createElement('radio', 'update_option', '', get_string('by_opening_period', 'block_th_search_calculation'), 1);
		$mform->addGroup($radioarray, 'update_option', get_string('update', 'block_th_search_calculation'), array(''), false);

		$mform->setDefault('update_option', 0);

		$options1 = array(
			'kt' => get_string('test_score', 'block_th_search_calculation'),
			'tong' => get_string('final_score', 'block_th_search_calculation'),
			'ddk' => get_string('condition_score', 'block_th_search_calculation')
		);		

		$mform->addElement('select', 'ma_diem', get_string('select_score', 'block_th_search_calculation'), $options1);
		$mform->addRule('ma_diem', get_string('required_field', 'block_th_search_calculation'), 'required', null, 'server');

		$mform->addElement('text', 'calculation', get_string('current_formula', 'block_th_search_calculation'));
		$mform->setType('calculation', PARAM_TEXT);
		$mform->addRule('calculation', get_string('required_field', 'block_th_search_calculation'), 'required', null, 'server');

		$mform->addElement('text', 'calculation_new', get_string('new_formula', 'block_th_search_calculation'));
		$mform->setType('calculation_new', PARAM_TEXT);
		$mform->addRule('calculation_new', get_string('required_field', 'block_th_search_calculation'), 'required', null, 'server');

		$mform->addElement('date_selector', 'startdate', get_string('select_opening_period', 'block_th_search_calculation'));

		$mform->disabledIf('startdate', 'update_option', 'neq', '1');
		$mform->hideif('startdate', 'update_option', 'neq', '1');

		$this->add_action_buttons(true, get_string('submit', 'block_th_search_calculation'));
	}

	function validation($data, $files) {
		return array();
	}
}

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION;

		$th_update_calculation_key = $this->_customdata['th_update_calculation_key'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_update_calculation_key);

		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checked = null;
		if (isset($SESSION->th_update_calculation) && array_key_exists($th_update_calculation_key, $SESSION->th_update_calculation)) {
			$checked = $SESSION->th_update_calculation[$th_update_calculation_key];
			if (isset($checked->validfound) && empty($checked->validfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {

			$buttonstring = 'Gửi';

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
