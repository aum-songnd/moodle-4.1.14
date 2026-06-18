<?php

namespace local_th_bulk_send_message\task;

require_once $CFG->libdir . '/datalib.php';

defined('MOODLE_INTERNAL') || die();

class auto_delete_log_send_message extends \core\task\scheduled_task {
	
	public static function delete_log_send_message() {
		global $CFG, $DB;

		mtrace("My task started");

		$month = get_config('local_th_bulk_send_message', 'month');
		$date = strtotime('-' . $month . 'month', time());

		$select = "date <= $date";
        $DB->delete_records_select('th_bulk_send_message', $select);

		mtrace("My task finished");
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname', 'local_th_bulk_send_message');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::delete_log_send_message();
	}
}

?>