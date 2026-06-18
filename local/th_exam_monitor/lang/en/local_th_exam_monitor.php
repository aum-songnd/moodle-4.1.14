<?php
$string['pluginname'] = 'TH Exam Monitor';
$string['th_exam_monitor:view'] = 'View exam monitor';
$string['selectquiz'] = 'Select a quiz to monitor';
$string['confirmaction'] = 'Are you sure you want to perform this action?';
$string['actionsaved'] = 'The action has been successfully saved.';

$string['suspend_notification_message'] = 
    'Xin chào anh/chị học viên <strong>{$a->fullname}</strong>,<br>
    Phòng thi <strong>{$a->coursename}</strong> đã bị đình chỉ do vi phạm quy chế thi.<br>
    <strong>Kết quả bài thi không được công nhận.</strong><br><br>
    Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline, 0943846035).';
$string['suspend_notification_subject'] = 'Thông báo đình chỉ bài thi';
$string['suspend_notification_short'] = 'Bài thi đã bị đình chỉ.';


$string['warn_subject'] = 'Thông báo khiển trách bài thi';
$string['warn_message'] ='Xin chào anh/chị học viên {$a->fullname},<br>
Hệ thống ghi nhận vi phạm quy chế trong phòng thi <strong>{$a->coursename}</strong>.<br>
Vi phạm đã được ghi nhận và được nhắc nhở ở mức khiển trách.<br>
Vui lòng lưu ý và tuân thủ nghiêm túc quy chế trong kỳ thi.<br><br>
Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline, 0943846035).';
$string['warn_short'] = 'Thông báo khiển trách trong bài thi.';


$string['alert_subject'] = 'Thông báo cảnh cáo bài thi';
$string['alert_message'] =
'Xin chào anh/chị học viên {$a->fullname},<br>
Hệ thống ghi nhận vi phạm quy chế trong phòng thi <strong>{$a->coursename}</strong>.<br>
Vi phạm này đã được hệ thống ghi nhận và được xử lý ở mức cảnh cáo.<br>
Nếu tiếp tục vi phạm, bài thi có thể bị đình chỉ và không được công nhận kết quả.<br><br>
Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline, 0943846035).';
$string['alert_short'] = 'Thông báo cảnh cáo trong bài thi.';

$string['view'] = 'View';
$string['studentlist'] = 'Student Exam Monitoring';
$string['update'] = 'Refresh';
$string['notattempted'] = 'Not attempted';
$string['inprogress'] = 'In progress';
$string['finished'] = 'Submitted';
$string['remainingtime'] = 'Remaining time:';
$string['selectaction'] = 'Select action';
$string['warn'] = 'Warning';
$string['alert'] = 'Alert';
$string['suspend_exam'] = 'Suspend exam';
$string['enternote'] = 'Enter violation note...';
$string['submit'] = 'Submit';
$string['viewhistory'] = 'View history';

//view-history
$string['viewhistorytitle'] = 'Violation History';
$string['viewhistoryheading'] = 'Violation History';
$string['violationhistory'] = 'Violation handling history:';
$string['violationhistoryempty'] = 'No violation history.';
$string['time'] = 'Time';
$string['status'] = 'Status';
$string['note'] = 'Note';
$string['unknownstatus'] = 'Unknown';

$string['notstarted'] = 'Not started';
$string['statussubmitted'] = 'Submitted';
$string['statusabandoned'] = 'Abandoned';

$string['param_userid'] = 'User ID';
$string['param_courseid'] = 'Course ID';
$string['param_quizid'] = 'Quiz ID';
$string['param_attemptid'] = 'Attempt ID';
$string['param_action_status'] = 'Action status as integer';
$string['param_note'] = 'Teacher\'s note';

$string['report_exam'] = 'Report exam monitor';

$string['filterbycourse'] = 'By exam room';
$string['filterbydate'] = 'By exam date';
$string['filterbyuser'] = 'By student';
$string['choosefiltertype'] = 'Choose filter type:';
$string['choosecourse'] = 'Choose exam room';
$string['examdate'] = 'Exam date';
$string['choosestudent'] = 'Choose student';
$string['actionlevel'] = 'Action level';
$string['viewreport'] = 'View report';
$string['violationreportheading'] = 'Exam Violation Handling Report';

$string['serialnumber'] = 'No.';
$string['fullname'] = 'Full name';
$string['studentcode'] = 'Student ID';
$string['coursename'] = 'Exam subject';
$string['shortname'] = 'Exam Room Code';
$string['examname'] = 'Exam subject';
$string['examstatus'] = 'Exam status';
$string['violationtime'] = 'Violation time';
$string['violationreason'] = 'Violation reason';
$string['quizfinished'] = 'Finished';
$string['quizinprogress'] = 'Inprogress';
$string['undefined'] = 'Undefined';
$string['nodatafound'] = 'No matching data found.';
$string['all'] = 'All';

$string['suspend'] = 'Suspended';
$string['suspendbeforelabel'] = 'Before the exam';
$string['suspendduringlabel'] = 'During the exam';
$string['suspendsubmittedlabel'] = 'After submission';
$string['suspendafterlabel'] = 'After the exam';
$string['suspendgradedlabel'] = 'After grading';
$string['processingtime'] = 'Processing time';

$string['view_card'] = 'Card view';
$string['view_table'] = 'Table view';
$string['viewmode'] = 'View mode';
$string['apply'] = 'Apply';

//table
$string['fullnametable'] = 'Full name';
$string['statustable'] = 'Status';
$string['remainingtimetable'] = 'Remaining time';
$string['actionstable'] = 'Actions';
$string['notestable'] = 'Notes';
$string['actionhistorytable'] = 'Action history';

$string['attemptsuspendedmsg'] = 'You have been suspended from this quiz. Please contact your supervisor.';

$string['currentstatus'] = 'Status';
$string['nostatus'] = 'Normal';

$string['suspendedpagetitle'] = 'Exam suspension notice';
$string['suspendednotice'] = 'You have been suspended from the exam';
$string['reasonlabel'] = 'Reason:';
$string['noreasondetails'] = 'No detailed reason provided.';
$string['nosuspensioninfo'] = 'No suspension information found';
$string['confirmdeletehistory'] = 'Are you sure you want to delete this violation record?';
$string['recorddeleted'] = 'The violation record has been deleted.';
$string['th_exam_monitor:manage'] = 'Allow users to delete or manage exam violation records.';

$string['actionstudent'] = 'Student monitoring';
$string['student'] = 'Student';
$string['classcode'] = 'Class Code';
