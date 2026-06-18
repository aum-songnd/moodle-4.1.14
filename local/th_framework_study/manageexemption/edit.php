<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/edit_exemption_form.php');

require_login();
$context = context_system::instance();
require_capability('local/th_framework_study:manageexemption', $context);

global $DB, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/th_framework_study/manageexemption/edit.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manageexemption', 'local_th_framework_study'));
$PAGE->set_heading(get_string('manageexemption', 'local_th_framework_study'));
$PAGE->navbar->add(get_string('manageexemption', 'local_th_framework_study'),
    new moodle_url('/local/th_framework_study/manageexemption/manage.php'));

// Lấy dữ liệu cũ nếu sửa.
if ($id) {
    $data = $DB->get_record('th_subject_exemption', ['id' => $id], '*', MUST_EXIST);

    // Lấy danh sách các shortname từ setting
    $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname'); // ví dụ: "th_student_code,th_student_code2"
    $ma_hoc_vien_list = array_map('trim', explode(',', $setting_ma_hoc_vien));

    $studentcode = null;
    foreach ($ma_hoc_vien_list as $fieldshortname) {
        if (empty($fieldshortname)) {
            continue;
        }
        $studentcode = $DB->get_field_sql("
        SELECT d.data
          FROM {user_info_data} d
          JOIN {user_info_field} f ON f.id = d.fieldid
         WHERE f.shortname = :field
           AND f.categoryid = :nganhhoc
           AND d.userid = :userid
    ", ['field' => $fieldshortname, 'nganhhoc' => $data->major_code, 'userid' => $data->userid]);
    

        if ($studentcode) {
            break; // tìm thấy mã học viên đầu tiên, dừng
        }
    }

    $data->student_code = $studentcode;

} else {
    $data = null;
}


$mform = new \local_th_framework_study\form\edit_exemption_form(null, []);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_framework_study/manageexemption/manage.php'));
} else if ($formdata = $mform->get_data()) {

    $studentcode = trim($formdata->student_code);
    $subjectcode = trim($formdata->subject_code);

    // Lấy danh sách shortname mã học viên từ setting
    $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname'); // ví dụ: "th_student_code,th_student_code2"
    $ma_hoc_vien_list = array_map('trim', explode(',', $setting_ma_hoc_vien));

    $userid = null;
    $major_code = null; // thêm biến lưu ngành học

    foreach ($ma_hoc_vien_list as $fieldshortname) {
        if (empty($fieldshortname)) {
            continue;
        }

        // Truy vấn lấy userid và categoryid (ngành học)
        $record = $DB->get_record_sql("
            SELECT u.id AS userid, f.categoryid AS major_code
              FROM {user} u
              JOIN {user_info_data} d ON d.userid = u.id
              JOIN {user_info_field} f ON f.id = d.fieldid
             WHERE f.shortname = :field
               AND d.data = :code
               AND u.deleted = 0
             LIMIT 1
        ", ['field' => $fieldshortname, 'code' => $studentcode]);

        if ($record) {
            $userid = $record->userid;
            $major_code = $record->major_code;
            break; // tìm thấy thì dừng
        }
    }

    if (!$userid) {
        redirect(
            new moodle_url('/local/th_framework_study/manageexemption/edit.php', ['id' => $id]),
            get_string('usernotfound', 'local_th_framework_study'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    // Kiểm tra môn học có tồn tại không
    if (!$DB->record_exists('th_framework_study', ['subject_code' => $subjectcode])) {
        redirect(
            new moodle_url('/local/th_framework_study/manageexemption/edit.php', ['id' => $id]),
            get_string('subjectnotfound', 'local_th_framework_study'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    // Chuẩn bị bản ghi
    $record = new stdClass();
    $record->userid = $userid;
    $record->subject_code = $subjectcode;
    $record->major_code = $major_code; // lưu thêm ngành học
    $record->timemodified = time();
    $record->updated_by = $USER->id;

    if (!empty($formdata->id)) {
        // SỬA – update theo ID
        $record->id = $formdata->id;
        $DB->update_record('th_subject_exemption', $record);

    } else {
        // THÊM MỚI – kiểm tra trùng theo userid + subject_code + major_code
        $exists = $DB->record_exists('th_subject_exemption', [
            'userid' => $userid,
            'subject_code' => $subjectcode,
            'major_code' => $major_code
        ]);

        if ($exists) {
            redirect(
                new moodle_url('/local/th_framework_study/manageexemption/edit.php'),
                get_string('exemptionduplicate', 'local_th_framework_study'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }

        $record->timecreated = time();
        $record->created_by = $USER->id;
        $DB->insert_record('th_subject_exemption', $record);
    }

    redirect(
        new moodle_url('/local/th_framework_study/manageexemption/manage.php'),
        get_string('exemptionsaved', 'local_th_framework_study'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}



// Hiển thị form
if ($data) {
    $mform->set_data($data);
}

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('manageexemption', 'local_th_framework_study') . '</center>');
local_th_exemption_tabs($context, $PAGE->url);
$mform->display();
echo $OUTPUT->footer();
