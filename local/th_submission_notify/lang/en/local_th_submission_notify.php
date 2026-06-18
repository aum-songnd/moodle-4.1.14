<?php
$string['pluginname'] = 'Input Test Submission Alerts';
$string['th_submission_notify:select_course'] = 'Select courses to look up for quiz attempts';
$string['enablenotifications'] = 'Enable notification';
$string['enablenotifications_desc'] = 'Enable notification';
$string['emaillist'] = 'Notification email list';
$string['emaillist_desc'] = 'Comma-separated list of emails to notify.';
$string['hour_send_setting'] = 'Send time';
$string['hour_send_setting_desc'] = 'Time to send mail.';
$string['task_send_notifications'] = 'Send notifications for ungraded quiz submissions';
$string['averagescore_shortname_setting'] = 'Final grade shortname setting';
$string['averagescore_shortname_setting_description'] = 'Final grade shortname setting';

$string['textfields'] = 'Select courses for notification';
$string['chooseacourse'] = 'Choose courses';
$string['savechanges'] = 'Save changes';

$string['subject'] = 'Thông báo chấm bài kiểm tra đầu vào';
$string['message'] = 'Chào anh/chị
Khóa học anh/chị đang phụ trách có Bài kiểm tra tự luận, Anh/chị vui lòng truy cập khóa học để kiểm tra và hoàn thiện chấm bài.
Báo cáo số lượng bài cần chấm chữa ngày: {$a->date}

1. Số lượng yêu cầu mới: {$a->newcreate} yêu cầu
2. Số lượng yêu cầu còn tồn đọng: {$a->backlog} yêu cầu

Xin cảm ơn';

$string['subject_student'] = 'Thông báo kết quả kiểm tra đầu vào';
$string['message_student'] = 'Kính gửi Anh/Chị {$a->studentname},

Trung tâm Ngoại ngữ OM\'C trân trọng gửi đến Anh/Chị kết quả bài kiểm tra đầu vào như sau:
1. Kết quả từng kỹ năng:
{$a->scores}
2. Điểm trung bình: {$a->average_score}.
3. Trình độ tương ứng: {$a->cefr_score}.

Chúc mừng Anh/Chị đã hoàn thành bài kiểm tra đánh giá năng lực đầu vào. Nếu có bất kỳ câu hỏi hoặc thắc mắc nào liên quan đến kết quả, vui lòng liên hệ với chúng tôi qua hotline 085.224.8855 để được hỗ trợ kịp thời.';

