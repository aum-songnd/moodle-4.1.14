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
 * @package     local_th_alert_forum
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_alert_forum';
$string['taskname'] = 'Thông báo Diễn đàn';
$string['mailsubject_hoi_dap'] = 'Các câu hỏi và câu trả lời trên Hỏi đáp';
$string['th_alert_forum:view'] = 'view permission';

$string['setting_email'] = 'Danh sách email gửi mail, ngăn cách nhau bằng dấu phẩy';
$string['setting_send'] = 'Kiểu gửi';
$string['note'] = 'Chọn gửi theo lịch hoặc theo sự kiện';

$string['mailsubject'] = 'Các câu hỏi và câu trả lời trên Diễn đàn';
$string['postincontext'] = 'See this post in context';
$string['cannot_send_email_null_user'] = 'Không thể gửi email đến người dùng rỗng';
$string['cannot_send_email_no_email'] = 'Không thể gửi email cho người dùng không có email: {$a}';
$string['cannot_send_email_deleted_user'] = 'Không thể gửi email cho người dùng đã bị xóa: {$a}';
$string['not_sending_email_noemailever'] = 'Không gửi email do cấu hình $CFG->noemailever';
$string['email_to_user_invalid'] = 'email_to_user: Người dùng {$a->id} ({$a->fullname}) có email ({$a->email}) không hợp lệ! Không gửi.';
$string['email_to_user_bounce'] = 'email_to_user: Người dùng {$a->id} ({$a->fullname}) đã vượt quá ngưỡng bounce! Không gửi.';
$string['email_to_user_invalid_domain'] = 'email_to_user: Người dùng {$a->id} ({$a->fullname}) có miền email ({$a->email}) không hợp lệ! Không gửi.';
$string['invalid_noreply_email'] = 'email_to_user: Địa chỉ email noreply không hợp lệ {$a->noreply}';
$string['invalid_replyto_email'] = 'email_to_user: Địa chỉ email reply-to không hợp lệ {$a->replyto}';
$string['invalid_from_email'] = 'email_to_user: Địa chỉ email gửi không hợp lệ {$a->email} - không gửi';
$string['email_dkim_no_cert'] = 'Đã chọn DKIM cho email từ {$a->from} nhưng không tìm thấy chứng chỉ tại {$a->pempath}';
$string['email_to_user_error'] = 'Lỗi: lib/moodlelib.php email_to_user(): {$a}';