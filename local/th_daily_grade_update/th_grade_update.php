<?php
require('../../config.php');
require_once($CFG->dirroot.'/local/th_daily_grade_update/open_course_form.php');

require_login();

$context = context_system::instance();
require_capability('local/th_daily_grade_update:view', $context);

$PAGE->set_url(new moodle_url('/local/th_daily_grade_update/viewlog.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('daily_update_custom_grades', 'local_th_daily_grade_update'));
$PAGE->set_heading(get_string('daily_update_custom_grades', 'local_th_daily_grade_update'));

global $DB, $CFG;
use core_completion\progress;

$selectedweekday = get_config('local_th_daily_grade_update', 'select_weekday');
$currentweekday = date('w');

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir. '/completionlib.php');
$ccidnumber = get_config('local_th_daily_grade_update', 'cc1_idnumber') ?? 'bg';
$selectedmonth = get_config('local_th_daily_grade_update', 'select_month') ?: 6;
$selectedday_delay = get_config('local_th_daily_grade_update', 'select_day_delay') ?: 0;
$completion_type = get_config('local_th_daily_grade_update', 'completion') ?: 'all';
$timefrom = strtotime("-{$selectedmonth} months");

$mform = new local_th_open_course_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/my/'));
} else if ($data = $mform->get_data()) {

    $startdate = $data->opendate;
    $sql = "SELECT c.*
                FROM {course} c
            WHERE c.visible = 1
                AND c.startdate >= :timefrom AND c.startdate = :startdate";

    $params = [
    'timefrom'     => $timefrom,
    'startdate' => $startdate,
    ];

    $courses = $DB->get_records_sql($sql, $params);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('daily_update_custom_grades', 'local_th_daily_grade_update'));

    if($courses) {
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
                    $completed = 0;
                    foreach ($scormmodules as $scormcm) {
                        $cminfo = $completion->get_data($scormcm, true, $student->id);
                        if($cminfo->completionstate == COMPLETION_COMPLETE || $cminfo->completionstate == COMPLETION_COMPLETE_PASS) {
                            $completed++;
                        }
                    }
    
                    $percent = ($completed / $total) * 100;

                    $rawscore = $percent / 10.0;
                    
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
                    continue;
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

        echo $OUTPUT->notification(
            get_string('update_success', 'local_th_daily_grade_update'),
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        echo $OUTPUT->notification(
            get_string('update_warning', 'local_th_daily_grade_update'),
            \core\output\notification::NOTIFY_WARNING
        );
    }
    
    $mform->display();
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('daily_update_custom_grades', 'local_th_daily_grade_update'));
    $mform->display();
    echo $OUTPUT->footer();
}