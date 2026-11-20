<?php
$string['pluginname'] = 'TH Quản lý Hỏi/Đáp';
$string['th_qaa_manager:manage'] = 'Quản lý Hỏi/Đáp';
$string['th_qaa_manager:approvequestion'] = 'Duyệt câu hỏi';
$string['th_qaa_manager:assign'] = 'Phân công câu hỏi cho CTV';
$string['th_qaa_manager:answer'] = 'CTV trả lời câu hỏi được phân công';
$string['th_qaa_manager:approveanswer'] = 'Duyệt câu trả lời của CTV';
$string['qaa_manager'] = 'Quản lý Hỏi/Đáp';
$string['manage'] = 'Quản lý Hỏi/Đáp';
$string['listquestion'] = 'Danh sách câu hỏi';
$string['questiondetail'] = 'Chi tiết câu hỏi';

$string['course'] = 'Khóa học';
$string['student'] = 'Học viên';
$string['assignee'] = 'CTV';
$string['status'] = 'Trạng thái';
$string['question'] = 'Câu hỏi';
$string['answer'] = 'Trả lời';
$string['actions'] = 'Thao tác';
$string['timecreated'] = 'Ngày tạo';

$string['approvequestion'] = 'Duyệt câu hỏi';
$string['approve'] = 'Duyệt';
$string['rejectquestion'] = 'Từ chối';
$string['assign'] = 'Phân công';
$string['submitanswer'] = 'Gửi câu trả lời';
$string['approveanswer'] = 'Duyệt trả lời';
$string['rejectanswer'] = 'Từ chối trả lời';

$string['status_new'] = 'Chưa phân công';
$string['status_approved'] = 'Phân công CTV';
$string['status_assigned'] = 'Đã phân công - chưa trả lời';
$string['status_answered'] = 'Chờ duyệt trả lời';
$string['status_closed'] = 'Hoàn thành';
$string['status_rejected'] = 'Đã từ chối';

$string['nothingtodisplay'] = 'Không có dữ liệu.';
$string['invalidticket'] = 'Ticket không hợp lệ.';
$string['notassignedtoyou'] = 'Câu hỏi này không được giao cho bạn.';
$string['selectall'] = 'Chọn tất cả';
$string['bulkapprovequestion'] = 'Duyệt câu hỏi đã chọn';
$string['bulkassign'] = 'Phân công câu hỏi đã chọn';
$string['assigneeuserid'] = 'User ID CTV';
$string['noselectedtickets'] = 'Chưa chọn câu hỏi nào.';
$string['invalidassignee'] = 'CTV không hợp lệ.';
$string['bulkapprovedone'] = 'Đã duyệt {$a} câu hỏi.';
$string['bulkassigndone'] = 'Đã phân công {$a} câu hỏi.';
$string['confirmbulkapprove'] = 'Bạn có chắc muốn duyệt các câu hỏi đã chọn?';
$string['confirmbulkassign'] = 'Bạn có chắc muốn phân công các câu hỏi đã chọn?';
$string['selectassignee'] = 'Chọn CTV';
$string['noassignees'] = 'Chưa có CTV nào có quyền trả lời.';
$string['confirmapprovequestion'] = 'Bạn có chắc muốn duyệt câu hỏi này?';
$string['confirmrejectquestion'] = 'Bạn có chắc muốn từ chối câu hỏi này?';
$string['confirmassignquestion'] = 'Bạn có chắc muốn phân công câu hỏi này cho CTV đã chọn?';
$string['confirmapproveanswer'] = 'Bạn có chắc muốn duyệt câu trả lời này?';
$string['confirmrejectanswer'] = 'Bạn có chắc muốn từ chối câu trả lời này?';

$string['task_weekly_question_notify'] = 'Gửi thông báo câu hỏi mới hàng tuần';

$string['email_weekly_new_questions_subject'] = 'Thông báo câu hỏi mới cần xử lý';
$string['email_weekly_new_questions_intro'] = 'Anh/chị vui lòng truy cập Báo cáo hỏi đáp để duyệt các câu hỏi từ học viên.';

$string['email_assignment_subject'] = 'Bạn được phân công xử lý câu hỏi';
$string['email_assignment_body'] = 'Xin chào anh/chị!

Anh/chị được phân công trả lời câu hỏi, vui lòng kiểm tra mục Danh sách các Câu hỏi và hoàn thành yêu cầu.
Đường dẫn: {$a->url}

Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline 0948.293.794 ).
Xin cảm ơn.';

$string['email_answer_approved_subject'] = 'Thông báo câu trả lời';

$string['email_answer_approved_student_body'] = 'Xin chào {$a->student}!

Câu hỏi của bạn đã được phản hồi.
Trích dẫn câu trả lời:
{$a->answer}

Chúc bạn học tập và đạt kết quả tốt nhất!
Chi tiết xem tại: {$a->studenturl}';

$string['email_answer_approved_ctv_body'] = 'Chào {$a->ctv},

Câu trả lời của bạn đã được Quản trị Cộng tác viên phê duyệt.

Khóa học: {$a->course}
Học viên: {$a->student}
Câu hỏi: {$a->question}

Nội dung phản hồi:
{$a->answer}

Bạn có thể xem lại tại:
{$a->ctvurl}';

$string['email_daily_answered_subject'] = 'Thông báo: CTV đã trả lời câu hỏi';
$string['email_daily_answered_intro'] = 'Xin chào anh/chị!
CTV đã hoàn thành trả lời câu hỏi, vui lòng kiểm tra và hoàn thành phê duyệt câu trả lời:';
$string['email_daily_answered_outro'] = 'Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline 0948.293.794 ).
Xin cảm ơn.';

$string['email_recalled_subject'] = 'Thông báo: Câu hỏi đã bị thu hồi';
$string['email_recalled_body'] = 'Xin chào anh/chị!
Câu hỏi [{$a->question}] đã được thu hồi do chưa có phản hồi sau {$a->days} ngày. Câu hỏi sẽ được phân công lại cho CTV khác.
Nếu cần hỗ trợ vui lòng liên hệ hotline 0948.293.794. Xin cảm ơn.';

$string['task_daily_answer_notify'] = 'Gửi thông báo tổng hợp câu trả lời hàng ngày cho Quản trị Cộng tác viên';
$string['rejectreason'] = 'Lý do từ chối';
$string['rejectreasonrequired'] = 'Vui lòng nhập lý do từ chối.';
$string['questionrejectdone'] = 'Đã từ chối câu hỏi.';
$string['error_answer_too_short'] = 'Câu trả lời phải có độ dài ít nhất 20 ký tự (không tính hình ảnh/định dạng).';
$string['cancelrejectq'] = 'Hủy từ chối';
$string['confirmcancelrejectq'] = 'Bạn có chắc chắn muốn hủy từ chối câu hỏi này và đưa về trạng thái chờ duyệt?';
$string['cancelreject_success'] = 'Đã hủy từ chối câu hỏi thành công.';
$string['assigneerole'] = 'Vai trò người được phân công trả lời';
$string['assigneerole_desc'] = 'Chọn vai trò hệ thống của người dùng sẽ được hiển thị trong danh sách phân công trả lời câu hỏi.';
$string['manageremails'] = 'Email nhận thông báo của Quản trị Cộng tác viên';
$string['manageremails_desc'] = 'Danh sách địa chỉ email nhận thông báo của Quản trị Cộng tác viên, phân cách bởi dấu phẩy.';
$string['autorecalldays'] = 'Số ngày thu hồi phân công';
$string['autorecalldays_desc'] = 'Thời gian (tính theo ngày) để hệ thống tự động thu hồi câu hỏi nếu Cộng tác viên không trả lời.';
$string['setting_heading_general'] = '1. Cấu hình chung';
$string['setting_heading_general_desc'] = 'Quản lý vai trò CTV, danh sách email Quản trị Cộng tác viên và thiết lập tự động thu hồi câu hỏi.';
$string['setting_heading_weekly_notify'] = '2. Thông báo: Câu hỏi cần phân công / duyệt';
$string['setting_heading_weekly_notify_desc'] = 'Cấu hình tiêu đề và mẫu nội dung email gửi đến Quản trị Cộng tác viên khi có câu hỏi mới cần phân công.';
$string['setting_heading_daily_notify'] = '3. Thông báo: CTV đã trả lời câu hỏi';
$string['setting_heading_daily_notify_desc'] = 'Cấu hình tiêu đề và mẫu nội dung email gửi đến Quản trị Cộng tác viên khi có câu trả lời của CTV chờ phê duyệt.';
$string['email_weekly_subject'] = 'Tiêu đề thư thông báo câu hỏi cần xử lý';
$string['email_weekly_subject_desc'] = 'Tiêu đề email gửi thông báo đến Quản trị Cộng tác viên khi có câu hỏi cần phân công/duyệt.';
$string['email_weekly_body'] = 'Nội dung thư thông báo câu hỏi cần xử lý';
$string['email_weekly_body_desc'] = 'Nội dung email thông báo. Hỗ trợ các biến thay thế: {$a->url} (Đường dẫn trang phân công), {$a->count} (Số lượng câu hỏi).';
$string['email_daily_subject'] = 'Tiêu đề thư thông báo CTV đã trả lời câu hỏi';
$string['email_daily_subject_desc'] = 'Tiêu đề email gửi thông báo đến Quản trị Cộng tác viên khi có câu trả lời của CTV cần duyệt.';
$string['email_daily_body'] = 'Nội dung thư thông báo CTV đã trả lời câu hỏi';
$string['email_daily_body_desc'] = 'Nội dung email thông báo. Hỗ trợ các biến thay thế: {$a->url} (Đường dẫn trang duyệt câu trả lời), {$a->count} (Số lượng câu hỏi).';
$string['questiontodo'] = 'Câu hỏi cần xử lý';
$string['questioncompleted'] = 'Câu hỏi đã trả lời';
$string['status_question_rejected'] = 'Đã từ chối câu hỏi';
$string['status_answer_rejected'] = 'Đã từ chối câu trả lời';
$string['status_recalled'] = 'Đã thu hồi';
$string['answerrejectreason'] = 'Lý do từ chối câu trả lời: {$a}';
$string['resubmitanswer'] = 'Chỉnh sửa và gửi lại câu trả lời';
$string['answercolumn'] = 'Câu trả lời';
$string['answercontainimage'] = '[Câu trả lời có hình ảnh]';
$string['viewdetail'] = 'Xem đầy đủ';

$string['tab_qaa_manage'] = 'Quản lý Hỏi/Đáp';
$string['filter_ctv'] = 'Chọn CTV';
$string['filter_course'] = 'Chọn khóa học';
$string['filter_time_range'] = 'Chọn khoảng thời gian CTV trả lời câu hỏi';
$string['filter_startdate'] = 'Từ ngày';
$string['filter_enddate'] = 'Đến ngày';
$string['error_startdate_after_enddate'] = 'Từ ngày không được lớn hơn Đến ngày.';
$string['filter_status'] = 'Trạng thái';
$string['filter_status_all'] = 'Tất cả';
$string['filter_status_completed'] = 'Hoàn thành';
$string['filter_status_incomplete'] = 'Chưa hoàn thành';
$string['filter_button'] = 'Lọc';
$string['filter_reset'] = 'Xóa bộ lọc';
$string['col_no'] = 'STT';
$string['col_ctv'] = 'Tên CTV';
$string['col_course_name'] = 'Tên khóa học';
$string['col_course_code'] = 'Mã khóa học';
$string['col_question_link'] = 'Link câu hỏi';
$string['col_student'] = 'Học viên';
$string['col_student_username'] = 'Mã AUM';
$string['col_date_question'] = 'Ngày gửi câu hỏi';
$string['col_date_assign'] = 'Thời gian phân công';
$string['col_date_answer'] = 'Ngày trả lời';
$string['col_approval'] = 'Phê duyệt';
$string['col_approver'] = 'Người phê duyệt';
$string['col_delete_assignment'] = 'Xóa phân công';
$string['delete_assignment_confirm'] = 'Bạn có chắc chắn muốn xóa phân công cho câu hỏi này?';
$string['delete_assignment_success'] = 'Đã xóa phân công thành công.';
$string['bulk_delete_assignment_confirm'] = 'Bạn có chắc chắn muốn xóa phân công cho các câu hỏi đã chọn?';
$string['bulk_delete_assignment_success'] = 'Đã xóa phân công thành công cho {$a} câu hỏi.';
$string['status_approved_short'] = 'Đã duyệt';
$string['status_unapproved_short'] = 'Chưa duyệt';
$string['view'] = 'Xem';

$string['tab_qaa_stats'] = 'Thống kê Hỏi/Đáp';
$string['filter_time_range_stats'] = 'Chọn khoảng thời gian thống kê';
$string['stats_total_new'] = 'Tổng số câu hỏi';
$string['stats_total_new_desc'] = 'Tất cả câu hỏi phát sinh trong kỳ';
$string['stats_pending_assign'] = 'Chờ phân công';
$string['stats_pending_assign_desc'] = 'Chưa giao CTV nào';
$string['stats_pending_approve'] = 'Chờ phê duyệt';
$string['stats_pending_approve_desc'] = 'CTV đã trả lời, chưa duyệt';
$string['stats_completed'] = 'Đã hoàn thành';
$string['stats_completed_desc'] = 'Đã phê duyệt và gửi HV';
$string['stats_auto_recalled'] = 'Bị thu hồi tự động';
$string['stats_auto_recalled_desc'] = 'Quá {$a} ngày, chưa CTV trả lời';

$string['col_assigned_count'] = 'Câu được giao';
$string['col_answered_count'] = 'Đã trả lời';
$string['col_pending_count'] = 'Chờ duyệt';
$string['col_completed_count'] = 'Hoàn thành';
$string['col_recalled_count'] = 'Bị thu hồi';

$string['task_auto_recall_assignments'] = 'Tự động thu hồi phân công câu hỏi quá hạn';
$string['col_answer_status'] = 'Trạng thái trả lời';
$string['col_approval_status'] = 'Trạng thái phê duyệt';
$string['answer_status_unanswered'] = 'Đã phân công - chưa trả lời';
$string['answer_status_answered'] = 'Đã trả lời';
$string['approval_status_pending'] = 'Chưa duyệt';
$string['approval_status_approved'] = 'Đã duyệt';
$string['approval_status_rejected'] = 'Từ chối duyệt';
$string['bulkapproveanswer'] = 'Duyệt câu trả lời đã chọn';
$string['confirmbulkapproveanswer'] = 'Bạn có chắc chắn muốn duyệt các câu trả lời đã chọn?';
$string['bulk_approve_answer_success'] = 'Đã phê duyệt thành công {$a} câu trả lời.';
$string['exportexcel'] = 'Xuất Excel';
$string['ctv_status_detail'] = 'Chi tiết trạng thái theo từng CTV';
$string['select_filter_info'] = 'Vui lòng chọn bộ lọc để hiển thị dữ liệu.';
$string['from_ctv'] = 'từ CTV: {$a}';
$string['select_course_placeholder'] = 'Chọn khóa học...';
$string['not_exist'] = 'Không tồn tại';
$string['administrator'] = 'Quản trị viên';
$string['question_content'] = 'Nội dung câu hỏi';
$string['col_answer_content'] = 'Nội dung trả lời';
$string['col_crm_code'] = 'Mã CRM';
$string['count_questions_unit'] = '{$a} câu';
$string['show'] = 'Xem';
$string['items'] = 'mục';
$string['search_keyword'] = 'Từ khóa tìm kiếm';
$string['search_keyword_placeholder'] = 'Nhập từ khóa tìm kiếm';
$string['search_table_placeholder'] = 'Nhập từ khóa tìm kiếm';
$string['search_button'] = 'Tìm kiếm';
$string['assign_success'] = 'Phân công câu hỏi thành công.';
$string['company'] = 'Đơn vị';
$string['filter_company'] = 'Đơn vị';
$string['allcompanies'] = 'Tất cả đơn vị';
$string['col_company'] = 'Đơn vị';
$string['select_company'] = 'Chọn đơn vị';
