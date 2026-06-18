<?php
require '../../../config.php';
require_once $CFG->dirroot . '/lib/classes/notification.php';
require_once $CFG->dirroot . '/local/th_register/dot_mo_mon/lib.php';
require_once $CFG->dirroot . '/local/th_register/dot_mo_mon/th_register_dot_mo_mon_form.php';

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
    $record = $DB->get_record('th_dot_mo_mon', array('id' => $id), '*', MUST_EXIST);
} else {
    $record = new stdClass();
    $record->id = 0;
    $record->ma_dot_mo_mon = '';
    $record->ten_dot_mo = '';
    // $record->hoc_phi_mot_tin_chi = '';
    $record->tu_ngay = '';
    $record->den_ngay = '';
}

$baseurl = new moodle_url('/local/th_register/dot_mo_mon/edit.php');
$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $dot_mo_has_mon_hoc_id = $DB->get_records('th_dot_mo_has_mon_hoc', ['dot_mo_mon_id' => $id], '', 'id');
        if (!empty($dot_mo_has_mon_hoc_id)) {
            foreach ($dot_mo_has_mon_hoc_id as $key => $dot_mo_has_mon_hoc) {
                if ($DB->record_exists('th_dang_ky_mon_hoc', ['dot_mo_has_mon_hoc_id' => $dot_mo_has_mon_hoc->id])) {
                    $DB->delete_records('th_dang_ky_mon_hoc', ['dot_mo_has_mon_hoc_id' => $dot_mo_has_mon_hoc->id]);
                }
            }

        }
        if ($DB->record_exists('th_dot_mo_has_mon_hoc', ['dot_mo_mon_id' => $id])) {

            $DB->delete_records('th_dot_mo_has_mon_hoc', ['dot_mo_mon_id' => $id]);
        }
        if ($DB->record_exists('th_dot_mo_mon', ['id' => $id])) {

            $DB->delete_records('th_dot_mo_mon', ['id' => $id]);
        }
        redirect($CFG->wwwroot . '/local/th_register/dot_mo_mon/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    $strheading = get_string('delete');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $record = $DB->get_record('th_dot_mo_mon', array('id' => $id));
    $yesurl = new moodle_url('/local/th_register/dot_mo_mon/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
    $a = new stdClass();
    $a->ten_dot_mo = $record->ten_dot_mo;
    $a->ma_dot_mo_mon = $record->ma_dot_mo_mon;
    $message = get_string('confirm_delete_batch', 'local_th_register', $a);

    // $message = "Bạn có chắc chắn muốn xóa Đợt " . html_writer::tag('b', $record->ten_dot_mo . "($record->ma_dot_mo_mon)");
    echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_register/dot_mo_mon/index.php'));
    echo $OUTPUT->footer();
    die;
}

if ($record->id) {
    $strheading = get_string('sua_dot_mo_mon', 'local_th_register');

} else {
    $strheading = get_string('them_dot_mo_mon', 'local_th_register');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
$PAGE->navbar->add(get_string('manage_opening', 'local_th_register'), new moodle_url('/local/th_register/doi_tuong/index.php'));
$editurl = new moodle_url('/local/th_register/dot_mo_mon/edit.php');
$PAGE->navbar->add($strheading, $editurl);

$editform = new th_register_add_form();
if ($record->id) {
    // Edit existing.
    $data = array(
        'ma_dot_mo_mon' => $record->ma_dot_mo_mon,
        'ten_dot_mo' => $record->ten_dot_mo,
        'id' => $id,
        'tu_ngay' => $record->tu_ngay,
        'den_ngay' => $record->den_ngay,
        // 'hoc_phi_mot_tin_chi' => $record->hoc_phi_mot_tin_chi,
    );
    $editform->set_data($data);
}

if ($editform->is_cancelled()) {
    redirect(new moodle_url('/local/th_register/dot_mo_mon/index.php'));

} else if ($data = $editform->get_data()) {

    $ma_dot_mo_mon = trim($data->ma_dot_mo_mon);
    $ten_dot_mo = trim($data->ten_dot_mo);
    // $hoc_phi_mot_tin_chi = trim($data->hoc_phi_mot_tin_chi);
    $tu_ngay = trim($data->tu_ngay);
    $den_ngay = trim($data->den_ngay);

    $dataobject = new stdClass();
    $dataobject->ma_dot_mo_mon = $ma_dot_mo_mon;
    $dataobject->ten_dot_mo = $ten_dot_mo;
    // $dataobject->hoc_phi_mot_tin_chi = $hoc_phi_mot_tin_chi;
    $dataobject->tu_ngay = $tu_ngay;
    $dataobject->den_ngay = $den_ngay;

    if ($record->id) {
        $dataobject->id = $record->id;

        if (strlen(trim($data->ma_dot_mo_mon)) < 45 && strlen(trim($data->ten_dot_mo) < 45)) {
            $dataobject->nguoi_sua = $USER->id;
            $dataobject->ngay_sua = time();
            $DB->update_record('th_dot_mo_mon', $dataobject);

            redirect($CFG->wwwroot . '/local/th_register/dot_mo_mon/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
        }

        redirect($CFG->wwwroot . '/local/th_register/dot_mo_mon/index.php', get_string('invalid_data', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);

    } else {

        if (!$DB->record_exists('th_dot_mo_mon', array('ma_dot_mo_mon' => trim($data->ma_dot_mo_mon)))) {
            $dataobject->nguoi_tao = $USER->id;
            $dataobject->ngay_tao = time();

            $DB->insert_record('th_dot_mo_mon', $dataobject);

            redirect($CFG->wwwroot . '/local/th_register/dot_mo_mon/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        redirect($CFG->wwwroot . '/local/th_register/dot_mo_mon/index.php', get_string('record_exists', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
    }
}

echo $OUTPUT->header();
if (($editcontrols = local_th_register_dot_mo_mon_controls($context, $baseurl))) {
    echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading($strheading);
echo $editform->display();
echo $OUTPUT->footer();
