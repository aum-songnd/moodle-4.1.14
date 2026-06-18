<?php

$string['pluginname'] = 'TH Giám sát thi';
$string['th_exam_monitor:view'] = 'Giám sát học viên làm bài';
$string['selectquiz'] = 'Chọn một bài kiểm tra để giám sát';
$string['confirmaction'] = 'Bạn có chắc chắn muốn thực hiện hành động này?';
$string['actionsaved'] = 'Hành động đã được lưu thành công.';

$string['suspend_notification_message'] = 
    'Xin chào anh/chị học viên <strong>{$a->fullname}</strong>,<br>
    Phòng thi <strong>{$a->coursename}</strong> đã bị đình chỉ do vi phạm quy chế thi.<br>
    <strong>Kết quả bài thi không được công nhận.</strong><br><br>
    Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline, 0943846035).';
$string['suspend_notification_subject'] = 'Thông báo đình chỉ bài thi';
$string['suspend_notification_short'] = 'Bài thi đã bị đình chỉ.';


$string['warn_subject'] = 'Thông báo khiển trách bài thi';
$string['warn_message'] = 'Xin chào anh/chị học viên {$a->fullname},<br>
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

$string['view'] = 'Xem';
$string['studentlist'] = 'Giám sát học viên làm bài';
$string['update'] = 'Cập nhật';
$string['notattempted'] = 'Chưa làm bài';
$string['inprogress'] = 'Đang làm bài';
$string['finished'] = 'Đã nộp bài';
$string['remainingtime'] = 'Thời gian còn lại:';
$string['selectaction'] = 'Chọn thao tác';
$string['warn'] = 'Khiển trách';
$string['alert'] = 'Cảnh cáo';
$string['suspend_exam'] = 'Đình chỉ thi';
$string['enternote'] = 'Nhập ghi chú vi phạm...';
$string['submit'] = 'Gửi';
$string['viewhistory'] = 'Xem lịch sử';

//view-history
$string['viewhistorytitle'] = 'Lịch sử vi phạm';
$string['viewhistoryheading'] = 'Lịch sử vi phạm';
$string['violationhistory'] = 'Lịch sử xử lý vi phạm:';
$string['violationhistoryempty'] = 'Không có lịch sử vi phạm.';
$string['time'] = 'Thời gian';
$string['status'] = 'Trạng thái';
$string['note'] = 'Ghi chú';
$string['unknownstatus'] = 'Không xác định';

$string['notstarted'] = 'Chưa bắt đầu';
$string['statussubmitted'] = 'Đã nộp bài';
$string['statusabandoned'] = 'Bỏ thi giữa chừng';

$string['param_userid'] = 'ID người dùng';
$string['param_courseid'] = 'ID khóa học';
$string['param_quizid'] = 'ID bài kiểm tra';
$string['param_attemptid'] = 'ID lần làm bài';
$string['param_action_status'] = 'Trạng thái hành động (số nguyên)';
$string['param_note'] = 'Ghi chú của giáo viên';

$string['report_exam'] = 'Báo cáo vi phạm thi';

$string['filterbycourse'] = 'Theo phòng thi';
$string['filterbydate'] = 'Theo ngày thi';
$string['filterbyuser'] = 'Theo học viên';
$string['choosefiltertype'] = 'Chọn kiểu lọc:';
$string['choosecourse'] = 'Chọn phòng thi';
$string['examdate'] = 'Ngày thi';
$string['choosestudent'] = 'Chọn học viên';
$string['actionlevel'] = 'Mức độ xử lý';
$string['viewreport'] = 'Xem báo cáo';
$string['violationreportheading'] = 'Báo cáo vi phạm thi';

$string['serialnumber'] = 'STT';
$string['fullname'] = 'Họ và đệm/Tên';
$string['studentcode'] = 'Mã học viên';
$string['coursename'] = 'Tên phòng thi';
$string['shortname'] = 'Mã phòng thi rút gọn';
$string['examname'] = 'Bài thi';
$string['examstatus'] = 'Trạng thái thi';
$string['violationtime'] = 'Thời điểm vi phạm';
$string['violationreason'] = 'Lý do vi phạm';
$string['quizfinished'] = 'Đã nộp bài';
$string['quizinprogress'] = 'Đang làm bài';
$string['undefined'] = 'Không xác định';
$string['nodatafound'] = 'Không tìm thấy dữ liệu.';
$string['all'] = 'Tất cả';

$string['suspend'] = 'Đình chỉ';
$string['suspendbeforelabel'] = 'Chưa làm bài';
$string['suspendduringlabel'] = 'Đang làm bài';
$string['suspendsubmittedlabel'] = 'Đã nộp bài';
$string['suspendafterlabel'] = 'Sau khi kết thúc';
$string['suspendgradedlabel'] = 'Sau khi chấm';
$string['processingtime'] = 'Thời gian xử lý';

$string['view_card'] = 'Dạng thẻ';
$string['view_table'] = 'Dạng bảng';
$string['viewmode'] = 'Chế độ xem';
$string['apply'] = 'Chọn';

//table
$string['fullnametable'] = 'Họ tên';
$string['statustable'] = 'Trạng thái';
$string['remainingtimetable'] = 'Thời gian còn lại';
$string['actionstable'] = 'Thao tác';
$string['notestable'] = 'Ghi chú';
$string['actionhistorytable'] = 'Lịch sử thao tác';

$string['attemptsuspendedmsg'] = 'Bạn đã bị đình chỉ khỏi bài kiểm tra này. Vui lòng liên hệ giám thị.';

$string['currentstatus'] = 'Trạng thái';
$string['nostatus'] = 'Bình thường';

$string['suspendedpagetitle'] = 'Thông báo đình chỉ thi';
$string['suspendednotice'] = 'Bạn đã bị đình chỉ thi';
$string['reasonlabel'] = 'Lý do:';
$string['noreasondetails'] = 'Không có lý do chi tiết.';
$string['nosuspensioninfo'] = 'Không tìm thấy thông tin đình chỉ thi';
$string['confirmdeletehistory'] = 'Bạn có chắc chắn muốn xóa bản ghi vi phạm này?';
$string['recorddeleted'] = 'Đã xóa bản ghi vi phạm.';
$string['th_exam_monitor:manage'] = 'Cho phép người dùng xóa hoặc quản lý các bản ghi vi phạm trong kỳ thi.';

$string['actionstudent'] = 'Giám sát Học viên';
$string['student'] = 'Học viên';
$string['classcode'] = 'Mã lớp';
