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
 * @package     local_th_alert_scheduledtask
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_alert_scheduledtask';

$string['subjectalertapprovecourse'] = 'Duyệt đề cương chi tiết';
$string['alertapprovecourse'] = 'Duyệt đề cương chi tiết, tài liệu và giáo án điện tử trên hệ thống, bấm nút Phê duyệt để
mở môn học (trước khi mở lớp ít nhất 1 tuần)';
$string['emailapprovecourse'] = 'Chào anh/chị,

Hệ thống Elearning đã thiết lập môn học ngày {$a->startdate}.
Anh chị vui lòng đăng nhập hệ thống {$a->elearningwebsite} theo tài khoản đã được cung cấp, vào môn dạy của mình và duyệt đề cương chi tiết, tài liệu và giáo án điện tử trên hệ thống bằng cách bấm nút "Phê duyệt" để mở môn học.
Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật ({$a->hotline}).

Xin cảm ơn';

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

$string['subject_alert_write_welcome_to_courseforum'] = 'Đăng bài chào mừng';
$string['alertwritewelcomtocourseforum'] = 'Đăng bài chào mừng hoặc chia sẻ kinh nghiệm lên mục Diễn đàn lớp học (trước ngày...): Giới thiệu sơ lược bản thân...';
$string['setting_alertwritewelcomtocourseforum'] = 'Nhắc nhở đăng bài giới thiệu trên forum';
$string['setting_dayalertwritewelcomtocourseforum'] = 'Ngày nhắc nhở đăng bài giới thiệu trên forum';

$string['setting_day_in_week'] = 'ngày trong nội dung thư:';
$string['setting_day_in_week_desc'] = '{ngày trong nội dung thư} tính từ ngày bắt đầu khóa học. Nếu có nhiều ngày, dùng dấy phẩy làm phân cách. Ví dụ: 5. Hoặc 9, 16, 23, 30, 37, 44, 51';

$string['emailwritewelcomtocourseforum'] = 'Chào anh/chị,

Anh chị cần đăng bài chào mừng hoặc chia sẻ kinh nghiệm tại Diễn đàn lớp học, giới thiệu sơ lược bản thân... trước {$a->duedate}. (Nếu đã thao tác, vui lòng bỏ qua nội dung thư này). Nếu cần hỗ trợ anh chị vui lòng liên hệ hotline: {$a->hotline}.

Xin cảm ơn';

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

$string['subjectalertpostscheduleonlinecourse'] = 'Đăng lịch học trực tuyến';
$string['alertpostscheduleonlinecourse'] = 'Đăng lịch học trực tuyến lên mục Thông báo (Trước thứ 6 của tuần học) (nếu có)';
$string['setting_alertpostscheduleonlinecourse'] = 'Nhắc nhở đăng lịch học trực tuyến';
$string['setting_dayalertpostscheduleonlinecourse'] = 'Ngày nhắc nhở đăng lịch học trực tuyến';

$string['emailpostscheduleonlinecourse'] = 'Chào anh/chị,

Anh chị cần đăng lịch học trực tuyến lên mục Thông báo trước thứ 6 tuần này.

Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline,{$a->hotline}).';

/////////////////////////////////////////////////////////////////////////////////////////////////////////////

$string['subjectalertanswerquestion'] = 'Trả lời mục "Hỏi/Đáp"';
$string['alertanswerquestion'] = 'Trả lời các câu hỏi trên mục "Hỏi/Đáp"';
$string['setting_alertanswerquestion'] = 'Nhắc nhở trả lời câu hỏi "Hỏi/Đáp"';
$string['setting_dayalertanswerquestion'] = 'Ngày nhắc nhở trả lời câu hỏi "Hỏi/Đáp"';
$string['emailanswerquestion'] = 'Chào anh/chị,

Anh chị vui lòng kiểm tra mục "Hỏi/Đáp" và phản hồi các câu hỏi của học
viên trong vòng 24h kể từ thời điểm học viên đặt câu hỏi.

Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline, {$a->hotline}).';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$string['subjectalertnhanxetdanhgiahangtuan'] = 'Đăng định hướng học tập';
$string['alertnhanxetdanhgiahangtuan'] = 'Đăng định hướng học tập tuần 2 hoặc chia sẻ kinh nghiệm tại Diễn đàn lớp học trước ngày.. tuần thứ 2 đến tuần thứ 8';
$string['setting_alertnhanxetdanhgiahangtuan'] = 'Nhắc nhở đăng định hướng học tập';
$string['setting_dayalertnhanxetdanhgiahangtuan'] = 'Ngày nhắc nhở đăng định hướng học tập';
$string['setting_dayalertnhanxetdanhgiahangtuandesc'] = 'Ngày nhắc nhở đăng định hướng học tập, nếu có nhiều ngày, dùng dấy phẩy làm phân cách. Ví dụ: 7 hoặc 7,14,21';

$string['emailnhanxetdanhgiahangtuan'] =
	'Chào anh/chị,

Anh chị cần đăng bài nhận xét về tuần học vừa qua và định hướng học tập
hoặc chia sẻ kinh nghiệm học tập cho nội dung học tuần này trước {$a->duedate}.

Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline, {$a->hotline}).';

////////////////////////////////////////////////////////////////////////////////////

$string['subjectalertchotbangdiem'] = 'Chốt bảng điểm bài tập nhóm';
$string['alertchotbangdiem'] = 'Chốt bảng điểm bài tập nhóm (nếu có) trước 23h55 ngày...';
$string['setting_alertchotbangdiem'] = 'Nhắc nhở chốt bảng điểm';
$string['setting_dayalertchotbangdiem'] = 'Ngày nhắc nhở chốt bảng điểm';

$string['emailchotbangdiem'] = 'Chào anh/chị,

Anh chị cần chốt bảng điểm bài tập nhóm (nếu có) trước 23h55, {$a->duedate}.
Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline, {$a->hotline}).

Xin cảm ơn';

/////////////////////////////////////////////////////////////////////////////////////
$string['setting_alertapprovecourse'] = 'Nhắc nhở phê duyệt khóa học';

$string['setting_dayalertapprovecourse'] = 'Ngày nhắc nhở phê duyệt khóa học';

$string['timedaymonthyear'] = '%d/%m/%Y';
$string['hotline'] = 'Hotline';
$string['elearningwebsite'] = 'Elearning website';
$string['universityshortname'] = 'University Shortname';
$string['emailcontact'] = 'Email contact';
$string['setting_other'] = 'Setting other';
$string['default_hotline'] = 'TNU: 0943846035, AOF: 0948293794';
$string['default_elearningwebsite'] = 'example: TNU: tnu.aum.edu.vn, AOF: aof.aum.edu.vn';
$string['default_emailcontact'] = 'thaovt@aum.edu.vn';

////////////////////////////////////////////////////////////////////////////////////

$string['subjectalertchamdiembaituluan'] = 'Chấm điểm bài kiểm tra tự luận';
$string['alertchamdiembaituluan'] = 'Chấm điểm bài kiểm tra tự luận (nếu có) trước 23h55 ngày...';
$string['setting_alertchamdiembaituluan'] = 'Nhắc nhở Chấm điểm bài kiểm tra tự luận';
$string['setting_dayalertchamdiembaituluan'] = 'Ngày nhắc nhở Chấm điểm bài kiểm tra tự luận';

$string['emailchamdiembaituluan'] = 'Chào anh/chị,

Nếu môn học anh/chị phụ trách giảng dạy có Bài kiểm tra tự luận, Anh/chị cần chấm điểm trước 23h55, {$a->duedate}. Nếu không có, vui lòng bỏ qua nội dung này.
Nếu cần hỗ trợ anh chị vui lòng liên hệ kỹ thuật (hotline, {$a->hotline}).

Xin cảm ơn';
$string['alert_answer_question'] = 'Cảnh báo: Trả lời câu hỏi';
$string['daysend'] = 'Ngày kết thúc: {$a}';
$string['coursefullname'] = 'Tên đầy đủ khóa học: {$a}';
$string['emailsendingto'] = 'Đang gửi email đến: {$a}';
$string['sendok'] = 'Gửi thành công: {$a}';
$string['email_already_sent'] = 'Email đã được gửi đến {$a}';
$string['alert_approve_course'] = 'Cảnh báo phê duyệt khóa học';
$string['daysend'] = 'Ngày gửi: {$a}';
$string['alert_chot_bang_diem'] = 'Cảnh báo chốt bảng điểm';
$string['alert_viet_nhanxetdanhgia_hangtuan'] = 'Cảnh báo viết nhận xét đánh giá hàng tuần';
$string['dayincontent'] = 'Ngày trong nội dung: {$a}';
$string['alert_write_welcome_to_course_forum'] = 'Cảnh báo: Viết tin nhắn chào mừng trong diễn đàn khóa học';
$string['alert_cham_diem_bai_tu_luan'] = 'Cảnh báo: Chấm điểm bài tự luận';
$string['dayincontent'] = 'Ngày trong nội dung: ';
