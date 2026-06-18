<?php

namespace local_th_forcepasswordchange\task;

require_once $CFG->libdir . '/datalib.php';

defined('MOODLE_INTERNAL') || die();
/**
 * Simple task to delete old password reset records.
 */
class password_reset_cleanup_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskpasswordresetcleanup', 'local_th_forcepasswordchange');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB, $CFG;

        $config = get_config('local_th_forcepasswordchange');

        // Cleanup user password reset records.
        // Delete any reset request records which are expired by more than a day.
        // (We keep recently expired requests around so we can give a different error msg to users who
        // are trying to user a recently expired reset attempt).
        $pwresettime = isset($config->pwresettime) ? $config->pwresettime : 1800;
        $earliestvalid = time() - $pwresettime - DAYSECS;
        $DB->delete_records_select('local_th_forcepasswordchange', "timerequested < ?", array($earliestvalid));
        mtrace(' Cleaned up old password reset records');

    }

}
