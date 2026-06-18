<?php
namespace local_th_studynotify\task;

defined('MOODLE_INTERNAL') || die();

class notify_weekly_study extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('weekly_study', 'local_th_studynotify');
    }

    private function get_notify_offsets(): array {
        $config = get_config('local_th_studynotify', 'notification_weeks');

        $result = [];

        if (empty($config)) {
            return $result;
        }

        $pairs = explode(',', $config);

        foreach ($pairs as $pair) {
            [$week, $days] = array_map('trim', explode(':', $pair));
            if (is_numeric($week) && is_numeric($days)) {
                $result[(int)$week] = (int)$days;
            }
        }

        return $result;
    }

    /**
     * Trả về danh sách tuần bị dính holiday của 1 batchstart.
     * Quy ước:
     * - T = batchstart (ngày mở môn) tại 00:00
     * - Holiday lưu theo [timestart, timeend) (end exclusive)
     * - Tuần 1: day 0..6, Tuần 2: day 7..13, ...
     */
    public function get_holiday_weeks_for_batch(int $batchstart, array $holidays): array {

        $notifyOffsets = $this->get_notify_offsets();
        $weeks = [];

        foreach ($notifyOffsets as $week => $daysAfterT) {

            $targetStart = $batchstart + ($daysAfterT * DAYSECS);
            $targetEnd   = $targetStart + DAYSECS;

            foreach ($holidays as $h) {
                if ($h->timestart < $targetEnd && $h->timeend > $targetStart) {
                    $weeks[] = $week;
                    break;
                }
            }
        }

        sort($weeks);
        return $weeks;
    }

    /**
     * Rule:
     * - Nếu timeend rơi vào Chủ nhật => end-exclusive = 00:00 ngày hôm sau (nếu timeend không đúng 00:00)
     * - Nếu timeend rơi vào T2..T7 => end-exclusive = 00:00 Thứ 2 của tuần kế tiếp
     *
     * $tz = 99: timezone Moodle (mặc định/user)
     */
    private function to_end_exclusive_midnight(int $timeend, int $tz = 99): int {
        // Midnight của ngày chứa timeend (theo TZ)
        $daymidnight = usergetmidnight($timeend, $tz);

        // Lấy day-of-week theo TZ: 0=CN, 1=T2, ... 6=T7
        $tzname = \core_date::get_user_timezone($tz);
        $timezone = new \DateTimeZone($tzname);

        $dow = (int)(new \DateTimeImmutable('@' . $daymidnight))
            ->setTimezone($timezone)
            ->format('w');

        // Nếu là Chủ nhật: xử lý như cũ
        if ($dow === 0) {
            return ($timeend === $daymidnight) ? $daymidnight : ($daymidnight + DAYSECS);
        }

        // Giữa tuần (T2..T7): trả về 00:00 Thứ 2 tuần kế tiếp
        // dow: 1..6 => số ngày cần cộng để tới Thứ 2 tuần sau:
        // T2(1)->7, T3(2)->6, T4(3)->5, T5(4)->4, T6(5)->3, T7(6)->2
        $daysToNextMonday = 8 - $dow;

        return $daymidnight + ($daysToNextMonday * DAYSECS);
    }

    /**
     * Chuẩn hoá list holiday về dạng [timestart, timeend) theo ngày (00:00).
     */
    private function normalize_holidays(array $holidays, int $tz = 99): array {
        foreach ($holidays as $h) {
            // timestart: ép về 00:00 ngày bắt đầu
            $h->timestart = usergetmidnight((int)$h->timestart, $tz);
            // timeend: ép về 00:00 ngày đi học lại (end-exclusive)
            $h->timeend   = $this->to_end_exclusive_midnight((int)$h->timeend, $tz);
        }

        // sort theo timestart
        usort($holidays, fn($a, $b) => $a->timestart <=> $b->timestart);
        return $holidays;
    }

    /**
     * Tìm holiday interval mà daystart (00:00) nằm trong đó.
     * Holiday đã chuẩn hoá dạng [timestart, timeend).
     */
    private function find_holiday_covering_day(int $daystart, array $holidays) {
        foreach ($holidays as $h) {
            if ($h->timestart <= $daystart && $h->timeend > $daystart) {
                return $h;
            }
        }
        return null;
    }

    /**
     * Lịch gửi thực tế (có tịnh tiến dây chuyền).
     * Trả về: [week => realSendDayStartTimestamp(00:00)]
     */
    private function build_real_send_schedule(
        int $batchstart,
        array $holidays,
        array $notifyOffsets,
        int $tz = 99
    ): array {
        // batchstart nên là 00:00
        $batchstart = usergetmidnight($batchstart, $tz);

        // Chuẩn hoá holiday
        $holidays = $this->normalize_holidays($holidays, $tz);

        $schedule = [];
        $delaysecs = 0;

        foreach ($notifyOffsets as $week => $offsetdays) {
            // planned day 00:00 + delay hiện tại
            $planned = $batchstart + ((int)$offsetdays * DAYSECS);
            $realday = $planned + $delaysecs;

            // đảm bảo realday luôn ở 00:00 (phòng khi delaysecs bị lệch)
            $realday = usergetmidnight($realday, $tz);

            // Nếu rơi vào holiday => đẩy đến timeend (00:00 ngày đi học lại).
            // Có thể dính nhiều holiday liên tiếp nên while.
            while (true) {
                $h = $this->find_holiday_covering_day($realday, $holidays);
                if (!$h) {
                    break;
                }
                $realday = (int)$h->timeend; // đã là 00:00 ngày hôm sau nếu end inclusive 23:59:59
            }

            // Update delay dây chuyền cho các tuần sau
            $delaysecs = $realday - $planned;

            $schedule[(int)$week] = $realday;
        }

        return $schedule;
    }

    public function execute() {
        global $DB;

        $enabled = (int) get_config('local_th_studynotify', 'enable_weekly_study');
        if ($enabled !== 1) {
            mtrace("ℹ️ Task Weekly Study is disabled by admin setting.");
            return;
        }

        $setting_weeks = get_config('local_th_studynotify', 'notification_weeks');
        if (empty($setting_weeks)) {
            mtrace("ℹ️ No weeks configured.");
            return;
        }
    
        $allowed_weeks = $this->get_notify_offsets();
    
        if (empty($allowed_weeks)) {
            mtrace("ℹ️ Parsed notification weeks is empty after filtering.");
            return;
        }

        $configdate = trim((string)get_config('local_th_studynotify', 'testdate'));
        if (!empty($configdate)) {
            $today = strtotime($configdate);
        } else {
            $today = strtotime('today 00:00:00');
        }

        foreach ($allowed_weeks as $w => $week) {
            $targetstart = $today - $week * DAYSECS; // ngày cần gửi (T+week) quy về 00:00

            $holidays = $DB->get_records(
                'local_th_studynotify_holiday',
                ['batchstart' => $targetstart],
                'timestart ASC'
            );

            $holidayWeeks = $this->get_holiday_weeks_for_batch($targetstart, $holidays);
            if (!empty($holidayWeeks)) {
                sort($holidayWeeks, SORT_NUMERIC);
                $minWeek = $holidayWeeks[0];
            } else {
                $minWeek = null;
            }

            if (in_array($w, $holidayWeeks) || ($minWeek && $w > $minWeek)) {
                continue;
            } else {
                $sql = "SELECT DISTINCT u.id AS userid, u.email, u.firstname, u.lastname, u.username,
                                u.maildisplay, u.mailformat, u.emailstop, u.lang, u.timezone,
                                c.id AS courseid, c.fullname AS coursename, c.shortname, c.startdate
                        FROM {user_enrolments} ue
                        JOIN {enrol} e ON e.id = ue.enrolid
                        JOIN {course} c ON c.id = e.courseid
                        JOIN {user} u ON u.id = ue.userid
                        JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                        JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = u.id)
                        JOIN {role} r ON (r.id = ra.roleid)
                        WHERE c.visible = 1
                        AND ue.status = :ueactive
                        AND u.deleted = 0
                        AND u.suspended = 0
                        AND c.startdate = :startdate
                        AND r.shortname = :rolename";
            
                $params = [
                    'ueactive'     => ENROL_USER_ACTIVE,
                    'startdate'    => $targetstart,
                    'contextlevel' => CONTEXT_COURSE,
                    'rolename'     => 'student'
                ];
            
                $rs = $DB->get_records_sql($sql, $params);

                $template = (string) get_config('local_th_studynotify', 'weekly_template');
                foreach ($rs as $u) {
                    $batchdate = date('d/m/Y', $targetstart);
                    $subject = get_string('notify_weekly_study', 'local_th_studynotify') .
                           " - Tuần $w";
    
                    $body = str_replace(
                        ['{firstname}', '{lastname}', '{weekno}', '{batchdate}'],
                        [$u->firstname, $u->lastname, $w, $batchdate],
                        $template
                    );
        
                    $bodytext = $body;
                    $bodyhtml = nl2br(s($body));
                    $real_user = \core_user::get_user($u->userid);
        
                    $message = new \core\message\message();
                    $message->component         = 'local_th_studynotify';
                    $message->name              = 'weekly_study';
                    $message->userfrom          = \core_user::get_noreply_user();
                    $message->userto            = $real_user;
                    $message->subject           = $subject;
                    $message->fullmessage       = $bodytext;
                    $message->fullmessageformat = FORMAT_PLAIN;
                    $message->fullmessagehtml   = $bodyhtml;
                    $message->smallmessage      = $subject;
                    $message->notification      = 1;
        
                    if (message_send($message)) {
                        mtrace("🔔 Sent to user {$u->userid} (Week {$w}, batch {$batchdate})");
                    } else {
                        mtrace("❌ Failed sending to user {$u->userid} (Week {$w}, batch {$batchdate})");
                    }
                }
            }
        }

        // Lấy toàn bộ holiday, group theo batchstart.
        $all_holidays = $DB->get_records('local_th_studynotify_holiday', null, 'batchstart ASC, timestart ASC');
        if (empty($all_holidays)) {
            mtrace('[studynotify] no holidays found - nothing to compute from holiday table');
            // NOTE: Nếu bạn vẫn muốn gửi cho batch không có holiday,
            // thì phải lấy batchstart từ course/startdate thay vì từ bảng holiday.
            return;
        }

        $holidaysByBatch = [];
        foreach ($all_holidays as $h) {
            $holidaysByBatch[(int)$h->batchstart][] = $h;
        }

        // Tìm các batchstart cần gửi hôm nay (theo lịch tịnh tiến).
        $sendQueue = []; // each: ['batchstart' => ..., 'week' => ...]
        foreach ($holidaysByBatch as $batchstart => $holidays) {
            $schedule = $this->build_real_send_schedule((int)$batchstart, $holidays, $allowed_weeks);
            foreach ($schedule as $week => $realday) {
                if ((int)$realday === (int)$today) {
                    $sendQueue[] = ['batchstart' => (int)$batchstart, 'week' => (int)$week];
                }
            }
        }

        if (empty($sendQueue)) {
            mtrace('[studynotify] nothing to send today');
            return;
        }

        // Gửi theo từng batchstart cần gửi hôm nay.
        foreach ($sendQueue as $item) {
            $batchstart = $item['batchstart'];
            $week       = $item['week'];

            // Query danh sách học viên theo course.startdate = batchstart
            $sql = "SELECT DISTINCT u.id AS userid, u.email, u.firstname, u.lastname, u.username,
                        u.maildisplay, u.mailformat, u.emailstop, u.lang, u.timezone,
                        c.id AS courseid, c.fullname AS coursename, c.shortname, c.startdate
                    FROM {user_enrolments} ue
                    JOIN {enrol} e ON e.id = ue.enrolid
                    JOIN {course} c ON c.id = e.courseid
                    JOIN {user} u ON u.id = ue.userid
                    JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                    JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = u.id)
                    JOIN {role} r ON (r.id = ra.roleid)
                    WHERE c.visible = 1
                    AND ue.status = :ueactive
                    AND u.deleted = 0
                    AND u.suspended = 0
                    AND c.startdate = :startdate
                    AND r.shortname = :rolename";

            $params = [
                'ueactive'     => ENROL_USER_ACTIVE,
                'startdate'    => $batchstart,
                'contextlevel' => CONTEXT_COURSE,
                'rolename'     => 'student',
            ];

            $rs = $DB->get_records_sql($sql, $params);

            $template = (string) get_config('local_th_studynotify', 'weekly_template');
            foreach ($rs as $u) {
                $batchdate = date('d/m/Y', $batchstart);
                $subject = get_string('notify_weekly_study', 'local_th_studynotify') .
                        " - Tuần $week";

                $body = str_replace(
                    ['{firstname}', '{lastname}', '{weekno}', '{batchdate}'],
                    [$u->firstname, $u->lastname, $week, $batchdate],
                    $template
                );
    
                $bodytext = $body;
                $bodyhtml = nl2br(s($body));
                $real_user = \core_user::get_user($u->userid);
    
                $message = new \core\message\message();
                $message->component         = 'local_th_studynotify';
                $message->name              = 'weekly_study';
                $message->userfrom          = \core_user::get_noreply_user();
                $message->userto            = $real_user;
                $message->subject           = $subject;
                $message->fullmessage       = $bodytext;
                $message->fullmessageformat = FORMAT_PLAIN;
                $message->fullmessagehtml   = $bodyhtml;
                $message->smallmessage      = $subject;
                $message->notification      = 1;
    
                if (message_send($message)) {
                    mtrace("Sent to user {$u->userid} (Week {$week}, batch {$batchdate})");
                } else {
                    mtrace("Failed sending to user {$u->userid} (Week {$week}, batch {$batchdate})");
                }
            }

            mtrace("[studynotify] sent week={$week} for batchstart={$batchstart}");
        }
    }
}
