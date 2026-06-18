<?php

defined('MOODLE_INTERNAL') || die();

require_once "$CFG->libdir/externallib.php";

class local_th_woocommerce_key_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function th_woocommerce_key_parameters() {
        global $CFG;

        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
            ]
        );
    }
    /**
     * registeredcourse of users.
     *
     * Function throw an exception at the first error encountered.
     * @param array $registeredcourses  An array of user registeredcourse
     * @since Moodle 2.2
     */
    public static function th_woocommerce_key($userid) {
        global $DB, $CFG;

        $data = get_config('local_th_woocommerce_key');

        $results = new stdClass();

        $results->url_woocommerce = $data->url_woocommerce;
        $results->consumerKey_woocommerce = $data->consumerKey_woocommerce;
        $results->consumerSecret_woocommerce = $data->consumerSecret_woocommerce;

        

        return $results ;
    }
    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function th_woocommerce_key_returns() {
        return new external_single_structure(
            array(
                    'url_woocommerce' => new external_value(PARAM_TEXT, 'url'),
                    'consumerKey_woocommerce' => new external_value(PARAM_TEXT, 'consumerKey'),
                    'consumerSecret_woocommerce' => new external_value(PARAM_TEXT, 'consumerSecret'),
            ),
        );        
    }
}