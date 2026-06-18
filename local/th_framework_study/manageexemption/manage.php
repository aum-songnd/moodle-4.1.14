<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');

require_login();
$context = context_system::instance();
require_capability('local/th_framework_study:manageexemption', $context);

global $DB, $PAGE, $OUTPUT;

$delete = optional_param('delete', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/th_framework_study/manageexemption/manage.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manageexemption', 'local_th_framework_study'));
$PAGE->set_heading(get_string('manageexemption', 'local_th_framework_study'));
$PAGE->navbar->add(get_string('manageexemption', 'local_th_framework_study'), new moodle_url('/local/th_framework_study/manageexemption/manage.php'));

if ($delete && confirm_sesskey()) {
    $DB->delete_records('th_subject_exemption', ['id' => $delete]);
    redirect(new moodle_url('/local/th_framework_study/manageexemption/manage.php'), get_string('exemptiondeleted', 'local_th_framework_study'));
}

// Lấy danh sách miễn môn
$records = $DB->get_records('th_subject_exemption', null, 'timecreated DESC');

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('manageexemption', 'local_th_framework_study') . '</center>');
local_th_exemption_tabs($context, $PAGE->url);

// Hiển thị bảng
if ($records) {
    $table = new html_table();
    $table->attributes = [
        'class' => 'th_framework_study display',
        'border' => '1',
        'style' => 'width: 100%; text-align:center;'
    ];

    $table->head = [
        get_string('stt', 'local_th_framework_study'),
        get_string('subjectname', 'local_th_framework_study'),
        get_string('subjectcode', 'local_th_framework_study'),
        get_string('studentname', 'local_th_framework_study'),
        get_string('studentcode', 'local_th_framework_study'),
        get_string('timecreated', 'local_th_framework_study'),
        get_string('timemodified', 'local_th_framework_study'),
        get_string('actions', 'local_th_framework_study')
    ];

    $stt = 1;
    foreach ($records as $r) {
        // Lấy thông tin user
        $user = \core_user::get_user($r->userid);

        // Lấy mã học viên theo đúng ngành học đã lưu
        $studentcode = '-';
        $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname'); // "th_student_code,th_student_code2"
        $ma_hoc_vien_list = array_map('trim', explode(',', $setting_ma_hoc_vien));

        if (!empty($r->major_code)) {
            // Lấy tất cả field trong ngành học
            $fields = $DB->get_records('user_info_field', ['categoryid' => $r->major_code]);

            foreach ($fields as $f) {
                if (in_array($f->shortname, $ma_hoc_vien_list)) {
                    $code = $DB->get_field('user_info_data', 'data', [
                        'userid'  => $r->userid,
                        'fieldid' => $f->id
                    ]);
                    if ($code) {
                        $studentcode = $code;
                        break; // tìm thấy mã đầu tiên hợp lệ
                    }
                }
            }
        }

        // fallback nếu không tìm thấy trong ngành
        if ($studentcode === '-') {
            foreach ($ma_hoc_vien_list as $fieldshortname) {
                if (empty($fieldshortname)) continue;
                $code = $DB->get_field_sql("
                    SELECT d.data 
                    FROM {user_info_data} d
                    JOIN {user_info_field} f ON f.id = d.fieldid
                    WHERE f.shortname = :field AND d.userid = :userid
                ", ['field' => $fieldshortname, 'userid' => $r->userid]);
                if ($code) {
                    $studentcode = $code;
                    break;
                }
            }
        }
        // Lấy tên môn học
        $subjectname = $DB->get_field_sql("
            SELECT subject_name
            FROM {th_framework_study}
            WHERE subject_code = :code
            ORDER BY id ASC
            LIMIT 1
        ", ['code' => $r->subject_code]) ?? '-';

        $editurl = new moodle_url('/local/th_framework_study/manageexemption/edit.php', ['id' => $r->id]);
        $deleteurl = new moodle_url('/local/th_framework_study/manageexemption/manage.php', ['delete' => $r->id, 'sesskey' => sesskey()]);

        $actions = html_writer::link($editurl, get_string('edit')) . ' | ' .
            html_writer::link(
                $deleteurl,
                get_string('delete'),
                ['onclick' => "return confirm('" . get_string('confirmdeletemienmon', 'local_th_framework_study') . "');"]
            );

        $table->data[] = [
            $stt++,
            format_string($subjectname),
            s($r->subject_code),
            fullname($user),
            s($studentcode),
            userdate($r->timecreated, '%d/%m/%Y'),
            $r->timemodified ? userdate($r->timemodified, '%d/%m/%Y') : '-',
            $actions
        ];
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('noexemptions', 'local_th_framework_study'), 'info');
}

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_framework_study', get_string('pluginname', 'local_th_framework_study'), $lang));
echo $OUTPUT->footer();
