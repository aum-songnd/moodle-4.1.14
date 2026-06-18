<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/form/import_courses_form.php');

require_login();
$context = context_system::instance();
require_capability('block/th_exam_schedule:manageimports', $context);

global $DB, $USER, $OUTPUT, $PAGE;

$preview = optional_param('preview', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/import_courses.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('addcourse', 'block_th_exam_schedule'));
$PAGE->set_heading(get_string('addcourse', 'block_th_exam_schedule'));
$PAGE->navbar->add(get_string('addcourse', 'block_th_exam_schedule'),
    new moodle_url('/blocks/th_exam_schedule/import_courses.php'));

$form = new \block_th_exam_schedule\form\import_courses_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/my/'));
    exit;
}

/**
 * 1) CONFIRM: ghi DB từ preview trong session (redirect xong exit)
 */
if ($confirm && confirm_sesskey()) {
    $items = $_SESSION['th_exam_schedule_courses_preview'] ?? null;

    if (!empty($items) && is_array($items)) {
        $transaction = $DB->start_delegated_transaction();
        try {
            $r = \block_th_exam_schedule\local\importer::apply_courses_preview($items);
            unset($_SESSION['th_exam_schedule_courses_preview']);

            $transaction->allow_commit();

            // Build placeholder object for lang string.
            $a = (object)[
                'new' => $r['new'] ?? 0,
                'update' => $r['update'] ?? 0,
                'same' => $r['same'] ?? 0,
                'invalid' => $r['invalid'] ?? 0,
            ];

            redirect(
                new moodle_url('/blocks/th_exam_schedule/import_courses.php'),
                get_string('importcoursesuccessmsg', 'block_th_exam_schedule', $a),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
            exit;
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            // fall through to show page with error
            $confirmerror = $e->getMessage();
        }
    } else {
        $confirmerror = get_string('missingpreviewdata', 'block_th_exam_schedule');
    }
}

/**
 * 2) UPLOAD: đọc file + tạo preview -> redirect qua preview page (redirect xong exit)
 * Chỉ chạy khi KHÔNG ở preview mode.
 */
if (!$preview && ($data = $form->get_data())) {
    $fs = get_file_storage();
    $files = $fs->get_area_files(context_user::instance($USER->id)->id, 'user', 'draft', $data->coursesfile, 'id', false);

    if (empty($files)) {
        throw new moodle_exception('nofile');
    }

    $file = reset($files);

    try {
        $rows = \block_th_exam_schedule\local\importer::read_rows_from_storedfile($file);
        $items = \block_th_exam_schedule\local\importer::preview_courses($rows);

        $_SESSION['th_exam_schedule_courses_preview'] = $items;

        redirect(new moodle_url('/blocks/th_exam_schedule/import_courses.php', ['preview' => 1]));
        exit;
    } catch (\Throwable $e) {
        $uploaderror = $e->getMessage();
        // fall through to show page with error
    }
}

/**
 * ===== TỪ ĐÂY MỚI BẮT ĐẦU OUTPUT =====
 */
echo $OUTPUT->header();
block_th_exam_schedule_tabs($context, $PAGE->url);

// show any errors collected before output
if (!empty($confirmerror)) {
    echo $OUTPUT->notification($confirmerror, 'error');
}
if (!empty($uploaderror)) {
    echo $OUTPUT->notification($uploaderror, 'error');
}

/**
 * 3) PREVIEW PAGE: hiển thị bảng preview + nút confirm
 */
if ($preview) {
    $items = $_SESSION['th_exam_schedule_courses_preview'] ?? [];
    echo $OUTPUT->heading(get_string('previewimportcourses', 'block_th_exam_schedule'), 3);

    if (empty($items)) {
        echo $OUTPUT->notification(get_string('nodataforpreview', 'block_th_exam_schedule'), 'info');
        echo $OUTPUT->footer();
        exit;
    }

    $statusmap = [
        'NEW' => get_string('status_new', 'block_th_exam_schedule'),
        'UPDATE' => get_string('status_update', 'block_th_exam_schedule'),
        'SAME' => get_string('status_same', 'block_th_exam_schedule'),
        'INVALID' => get_string('status_invalid', 'block_th_exam_schedule'),
    ];

    $t = new html_table();
    $t->attributes = ['class' => 'generaltable', 'style' => 'width:100%; text-align:center;'];
    $t->head = [
        get_string('col_no', 'block_th_exam_schedule'),
        get_string('col_status', 'block_th_exam_schedule'),
        get_string('col_coursecode', 'block_th_exam_schedule'),
        get_string('col_coursename', 'block_th_exam_schedule'),
    ];

    $i = 1;
    foreach ($items as $it) {
        $st = strtoupper((string)($it['status'] ?? ''));
        //$label = $statusmap[$st] ?? get_string('status_unknown', 'block_th_exam_schedule');
        $label = !empty($it['statuslabel'])
    ? (string)$it['statuslabel']
    : ($statusmap[$st] ?? get_string('status_unknown', 'block_th_exam_schedule'));


        $t->data[] = [
            $i++,
            s($label),
            s($it['coursecode'] ?? ''),
            s($it['coursename'] ?? ''),
        ];
    }

    echo html_writer::table($t);

    $confirmurl = new moodle_url('/blocks/th_exam_schedule/import_courses.php', [
        'confirm' => 1,
        'sesskey' => sesskey(),
    ]);
    $cancelurl = new moodle_url('/blocks/th_exam_schedule/import_courses.php');

    echo html_writer::div(
        html_writer::link($confirmurl, get_string('confirmimport', 'block_th_exam_schedule'), ['class' => 'btn btn-primary']) . ' ' .
        html_writer::link($cancelurl, get_string('cancel', 'block_th_exam_schedule'), ['class' => 'btn btn-secondary']),
        'mt-3'
    );

    echo $OUTPUT->footer();
    exit;
}

// Default: show upload form
$form->display();
echo $OUTPUT->footer();
