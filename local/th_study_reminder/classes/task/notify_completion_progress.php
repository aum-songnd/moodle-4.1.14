<?php
namespace local_th_study_reminder\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;
use core\message\message;

require_once(__DIR__ . '/../../locallib.php');

/**
 * Task: Notify users about combo learning progress.
 * Flow:
 *  - Fetch active combos from mdl_th_bundleassign_api
 *  - Determine program (CT/CB) from user profile
 *  - Read combo duration from plugin config
 *  - Compute actual completion % (per quizzes)
 *  - Send message if combo is still within duration
 *  - Only once per 30-day cycle since T+30
 */
class notify_completion_progress extends scheduled_task {

    public function get_name() {
        return get_string('task_notify_completion_progress', 'local_th_study_reminder');
    }

    public function execute() {
        global $DB;

        $config = get_config('local_th_study_reminder');

        if(!$config->enable_notify_completion_progress) {
            mtrace("Disable by setting");
            return;
        }

        $now = time();
        $day = DAYSECS;
        //tính chu kì gửi T+30
        $cyclelen = 30 * $day;
        $startoffset = 30 * $day;
        mtrace("start at " . date('Y-m-d H:i:s'));

        // Get module id
        $quizmoduleid = (int)$DB->get_field('modules', 'id', ['name' => 'quiz']);
        if (!$quizmoduleid) {
            mtrace('Quiz module not found, abort.');
            return;
        }

        $finalquiz_idnumber = $config->config_finalquiz;
        $finalquiz_point = isset($config->config_point)? (float) $config->config_point : 5.0;

        //Using recordset
        $combos = $DB->get_recordset('th_bundleassign_api', ['status' => 1]);
        $combo_count = 0;
        foreach ($combos as $combo) {
            $combo_count++;

            $userid = $combo->userid;
            $combo_id = $combo->id;
            $combo_name = strtoupper(trim($combo->combo_shortname));
            $combo_start = (int)$combo->date_assign;
            mtrace("Processing combo {$combo_name} for user {$userid}");
            // Send by cycle (T+30 check)
            if ($now < $combo_start + $startoffset) {
                mtrace("Not yet T+30");
                continue;
            }

            $cycle = (int)floor(($now - ($combo_start + $startoffset))/$cyclelen) + 1;
            if ($cycle < 1 ) $cycle = 1;

            $prefkey = "thcn_last_cycle_{$combo_id}";
            $lastsent = (int)get_user_preferences($prefkey, 0, $userid);
            if ($lastsent >= $cycle) {
                mtrace("Already sent in cycle {$lastsent}, skipping");
                continue;
            }

            $program = thcn_get_user_program($userid);
            if (!$program) {
                mtrace("Cannot resolve program for user {$userid}");
                continue;
            }
            $duration_months = thcn_get_combo_duration_from_config($program, $combo_name);
            if (!$duration_months) {
                mtrace("No duration configured for {$combo_name} of program {$program}");
                continue;
            }

            $combo_end = strtotime("+{$duration_months} months", $combo_start);
            if ($now > $combo_end) {
                mtrace("Combo expired");
                continue;
            }
        
            $data = json_decode($combo->all_remote_course, true);
            if (isset($data['data'][0]['courses_combo'])) {
                $courses_combo = $data['data'][0]['courses_combo'];
            } elseif (isset($data[0]) && is_array($data[0])) {
                $courses_combo = $data;
            } else {
                $courses_combo = [];
            }

            if (empty($courses_combo)) {
                mtrace("Combo has no courses");
                continue;
            }

            // Compute progress by quizzes 
            $actual = compute_combo_progress_batched($userid, $courses_combo, $quizmoduleid, explode(',', $finalquiz_idnumber), $finalquiz_point);

            // Compute expected progress
            $elapsed_ratio = expected_progress($combo_start, $now, $duration_months);

            mtrace(" Combo {$combo_name} actual={$actual}%, expected={$elapsed_ratio}%");

            if ($actual < $elapsed_ratio) {
                $status = 'slow';
            } elseif ($actual > $elapsed_ratio) {
                $status = 'ahead';
            } else {
                $status = 'ontrack';
            }
            mtrace("  Status = {$status}");

            $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0, 'suspended' => 0]);
            if (!$user) {
                mtrace(" User not found or invalid");
                continue;
            }

            $subject = get_string('completion_subject', 'local_th_study_reminder', $combo_name);

            $percent_diff = abs(round($elapsed_ratio - $actual, 2));
            $params = (object)[
                'actual' => $actual,
                'combo'  => $combo_name,
                'diff'   => abs((float)$percent_diff),
            ];

            if ($status === 'ontrack') {
                $body = get_string('msg_ontrack', 'local_th_study_reminder', $params);
            } elseif ($status === 'ahead') {
                $body = get_string('msg_ahead', 'local_th_study_reminder', $params);
            } else {
                $body = get_string('msg_behind', 'local_th_study_reminder', $params);
            }
            $body_html = nl2br($body);


            $msg = new message();
            $msg->component = 'local_th_study_reminder';
            $msg->name = 'completion_notify';
            $msg->userfrom = \core_user::get_noreply_user();
            $msg->userto = $user;
            $msg->subject = $subject;
            $msg->fullmessage = $body;
            $msg->fullmessageformat = FORMAT_PLAIN;
            $msg->fullmessagehtml = $body_html;
            $msg->smallmessage = strip_tags($subject);
            $msg->notification = 1;


            if (message_send($msg)) {
                mtrace(" Sent to {$user->id} ({$user->firstname}) combo {$combo_name} {$status}");
                set_user_preference($prefkey, $cycle, $userid); 
            }
            else {
                mtrace("  Send failed {$user->id} ({$user->firstname}) combo {$combo_name}");
            }
        }
        $combos->close();

        mtrace("task ht  " . date('Y-m-d H:i:s') . " ");
    }
}

