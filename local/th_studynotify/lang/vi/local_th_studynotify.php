<?php
$string['pluginname']               = 'TH Thông báo học tập';
$string['notify_course_opened']     = 'Nhắc mở môn học';
$string['messageprovider:course_opened'] = 'Thông báo mở khóa học';
$string['messageprovider:inactive_students'] = 'Thông báo nhắc không đăng nhập > 2 tuần';
$string['messageprovider:quiz_kt_reminder'] = 'Thông báo nhắc làm bài kiểm tra';
$string['messageprovider:weekly_study'] = 'Thông báo nhắc học tập theo tuần';
$string['weekly_study'] = 'Thông báo nhắc học tập theo tuần';
$string['notify_inactive_students'] = 'Nhắc SV không đăng nhập > 2 tuần';
$string['study_reminder_heading'] = 'Nhắc nhở định hướng học tập';
$string['enable_study_reminder'] = 'Nhắc nhở định hướng học tập';
$string['enable_study_reminder_desc'] = 'Bật/tắt tự động gửi email nhắc nhở định hướng học tập theo các mốc ngày đã cấu hình.';
$string['study_reminder_template'] = 'Mẫu nhắc nhở định hướng học tập';
$string['study_reminder_template_desc'] = 'Biến dùng: {firstname}, {batchdate}, {weekno}, {coursescount}, {mycourseslink}, {feedbacklink}, {feedbackline}, {dayincontent}, {dayincontent_list}';
$string['day_in_content5'] = 'Ngày trong nội dung thư';
$string['day_in_content5_desc'] = 'Các mốc ngày hiển thị trong nội dung thư (VD: 9,16,23,30,37,44,51).';
$string['study_reminder_days'] = 'Ngày nhắc nhở định hướng học tập';
$string['study_reminder_days_desc'] = 'Các mốc ngày thực tế gửi thư (VD: 7,14,21,28,35,42,49).';
$string['notify_study_reminder'] = 'Task: Nhắc nhở định hướng học tập';
$string['task_notify_quiz_kt_reminder'] = 'Task: Nhắc nhở học viên làm bài kiểm tra';
$string['quizid'] = 'Nhập';
$string['quizid_desc'] = 'Nhập chuỗi JSON chứa danh sách idnumber của các bài kiểm tra.';

$string['courseopen_template_batch_title'] = 'Mẫu email ĐỢT MỞ MÔN (gộp theo user, không nêu tên môn)';
$string['courseopen_template_batch_desc'] = 'Biến dùng: {firstname}, {batchdate}, {coursescount}, {mycourseslink}, {feedbacklink}';
$string['courseopen_template_batch_default'] = <<<EOD
Chào học viên {firstname} {lastname},

Môn học mới đợt mở môn {batchdate} đã mở đầy đủ trên hệ thống elearning.

Anh/Chị Học viên truy cập vào KHÓA HỌC CỦA TÔI để bắt đầu học tập. QLHT lưu ý Anh/ Chị Học viên đọc kỹ ĐỀ CƯƠNG CHI TIẾT trước khi học tập để đảm bảo hoàn thành đúng yêu cầu của môn học.

Trường hợp môn học mở chưa đúng theo kế hoạch học tập, Anh/chị vui lòng điền link phản hồi hoặc liên hệ QLHT/GVCN lớp để được hỗ trợ.

Link phản hồi: {feedbacklink}.

Chúc Anh/Chị học tập tốt !
EOD;

$string['inactive_template_batch_title'] = 'Mẫu email NHẮC NHỞ >2 TUẦN (gộp theo user, không nêu tên môn)';
$string['inactive_template_batch_desc'] = 'Biến dùng: {firstname}, {batchdate}, {coursescount}, {mycourseslink}, {feedbacklink}';
$string['inactive_template_batch_default'] = <<<EOD
Chào học viên {firstname} {lastname},

QLHT thông báo môn học đợt mở môn {batchdate} đã mở trên hệ thống.

Thời gian học tập bắt đầu từ ngày {batchdate}.

Tuy nhiên, hệ thống đang ghi nhận Anh/Chị đã không đăng nhập học tập trong hơn 2 tuần.

Anh/Chị vui lòng truy cập hệ thống eLearning để tiếp tục học các môn học.

Lưu ý:
    + Đọc kỹ đề cương chi tiết của từng môn để đảm bảo hoàn thành đúng hạn và đúng yêu cầu.

Chúc Anh/Chị học tập hiệu quả và đạt kết quả cao!
EOD;
//$string['courseopen_email_subject'] = 'Đợt mở môn ngày {$a}';
$string['courseopen_email_subject'] = '[{$a}-ELEARNING] THÔNG BÁO MỞ MÔN ĐỢT HỌC {$b}';
//$string['inactive_email_subject'] = 'Nhắc nhở: đợt mở môn {$a} — bạn đã >2 tuần chưa đăng nhập';
$string['inactive_email_subject'] = '[{$a}-ELEARNING] CẢNH BÁO CHƯA ĐĂNG NHẬP 02 TUẦN HỌC ĐỢT HỌC {$b}';

$string['quiz_reminder_template_title'] = 'Mẫu email nhắc hoàn thành bài tập/trắc nghiệm (quiz)';
$string['quiz_reminder_template_desc'] = 'Biến dùng: {firstname}, {lastname}, {quizlabel}, {startdate}, {daysleft}, {deadline}';
$string['quiz_reminder_template_default'] = <<<EOD
Chào học viên {firstname} {lastname},

📢  THÔNG TIN HOÀN THIỆN {quizlabel} CÁC MÔN HỌC ĐỢT MỞ MÔN {startdate}

{dayslefttext}

Hạn kết thúc: {deadline}

🎓 Anh/Chị học viên chú ý truy cập vào hệ thống để hoàn thành đầy đủ bài tập của môn học đúng hạn để đảm bảo điều kiện dự thi.

Lưu ý:
KHÔNG LÀM {quizlabel} => KHÔNG đủ điều kiện thi => học lại  
{quizlabel} ĐƯỢC LÀM 03 LẦN

Chúc Anh/Chị học tập hiệu quả và đạt kết quả cao!
EOD;
$string['quiz_due_tomorrow'] = 'Ngày mai kết thúc hạn làm {$a}';
$string['quiz_due_in_days'] = 'Còn {$a->daysleft} ngày nữa kết thúc hạn làm {$a->quizlabel}';
//$string['quiz_reminder_subject'] = 'Nhắc nhở hoàn thành {$a}';
$string['quiz_reminder_subject7'] = '[{$a}-ELEARNING] THÔNG BÁO HẠN LÀM {$b} NGÀY {$c}. ĐỢT MỞ MÔN {$d}.';
$string['quiz_reminder_subject1'] = '[{$a}-ELEARNING] CẢNH BÁO CHƯA HOÀN THIỆN {$b} NGÀY {$c}. ĐỢT MỞ MÔN {$d}.';

$string['common_template'] = 'Template chung cho các tuần học (T+8 .. T+50)';
$string['common_template_desc'] = 'Các ký hiệu thay thế: {firstname}, {batchdate}, {coursescount}, {mycourseslink}, {feedbacklink}, {weekno}';

$string['common_template_default'] = "Chào học viên {firstname} {lastname},\n"
    . "📢 QLHT thông tin tuần học số {weekno} đợt mở môn {batchdate}.\n\n"
    . "🎓 A/C học viên chú ý truy cập vào hệ thống hoàn thành đầy đủ bài luyện tập trắc nghiệm của môn học đúng hạn để đảm bảo điều kiện dự thi điểm chuyển cần.\n"
    . "Lưu ý:\n"
    . " + Đọc đề cương chi tiết từng môn để hoàn thành đúng hạn yêu cầu từng môn học.\n\n"
    . "Chúc A/C học viên hoàn thành bài tập đạt kết quả cao!";

$string['feedback_link'] = 'Đường dẫn phản hồi';
$string['feedback_link_desc'] = 'URL hoặc email liên hệ hỗ trợ (không bắt buộc).';
$string['local_studentnotify_weeklyhdr'] = 'Mẫu thông báo hàng tuần';
$string['local_studentnotify_weeklyhdr_desc'] = 'Các mẫu dùng để gửi thông báo hàng tuần cho học viên theo tiến độ mở môn (T+8 .. T+50).';
$string['weekly_study_subject'] = 'Thông báo thông tin học tập theo tuần';
$string['quizidnumber'] = 'Mã bài kiểm tra';
$string['quizidnumberdesc'] = 'Nhập danh sách mã của các bài kiểm tra, cách nhau bằng dấu phẩy. Ví dụ: kt.1, kt.2';
$string['messageprovider:defaults'] = 'Thông báo cho người dùng khi khóa học mở';
$string['days_before_quiz'] = 'Hạn ngày nhắc nhở bài kiểm tra';
$string['days_before_quiz_desc'] = 'Nhập số ngày trước hạn nộp bài kiểm tra để gửi nhắc nhở, cách nhau bằng dấu phẩy, ví dụ: 7,4,1.';
$string['inactive_days'] = 'Số ngày chưa đăng nhập để nhắc';
$string['inactive_days_desc'] = 'Danh sách ngày chưa đăng nhập, cách nhau bằng dấu phẩy, ví dụ 28,14';
$string['enable_course_opened'] = 'Bật gửi thông báo mở môn học';
$string['enable_course_opened_desc'] = 'Nếu kích hoạt, sẽ bật tính năng gửi thông báo môn học đã mở';
$string['enable_inactive_students'] = 'Bật gửi thông báo học viên chưa đăng nhập';
$string['enable_inactive_students_desc'] = 'Nếu kích hoạt, sẽ bật tính năng gửi thông báo học viên chưa đăng nhập';
$string['enable_quiz_kt_reminder'] = 'Bật gửi thông báo làm bài kiểm tra';
$string['enable_quiz_kt_reminder_desc'] = 'Nếu kích hoạt, sẽ bật tính năng gửi thông báo làm bài kiểm tra';

// Form holiday
$string['holiday_start'] = 'Ngày bắt đầu nghỉ';
$string['holiday_end'] = 'Ngày kết thúc nghỉ';
$string['holiday_date_error'] = 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.';

// View / manage holiday
$string['holiday_calendar_title'] = 'Lịch nghỉ lễ';
$string['holiday_calendar_heading'] = 'Lịch nghỉ lễ';
$string['holiday_date_ranges_heading'] = 'Các khoảng thời gian nghỉ lễ';

$string['holiday_add_button'] = 'Thêm ngày nghỉ lễ';
$string['holiday_col_from'] = 'Từ ngày';
$string['holiday_col_to'] = 'Đến ngày';
$string['holiday_col_description'] = 'Mô tả';
$string['holiday_col_actions'] = 'Thao tác';

$string['holiday_action_edit'] = 'Sửa';
$string['holiday_action_delete'] = 'Xóa';

$string['holiday_saved'] = 'Đã lưu lịch nghỉ lễ.';
$string['holiday_deleted'] = 'Đã xóa lịch nghỉ lễ.';


$string['holiday_batch'] = 'Đợt mở môn';
$string['holiday_batch_choose'] = 'Chọn đợt mở';
$string['holiday_batch_required'] = 'Vui lòng chọn đợt mở môn.';
$string['holiday_col_batch'] = 'Đợt mở môn';

$string['notification_weeks'] = 'Các tuần cần gửi thông báo';
$string['notification_weeks_desc'] = 'Nhập các số tuần cách nhau bởi dấu phẩy. Tính từ ngày bắt đầu khóa học.';

$string['weekly_template'] = 'Nội dung thông báo';
$string['weekly_template_desc'] = 'Các ký hiệu thay thế: {firstname}, {lastname}, {weekno} (Số tuần), {batchdate} (Ngày khai giảng).';
$string['weekly_template_default'] = "Chào học viên {firstname} {lastname},\n"
    . "📢 QLHT thông tin tuần học số {weekno} đợt mở môn {batchdate}.\n\n"
    . "🎓 A/C học viên chú ý truy cập vào hệ thống hoàn thành đầy đủ bài luyện tập trắc nghiệm của môn học đúng hạn để đảm bảo điều kiện dự thi điểm chuyển cần.\n"
    . "Lưu ý:\n"
    . " + Đọc đề cương chi tiết từng môn để hoàn thành đúng hạn yêu cầu từng môn học.\n\n"
    . "Chúc A/C học viên hoàn thành bài tập đạt kết quả cao!";

$string['weekly_email_subject'] = 'Thông báo nhắc nhở học tập - Tuần {$a}';

$string['enable_weekly_study'] = 'Bật gửi thông báo học tập theo tuần';
$string['enable_weekly_study_desc'] = 'Nếu kích hoạt, sẽ bật tính năng gửi thông báo học tập theo tuần';
$string['notify_weekly_study'] = 'Thông báo học tập theo tuần';
$string['holiday_confirm_delete'] = 'Bạn có chắc chắn muốn xóa đợt nghỉ lễ này không?';
$string['holiday_no_records'] = 'Hiện chưa có đợt nghỉ nào.';
$string['testdate'] = 'Ngày test (YYYY-MM-DD)';
$string['testdate_desc'] = 'Nhập ngày dạng 2026-03-09. Nếu để trống sẽ dùng ngày hiện tại.';