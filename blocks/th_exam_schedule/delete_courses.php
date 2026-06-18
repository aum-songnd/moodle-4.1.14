<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/form/delete_courses_form.php');

require_login();
$context = context_system::instance();
require_capability('block/th_exam_schedule:manageimports', $context);

global $DB, $USER, $OUTPUT, $PAGE;

$preview = optional_param('preview', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/delete_courses.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('deletecourses', 'block_th_exam_schedule'));
$PAGE->set_heading(get_string('deletecourses', 'block_th_exam_schedule'));
$PAGE->navbar->add(get_string('deletecourses', 'block_th_exam_schedule'),
    new moodle_url('/blocks/th_exam_schedule/delete_courses.php'));

$form = new \block_th_exam_schedule\form\delete_courses_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/my/')); exit;
}

if ($confirm && confirm_sesskey()) {
    $items = $_SESSION['th_exam_schedule_delete_courses_preview'] ?? null;

    if (!empty($items) && is_array($items)) {
        $tx = $DB->start_delegated_transaction();
        try {
            $r = \block_th_exam_schedule\local\importer::apply_delete_courses_preview($items);
            unset($_SESSION['th_exam_schedule_delete_courses_preview']);
            $tx->allow_commit();

            redirect(
                new moodle_url('/blocks/th_exam_schedule/delete_courses.php'),
                "Xóa môn học xong. Đã xóa: {$r['delete']}, Trùng/bỏ qua: {$r['same']}, Dòng lỗi: {$r['invalid']}",
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
            exit;
        } catch (\Throwable $e) {
            $tx->rollback($e);
            $err = $e->getMessage();
        }
    } else {
        $err = 'Không tìm thấy dữ liệu preview (hết hạn).';
    }
}

if (!$preview && ($data = $form->get_data())) {
    $fs = get_file_storage();
    $files = $fs->get_area_files(context_user::instance($USER->id)->id, 'user', 'draft', $data->coursesfile, 'id', false);
    if (empty($files)) throw new moodle_exception('nofile');
    $file = reset($files);

    try {
        $rows = \block_th_exam_schedule\local\importer::read_rows_from_storedfile($file);
        $items = \block_th_exam_schedule\local\importer::preview_delete_courses($rows);
        $_SESSION['th_exam_schedule_delete_courses_preview'] = $items;

        redirect(new moodle_url('/blocks/th_exam_schedule/delete_courses.php', ['preview' => 1]));
        exit;
    } catch (\Throwable $e) {
        $err = $e->getMessage();
    }
}

echo $OUTPUT->header();
block_th_exam_schedule_tabs($context, $PAGE->url);

if (!empty($err)) echo $OUTPUT->notification($err, 'error');

if ($preview) {
    $items = $_SESSION['th_exam_schedule_delete_courses_preview'] ?? [];
    echo $OUTPUT->heading('Preview xóa môn học', 3);

    if (empty($items)) {
        echo $OUTPUT->notification('Không có dữ liệu preview.', 'info');
        echo $OUTPUT->footer(); exit;
    }

    $t = new html_table();
    $t->attributes = ['class' => 'generaltable', 'style' => 'width:100%; text-align:center;'];
    $t->head = ['STT', 'Trạng thái', 'Mã môn'];

    $i = 1;
    foreach ($items as $it) {
        $t->data[] = [
            $i++,
            s((string)($it['statuslabel'] ?? '')),
            s((string)($it['coursecode'] ?? '')),
        ];
    }

    echo html_writer::table($t);

    $confirmurl = new moodle_url('/blocks/th_exam_schedule/delete_courses.php', ['confirm'=>1, 'sesskey'=>sesskey()]);
    $cancelurl  = new moodle_url('/blocks/th_exam_schedule/delete_courses.php');

    echo html_writer::div(
        html_writer::link($confirmurl, 'XÁC NHẬN XÓA', ['class' => 'btn btn-danger']) . ' ' .
        html_writer::link($cancelurl, 'HỦY', ['class' => 'btn btn-secondary']),
        'mt-3'
    );

    echo $OUTPUT->footer(); exit;
}

$form->display();
echo $OUTPUT->footer();
