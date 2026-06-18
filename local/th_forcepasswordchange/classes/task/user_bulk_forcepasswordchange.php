<?php

namespace local_th_forcepasswordchange\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/local/th_forcepasswordchange/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class user_bulk_forcepasswordchange extends \core\task\scheduled_task {

	public static function alert() {
		th_forcepasswordchange();
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('tasksetpassword', 'local_th_forcepasswordchange');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::alert();
	}
}
