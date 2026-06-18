<?php

defined('MOODLE_INTERNAL') || die();

$string['th_evstep_mailling_reminder:view'] = 'View';

$string['pluginname'] = 'Gửi email nhắc việc';
// 1
$string['subject_email_attemps_need_to_grading'] = 'Thông báo bài tập học viên cần chấm';
$string['message_email_attemps_need_to_grading'] = 'Báo cáo số lượng học viên yêu cầu chấm chữa ngày:  {$a->date}
1. Số lượng yêu cầu mới: {$a->newcreate} yêu cầu
2. Số lượng yêu cầu còn tồn đọng: {$a->backlog} yêu cầu
{$a->url}

Xin cảm ơn.';
// 2
$string['subject_email_attemps_to_grading'] = 'Thông báo bài tập cần chấm';
$string['message_email_attemps_to_grading'] = 'Xin chào anh/chị!

Anh/chị được phân công bài chấm, vui lòng kiểm tra mục "Danh sách các Bài kiểm tra Cần chấm" và hoàn thành chấm điểm trong vòng 48h.
{$a->url}

Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline 0948.293.794 ).

Xin cảm ơn.';
// 3
$string['subject_email_ctv_finished_grading'] = 'Thông báo CTV hoàn thành chấm bài';
$string['message_email_ctv_finished_grading'] = 'Xin chào anh/chị!


Bài chấm đã được hoàn thành, vui lòng kiểm tra mục "Quản lý Phân chia Công việc cho CTV" và hoàn thành phê duyệt bài chấm.
{$a->url}

Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline 0948.293.794 ).

Xin cảm ơn.';
// 4
$string['subject_email_grading_approved'] = 'Thông báo phê duyệt bài chấm';
$string['message_email_grading_approved'] = 'Xin chào anh/chị!

Bài chấm của anh/chị đã được phê duyệt.
{$a->url}

Xin cảm ơn.';
// 5
$string['subject_email_assign_turns'] = 'Thông tin gói gia sư của học viên';
$string['message_email_assign_turns'] = 'Xin chào {$a->fullname}!

Bạn có {$a->turn} lần chữa bài nói hoặc viết. 

Chúc bạn học tập và đạt kết quả tốt nhất!';
// 6
$string['subject_email_graded_attemp'] = 'Thông báo bài tập đã chấm';
$string['message_email_graded_attemp'] = 'Xin chào {$a->fullname}!

Bài kiểm tra của bạn đã có kết quả.
{$a->url}

Chúc bạn học tập và đạt kết quả tốt nhất!.';
// 7
$string['subject_email_user_enrolled'] = 'Thông báo ghi danh';
$string['message_email_user_enrolled'] = 'Xin chào {$a->fullname}!

1. Bạn đã được ghi danh vào khoá học {$a->coursename}
Hãy đăng nhập và truy cập khoá học tại {$a->url}
Nếu bạn chưa nhận được email thông báo tài khoản, vui lòng kiểm tra trong thư mục Quảng cáo/Spam, hoặc liên hệ hotline kỹ thuật (hotline 0948.293.794) để được hỗ trợ. 

Xin cảm ơn.';

$string['taskname_attemps_need_to_grading'] = 'Gửi mail thông báo bài tập học viên cần chấm';
$string['taskname_attemps_to_grading'] = 'Gửi mail thông báo bài tập cần chấm';
$string['taskname_ctv_finished_grading'] = 'Gửi mail thông báo CTV hoàn thành chấm bài';
$string['taskname_grading_approved'] = 'Gửi mail thông báo phê duyệt bài chấm';
$string['taskname_assign_turns'] = 'Gửi mail thông tin gói gia sư của học viên';
// $string['taskname_graded_attemp'] = 'Gửi mail thông báo bài tập đã chấm';
$string['taskname_user_enrolled'] = 'Gửi mail thông báo ghi danh';

$string['defaultneedtogradingsetting'] = 'Giờ gửi thông báo bài tập học viên cần chấm';
$string['defaultneedtogradingsettingdescription'] = 'Thông báo bài tập học viên cần chấm';

$string['defaultattempstogradingsetting'] = 'Giờ gửi thông báo bài tập cần chấm';
$string['defaultattempstogradingsettingdescription'] = 'Thông báo bài tập cần chấm';

$string['defaultctvfinishedgradingsetting'] = 'Giờ gửi thông báo CTV hoàn thành chấm bài';
$string['defaultctvfinishedgradingsettingdescription'] = 'Thông báo CTV hoàn thành chấm bài';

$string['defaultgradingapprovedsetting'] = 'Giờ gửi thông báo phê duyệt bài chấm';
$string['defaultgradingapprovedsettingdescription'] = 'Thông báo phê duyệt bài chấm';

$string['defaultassignturnssetting'] = 'Giờ gửi thông tin gói gia sư của học viên';
$string['defaultassignturnssettingdescription'] = 'Thông tin gói gia sư của học viên';

$string['defaultuserenrolledssetting'] = 'Giờ gửi thông báo ghi danh';
$string['defaultuserenrolledssettingdescription'] = 'Thông báo ghi danh';

$string['defaultqtvssetting'] = 'Mã QTV chấm chữa';
$string['defaultqtvssettingdescription'] = 'Mã QTV chấm chữa';


