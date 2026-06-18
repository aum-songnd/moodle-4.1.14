<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/th_exam_grade_sync:manage', $context);

const ERROR   = 'ERROR';
const OK   = 'OK';

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/local/th_exam_grade_sync/view.php'));
$PAGE->set_title(get_string('heading', 'local_th_exam_grade_sync'));
$PAGE->set_heading(get_string('heading', 'local_th_exam_grade_sync'));

$PAGE->navbar->add(
    get_string('pluginname', 'local_th_exam_grade_sync'),
    new moodle_url('/local/th_exam_grade_sync/view.php')
);

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('heading', 'local_th_exam_grade_sync') . '</center>');

$form = new \local_th_exam_grade_sync\form\select_session_form();

$form->display();

if ($data = $form->get_data()) {

    // $examdate = (int)$data->examdate;

    // $service = new \local_th_exam_grade_sync\service\sync_service();
    // $rows = $service->build_preview_by_examdate($examdate);
    $service = new \local_th_exam_grade_sync\service\sync_service();

if ($data->selecttype === 'bydate') {
    $examdate = (int)$data->examdate;
    $rows = $service->build_preview_by_examdate($examdate);

    $title = get_string(
        'previewtitle',
        'local_th_exam_grade_sync',
        userdate($examdate, '%d/%m/%Y')
    );

} else {
    $examcourseid = (int)$data->examcourseid;
    $rows = $service->build_preview_by_examcourse($examcourseid);

    $course = get_course($examcourseid);
    $title = format_string($course->fullname);
}

echo html_writer::tag('h3', $title);
 
    $table = new html_table();
    $table->attributes = [
        'class' => 'th_exam_grade_sync ',
        'border' => '1',
        'style' => 'width: 100%; text-align:center;'
    ];
    $table->head = [
        get_string('student', 'local_th_exam_grade_sync'),
        get_string('examcourse', 'local_th_exam_grade_sync'),
        get_string('score', 'local_th_exam_grade_sync'),
        get_string('learncourse', 'local_th_exam_grade_sync'),
        get_string('status', 'local_th_exam_grade_sync'),
        get_string('note', 'local_th_exam_grade_sync'),
    ];

    $table->align = [
        'center',
        'center',
        'center',
        'center',
        'center',
        'center',
    ];
    
    foreach ($rows as $r) {
        $statuslabel = '';

        switch ($r->status) {
            case ERROR:
                $statuslabel = get_string('status_error', 'local_th_exam_grade_sync');
                break;

            case OK:
                $statuslabel = get_string('status_ok', 'local_th_exam_grade_sync');
                break;

            default:
                $statuslabel = s($r->status);
        }

        $table->data[] = [
            s($r->fullname),
            $r->examcourse ? s($r->examcourse->fullname) : '-',
            $r->score !== null ? format_float($r->score, 2) : '-',
            $r->learncourse ? s($r->learncourse->fullname) : '-',
            $statuslabel,
            s($r->message),
        ];

    }

    echo html_writer::table($table);

    $params = [
        'selecttype' => $data->selecttype,
        'sesskey'    => sesskey(),
    ];
    
    if ($data->selecttype === 'bydate') {
        $params['examdate'] = $examdate;
    } else {
        $params['examcourseid'] = $examcourseid;
    }
    
    $syncurl = new moodle_url('/local/th_exam_grade_sync/sync.php', $params);
    

    $logparams = [
        'selecttype' => $data->selecttype,
    ];
    
    if ($data->selecttype === 'bydate') {
        $logparams['examdate'] = $examdate;
    } else {
        $logparams['examcourseid'] = $examcourseid;
    }
    
    $logurl = new moodle_url('/local/th_exam_grade_sync/log.php', $logparams);
    
    echo html_writer::div(
        html_writer::link($syncurl, get_string('syncbtn', 'local_th_exam_grade_sync'), ['class' => 'btn btn-primary mr-2']) .
        html_writer::link($logurl, get_string('historybtn', 'local_th_exam_grade_sync'), ['class' => 'btn btn-secondary']),
        'mt-3'
    );    
}


echo $OUTPUT->footer();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('input[type=radio][name=selecttype]').change(function() {
            $('#fitem_id_examdate .col-form-label label').removeAttr('hidden');
            $('#fitem_id_examcourseid .col-form-label label').removeAttr('hidden');
        });
    });
</script>

