<?php

namespace local_th_productivity_report\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/local/th_productivity_report/locallib.php';

defined('MOODLE_INTERNAL') || die();

class send_month_report extends \core\task\scheduled_task {

	public static function send() {
		th_send_month_productivity_report();
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname2', 'local_th_productivity_report');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::send();
	}
}
