<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$context = context_system::instance();
require_capability('block/th_exam_schedule:manageimports', $context);

global $DB, $PAGE, $OUTPUT;

$PAGE->set_url(new moodle_url('/blocks/th_exam_schedule/manage_courses.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('managecourses', 'block_th_exam_schedule'));
$PAGE->set_heading(get_string('managecourses', 'block_th_exam_schedule'));
$PAGE->navbar->add(get_string('managecourses', 'block_th_exam_schedule'), $PAGE->url);

$deleteid = optional_param('delete', 0, PARAM_INT);

if ($deleteid && confirm_sesskey()) {
    $DB->delete_records('block_thexamschedule_course', ['id' => $deleteid]);
    redirect(new moodle_url('/blocks/th_exam_schedule/manage_courses.php'), get_string('deleted'));
}

$courses = $DB->get_records('block_thexamschedule_course', null, 'coursecode ASC');

echo $OUTPUT->header();
block_th_exam_schedule_tabs($context, $PAGE->url);
echo $OUTPUT->heading(get_string('managecourses', 'block_th_exam_schedule'), 3);

if ($courses) {
    $table = new html_table();
    $table->attributes = [
        'class' => 'generaltablecourse',
        'border' => '1',
        'style' => 'width:100%; text-align:center;',
    ];

    $table->head = [
        get_string('col_no', 'block_th_exam_schedule'),
        get_string('col_coursecode', 'block_th_exam_schedule'),
        get_string('col_coursename', 'block_th_exam_schedule'),
        get_string('timemodified', 'block_th_exam_schedule'),
        get_string('actions'),
    ];

    $i = 1;
    foreach ($courses as $c) {
        $editurl = new moodle_url('/blocks/th_exam_schedule/edit_course.php', ['id' => $c->id]);
        $deleteurl = new moodle_url('/blocks/th_exam_schedule/manage_courses.php', [
            'delete' => $c->id,
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
            s($c->coursecode),
            format_string($c->coursename),
            $c->timemodified ? userdate((int)$c->timemodified, '%d/%m/%Y') : '-',
            $actions,
        ];
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('norecords', 'block_th_exam_schedule'), 'info');
}


$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.generaltablecourse', get_string('managecourses', 'block_th_exam_schedule'), $lang));
echo $OUTPUT->footer();
