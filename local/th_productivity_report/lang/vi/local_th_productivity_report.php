<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Báo cáo năng suất';
$string['taskname'] = 'Gửi báo cáo năng suất hàng tuần';

$string['list_email'] = 'Danh sách các thư điện tử';
$string['des_list_email'] = 'Danh sách email gửi mail, ngăn cách nhau bằng dấu phẩy';
$string['time'] = 'Thời gian';
$string['des_time'] = 'Khoảng thời gian lấy dữ liệu báo cáo so với hiện tại';
$string['setting_heading'] = 'Báo cáo năng suất';

$string['subject_email'] = 'Báo cáo năng suất VMC từ {$a->time}';
$string['message_email'] = 'Báo cáo số lượng học viên được tạo tài khoản, gán khóa học từ {$a->time}
	1. Số tài khoản được gán từ {$a->time} là: {$a->number_userenrol}
	2. Số tài khoản được tạo từ {$a->time} là: {$a->number_user}
	Chi tiết trong tệp đính kèm.';

$string['taskname2'] = 'Gửi báo cáo năng suất hàng tháng';
$string['subject_email_month'] = 'Báo cáo năng suất hàng tháng VMC từ {$a->time}';
$string['crm'] = 'Mã AUM';
$string['des_crm'] = 'Mã AUM';
$string['textfields'] = 'Lọc';
$string['submit'] = 'Tải xuống';
$string['from'] = 'Từ ngày';
$string['to'] = 'Đến ngày';