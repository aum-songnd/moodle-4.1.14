<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/upload_mapping_form.php');

require_login();
$context = context_system::instance();
require_capability('local/th_framework_study:manageframeworkstudy', $context);

global $PAGE, $OUTPUT, $DB, $SESSION;

$PAGE->set_url(new moodle_url('/local/th_framework_study/subjectcode/upload.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('uploadmapping', 'local_th_framework_study'));
$PAGE->set_heading(get_string('subjectcodemapping', 'local_th_framework_study'));

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('subjectcodemapping', 'local_th_framework_study') . '</center>');
local_th_subjectcode_tabs(context_system::instance(), $PAGE->url);

$mform = new upload_mapping_form();

// ======================
// CANCEL
// ======================
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_framework_study/subjectcode/manage.php'));
}

// ======================
// SUBMIT (UPLOAD + PREVIEW)
// ======================
else if ($data = $mform->get_data()) {

    $draft = file_get_submitted_draft_itemid('mappingfile');
    file_save_draft_area_files(
        $draft,
        $context->id,
        'local_th_framework_study',
        'subjectcodemapping',
        0,
        ['subdirs' => 0]
    );

    $fs = get_file_storage();
    $files = $fs->get_area_files(
        $context->id,
        'local_th_framework_study',
        'subjectcodemapping',
        0,
        'id',
        false
    );

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

    // ===== Validate header =====
    $header = array_map('trim', $rows[0]);
    $header = array_filter($header, fn($c) => $c !== '');
    $expected = ['current_code', 'old_code'];

    $lowerheader = array_map('strtolower', $header);

    if ($lowerheader !== $expected) {
        echo $OUTPUT->notification(get_string('invalid_excel_format', 'local_th_framework_study'), 'error');
        unlink($filepath);
        $mform->display();
        echo $OUTPUT->footer();
        exit;
    }

    $preview = [];
    $seen = []; // lưu mapping đã gặp trong file

    foreach ($rows as $index => $row) {
        if ($index === 0) continue;

        if (empty(array_filter($row, fn($v) => trim($v) !== ''))) {
            continue;
        }

        $current = trim($row[0]);
        $old     = trim($row[1]);

        if ($current === '' || $old === '') {
            $preview[] = [
                'current' => $current,
                'old'     => $old,
                'status'  => get_string('invalid', 'local_th_framework_study'),
                'action'  => 'invalid'
            ];
            continue;
        }

        // 🔑 KEY DUY NHẤT
        $key = strtolower($current . '|' . $old);

        // ❌ TRÙNG TRONG FILE
        if (isset($seen[$key])) {
            $preview[] = [
                'current' => $current,
                'old'     => $old,
                'status'  => get_string('duplicateinfile', 'local_th_framework_study'),
                'action'  => 'skip'
            ];
            continue;
        }

        $seen[$key] = true;

        // ❌ TRÙNG TRONG DB
        $exists = $DB->record_exists('th_subject_code_mapping', [
            'current_code' => $current,
            'old_code'     => $old
        ]);

        if ($exists) {
            $preview[] = [
                'current' => $current,
                'old'     => $old,
                'status'  => get_string('exists', 'local_th_framework_study'),
                'action'  => 'skip'
            ];
        } else {
            $preview[] = [
                'current' => $current,
                'old'     => $old,
                'status'  => get_string('insert', 'local_th_framework_study'),
                'action'  => 'insert'
            ];
        }
    }

    unlink($filepath);

    $SESSION->subjectcode_mapping_preview = $preview;

    // ===== Preview table =====
    $table = new html_table();
    $table->head = [
        get_string('currentcode', 'local_th_framework_study'),
        get_string('oldcode', 'local_th_framework_study'),
        get_string('status', 'local_th_framework_study')
    ];

    foreach ($preview as $p) {
        $table->data[] = [$p['current'], $p['old'], $p['status']];
    }

    echo html_writer::table($table);

    $confirmurl = new moodle_url($PAGE->url, [
        'confirm' => 1,
        'sesskey' => sesskey()
    ]);

    echo $OUTPUT->confirm(
        get_string('confirmuploadmapping', 'local_th_framework_study'),
        $confirmurl,
        new moodle_url('/local/th_framework_study/subjectcode/upload.php')
    );

    echo $OUTPUT->footer();
    exit;
}


// ======================
// CONFIRM IMPORT
// ======================
else if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {

    $preview = $SESSION->subjectcode_mapping_preview ?? [];

    $inserted = 0;
    $skipped  = 0;

    foreach ($preview as $p) {
        if (($p['action'] ?? '') !== 'insert') {
            $skipped++;
            continue;
        }

        $record = (object)[
            'current_code' => $p['current'],
            'old_code'     => $p['old'],
            'timecreated'  => time()
        ];

        $DB->insert_record('th_subject_code_mapping', $record);
        $inserted++;
    }

    unset($SESSION->subjectcode_mapping_preview);

    echo $OUTPUT->notification(
        get_string('importsummaryshort', 'local_th_framework_study', [
            'inserted' => $inserted,
            'skipped'  => $skipped
        ]),
        'success'
    );

    redirect(new moodle_url('/local/th_framework_study/subjectcode/manage.php'));
}


// ======================
// DEFAULT FORM
// ======================
$mform->display();
echo $OUTPUT->footer();
