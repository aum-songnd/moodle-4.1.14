<?php
$string['pluginname'] = 'Thông báo chấm bài kiểm tra đầu vào';
$string['th_submission_notify:select_course'] = 'Chọn các khóa học cần kiểm tra bài nộp';
$string['enablenotifications'] = 'Bật gửi thông báo';
$string['enablenotifications_desc'] = 'Bật hoặc tắt chức năng gửi thông báo chấm bài.';
$string['emaillist'] = 'Danh sách email nhận thông báo';
$string['emaillist_desc'] = 'Danh sách email ngăn cách bằng dấu phẩy để nhận thông báo.';
$string['hour_send_setting'] = 'Khung giờ gửi';
$string['hour_send_setting_desc'] = 'Chọn giờ cố định để gửi email thông báo.';
$string['task_send_notifications'] = 'Gửi thông báo cho các bài kiểm tra chưa được chấm';
$string['averagescore_shortname_setting'] = 'Shortname của điểm kiểm tra cuối khóa';
$string['averagescore_shortname_setting_description'] = 'Shortname của điểm kiểm tra cuối khóa';

$string['textfields'] = 'Chọn các khóa học cần gửi thông báo';
$string['chooseacourse'] = 'Chọn khóa học';
$string['savechanges'] = 'Lưu thay đổi';


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
