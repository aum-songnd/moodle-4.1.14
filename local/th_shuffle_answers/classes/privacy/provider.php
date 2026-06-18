<?php

namespace local_th_shuffle_answers\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for local_cohort_profile implementing null_provider.
 *
 * @package   local_cohort_profile
 * @copyright 2019, Yuriy Yurinskiy <yuriyyurinskiy@yandex.ru>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
