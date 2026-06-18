<?php
require('../../config.php');
require_once($CFG->dirroot . '/local/th_exam_monitor/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

$courseid = required_param('id', PARAM_INT);
$groupid = optional_param('groupid',0, PARAM_TEXT);
require_login($courseid);

$context = context_course::instance($courseid);
require_capability('local/th_exam_monitor:view', $context);

$PAGE->set_url('/local/th_exam_monitor/view.php', ['id' => $courseid, 'groupid' => $groupid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', 'local_th_exam_monitor'));
$PAGE->set_heading($COURSE->fullname);
$viewurl = new moodle_url('/local/th_exam_monitor/view.php', ['id' => $courseid, 'groupid' => $groupid]);
$PAGE->navbar->add(get_string('studentlist', 'local_th_exam_monitor'), $viewurl);

echo $OUTPUT->header();

$allgroups = groups_get_all_groups($courseid);
$usergroups = groups_get_user_groups($courseid, $USER->id)[0]; // [0] là group mode
$canaccessall = has_capability('moodle/course:managegroups', $context);

// Tạo action có courseid.
$action = new moodle_url($PAGE->url);
$action->param('id', $courseid);

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => $action->out(false)
]);

// Thêm hidden để chắc chắn courseid luôn submit theo form.
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'id',
    'value' => $courseid
]);

echo html_writer::label('Chọn phòng thi: ', 'groupid');

// Build options.
$options = ['0' => 'Vui lòng chọn'];
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

if (class_exists('\core_user\fields')) {
    $userfields = \core_user\fields::for_userpic();
    $ufields_sql = $userfields->get_sql('u', false, '', '', false)->selects;
} else {
    $ufields_sql = implode(', ', [
        'u.id',
        'u.firstname', 'u.lastname', 'u.picture', 'u.imagealt', 'u.email',
        'u.firstnamephonetic', 'u.lastnamephonetic', 'u.middlename', 'u.alternatename'
    ]);
}

$studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
$enrolledstudents = [];

if ($studentroleid) {
    if(!$groupid) {
        $enrolledusers = [];
    } else if($groupid == 'all' && $canaccessall) {
        $usergroupsql = '';
        $enrolledusers = th_exam_monitor_get_enrolled_users($context, $usergroupsql, '', 0, $ufields_sql . ', u.id');

        $enrolledstudents = [];
        foreach ($enrolledusers as $user) {
            if (user_has_role_assignment($user->id, $studentroleid, $context->id)) {
                $enrolledstudents[$user->id] = $user;
            }
        }
        $suspendedusers = $DB->get_records_sql("
            SELECT DISTINCT u.id, $ufields_sql
            FROM {user} u
            JOIN {local_th_exam_monitor} m ON u.id = m.userid
            WHERE m.courseid = :courseid",
            ['courseid' => $courseid]
        );
        foreach ($suspendedusers as $user) {
            if (!isset($enrolledstudents[$user->id])) {
                $enrolledstudents[$user->id] = $user;
            }
        }
    } else {
        $usergroups = $DB->get_fieldset_select('groups_members', 'userid', 'groupid = ?', [$groupid]);
    
        if($usergroups){
            $usergroupsql = 'AND u.id IN(' . implode(',', $usergroups) . ')';
            $enrolledusers = th_exam_monitor_get_enrolled_users($context, $usergroupsql, '', 0, $ufields_sql . ', u.id');

            $enrolledstudents = [];
            foreach ($enrolledusers as $user) {
                if (user_has_role_assignment($user->id, $studentroleid, $context->id)) {
                    $enrolledstudents[$user->id] = $user;
                }
            }
            $suspendedusers = $DB->get_records_sql("
                SELECT DISTINCT u.id, $ufields_sql
                FROM {user} u
                JOIN {local_th_exam_monitor} m ON u.id = m.userid
                WHERE m.courseid = :courseid $usergroupsql",
                ['courseid' => $courseid]
            );
            foreach ($suspendedusers as $user) {
                if (!isset($enrolledstudents[$user->id])) {
                    $enrolledstudents[$user->id] = $user;
                }
            }
        } else {
            $enrolledusers = [];
        }
    }
}

echo html_writer::tag('h2', get_string('studentlist', 'local_th_exam_monitor'), ['class' => 'title', 'style' => 'text-align: center;']);

echo html_writer::start_tag('table', ['class' => 'generaltable exam-monitor-table']);
echo html_writer::start_tag('thead');
echo html_writer::start_tag('tr');
echo html_writer::tag('th', get_string('fullname'));
echo html_writer::tag('th', get_string('currentstatus', 'local_th_exam_monitor')); // ✅ Cột trạng thái mới
echo html_writer::tag('th', get_string('actionhistorytable', 'local_th_exam_monitor'));
echo html_writer::tag('th', get_string('actionstable', 'local_th_exam_monitor'));
echo html_writer::end_tag('tr');
echo html_writer::end_tag('thead');
echo html_writer::start_tag('tbody');

foreach ($enrolledstudents as $user) {
    $userid = $user->id;
    $fullname = fullname($user);

    $sql = "SELECT action_status
            FROM {local_th_exam_monitor}
            WHERE userid = :userid AND courseid = :courseid
            ORDER BY timecreated DESC";
    $params = ['userid' => $userid, 'courseid' => $courseid];
    $record = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);

    $statuslabels = [
        EXAM_ACTION_WARN => get_string('warn', 'local_th_exam_monitor'),
        EXAM_ACTION_ALERT => get_string('alert', 'local_th_exam_monitor'),
        EXAM_ACTION_SUSPEND => get_string('suspend_exam', 'local_th_exam_monitor'),
    ];

    $currentstatus = $record && isset($statuslabels[$record->action_status])
        ? $statuslabels[$record->action_status]
        : get_string('nostatus', 'local_th_exam_monitor');

    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', $fullname);
    echo html_writer::tag('td', html_writer::div($currentstatus, 'current-status', [
        'style' => 'font-weight:bold;'
    ]));

    $historyurl = new moodle_url('/local/th_exam_monitor/view_history.php', [
        'userid' => $userid,
        'courseid' => $courseid,
        'groupid' => $groupid
    ]);
    echo html_writer::tag('td',
        html_writer::link($historyurl, get_string('viewhistory', 'local_th_exam_monitor'), [
            'class' => 'view-history-button',
            'style' => 'font-weight: bold; color: #0073aa;',
        ])
    );

    $actionurl = new moodle_url('/local/th_exam_monitor/action.php', [
        'userid' => $userid,
        'courseid' => $courseid,
        'groupid' => $groupid
    ]);

    echo html_writer::tag('td',
        html_writer::link(
            $actionurl,
            get_string('actionstable', 'local_th_exam_monitor'),
            ['class' => 'btn btn-primary']
        )
    );

    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('tbody');
echo html_writer::end_tag('table');

echo $OUTPUT->footer();
