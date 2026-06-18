<?php
namespace local_th_studynotify\task;

defined('MOODLE_INTERNAL') || die();

class notify_inactive_students extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('notify_inactive_students', 'local_th_studynotify');
    }

    public function execute() {
        global $DB, $SITE;
        if (get_config('local_th_studynotify', 'enable_inactive_students') != 1) {
            mtrace("ℹ️ Task is disabled by the admin setting.");
            return;
        }
        $rawdays = get_config('local_th_studynotify', 'inactive_days');
        $daysarray = array_filter(array_map('trim', explode(',', $rawdays)), 'is_numeric');
        $daysarray = array_map('intval', $daysarray);

        foreach ($daysarray as $daysbefore) {
            $target0   = usergetmidnight(time()) - ($daysbefore * DAYSECS);
            $target1   = $target0 + DAYSECS - 1;
            $batchdate = date('d/m/Y', $target0);
            $systemname = format_string($SITE->shortname);
        
            mtrace("✅ [studentnotify] notify_inactive_students (T+{$daysbefore}) - target window courses startdate in [" .
            date('Y-m-d H:i', $target0) . " .. " . date('Y-m-d H:i', $target1) . "]");

            $template = trim((string) get_config('local_th_studynotify', 'inactive_template_batch'));
            if ($template === '') {
                $template = get_string('inactive_template_batch_default', 'local_th_studynotify');
                mtrace("⚠ No template (local_th_studynotify/inactive_template_batch). Abort.");
            }
        
            $feedbacklink  = trim((string) get_config('local_th_studynotify', 'feedback_link'));
            $mycourseslink = (new \moodle_url('/my/'))->out(false);
    
            $courseselect = 'id <> ? AND visible = 1 AND startdate BETWEEN ? AND ?';
            $courseparams = [SITEID, $target0, $target1];
            $courses = $DB->get_records_select('course', $courseselect, $courseparams);
            mtrace("↪ candidate courses (T+{$daysbefore}): " . count($courses));
    
            if (!$courses) {
                mtrace("ℹ️ No courses for this batch date {$batchdate} → done.");
                continue;
            }
    
            $byuser = [];
            $pairs  = 0;
    
            foreach ($courses as $course) {
                $sql = "
                    SELECT DISTINCT u.*
                    FROM {user} u
                    JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.status = :ueactive
                    JOIN {enrol} e ON e.id = ue.enrolid
                    JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = :contextcourse
                    JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
                    JOIN {role} r ON r.id = ra.roleid
                    WHERE e.courseid = :courseid
                    AND e.status = :eenabled
                    AND u.deleted = 0
                    AND u.suspended = 0
                    AND r.shortname = :studentrole
                ";
                
                $students = $DB->get_records_sql($sql, [
                    'ueactive'     => ENROL_USER_ACTIVE,
                    'eenabled'     => ENROL_INSTANCE_ENABLED,
                    'courseid'     => $course->id,
                    'contextcourse'=> CONTEXT_COURSE,
                    'studentrole'  => 'student',
                ]);
            
                foreach ($students as $u) {
                    if (!empty($u->lastaccess) && $u->lastaccess >= $target0) {
                        continue; 
                    }
                    if (!isset($byuser[$u->id])) {
                        $byuser[$u->id] = ['user' => $u, 'count' => 0];
                    }
                    $byuser[$u->id]['count']++;
                    $pairs++;
                }
            }
    
            mtrace("↪ unique inactive users: " . count($byuser) . " (user-course pairs=" . $pairs . "), batchdate={$batchdate}");
    
            $totalsent = 0;
            $totalfailed = 0;
    
            foreach ($byuser as $uid => $info) {
                $u = $info['user'];
                $count = (int)$info['count'];
    
                $subject = get_string('inactive_email_subject', 'local_th_studynotify');
                $subject = str_replace(
                    ['{$a}', '{$b}'],
                    [$systemname, $batchdate],
                    $subject
                );
                $body = str_replace(
                    ['{firstname}', '{lastname}' ,'{batchdate}', '{coursescount}', '{mycourseslink}', '{feedbacklink}'],
                    [$u->firstname, $u->lastname, $batchdate, $count, $mycourseslink, $feedbacklink],
                    $template
                );
    
                // --- GỬI NOTIFICATION (chuông) ---
                $eventdata = new \core\message\message();
                $eventdata->component         = 'local_th_studynotify';
                $eventdata->name              = 'inactive_students';
                $eventdata->userfrom          = \core_user::get_noreply_user();
                $eventdata->userto            = $u;
                $eventdata->subject           = $subject;
                $eventdata->fullmessage       = $body;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = nl2br($body);
                $eventdata->smallmessage      = get_string('inactive_email_subject', 'local_th_studynotify', $batchdate);
                $eventdata->notification      = 1;
                $messageid = message_send($eventdata);
                if ($messageid) {
                    $totalsent++;
                    mtrace("🔔 notification sent to user {$u->id}");
                } else {
                    $totalsent--;
                    mtrace("❌ notification failed for user {$u->id}");
                }
        
    
            }
    
            mtrace("✅ [studentnotify] done | users_notified=" . count($byuser) . ", sent={$totalsent}, failed={$totalfailed}");

        }
    }
}
