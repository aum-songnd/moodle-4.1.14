<?php
require('../../config.php');
require_once($CFG->dirroot . '/local/th_exam_monitor/lib.php');

$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$groupid = optional_param('groupid',0, PARAM_TEXT);
$context = context_course::instance($courseid);
require_login($courseid);
require_capability('local/th_exam_monitor:view', $context);
$canmanage = has_capability('local/th_exam_monitor:manage', $context);

$deleteid = optional_param('deleteid', 0, PARAM_INT);
$confirm  = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_url('/local/th_exam_monitor/view_history.php', ['userid' => $userid, 'courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('viewhistorytitle', 'local_th_exam_monitor'));
$PAGE->set_heading(get_string('viewhistoryheading', 'local_th_exam_monitor'));
$historyurl = new moodle_url('/local/th_exam_monitor/view_history.php', ['userid' => $userid, 'courseid' => $courseid]);
$viewurl = new moodle_url('/local/th_exam_monitor/view.php', ['id' => $courseid]);
$PAGE->navbar->add(get_string('studentlist', 'local_th_exam_monitor'), $viewurl);
$PAGE->navbar->add(get_string('viewhistorytitle', 'local_th_exam_monitor'), $historyurl);

if ($deleteid && confirm_sesskey()) {
    require_capability('local/th_exam_monitor:manage', $context);

    if ($confirm) {
        // 1️⃣ LẤY CONTEXT
        $context = context_course::instance($courseid);

        // 2️⃣ XÓA FILE TRONG FILEAREA
        $fs = get_file_storage();
        $fs->delete_area_files(
            $context->id,
            'local_th_exam_monitor',
            'note',
            $deleteid
        );

        $DB->delete_records('local_th_exam_monitor', ['id' => $deleteid]);
        redirect(
            new moodle_url('/local/th_exam_monitor/view_history.php', [
                'userid' => $userid,
                'courseid' => $courseid
            ]),
            get_string('recorddeleted', 'local_th_exam_monitor'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(
            get_string('confirmdeletehistory', 'local_th_exam_monitor'),
            new moodle_url($PAGE->url, [
                'userid' => $userid,
                'courseid' => $courseid,
                'deleteid' => $deleteid,
                'confirm' => 1,
                'sesskey' => sesskey()
            ]),
            new moodle_url($PAGE->url, [
                'userid' => $userid,
                'courseid' => $courseid
            ])
        );
        echo $OUTPUT->footer();
        exit;
    }
}

echo $OUTPUT->header();

$actionstatuslabels = [
    EXAM_ACTION_WARN => get_string('warn', 'local_th_exam_monitor'),
    EXAM_ACTION_ALERT => get_string('alert', 'local_th_exam_monitor'),
    EXAM_ACTION_SUSPEND => get_string('suspend_exam', 'local_th_exam_monitor'),
];

$sql = "SELECT id, action_status, note, timecreated
        FROM {local_th_exam_monitor}
        WHERE userid = :userid AND courseid = :courseid
        ORDER BY timecreated DESC";
$params = ['userid' => $userid, 'courseid' => $courseid];
$records = $DB->get_records_sql($sql, $params);
echo html_writer::tag('h3', get_string('violationhistory', 'local_th_exam_monitor'));
if ($records) {

    $table = new html_table();
    $table->head = [
        get_string('time', 'local_th_exam_monitor'),
        get_string('status', 'local_th_exam_monitor'),
        get_string('note', 'local_th_exam_monitor')
    ];

    if ($canmanage) {
        $table->head[] = get_string('action');
    }
   
    $table->data = [];

    foreach ($records as $record) {
        $status = $actionstatuslabels[$record->action_status]
            ?? get_string('unknownstatus', 'local_th_exam_monitor');

        $context = context_course::instance($courseid);

        // 1️⃣ Rewrite URL file
        $note = file_rewrite_pluginfile_urls(
            $record->note,
            'pluginfile.php',
            $context->id,
            'local_th_exam_monitor',
            'note',
            $record->id
        );

        // 2️⃣ Render HTML + ảnh
        $note = format_text(
            $note,
            FORMAT_HTML,
            [
                'context' => $context,
                'noclean' => true,
                'filter' => true,
            ]
        );

        $time = userdate($record->timecreated);

        if ($canmanage) {
            $deleteurl = new moodle_url('/local/th_exam_monitor/view_history.php', [
                'userid' => $userid,
                'courseid' => $courseid,
                'deleteid' => $record->id,
                'sesskey' => sesskey()
            ]);

            $deletebtn = html_writer::link(
                $deleteurl,
                get_string('delete'),
                ['class' => 'btn btn-danger btn-sm']
            );
        } else {
            $deletebtn = ''; // Không có quyền → không hiện gì
        }

        $row = [$time, $status, $note];

        if ($canmanage) {
            $row[] = $deletebtn;
        }

        $table->data[] = $row;
    }

    echo html_writer::start_div('exam-monitor-history');
    echo html_writer::table($table);
    echo html_writer::end_div();
} else {
    echo html_writer::tag('p', get_string('violationhistoryempty', 'local_th_exam_monitor'));
}

$backurl = new moodle_url('/local/th_exam_monitor/view.php', [
    'id' => $courseid,
    'groupid' => $groupid
]);

echo html_writer::link(
    $backurl,
    html_writer::tag('button', get_string('back'), [
        'type' => 'button',
        'class' => 'btn btn-secondary'
    ])
);

$PAGE->requires->js_init_code("
    document.addEventListener('click', function(e) {
        if (e.target.closest('.exam-monitor-history') && e.target.tagName === 'IMG') {

            let src = e.target.src;

            let overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = 0;
            overlay.style.left = 0;
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.background = 'rgba(0,0,0,0.9)';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.zIndex = 9999;
            overlay.style.padding = '10px';

            // Ảnh
            let img = document.createElement('img');
            img.src = src;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '100%';
            img.style.borderRadius = '8px';
            img.style.boxShadow = '0 0 20px rgba(255,255,255,0.3)';
            img.style.transition = 'transform 0.2s ease';
            img.style.transform = 'scale(0.95)';

            // Nút đóng
            let closeBtn = document.createElement('div');
            closeBtn.innerHTML = '✕';
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '15px';
            closeBtn.style.right = '20px';
            closeBtn.style.fontSize = '28px';
            closeBtn.style.color = '#fff';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.fontWeight = 'bold';
            closeBtn.style.zIndex = 10000;

            // Hover hiệu ứng
            closeBtn.onmouseover = () => closeBtn.style.color = '#ff4d4f';
            closeBtn.onmouseout = () => closeBtn.style.color = '#fff';

            // Click đóng
            closeBtn.onclick = function(e) {
                e.stopPropagation();
                document.body.removeChild(overlay);
            };

            overlay.onclick = function() {
                document.body.removeChild(overlay);
            };

            overlay.appendChild(closeBtn);
            overlay.appendChild(img);
            document.body.appendChild(overlay);

            // Animation nhẹ
            setTimeout(() => {
                img.style.transform = 'scale(1)';
            }, 10);
        }
    });
");
echo $OUTPUT->footer();
