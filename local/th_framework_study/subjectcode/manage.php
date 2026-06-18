<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
require_capability('local/th_framework_study:manageframeworkstudy', context_system::instance());

$PAGE->set_url(new moodle_url('/local/th_framework_study/subjectcode/manage.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Subject code mapping');
$PAGE->set_heading('Subject code mapping');
$PAGE->navbar->add('Subject code mapping', new moodle_url('/local/th_framework_study/subjectcode/manage.php'));

$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    th_framework_subjectcode_delete($delete);
    redirect(
        new moodle_url('/local/th_framework_study/subjectcode/manage.php'),
        'Đã xóa mapping',
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('subjectcodemapping', 'local_th_framework_study') . '</center>');
local_th_subjectcode_tabs(context_system::instance(), $PAGE->url);

$records = th_framework_subjectcode_get_list();

$table = new html_table();
$table->head = ['ID', get_string('currentcode', 'local_th_framework_study'), get_string('oldcode', 'local_th_framework_study'), 'Hành động'];
$table->attributes = [
        'class' => 'th_subjectcode display',
        'border' => '1',
        'style' => 'width: 100%; text-align:center;'
    ];
foreach ($records as $r) {
    $editurl = new moodle_url('/local/th_framework_study/subjectcode/edit.php', ['id' => $r->id]);

    $deleteurl = new moodle_url(
        $PAGE->url,
        [
            'delete'  => $r->id,
            'sesskey' => sesskey()
        ]
    );

    $table->data[] = [
        $r->id,
        s($r->current_code),
        s($r->old_code),
        html_writer::link($editurl, get_string('edit')) . ' | ' .
        html_writer::link(
            $deleteurl,
            get_string('delete'),
            [
                'onclick' => "return confirm('" .
                    get_string('confirmdeleteframe', 'local_th_framework_study') .
                "');"
            ]
        )
    ];
}

echo html_writer::table($table);
$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_subjectcode', get_string('pluginname', 'local_th_framework_study'), $lang));
echo $OUTPUT->footer();
