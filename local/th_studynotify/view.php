<?php
require('../../config.php');

require_login();
require_capability('local/th_studynotify:view', context_system::instance());

global $PAGE, $OUTPUT, $DB;

$action = optional_param('action', 'list', PARAM_ALPHA);
$id     = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/local/th_studynotify/view.php');
$PAGE->set_url($baseurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('holiday_calendar_title', 'local_th_studynotify'));
$PAGE->set_heading(get_string('holiday_calendar_heading', 'local_th_studynotify'));
$PAGE->navbar->add(
    get_string('pluginname', 'local_th_studynotify'),
    $baseurl
);

if ($action === 'delete' && $id) {
    require_sesskey();
    $DB->delete_records('local_th_studynotify_holiday', ['id' => $id]);
    redirect($baseurl, get_string('holiday_deleted', 'local_th_studynotify'));
}

if ($action === 'add' || $action === 'edit') {

    require_once(__DIR__ . '/classes/form/holiday_form.php');

    $record = null;
    if ($action === 'edit' && $id) {
        $record = $DB->get_record(
            'local_th_studynotify_holiday',
            ['id' => $id],
            '*',
            MUST_EXIST
        );
    }

    $formurl = new moodle_url('/local/th_studynotify/view.php', [
        'action' => $action,
        'id'     => $id
    ]);

    $mform = new \local_th_studynotify\form\holiday_form(
        $formurl,
        ['record' => $record]
    );

    if ($mform->is_cancelled()) {
        redirect($baseurl);

    } else if ($data = $mform->get_data()) {
        require_sesskey();

        $data->timeend      = $data->timeend + DAYSECS - 1;
        $data->timemodified = time();

        if (!empty($data->id)) {
            $DB->update_record('local_th_studynotify_holiday', $data);
        } else {
            unset($data->id);
            $data->timecreated = time();
            $DB->insert_record('local_th_studynotify_holiday', $data);
        }

        redirect($baseurl, get_string('holiday_saved', 'local_th_studynotify'));
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('holiday_date_ranges_heading', 'local_th_studynotify'));

    $mform->display();
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('holiday_date_ranges_heading', 'local_th_studynotify'));

$records = $DB->get_records(
    'local_th_studynotify_holiday',
    null,
    'batchstart ASC, timestart ASC'
);

// Nút thêm mới
echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/th_studynotify/view.php', ['action' => 'add']),
        get_string('holiday_add_button', 'local_th_studynotify'),
        ['class' => 'btn btn-primary']
    ),
    'mb-3'
);

if (empty($records)) {
    echo $OUTPUT->notification(
        get_string('holiday_no_records', 'local_th_studynotify'),
        'info'
    );

} else {
    echo html_writer::start_tag('table', [
        'class' => 'generaltable boxaligncenter',
        'style' => 'border-collapse: collapse;'
    ]);

    echo html_writer::tag('tr',
        html_writer::tag('th', get_string('holiday_col_batch', 'local_th_studynotify'), ['style' => 'border:1px solid #ccc; padding:4px;']) .
        html_writer::tag('th', get_string('holiday_col_from', 'local_th_studynotify'), ['style' => 'border:1px solid #ccc; padding:4px;']) .
        html_writer::tag('th', get_string('holiday_col_to', 'local_th_studynotify'), ['style' => 'border:1px solid #ccc; padding:4px;']) .
        html_writer::tag('th', get_string('holiday_col_description', 'local_th_studynotify'), ['style' => 'border:1px solid #ccc; padding:4px;']) .
        html_writer::tag('th', get_string('holiday_col_actions', 'local_th_studynotify'), ['style' => 'border:1px solid #ccc; padding:4px;'])
    );

    $confirmdelete = addslashes(get_string('holiday_confirm_delete', 'local_th_studynotify'));

    foreach ($records as $r) {
        $editurl = new moodle_url('/local/th_studynotify/view.php', [
            'action' => 'edit',
            'id'     => $r->id
        ]);

        $delurl = new moodle_url('/local/th_studynotify/view.php', [
            'action'  => 'delete',
            'id'      => $r->id,
            'sesskey' => sesskey()
        ]);

        $batchtext = !empty($r->batchstart)
            ? date('d/m/Y', $r->batchstart)
            : '';

        $editlink = html_writer::link(
            $editurl,
            get_string('holiday_action_edit', 'local_th_studynotify')
        );

        $deletelink = html_writer::link(
            $delurl,
            get_string('holiday_action_delete', 'local_th_studynotify'),
            ['onclick' => "return confirm('{$confirmdelete}');"]
        );

        echo html_writer::tag('tr',
            html_writer::tag('td', $batchtext, ['style' => 'border:1px solid #ccc; padding:4px;']) .
            html_writer::tag('td', date('d/m/Y', $r->timestart), ['style' => 'border:1px solid #ccc; padding:4px;']) .
            html_writer::tag('td', date('d/m/Y', $r->timeend), ['style' => 'border:1px solid #ccc; padding:4px;']) .
            html_writer::tag('td', format_string($r->description), ['style' => 'border:1px solid #ccc; padding:4px;']) .
            html_writer::tag('td', $editlink . ' | ' . $deletelink, ['style' => 'border:1px solid #ccc; padding:4px;'])
        );
    }

    echo html_writer::end_tag('table');
}

echo $OUTPUT->footer();
