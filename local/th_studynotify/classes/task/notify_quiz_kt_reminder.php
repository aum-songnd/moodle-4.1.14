<?php
namespace local_th_studynotify\task;

defined('MOODLE_INTERNAL') || die();
class notify_quiz_kt_reminder extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('task_notify_quiz_kt_reminder', 'local_th_studynotify');
    }

    public function execute() {
        global $DB, $SITE;

        if (get_config('local_th_studynotify', 'enable_quiz_kt_reminder') != 1) {
            mtrace("ℹ️ Task is disabled by the admin setting.");
            return;
        }

        $today = time();
        $today_midnight = strtotime('midnight');

        // Cấu hình plugin
        $rawids = get_config('local_th_studynotify', 'quizidnumber');
        $rawdays = get_config('local_th_studynotify', 'days_before_quiz');
        $systemname = format_string($SITE->shortname);

        if (empty($rawids) || empty($rawdays)) {
            mtrace("⛔ Thiếu cấu hình quizidnumber hoặc days_before_quiz.");
            return;
        }

        $idnumbers = array_filter(array_map('trim', explode(',', $rawids)));
        $daysarray = array_map('intval', array_filter(array_map('trim', explode(',', $rawdays)), 'is_numeric'));
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);

        foreach ($idnumbers as $idnumber) {
            mtrace("🔍 Đang xử lý idnumber: $idnumber");

            // Lấy tất cả quiz có cùng idnumber và còn hạn
            $sql = "
                SELECT q.id AS quizid, q.name, q.course, q.timeopen, q.timeclose,
                       cm.id AS cmid, c.startdate
                  FROM {quiz} q
                  JOIN {course_modules} cm ON cm.instance = q.id
                  JOIN {modules} m ON m.id = cm.module
                  JOIN {course} c ON c.id = q.course
                 WHERE cm.idnumber = :idnumber
                   AND m.name = 'quiz'
                   AND q.timeclose > :now
              ORDER BY c.startdate ASC";
            $params = [
                'idnumber' => $idnumber,
                'now' => $today
            ];

            $quizzes = $DB->get_records_sql($sql, $params);
            if (!$quizzes) {
                mtrace("⛔ Không có quiz nào cho idnumber $idnumber.");
                continue;
            }

            // Gom quiz theo courseid để giảm truy vấn user
            $courses = [];
            foreach ($quizzes as $q) {
                $quizclose_day = strtotime('midnight', $q->timeclose);
                $daysleft = ($quizclose_day - $today_midnight) / DAYSECS;

                // Chỉ lấy quiz trong khoảng ngày được cấu hình
                if (!in_array($daysleft, $daysarray)) {
                    continue;
                }

                $courses[$q->course]['quizzes'][] = $q;
                $courses[$q->course]['daysleft'] = $daysleft;
                $courses[$q->course]['startdate'] = $q->startdate;
            }

            if (empty($courses)) {
                mtrace("⛔ Không có quiz nào đến hạn trong $idnumber.");
                continue;
            }

            // Mảng lưu user đã gửi trong từng đợt (idnumber + startdate)
            $sentusers = [];

            foreach ($courses as $courseid => $courseinfo) {
                $quizlist = $courseinfo['quizzes'];
                $daysleft = $courseinfo['daysleft'];
                $startdate = $courseinfo['startdate'];

                $quizids = implode(',', array_map(fn($q) => $q->cmid, $quizlist));
                $batchkey = $idnumber . '_' . $startdate; // ✅ phân biệt theo đợt mở

                // Lấy danh sách học viên chưa hoàn thành bất kỳ quiz nào trong đợt
                $uids = $DB->get_fieldset_sql("
                    SELECT DISTINCT u.id
                    FROM {user} u
                    JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.status = :ueactive
                    JOIN {enrol} e ON e.id = ue.enrolid AND e.status = :eenabled
                    JOIN {role_assignments} ra ON ra.userid = u.id
                    JOIN {context} ctx ON ctx.id = ra.contextid 
                                        AND ctx.contextlevel = :contextcourse 
                                        AND ctx.instanceid = e.courseid
                    JOIN {course_modules} cm ON cm.course = e.courseid
                    JOIN {modules} m ON m.id = cm.module AND m.name = 'quiz'
                    JOIN {quiz} q ON q.id = cm.instance
                LEFT JOIN {course_modules_completion} cmc 
                        ON cmc.userid = u.id AND cmc.coursemoduleid = cm.id
                    WHERE e.courseid = :courseid
                    AND ra.roleid = :studentrole
                    AND cm.idnumber = :idnumber
                    AND (cmc.completionstate IS NULL OR cmc.completionstate = 0)
                ", [
                    'ueactive'      => ENROL_USER_ACTIVE,
                    'eenabled'      => ENROL_INSTANCE_ENABLED,
                    'contextcourse' => CONTEXT_COURSE,
                    'courseid'      => $courseid,
                    'studentrole'   => $studentroleid,
                    'idnumber'      => $idnumber
                ]);

                if (empty($uids)) {
                    mtrace("⚠️ Không có học viên cần gửi cho đợt $batchkey.");
                    continue;
                }

                mtrace("📦 Đợt $batchkey – Course id $courseid có " . count($quizlist) . " quiz, gửi cho " . count($uids) . " học viên.");

                // Thông tin mẫu từ quiz đầu tiên
                $quiz = reset($quizlist);
                $course = $DB->get_record('course', ['id' => $quiz->course]);
                $formatted_start = userdate($course->startdate, "%d-%m-%Y");
                $deadline = userdate($quiz->timeclose, "%H:%M' ngày %d-%m-%Y");
                $closequiz = userdate($quiz->timeclose, "%d-%m-%Y");

                $template = trim(get_config('local_th_studynotify', 'quiz_reminder_template'));
                if ($template === '') {
                    $template = get_string('quiz_reminder_template_default', 'local_th_studynotify');
                }

                $quizlabel = $quiz->name;
                $dayslefttext = $daysleft == 1
                    ? get_string('quiz_due_tomorrow', 'local_th_studynotify', $quizlabel)
                    : get_string('quiz_due_in_days', 'local_th_studynotify', [
                        'daysleft' => $daysleft,
                        'quizlabel' => $quizlabel
                    ]);

                $sentcount = 0;
                $skipcount = 0;

                foreach ($uids as $uid) {
                    if (isset($sentusers[$batchkey][$uid])) {
                        $skipcount++;
                        continue;
                    }

                    $student = $DB->get_record('user', ['id' => $uid], '*', MUST_EXIST);

                    $subjectkey = 'quiz_reminder_subject' . $daysleft;

                    // Nếu không tồn tại string tương ứng → fallback
                    if (!get_string_manager()->string_exists($subjectkey, 'local_th_studynotify')) {
                        mtrace("Không tìm thấy $subjectkey, dùng subject mặc định.");
                        $subjectkey = 'quiz_reminder_subject1';
                    }

                    $subject = get_string($subjectkey, 'local_th_studynotify');

                    $subject = str_replace(
                        ['{$a}', '{$b}', '{$c}', '{$d}'],
                        [$systemname, $quizlabel, $closequiz, $formatted_start],
                        $subject
                    );

                    $message = str_replace(
                        ['{firstname}', '{lastname}', '{quizlabel}', '{startdate}', '{daysleft}', '{deadline}', '{dayslefttext}'],
                        [$student->firstname, $student->lastname, $quizlabel, $formatted_start, $daysleft, $deadline, $dayslefttext],
                        $template
                    );

                    // Gửi tin nhắn
                    $eventdata = new \core\message\message();
                    $eventdata->component = 'local_th_studynotify';
                    $eventdata->name = 'quiz_kt_reminder';
                    $eventdata->userfrom = \core_user::get_noreply_user();
                    $eventdata->userto = $student;
                    $eventdata->subject = $subject;
                    $eventdata->fullmessage = $message;
                    $eventdata->fullmessageformat = FORMAT_PLAIN;
                    $eventdata->fullmessagehtml = nl2br($message);
                    $eventdata->smallmessage = $dayslefttext;
                    $eventdata->notification = 1;

                    message_send($eventdata);

                    $sentusers[$batchkey][$uid] = true;
                    $sentcount++;
                }
                // ✅ In gọn tổng kết của đợt
                mtrace("   ✅ Đã gửi {$sentcount} mail (bỏ qua {$skipcount}) cho đợt $batchkey.");
            }
        }

        mtrace("✅ Hoàn tất gửi nhắc nhở quiz.");
    }
}