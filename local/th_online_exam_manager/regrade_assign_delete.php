<?php
require_once('../../config.php');
require_login();

$id = required_param('id', PARAM_INT);
$status = 'waiting';

// Lấy bản ghi phúc khảo
$record = $DB->get_record('th_exam_regrade_assign', ['id' => $id], '*', MUST_EXIST);

// Lấy context từ quiz (hoặc có thể từ course nếu bạn muốn đơn giản hóa)
$cm = get_coursemodule_from_instance('quiz', $record->quizid, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);

require_capability('local/th_online_exam_manager:view', $context);
$PAGE->set_cm($cm);
$PAGE->set_url(new moodle_url('/local/th_online_exam_manager/regrade_assign_delete.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('deleteregraderequesttitle', 'local_th_online_exam_manager'));
$PAGE->set_heading(get_string('deleteregraderequesttitle', 'local_th_online_exam_manager'));

if (optional_param('confirm', false, PARAM_BOOL) && confirm_sesskey()) {
    $DB->delete_records('th_exam_regrade_assign', ['id' => $id]);
    redirect(new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => $status]), get_string('deletedsuccessfully', 'local_th_online_exam_manager'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('confirmdeleterequest', 'local_th_online_exam_manager'));

$yesurl = new moodle_url('/local/th_online_exam_manager/regrade_assign_delete.php', [
    'id' => $id,
    'confirm' => 1,
    'sesskey' => sesskey()
]);
$cancelurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => $status]);

echo $OUTPUT->confirm(get_string('actioncannotbeundone', 'local_th_online_exam_manager'), $yesurl, $cancelurl);
echo $OUTPUT->footer();
