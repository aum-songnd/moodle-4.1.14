<?php
namespace local_th_daily_grade_update\task;

defined('MOODLE_INTERNAL') || die();

class daily_update_qaa_grades extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('daily_update_qaa_grades', 'local_th_daily_grade_update');
    }

    public function execute() {
        global $DB, $CFG;
        require_once($CFG->libdir . '/gradelib.php');

        $qaaidnumber = get_config('local_th_daily_grade_update', 'qaa_idnumber') ?? 'qa';
        $dcc3 = get_config('local_th_daily_grade_update', 'cc3_idnumber') ?? 'hd';
        $selectedmonth = get_config('local_th_daily_grade_update', 'select_month') ?: 6;
        $selectedday_delay = get_config('local_th_daily_grade_update', 'select_day_delay') ?: 0;
        $timefrom = strtotime("-{$selectedmonth} months");

        $sql = "SELECT c.*
                FROM {course} c
                JOIN {quiz} q ON q.course = c.id
                JOIN {grade_items} gi ON gi.iteminstance = q.id
                                    AND gi.itemmodule = 'quiz'
                                    AND gi.itemtype = 'mod'
                WHERE c.visible = 1
                AND c.startdate >= :timefrom
                AND gi.idnumber = :quizidnumber
                AND (q.timeclose + (:day_delay * 86400)) > :now";

        $params = [
            'timefrom'     => $timefrom,
            'quizidnumber' => 'cc.1',
            'day_delay' => $selectedday_delay,
            'now'          => time(),
        ];

        $courses = $DB->get_records_sql($sql, $params);

        $time = time();
        foreach ($courses as $course) {
            $qaa_grade_item_source = $DB->get_record('grade_items', [
                'courseid' => $course->id,
                'idnumber' => $qaaidnumber
            ]);
            if (!$qaa_grade_item_source) {
                continue;
            }
            $qaa_grade_item_target = $DB->get_record('grade_items', [
                'courseid' => $course->id,
                'idnumber' => $dcc3
            ]);

            if (!$qaa_grade_item_target) {
                continue;
            }

            $context = \context_course::instance($course->id);
            $students = get_enrolled_users($context, 'moodle/grade:view');

            // Lấy tất cả các qaa id của khóa học
            $qaaids = $DB->get_fieldset_select('qaa', 'id', 'course = ?', [$course->id]);
            if (empty($qaaids)) {
                continue;
            }

            list($in_sql, $params) = $DB->get_in_or_equal($qaaids, SQL_PARAMS_NAMED, 'qaaid');

            foreach ($students as $student) {
                $studentparams = array_merge($params, ['studentid' => $student->id]);

                // Đếm số lần học viên đặt câu hỏi
                $questioncount = $DB->count_records_select('qaapairs',
                    "qaaid $in_sql AND studentid = :studentid AND question IS NOT NULL AND TRIM(question) <> ''",
                    $studentparams
                );

                $score = 0;
                if ($questioncount >= 8) {
                    $score = 10;
                } elseif ($questioncount >= 5) {
                    $score = 5;
                }
                $score = round($score, 2);

                // Kiểm tra log gần nhất
                $logs = $DB->get_records('local_th_daily_grade_log', [
                    'userid' => $student->id,
                    'courseid' => $course->id,
                    'gradeitemid' => $qaa_grade_item_target->id
                ], 'timecreated DESC', '*', 0, 1);
                $lastlog = $logs ? reset($logs) : null;

                if ($lastlog && round((float)$lastlog->newgrade, 2) === $score) {
                    continue;
                }

                // Kiểm tra điểm hiện tại
                $oldgrade = $DB->get_field('grade_grades', 'finalgrade', [
                    'itemid' => $qaa_grade_item_target->id,
                    'userid' => $student->id
                ]);
                $oldgrade = is_null($oldgrade) ? null : round((float)$oldgrade, 2);

                if (is_null($oldgrade) || abs($oldgrade - $score) > 0.00001) {
                    $gradeitemobj = \grade_item::fetch(['id' => $qaa_grade_item_target->id]);

                    if ($gradeitemobj->is_calculated() || $gradeitemobj->is_locked()) {
                        mtrace("Skipped: grade item is either calculated or locked.");
                        continue;
                    }

                    try {
                        // Đảm bảo grademax lớn hơn hoặc bằng điểm mới
                        if ($qaa_grade_item_target->grademax < $score) {
                            $qaa_grade_item_target->grademax = $score;
                            $DB->update_record('grade_items', $qaa_grade_item_target);
                            $gradeitemobj = \grade_item::fetch(['id' => $qaa_grade_item_target->id]);
                        }

                        // Cập nhật điểm vào cc3
                        $gradeitemobj->update_final_grade($student->id, $score, 'local_th_daily_grade_update');

                        // Sau khi update, lấy lại điểm thực tế
                        $newgrade = $DB->get_field('grade_grades', 'finalgrade', [
                            'itemid' => $qaa_grade_item_target->id,
                            'userid' => $student->id
                        ]);
                        $newgrade = is_null($newgrade) ? null : round((float)$newgrade, 2);
                        $status = 'success';
                    } catch (\Exception $e) {
                        $newgrade = $oldgrade;
                        $status = 'fail';
                        mtrace("Grade update failed for user {$student->id}: " . $e->getMessage());
                    }

                    // Ghi log theo grade item cc3
                    $DB->insert_record('local_th_daily_grade_log', [
                        'timecreated'  => $time,
                        'userid'       => $student->id,
                        'courseid'     => $course->id,
                        'gradeitemid'  => $qaa_grade_item_target->id,
                        'oldgrade'     => $oldgrade,
                        'newgrade'     => $newgrade,
                        'status'       => $status
                    ]);
                }
            }
        }
    }
}
