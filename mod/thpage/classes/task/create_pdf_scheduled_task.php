<?php

namespace mod_thpage\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/mod/thpage/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class create_pdf_scheduled_task extends \core\task\scheduled_task {

	public static function alert() {
		mtrace('create_pdf_scheduled_task');
		thpage_create_pdf_task();
	}

	/**
	 * Name for this task.
	 *ták
	 * @return string
	 */
	public function get_name() {
		return get_string('create_pdf_scheduled_task', 'mod_thpage');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::alert();
	}
}
