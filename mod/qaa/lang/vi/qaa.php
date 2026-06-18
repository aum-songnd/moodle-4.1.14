<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_qaa
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH Hỏi & Đáp';
$string['modulename'] = 'Hỏi & Đáp';
$string['modulenameplural'] = 'Danh sách Hỏi & Đáp';

$string['qaa:addinstance'] = 'Thêm một hoạt động Hỏi & Đáp mới';
$string['qaa:view'] = 'Xem Hỏi & Đáp';
$string['qaa:deletequest'] = 'Xoá câu hỏi trong Hỏi & Đáp';
$string['qaa:approval'] = 'Phê duyệt câu hỏi trong Hỏi & Đáp';
$string['qaa:makequest'] = 'Tạo câu hỏi';
$string['qaa:manage'] = 'Quản lý hỏi đáp';

$string['pluginadministration'] = 'Quản lý Hỏi & Đáp';

$string['qaname'] = 'Tên hoạt động';
$string['qaname_help'] = 'Tên của hoạt động Hỏi & Đáp.';

$string['addquestion'] = 'Thêm câu hỏi mới';
$string['addanswer'] = 'Thêm câu trả lời';
$string['editanswer'] = 'Chỉnh sửa câu trả lời';
$string['editquestion'] = 'Chỉnh sửa câu hỏi';
$string['pagetitlequestion'] = 'Câu hỏi:';
$string['textfieldsquestion'] = 'Thêm câu hỏi';
$string['pagetitleanswer'] = 'Câu trả lời:';
$string['textfieldsanswer'] = 'Trả lời';
$string['submit'] = 'Gửi';

$string['question'] = 'Câu hỏi';
$string['answer'] = 'Trả lời';

$string['questiondetail'] = 'Câu hỏi ({$a->student} - {$a->datetime})';
$string['answerdetail'] = 'Trả lời ({$a->teacher} - {$a->datetime})';
$string['detailmode'] = 'Chi tiết câu hỏi';

$string['answered'] = 'Đã trả lời {$a}';
$string['unanswered'] = 'Chưa trả lời {$a}';
$string['week'] = 'Tuần: {$a->weekbegin_humandate} → {$a->weekend_humandate}';
$string['strftimedaydate'] = '%A, %d/%m/%Y';

$string['choice'] = 'Lựa chọn';
$string['qaasaved'] = 'Đã lưu Hỏi & Đáp';
$string['members/'] = 'Thành viên';
$string['members/max'] = 'Thành viên / Sức chứa';
$string['removemyqaa'] = 'Xoá khỏi danh sách';
$string['savemyqaa'] = 'Lưu Hỏi & Đáp';
$string['postincontext'] = 'Xem bài viết trong ngữ cảnh';
$string['mailsubject'] = '{$a->coursname}: {$a->type} - {$a->subject}';
$string['delete_question'] = 'Xoá câu hỏi này';
$string['deleteconfirm'] = 'Bạn có chắc chắn muốn xoá câu hỏi này không?';
$string['sessionerror'] = 'Phiên làm việc của bạn đã hết hạn. Vui lòng tải lại trang.';
$string['deleteerror'] = 'Xoá thất bại';
$string['min_character'] = 'Số ký tự tối thiểu của câu hỏi';
$string['min_characterdesc'] = 'Đây là số ký tự tối thiểu mà học viên phải nhập cho câu hỏi.';
$string['err_questionform'] = 'Số ký tự phải ít nhất là {$a}';

$string['event_qaapairs_created'] = 'Câu hỏi mới được tạo';
$string['event_qaapairs_reject'] = 'Câu hỏi bị từ chối';
$string['event_qaapairs_deleted'] = 'Câu hỏi đã bị xoá';
$string['event_qaapairs_answered'] = 'Câu hỏi đã được trả lời';
$string['event_qaapairs_answer_edited'] = 'Câu trả lời đã được chỉnh sửa';
$string['event_qaapairs_question_edited'] = 'Câu hỏi đã được chỉnh sửa';
$string['event_qaapairs_viewed'] = 'Đã xem Hỏi & Đáp';

$string['submitnewquestion'] = 'Gửi câu hỏi mới';
$string['submitnewanswer'] = 'Gửi câu trả lời mới';
$string['placeholderquestion'] = 'Nhập câu hỏi mới...';
$string['placeholderanswer'] = 'Nhập câu trả lời...';

$string['monitoremailaddress'] = 'Địa chỉ email giám sát Hỏi & Đáp';

$string['typequestion'] = 'Câu hỏi';
$string['typequestionedited'] = 'Câu hỏi đã chỉnh sửa';
$string['typeanswer'] = 'Trả lời';
$string['typeansweredited'] = 'Trả lời đã chỉnh sửa';

$string['maxfileupload'] = 'Kích thước tệp tải lên tối đa';
$string['qaahelpmaximumupload'] = 'Xác định kích thước tệp lớn nhất có thể tải lên trong hoạt động này, bị giới hạn bởi cài đặt toàn hệ thống.';
$string['typefileupload'] = 'Loại tệp được phép tải lên';
$string['limittypefileupload'] = 'Giới hạn loại tệp tải lên (phân cách bằng dấu phẩy ,)';
$string['limitmaxfiles'] = 'Giới hạn số lượng tệp tải lên';
$string['limitmaxfilesdesc'] = 'Giới hạn số lượng tệp mà học viên được phép tải lên.';
$string['limittimecreatequestion'] = 'Giới hạn thời gian tạo câu hỏi';
$string['des_limittimecreatequestion'] = 'Thời gian giới hạn tạo câu hỏi (giây)';

$string['user_edited_answer'] = 'Người dùng có ID {$a->userid} đã chỉnh sửa câu trả lời có ID {$a->objectid} trong hoạt động Hỏi & Đáp với module ID {$a->contextinstanceid}.';
$string['user_answered_question'] = 'Người dùng có ID {$a->userid} đã trả lời câu hỏi có ID {$a->objectid} trong hoạt động Hỏi & Đáp với module ID {$a->contextinstanceid}.';
$string['user_created_question'] = 'Người dùng có ID {$a->userid} đã tạo câu hỏi có ID {$a->objectid} trong hoạt động Hỏi & Đáp với module ID {$a->contextinstanceid}.<br><b>Khác:</b> {$a->other}';
$string['user_reject_question'] = 'Người dùng có ID {$a->userid} đã từ chối câu hỏi có ID {$a->objectid} trong hoạt động Hỏi & Đáp với module ID {$a->contextinstanceid}.';
$string['user_deleted_question'] = 'Người dùng có ID {$a->userid} đã xoá câu hỏi có ID {$a->objectid} trong hoạt động Hỏi & Đáp với module ID {$a->contextinstanceid}.';
$string['user_edited_question'] = 'Người dùng có ID {$a->userid} đã chỉnh sửa câu hỏi có ID {$a->objectid} trong hoạt động Hỏi & Đáp với module ID {$a->contextinstanceid}.';
$string['user_viewed_qaa'] = 'Người dùng có ID {$a->userid} đã xem hoạt động Hỏi & Đáp có ID {$a->objectid} trong khoá học với module ID {$a->contextinstanceid}.';

$string['runtask'] = 'Tác vụ ad-hoc của tôi đã bắt đầu';
$string['question_not_exist_or_no_teacher'] = 'Câu hỏi không tồn tại hoặc không có giảng viên.';
$string['email_send_success'] = 'Gửi email thành công tới: {$a}';
$string['email_send_fail'] = 'Gửi email thất bại tới: {$a}';
$string['enable_sendmailcreatequestion'] = 'Gửi email cho giảng viên khi học viên tạo câu hỏi';
$string['des_enable_sendmailcreatequestion'] = 'Bật/tắt chức năng gửi email thông báo đến giảng viên khi học viên gửi câu hỏi.';

$string['creator'] = 'Người hỏi';
$string['discussion'] = 'Câu hỏi';
$string['latestpost'] = 'Trao đổi gần nhất';
$string['replies'] = 'Trả lời';

$string['approval'] = 'Phê duyệt và trả lời câu hỏi';
$string['approvalqueue'] = 'Danh sách câu hỏi chờ phê duyệt';
$string['approveandanswer'] = 'Phê duyệt và trả lời';
$string['youranswer'] = 'Câu trả lời của bạn';
$string['rejectwithreason'] = 'Từ chối và ghi lý do';
$string['reason'] = 'Lý do từ chối';
$string['approvequestion'] = 'Phê duyệt và trả lời câu hỏi';
$string['rejectquestion'] = 'Từ chối câu hỏi';
$string['rejectreason'] = 'Lý do từ chối';
$string['rejected'] = 'Đã bị từ chối';

$string['confirmdeletequestion'] = 'Bạn có chắc chắn muốn xoá câu hỏi này không?';
$string['deletewarning'] = 'Hành động này không thể hoàn tác.';
$string['deletechildwarning'] = '⚠️ Câu hỏi này có các câu trả lời liên quan. Nếu bạn tiếp tục, toàn bộ các câu trả lời đó cũng sẽ bị xoá.';
$string['timecreated'] = 'Thời gian tạo';
$string['err_content_too_short'] = 'Nội dung quá ngắn (tối thiểu {$a} ký tự).';
// mail
$string['typereply']   = 'Phản hồi mới';
$string['replyinline'] = 'Có phản hồi mới trong luồng';
$string['reply'] = 'Trả lời';
$string['unansweredquestions'] = 'Danh sách câu hỏi chưa trả lời';
$string['typequestionapproved'] = 'Câu hỏi đã được phê duyệt';
$string['questionapproved'] = 'Câu hỏi của bạn đã được phê duyệt';
$string['typequestionreject'] = 'Câu hỏi đã bị từ chối';
$string['questionreject'] = 'Câu hỏi của bạn đã bị từ chối';
$string['tag_pending'] = '🕓 Chờ phê duyệt';
$string['rejected_tag'] = 'Bị từ chối';
$string['edit'] = 'Chỉnh sửa';
$string['delete'] = 'Xóa';
$string['badge_author'] = 'Tác giả';
$string['question_close_time'] = 'Thời hạn kết thúc hỏi đáp';