<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/lib.php');
require_once('lib.php');

global $DB, $USER;

// $courseid = required_param('courseid', PARAM_INT);
$courseid = $COURSE->id;
$status = required_param('status', PARAM_TEXT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($courseid);
require_capability('local/th_online_exam_manager:regrade', $context);

$PAGE->set_url('/local/th_online_exam_manager/regrade_assignments_list.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('assigned_regrade_requests', 'local_th_online_exam_manager'));
$PAGE->set_pagelayout('course');

echo $OUTPUT->header();
$baseurl = new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', array('courseid'=> $context->instanceid, 'status' => $status));

if ($editcontrols = th_regrade_assignments_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}

echo $OUTPUT->heading(get_string('all_assigned_regrade_requests', 'local_th_online_exam_manager'));

$sql = "SELECT r.*, q.name AS quizname, c.fullname AS coursename
        FROM {th_exam_regrade_requests} r
        JOIN {quiz} q ON q.id = r.quizid
        JOIN {course} c ON c.id = q.course
        WHERE r.reviewerid = :userid
          AND r.status = :status";

$params = [
    'userid' => $USER->id,
    'status' => $status
];

$regrades = $DB->get_records_sql($sql, $params);

if (empty($regrades)) {
    echo $OUTPUT->notification(get_string('noregraderequestsfound', 'local_th_online_exam_manager'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

$PAGE->requires->js_call_amd('local_th_online_exam_manager/status_toggle', 'init');

$table = new html_table();
$table->head = [
    get_string('course', 'local_th_online_exam_manager'),
    get_string('quiz', 'local_th_online_exam_manager'),
    get_string('student', 'local_th_online_exam_manager'),
    get_string('attemptid', 'local_th_online_exam_manager'),
    get_string('reviewer', 'local_th_online_exam_manager'),
    get_string('status', 'local_th_online_exam_manager'),
    get_string('note', 'local_th_online_exam_manager'),
    get_string('grade', 'local_th_online_exam_manager'),
    get_string('timeassigned', 'local_th_online_exam_manager'),
    get_string('review', 'local_th_online_exam_manager'),
    get_string('actions', 'local_th_online_exam_manager')
];
$table->data = [];

foreach ($regrades as $r) {
    $quizname = $r->quizname;
    $coursename = $r->coursename;
    $student = core_user::get_user($r->userid);
    $reviewer = $r->reviewerid ? core_user::get_user($r->reviewerid) : null;

    $statusoptions = [
        'pending' => get_string('pending', 'local_th_online_exam_manager'),
        'regraded' => get_string('regraded', 'local_th_online_exam_manager'),
        // 'waitingreview' => get_string('waitingreview', 'local_th_online_exam_manager'),
    ];

    if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
        $statusoptions['reviewed'] = get_string('reviewed', 'local_th_online_exam_manager');
    }
    
    $statusselect = html_writer::select(
        $statusoptions,                            // options
        'status_' . $r->id,                        // name
        $r->status,                                // default selected
        false,                                     // no 'choose'
        [
            'class' => 'regrade-status-select',
            'data-id' => $r->id
        ]
    );

    // Nút ghi chú
    $noteurl = new moodle_url('/local/th_online_exam_manager/regrade_note.php', ['id' => $r->id, 'status' => $status]);
    $token = encrypt_attempt_id($r->attemptid);

    $review_url = new moodle_url('/mod/quiz/report/thessaygrader/review.php', ['attempt_token' => $token, 'action' => 'regrade', 'status' => $status]);
    $noteaction = html_writer::link($noteurl, get_string('note', 'local_th_online_exam_manager'), ['class' => 'btn btn-secondary btn-sm']);
    $reviewaction = html_writer::link($review_url, get_string('review', 'local_th_online_exam_manager'), ['class' => 'btn btn-secondary btn-sm']);

    $actions = $noteaction;

    $table->data[] = [
        $coursename,
        format_string($quizname),
        fullname($student),
        $r->attemptid,
        $reviewer ? fullname($reviewer) : '-',
        $statusselect,
        $r->note,
        $r->grade ? format_float($r->grade, 2) : get_string('nochange', 'local_th_online_exam_manager'),
        userdate($r->timerequested),
        $reviewaction,
        $actions
    ];
}

$table->attributes['class'] = 'report_table';
$table->attributes['style'] = 'width: 100%; text-align: center;';
$table->attributes['border'] = 1;

echo html_writer::table($table);
$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.report_table', 'Report', $lang));
echo $OUTPUT->footer();

