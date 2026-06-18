<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/upload_exemption_form.php');

require_login();
$context = context_system::instance();
require_capability('local/th_framework_study:manageexemption', $context);

global $PAGE, $OUTPUT, $DB, $SESSION;

$PAGE->set_url(new moodle_url('/local/th_framework_study/manageexemption/upload.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('batchupload', 'local_th_framework_study'));
$PAGE->set_heading(get_string('manageexemption', 'local_th_framework_study'));
$PAGE->navbar->add(get_string('manageexemption', 'local_th_framework_study'),
    new moodle_url('/local/th_framework_study/manageexemption/upload.php'));

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('manageexemption', 'local_th_framework_study') . '</center>');
local_th_exemption_tabs($context, $PAGE->url);

$mform = new exemption_upload_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_framework_study/manageexemption/manage.php'));
}

else if ($data = $mform->get_data()) {

    // LƯU ACTION MODE (import | delete)
    $SESSION->exemption_action = $data->actionmode;

    $draft = file_get_submitted_draft_itemid('frameworkfile');
    $fs = get_file_storage();

    file_save_draft_area_files(
        $draft,
        $context->id,
        'local_th_framework_study',
        'exemptionupload',
        0,
        ['subdirs' => 0]
    );

    $files = $fs->get_area_files($context->id, 'local_th_framework_study', 'exemptionupload', 0, 'id', false);

    if (!$files) {
        echo $OUTPUT->notification(get_string('filenotfound', 'local_th_framework_study'), 'error');
        $mform->display();
        echo $OUTPUT->footer();
        exit;
    }

    $file = reset($files);
    $filepath = $file->copy_content_to_temp('local_th_framework_study', '.xlsx');

    require_once($CFG->dirroot . '/lib/phpspreadsheet/vendor/autoload.php');
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($filepath);
    $rows = $spreadsheet->getActiveSheet()->toArray();

    // Kiểm tra cấu trúc cột của file Excel
    $header = array_map('trim', $rows[0]);
    // Lọc bỏ các cột rỗng
    $header = array_filter($header, function($col) {
        return $col !== '';
    });
    $expected = ['student_code', 'subject_code'];

    // Chuyển toàn bộ header về chữ thường
    $lowerheader = array_map('strtolower', $header);

    // Kiểm tra: phải có đúng 2 cột, và phải trùng khớp hoàn toàn
    if (count($lowerheader) !== count($expected) || $lowerheader !== $expected) {
        echo $OUTPUT->notification(
            get_string('invalid_excel_format', 'local_th_framework_study'),
            'error'
        );
        unlink($filepath); // Xóa file tạm
        $mform->display();
        echo $OUTPUT->footer();
        exit;
    }

    $preview = [];

    foreach ($rows as $index => $row) {
        if ($index === 0) continue;
    
        if (empty(array_filter($row, fn($v) => trim($v) !== ''))) {
            continue;
        }
    
        $identifier  = trim($row[0]);
        $subjectcode = trim($row[1]);
    
        if ($identifier === '' || $subjectcode === '') {
            $preview[] = ['identifier'=>$identifier,'subject'=>$subjectcode,'status' => get_string('invalid', 'local_th_framework_study')];
            continue;
        }
    
        // tìm user theo email hoặc nhiều th_student_code
        $userid = null;
    
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $userid = $DB->get_field('user', 'id', ['email'=>$identifier,'deleted'=>0]);
        }
    
        if (!$userid) {
            // Lấy danh sách shortname từ plugin th_lib
            $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname'); // ví dụ: "th_student_code,th_student_code2"
            $ma_hoc_vien_list = array_map('trim', explode(',', $setting_ma_hoc_vien));
    
            foreach ($ma_hoc_vien_list as $fieldshortname) {
                if (empty($fieldshortname)) continue;
    
                $userid = $DB->get_field_sql("
                    SELECT u.id
                      FROM {user} u
                      JOIN {user_info_data} d ON d.userid = u.id
                      JOIN {user_info_field} f ON f.id = d.fieldid
                     WHERE f.shortname = :field
                       AND d.data = :scode
                       AND u.deleted = 0
                ", ['field'=>$fieldshortname, 'scode'=>$identifier]);
    
                if ($userid) break; // tìm thấy userid đầu tiên
            }
        }
    
        if (!$userid) {
            $preview[] = ['identifier'=>$identifier,'subject'=>$subjectcode,'status' => get_string('user_not_found', 'local_th_framework_study')];
            continue;
        }
    
        // kiểm tra subject
        $subjectexists = $DB->record_exists('th_framework_study', ['subject_code'=>$subjectcode]);
        if (!$subjectexists) {
            $preview[] = ['identifier'=>$identifier,'subject'=>$subjectcode,'status' => get_string('subject_not_found', 'local_th_framework_study')];
            continue;
        }
    
        // kiểm tra dữ liệu đã tồn tại
        $existing = $DB->get_record('th_subject_exemption', [
            'userid'=>$userid,
            'subject_code'=>$subjectcode
        ]);
    
        if ($existing) {
            $preview[] = [
                'identifier'=>$identifier,
                'subject'=>$subjectcode,
                'userid'=>$userid,
                'status'=> ($SESSION->exemption_action === 'delete' ? get_string('delete', 'local_th_framework_study') : get_string('update', 'local_th_framework_study')),
                'action' => ($SESSION->exemption_action === 'delete' ? 'delete' : 'update')
            ];
        } else {
            $preview[] = [
                'identifier'=>$identifier,
                'subject'=>$subjectcode,
                'userid'=>$userid,
                'status'=> ($SESSION->exemption_action === 'delete' ? get_string('not_found', 'local_th_framework_study') : get_string('insert', 'local_th_framework_study')),
                'action' => ($SESSION->exemption_action === 'delete' ? 'not_found' : 'insert')
            ];
        }
    }
    

    unlink($filepath);

    $SESSION->th_exemption_preview = $preview;

    // hiển thị preview
    $table = new html_table();
    $table->head = [
        get_string('studentcode', 'local_th_framework_study'), 
        get_string('subjectcode', 'local_th_framework_study'), 
        get_string('status', 'local_th_framework_study')
    ];

    foreach ($preview as $p) {
        $table->data[] = [$p['identifier'], $p['subject'], $p['status']];
    }

    echo html_writer::table($table);

    $confirmurl = new moodle_url('/local/th_framework_study/manageexemption/upload.php', [
        'confirm' => 1,
        'sesskey' => sesskey()
    ]);

    echo $OUTPUT->confirm(
        get_string('confirmaction', 'local_th_framework_study'),
        $confirmurl,
        new moodle_url('/local/th_framework_study/manageexemption/upload.php')
    );

    echo $OUTPUT->footer();
    exit;
}

else if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {

    $action = $SESSION->exemption_action ?? 'import';
    $preview = $SESSION->th_exemption_preview ?? [];
    // DELETE MODE
    if ($action === 'delete') {

        $deleted = 0;
        $notfound = 0;

        foreach ($preview as $p) {
            if (empty($p['userid'])) continue;

            if ($p['action'] === 'delete') {
                $DB->delete_records('th_subject_exemption', [
                    'userid' => $p['userid'],
                    'subject_code' => $p['subject']
                ]);
                $deleted++;
            } else {
                $notfound++;
            }
        }

        unset($SESSION->exemption_action, $SESSION->th_exemption_preview);
        $mform->display();
        echo $OUTPUT->notification(
            get_string('deletesummaryshort', 'local_th_framework_study', [
                'deleted'  => $deleted,
                'notfound' => $notfound
            ]),
            'success'
        );

        echo $OUTPUT->footer();
        exit;
    }
    // IMPORT MODE
    $inserted = 0;
    $skipped = 0;

    foreach ($preview as $p) {
        if (empty($p['userid'])) continue;

        // kiểm tra bản ghi tồn tại
        $existing = $DB->get_record('th_subject_exemption', [
            'userid' => $p['userid'],
            'subject_code' => $p['subject']
        ]);

        if ($existing) {
            $skipped++;
            continue;
        }

        // nếu chưa có → insert
        $record = new stdClass();
        $record->userid = $p['userid'];
        $record->subject_code = $p['subject'];
        $record->timecreated = time();
        $record->timemodified = 0;
        $record->created_by = $USER->id;

        // LẤY categoryid đầu tiên từ shortname được cấu hình trong setting
        $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');
        $ma_hoc_vien_list = array_map('trim', explode(',', $setting_ma_hoc_vien));

        foreach ($ma_hoc_vien_list as $fieldshortname) {
            if (empty($fieldshortname)) continue;

            $categoryid = $DB->get_field_sql("
                SELECT f.categoryid
                FROM {user_info_field} f
                JOIN {user_info_data} d ON d.fieldid = f.id
                WHERE f.shortname = :shortname
                AND d.userid = :userid
                LIMIT 1
            ", ['shortname' => $fieldshortname, 'userid' => $p['userid']]);

            if ($categoryid) {
                $record->major_code = $categoryid;
                break;
            }
        }

        $DB->insert_record('th_subject_exemption', $record);
        $inserted++;
    }
    unset($SESSION->exemption_action, $SESSION->th_exemption_preview);
    
    $mform->display();
    echo $OUTPUT->notification(
        get_string('importsummaryshort', 'local_th_framework_study', [
        'inserted' => $inserted,
        'skipped'  => $skipped
    ]), 
    'success');
    echo $OUTPUT->footer();
    exit;    
}

// Hiển thị form ban đầu
$mform->display();
echo $OUTPUT->footer();
