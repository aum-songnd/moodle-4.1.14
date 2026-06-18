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
 * @package     local_th_vmc_sms
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_vmc_sms';

$string['setting_timecourse'] = 'Thời gian của khóa học';

$string['tin_nhan2'] = 'Gửi SMS đối với học viên chưa hoàn thành 50% bài giảng';

$string['setting_tin_nhan2'] = 'Gửi SMS đối với học viên chưa hoàn thành 50% bài giảng';

$string['noi_dung_tin_nhan2'] = 'Khoa hoc {$a->courseshortname} cua ban van chua hoan thanh. Hay danh thoi gian tu cham soc ban than ban nhe.';

$string['tin_nhan3'] = 'Gửi SMS đối với học viên chưa hoàn thành bài giảng';

$string['noi_dung_tin_nhan3'] = 'Khoa hoc {$a->courseshortname} cua ban van chua hoan thanh. Chi con {$a->day} ngay nua la het han khoa hoc. Hay danh thoi gian tu cham soc ban than ban nhe.';

$string['setting_tin_nhan3'] = 'Gửi SMS đối với học viên chưa hoàn thành khóa học';

$string['tin_nhan4'] = 'Gửi SMS đối với học viên chưa hoàn thành bài giảng';

$string['noi_dung_tin_nhan4'] = 'Khoa hoc {$a->courseshortname} cua ban van chua hoan thanh. Chi con {$a->day} ngay nua la het han khoa hoc. Hay danh thoi gian tu cham soc ban than ban nhe.';

$string['setting_tin_nhan4'] = 'Gửi SMS đối với học viên chưa hoàn thành khóa học';

$string['sendsms'] = 'Gửi SMS nếu tính năng này sẽ được bật';

$string['setting_daysend'] = 'Ngày gửi SMS sau ngày tạo tài khoản';

$string['noidungtaikhoanmoi'] = 'Tai khoan khoa hoc cua ban da duoc kich hoat. Ten dang nhap: {$a->username}. Mat khau: {$a->newpassword}. Truy cap web: https://bit.ly/43nL84l';

$string['setting_smsapi'] = 'Cấu hình gửi SMS';

$string['setting_sms_api_url'] = 'URL gửi SMS';

$string['setting_sms_api_url_desc'] = 'Đường dẫn đến API gửi tin nhắn của nhà cung cấp.';

$string['setting_sms_api_token'] = 'Token xác thực';

$string['setting_sms_api_token_desc'] = 'Token xác thực để gửi tin nhắn.';

$string['setting_sms_brandname'] = 'Brandname';

$string['setting_sms_brandname_desc'] = 'Tên brandname hiển thị khi gửi SMS.';
