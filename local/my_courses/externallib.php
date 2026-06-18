<?php
// local/mycourses/externallib.php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/enrol/externallib.php'); // core_enrol_external

class local_my_courses_external extends \core_enrol_external {

    public static function get_my_courses_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID (0 = current user)', VALUE_DEFAULT, 0),
            'limit'  => new external_value(PARAM_INT, 'Max courses to return', VALUE_DEFAULT, 4),
        ]);
    }

    public static function get_my_courses($userid = 0, $limit = 4) {
        global $USER;

        $params = self::validate_parameters(self::get_my_courses_parameters(), [
            'userid' => $userid,
            'limit'  => $limit,
        ]);

        require_login();

        $userid = (int) $params['userid'] ?: (int) $USER->id;
        $limit  = max(1, (int) $params['limit']);

        // Lấy toàn bộ courses (core validate + format). Không cần usercount => truyền false.
        $courses = parent::get_users_courses($userid, false);

        // Random toàn bộ danh sách
        if (count($courses) > 1) {
            shuffle($courses);
        }

        // Lấy random N courses
        $picked = array_slice($courses, 0, $limit);

        return [
            'courses'     => $picked,
        ];
    }

    public static function get_my_courses_returns() {
        return new external_single_structure([
            'courses' => parent::get_users_courses_returns(),
        ]);
    }
}
