<?php 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Generate an Excel file from quiz attempts.
 *
 * @param array $attempts List of attempt objects (must contain firstname, lastname, username, timestart, timefinish).
 * @return string Full file path to the generated .xlsx file.
 */
function generate_excel_file($attempts) {
    global $CFG;

    require_once($CFG->libdir . '/phpspreadsheet/vendor/autoload.php');

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Quiz Submissions");

    // Set header
    $headers = ['Họ và đệm/Tên', 'Tên đăng nhập', 'Trạng thái', 'Bắt đầu vào lúc', 'Được hoàn thành', 'Thời gian thực hiện', 'Điểm/10,00', 'Link bài chấm'];
    $sheet->fromArray($headers, NULL, 'A1');

    $row = 2;
    foreach ($attempts as $attempt) {
        $fullname = $attempt->firstname ?? ''.' '. $attempt->lastname ?? '';

        $start = $attempt->timestart;
        $finish = $attempt->timefinish;
        $duration = $finish - $start;

        $durationstr = ($duration < 60) ? $duration . ' giây' : format_time($duration);

        $sheet->setCellValue("A$row", $fullname);
        $sheet->setCellValue("B$row", $attempt->username);
        $sheet->setCellValue("C$row", 'Đã hoàn thành');
        $sheet->setCellValue("D$row", userdate($start, '%d tháng %m %Y %H:%M'));
        $sheet->setCellValue("E$row", userdate($finish, '%d tháng %m %Y %H:%M'));
        $sheet->setCellValue("F$row", $durationstr);
        $sheet->setCellValue("G$row", 'Chưa được chấm điểm');
        $sheet->setCellValue("H$row", $attempt->link);
        $row++;
    }
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->getColumnDimension('D')->setAutoSize(true);
    $sheet->getColumnDimension('E')->setAutoSize(true);
    $sheet->getColumnDimension('F')->setAutoSize(true);
    $sheet->getColumnDimension('G')->setAutoSize(true);
    $sheet->getColumnDimension('H')->setAutoSize(true);

    // Save to tempdir
    $filename = 'quiz_report_' . date('Ymd_His') . '.xlsx';
    $filepath = $CFG->tempdir . '/' . $filename;

    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);

    return $filepath;
}

/**
 * th_send_mail_attemps_to_grading function gửi thông báo bài tập học viên cần chấm
 *
 * @return void
 */
function send_notification() {
    global $DB, $CFG;

    mtrace("==== START: Send email to notify users about submissions ====");

    $now = time();
    $one_day = 24 * 60 * 60;

    // Step 1: Get selected course IDs
    mtrace("Step 1: Checking courses to look up for quiz attempts...");
    $records = $DB->get_records('th_submission_notify_courses');
    $course_ids = array_column($records, 'courseid');

    if (empty($course_ids)) {
        mtrace("----> No courses selected for notification. Exit.");
        return;
    }

    $mail_str = get_config('local_th_submission_notify','emaillist');
    if (empty($mail_str)) {
        mtrace("----> No email recipients configured. Exit.");
        return;
    }

    list($course_sql, $course_params) = $DB->get_in_or_equal($course_ids, SQL_PARAMS_NAMED);

    // Step 2: Get quiz attempts that are finished, not graded, and belong to selected courses
    mtrace("Step 2: Fetching ungraded quiz attempts from selected courses...");
    $sql = "SELECT qa.id AS attemptid, qa.timestart, qa.timefinish, qa.state, qa.sumgrades,
                   u.id AS userid, u.username, u.firstname, u.lastname, cm.id AS cmid
            FROM {quiz_attempts} qa
            JOIN {user} u ON u.id = qa.userid
            JOIN {quiz} q ON q.id = qa.quiz
            JOIN {course_modules} cm ON cm.instance = q.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
            WHERE qa.state = 'finished' 
              AND qa.sumgrades IS NULL 
              AND qa.preview = 0
              AND (cm.idnumber LIKE '%ess%' OR cm.idnumber LIKE '%rec%')
              AND q.course $course_sql";
    $attempts = $DB->get_records_sql($sql, $course_params);

    if (!$attempts) {
        mtrace("----> No quiz attempts found. Exit.");
        return;
    }

    mtrace("----> Found " . count($attempts) . " total ungraded quiz attempts.");

    // Step 3: Filter out attempts that were already notified
    mtrace("Step 3: Filtering attempts that have already been logged...");
    list($in_sql, $in_params) = $DB->get_in_or_equal(array_keys($attempts), SQL_PARAMS_NAMED);
    $existing_logs = $DB->get_records_select('th_submission_notify_logs', "attemptid $in_sql", $in_params);
    $sent_attempt_ids = array_column($existing_logs, 'attemptid');

    $new_created = 0;
    $backlog = 0;
    $log_to_insert = [];

    foreach ($attempts as $attempt) {

        $timediff = $now - $attempt->timefinish;
        $already_notified = in_array($attempt->attemptid, $sent_attempt_ids);

        if ($timediff <= $one_day && !$already_notified) {
            $new_created++;
            $log_to_insert[] = (object)[
                'attemptid' => $attempt->attemptid,
                'timesent' => $now
            ];
        } else {
            $backlog++;
        }

        $attempt->link = $CFG->wwwroot.'/mod/quiz/review.php?attempt='.$attempt->attemptid . '&cmid='.$attempt->cmid;

    }

    mtrace("----> New submissions in last 24h: {$new_created}");
    mtrace("----> Backlog submissions (older than 24h): {$backlog}");

    if ($new_created == 0 && $backlog == 0) {
        mtrace("----> No new or backlog attempts to notify. Exit.");
        return;
    }

    // Step 4: Get email list
    mtrace("Step 4: Fetching configured email recipients...");
    
    $mail_lists = array_filter(array_map('trim', explode(',', $mail_str)), function($email) {
        return !empty($email) && validate_email($email);
    });
    
    if (empty($mail_lists)) {
        mtrace("----> No valid emails found in config. Exit.");
        return;
    }
    
    list($sql, $params) = $DB->get_in_or_equal($mail_lists, SQL_PARAMS_NAMED);
    $users = $DB->get_records_sql("SELECT * FROM {user} WHERE email $sql AND deleted = 0 AND suspended = 0", $params);

    if (count($users) == 0) {
        mtrace("----> No user found. Exit.");
        return;
    }

    mtrace("----> Found " . count($users) . " users to notify.");

    // Step 5: Send emails
    $from = core_user::get_support_user();
    $subject = (isset($CFG->supportname) ? "[" . $CFG->supportname . "] " : "") . get_string('subject', 'local_th_submission_notify');

    mtrace("Step 5: Sending email to users...");

    // Generate Excel report from current attempts
    $filepath = generate_excel_file($attempts);

    // Send email with attachment (if supported, or log path)
    mtrace("Attached report: {$filepath}");

    $a = (object)[
        'date' => date('d/m/Y', $now),
        'newcreate' => $new_created,
        'backlog' => $backlog
    ];
    $message = get_string('message', 'local_th_submission_notify', $a);

    foreach ($users as $user) {
        
        $result = email_to_user($user, $from, $subject, $message, '', $filepath, basename($filepath));

        if ($result) {
            mtrace("---> Email sent to: {$user->firstname} {$user->lastname} <{$user->email}>");
        } else {
            mtrace("---> FAILED to send email to: {$user->firstname} {$user->lastname} <{$user->email}>");
        }
    }

    

    // Step 6: Insert log entries to prevent duplicate notifications
    mtrace("Step 6: Inserting log entries...");
    foreach ($log_to_insert as $log) {
        $DB->insert_record('th_submission_notify_logs', $log);
    }

    mtrace("----> Inserted " . count($log_to_insert) . " log entries.");
    mtrace("==== DONE ====");
}

function send_result_to_student(array $assign_ids) {
    global $DB, $CFG, $PAGE;

    if (!isset($PAGE->context)) {
        $PAGE->set_context(context_system::instance());
    }

    $users = [];
    
    foreach ($assign_ids as $id) {
        $assign_sql = "SELECT * FROM {block_th_assign_grading} WHERE id = :id";
        $assign_row = $DB->get_record_sql($assign_sql, ['id' => $id]);
        if (!$assign_row) {
            continue;
        }
        $attempt = $DB->get_record_sql("SELECT qa.id AS attemptid, ROUND(qa.sumgrades / q.sumgrades * q.grade, 2) AS sumgrades,
                   u.id AS userid, cm.id AS cmid, cm.idnumber, cm.course
            FROM {quiz_attempts} qa
            JOIN {user} u ON u.id = qa.userid
            JOIN {quiz} q ON q.id = qa.quiz
            JOIN {course_modules} cm ON cm.instance = q.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
            WHERE qa.id = :attemptid
              AND qa.state = 'finished'
              AND qa.preview = 0
              AND (cm.idnumber LIKE '%ess%' OR cm.idnumber LIKE '%rec%')", ['attemptid' => $assign_row->attemptid]);

        if (!$attempt) {
            continue;
        }
        
        $userid = $attempt->userid;
        $courseid = $attempt->course;
        $currenttype = (strpos($attempt->idnumber, 'ess') !== false) ? 'ess' : 'rec';
        $othertype = ($currenttype === 'ess') ? 'rec' : 'ess';
        
        $otherconfirmed = $DB->get_record_sql("SELECT qa.id AS attemptid, ROUND(qa.sumgrades / q.sumgrades * q.grade, 2) AS sumgrades, cm.id AS cmid, cm.idnumber
            FROM {block_th_assign_grading} bg
            JOIN {quiz_attempts} qa ON qa.id = bg.attemptid
            JOIN {quiz} q ON q.id = qa.quiz
            JOIN {course_modules} cm ON cm.instance = q.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
            WHERE bg.confirm_grading = 1
              AND qa.userid = :userid
              AND q.course = :courseid
              AND cm.idnumber LIKE :othertype
        ", [
            'userid' => $userid,
            'courseid' => $courseid,
            'othertype' => "%$othertype%"
        ]);

        if(!$otherconfirmed) {
            th_notify_log_save((object)['id'=>$userid], $courseid, $currenttype === 'ess' ? $attempt->sumgrades : null, $currenttype === 'rec' ? $attempt->sumgrades : null, null, null, 0, 'pending_other');
            continue;
        }

        // Fetch 2 other exam result
        $otherexams = $DB->get_records_sql("SELECT qa.id AS attemptid, ROUND(qa.sumgrades / q.sumgrades * q.grade, 2) AS sumgrades,
                   u.id AS userid, cm.id AS cmid, cm.idnumber
            FROM {quiz_attempts} qa
            JOIN {user} u ON u.id = qa.userid
            JOIN {quiz} q ON q.id = qa.quiz
            JOIN {course_modules} cm ON cm.instance = q.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
            WHERE qa.userid = :userid
              AND q.course = :courseid
              AND qa.preview = 0
              AND (cm.idnumber LIKE '%reading%' OR cm.idnumber LIKE '%listening%')", [
            'userid' => $userid,
            'courseid' => $courseid,
        ]);

        
        $reading = null;
        $listening = null;
        if (!empty($otherexams)) {
            foreach ($otherexams as $exam) {
                if (stripos($exam->idnumber, 'reading') !== false && is_numeric($exam->sumgrades)) {
                    $reading = $exam->sumgrades;
                } elseif (stripos($exam->idnumber, 'listening') !== false && is_numeric($exam->sumgrades)) {
                    $listening = $exam->sumgrades;
                }
            }
        }

        $essid = ($currenttype === 'ess') ? $attempt->attemptid : $otherconfirmed->attemptid;
        $recid = ($currenttype === 'rec') ? $attempt->attemptid : $otherconfirmed->attemptid;
        $summaryid = md5("{$userid}_{$essid}_{$recid}");

        $users[] = (object)[
            'userid' => $userid,
            'courseid' => $courseid,
            "$currenttype" => $attempt->sumgrades,
            "$othertype" => $otherconfirmed->sumgrades,
            'reading' => $reading,
            'listening' => $listening,
            'summaryid' => $summaryid
        ];
        
        
    }

    // Step 5: Send emails
    $from = core_user::get_support_user();
    $subject = (isset($CFG->supportname) ? "[" . $CFG->supportname . "] " : "") . get_string('subject_student', 'local_th_submission_notify');

    foreach ($users as $userResult) {

        if ($DB->record_exists('local_th_sub_result_logs', [
            'summaryhash' => $userResult->summaryid,
            'status' => 'sent'
        ])) {
            error_log("[SKIP] Already sent for user {$userResult->userid} course {$userResult->courseid}");
            continue;
        }
        
        $user = $DB->get_record('user', ['id' => $userResult->userid], '*', MUST_EXIST);
        
        $ess = $userResult->ess ?? null;
        $rec = $userResult->rec ?? null;
        $reading = $userResult->reading ?? null;
        $listening = $userResult->listening ?? null;
    
        $lines = [];
    
        if (!is_null($listening)) {
            $lines[] = "Nghe: $listening.";
        }
    
        if (is_null($ess) || is_null($rec)) {
            th_notify_log_save($user, $userResult->courseid ?? 0, $ess, $rec, $reading, $listening, 0, 'pending_other');
            continue;
        }

        $lines[] = "Nói: $rec.";
    
        if (!is_null($reading)) {
            $lines[] = "Đọc: $reading.";
        }
    
        $lines[] = "Viết: $ess.";
        $idnumber = get_config('local_th_submission_notify','config_averagescore');
        if(!$idnumber) {
            $idnumber = 'trinhdotrungbinh';
        }

        $cefr_sql = "
            SELECT gg.finalgrade, gl.letter
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gi.id = gg.itemid
            JOIN {context} ctx ON ctx.instanceid = gi.courseid AND ctx.contextlevel = 50
            JOIN {grade_letters} gl ON gl.contextid = ctx.id
            WHERE gg.userid = :userid
                AND gi.courseid = :courseid
                AND gi.idnumber LIKE :idnumber
                AND gg.finalgrade IS NOT NULL
                AND (gg.finalgrade / gi.grademax) * 100 >= gl.lowerboundary
                AND NOT EXISTS (
                    SELECT 1
                    FROM {grade_letters} gl2
                    WHERE gl2.contextid = ctx.id
                        AND (gg.finalgrade / gi.grademax) * 100 >= gl2.lowerboundary
                        AND gl2.lowerboundary > gl.lowerboundary
                )
            LIMIT 1
        ";
        $cefr_score = $DB->get_record_sql($cefr_sql, [
            'userid' => $userResult->userid,
            'courseid' => $userResult->courseid,
            'idnumber' => $idnumber
        ]) ?: '';

        $a = (object)[
            'studentname' => fullname($user),
            'scores' => implode("\n", $lines),
            'average_score' => $cefr_score ? round($cefr_score->finalgrade, 2) : 0,
            'cefr_score' => $cefr_score->letter ?? ' ',
        ];
        
        $message = get_string('message_student', 'local_th_submission_notify', $a);

        $result = email_to_user($user, core_user::get_support_user(), $subject, $message);
    
        if ($result) {
            th_notify_log_save($user, $userResult->courseid ?? 0, $ess, $rec, $reading, $listening, round($cefr_score->finalgrade, 2), 'sent', $userResult->summaryid);
        } else {
            th_notify_log_save($user, $userResult->courseid ?? 0, $ess, $rec, $reading, $listening, round($cefr_score->finalgrade, 2), 'failed', $userResult->summaryid);
        }
    }
}

function th_notify_log_save($user, $courseid, $ess, $rec, $reading, $listening, $average, $status, $summaryid = null) {
    global $DB;

    $log = new stdClass();
    $log->userid = $user->id;
    $log->courseid = $courseid;
    $log->ess = $ess;
    $log->rec = $rec;
    $log->reading = $reading;
    $log->listening = $listening;
    $log->average = $average;
    $log->status = $status;
    $log->timesent = time();
    $log->summaryhash = $summaryid;

    $DB->insert_record('local_th_sub_result_logs', $log);
}

?>