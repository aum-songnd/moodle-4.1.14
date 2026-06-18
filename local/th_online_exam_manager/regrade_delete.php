<?php
require_once('../../config.php');
require_login();

$id = required_param('id', PARAM_INT);
$status = required_param('status', PARAM_TEXT);

// Lấy bản ghi phúc khảo
$record = $DB->get_record('th_exam_regrade_requests', ['id' => $id], '*', MUST_EXIST);
$context = context_system::instance();

require_capability('local/th_online_exam_manager:manager_regrade', $context);
$PAGE->set_url(new moodle_url('/local/th_online_exam_manager/regrade_delete.php', ['id' => $id, 'status' => $status]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('deleteregraderequesttitle', 'local_th_online_exam_manager'));
$PAGE->set_heading(get_string('deleteregraderequesttitle', 'local_th_online_exam_manager'));

if ($record->status === 'reviewed' || $record->status === 'rejected') {
    print_error('cannotdeletereviewed', 'local_th_online_exam_manager');
}

if (optional_param('confirm', false, PARAM_BOOL) && confirm_sesskey()) {
    $DB->delete_records('th_exam_regrade_requests', ['id' => $id]);
    redirect(new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => $status]), get_string('deletedsuccessfully', 'local_th_online_exam_manager'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('confirmdeleterequest', 'local_th_online_exam_manager'));

$yesurl = new moodle_url('/local/th_online_exam_manager/regrade_delete.php', [
    'id' => $id,
    'status' => $status,
    'confirm' => 1,
    'sesskey' => sesskey()
]);
$cancelurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => $status]);

echo $OUTPUT->confirm(get_string('actioncannotbeundone', 'local_th_online_exam_manager'), $yesurl, $cancelurl);
echo $OUTPUT->footer();
