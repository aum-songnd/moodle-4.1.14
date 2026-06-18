<?php
namespace local_th_daily_grade_update\task;

use core_completion\progress;

defined('MOODLE_INTERNAL') || die();

class daily_update_custom_grades extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('daily_update_custom_grades', 'local_th_daily_grade_update');
    }

    public function execute() {
        global $DB, $CFG;

        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->libdir. '/completionlib.php');
        $ccidnumber = get_config('local_th_daily_grade_update', 'cc1_idnumber') ?? 'bg';
        $selectedmonth = get_config('local_th_daily_grade_update', 'select_month') ?: 6;
        $selectedday_delay = get_config('local_th_daily_grade_update', 'select_day_delay') ?: 0;
        $completion_type = get_config('local_th_daily_grade_update', 'completion') ?: 'all';
        $lt1idnumber = get_config('local_th_daily_grade_update', 'lt1_idnumber') ?? 'cc1';
        $thidnumber  = get_config('local_th_daily_grade_update', 'th_idnumber') ?? 'th';
        $enddate_method = get_config('local_th_daily_grade_update', 'enddate_method') ?? 'cc1';
        $graderounding = get_config('local_th_daily_grade_update', 'graderounding') ?? 'round';

        $timefrom = strtotime("-{$selectedmonth} months");

        if($enddate_method == 'cc1') {
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
                'quizidnumber' => $lt1idnumber,
                'day_delay' => $selectedday_delay,
                'now'          => time(),
                ];
        } else if ($enddate_method == 'th') {
            $sql = "SELECT c.*, rd.duedate as time_expired_update
                FROM {course} c
                JOIN {thrdassign} rd ON rd.course = c.id
                JOIN {grade_items} gi ON gi.iteminstance = rd.id
                                    AND gi.itemmodule = 'thrdassign'
                                    AND gi.itemtype = 'mod'
            WHERE c.visible = 1
                AND c.startdate >= :timefrom
                AND gi.idnumber = :thidnumber
                AND (rd.duedate + (:day_delay * 86400)) > :now";

            $params = [
                'timefrom'     => $timefrom,
                'thidnumber' => $thidnumber,
                'day_delay' => $selectedday_delay,
                'now'          => time(),
            ];
        }
        
        $courses = $DB->get_records_sql($sql, $params);
        
        foreach ($courses as $course) {
            $gradeitem = $DB->get_record('grade_items', [
                'courseid' => $course->id,
                'idnumber' => $ccidnumber
            ]);
            if (!$gradeitem) {
                continue;
            }

            $context = \context_course::instance($course->id);
            $roleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
            $students= get_role_users($roleid, $context);
            
            if($completion_type == 'scorm'){
                $completion = new \completion_info($course);
                $scormmodules = $DB->get_records_sql("
                    SELECT cm.*
                    FROM {course_modules} cm
                    JOIN {modules} m ON m.id = cm.module
                    WHERE cm.course = :courseid
                    AND m.name = 'scorm'
                ", ['courseid' => $course->id]);

                $total = count($scormmodules);
                if($total == 0) {
                    continue;
                }
            }

            foreach ($students as $student) {

                if ($completion_type == 'all') {
                    $percent = progress::get_course_progress_percentage($course, $student->id);
                    if ($percent === null) {
                        continue;
                    }

                    $score = 0;
                    if ($percent >= 80) {
                        $score = 10;
                    } elseif ($percent >= 50) {
                        $score = 5;
                    }
                } else if ($completion_type == 'scorm') {
                    $time_expired_update = $course->time_expired_update;
                    $completed = 0;
                    foreach ($scormmodules as $scormcm) {
                        $cminfo = $completion->get_data($scormcm, true, $student->id);
                        if(($cminfo->completionstate == COMPLETION_COMPLETE || $cminfo->completionstate == COMPLETION_COMPLETE_PASS) && $cminfo->timemodified <= $time_expired_update + 59) {
                            $completed++;
                        }
                    }

                    $percent = ($completed / $total) * 100;

                    $rawscore = $percent / 10.0;
                    
                    if($graderounding == 'round') {
                        // Làm tròn đến 1 chữ số thập phân
                        $score = round($rawscore, 1);
                    } else if($graderounding == 'round_05') {
                        $int = floor($rawscore);
                        $frac = $rawscore - $int;
                        $eps  = 1e-7;

                        if (abs($frac - 0.5) < $eps) {
                            $score = $int + 0.5;          // giữ đúng .5
                        } elseif ($frac < 0.5) {
                            $score = (float)$int;         // làm tròn xuống .0
                        } else {
                            $score = (float)($int + 1);   // làm tròn lên .0 kế tiếp
                        }
                    } else {
                        break;
                    }
                } else {
                    break;
                }
                
                // Lấy điểm cũ trước khi cập nhật
                $oldgrade = $DB->get_field('grade_grades', 'finalgrade', [
                    'itemid' => $gradeitem->id,
                    'userid' => $student->id,
                ]);
                // So sánh điểm mới với điểm cũ, nếu giống thì bỏ qua
                $oldgrade = is_null($oldgrade) ? null : round((float)$oldgrade, 2);
                $score = round($score, 2);

                if ($oldgrade !== null && abs($oldgrade - $score) < 0.00001) {
                    // Không cần update, không ghi log
                    continue;
                }

                $gradeitemobj = \grade_item::fetch(['id' => $gradeitem->id]);

                if ($gradeitemobj->is_calculated() || $gradeitemobj->is_locked()) {
                    // Không update được, bỏ qua
                    continue;
                }

                try {
                    $gradeitemobj->update_final_grade($student->id, $score, 'local_th_daily_grade_update');
                    $status = 'success';
                } catch (\Exception $e) {
                    $status = 'fail';
                }
                // Ghi log chi tiết
                $DB->insert_record('local_th_daily_grade_log', [
                    'timecreated' => time(),
                    'userid' => $student->id,
                    'courseid' => $course->id,
                    'gradeitemid' => $gradeitem->id,
                    'oldgrade' => $oldgrade,
                    'newgrade' => $score,
                    'status' => $status,
                ]);
            }
        }
    }
}
