<?php
require_once($CFG->dirroot . '/mod/quiz/lib.php');
/**
 * th_send_mail_attemps_to_grading function gửi thông báo bài tập học viên cần chấm
 *
 * @return void
 */
function send_study_reminder_notification() {
    global $DB;

    $courseids_object = $DB->get_records('th_study_reminder', null, '', 'courseid');

    $ktck_shortname = get_config('local_th_study_reminder', 'config_ktck');
    $ktck_shortname = '%' .$ktck_shortname . '%';
    $final_point = get_config('local_th_study_reminder', 'config_point');

    $courses = [];
    $courseids = array_keys($courseids_object); 

    if (!empty($courseids)) {
        list($sql, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        
        $courses = $DB->get_records_sql("SELECT id, fullname FROM {course} WHERE id $sql AND visible = 1", $params);

        $students_id = [];

        foreach ($courses as $course) {
            if($course->id == 1) {
                continue;
            }
            echo "Khóa học: {$course->fullname} (ID: {$course->id})\n";
        
            // check enrol id in a course
            $enrolments = $DB->get_records('enrol', ['courseid' => $course->id], '', 'id');
        
            if (!$enrolments) {
                echo "Không có ghi danh trong khóa này.\n";
                continue;
            }
        
            // Get the student that enrol in the course and not be suspened or deleted
            // check student enrol status 
            $sql = "SELECT DISTINCT u.id
                FROM {user} u
                JOIN {user_enrolments} ue ON u.id = ue.userid
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.instanceid = e.courseid
                JOIN {role} r ON r.id = ra.roleid
                WHERE u.deleted = 0 AND u.suspended = 0
                    AND e.courseid = :courseid
                    AND r.shortname = 'student'
                    AND ctx.contextlevel = :contextlevel
                    AND ue.status = 0
                    AND e.status = 0
                    AND ue.timestart < UNIX_TIMESTAMP()
                    AND (ue.timeend = 0 OR ue.timeend > UNIX_TIMESTAMP()) -- Chưa hết hạn ghi danh
            ";
        
            $students = $DB->get_records_sql($sql, [
                'courseid' => $course->id,
                'contextlevel' => CONTEXT_COURSE,
            ]);
            
            // JOIN grade item and grade with userid and itemid 
        
            if ($students) {
                foreach ($students as $student) {
                    if (!in_array($student->id, $students_id)) {
                        $sql = "SELECT * 
                            FROM {grade_grades} AS gra
                            LEFT JOIN {grade_items} AS item ON gra.itemid = item.id
                            WHERE userid = :userid  
                                AND item.courseid = :courseid
                                AND item.idnumber LIKE :ktck
                                AND gra.finalgrade > :finalpoint";
                        $student_completed = $DB->get_records_sql($sql, [
                            'courseid' => $course->id,
                            'userid' => $student->id,
                            'ktck' => $ktck_shortname,
                            'finalpoint' => $final_point
                        ]);
                        if(!$student_completed) {
                            $students_id[] = $student->id;
                        }
                    }
                }
            }
        }
        if (!empty($students_id)) {
            foreach($students_id as $userid) {
                $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0, 'suspended' => 0], '*', MUST_EXIST);

                $messagecontent = get_config('local_th_study_reminder', 'config_notification_content');
                // Tạo thông báo
                $eventdata = new \core\message\message();
                $eventdata->component         = 'local_th_study_reminder';
                $eventdata->name              = 'reminder';
                $eventdata->userfrom          = \core_user::get_noreply_user();
                $eventdata->userto            = $user;
                $eventdata->subject           = get_string('notification_subject', 'local_th_study_reminder');
                $eventdata->fullmessage       = $messagecontent;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = $messagecontent;
                $eventdata->smallmessage      = "OM'C chúc bạn ngày mới năng lượng và nhiều niềm vui.";
                $eventdata->notification      = 1; // Đây là thông báo, không phải email

                mtrace("--> Sending a message to the user.". $user->firstname . ' ' . $user->lastname);
                if (!message_send($eventdata)) {
                    mtrace("---> Warning: Message could not be sent.");
                } else {
                    mtrace("---> Success: Message has been sent.");
                }
            }
        } else {
            mtrace("---> " . get_string('no_student', 'local_th_study_reminder'));
        }
    } else {
        mtrace("---> " . get_string('no_course', 'local_th_study_reminder'));
    }
}

/**
*lấy thời gian từ config
 */
function thcn_get_combo_duration_from_config(string $programtype, string $combo_name): ?int {
    $config = get_config('local_th_study_reminder') ?? new stdClass();

    $map_cb = property_exists($config, 'standard_months_map_cb')
        ? $config->standard_months_map_cb
        : get_config('local_th_study_reminder', 'standard_months_map_cb');

    $map_ct = property_exists($config, 'standard_months_map_ct')
        ? $config->standard_months_map_ct
        : get_config('local_th_study_reminder', 'standard_months_map_ct');
        
    $tail = strtolower(trim((string)$programtype));

    if (function_exists('str_ends_with') ? str_ends_with($tail, 'ct') : substr($tail, -2) === 'ct') {
        $map_string = $map_ct;
    } elseif (function_exists('str_ends_with') ? str_ends_with($tail, 'cb') : substr($tail, -2) === 'cb') {
        $map_string = $map_cb;
    } else {
        return null;
    }

    mtrace(" Program={$programtype}, Combo={$combo_name}");

    if (empty($map_string)) {
        mtrace(" No config for program {$programtype}");
        return null;
    }

    foreach (explode("\n", trim($map_string)) as $line) {
        if (strpos($line, ':') === false) continue;
        [$name, $months] = array_map('trim', explode(':', $line));

        if (strcasecmp($name, $combo_name) === 0) {
            return (int)$months;
        }
    }

    mtrace("No duration time for combo {$combo_name}");
    return null;
}

/**
 * lấy ct cb từ profile
 */
function thcn_get_user_program(int $userid): ?string {
    global $DB;

    static $cache = [];
    static $fieldid = 0; 
    $program = null;

    if (array_key_exists($userid, $cache)) {
        return $cache[$userid];
    }

    if (empty($fieldid)) {
        $field = $DB->get_record('user_info_field', ['shortname' => 'th_student_class']);
        if (!$field) {
            mtrace("Not found user_info_field 'th_student_class'");
            $cache[$userid] = null;
            return null;
        }
        $fieldid = $field->id;
    }

    $data = $DB->get_field('user_info_data', 'data', [
        'userid' => $userid,
        'fieldid' => $fieldid
    ]);

    if ($data === false) {
        $cache[$userid] = null;
        return null;
    }

    $program = trim((string)$data);
    if ($program === '') {
        $cache[$userid] = null;
        return null;
    }

    if (function_exists('mb_strtolower')) {
        $program = mb_strtolower($program, 'UTF-8');
    } else {
        $program = strtolower($program);
    }

    $cache[$userid] = $program;

    return $program;
}

function expected_progress(int $startTs, int $nowTs, int $totalMonths): float {
    $origTotal = $totalMonths;
    $totalMonths = max(1, (int)$totalMonths);

    if ($nowTs <= $startTs) {
        $monthsPassed = 0;
    } else {
        $start = (new DateTime())->setTimestamp($startTs);
        $now   = (new DateTime())->setTimestamp($nowTs);

        $diff = $start->diff($now);
        $monthsExact = $diff->y * 12 + $diff->m + ($diff->d > 0 ? $diff->d / 30 : 0);

        $monthsPassed = (int) floor($monthsExact);

        $monthsPassed = max(0, min($totalMonths, $monthsPassed));
    }

    $unit = 100 / $totalMonths;
    $result = round($monthsPassed * $unit, 2);
    $result = max(0, min(100, $result));

    mtrace(sprintf(
        "[expected_progress] unit=%.6f, progress_before_cap=%.2f => progress=%.2f",
        $unit, $monthsPassed * $unit, $result
    ));

    return $result;
}



function compute_combo_progress_batched(
    int $userid,
    array $courses_combo,
    int $quizmoduleid,
    array $final_quiz_idnumbers = ['kt','ess'],
    float $pass_point = 5.0
): float {
    mtrace("  [BATCH] start compute for user={$userid}");
    global $DB;

    // mtrace('  [BATCH] step1: collect shortnames from courses_combo');
    $shortnames = [];
    foreach ($courses_combo as $c) {
        $sn = isset($c['course']) ? trim((string)$c['course']) : '';
        if ($sn !== '') { $shortnames[$sn] = true; }
    }
    $shortnames = array_keys($shortnames);
    if (empty($shortnames)) {
        mtrace('   -> No shortname, skip.');
        return 0.0;
    }

    // mtrace('  [BATCH] step2: fetch course ids by shortname (unique shortnames=' . count($shortnames) . ')');
    list($inSql, $params) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
    $courseMap = $DB->get_records_select_menu('course', "shortname $inSql", $params, '', 'shortname,id');
    if (empty($courseMap)) {
        mtrace('   -> no course matched by shortname');
        return 0.0;
    }
    $courseIds = array_values($courseMap);
    $idToShortname = array_flip($courseMap);
    // mtrace('   -> mapped courses=' . count($courseIds));

    // mtrace('  [BATCH] step3: fetch quizzes with completion+criteria for mapped courses');
    list($inSql2, $p2) = $DB->get_in_or_equal($courseIds, SQL_PARAMS_NAMED);
    $p2['moduleid'] = $quizmoduleid;
    $quizRows = $DB->get_records_sql("
        SELECT q.id AS quizid, q.course AS courseid, LOWER(COALESCE(cm.idnumber, '')) AS cmidnumber
          FROM {quiz} q
          JOIN {course_modules} cm ON cm.instance = q.id AND cm.course = q.course
          JOIN {course_completion_criteria} ccc
               ON ccc.module = 'quiz'
              AND ccc.moduleinstance = cm.id
              AND ccc.criteriatype = 4
              AND ccc.course = q.course
         WHERE cm.module = :moduleid
           AND q.course $inSql2
           AND cm.completion > 0
    ", $p2);

    if (empty($quizRows)) {
        mtrace('   -> 0 quizzes found with completion/criteria');
        return 0.0;
    }

    // mtrace('  [BATCH] step4: group quizzes per course & collect quiz ids');
    $perCourse = [];
    $allQuizIds = [];
    foreach ($quizRows as $r) {
        $cid = (int)$r->courseid;
        $qid = (int)$r->quizid;
        $idnum = (string)$r->cmidnumber;
        $perCourse[$cid][] = ['quizid' => $qid, 'cmidnumber' => $idnum];
        $allQuizIds[$qid] = true;
    }
    $allQuizIds = array_keys($allQuizIds);
    // mtrace('   -> total quizzes=' . count($allQuizIds) . ', courses with quizzes=' . count($perCourse));

    if (empty($allQuizIds)) {
        mtrace('   -> no quiz ids to grade');
        return 0.0;
    }

    // mtrace('  [BATCH] step5: fetch grades for user over quizzes');
    list($inSql3, $p3) = $DB->get_in_or_equal($allQuizIds, SQL_PARAMS_NAMED);
    $p3['userid'] = $userid;
    $gradeRows = $DB->get_records_sql("
        SELECT quiz, grade
          FROM {quiz_grades}
         WHERE userid = :userid
           AND quiz $inSql3
    ", $p3);
    $gradeMap = [];
    foreach ($gradeRows as $gr) {
        $gradeMap[(int)$gr->quiz] = (float)$gr->grade;
    }

    // mtrace('  [BATCH] step6: compute per course completion');
    $total_quizzes_combo = 0;
    $passed_quizzes_combo = 0;

    $finalSet = [];
    foreach ($final_quiz_idnumbers as $fx) { $finalSet[strtolower($fx)] = true; }

    foreach ($perCourse as $courseId => $items) {
        $courseShortname = $idToShortname[$courseId] ?? '';
        // mtrace("    - course={$courseShortname} (id={$courseId}), quizzes=" . count($items));

        $total = count($items);
        $practice_total = 0;
        $practice_all_passed = true;
        $final_total = 0;
        $final_all_passed = true;
        $passed_individual = 0;

        foreach ($items as $it) {
            $qid = (int)$it['quizid'];
            $idnum = strtolower((string)$it['cmidnumber']);
            $grade = $gradeMap[$qid] ?? null;
            $passed = ($grade !== null && $grade >= $pass_point);

            $is_final = isset($finalSet[$idnum]);
            if ($is_final) {
                $final_total++;
                if (!$passed) { $final_all_passed = false; }
            } else {
                $practice_total++;
                if (!$passed) { $practice_all_passed = false; }
            }
            if ($passed) { $passed_individual++; }
        }

        $course_completed =
            (($practice_total > 0) && $practice_all_passed) ||
            (($final_total > 0) && $final_all_passed);

        // mtrace(
        //     "      summary: practice_total={$practice_total}, practice_all_passed=" . ($practice_all_passed?'YES':'NO') .
        //     ", final_total={$final_total}, final_all_passed=" . ($final_all_passed?'YES':'NO') .
        //     ", completed=" . ($course_completed?'YES':'NO')
        // );

        $total_quizzes_combo += $total;
        if ($course_completed) {
            $passed_quizzes_combo += $total;
        } else {
            $passed_quizzes_combo += $passed_individual;
        }
    }

    if ($total_quizzes_combo === 0) {
        mtrace('  [BATCH] done: total_quizzes_combo=0');
        return 0.0;
    }

    $percent = round(($passed_quizzes_combo / $total_quizzes_combo) * 100, 2);
    mtrace('  [BATCH] done: total=' . $total_quizzes_combo . ', passed=' . $passed_quizzes_combo . ' => ' . $percent . '%');
    return $percent;
}

?>