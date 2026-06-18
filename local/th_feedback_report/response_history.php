<?php

require_once('../../config.php');

global $DB, $OUTPUT, $PAGE, $USER;

require_login();

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$context = context_system::instance();
$PAGE->set_context($context);

$report = $DB->get_record('local_th_feedback_report', ['id' => $id], '*', MUST_EXIST);

$statusoptions = [
    0 => get_string('not_processed', 'local_th_feedback_report'),
    1 => get_string('processing', 'local_th_feedback_report'),
    2 => get_string('processed', 'local_th_feedback_report'),
];

$canviewall = has_capability('local/th_feedback_report:view', $context);

if (!$canviewall && (int)$report->userid !== (int)$USER->id) {
    throw new required_capability_exception(
        $context,
        'local/th_feedback_report:view',
        'nopermissions',
        ''
    );
}

$url = new moodle_url('/local/th_feedback_report/response_history.php', ['id' => $id]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('response_history', 'local_th_feedback_report'));
$PAGE->set_heading(get_string('response_history', 'local_th_feedback_report'));
$PAGE->navbar->add(get_string('response_history', 'local_th_feedback_report'), $url);


$namefields = \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;
if (!empty($namefields)) {
    $namefields = ', ' . $namefields;
}

$responses = $DB->get_records_sql(
    "SELECT r.*, u.email {$namefields}
       FROM {th_feedback_responses} r
       LEFT JOIN {user} u ON u.id = r.userid
      WHERE r.reportid = :reportid
   ORDER BY r.timecreated DESC",
    ['reportid' => $id]
);
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('response_history', 'local_th_feedback_report'));

echo html_writer::tag(
    'p',
    html_writer::tag('strong', get_string('description', 'local_th_feedback_report') . ': ')
    . s($report->description)
);

if (empty($responses)) {
    echo $OUTPUT->notification(
        get_string('no_response_history', 'local_th_feedback_report'),
        \core\output\notification::NOTIFY_INFO
    );
} else {
    $table = new html_table();

    $table->head = [
        get_string('index', 'local_th_feedback_report'),
        get_string('status', 'local_th_feedback_report'),
        get_string('responder', 'local_th_feedback_report'),
        get_string('response_content', 'local_th_feedback_report'),
        get_string('response_time', 'local_th_feedback_report')
    ];

    $count = 0;

    foreach ($responses as $response) {
        $count++;

        if (!empty($response->userid)) {
            $responderinfo = fullname($response) . html_writer::empty_tag('br') . s($response->email ?? '');
        } else {
            $responderinfo = get_string('unknown', 'local_th_feedback_report');
        }

        $responsecell = new html_table_cell(s($response->response));
        $responsecell->style = 'max-width: 500px; white-space: normal; word-wrap: break-word;';

        if ($response->status === null) {
            $statusname = get_string('unknown', 'local_th_feedback_report');
        } else {
            $statusname = $statusoptions[(int)$response->status] ?? get_string('unknown', 'local_th_feedback_report');
        }

        $table->data[] = [
            $count,
            s($statusname),
            $responderinfo,
            $responsecell,
            userdate($response->timecreated)
        ];
    }

    $table->attributes = [
        'class' => 'response_history',
        'border' => 1
    ];

    echo html_writer::table($table);
}

if (!empty($returnurl)) {
    $backurl = new moodle_url($returnurl);
} else {
    $backurl = $canviewall
        ? new moodle_url('/local/th_feedback_report/index.php')
        : new moodle_url('/local/th_feedback_report/view.php');
}

echo html_writer::link(
    $backurl,
    get_string('back'),
    ['class' => 'btn btn-primary']
);


$lang = current_language();
echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', ['.response_history', get_string('response_history', 'local_th_feedback_report'), $lang]);

echo $OUTPUT->footer();