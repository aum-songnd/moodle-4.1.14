<?php

defined('MOODLE_INTERNAL') || die();

$string['th_evstep_mailling_reminder:view'] = 'View';

$string['pluginname'] = 'th_evstep_email_reminder';
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

$string['taskname_attemps_need_to_grading'] = 'Send mail attemps need to grading';
$string['taskname_attemps_to_grading'] = 'Send mail attemps to grading';
$string['taskname_ctv_finished_grading'] = 'Send mail ctv finished grading';
$string['taskname_grading_approved'] = 'Send mail grading approved';
$string['taskname_assign_turns'] = 'Send mail assign turns';
$string['taskname_graded_attemp'] = 'Send mail graded attemp';
$string['taskname_user_enrolled'] = 'Send mail user enrolled';

$string['defaultneedtogradingsetting'] = 'Hour send mail attemps need to grading';
$string['defaultneedtogradingsettingdescription'] = 'Send mail attemps need to grading';

$string['defaultattempstogradingsetting'] = 'Hour Send mail attemps to grading';
$string['defaultattempstogradingsettingdescription'] = 'Send mail attemps to grading';

$string['defaultctvfinishedgradingsetting'] = 'Hour Send mail ctv finished grading';
$string['defaultctvfinishedgradingsettingdescription'] = 'Send mail ctv finished grading';

$string['defaultgradingapprovedsetting'] = 'Hour Send mail grading approved';
$string['defaultgradingapprovedsettingdescription'] = 'Send mail grading approved';

$string['defaultassignturnssetting'] = 'Hour Send mail assign turns';
$string['defaultassignturnssettingdescription'] = 'Send mail assign turns';

$string['defaultuserenrolledssetting'] = 'Hour Send mail user enrolled';
$string['defaultuserenrolledssettingdescription'] = 'Send mail user enrolled';

$string['defaultqtvssetting'] = 'collaborator manager shortname';
$string['defaultqtvssettingdescription'] = 'collaborator manager shortname';