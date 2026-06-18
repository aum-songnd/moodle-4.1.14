<?php
require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/th_exam_grade_sync:manage', $context);

const ERROR   = 'ERROR';
const UPDATED   = 'UPDATED';

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/th_exam_grade_sync/log.php'));
$PAGE->set_title(get_string('synchistorytitle', 'local_th_exam_grade_sync'));
$PAGE->set_heading(get_string('synchistorytitle', 'local_th_exam_grade_sync'));
$PAGE->navbar->add(
    get_string('pluginname', 'local_th_exam_grade_sync'),
    new moodle_url('/local/th_exam_grade_sync/log.php')
);

global $DB, $OUTPUT;

// Lấy log (mới nhất trước)
$sql = "
SELECT l.*, 
       u.id AS userid,
       u.firstname,
       u.lastname,
       u.firstnamephonetic,
       u.lastnamephonetic,
       u.middlename,
       u.alternatename,
       c1.fullname AS examcoursename,
       c2.fullname AS learncoursename
      FROM {local_gradesync_log} l
 LEFT JOIN {user} u ON u.id = l.userid
 LEFT JOIN {course} c1 ON c1.id = l.examcourseid
 LEFT JOIN {course} c2 ON c2.id = l.learncourseid
  ORDER BY l.timecreated DESC
";

$logs = $DB->get_records_sql($sql);

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('synchistorytitle', 'local_th_exam_grade_sync') . '</center>');

$backurl = new moodle_url('/local/th_exam_grade_sync/view.php');
echo html_writer::div(
    html_writer::link(
        $backurl,
        get_string('backtoview', 'local_th_exam_grade_sync'),
        ['class' => 'btn btn-secondary mb-3']
    )
);

if (!$logs) {
    echo $OUTPUT->notification(get_string('nosynchistory', 'local_th_exam_grade_sync'), 'info');
    echo $OUTPUT->footer();
    exit;
}

// Tạo tableđược
$table = new html_table();
$table->attributes = [
    'class' => 'th_log_exam_grade_sync ',
    'border' => '1',
    'style' => 'width: 100%; text-align:center;'
];
$table->head = [
    get_string('time', 'local_th_exam_grade_sync'),
    get_string('student', 'local_th_exam_grade_sync'),
    get_string('examcourse', 'local_th_exam_grade_sync'),
    get_string('learncourse', 'local_th_exam_grade_sync'),
    get_string('score', 'local_th_exam_grade_sync'),
    get_string('status', 'local_th_exam_grade_sync'),
    get_string('note', 'local_th_exam_grade_sync')
];

$table->align = [
    'center',
    'center',
    'center',
    'center',
    'center',
    'center',
    'center',
];

$table->data = [];

foreach ($logs as $log) {
    $statuslabel = '';

    switch ($log->status) {
        case ERROR:
            $statuslabel = get_string('status_error', 'local_th_exam_grade_sync');
            break;

        case UPDATED:
            $statuslabel = get_string('status_updated', 'local_th_exam_grade_sync');
            break;

        default:
            $statuslabel = s($log->status);
    }

    $table->data[] = [
        userdate($log->timecreated, '%d/%m/%Y %H:%M:%S'),
        fullname($log),
        $log->examcoursename ?? '-',
        $log->learncoursename ?? '-',
        $log->examscore !== null ? format_float($log->examscore, 2) : '-',
        $statuslabel,
        s($log->message)
    ];

}

echo html_writer::table($table);

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_log_exam_grade_sync', get_string('pluginname', 'local_th_exam_grade_sync'), $lang));

echo $OUTPUT->footer();
