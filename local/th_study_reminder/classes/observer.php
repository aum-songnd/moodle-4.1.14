<?php
namespace local_th_study_reminder;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * Update schedule task time run
     *
     * @param \core\event\config_log_created $event
     * @return void
     */
    public static function config_schedule_task(\core\event\config_log_created $event) {
        global $DB;

        try {
            $name   = $event->other['name'] ?? '';
            $plugin = $event->other['plugin'] ?? '';
            $value  = trim($event->other['value'] ?? '');

            // Check plugin local_th_study_reminder
            if ($plugin !== 'local_th_study_reminder') {
                return;
            }

            $validate_hour_format = function(string $hours): string {
                $hours = preg_replace('/\s+/', '', $hours);
                if (!preg_match('/^(\d{1,2})(,\d{1,2})*$/', $hours)) {
                    throw new \Exception("Invalid hour format: $hours. Must be comma-separated numbers.");
                }
                $hourlist = explode(',', $hours);
                foreach ($hourlist as $h) {
                    if (!is_numeric($h) || $h < 0 || $h > 23) {
                        throw new \Exception("Invalid hour value: $h. Must be between 0 and 23.");
                    }
                }
                return $hours;
            };

            if ($name === 'config_notification_hour_send') {
                $hoursend = $validate_hour_format($value);

                $DB->execute(
                    "UPDATE {task_scheduled}
                     SET hour = ?
                     WHERE classname = ?",
                    [$hoursend, '\local_th_study_reminder\task\send_study_reminder']
                );

                error_log("[TH Study Reminder] Updated task 'send_study_reminder' hour = $hoursend");
            }
            
            if ($name === 'config_notify_inactive_hour') {
                $hourinactive = $validate_hour_format($value);

                $DB->execute(
                    "UPDATE {task_scheduled}
                     SET hour = ?
                     WHERE classname = ?",
                    [$hourinactive, '\local_th_study_reminder\task\notify_inactive_users']
                );

                error_log("[TH Study Reminder] Updated task 'notify_inactive_users' hour = $hourinactive");
            }

            if ($name === 'config_notify_completion_progress_hour') {
                $hourinactive = $validate_hour_format($value);

                $DB->execute(
                    "UPDATE {task_scheduled}
                     SET hour = ?
                     WHERE classname = ?",
                    [$hourinactive, '\local_th_study_reminder\task\notify_completion_progress']
                );

                error_log("[TH Study Reminder] Updated task 'notify_completion_progress' hour = $hourinactive");
            }

        } catch (\dml_exception $e) {
            error_log("Database error while updating scheduled task: " . $e->getMessage());
        } catch (\Exception $e) {
            error_log("Error in config_schedule_task observer: " . $e->getMessage());
        }
    }
}
?>
