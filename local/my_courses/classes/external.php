<?php
// local/mycourses/classes/external.php

namespace local_mycourses;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

class external extends external_api {

    /**
     * Returns description of method parameters
     */
    public static function get_my_courses_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Get user's enrolled courses with priority logic
     */
    public static function get_my_courses($userid = 0) {
        global $USER, $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_my_courses_parameters(), [
            'userid' => $userid
        ]);

        // Use current user if not specified
        if ($params['userid'] == 0) {
            $params['userid'] = $USER->id;
        }

        // Check authentication
        $context = \context_system::instance();
        self::validate_context($context);
        require_login();

        // Get enrolled courses
        $courses = enrol_get_users_courses($params['userid'], true, 'id,fullname,shortname,summary,startdate,enddate');

        $inprogress = [];
        $notstarted = [];
        $completed = [];

        foreach ($courses as $course) {
            $coursedata = self::get_course_data($course, $params['userid']);
            
            if ($coursedata['status'] == 'inprogress') {
                $inprogress[] = $coursedata;
            } elseif ($coursedata['status'] == 'notstarted') {
                $notstarted[] = $coursedata;
            } else {
                $completed[] = $coursedata;
            }
        }

        // Priority logic: Đang học -> Chưa học -> Đã hoàn thành
        $result = [];
        if (!empty($inprogress)) {
            $result = array_slice($inprogress, 0, 4);
            $category = 'Đang học';
        } elseif (!empty($notstarted)) {
            $result = array_slice($notstarted, 0, 4);
            $category = 'Chưa học';
        } else {
            $result = array_slice($completed, 0, 4);
            $category = 'Đã hoàn thành';
        }

        return [
            'courses' => $result,
            'category' => $category,
            'total' => count($courses),
            'hasmore' => count($courses) > 4
        ];
    }

    /**
     * Get course data with completion progress
     */
    private static function get_course_data($course, $userid) {
        global $DB, $CFG;
        
        require_once($CFG->libdir . '/completionlib.php');
        
        $coursecontext = \context_course::instance($course->id);
        
        // Get completion info
        $completion = new \completion_info($course);
        $percentage = 0;
        $status = 'notstarted';

        if ($completion->is_enabled()) {
            $completiondata = $completion->get_data($course, false, $userid);
            
            // Calculate completion percentage
            $modules = $completion->get_activities();
            if (!empty($modules)) {
                $completed_count = 0;
                $total_count = count($modules);
                
                foreach ($modules as $module) {
                    $modulecompletion = $completion->get_data($module, false, $userid);
                    if ($modulecompletion->completionstate != COMPLETION_INCOMPLETE) {
                        $completed_count++;
                    }
                }
                
                $percentage = $total_count > 0 ? round(($completed_count / $total_count) * 100) : 0;
            }

            // Determine status
            if ($percentage == 100) {
                $status = 'completed';
            } elseif ($percentage > 0) {
                $status = 'inprogress';
            } else {
                // Check if user has accessed the course
                $sql = "SELECT COUNT(*) 
                        FROM {logstore_standard_log} 
                        WHERE userid = :userid 
                        AND courseid = :courseid";
                $accesscount = $DB->count_records_sql($sql, [
                    'userid' => $userid, 
                    'courseid' => $course->id
                ]);
                
                $status = $accesscount > 0 ? 'inprogress' : 'notstarted';
            }
        } else {
            // No completion tracking, check access
            $sql = "SELECT COUNT(*) 
                    FROM {logstore_standard_log} 
                    WHERE userid = :userid 
                    AND courseid = :courseid";
            $accesscount = $DB->count_records_sql($sql, [
                'userid' => $userid, 
                'courseid' => $course->id
            ]);
            
            $status = $accesscount > 0 ? 'inprogress' : 'notstarted';
        }

        // Get course image
        $course_obj = new \core_course_list_element($course);
        $courseimage = '';
        foreach ($course_obj->get_course_overviewfiles() as $file) {
            $courseimage = \moodle_url::make_webservice_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);
            break;
        }

        return [
            'id' => $course->id,
            'fullname' => $course->fullname,
            'shortname' => $course->shortname,
            'summary' => strip_tags($course->summary),
            'image' => $courseimage,
            'progress' => $percentage,
            'status' => $status,
            'url' => (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false)
        ];
    }

    /**
     * Returns description of method result value
     */
    public static function get_my_courses_returns() {
        return new external_single_structure([
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Course ID'),
                    'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
                    'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                    'summary' => new external_value(PARAM_RAW, 'Course summary'),
                    'image' => new external_value(PARAM_URL, 'Course image URL'),
                    'progress' => new external_value(PARAM_INT, 'Completion percentage'),
                    'status' => new external_value(PARAM_TEXT, 'Course status'),
                    'url' => new external_value(PARAM_URL, 'Course URL')
                ])
            ),
            'category' => new external_value(PARAM_TEXT, 'Display category'),
            'total' => new external_value(PARAM_INT, 'Total enrolled courses'),
            'hasmore' => new external_value(PARAM_BOOL, 'Has more than 4 courses')
        ]);
    }
}