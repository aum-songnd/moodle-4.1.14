<?php
    namespace local_th_submission_notify;
    use local_th_submission_notify\task\send_result_task;

    defined('MOODLE_INTERNAL') || die();

    require_once $CFG->dirroot . '/local/th_submission_notify/locallib.php';

    class observer {
        public static function config_schedule_task(\core\event\config_log_created $event) {
            global $DB;
    
            try {
                if ($event->other['name'] === 'hour_send' && $event->other['plugin'] === 'local_th_submission_notify') {
                    
                    $hoursend = trim($event->other['value']);

                    $hoursend = preg_replace('/\s+/', '', $hoursend);
    
                    if (!preg_match('/^(\d{1,2})(,\d{1,2})*$/', $hoursend)) {
                        throw new \Exception("Invalid hour format: $hoursend. Must be comma-separated numbers.");
                    }

                    $hourlist = explode(',', $hoursend);
                    foreach ($hourlist as $hour) {
                        if (!is_numeric($hour) || $hour < 0 || $hour > 23) {
                            throw new \Exception("Invalid hour value: $hour. Must be between 0 and 23.");
                        }
                    }
    
                    $DB->execute(
                        "UPDATE {task_scheduled}
                        SET hour = ?
                        WHERE classname = ?",
                        [$hoursend, '\local_th_submission_notify\task\send_notifications']
                    );
    
                    error_log("Updated scheduled task to run at hour: $hoursend");
    
                }
            } catch (\dml_exception $e) {
                error_log("Database error while updating scheduled task: " . $e->getMessage());
            } catch (\Exception $e) {
                error_log("Error in config_schedule_task observer: " . $e->getMessage());
            }
        }

        public static function on_approval_completed(\block_th_assign_grading\event\approval_completed $event) {
            try {
                $userid = $event->userid;
                $approvedCount = $event->other['approved_count'] ?? 0;
                $mode = $event->other['mode'] ?? 'unknown';
        
                $task = new send_result_task();
                $task->set_custom_data([
                    'approved_ids' => $event->other['approved_ids']
                ]);

                \core\task\manager::queue_adhoc_task($task);
            } catch (\Exception $e) {
                error_log("Error in on_approval_completed observer: " . $e->getMessage());
            }
        }
    }
?>
