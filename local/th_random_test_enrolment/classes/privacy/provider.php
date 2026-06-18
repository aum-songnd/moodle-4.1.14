<?php

namespace local_th_random_test_enrolment\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementing null_provider.
 *
 * @package    local_th_random_test_enrolment
 * @copyright  2018 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {

	/**
	 * Get the language string identifier with the component's language
	 * file to explain why this plugin stores no data.
	 *
	 * @return string
	 */
	public static function get_reason(): string {
		return 'privacy:metadata';
	}
}
