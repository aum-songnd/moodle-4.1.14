<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/form/edit_exam_form.php');
require_once(__DIR__ . '/lib.php');

require_login();
$context = context_system::instance();
require_capability('block/th_exam_schedule:manageimports', $context);

global $DB, $PAGE, $OUTPUT;

$id = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/edit.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('edit'));
$PAGE->set_heading(get_string('manage', 'block_th_exam_schedule'));
$PAGE->navbar->add(get_string('manage', 'block_th_exam_schedule'), new moodle_url('/blocks/th_exam_schedule/manage.php'));
$PAGE->navbar->add(get_string('edit'));

$rec = $DB->get_record('block_thexamschedule_exam', ['id' => $id], '*', MUST_EXIST);

$form = new \block_th_exam_schedule\form\edit_exam_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/blocks/th_exam_schedule/manage.php'));
    exit;
}

if ($data = $form->get_data()) {

    // kiểm tra trùng
    $exists = $DB->record_exists_select(
        'block_thexamschedule_exam',
        'id <> :id AND studentcode = :studentcode AND coursecode = :coursecode AND examdate = :examdate AND examslot = :examslot',
        [
            'id' => $rec->id,
            'studentcode' => $data->studentcode,
            'coursecode' => $data->coursecode,
            'examdate' => (int)$data->examdate,
            'examslot' => $data->examslot,
        ]
    );
    

    if ($exists) {
        \core\notification::error(get_string('recordalreadyexists', 'block_th_exam_schedule'));
    } else {
        $rec->studentcode = trim((string)$data->studentcode);
        $rec->coursecode  = trim((string)$data->coursecode);
        $rec->examdate    = (int)$data->examdate;
        $rec->examslot    = trim((string)$data->examslot);
        $rec->timemodified = time();

        $DB->update_record('block_thexamschedule_exam', $rec);

        redirect(
            new moodle_url('/blocks/th_exam_schedule/manage.php'),
            get_string('changesaved', 'block_th_exam_schedule'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        exit;
    }
}

// Set defaults
$form->set_data((object)[
    'id' => $rec->id,
    'studentcode' => $rec->studentcode,
    'coursecode' => $rec->coursecode,
    'examdate' => (int)$rec->examdate,
    'examslot' => $rec->examslot,
]);

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
