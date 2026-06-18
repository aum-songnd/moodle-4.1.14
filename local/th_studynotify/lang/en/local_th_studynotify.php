<?php
$string['pluginname']               = 'TH Study Notify';
$string['notify_course_opened']     = 'Notify students when course opens';
$string['notify_inactive_students'] = 'Notify students when inactive for 2 weeks';
$string['messageprovider:course_opened'] = 'Course opening notification';
$string['messageprovider:inactive_students'] = 'Reminder for users who have been inactive for more than 2 weeks';
$string['messageprovider:quiz_kt_reminder'] = 'Reminder to complete quizzes';
$string['messageprovider:weekly_study'] = 'Weekly study summary notification';
$string['weekly_study'] = 'Weekly study summary notification';
$string['study_reminder_heading'] = 'Study Orientation Reminder';
$string['enable_study_reminder'] = 'Enable study orientation reminder';
$string['enable_study_reminder_desc'] = 'Automatically send reminder emails on configured day offsets since course start.';
$string['study_reminder_template'] = 'Study orientation email template';
$string['study_reminder_template_desc'] = 'Placeholders: {firstname}, {batchdate}, {weekno}, {coursescount}, {mycourseslink}, {feedbacklink}, {feedbackline}, {dayincontent}, {dayincontent_list}';
$string['day_in_content5'] = 'Days shown in email content';
$string['day_in_content5_desc'] = 'Reference days displayed in the email body (e.g., 9,16,23,30,37,44,51).';
$string['study_reminder_days'] = 'Actual reminder send days';
$string['study_reminder_days_desc'] = 'Exact day offsets to send reminders (e.g., 7,14,21,28,35,42,49).';
$string['notify_study_reminder'] = 'Task: Study orientation reminder';
$string['task_notify_quiz_kt_reminder'] = 'Task: Reminder for students to take the quiz';
$string['quizid'] = 'Enter';
$string['quizid_desc'] =  'Enter a JSON array of quiz idnumbers.';

$string['courseopen_template_batch_title'] = 'Email template for COURSE OPENING BATCH (grouped by user, no course names)';
$string['courseopen_template_batch_desc'] = 'Available variables: {firstname}, {batchdate}, {coursescount}, {mycourseslink}, {feedbacklink}';
$string['courseopen_template_batch_default'] = <<<EOD
Chào học viên {firstname} {lastname},

Môn học mới đợt mở môn {batchdate} đã mở đầy đủ trên hệ thống elearning.

Anh/Chị Học viên truy cập vào KHÓA HỌC CỦA TÔI để bắt đầu học tập. QLHT lưu ý Anh/ Chị Học viên đọc kỹ ĐỀ CƯƠNG CHI TIẾT trước khi học tập để đảm bảo hoàn thành đúng yêu cầu của môn học.

Trường hợp môn học mở chưa đúng theo kế hoạch học tập, Anh/chị vui lòng điền link phản hồi hoặc liên hệ QLHT/GVCN lớp để được hỗ trợ.

Link phản hồi: {feedbacklink}.

Chúc Anh/Chị học tập tốt !
EOD;

$string['inactive_template_batch_title'] = 'Reminder email template: INACTIVE >2 WEEKS (grouped by user, no course names)';
$string['inactive_template_batch_desc'] = 'Available variables: {firstname}, {batchdate}, {coursescount}, {mycourseslink}, {feedbacklink}';
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
//$string['courseopen_email_subject'] = 'Course opening on {$a}';
$string['courseopen_email_subject'] = '[{$a}-ELEARNING] THÔNG BÁO MỞ MÔN ĐỢT HỌC {$b}';
//$string['inactive_email_subject'] = 'Reminder: course opening {$a} — you have not logged in for over 2 weeks';
$string['inactive_email_subject'] = '[{$a}-ELEARING] CẢNH BÁO CHƯA ĐĂNG NHẬP 02 TUẦN HỌC ĐỢT HỌC {$b}';

$string['quiz_reminder_template_title'] = 'Email template for quiz/homework completion reminder';
$string['quiz_reminder_template_desc'] = 'Available variables: {firstname}, {lastname}, {quizlabel}, {startdate}, {daysleft}, {deadline}';
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
// $string['quiz_reminder_subject'] = 'Nhắc nhở hoàn thành {$a}';
$string['quiz_reminder_subject7'] = '[{$a}-ELEARNING] THÔNG BÁO HẠN LÀM {$b} NGÀY {$c}. ĐỢT MỞ MÔN {$d}.';
$string['quiz_reminder_subject1'] = '[{$a}-ELEARNING] CẢNH BÁO CHƯA HOÀN THIỆN{$b} NGÀY {$c}. ĐỢT MỞ MÔN {$d}.';

$string['common_template'] = 'Common template for weekly study (T+8 .. T+50)';
$string['common_template_desc'] = 'Placeholders: {firstname}, {batchdate}, {coursescount}, {mycourseslink}, {feedbacklink}, {weekno}';

$string['common_template_default'] = "Chào học viên {firstname} {lastname},\n"
    . "📢 QLHT thông tin tuần học số {weekno} đợt mở môn {batchdate}.\n\n"
    . "🎓 A/C học viên chú ý truy cập vào hệ thống hoàn thành đầy đủ bài luyện tập trắc nghiệm của môn học đúng hạn để đảm bảo điều kiện dự thi điểm chuyển cần.\n"
    . "Lưu ý:\n"
    . " + Đọc đề cương chi tiết từng môn để hoàn thành đúng hạn yêu cầu từng môn học.\n\n"
    . "Chúc A/C học viên hoàn thành bài tập đạt kết quả cao!";

$string['feedback_link'] = 'Feedback link';
$string['feedback_link_desc'] = 'Support contact URL or email address (optional).';
$string['local_studentnotify_weeklyhdr'] = 'Weekly study info templates';
$string['local_studentnotify_weeklyhdr_desc'] = 'Templates used for weekly learning notifications (T+8 .. T+50).';
$string['weekly_study_subject'] = 'Weekly study information notification';
$string['quizidnumber'] = 'Quiz ID Numbers';
$string['quizidnumberdesc'] = 'Enter the list of quiz ID numbers, separated by commas. For example: kt.1, kt.2';
$string['messageprovider:defaults'] = 'Notify users when a course opens';
$string['days_before_quiz'] = 'Quiz reminder days';
$string['days_before_quiz_desc'] = 'Enter the number of days before the quiz deadline to send reminders, separated by commas, e.g., 7,4,1.';
$string['inactive_days'] = 'Inactive days for reminders';
$string['inactive_days_desc'] = 'List of inactive days, separated by commas (e.g., 28,14).';
$string['enable_course_opened'] = 'Enable course open notifications';
$string['enable_course_opened_desc'] = 'If enabled, it will activate the feature to send notifications when a course is opened';
$string['enable_inactive_students'] = 'Enable notifications for inactive students';
$string['enable_inactive_students_desc'] = 'If enabled, it will activate the feature to send notifications for students who have not logged in';
$string['enable_quiz_kt_reminder'] = 'Enable quiz reminder notifications';
$string['enable_quiz_kt_reminder_desc'] = 'If enabled, it will activate the feature to send notifications for upcoming quizzes';

// Form holiday (có thể bạn đã có vài cái rồi)
$string['holiday_start'] = 'Holiday start date';
$string['holiday_end'] = 'Holiday end date';
$string['holiday_date_error'] = 'End date must be greater than or equal to start date.';

// View / manage holiday
$string['holiday_calendar_title'] = 'Holiday calendar';
$string['holiday_calendar_heading'] = 'Holiday calendar';
$string['holiday_date_ranges_heading'] = 'Holiday date ranges';

$string['holiday_add_button'] = 'Add holiday';
$string['holiday_col_from'] = 'From';
$string['holiday_col_to'] = 'To';
$string['holiday_col_description'] = 'Description';
$string['holiday_col_actions'] = 'Actions';

$string['holiday_action_edit'] = 'Edit';
$string['holiday_action_delete'] = 'Delete';

$string['holiday_saved'] = 'Holiday data saved.';
$string['holiday_deleted'] = 'Holiday deleted.';


$string['holiday_batch'] = 'Opening date';
$string['holiday_batch_choose'] = 'Choose an opening date';
$string['holiday_batch_required'] = 'Please choose an opening date.';
$string['holiday_col_batch'] = 'Opening date';

$string['notification_weeks'] = 'Weeks that require notifications.';
$string['notification_weeks_desc'] = 'Enter the week numbers separated by commas, counted from the course start date.';

$string['weekly_email_subject'] = 'Thông báo nhắc nhở học tập - Tuần {$a}';

$string['weekly_template'] = 'Notification content';
$string['weekly_template_desc'] = 'Placeholders: {firstname}, {lastname}, {weekno} (Số tuần), {batchdate} (Ngày khai giảng).';
$string['weekly_template_default'] = "Chào học viên {firstname} {lastname},\n"
    . "📢 QLHT thông tin tuần học số {weekno} đợt mở môn {batchdate}.\n\n"
    . "🎓 A/C học viên chú ý truy cập vào hệ thống hoàn thành đầy đủ bài luyện tập trắc nghiệm của môn học đúng hạn để đảm bảo điều kiện dự thi điểm chuyển cần.\n"
    . "Lưu ý:\n"
    . " + Đọc đề cương chi tiết từng môn để hoàn thành đúng hạn yêu cầu từng môn học.\n\n"
    . "Chúc A/C học viên hoàn thành bài tập đạt kết quả cao!";

$string['enable_weekly_study'] = 'Activate sending lesson notifications weekly';
$string['enable_weekly_study_desc'] = 'If enabled, the feature of sending weekly learning notifications will be enabled';
$string['notify_weekly_study'] = 'Thông báo học tập theo tuần';
$string['holiday_confirm_delete'] = 'Are you sure you want to delete this holiday period?';
$string['holiday_no_records'] = 'There are currently no holiday date ranges.';
$string['testdate'] = 'Test date (YYYY-MM-DD)';
$string['testdate_desc'] = 'Enter date in format 2026-03-09. If empty, current date will be used.';