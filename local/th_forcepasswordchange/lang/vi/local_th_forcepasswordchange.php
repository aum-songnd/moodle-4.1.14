<?php


$string['privacy:metadata'] = 'TH Bắt buộc thay đổi mật khẩu';
$string['pluginname'] = 'TH Bắt buộc thay đổi mật khẩu';
$string['selectuser'] = 'Chọn người dùng';
$string['heading_selectrole'] = 'Chọn Quyền Bắt buộc thay đổi mật khẩu';
$string['selectrole'] = 'Chọn Quyền';
$string['selectrole_desc'] = 'Sẽ lấy tất cả các Tài khoản có quyền được chọn này. Bắt buộc thay đổi mật khẩu.';
$string['send_mail'] = 'Gửi liên kết đặt lại mật khẩu qua email.';
$string['cron_send_mail'] = 'Gửi liên kết qua thư điện tử';
$string['cron_send_mail_desc'] = 'Lập lịch gửi liên kết qua thư điện tử';
$string['here'] = 'Đây';
$string['heading_sendmail'] = 'Gửi thư điên tử';
$string['heading_tokenduration'] = 'Thời gian URL còn sống';
$string['tokenduration'] = 'Thời gian URL còn sống';
$string['tokenduration_desc'] = 'Thời gian URL đặt mật khẩu còn sống';
$string['setpasswordsubj'] = 'Đặt lại mật khẩu';
$string['taskpasswordresetcleanup'] = 'TH Dọn dẹp các lần đặt lại mật khẩu';
$string['tasksetpassword'] = 'TH Đặt mật khẩu';
$string['setpasswordtext'] = '
{$a->firstname} {$a->lastname} thân mến!

Bạn cần phải Bấm vào {$a->link} ({$a->url}) để tạo mật khẩu đăng nhập vào hệ thống.
- Địa chỉ đăng nhập tại: {$a->login}
Lưu ý: mật khẩu phân biệt chữ hoa, chữ thường và không chứa dấu cách ở hai đầu. Khi nhập mật khẩu nên tắt chế độ gõ tiếng Việt.

Cảm ơn,
Ban Quản Trị {$a->sitename}';