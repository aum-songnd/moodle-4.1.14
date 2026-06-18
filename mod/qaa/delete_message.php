<?php
// delete_message.php - delete a threaded message (single-table: qaapairs)

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once $CFG->dirroot . '/lib/grade/grade_item.php';
require_once $CFG->dirroot . '/mod/qaa/lib.php';

global $DB, $USER, $OUTPUT, $PAGE;

// Params
$cmid    = required_param('id', PARAM_INT);    // course module id
$mid     = required_param('mid', PARAM_INT);   // qaapairs.id (message to delete)
$rootid  = optional_param('rootid', 0, PARAM_INT);   // qaapairs.id (message to delete)
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$cm      = get_coursemodule_from_id('qaa', $cmid, 0, false, MUST_EXIST);
$course  = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance= $DB->get_record('qaa',    ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
require_sesskey();

$context = context_module::instance($cm->id);

$msg = $DB->get_record('qaapairs', ['id' => $mid], '*', MUST_EXIST);
// Permissions
$canmanage   = has_capability('mod/qaa:approval', $context); // teacher/manager
$candelete   = has_capability('mod/qaa:deletequest', $context);
$authorid    = !empty($msg->studentid) ? (int)$msg->studentid : (int)$msg->teacherid;
$isowner     = ((int)$authorid === (int)$USER->id);
$haschildren = $DB->record_exists('qaapairs', ['parentid' => $mid]);
if(!empty($msg->studentid)) {
    $authorid = $msg->studentid;
    $is_update_grade = true;
} else {
    $authorid = $msg->teacherid;
    $is_update_grade = false;
}
// Author can only delete leaf non-root; managers can delete anything.
// if (!$canmanage) {
//     if ($haschildren || !$isowner || $msg->status != 0 || !$candelete) {
//         print_error('nopermissions', 'error', '', 'delete message');
//     }
// }

$is_delete = qaa_is_delete($msg, $rootid, $cmid, $USER->id);
if(!$is_delete) {
    print_error('nopermissions', 'error', '', 'delete message');
}

// UI
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/qaa/delete_message.php', ['id' => $cmid, 'mid' => $mid]));
$PAGE->set_title(get_string('delete'));
$PAGE->set_heading(format_string($course->fullname));

$backurl = new moodle_url('/mod/qaa/view.php', ['id' => $cmid, 'questionid' => $rootid, 'embed' => 1]);

if (!$confirm) {
    echo $OUTPUT->header();

    // Rewrite pluginfile URLs trước khi format_text
    $raw = file_rewrite_pluginfile_urls((string)($msg->message ?? ''), 'pluginfile.php',
        $context->id, 'mod_qaa', 'message', $mid);
    $msgtext = format_text($raw, FORMAT_HTML, ['context' => $context]);

    $prompt = html_writer::tag('div',
        '<strong>' . get_string('confirmdeletequestion', 'mod_qaa') . '</strong><br>' .
        get_string('deletewarning', 'mod_qaa') .
        html_writer::div($msgtext, 'mt-2'),
        ['class' => 'mb-2']
    );

    if ($candelete && $haschildren) {
        $prompt .= html_writer::div(
            get_string('deletechildwarning', 'mod_qaa'),
            'text-danger mt-2'
        );
    }

    $yesurl = new moodle_url('/mod/qaa/delete_message.php', [
        'id' => $cmid, 'mid' => $mid, 'rootid' => $rootid, 'confirm' => 1, 'sesskey' => sesskey()
    ]);

    echo $OUTPUT->confirm($prompt, $yesurl, $backurl);
    echo $OUTPUT->footer();
    exit;
}

// Delete helpers (cascade)
function qaa_delete_message_subtree(int $mid, context_module $context): void {
    global $DB;
    // Xoá con trước
    $children = $DB->get_records('qaapairs', ['parentid' => $mid], 'id ASC', 'id');
    foreach ($children as $c) {
        qaa_delete_message_subtree((int)$c->id, $context);
    }
    // Xoá file đính kèm ở filearea 'message'
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_qaa', 'message', $mid);
    // Xoá bản ghi chính
    $DB->delete_records('qaapairs', ['id' => $mid]);
}

// Managers: xoá cả cây; Author: đã kiểm tra chỉ xoá lá (không cần cascade, vẫn gọi cho thống nhất)
qaa_delete_message_subtree($mid, $context);
// Cập nhật điểm (đếm số câu hỏi theo luồng của HV).
$gradeitem = grade_item::fetch([
    'itemtype'    => 'mod',
    'itemmodule'  => $cm->modname,
    'iteminstance'=> $cm->instance,
    'itemnumber'  => 0,
    'courseid'    => $course->id
]);
if ($gradeitem && $is_update_grade) {
    $count = $DB->count_records('qaapairs', [
        'qaaid' => $cm->instance, 'studentid' => $authorid, 'status' => 1
    ]);

    $rawgrade = min($count, (float)$gradeitem->grademax);
    $grade = (object)[
        'userid'        => $authorid,
        'rawgrade'      => $rawgrade,
        'dategraded'    => time(),
        'datesubmitted' => time()
    ];
    // Hàm từ lib.php của plugin.
    if (function_exists('qaa_grade_item_update')) {
        qaa_grade_item_update($instance, [$authorid => $grade]);
    }
}

// Bỏ qua để giữ nguyên hành vi cũ; nếu cần, có thể tính lại grade tại đây.
redirect($backurl);
