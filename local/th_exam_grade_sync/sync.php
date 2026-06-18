<?php
require_once(__DIR__ . '/../../config.php');

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/th_exam_grade_sync:manage', $context);

// nhận examdate thay vì courseid
// $examdate = required_param('examdate', PARAM_INT);
$selecttype   = required_param('selecttype', PARAM_ALPHA);
$examdate     = optional_param('examdate', 0, PARAM_INT);
$examcourseid = optional_param('examcourseid', 0, PARAM_INT);


$PAGE->set_context($context);

$service = new \local_th_exam_grade_sync\service\sync_service();

if ($selecttype === 'bycourse' && $examcourseid) {
    $result = $service->sync_by_examcourse($examcourseid);
} else {
    // mặc định: theo ngày
    $result = $service->sync_by_examdate($examdate);
}


$type = ($result['errors'] > 0) ? \core\output\notification::NOTIFY_WARNING
                                : \core\output\notification::NOTIFY_SUCCESS;
$params = ['selecttype' => $selecttype];

if ($selecttype === 'bycourse') {
    $params['examcourseid'] = $examcourseid;
} else {
    $params['examdate'] = $examdate;
}

redirect(
    new moodle_url('/local/th_exam_grade_sync/view.php', $params),
    get_string('syncresult', 'local_th_exam_grade_sync', (object)$result),
    2,
    $type
);

