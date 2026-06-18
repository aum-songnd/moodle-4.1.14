<?php

namespace local_th_random_test_enrolment;

use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->libdir . '/formslib.php';

/**
 * Class confirm_form
 * @package local_th_random_test_enrolment
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirm_form extends moodleform {

	/**
	 * Form definition. Abstract method - always override!
	 */
	protected function definition() {
		global $SESSION;

		$localth_random_test_enrolmentkey = $this->_customdata['local_th_random_test_enrolment_key'];
		$courseid = $this->_customdata['courseid'];

		$mform = $this->_form;

		$mform->addElement('hidden', 'key');
		$mform->setType('key', PARAM_RAW);
		$mform->setDefault('key', $localth_random_test_enrolmentkey);

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', $courseid);

		// Check if we want to show the enrol user button.
		$showenrolebutton = true;
		$checkedmails = null;
		if (isset($SESSION->local_th_random_test_enrolment) && array_key_exists($localth_random_test_enrolmentkey, $SESSION->local_th_random_test_enrolment)) {
			$checkedmails = $SESSION->local_th_random_test_enrolment[$localth_random_test_enrolmentkey];
			if (isset($checkedmails->validemailfound) && empty($checkedmails->validemailfound)) {
				$showenrolebutton = false;
			}
		}

		// Only show the enrol user button if necessary.
		if ($showenrolebutton) {

			$identifiertype = $SESSION->local_th_random_test_enrolment_identifiertype[$localth_random_test_enrolmentkey];

			$buttonstring = get_string('enrol_users', 'local_th_random_test_enrolment');

			if ($identifiertype == 1) {
				$buttonstring = get_string('enrol_users', 'local_th_random_test_enrolment');
			}

			$this->add_action_buttons(true, $buttonstring);
		}
	}
}
