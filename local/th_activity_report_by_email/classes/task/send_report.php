<?php

namespace local_th_activity_report_by_email\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/local/th_activity_report_by_email/locallib.php';

defined('MOODLE_INTERNAL') || die();

class send_report extends \core\task\scheduled_task {

	public static function send() {
		th_activity_report_by_email();
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname', 'local_th_activity_report_by_email');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::send();
	}
}
