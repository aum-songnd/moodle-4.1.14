<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/subjectcode_form.php');

use local_th_framework_study\form\subjectcode_form;

require_login();
require_capability('local/th_framework_study:manageframeworkstudy', context_system::instance());

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_url('/local/th_framework_study/subjectcode/edit.php', ['id' => $id]);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Edit subject code mapping');
$PAGE->set_heading('Edit subject code mapping');
$PAGE->navbar->add('Edit subject code mapping', new moodle_url('/local/th_framework_study/subjectcode/edit.php'));

// Load dữ liệu khi edit
$record = null;
if ($id) {
    $record = $DB->get_record('th_subject_code_mapping', ['id' => $id], '*', MUST_EXIST);
}

// Khởi tạo form
$form = new subjectcode_form(
    $PAGE->url,
    ['id' => $id]
);

// Set data khi edit
if ($record) {
    $form->set_data($record);
}

// Cancel
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/th_framework_study/subjectcode/manage.php'));
}

// Submit
if ($data = $form->get_data()) {

    if ($data->id) {
        $result = th_framework_subjectcode_update(
            $data->id,
            $data->current_code,
            $data->old_code
        );
    } else {
        $result = th_framework_subjectcode_add(
            $data->current_code,
            $data->old_code
        );
    }

    if ($result === false) {
        throw new moodle_exception('Mapping đã tồn tại hoặc dữ liệu không hợp lệ');
    }

    redirect(new moodle_url('/local/th_framework_study/subjectcode/manage.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('subjectcodemapping', 'local_th_framework_study') . '</center>');
local_th_subjectcode_tabs(context_system::instance(), $PAGE->url);
$form->display();
echo $OUTPUT->footer();
