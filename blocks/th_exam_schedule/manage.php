<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();
$context = context_system::instance();
require_capability('block/th_exam_schedule:manageimports', $context);

global $DB, $PAGE, $OUTPUT;

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/manage.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manage', 'block_th_exam_schedule'));
$PAGE->set_heading(get_string('manage', 'block_th_exam_schedule'));
$PAGE->navbar->add(get_string('manage', 'block_th_exam_schedule'), $PAGE->url);

$deleteid = optional_param('delete', 0, PARAM_INT);

if ($deleteid && confirm_sesskey()) {
    $DB->delete_records('block_thexamschedule_exam', ['id' => $deleteid]);
    redirect(new moodle_url('/blocks/th_exam_schedule/manage.php'), get_string('deleted'));
}

$sql = "
    SELECT
        e.id,
        e.studentcode,
        e.coursecode,
        c.coursename,
        e.examdate,
        e.examslot,
        e.timemodified
    FROM {block_thexamschedule_exam} e
    LEFT JOIN {block_thexamschedule_course} c ON c.coursecode = e.coursecode
    ORDER BY e.studentcode ASC, e.examdate ASC, e.coursecode ASC, e.examslot ASC
";

$records = $DB->get_records_sql($sql, []);

echo $OUTPUT->header();
block_th_exam_schedule_tabs($context, $PAGE->url);
echo $OUTPUT->heading(get_string('manage', 'block_th_exam_schedule'), 3);

if ($records) {
    $table = new html_table();
    $table->attributes = [
        'class' => 'generaltable',
        'border' => '1',
        'style' => 'width:100%; text-align:center;',
    ];

    $table->head = [
        get_string('col_no', 'block_th_exam_schedule'),
        get_string('studentcode', 'block_th_exam_schedule'),
        get_string('col_coursecode', 'block_th_exam_schedule'),
        get_string('col_coursename', 'block_th_exam_schedule'),
        get_string('col_examdate', 'block_th_exam_schedule'),
        get_string('col_examslot', 'block_th_exam_schedule'),
        get_string('timemodified', 'block_th_exam_schedule'),
        get_string('actions'),
    ];

    $i = 1;
    foreach ($records as $r) {
        $editurl = new moodle_url('/blocks/th_exam_schedule/edit.php', ['id' => $r->id]);
        $deleteurl = new moodle_url('/blocks/th_exam_schedule/manage.php', [
            'delete' => $r->id,
            'sesskey' => sesskey(),
        ]);

        $actions = html_writer::link($editurl, get_string('edit')) . ' | ' .
            html_writer::link(
                $deleteurl,
                get_string('delete'),
                ['onclick' => "return confirm('" . get_string('confirmdelete', 'block_th_exam_schedule') . "');"]
            );

        $table->data[] = [
            $i++,
            s($r->studentcode),
            s($r->coursecode),
            s($r->coursename ?? ''),
            userdate((int)$r->examdate, '%d/%m/%Y'),
            s($r->examslot),
            $r->timemodified ? userdate((int)$r->timemodified, '%d/%m/%Y') : '-',
            $actions,
        ];
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('norecords', 'block_th_exam_schedule'), 'info');
}
$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.generaltable', get_string('manage', 'block_th_exam_schedule'), $lang));
echo $OUTPUT->footer();
