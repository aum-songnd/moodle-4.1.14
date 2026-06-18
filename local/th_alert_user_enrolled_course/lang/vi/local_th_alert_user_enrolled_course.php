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
 * @package     local_th_alert_user_enrolled_course
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_alert_user_enrolled_course';
$string['th_alert_user_enrolled_course:view'] = 'view permission';
$string['taskname'] = 'Thông báo ghi danh khóa học';

$string['mailsubject'] = 'Thông báo Ghi danh Khoá học';

$string['mailbody'] = 'Xin chào {$a->username},

Bạn đã được ghi danh vào khóa học {$a->coursename}.
Hãy đăng nhập và truy cập khóa học tại {$a->courselink}.
Nếu bạn chưa nhận được email thông báo tài khoản, vui lòng kiểm tra trong thư mục Quảng cáo / Spam, hoặc liên hệ với hotline kỹ thuật {$a->hotline} để được hỗ trợ.

Xin cảm ơn.
Ban quản trị hệ thống {$a->universityshortname} elearning.';

$string['mailbody_fragment1'] = '{$a->stt}. Bạn đã được ghi danh vào khoá học {$a->coursefullname}, Hãy đăng nhập và truy cập khoá học tại {$a->courselink}
';

$string['mailsubject_mode2'] = 'Xin chào {$a->fullname},
{$a->fragment1}Nếu bạn chưa nhận được email thông báo tài khoản, vui lòng kiểm tra trong thư mục Quảng cáo / Spam, hoặc liên hệ với hotline kỹ thuật {$a->hotline} để được hỗ trợ. Xin cảm ơn.';
$string['enrollment_notification'] = 'Thông báo Ghi danh Khoá học';
$string['adhoc_task_started'] = 'Tác vụ ad-hoc của tôi đã bắt đầu';
$string['send_email_per_enrollment'] = 'Mỗi ghi danh sẽ gửi 1 email';
$string['send_email_daily_at_22'] = 'Gửi toàn bộ mail ghi danh trong ngày vào 22h00';
$string['choose_email_method'] = 'Chọn phương thức gửi mail';
$string['page_heading'] = 'Tiêu đề';
