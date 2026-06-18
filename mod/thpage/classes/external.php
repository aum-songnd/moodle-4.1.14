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
 * thpage external API
 *
 * @package    mod_thpage
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * thpage external functions
 *
 * @package    mod_thpage
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_thpage_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_thpage_parameters() {
        return new external_function_parameters(
            array(
                'thpageid' => new external_value(PARAM_INT, 'thpage instance id')
            )
        );
    }

    /**
     * Simulate the thpage/view.php web interface thpage: trigger events, completion, etc...
     *
     * @param int $thpageid the thpage instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_thpage($thpageid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/thpage/lib.php");

        $params = self::validate_parameters(self::view_thpage_parameters(),
                                            array(
                                                'thpageid' => $thpageid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $thpage = $DB->get_record('thpage', array('id' => $params['thpageid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($thpage, 'thpage');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/thpage:view', $context);

        // Call the thpage/lib API.
        thpage_view($thpage, $course, $cm, $context);

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
    public static function view_thpage_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_thpages_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_thpages_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of thpages in a provided list of courses.
     * If no list is provided all thpages that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and thpages
     * @since Moodle 3.3
     */
    public static function get_thpages_by_courses($courseids = array()) {

        $warnings = array();
        $returnedthpages = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_thpages_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the thpages in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $thpages = get_all_instances_in_courses("thpage", $courses);
            foreach ($thpages as $thpage) {
                $context = context_module::instance($thpage->coursemodule);
                // Entry to return.
                $thpage->name = external_format_string($thpage->name, $context->id);

                $options = array('noclean' => true);
                list($thpage->intro, $thpage->introformat) =
                    external_format_text($thpage->intro, $thpage->introformat, $context->id, 'mod_thpage', 'intro', null, $options);
                $thpage->introfiles = external_util::get_area_files($context->id, 'mod_thpage', 'intro', false, false);

                $options = array('noclean' => true);
                list($thpage->content, $thpage->contentformat) = external_format_text($thpage->content, $thpage->contentformat,
                                                                $context->id, 'mod_thpage', 'content', $thpage->revision, $options);
                $thpage->contentfiles = external_util::get_area_files($context->id, 'mod_thpage', 'content');
                try {
                    $thpage->pdffile = external_util::get_area_files($context->id, 'mod_thpage', 'pdf');
                } catch (Exception $e) {
                    $thpage->pdffile = external_util::get_area_files(1, 'mod_thpage', 'example_pdf');
                }

                $returnedthpages[] = $thpage;
            }
        }

        $result = array(
            'thpages' => $returnedthpages,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_thpages_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_thpages_by_courses_returns() {
        return new external_single_structure(
            array(
                'thpages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'thpage name'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'introfiles' => new external_files('Files in the introduction text'),
                            'content' => new external_value(PARAM_RAW, 'thpage content'),
                            'contentformat' => new external_format_value('content', 'Content format'),
                            'contentfiles' => new external_files('Files in the content'),
                            'pdffile' => new external_files('Files pdf'),
                            'legacyfiles' => new external_value(PARAM_INT, 'Legacy files flag'),
                            'legacyfileslast' => new external_value(PARAM_INT, 'Legacy files last control flag'),
                            'display' => new external_value(PARAM_INT, 'How to display the thpage'),
                            'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                            'revision' => new external_value(PARAM_INT, 'Incremented when after each file changes, to avoid cache'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the thpage was modified'),
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
    }
}
