<?php
require('../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_login();

global $DB;

$userid = required_param('userid', PARAM_INT);
$quizid = required_param('quizid', PARAM_INT);
$grade = required_param('grade', PARAM_FLOAT);
$attemptid = required_param('attemptid', PARAM_INT);

$quiz = $DB->get_record('quiz', ['id' => $quizid], '*', MUST_EXIST);
$courseid = $quiz->course;

$gradeitem = $DB->get_record('grade_items', [
    'courseid' => $courseid,
    'idnumber' => 'pk'
]);

if (!$gradeitem) {
    redirect(
        new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => 'waitingreview']),
        get_string('gradeitem_not_found_by_idnumber', 'local_th_online_exam_manager', 'pk'),
        2
    );
}

// Lấy điểm cũ trước khi cập nhật
$oldgrade = $DB->get_field('grade_grades', 'finalgrade', [
    'itemid' => $gradeitem->id,
    'userid' => $userid,
]);
// So sánh điểm mới với điểm cũ, nếu giống thì bỏ qua
$oldgrade = is_null($oldgrade) ? null : round((float)$oldgrade, 2);
$grade = round($grade, 2);

$gradeitemobj = \grade_item::fetch(['id' => $gradeitem->id]);

if ($gradeitemobj->is_calculated() || $gradeitemobj->is_locked()) {
    redirect(
        new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => 'waitingreview']),
        get_string('cannot_update_grade_skipped', 'local_th_online_exam_manager'),
        2
    );
}

try {
    $gradeitemobj->update_final_grade($userid, $grade, 'th_online_exam_manager');
    $DB->execute(
        "UPDATE {th_exam_regrade_requests} SET status = :status WHERE attemptid = :attemptid",
        ['status' => 'reviewed', 'attemptid' => $attemptid]
    );
    redirect(
        new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => 'reviewed']),
        get_string('grade_update_success', 'local_th_online_exam_manager'),
        2
    );
} catch (\Exception $e) {
    redirect(
        new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => 'waitingreview']),
        get_string('grade_update_error', 'local_th_online_exam_manager'),
        2
    );
}


