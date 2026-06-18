<?php
namespace local_th_studynotify\task;

defined('MOODLE_INTERNAL') || die();

class notify_course_opened extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('notify_course_opened', 'local_th_studynotify');
    }

    public function execute() {
        global $DB, $SITE;

        if (get_config('local_th_studynotify', 'enable_course_opened') != 1) {
            mtrace("ℹ️ Task is disabled by the admin setting.");
            return;
        }

        mtrace("✅ [studentnotify] notify_course_opened - remind on day 2 after open");

        $yesterday0   = usergetmidnight(time()) - DAYSECS;
        $yesterday1   = $yesterday0 + DAYSECS - 1;
        $batchdatestr = date('d/m/Y', $yesterday0);
        $systemname = format_string($SITE->shortname);

        $courseselect = 'id <> ? AND visible = 1 AND startdate BETWEEN ? AND ?';
        $courses      = $DB->get_records_select('course', $courseselect, [SITEID, $yesterday0, $yesterday1]);

        mtrace("↪ courses opened yesterday: " . count($courses) .
               " (" . date('Y-m-d H:i', $yesterday0) . ".." . date('Y-m-d H:i', $yesterday1) . ")");

        if (!$courses) {
            mtrace("ℹ️ No courses matched (opened yesterday) → done.");
            return;
        }

        $template = trim((string) get_config('local_th_studynotify', 'courseopen_template_batch'));
        if ($template === '') {
            $template = get_string('courseopen_template_batch_default', 'local_th_studynotify');
            mtrace("ℹ️ No custom courseopen_template_batch; using built-in default.");
        }

        $feedbacklink  = trim((string) get_config('local_th_studynotify', 'feedback_link'));
        $mycourseslink = (new \moodle_url('/my/'))->out(false);

        $byuser = [];
        $totalpairs = 0;

        foreach ($courses as $course) {
            $sql = "
                SELECT u.*
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.status = :ueactive
                JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = :courseid AND e.status = :eenabled
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = :contextcourse AND ctx.instanceid = e.courseid
                JOIN {role} r ON r.id = ra.roleid AND r.shortname = :studentrole
                WHERE u.deleted = 0
                AND u.suspended = 0
            ";
            $students = $DB->get_records_sql($sql, [
                'ueactive'      => ENROL_USER_ACTIVE,
                'eenabled'      => ENROL_INSTANCE_ENABLED,
                'courseid'      => $course->id,
                'contextcourse' => CONTEXT_COURSE,
                'studentrole'   => 'student',
            ]);

            foreach ($students as $u) {
                if (!isset($byuser[$u->id])) {
                    $byuser[$u->id] = ['user' => $u, 'count' => 0];
                }
                $byuser[$u->id]['count']++;
                $totalpairs++;
            }
        }

        mtrace("↪ unique users to notify: " . count($byuser) . " (user-course pairs=" . $totalpairs . ")");

        $totalsent = 0;
        $totalfailed = 0;

        foreach ($byuser as $uid => $info) {
            $u = $info['user'];
            $count = (int)$info['count'];

            // $subject = get_string('courseopen_email_subject', 'local_th_studynotify', $batchdatestr);
            $subject = get_string(
                'courseopen_email_subject',
                'local_th_studynotify'
            );
            $subject = str_replace(
                ['{$a}', '{$b}'],
                [$systemname, $batchdatestr],
                $subject
            );

            $body = str_replace(
                ['{firstname}', '{lastname}', '{batchdate}', '{coursescount}', '{mycourseslink}', '{feedbacklink}'],
                [$u->firstname, $u->lastname, $batchdatestr, $count, $mycourseslink, $feedbacklink],
                $template
            );

            // 2️⃣ Gửi notification (bell + message area)
            $smallmsg = get_string_manager()->string_exists('courseopen_smallmessage', 'local_th_studynotify')
                ? get_string('courseopen_smallmessage', 'local_th_studynotify', $count)
                : $subject;

            $message = new \core\message\message();
            $message->component         = 'local_th_studynotify';
            $message->name              = 'course_opened';
            $message->userfrom          = \core_user::get_noreply_user();
            $message->userto            = $u;
            $message->subject           = $subject;
            $message->fullmessage       = $body;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml   = nl2br($body);
            $message->smallmessage      = $smallmsg;
            $message->notification      = 1;
            $message->contexturl        = $mycourseslink;
            $message->contexturlname    = get_string('mycourses');

            $messageid = message_send($message);

            if ($messageid) {
                $totalsent++;
                mtrace("    🔔 notification sent to user {$u->id}");
            } else {
                $totalfailed++;
                mtrace("    ❌ notification failed for user {$u->id}");
            }            
        }

        mtrace("✅ [studentnotify] done | users_notified=" . count($byuser) . ", sent={$totalsent}, failed={$totalfailed}");
    }
}
