<?php
require('../../config.php');
require_once(__DIR__ . '/classes/form/report_form.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once 'lib.php';

$id = required_param('courseid', PARAM_INT);
$groupid = optional_param('groupid',0, PARAM_TEXT);
$context = context_course::instance($id);

require_login($id);
require_capability('local/th_online_exam_manager:exam_attendance', $context);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/th_online_exam_manager/exam_attendance.php', array('courseid' => $id)));
$PAGE->set_pagelayout('course');
$PAGE->set_heading(get_string('contestreport', 'local_th_online_exam_manager'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('contestreport', 'local_th_online_exam_manager'));

echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('contestreport', 'local_th_online_exam_manager'), [
    'id' => 'report-title',
    'class' => 'text-center mb-4'
]);

$allgroups = groups_get_all_groups($id);
$usergroups = groups_get_user_groups($id, $USER->id)[0]; // [0] là group mode
$canaccessall = has_capability('moodle/course:managegroups', $context);

// Tạo action có courseid.
$action = new moodle_url($PAGE->url);
$action->param('courseid', $id);

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => $action->out(false)
]);

// Thêm hidden để chắc chắn courseid luôn submit theo form.
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'courseid',
    'value' => $id
]);

echo html_writer::label(get_string('selectexamroom', 'local_th_online_exam_manager') . ': ', 'groupid');

// Build options.
$options = ['0' => get_string('pleaseselect', 'local_th_online_exam_manager')];
if ($canaccessall) {
    $options['all'] = get_string('allparticipants');
}
$usergroupset = $canaccessall ? null : array_flip((array)$usergroups);
foreach ($allgroups as $g) {
    if ($canaccessall || isset($usergroupset[$g->id])) {
        $options[(string)$g->id] = format_string($g->name);
    }
}

// Select + auto submit.
echo html_writer::select(
    $options,
    'groupid',
    (string)$groupid,
    null,
    ['id' => 'groupid', 'onchange' => 'this.form.submit()']
);

// Fallback khi tắt JS.
echo html_writer::tag('noscript',
    html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('go')])
);

echo html_writer::end_tag('form');

$quizids = [];
$courseids = [$id];
$errors = '';
$quizids = load_quizzes_in_courses($courseids);  

if (empty($errors)) {
    if(!$groupid) {
        $enrolmap = [];
    } else if($groupid == 'all' && $canaccessall) {
        $enrolmap = load_users_enrolled_in_courses($courseids);
    } else {
        $enrolmap = load_users_in_groups($groupid);
    }
    
    $userids = array_keys($enrolmap);

    if (empty($userids)) {
        $errors .= html_writer::div(get_string('nodatafound', 'local_th_online_exam_manager'), 'alert alert-danger');
    }
}

if (!empty($errors)) {
    echo $errors;
}

if (empty($errors)) {
    require_once(__DIR__ . '/classes/report/report_enroll_table.php');
    $table = new \local_th_online_exam_manager\report\report_enroll_table(
        'report_enroll_table',
        $userids,
        $courseids,
        '',
        $quizids,
        $enrolmap
    );

    $table->attributes['id'] = 'report_table';
    $table->attributes['style'] = 'width: 100%; text-align: center;';
    $table->attributes['border'] = 1;
    $table->define_baseurl(new moodle_url('/local/th_online_exam_manager/exam_attendance.php', array('courseid' => $id)));
    $table->setup();
    $table->query_db(0);
    $table->out(0, false);

    $lang = current_language();
    echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('#report_table', 'Report', $lang));
}

echo $OUTPUT->footer();