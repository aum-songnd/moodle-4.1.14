<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/edit_framework_form.php');

require_login();
$context = context_system::instance();
require_capability('local/th_framework_study:manageframeworkstudy', $context);

global $DB, $PAGE, $OUTPUT, $USER;

$id = optional_param('id', 0, PARAM_INT);

$enable_train_code = (bool)get_config('local_th_framework_study', 'enable_train_code');
$skip = (bool)get_config('local_th_framework_study', 'skip_slot_target');

// Nếu DB bắt slot >=1 thì đổi thành 1
$slotdefault = 0;

$PAGE->set_url(new moodle_url('/local/th_framework_study/manageframeworkstudy/edit.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('editframework', 'local_th_framework_study'));
$PAGE->set_heading(get_string('editframework', 'local_th_framework_study'));
$PAGE->navbar->add(
    get_string('manageframeworkstudy', 'local_th_framework_study'),
    new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php')
);

if ($id) {
    $data = $DB->get_record('th_framework_study', ['id' => $id], '*', MUST_EXIST);
} else {
    $data = null;
}

$mform = new \local_th_framework_study\form\edit_framework_form(null, []);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php'));
} else if ($formdata = $mform->get_data()) {

    // Xử lý slot
    if ($formdata->slot === '' || $formdata->slot === null) {
        $formdata->slot = $slotdefault; // 0 hoặc 1 tuỳ DB
    }

    // Xử lý target_code
    if ($formdata->target_code === null) {
        $formdata->target_code = '';
    }

    $formdata->timemodified = time();
    $formdata->updated_by = $USER->id;
    // CREDIT: cho phép NULL
    if ($skip && ($formdata->credit === '' || $formdata->credit === null)) {
        $formdata->credit = null;
    } else {
        $formdata->credit = (int)$formdata->credit;
    }

    if (!empty($formdata->id)) {
        $DB->update_record('th_framework_study', $formdata);
    } else {
        $formdata->timecreated = time();
        $formdata->created_by = $USER->id;
        $DB->insert_record('th_framework_study', $formdata);
    }

    redirect(
        new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php'),
        get_string('frameworksaved', 'local_th_framework_study')
    );
}

if ($data) {
    // Nếu skip đang bật thì vẫn set_data bình thường, form sẽ ẩn 2 field (ở file form)
    $mform->set_data($data);
}

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('manageframeworkstudy', 'local_th_framework_study') . '</center>');
local_th_framework_study_tabs($context, $PAGE->url);
$mform->display();
echo $OUTPUT->footer();
