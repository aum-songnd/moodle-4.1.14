<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/th_syncexamqbank/th_syncexamqbank_form1.php');

require_login();
$context = context_system::instance();
require_capability('local/th_syncexamqbank:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/th_syncexamqbank/view.php'));
$PAGE->set_title(get_string('pluginname', 'local_th_syncexamqbank'));
$PAGE->set_heading(get_string('pluginname', 'local_th_syncexamqbank'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('pluginname', 'local_th_syncexamqbank'), new moodle_url('/local/th_syncexamqbank/view.php'));

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('heading', 'local_th_syncexamqbank') . '</center>');

$mform = new th_syncexamqbank_form1();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/search.php'), get_string('cancelled', 'local_th_syncexamqbank'));
} else if ($data = $mform->get_data()) {
    $startdate = $data->startdate;

    require_once($CFG->dirroot . '/local/th_syncexamqbank/classes/sync_manager.php');
    require_once($CFG->dirroot . '/local/th_syncexamqbank/classes/task/sync_exam_qbank_task.php');

    $task = new \local_th_syncexamqbank\task\sync_exam_qbank_task();
    $task->set_custom_data((object)[
        'startdate' => $startdate,
        'userid' => $USER->id,
    ]);

    // Đưa vào hàng đợi background
    \core\task\manager::queue_adhoc_task($task);

    echo $OUTPUT->notification(
        get_string('sync_running_notice', 'local_th_syncexamqbank'),
        'notifysuccess'
    );
    
    echo html_writer::link(
        new moodle_url('/local/th_syncexamqbank/history.php'),
        get_string('view_sync_log', 'local_th_syncexamqbank'),
        ['class' => 'btn btn-primary mt-3']
    );

    echo $OUTPUT->footer();
    exit;
}

$mform->display();
echo $OUTPUT->footer();
