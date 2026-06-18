<?php
require('../../config.php');
require_once($CFG->dirroot . '/local/th_exam_monitor/lib.php');

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$groupid = optional_param('groupid',0, PARAM_TEXT);

require_login($courseid);
$context = context_course::instance($courseid);
require_capability('local/th_exam_monitor:view', $context);
$PAGE->set_url('/local/th_exam_monitor/report.php', ['courseid' => $courseid, 'groupid' => $groupid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('report_exam', 'local_th_exam_monitor'));
$PAGE->set_heading(get_string('report_exam', 'local_th_exam_monitor'));

echo $OUTPUT->header();
echo html_writer::tag('h3', get_string('violationreportheading', 'local_th_exam_monitor'), ['class' => 'report-heading', 'style' => 'text-align: center;']);

$context = context_course::instance($courseid);
$allgroups = groups_get_all_groups($courseid);
$usergroups = groups_get_user_groups($courseid, $USER->id)[0]; // [0] là group mode
$canaccessall = has_capability('moodle/course:managegroups', $context);

// Tạo action có courseid.
$action = new moodle_url($PAGE->url);
$action->param('courseid', $courseid);

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => $action->out(false)
]);

// Thêm hidden để chắc chắn courseid luôn submit theo form.
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'courseid',
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

$setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');
$setting_classcode = get_config('local_thlib', 'classcodeshortname');
$ma_hoc_vien1 = explode(',', $setting_ma_hoc_vien)[0] ?? '';
$classcode1 = explode(',', $setting_classcode)[0] ?? '';

if(!$groupid) {
    $records = [];
} else if($groupid == 'all' && $canaccessall) {
    $usergroupsql = '';

    $sql = "SELECT em.*, 
               u.firstname, u.lastname, uid.data AS studentcode, uid2.data AS classcode,
               u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
               c.fullname AS coursename, c.shortname
        FROM {local_th_exam_monitor} em
        JOIN {user} u ON u.id = em.userid
        LEFT JOIN {user_info_field} uif ON uif.shortname = :mahv
        LEFT JOIN {user_info_data} uid ON uid.userid = u.id AND uid.fieldid = uif.id
        LEFT JOIN {user_info_field} uif2 ON uif2.shortname = :classcode
        LEFT JOIN {user_info_data} uid2 ON uid2.userid = u.id AND uid2.fieldid = uif2.id
        JOIN {course} c ON c.id = em.courseid
        WHERE c.id = :courseid $usergroupsql
        ORDER BY em.timecreated DESC";

    $params = [
        'courseid' => $courseid,
        'mahv' => $ma_hoc_vien1,
        'classcode' => $classcode1,
    ];

    $records = $DB->get_records_sql($sql, $params);
} else {
    $usergroups = $DB->get_fieldset_select('groups_members', 'userid', 'groupid = ?', [$groupid]);

    if($usergroups){
        $usergroupsql = 'AND em.userid IN(' . implode(',', $usergroups) . ')';

        $sql = "SELECT em.*, 
                    u.firstname, u.lastname, uid.data AS studentcode, uid2.data AS classcode,
                    u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
                    c.fullname AS coursename, c.shortname
                FROM {local_th_exam_monitor} em
                JOIN {user} u ON u.id = em.userid
                LEFT JOIN {user_info_field} uif ON uif.shortname = :mahv
                LEFT JOIN {user_info_data} uid ON uid.userid = u.id AND uid.fieldid = uif.id
                LEFT JOIN {user_info_field} uif2 ON uif2.shortname = :classcode
                LEFT JOIN {user_info_data} uid2 ON uid2.userid = u.id AND uid2.fieldid = uif2.id
                JOIN {course} c ON c.id = em.courseid
                WHERE c.id = :courseid $usergroupsql
                ORDER BY em.timecreated DESC";

        $params = [
            'courseid' => $courseid,
            'mahv' => $ma_hoc_vien1,
            'classcode' => $classcode1,
        ];

        $records = $DB->get_records_sql($sql, $params);
    } else {
        $records = [];
    }
}

if ($records) {
    $actions = [
        EXAM_ACTION_WARN => get_string('warn', 'local_th_exam_monitor'),
        EXAM_ACTION_ALERT => get_string('alert', 'local_th_exam_monitor'),
        EXAM_ACTION_SUSPEND => get_string('suspend_exam', 'local_th_exam_monitor'),
    ];

    $table = new html_table();
    $table->id = 'reporttable';
    $table->attributes['style'] = 'width: 100%; text-align: center;';
    $table->attributes['border'] = 1;

    // Header.
    $table->head = [
        get_string('serialnumber', 'local_th_exam_monitor'),
        get_string('fullname', 'local_th_exam_monitor'),
        get_string('classcode', 'local_th_exam_monitor'),
        get_string('studentcode', 'local_th_exam_monitor'),
        get_string('coursename', 'local_th_exam_monitor'),
        get_string('shortname', 'local_th_exam_monitor'),
        get_string('processingtime', 'local_th_exam_monitor'),
        get_string('actionlevel', 'local_th_exam_monitor'),
        get_string('violationreason', 'local_th_exam_monitor'),
    ];

    // Body.
    $table->data = [];

    $i = 1;
    foreach ($records as $r) {
        $fullname = html_writer::link(
            new moodle_url('/user/profile.php', ['id' => $r->userid]),
            fullname($r)
        );

        $note = file_rewrite_pluginfile_urls(
            $r->note,
            'pluginfile.php',
            $context->id,
            'local_th_exam_monitor',
            'note',
            $r->id
        );

        $note = format_text(
            $note,
            FORMAT_HTML,
            [
                'context' => $context,
                'noclean' => true,
                'filter' => true,
            ]
        );

        $table->data[] = [
            $i++,
            $fullname,
            $r->classcode,
            $r->studentcode,
            $r->coursename,
            $r->shortname,
            userdate($r->timecreated, '%H:%M - %e/%m/%Y'),
            $actions[$r->action_status] ?? get_string('undefined', 'local_th_exam_monitor'),
            $note,
        ];
    }

    // Output.
    echo html_writer::table($table);

} else {
    echo $OUTPUT->notification(get_string('nodatafound', 'local_th_exam_monitor'), 'info');
}

$lang = current_language();
echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_th_exam_monitor/datatable', 'init', ['#reporttable', get_string('violationreportheading', 'local_th_exam_monitor'), $lang]);

$PAGE->requires->js_init_code("
    document.addEventListener('click', function(e) {
        const img = e.target.closest('#reporttable img');
        if (!img) {
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'th-image-viewer';

        const fullimg = document.createElement('img');
        fullimg.src = img.src;
        fullimg.alt = img.alt || '';

        const closeBtn = document.createElement('div');
        closeBtn.className = 'th-image-viewer-close';
        closeBtn.innerHTML = '&times;';

        function closeViewer(ev) {
            if (ev) {
                ev.stopPropagation();
            }
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }

        closeBtn.addEventListener('click', closeViewer);
        overlay.addEventListener('click', closeViewer);

        fullimg.addEventListener('click', function(ev) {
            ev.stopPropagation();
        });

        document.addEventListener('keydown', function escHandler(ev) {
            if (ev.key === 'Escape') {
                closeViewer();
                document.removeEventListener('keydown', escHandler);
            }
        });

        overlay.appendChild(closeBtn);
        overlay.appendChild(fullimg);
        document.body.appendChild(overlay);
    });
");
echo $OUTPUT->footer();
