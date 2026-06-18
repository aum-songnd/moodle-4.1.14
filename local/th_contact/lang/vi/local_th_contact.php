<?php
// This file is part of the TH Contact Form plugin for Moodle - http://moodle.org/
//
// TH Contact Form is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TH Contact Form is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TH Contact Form.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This plugin for Moodle is used to send emails through a web form.
 *
 * @package    local_th_contact
 * @copyright  2016-2021 TNG Consulting Inc. - www.tngconsulting.ca
 * @author     Michael Milette
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH Liên hệ';
$string['privacy:metadata'] = 'Plugin TH Liên hệ không lưu trữ bất kỳ dữ liệu cá nhân nào của người dùng.';
$string['globalhelp'] = 'TH Liên hệ là plugin cho Moodle cho phép xử lý thông tin được gửi từ các biểu mẫu HTML đến email hỗ trợ của hệ thống.';
$string['configure'] = 'Cấu hình plugin';
$string['confirmationpage'] = 'Trạng thái';

$string['field-name'] = 'Họ và tên';
$string['field-email'] = 'Email';
$string['field-subject'] = 'Tiêu đề';
$string['field-message'] = 'Nội dung';

$string['noreplyto'] = 'Không dùng reply-to';
$string['noreplyto_description'] = 'Một số máy chủ SMTP sẽ từ chối email nếu trường from và noreply-to khác nhau. Bật tùy chọn này để đảm bảo địa chỉ no-reply giống với địa chỉ gửi thay vì email người gửi.';

$string['confirmationmessage'] = 'Cảm ơn bạn đã liên hệ với chúng tôi. Nếu cần, chúng tôi sẽ phản hồi bạn sớm nhất có thể.';
$string['confirmationsent'] = 'Một email đã được gửi đến địa chỉ của bạn tại {$a}.';
$string['forbidden'] = 'Truy cập bị từ chối';
$string['errorsendingtitle'] = 'Gửi email thất bại';
$string['errorsending'] = 'Đã xảy ra lỗi khi gửi email. Vui lòng thử lại sau.';

$string['defaultsubject'] = 'Tin nhắn mới';

$string['extrainfo'] = '<hr>
<p><strong>Thông tin bổ sung</strong></p>
<ul>
    <li><strong>Người dùng:</strong> [userstatus]</li>
    <li><strong>Ngôn ngữ:</strong> [lang]</li>
    <li><strong>Địa chỉ IP:</strong> [userip]</li>
    <li><strong>Trình duyệt:</strong> [http_user_agent]</li>
    <li><strong>Biểu mẫu:</strong> <a href="[http_referer]">[http_referer]</a></li>
</ul>
';

$string['confirmationemail'] = '
<p>Xin chào [fromname],</p>
<p>Cảm ơn bạn đã liên hệ với chúng tôi. Nếu cần, chúng tôi sẽ phản hồi bạn sớm nhất có thể.</p>
<p>Trân trọng,</p>
<p>[supportname]<br>
[sitefullname]<br>
<a href="[siteurl]">[siteurl]</a></p>
';

$string['lockedout'] = 'BỊ KHÓA';
$string['notconfirmed'] = 'CHƯA XÁC NHẬN';
$string['duplicateemailaddresses'] = 'Có nhiều người dùng sử dụng cùng một địa chỉ email.';

$string['senderaddress'] = 'Địa chỉ người gửi tùy chỉnh';
$string['senderaddress_description'] = 'Email sẽ được gửi từ địa chỉ này. Nếu để trống, email sẽ được gửi từ địa chỉ no-reply.';

$string['recipient_list'] = 'Danh sách người nhận';
$string['recipient_list_description'] = 'Bạn có thể cấu hình danh sách người nhận tại đây. Danh sách này có thể dùng trong form thông qua field ẩn hoặc dropdown mà không cần hiển thị email thật.
Nếu danh sách trống, email sẽ được gửi đến email hỗ trợ của Moodle hoặc admin chính.

Mỗi dòng phải có định dạng:
alias|email|tên

Ví dụ:
<pre>
hotro|support@example.com|Hỗ trợ kỹ thuật
quantri|admin@example.com|Quản trị hệ thống
</pre>';

$string['loginrequired'] = 'Yêu cầu đăng nhập';
$string['loginrequired_description'] = 'Chỉ cho phép người dùng đã đăng nhập gửi form. Khách (guest) không được tính.';

$string['nosubjectsitename'] = 'Không thêm tên site vào tiêu đề email';
$string['nosubjectsitename_description'] = 'Bật để không thêm [tên site] vào tiêu đề email. Không ảnh hưởng đến cấu hình chung của Moodle.';

$string['recapchainfo'] = 'Sử dụng ReCAPTCHA';
$string['recapchainfo_description'] = 'ReCAPTCHA hiện đang bật trong Moodle. Bạn cần:
<ul>
  <li>Đảm bảo tag {recaptcha} có trong form.</li>
  <li>Cài và bật plugin FilterCodes.</li>
</ul>';

$string['norecaptcha'] = 'Không dùng ReCAPTCHA';
$string['norecaptcha_description'] = 'Không sử dụng ReCAPTCHA cho form.';
