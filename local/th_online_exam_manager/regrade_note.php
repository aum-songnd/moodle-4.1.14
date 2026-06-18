<?php
require_once('../../config.php');
require_login();

$id = required_param('id', PARAM_INT);
$status = required_param('status', PARAM_TEXT);

// Lấy bản ghi yêu cầu phúc khảo
$regrade = $DB->get_record('th_exam_regrade_requests', ['id' => $id], '*', MUST_EXIST);
$context = context_system::instance();

require_login();
require_capability('local/th_online_exam_manager:regrade', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/th_online_exam_manager/regrade_note.php', ['id' => $id, 'status' => $status]));
$PAGE->set_title(get_string('editnotetitle', 'local_th_online_exam_manager'));
$PAGE->set_heading(get_string('editregradenoteheading', 'local_th_online_exam_manager'));

$baseurl = new moodle_url('/local/th_online_exam_manager/regrade_note.php', ['id' => $id, 'status' => $status]);
$settingsnode = $PAGE->settingsnav->add(get_string('editnotetitle', 'local_th_online_exam_manager'), $baseurl);
$settingsnode->make_active();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $note = required_param('note', PARAM_TEXT);
    $grade = optional_param('grade', null, PARAM_RAW); // dùng RAW để chấp nhận cả rỗng và số

    // Kiểm tra nếu có nhập thì phải là số
    if ($grade !== null && $grade !== '') {
        if (!is_numeric($grade)) {
            print_error('invalidgrade', 'local_th_online_exam_manager');
        }
        $grade = floatval($grade);
    } else {
        $grade = null;
    }

    $DB->update_record('th_exam_regrade_requests', [
        'id' => $id,
        'note' => $note,
        'grade' => $grade,
    ]);

    redirect(new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', ['status' => $status ]), get_string('notesaved', 'local_th_online_exam_manager'));
}

echo $OUTPUT->header();

echo html_writer::start_tag('form', ['method' => 'post']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::tag('label', get_string('grade', 'local_th_online_exam_manager'), ['for' => 'grade']);
// echo html_writer::empty_tag('input', [
//     'type' => 'text',
//     'name' => 'grade',
//     'value' => is_null($regrade->grade) ? '' : round($regrade->grade, 2),
//     'class' => 'form-control',
//     'style' => 'width: 200px;',
//     'placeholder' => get_string('entergrade', 'local_th_online_exam_manager'),
// ]);

echo html_writer::empty_tag('input', [
    'type'        => 'text',                 // dùng text để chấp nhận dấu , theo locale
    'name'        => 'grade',
    'value'       => is_null($regrade->grade) ? '' : number_format((float)$regrade->grade, 2, '.', ''),
    'class'       => 'form-control',
    'style'       => 'width: 200px;',
    'placeholder' => get_string('entergrade', 'local_th_online_exam_manager'),
    'inputmode'   => 'decimal',
    'pattern'     => '^\d+([.]\d{1,2})?$',  // số hoặc số thập phân với . hoặc , và tối đa 2 số sau dấu
    'title'       => get_string('grade_format_hint', 'local_th_online_exam_manager'), // ví dụ: "Nhập số, tối đa 2 chữ số thập phân"
    'maxlength'   => 10,
]);

echo html_writer::empty_tag('br');

echo html_writer::tag('label', get_string('note', 'local_th_online_exam_manager'), ['for' => 'note']);
echo html_writer::tag('textarea', $regrade->note, [
    'name' => 'note',
    'rows' => 6,
    'cols' => 80,
    'style' => 'width:100%',
]);

echo html_writer::empty_tag('br');
echo html_writer::tag('button', get_string('save', 'local_th_online_exam_manager'), [
    'type' => 'submit',
    'class' => 'btn btn-secondary'
]);

$backurl = new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', ['status' => $status]);
echo html_writer::link($backurl, get_string('back'), [
    'class' => 'btn btn-secondary ml-2'
]);

echo html_writer::end_tag('form');

echo $OUTPUT->footer();
