<?php

namespace local_th_log_email\task;

require_once $CFG->libdir . '/datalib.php';

defined('MOODLE_INTERNAL') || die();

class auto_delete_log_email extends \core\task\scheduled_task {
	
	public static function delete_log_email() {
		global $CFG, $DB;

		mtrace("My task started");

		$month = get_config('local_th_log_email', 'month');
		$date = strtotime('-' . $month . 'month', time());

		$select = "date <= $date";
        $count = $DB->count_records_select('local_th_log_email', $select, null, "COUNT('id')");
        $DB->delete_records_select('local_th_log_email', $select);

		mtrace("My task finished");
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname', 'local_th_log_email');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::delete_log_email();
	}
}

?>