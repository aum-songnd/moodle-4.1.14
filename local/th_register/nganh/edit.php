<?php
require '../../../config.php';
require_once $CFG->dirroot . '/lib/classes/notification.php';
require_once $CFG->dirroot . '/local/th_register/nganh/lib.php';
require_once $CFG->dirroot . '/local/th_register/nganh/th_register_nganh_form.php';

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
    $record = $DB->get_record('th_nganh_hoc', array('id' => $id), '*', MUST_EXIST);
} else {
    $record = new stdClass();
    $record->id = 0;
    $record->ma_nganh = '';
    $record->ten_nganh = '';
    $record->hoc_phi_mot_tin_chi = '';
    $record->nguoi_tao = $USER->id;
    $record->ngay_tao = time();
}
$baseurl = new moodle_url('/local/th_register/nganh/edit.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $DB->delete_records('th_mon_hoc_has_nganh_hoc', ['nganh_hoc_id' => $id]);
        $DB->delete_records('th_nganh_hoc', ['id' => $id]);
        redirect($CFG->wwwroot . '/local/th_register/nganh/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    $strheading = get_string('delete');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $record = $DB->get_record('th_nganh_hoc', array('id' => $id));
    $yesurl = new moodle_url('/local/th_register/nganh/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
    $message = get_string('confirm_delete_major', 'local_th_register', 
    html_writer::tag('b', $record->ten_nganh . " ($record->ma_nganh)")
);
    echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_register/nganh/index.php'));
    echo $OUTPUT->footer();
    die;
}

if ($record->id) {
    $strheading = get_string('sua_nganh', 'local_th_register');

} else {
    $strheading = get_string('them_nganh', 'local_th_register');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
$PAGE->navbar->add(get_string('manage_majors', 'local_th_register'), new moodle_url('/local/th_register/nganh/index.php'));
$PAGE->navbar->add($strheading, $baseurl);

$editform = new th_register_add_form();
$editform->set_data(
    array(
        'ma_nganh' => $record->ma_nganh, 
        'ten_nganh' => $record->ten_nganh, 
        'hoc_phi_mot_tin_chi' => $record->hoc_phi_mot_tin_chi,
        'id' => $id
    )
);

if ($editform->is_cancelled()) {
    redirect(new moodle_url('/local/th_register/nganh/index.php'));

} else if ($data = $editform->get_data()) {

    $ma_nganh = trim($data->ma_nganh);
    $ten_nganh = trim($data->ten_nganh);
    $hoc_phi_mot_tin_chi = trim($data->hoc_phi_mot_tin_chi);

    $dataobject = new stdClass();
    $dataobject->ma_nganh = $ma_nganh;
    $dataobject->ten_nganh = $ten_nganh;
    $dataobject->hoc_phi_mot_tin_chi = $hoc_phi_mot_tin_chi;

    if (strlen($ma_nganh) < 11 && strlen($ten_nganh) < 46) {
        if ($record->id) {
            $dataobject->id = $id;
            $dataobject->nguoi_sua = $USER->id;
            $dataobject->ngay_sua = time();
            $DB->update_record('th_nganh_hoc', $dataobject);

            redirect($CFG->wwwroot . '/local/th_register/nganh/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
        } else {

            if (!$DB->record_exists('th_nganh_hoc', array('ma_nganh' => $ma_nganh))) {
                $dataobject->nguoi_tao = $USER->id;
                $dataobject->ngay_tao = time();
                // print_object($dataobject);
                // exit;
                $DB->insert_record('th_nganh_hoc', $dataobject);

                redirect($CFG->wwwroot . '/local/th_register/nganh/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
        }
    }
    redirect($CFG->wwwroot . '/local/th_register/nganh/index.php', get_string('invalid_data', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
}

echo $OUTPUT->header();
if (($editcontrols = local_th_register_nganh_controls($context, $baseurl))) {
    echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading($strheading);
echo $editform->display();
echo $OUTPUT->footer();
