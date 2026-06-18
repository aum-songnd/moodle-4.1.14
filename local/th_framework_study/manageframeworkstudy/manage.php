<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');

require_login();
$context = context_system::instance();
require_capability('local/th_framework_study:manageframeworkstudy', $context);

global $DB, $PAGE, $OUTPUT;

$delete = optional_param('delete', 0, PARAM_INT);

// Kiểm tra cài đặt
$enable_train_code = (bool)get_config('local_th_framework_study', 'enable_train_code');
$skip = (bool)get_config('local_th_framework_study', 'skip_slot_target');

$PAGE->set_url(new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manageframeworkstudy', 'local_th_framework_study'));
$PAGE->set_heading(get_string('manageframeworkstudy', 'local_th_framework_study'));
$PAGE->navbar->add(
    get_string('manageframeworkstudy', 'local_th_framework_study'),
    new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php')
);

if ($delete && confirm_sesskey()) {
    $DB->delete_records('th_framework_study', ['id' => $delete]);
    redirect(
        new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php'),
        get_string('frameworkdeleted', 'local_th_framework_study')
    );
}

// Sort phù hợp
$sort = $skip ? 'subject_code ASC' : 'slot ASC';
$frameworks = $DB->get_records('th_framework_study', null, $sort);

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('manageframeworkstudy', 'local_th_framework_study') . '</center>');
local_th_framework_study_tabs($context, $PAGE->url);

if ($frameworks) {
    $table = new html_table();
    $table->attributes = [
        'class' => 'th_framework_study display',
        'border' => '1',
        'style' => 'width: 100%; text-align:center;'
    ];

    $table->head = [
        get_string('slot', 'local_th_framework_study'),
        get_string('subjectname', 'local_th_framework_study'),
        get_string('subjectcode', 'local_th_framework_study'),
        get_string('credit', 'local_th_framework_study'),
        get_string('majorcode', 'local_th_framework_study'),
        get_string('targetcode', 'local_th_framework_study'),
        get_string('timecreated', 'local_th_framework_study'),
        get_string('timemodified', 'local_th_framework_study')
    ];
    
    if ($enable_train_code) {
        $table->head[] = get_string('training_type_code', 'local_th_framework_study');
    }
    
    $table->head[] = get_string('actions', 'local_th_framework_study');
    

    foreach ($frameworks as $f) {
        $editurl = new moodle_url('/local/th_framework_study/manageframeworkstudy/edit.php', ['id' => $f->id]);
        $deleteurl = new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php', [
            'delete' => $f->id,
            'sesskey' => sesskey()
        ]);

        $actions = html_writer::link($editurl, get_string('edit')) . ' | ' .
            html_writer::link(
                $deleteurl,
                get_string('delete'),
                ['onclick' => "return confirm('" . get_string('confirmdeleteframe', 'local_th_framework_study') . "');"]
            );
            $row = [];
            $row[] = (!empty($f->slot)) ? $f->slot : '-';
            
            $row[] = format_string($f->subject_name);
            $row[] = s($f->subject_code);
            $row[] = ($f->credit === null) ? '-' : $f->credit;
            $row[] = s($f->major_code);
            
            $row[] = (!empty($f->target_code)) ? s($f->target_code) : '-';
            
            $row[] = !empty($f->timecreated)
                ? userdate($f->timecreated, '%d/%m/%Y')
                : '-';
            
            $row[] = !empty($f->timemodified)
                ? userdate($f->timemodified, '%d/%m/%Y')
                : '-';
            
            if ($enable_train_code) {
                $row[] = s($f->training_type_code);
            }
            
            $row[] = $actions;
            $table->data[] = $row;
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('noframeworks', 'local_th_framework_study'), 'info');
}

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_framework_study', get_string('pluginname', 'local_th_framework_study'), $lang));
echo $OUTPUT->footer();
