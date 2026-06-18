<?php

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/th_report_grading_requests/th_report_grading_requests_form.php';

global $DB, $OUTPUT, $PAGE, $USER;


$PAGE->set_url('/blocks/th_report_grading_requests/view.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('report_grading_requests', 'block_th_report_grading_requests'));
$PAGE->set_heading(get_string('report_grading_requests', 'block_th_report_grading_requests'));


$mform = new th_report_grading_requests_form();


if ($mform->is_submitted() && $mform->is_validated()) {
    $data = $mform->get_data();


    $from_date = $data->from_date;
    $to_date = $data->to_date;
    $courseid = $data->courseid;
    $status = $data->id;

	$config = get_config('block_th_report_grading_requests');
  $th_crm_code = get_config('block_th_report_grading_requests', 'th_crm_code');

    $params = [
        'courseid' => $courseid,
        'fromtime' => $from_date, 
        'totime' => $to_date + 86400 - 1, 
        'th_crm_code' => $th_crm_code, 
    ];

    $status_filter = '';
    // if ($status == 1) {
    //     $status_filter = "AND ag.confirm_grading = 1";
    // } elseif ($status == 0) {
    //     $status_filter = "AND ag.confirm_grading = 0";
    // }
    $course_filter = '';
    if (!empty($courseid)) {
        if (is_array($courseid)) {
            list($in_sql, $in_params) = $DB->get_in_or_equal($courseid, SQL_PARAMS_NAMED);
            $course_filter = "AND c.id $in_sql";
            $params = array_merge($params, $in_params);
        } else {
            $course_filter = "AND c.id = :courseid";
            $params['courseid'] = $courseid;
        }
    }
//     $totalRequestsSql = "
//     SELECT SUM(at.numberturns - at.unused) AS total_requests
//     FROM mdl_th_assign_turns at
// ";
// $totalRequestsRecord = $DB->get_record_sql($totalRequestsSql);
// $totalRequests = $totalRequestsRecord->total_requests ?? 0; 
$sql = "
SELECT DISTINCT 
  qa.id, 
  qa.attempt, 
  q.name AS quizname, 
  qa.timefinish AS timefinish,  
  c.fullname AS coursename, 
  c.shortname AS coursecode, 
  CONCAT(u.firstname, ' ', u.lastname) AS username, 
  (SELECT SUM(at.numberturns - at.unused) 
  FROM {th_assign_turns} at 
   WHERE at.userid = qa.userid
  ) AS total_grading_requests, 
  sr.time AS request_time,
  uif.shortname AS fieldname, 
  uid.data AS fieldvalue,
  (SELECT COUNT(*) 
   FROM {quiz_attempts} sub_qa 
   WHERE sub_qa.userid = qa.userid 
     AND sub_qa.quiz = qa.quiz 
     AND sub_qa.timefinish <= qa.timefinish
  ) AS attempt_number
FROM 
  {quiz_attempts} qa
JOIN 
  {quiz} q ON qa.quiz = q.id
JOIN 
  {course} c ON q.course = c.id
JOIN 
  {user} u ON qa.userid = u.id
LEFT JOIN 
  {block_th_scoring_request} sr ON sr.userid = qa.userid AND sr.quizid = qa.quiz AND sr.attemptid = qa.attempt
LEFT JOIN 
  {user_info_data} uid ON uid.userid = u.id
LEFT JOIN 
  {user_info_field} uif ON uid.fieldid = uif.id
WHERE
  qa.timefinish BETWEEN :fromtime AND :totime
  $course_filter
  $status_filter
  AND sr.time IS NOT NULL 
  AND uif.shortname = :th_crm_code 
ORDER BY 
  qa.userid, qa.quiz, qa.timefinish;
";

    $records = $DB->get_records_sql($sql, $params);
  
    $totalRequests = count($records);
$table = new html_table();
$table->head = array(
    'STT', 
    'Tên khóa học', 
    'Mã khóa ', 
    'Tên bài kiểm tra', 
    'Họ và tên', 
    'Mã AUM', 
    'Ngày yêu cầu',
    'Tổng số lượt ',
);


$stt = 0;
foreach ($records as $record) {
    $stt++;
    $quiz_with_attempt = sprintf('%s, lần làm bài thứ %d', $record->quizname, $record->attempt_number);
    $row = array(
        $stt,
        $record->coursename,
        $record->coursecode,
        $quiz_with_attempt, 
        $record->username,

        $record->fieldvalue,
        !empty($record->request_time) ? userdate($record->request_time, '%d/%m/%Y') : '',
     
        ($stt === 1) ? $totalRequests : '',
       // ($stt === 1) ? $totalRequests : '', 
     
    );
    $table->data[] = $row;
}
if (empty($table->data)) {
    $table->data[] = array('', '', '', '', '', '', '', '', '', '');
   
}
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_grading_requests', 'block_th_report_grading_requests'));


$mform->display();

if (!empty($table->data)) {
    // echo html_writer::table($table);
    $table->attributes = array('class' => 'table', 'border' => '1');
    $html = html_writer::table($table);
    $lang = current_language();
    $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo gán chấm điểm", $lang));
    echo $html;
} elseif ($mform->is_submitted()) {
    echo $OUTPUT->notification(get_string('nodata', 'block_th_report_grading_requests'), 'notifyproblem');
} if (empty($records)) {
    echo '<p style="text-align: center; color: red;">Không có dữ liệu</p>';
}

echo $OUTPUT->footer();
