<?php
require '../../../config.php';
require_once $CFG->dirroot . '/lib/classes/notification.php';
require_once $CFG->dirroot . '/local/th_register/doi_tuong/lib.php';
require_once $CFG->dirroot . '/local/th_register/doi_tuong/th_register_doi_tuong_form.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

if ($id) {
    $record = $DB->get_record('th_doi_tuong', array('id' => $id), '*', MUST_EXIST);
} else {
    $record = new stdClass();
    $record->id = 0;
    $record->ma_doi_tuong = '';
    $record->ten_doi_tuong = '';
    $record->nguoi_tao = $USER->id;
    $record->ngay_tao = time();
}

$baseurl = new moodle_url('/local/th_register/doi_tuong/edit.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $DB->delete_records('th_mon_hoc_has_doi_tuong', ['doi_tuong_id' => $id]);
        $DB->delete_records('th_doi_tuong', ['id' => $id]);
        redirect($CFG->wwwroot . '/local/th_register/doi_tuong/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    $strheading = get_string('delete');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $record = $DB->get_record('th_doi_tuong', array('id' => $id));
    $yesurl = new moodle_url('/local/th_register/doi_tuong/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
    $message = get_string('confirm_delete_object', 'local_th_register', [
        'name' => $record->ten_doi_tuong,
        'code' => $record->ma_doi_tuong
    ]);
    //$message = "Bạn có chắc chắn muốn xóa Đối tượng " . html_writer::tag('b', $record->ten_doi_tuong . "($record->ma_doi_tuong)");
    echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_register/doi_tuong/index.php'));
    echo $OUTPUT->footer();
    die;
}

if ($record->id) {
    $strheading = get_string('sua_doi_tuong', 'local_th_register');

} else {
    $strheading = get_string('them_doi_tuong', 'local_th_register');
}
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
$PAGE->navbar->add(get_string('manage_students', 'local_th_register'), new moodle_url('/local/th_register/doi_tuong/index.php'));
$editurl = new moodle_url('/local/th_register/doi_tuong/edit.php');
$PAGE->navbar->add($strheading, $editurl);

$editform = new th_register_add_form();
$editform->set_data(array('ma_doi_tuong' => $record->ma_doi_tuong, 'ten_doi_tuong' => $record->ten_doi_tuong, 'id' => $id));

if ($editform->is_cancelled()) {
    redirect(new moodle_url('/local/th_register/doi_tuong/index.php'));

} else if ($data = $editform->get_data()) {

    $ma_doi_tuong = trim($data->ma_doi_tuong);
    $ten_doi_tuong = trim($data->ten_doi_tuong);

    $dataobject = new stdClass();
    $dataobject->ma_doi_tuong = $ma_doi_tuong;
    $dataobject->ten_doi_tuong = $ten_doi_tuong;

    if (strlen($ma_doi_tuong) < 11 && strlen($ten_doi_tuong) < 46) {
        if ($record->id) {
            $dataobject->id = $record->id;
            $dataobject->nguoi_sua = $USER->id;
            $dataobject->ngay_sua = time();
            $DB->update_record('th_doi_tuong', $dataobject);

            redirect($CFG->wwwroot . '/local/th_register/doi_tuong/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);

        } else {

            if (!$DB->record_exists('th_doi_tuong', array('ma_doi_tuong' => trim($data->ma_doi_tuong)))) {
                $dataobject->nguoi_tao = $USER->id;
                $dataobject->ngay_tao = time();
                $DB->insert_record('th_doi_tuong', $dataobject);

                redirect($CFG->wwwroot . '/local/th_register/doi_tuong/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
        }
    }
    redirect($CFG->wwwroot . '/local/th_register/doi_tuong/index.php', get_string('invalid_data', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
}

echo $OUTPUT->header();
if (($editcontrols = local_th_register_doi_tuong_controls($context, $baseurl))) {
    echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading($strheading);
echo $editform->display();
echo $OUTPUT->footer();
