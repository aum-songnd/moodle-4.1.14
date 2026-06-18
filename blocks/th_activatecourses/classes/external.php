<?php

require_once "$CFG->libdir/externallib.php";
require_once $CFG->dirroot . "/course/externallib.php";
require_once $CFG->dirroot . "/blocks/th_activatecourses/lib.php";

use core_course\external\course_summary_exporter;

class block_th_activatecourses_external extends external_api {

	public static function load_registeredcourses_by_userid_parameters() {

		return new external_function_parameters(
			array(
				new external_value(PARAM_INT, 'The id of user to fetch registerd courses'),
			)
		);
	}

	public static function load_registeredcourses_by_userid_returns() {

		$returnstructure = course_summary_exporter::get_read_structure();
		$returnstructure->keys["activated_link"] = new external_value(PARAM_URL, 'link to active this course');
		return new external_single_structure(
			array(
				'courses' => new external_multiple_structure($returnstructure, 'Course'),
				'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request'),
			)
		);
	}

	public static function load_registeredcourses_by_userid($userid = null) {

		global $CFG, $PAGE, $DB, $USER;
		require_once $CFG->dirroot . '/course/lib.php';
		require_once $CFG->dirroot . '/user/profile/lib.php';

		self::validate_context(context_user::instance($USER->id));

		if (!$userid || $userid == 0) {
			$userid = $USER->id;
		}

		$courseidarr = [];
		$records = $DB->get_records('th_registeredcourses', ['userid' => $userid, 'timeactivated' => 0]);

		foreach ($records as $rc) {
			$courseidarr[] = $rc->courseid;
		}

		$filteredcourses = [];
		if (count($courseidarr) > 0) {
			list($insql, $params) = $DB->get_in_or_equal($courseidarr);
			$sql = "SELECT * from {course} where id $insql";

			$records = $DB->get_records_sql($sql, $params);
			foreach ($records as $record) {

				$courseid = $record->id;
				$context = context_course::instance($courseid);
				if (!is_enrolled($context, $userid, '', true)) {
					$filteredcourses[] = $record;
				}
			}
		}

		$offset = 0;
		$processedcount = count($filteredcourses);

		$renderer = $PAGE->get_renderer('core');

		$formattedcourses = array_map(function ($course) use ($renderer) {
			context_helper::preload_from_record($course);
			$context = context_course::instance($course->id);
			$exporter = new course_summary_exporter($course, ['context' => $context]);
			return $exporter->export($renderer);
		}, $filteredcourses);

		foreach ($formattedcourses as $key => $value) {
			$formattedcourses[$key]->activated_link = $CFG->wwwroot . "/blocks/th_activatecourses/activate.php?id=$value->id";
		}

		return [
			'courses' => $formattedcourses,
			'nextoffset' => $offset + $processedcount,
		];
	}

	public static function get_users_courses3_parameters() {
		return new external_function_parameters(
			array(
				'userid' => new external_value(PARAM_INT, 'user id'),
				'returnusercount' => new external_value(PARAM_BOOL,
					'Include count of enrolled users for each course? This can add several seconds to the response time'
					. ' if a user is on several large courses, so set this to false if the value will not be used to'
					. ' improve performance.',
					VALUE_DEFAULT, true),
			)
		);
	}

	/**
	 * Get list of courses user is enrolled in (only active enrolments are returned).
	 * Please note the current user must be able to access the course, otherwise the course is not included.
	 *
	 * @param int $userid
	 * @param bool $returnusercount
	 * @return array of courses
	 */
	public static function get_users_courses3($userid, $returnusercount = true) {

		global $CFG, $PAGE, $DB, $USER;
		require_once $CFG->dirroot . '/course/lib.php';
		require_once $CFG->dirroot . '/user/profile/lib.php';

		require_once $CFG->dirroot . '/enrol/locallib.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/enrol/externallib.php';

		self::validate_context(context_user::instance($USER->id));

		if (!$userid || $userid == 0) {
			$userid = $USER->id;
		}

		$courseidarr = [];
		$records = $DB->get_records('th_registeredcourses', ['userid' => $userid, 'timeactivated' => 0]);

		foreach ($records as $rc) {
			$courseidarr[] = $rc->courseid;
		}

		$filteredcourses = [];
		if (count($courseidarr) > 0) {
			list($insql, $params) = $DB->get_in_or_equal($courseidarr);
			$sql = "SELECT * from {course} where id $insql";

			$records = $DB->get_records_sql($sql, $params);
			foreach ($records as $record) {

				$courseid = $record->id;
				$context = context_course::instance($courseid);
				if (!is_enrolled($context, $userid, '', true)) {

					$course = $DB->get_record('course', array('id' => $courseid));
					$manager = new course_enrolment_manager($PAGE, $course);
					$instance = null;
					foreach ($manager->get_enrolment_instances() as $tempinstance) {
						if ($tempinstance->enrol == 'manual') {
							if ($instance === null) {
								$instance = $tempinstance;
								break;
							}
						}
					}
					$plugins = $manager->get_enrolment_plugins(true);
					$plugin = $plugins[$instance->enrol];
					$duration = $instance->enrolperiod;
					$duration_hr = local_thlib_secondsToTime($duration);
					$record->duration = $duration;
					$record->duration_hr = $duration_hr;

					$filteredcourses[] = $record;
				}
			}
		}

		return $filteredcourses;
	}

	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 */
	public static function get_users_courses3_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'id' => new external_value(PARAM_INT, 'id of course'),
					'shortname' => new external_value(PARAM_RAW, 'short name of course'),
					'fullname' => new external_value(PARAM_RAW, 'long name of course'),
					'displayname' => new external_value(PARAM_RAW, 'course display name for lists.', VALUE_OPTIONAL),
					'enrolledusercount' => new external_value(PARAM_INT, 'Number of enrolled users in this course',
						VALUE_OPTIONAL),
					'idnumber' => new external_value(PARAM_RAW, 'id number of course'),
					'visible' => new external_value(PARAM_INT, '1 means visible, 0 means not yet visible course'),
					'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
					'summaryformat' => new external_format_value('summary', VALUE_OPTIONAL),
					'format' => new external_value(PARAM_PLUGIN, 'course format: weeks, topics, social, site', VALUE_OPTIONAL),
					'showgrades' => new external_value(PARAM_BOOL, 'true if grades are shown, otherwise false', VALUE_OPTIONAL),
					'lang' => new external_value(PARAM_LANG, 'forced course language', VALUE_OPTIONAL),
					'enablecompletion' => new external_value(PARAM_BOOL, 'true if completion is enabled, otherwise false',
						VALUE_OPTIONAL),
					'completionhascriteria' => new external_value(PARAM_BOOL, 'If completion criteria is set.', VALUE_OPTIONAL),
					'completionusertracked' => new external_value(PARAM_BOOL, 'If the user is completion tracked.', VALUE_OPTIONAL),
					'category' => new external_value(PARAM_INT, 'course category id', VALUE_OPTIONAL),
					'progress' => new external_value(PARAM_FLOAT, 'Progress percentage', VALUE_OPTIONAL),
					'completed' => new external_value(PARAM_BOOL, 'Whether the course is completed.', VALUE_OPTIONAL),
					'startdate' => new external_value(PARAM_INT, 'Timestamp when the course start', VALUE_OPTIONAL),
					'enddate' => new external_value(PARAM_INT, 'Timestamp when the course end', VALUE_OPTIONAL),
					'duration' => new external_value(PARAM_INT, 'default manual duration', VALUE_OPTIONAL),
					'duration_hr' => new external_value(PARAM_RAW, 'default manual duration in humand readable format', VALUE_OPTIONAL),
					'marker' => new external_value(PARAM_INT, 'Course section marker.', VALUE_OPTIONAL),
					'lastaccess' => new external_value(PARAM_INT, 'Last access to the course (timestamp).', VALUE_OPTIONAL),
					'isfavourite' => new external_value(PARAM_BOOL, 'If the user marked this course a favourite.', VALUE_OPTIONAL),
					'hidden' => new external_value(PARAM_BOOL, 'If the user hide the course from the dashboard.', VALUE_OPTIONAL),
					'overviewfiles' => new external_files('Overview files attached to this course.', VALUE_OPTIONAL),
				)
			)
		);
	}

	///////////////

	public static function activate_course_parameters() {
		return new external_function_parameters(
			array(
				'courseid' => new external_value(PARAM_INT, 'Course ID to activate'),
				'userid'   => new external_value(PARAM_INT, 'User ID to activate the course for', VALUE_DEFAULT, 0),
			)
		);
	}

	/**
	 * Activates (enrols) a given user into a course if the activation date has been reached.
	 *
	 * Process:
	 * - Validate the context for the given user.
	 * - Verify that the course exists.
	 * - Retrieve remote product information and the user's registration time.
	 * - Determine the final course activation date based on:
	 *     + The default course open date (`preorder`).
	 *     + The user registration date plus the configured activation days (`activationdays`).
	 * - If the current time is before the activation date → return without enrolling.
	 * - If eligible → enrol the user into the course via the "manual enrolment" method.
	 * - Update `timeactivated` in the `th_registeredcourses` table.
	 *
	 * Return codes (`code`):
	 * - 0: General error.
	 * - 1: Success.
	 * - 2: Course not yet open.
	 * - 3: Course not found.
	 * - 4: Manual enrolment method not found.
	 * - 5: Enrolment plugin unavailable.
	 *
	 * @param int $courseid The ID of the course to activate.
	 * @param int $userid   The ID of the user to activate the course for. If 0 or not provided, the current logged-in user will be used.
	 *
	 * @return stdClass The activation result, containing:
	 *     - bool   $success  True if enrolment succeeded, false otherwise.
	 *     - int    $code     Result code (see description above).
	 *     - string $message  Result message.
	 *     - array  $data     Additional data (e.g., courseid, final_date_enroll, etc.).
	 *
	 * @throws moodle_exception If the context is invalid or the user lacks permission.
	 */
	public static function activate_course($courseid, $userid = 0) {
		global $CFG, $PAGE, $DB, $USER;

		require_once $CFG->dirroot . '/enrol/locallib.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/enrol/externallib.php';
		require_once $CFG->dirroot . '/local/th_ecommercelib/lib.php';

		// Default to current user if userid is missing or 0.
		if (empty($userid) || $userid == 0) {
			$userid = $USER->id;
		}

		self::validate_context(context_user::instance($userid));

		$result = (object) [
			'success' => false,
			'code'    => 0,
			'message' => '',
			'data'    => null
		];

		if (!$courseid) {
			$result->message = 'Missing course ID.';
			return $result;
		}

		$course = $DB->get_record('course', ['id' => $courseid]);
		if (!$course) {
			$result->code = 3;
			$result->message = 'Course not found.';
			return $result;
		}

		// Get remote product information.
		$remote_products = (array) th_ecommercelib::get_list_remote_products();
		$current_p = $remote_products[$course->shortname] ?? null;

		// Check course open date (simplified).
		if (!empty($current_p) && !empty($current_p->preorder_state) && $current_p->preorder > time()) {
			$result->code = 2;
			$result->message = get_string(
				'course_not_open_until',
				'block_th_activatecourses',
				userdate($current_p->preorder)
			);
			$result->data = ['final_date_enroll' => (int) $current_p->preorder];
			return $result;
		}


		// Enrol the user.
		$manager = new course_enrolment_manager($PAGE, $course);
		$instance = null;
		foreach ($manager->get_enrolment_instances() as $tempinstance) {
			if ($tempinstance->enrol == 'manual') {
				$instance = $tempinstance;
				break;
			}
		}

		if (!$instance) {
			$result->message = 'Manual enrolment method not found.';
			return $result;
		}

		$plugins = $manager->get_enrolment_plugins(true);
		$plugin = $plugins[$instance->enrol] ?? null;
		if (!$plugin) {
			$result->message = 'Enrolment plugin unavailable.';
			return $result;
		}

		$duration   = (int) $instance->enrolperiod;
		$timestart  = time();
		$timeend    = $duration > 0 ? ($timestart + $duration) : 0;

		$plugin->enrol_user($instance, $userid, 5, $timestart, $timeend);

		$context = context_course::instance($courseid);
		if (is_enrolled($context, $userid, '', true)) {
			$DB->set_field('th_registeredcourses', 'timeactivated', time(), [
				'userid' => $userid,
				'courseid' => $courseid,
				'timeactivated' => 0
			]);

			$result->success = true;
			$result->code    = 1;
			$result->message = 'Course activated successfully.';
			$result->data    = ['courseid' => $courseid];
		} else {
			$result->message = 'Course activation failed.';
		}

		return $result;
	}


	/**
	 * Returns description of method result value.
	 *
	 * @return external_description
	 */
	public static function activate_course_returns() {
		return new external_single_structure(
			array(
				'success' => new external_value(PARAM_BOOL, 'True if enrolment succeeded, false otherwise'),
				'code'    => new external_value(PARAM_INT,  'Result code: 0=error, 1=success, 2=not-open-yet, 3=course-not-found, 4=no-manual-enrol, 5=plugin-unavailable'),
				'message' => new external_value(PARAM_TEXT, 'Result message'),
				'data'    => new external_single_structure(
					array(
						'courseid'          => new external_value(PARAM_INT,  'Course ID', VALUE_OPTIONAL),
						'final_date_enroll' => new external_value(PARAM_INT,  'Unix timestamp when the course will open (if not open yet)', VALUE_OPTIONAL),
					),
					'Additional payload (optional)',
					VALUE_OPTIONAL
				),
			)
		);
	}

}
