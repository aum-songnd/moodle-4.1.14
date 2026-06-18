<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';

class confirm_form extends moodleform {

	protected function definition() {
		global $SESSION, $CFG;

		$th_roadmap_vstep_csvkey = $this->_customdata['th_roadmap_vstep_csvkey'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $th_roadmap_vstep_csvkey);

		// Check if we want to show the enrol user button.
		$showbutton = true;
		$checkedcourses = null;
		if (isset($SESSION->local_th_roadmap_vstep) && array_key_exists($th_roadmap_vstep_csvkey, $SESSION->local_th_roadmap_vstep)) {
			$checkedcourses = $SESSION->local_th_roadmap_vstep[$th_roadmap_vstep_csvkey];
			if (empty($checkedcourses)) {
				$showbutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showbutton) {

			$buttonstring = get_string('submit');

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
