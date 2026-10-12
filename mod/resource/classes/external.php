<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Resource external API
 *
 * @package    mod_resource
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * Resource external functions
 *
 * @package    mod_resource
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_resource_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_resource_parameters() {
        return new external_function_parameters(
            array(
                'resourceid' => new external_value(PARAM_INT, 'resource instance id')
            )
        );
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * @param int $resourceid the resource instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_resource($resourceid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/resource/lib.php");

        $params = self::validate_parameters(self::view_resource_parameters(),
                                            array(
                                                'resourceid' => $resourceid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $resource = $DB->get_record('resource', array('id' => $params['resourceid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($resource, 'resource');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/resource:view', $context);

        // Call the resource/lib API.
        resource_view($resource, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_resource_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_resources_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_resources_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of files in a provided list of courses.
     * If no list is provided all files that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and files
     * @since Moodle 3.3
     */
    public static function get_resources_by_courses($courseids = array()) {

        global $CFG;

        $warnings = array();
        $returnedresources = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_resources_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the resources in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $resources = get_all_instances_in_courses("resource", $courses);
            foreach ($resources as $resource) {
                $context = context_module::instance($resource->coursemodule);

                 $version = substr($CFG->release, 0, 1);
                if ($version == 3) {
                                    // Entry to return.
                    $resource->name = external_format_string($resource->name, $context->id);
                    $options = array('noclean' => true);
                    list($resource->intro, $resource->introformat) =
                        external_format_text($resource->intro, $resource->introformat, $context->id, 'mod_resource', 'intro', null,
                            $options);
                    $resource->introfiles = external_util::get_area_files($context->id, 'mod_resource', 'intro', false, false);
                } else {
                    core_course\external\helper_for_get_mods_by_courses::format_name_and_intro($resource, 'mod_resource');
                } 

                $resource->contentfiles = external_util::get_area_files($context->id, 'mod_resource', 'content');

                $returnedresources[] = $resource;
            }
        }

        $result = array(
            'resources' => $returnedresources,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_resources_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_resources_by_courses_returns() {
        global $CFG;
        $version = substr($CFG->release, 0, 1);
        if ($version == 3) {
            return new external_single_structure(
                array(
                    'resources' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'Module id'),
                                'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                                'course' => new external_value(PARAM_INT, 'Course id'),
                                'name' => new external_value(PARAM_RAW, 'Page name'),
                                'intro' => new external_value(PARAM_RAW, 'Summary'),
                                'introformat' => new external_format_value('intro', 'Summary format'),
                                'introfiles' => new external_files('Files in the introduction text'),
                                'contentfiles' => new external_files('Files in the content'),
                                'tobemigrated' => new external_value(PARAM_INT, 'Whether this resource was migrated'),
                                'legacyfiles' => new external_value(PARAM_INT, 'Legacy files flag'),
                                'legacyfileslast' => new external_value(PARAM_INT, 'Legacy files last control flag'),
                                'display' => new external_value(PARAM_INT, 'How to display the resource'),
                                'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                                'filterfiles' => new external_value(PARAM_INT, 'If filters should be applied to the resource content'),
                                'revision' => new external_value(PARAM_INT, 'Incremented when after each file changes, to avoid cache'),
                                'timemodified' => new external_value(PARAM_INT, 'Last time the resource was modified'),
                                'section' => new external_value(PARAM_INT, 'Course section id'),
                                'visible' => new external_value(PARAM_INT, 'Module visibility'),
                                'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                                'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                            )
                        )
                    ),
                    'warnings' => new external_warnings(),
                )
            );
        } else {

            return new external_single_structure(
                array(
                    'resources' => new external_multiple_structure(
                        new external_single_structure(array_merge(
                            core_course\external\helper_for_get_mods_by_courses::standard_coursemodule_elements_returns(),
                            [
                                'contentfiles' => new external_files('Files in the content'),
                                'tobemigrated' => new external_value(PARAM_INT, 'Whether this resource was migrated'),
                                'legacyfiles' => new external_value(PARAM_INT, 'Legacy files flag'),
                                'legacyfileslast' => new external_value(PARAM_INT, 'Legacy files last control flag'),
                                'display' => new external_value(PARAM_INT, 'How to display the resource'),
                                'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                                'filterfiles' => new external_value(PARAM_INT, 'If filters should be applied to the resource content'),
                                'revision' => new external_value(PARAM_INT, 'Incremented when after each file changes, to avoid cache'),
                                'timemodified' => new external_value(PARAM_INT, 'Last time the resource was modified'),
                            ]
                        ))
                    ),
                    'warnings' => new external_warnings(),
                )
            );
        }   
    }

    /**
     * Normalize the 101-cell map used by the web video tracker.
     * Only binary values are accepted. The server, not the client percent, calculates progress.
     */
    private static function parse_video_mapa($json) {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || count($decoded) !== 101 || array_keys($decoded) !== range(0, 100)) {
            throw new invalid_parameter_exception('mapa must be a JSON array containing 101 binary cells (0 or 1)');
        }
        foreach ($decoded as &$cell) {
            if ($cell !== 0 && $cell !== 1) {
                throw new invalid_parameter_exception('mapa cells must be numeric 0 or 1');
            }
        }
        unset($cell);
        return $decoded;
    }

    /** Match the percentage calculation in amd/src/video_tracker.js. */
    private static function video_mapa_percent($mapa) {
        return min(100, (int)round(array_sum($mapa) / 100 * 100));
    }

    public static function save_video_progress_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'userid' => new external_value(PARAM_INT, 'Ignored: authenticated user is used'),
            'currenttime' => new external_value(PARAM_INT, 'Playback position (seconds)'),
            'duration' => new external_value(PARAM_INT, 'Video duration (seconds)'),
            'percent' => new external_value(PARAM_INT, 'Client percentage (server recalculates from mapa)'),
            'mapa' => new external_value(PARAM_RAW, '101-cell JSON viewing map, required for accurate progress', VALUE_DEFAULT, '')
        ]);
    }

    public static function save_video_progress($cmid, $userid, $currenttime, $duration, $percent, $mapa = '') {
        global $DB, $USER;

        $params = self::validate_parameters(self::save_video_progress_parameters(), [
            'cmid' => $cmid,
            'userid' => $userid,
            'currenttime' => $currenttime,
            'duration' => $duration,
            'percent' => $percent,
            'mapa' => $mapa
        ]);

        $cm = get_coursemodule_from_id('resource', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/resource:view', $context);

        if ($params['duration'] <= 0 || $params['currenttime'] < 0 ||
                $params['currenttime'] > $params['duration'] ||
                $params['percent'] < 0 || $params['percent'] > 100) {
            throw new invalid_parameter_exception('Invalid video time, duration or percent');
        }
        // Legacy mobile requests without mapa cannot prove watched segments. Fail explicitly
        // rather than silently crediting a seek as watching the whole video.
        if ($params['mapa'] === '') {
            throw new invalid_parameter_exception('mapa is required; update the app to send the web-compatible viewing map');
        }
        $incomingmapa = self::parse_video_mapa($params['mapa']);
        $incomingpercent = self::video_mapa_percent($incomingmapa);
        $now = time();

        $latest = $DB->get_record_sql(
            "SELECT * FROM {resource_video_view}
              WHERE user_id = :user_id AND cm_id = :cm_id
              ORDER BY timemodified DESC, id DESC",
            ['user_id' => $USER->id, 'cm_id' => $params['cmid']], IGNORE_MULTIPLE
        );

        // Same as Web: after 100% a new session starts at the beginning with empty mapa.
        // The previous completed attempt is retained for Moodle completion reports.
        $newattempt = !$latest || ((int)$latest->percent >= 100 && $incomingpercent < 100);
        if ($newattempt) {
            $record = new stdClass();
            $record->user_id = $USER->id;
            $record->cm_id = $params['cmid'];
            $record->timecreated = $now;
        } else {
            $record = $latest;
            // Concurrent/late updates from one session must not erase viewed cells.
            // Do not merge with an already-completed attempt when starting a replay.
            if (!empty($latest->mapa)) {
                $previousmapa = json_decode($latest->mapa, true);
                if (is_array($previousmapa) && count($previousmapa) === 101) {
                    foreach ($incomingmapa as $index => $value) {
                        if (isset($previousmapa[$index]) && $previousmapa[$index] == 1) {
                            $incomingmapa[$index] = 1;
                        }
                    }
                }
            }
        }
        $record->currenttime = $params['currenttime'];
        $record->duration = $params['duration'];
        $record->mapa = json_encode($incomingmapa);
        $record->percent = self::video_mapa_percent($incomingmapa);
        $record->timemodified = $now;

        if ($newattempt) {
            $DB->insert_record('resource_video_view', $record);
        } else {
            $DB->update_record('resource_video_view', $record);
        }

        $course = get_course($cm->course);
        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC) {
            $resourcerecord = $DB->get_record('resource', ['id' => $cm->instance], 'completionpercent');
            $threshold = empty($resourcerecord->completionpercent) ? 0 : (int)$resourcerecord->completionpercent;
            if ($record->percent >= $threshold) {
                $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
            }
        }

        return ['status' => true, 'message' => $newattempt ? 'inserted' : 'updated'];
    }

    public static function save_video_progress_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Success'),
            'message' => new external_value(PARAM_TEXT, 'Result')
        ]);
    }

    /**
     * Describes the parameters for get_video_progress.
     *
     * @return external_function_parameters
     */
    public static function get_video_progress_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Returns the current user's saved video progress for a resource activity.
     *
     * @param int $cmid Course module ID
     * @return array Saved video progress
     */
    public static function get_video_progress($cmid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_video_progress_parameters(), [
            'cmid' => $cmid,
        ]);

        $cm = get_coursemodule_from_id('resource', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/resource:view', $context);

        $progress = $DB->get_record_sql(
            "SELECT *
               FROM {resource_video_view}
              WHERE user_id = :userid
                AND cm_id = :cmid
           ORDER BY timemodified DESC, id DESC",
            ['userid' => $USER->id, 'cmid' => $cm->id],
            IGNORE_MULTIPLE
        );

        $result = [
            'savedtime' => 0,
            'currenttime' => 0,
            'duration' => 0,
            'percent' => 0,
            'mapa' => '[]',
            'completed' => false,
            'timemodified' => 0,
        ];

        if ($progress) {
            $result['currenttime'] = (int) $progress->currenttime;
            $result['duration'] = (int) $progress->duration;
            $result['percent'] = (int) $progress->percent;
            $result['completed'] = $result['percent'] >= 100;
            $result['timemodified'] = (int) $progress->timemodified;

            if ($result['percent'] < 100) {
                $result['savedtime'] = $result['currenttime'];
                if (!empty($progress->mapa)) {
                    $result['mapa'] = $progress->mapa;
                }
            } else {
                $result['mapa'] = '[]';
            }
        }

        return $result;
    }

    /**
     * Describes the get_video_progress return value.
     *
     * @return external_single_structure
     */
    public static function get_video_progress_returns() {
        return new external_single_structure([
            'savedtime' => new external_value(PARAM_INT, 'Position to resume playback from, in seconds'),
            'currenttime' => new external_value(PARAM_INT, 'Last saved playback position, in seconds'),
            'duration' => new external_value(PARAM_INT, 'Video duration, in seconds'),
            'percent' => new external_value(PARAM_INT, 'Percentage of video viewed'),
            'mapa' => new external_value(PARAM_RAW, 'JSON viewing progress map'),
            'completed' => new external_value(PARAM_BOOL, 'Current attempt is complete'),
            'timemodified' => new external_value(PARAM_INT, 'Last save timestamp'),
        ]);
    }
}