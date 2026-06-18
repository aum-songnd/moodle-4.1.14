<?php 
namespace local_th_study_reminder\task;

defined('MOODLE_INTERNAL') || die();

class notify_inactive_users extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('task_notify_inactive_users', 'local_th_study_reminder');
    }

    public function execute() {
        if (!get_config('local_th_study_reminder', 'enable_notify_inactive_users')) {
            mtrace('[task_notify_inactive_users] Disable via settings.');
            return;
        }
        $now = time();
        mtrace("[task_notify_inactive_users] Running at ". userdate($now));

        //Nhóm theo bụckets
        $buckets = $this ->fetch_inactive_buckets($now);

        // Thống kê số lượng
        $total = 0;
        foreach ($buckets as $k => $arr) {
            $count = is_array($arr) ? count($arr) : 0;
            $total += $count;
            mtrace("  - Bucket {$k}: {$count} học viên");
        }
        mtrace("Tổng cộng: {$total} học viên sẽ được xử lý.\n");
        
        //Gửi thông báo theo nhóm
        $this ->notify_group($buckets['today'], 'msg:today');
        $this ->notify_group($buckets['6_10'], 'msg:6_10');
        $this ->notify_group($buckets['11_20'], 'msg:11_20');
        $this ->notify_group($buckets['21_30'], 'msg:21_30');
        $this ->notify_group($buckets['31p'], 'msg:31p');

        mtrace("[task_notify_inactive_users] Done.");
    }

    //lấy ds theo buckets
    private function fetch_inactive_buckets(int $now): array {
        global $DB;

        $day     = DAYSECS;
        $today0  = usergetmidnight($now);
        $since   = $now - 180 * $day;

        // Lấy danh sách học viên: có ít nhất 1 enrolment hợp lệ + role 'student' ở context KHÓA HỌC.
        $studentids = $DB->get_fieldset_sql("
            SELECT DISTINCT u.id
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.status = 0
            JOIN {enrol} e            ON e.id = ue.enrolid AND e.status = 0
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx         ON ctx.id = ra.contextid
                                    AND ctx.contextlevel = 50
                                    AND ctx.instanceid = e.courseid
            JOIN {role} r              ON r.id = ra.roleid AND r.shortname = 'student'
            WHERE u.deleted = 0 AND u.suspended = 0 AND ue.timestart < UNIX_TIMESTAMP()
                            AND (ue.timeend = 0 OR ue.timeend > UNIX_TIMESTAMP())
        ");

        $b = ['today' => [], '6_10' => [], '11_20' => [], '21_30' => [], '31p' => []];

        if (empty($studentids)) {
            mtrace("[fetch_inactive_buckets] Không có học viên hợp lệ để kiểm tra.");
            return $b;
        }

        $totalstudents = count($studentids);
        mtrace("[fetch_inactive_buckets] Tổng số học viên hợp lệ: {$totalstudents}");

        list($insql, $inparams) = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED);

        $rows = $DB->get_records_sql("SELECT
                    u.id AS userid,
                    COALESCE(MAX(l.timecreated), 0) AS lastact,
                    MAX(CASE WHEN l.timecreated >= :t0 AND l.timecreated < :now1 THEN 1 ELSE 0 END) AS hastoday
                FROM {user} u
                LEFT JOIN {logstore_standard_log} l
                    ON l.userid = u.id
                    AND l.realuserid IS NULL
                    AND l.courseid IS NOT NULL
                    AND l.edulevel IN (1,2)
                    AND l.crud IN ('r','c','u')
                    AND l.timecreated BETWEEN :since AND :now2
                WHERE u.id $insql
                AND u.deleted = 0 AND u.suspended = 0
                GROUP BY u.id
            ", array_merge([
                    't0' => $today0,
                    'now1' => $now,
                    'now2' => $now,
                    'since' => $since
                ], $inparams));

        $count = [
            'today' => 0, '6_10' => 0, '11_20' => 0, '21_30' => 0, '31p' => 0
        ];

        foreach ($rows as $r) {
            $uid      = (int)$r->userid;
            $last     = (int)$r->lastact;
            $hasToday = (int)$r->hastoday === 1;

            // Số ngày tính tới đầu ngày hôm nay (để gửi nhắc 5 ngày đầu nếu hôm nay chưa hoạt động).
            $days_to_midnight = $last ? (int)floor(($today0 - $last) / $day) : 32;
            if (!$hasToday && $days_to_midnight >= 1 && $days_to_midnight <= 5) {
                $b['today'][] = [$uid, null];
                $count['today']++;
            }

            // Số ngày tính từ lần hoạt động gần nhất đến hiện tại.
            $days_since_last = $last ? (int)floor(($now - $last) / $day) : 32;

            if     ($days_since_last >= 6  && $days_since_last <= 10) { $b['6_10'][]  = [$uid, $days_since_last]; $count['6_10']++; }
            elseif ($days_since_last >= 11 && $days_since_last <= 20) { $b['11_20'][] = [$uid, $days_since_last]; $count['11_20']++; }
            elseif ($days_since_last >= 21 && $days_since_last <= 30) { $b['21_30'][] = [$uid, $days_since_last]; $count['21_30']++; }
            elseif ($days_since_last >= 31)                           { $b['31p'][]   = [$uid, $days_since_last]; $count['31p']++; }
        }

        // --- Thống kê tổng kết ---
        $totalfound = array_sum($count);
        mtrace("[fetch_inactive_buckets] Thống kê học viên không hoạt động:");
        mtrace("   - 1–5 ngày  (today):   {$count['today']}");
        mtrace("   - 6–10 ngày (6_10):    {$count['6_10']}");
        mtrace("   - 11–20 ngày (11_20):  {$count['11_20']}");
        mtrace("   - 21–30 ngày (21_30):  {$count['21_30']}");
        mtrace("   - 31+ ngày  (31p):     {$count['31p']}");
        mtrace("   → Tổng học viên có log inact: {$totalfound}/{$totalstudents}");

        return $b;
    }

    //Notify inactive user by buck
    private function notify_group(array $items, string $langkey): void {
        global $DB;

        if (empty($items)) return;

        foreach ($items as [$uid, $days]) {
            $user = $DB->get_record('user', ['id'=>$uid], '*', MUST_EXIST);

            // message from lang
            $raw = get_string($langkey, 'local_th_study_reminder');

            $vars = [
                '{firstname}'    => fullname($user, true),
                '{inactivedays}' => $days === null ? '' : (string)$days
            ];
            $body = strtr($raw, $vars);

           
            $m = new \core\message\message();
            $m->component         = 'local_th_study_reminder';       
            $m->name              = 'notify_inactive_users';    
            $m->userfrom          = \core_user::get_noreply_user();
            $m->userto            = $user;
            $m->subject           = get_string('subject_notify_inactive_users', 'local_th_study_reminder');
            $m->fullmessage       = html_to_text($body);
            $m->fullmessageformat = FORMAT_PLAIN;
            $m->fullmessagehtml   = format_text($body, FORMAT_HTML);
            $m->smallmessage      = html_to_text($body);
            $m->notification      = 1;

            message_send($m);
        }
    }
}