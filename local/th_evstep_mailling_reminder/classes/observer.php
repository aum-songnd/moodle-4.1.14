<?php
    namespace local_th_evstep_mailling_reminder;
    use local_th_evstep_mailling_reminder\task\send_mail_graded_attemp;

    defined('MOODLE_INTERNAL') || die();

    class observer {
        public static function config_schedule_task(\core\event\config_log_created $event) {
            global $DB;

            try {
                
                if ($event->other['plugin'] !== 'local_th_evstep_mailling_reminder') {
                    return;
                }

                
                $task_mapping = [
                    'config_attemps_need_to_grading' => '\local_th_evstep_mailling_reminder\task\send_mail_attemps_need_to_grading',
                    'config_assign_turns' => '\local_th_evstep_mailling_reminder\task\send_mail_assign_turns',
                    'config_attemps_to_grading' => '\local_th_evstep_mailling_reminder\task\send_mail_attemps_to_grading',
                    'config_ctv_finished_grading' => '\local_th_evstep_mailling_reminder\task\send_mail_ctv_finished_grading',
                    'config_grading_approved' => '\local_th_evstep_mailling_reminder\task\send_mail_grading_approved',
                    'config_user_enrolled' => '\local_th_evstep_mailling_reminder\task\send_mail_user_enrolled',
                ];

                
                if (!isset($task_mapping[$event->other['name']])) {
                    return;
                }

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

                $task_classname = $task_mapping[$event->other['name']];

               
                $DB->execute(
                    "UPDATE {task_scheduled} SET hour = ? WHERE classname = ?",
                    [$hoursend, $task_classname]
                );

                error_log("Updated scheduled task [$task_classname] to run at hour: $hoursend");

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
        
                $task = new send_mail_graded_attemp();
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
