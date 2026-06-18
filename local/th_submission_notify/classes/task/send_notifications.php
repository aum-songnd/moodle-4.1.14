<?php
namespace local_th_submission_notify\task;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/local/th_submission_notify/locallib.php';
/**
 * Scheduled task to send email notifications for ungraded quiz submissions.
 */
class send_notifications extends \core\task\scheduled_task {

    public static function send() {
        global $DB;

        send_notification();
	}

    /**
     * Get the task name (shown in Moodle admin UI).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_send_notifications', 'local_th_submission_notify');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        if (!get_config('local_th_submission_notify', 'enable_notifications')) {
            mtrace('Notifications are disabled. Skipping task execution.');
            return;
        }
        mtrace("=== Executing scheduled task: th_submission_notify/send_notifications ===");
        
        self::send();
    }
}
