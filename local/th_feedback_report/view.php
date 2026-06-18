<?php

require_once('../../config.php');
require_once('lib.php');

global $DB, $OUTPUT, $PAGE, $USER;

require_login();

$context = context_system::instance();

$baseurl = new moodle_url('/local/th_feedback_report/view.php');
$title = get_string('my_feedback_reports', 'local_th_feedback_report');

$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $baseurl);

$records = $DB->get_records_sql(
    "SELECT *
       FROM {local_th_feedback_report}
      WHERE userid = :userid
   ORDER BY id DESC",
    ['userid' => $USER->id]
);

$component = 'local_th_feedback_report';
$filearea = 'screenshot';

$table = new html_table();
$table->head = [
    get_string('index', 'local_th_feedback_report'),
    get_string('description', 'local_th_feedback_report'),
    get_string('url', 'local_th_feedback_report'),
    get_string('attached_image', 'local_th_feedback_report'),
    get_string('sent_time', 'local_th_feedback_report'),
    get_string('status', 'local_th_feedback_report'),
    get_string('processing_time', 'local_th_feedback_report'),
    get_string('response_content', 'local_th_feedback_report'),
    get_string('response_history', 'local_th_feedback_report')
];

$statusoptions = [
    0 => get_string('not_processed', 'local_th_feedback_report'),
    1 => get_string('processing', 'local_th_feedback_report'),
    2 => get_string('processed', 'local_th_feedback_report'),
];

$count = 0;

foreach ($records as $record) {
    $count++;

    $row = new html_table_row();

    $row->cells[] = new html_table_cell($count);

    $descriptioncell = new html_table_cell(s($record->description));
    $descriptioncell->style = 'max-width: 250px; word-wrap: break-word; white-space: normal;';
    $row->cells[] = $descriptioncell;

    $urlcell = new html_table_cell(s($record->url));
    $urlcell->style = 'max-width: 250px; word-wrap: break-word; white-space: normal;';
    $row->cells[] = $urlcell;

    $fs = get_file_storage();
    $filecontext = context_system::instance();
    $files = $fs->get_area_files(
        $filecontext->id,
        $component,
        $filearea,
        $record->id,
        'itemid',
        false
    );

    if (!empty($files)) {
        $imagehtml = '';
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            );

            $imagehtml .= html_writer::link(
                $fileurl,
                get_string('view_image', 'local_th_feedback_report'),
                ['target' => '_blank', 'rel' => 'noopener noreferrer']
            );
        }
    } else {
        $imagehtml = 'N/A';
    }

    $row->cells[] = new html_table_cell($imagehtml);

    $row->cells[] = new html_table_cell(date('H:i:s d-m-Y', $record->timecreated));

    $status = $statusoptions[$record->status] ?? get_string('unknown', 'local_th_feedback_report');
    $row->cells[] = new html_table_cell($status);

    if (!empty($record->timemodified)) {
        $row->cells[] = new html_table_cell(date('H:i:s d-m-Y', $record->timemodified));
    } else {
        $row->cells[] = new html_table_cell('N/A');
    }

    $responsehtml = '';

    if (!empty($record->response)) {
        $responsehtml .= html_writer::tag('div', s($record->response));
    } else {
        $responsehtml .= 'N/A';
    }
    
    $responsecell = new html_table_cell($responsehtml);
    $responsecell->style = 'max-width: 300px; word-wrap: break-word; white-space: normal;';
    $row->cells[] = $responsecell;
    
    $historyurl = new moodle_url('/local/th_feedback_report/response_history.php', [
        'id' => $record->id,
        'returnurl' => (new moodle_url('/local/th_feedback_report/view.php'))->out(false)
    ]);
    
    $historyhtml = html_writer::link(
        $historyurl,
        get_string('view_response_history', 'local_th_feedback_report'),
        [
            'class' => 'btn btn-secondary',
            'style' => 'white-space: nowrap;'
        ]
    );
    
    $row->cells[] = new html_table_cell($historyhtml);

    $table->data[] = $row;
}

$table->attributes = [
    'class' => 'myfeedbackreport',
    'border' => 1
];
$table->width = '100%';

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if (empty($records)) {
    echo $OUTPUT->notification(
        get_string('no_feedback_reports', 'local_th_feedback_report'),
        \core\output\notification::NOTIFY_INFO
    );
} else {
    echo html_writer::table($table);
}

$lang = current_language();
echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', ['.myfeedbackreport', get_string('my_feedback_reports', 'local_th_feedback_report'), $lang]);


echo $OUTPUT->footer();