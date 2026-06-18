<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/form/edit_course_form.php');

require_login();
$context = context_system::instance();
require_capability('block/th_exam_schedule:manageimports', $context);

global $DB, $PAGE, $OUTPUT;

$id = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/edit_course.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('edit'));
$PAGE->set_heading(get_string('managecourses', 'block_th_exam_schedule'));
$PAGE->navbar->add(get_string('managecourses', 'block_th_exam_schedule'), new moodle_url('/blocks/th_exam_schedule/manage_courses.php'));
$PAGE->navbar->add(get_string('edit'));

$rec = $DB->get_record('block_thexamschedule_course', ['id' => $id], '*', MUST_EXIST);

$form = new \block_th_exam_schedule\form\edit_course_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/blocks/th_exam_schedule/manage_courses.php'));
}

if ($data = $form->get_data()) {
    $newcode = trim((string)$data->coursecode);
    $newname = trim((string)$data->coursename);

    // Check unique coursecode (không trùng với record khác)
    $exists = $DB->record_exists_select(
        'block_thexamschedule_course',
        'id <> :id AND coursecode = :coursecode',
        ['id' => $rec->id, 'coursecode' => $newcode]
    );

    if ($exists) {
        \core\notification::error(get_string('coursecodeexists', 'block_th_exam_schedule'));
    } else {
        $rec->coursecode = $newcode;
        $rec->coursename = $newname;
        $rec->timemodified = time();

        $DB->update_record('block_thexamschedule_course', $rec);

        redirect(
            new moodle_url('/blocks/th_exam_schedule/manage_courses.php'),
            get_string('changesavedcourse', 'block_th_exam_schedule'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

$form->set_data((object)[
    'id' => $rec->id,
    'coursecode' => $rec->coursecode,
    'coursename' => $rec->coursename,
]);

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
