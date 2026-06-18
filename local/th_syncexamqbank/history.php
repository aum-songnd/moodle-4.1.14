<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/th_syncexamqbank/th_syncexamqbank_form2.php');

require_login();
$context = context_system::instance();
require_capability('local/th_syncexamqbank:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/th_syncexamqbank/history.php'));
$PAGE->set_title(get_string('historytitle', 'local_th_syncexamqbank'));
$PAGE->set_heading(get_string('historytitle', 'local_th_syncexamqbank'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('pluginname', 'local_th_syncexamqbank'), new moodle_url('/local/th_syncexamqbank/view.php'));
$PAGE->navbar->add(get_string('historytitle', 'local_th_syncexamqbank'));

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('historytitle', 'local_th_syncexamqbank') . '</center>');

// --- Hiển thị form chọn đợt mở môn ---
$mform = new th_syncexamqbank_form2();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_syncexamqbank/history.php'));
} else if ($data = $mform->get_data()) {
    $selectedstartdate = $data->startdate;
} else {
    $selectedstartdate = optional_param('startdate', 0, PARAM_INT);
}

$mform->display();

// --- Hiển thị bảng log ---
if (!empty($selectedstartdate)) {

    echo html_writer::tag('h4',
        get_string('syncresultfor', 'local_th_syncexamqbank',
            userdate($selectedstartdate, '%d/%m/%Y'))
    );

    $logs = $DB->get_records('local_th_syncexamqbank_log',
        ['coursestartdate' => $selectedstartdate],
        'timecreated DESC',
        '*',
        0,
        50
    );

    if ($logs) {
        $table = new html_table();
        $table->head = [
            get_string('time', 'local_th_syncexamqbank'),
            get_string('coursestartdate', 'local_th_syncexamqbank'),
            get_string('user', 'local_th_syncexamqbank'),
            get_string('sourcecourse', 'local_th_syncexamqbank'),
            get_string('targetcourse', 'local_th_syncexamqbank'),
            get_string('questioncount', 'local_th_syncexamqbank'),
            get_string('details', 'local_th_syncexamqbank')
        ];

        foreach ($logs as $log) {
            $user = $DB->get_record('user', ['id' => $log->userid],
                'id, firstname, lastname, alternatename, middlename, lastnamephonetic, firstnamephonetic');
            $source = $DB->get_record('course', ['id' => $log->sourcecourseid], 'id, fullname');
            $target = $DB->get_record('course', ['id' => $log->targetcourseid], 'id, fullname');

            $sourcecourse = $source
                ? html_writer::link(new moodle_url('/course/view.php', ['id' => $source->id]), format_string($source->fullname))
                : 'N/A';
            $targetcourse = $target
                ? html_writer::link(new moodle_url('/course/view.php', ['id' => $target->id]), format_string($target->fullname))
                : 'N/A';
            $username = $user ? fullname($user) : 'N/A';
            $logdetails = html_writer::tag('details',
                html_writer::tag('summary', get_string('viewdetails', 'local_th_syncexamqbank')) .
                html_writer::tag('pre', s($log->logdetail), [
                    'style' => 'white-space: pre-wrap; overflow-x: auto; word-break: normal;'
                ])
            );

            $table->data[] = [
                userdate($log->timecreated, '%d/%m/%Y %H:%M'),
                userdate($log->coursestartdate, '%d/%m/%Y'),
                $username,
                $sourcecourse,
                $targetcourse,
                $log->syncedcount,
                $logdetails
            ];
        }

        $table->attributes['class'] = 'generaltable table table-bordered';
        echo html_writer::table($table);

    } else {
        echo $OUTPUT->notification(get_string('synclogsnotfound', 'local_th_syncexamqbank'), 'info');
    }
} else {
    echo $OUTPUT->notification(get_string('pleaseselectstartdate', 'local_th_syncexamqbank'), 'info');
}

echo $OUTPUT->footer();

