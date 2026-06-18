<?php


$string['privacy:metadata'] = 'TH Force Password Change';
$string['pluginname'] = 'TH Force Password Change';
$string['selectuser'] = 'Select User';
$string['heading_selectrole'] = 'Select Roles Force Password Change';
$string['selectrole'] = 'Select Roles';
$string['selectrole_desc'] = 'Will take all accounts with this selected permission. Force password change.';
$string['send_mail'] = 'Send Email reset password.';
$string['cron_send_mail'] = 'Send the link by email';
$string['cron_send_mail_desc'] = 'Schedule email links';
$string['here'] = 'Đây';
$string['heading_sendmail'] = 'Send mail';
$string['heading_tokenduration'] = 'Link time is alive';
$string['tokenduration'] = 'Link time is alive';
$string['tokenduration_desc'] = 'Time URL set password alive';
$string['setpasswordsubj'] = 'Đặt lại mật khẩu';
$string['taskpasswordresetcleanup'] = 'TH Cleanup password reset attempts';
$string['tasksetpassword'] = 'TH set password';
$string['setpasswordtext'] = '
{$a->firstname} {$a->lastname} thân mến!

Bạn cần phải Bấm vào {$a->link} ({$a->url}) để tạo mật khẩu đăng nhập vào hệ thống.
- Địa chỉ đăng nhập tại: {$a->login}
Lưu ý: mật khẩu phân biệt chữ hoa, chữ thường và không chứa dấu cách ở hai đầu. Khi nhập mật khẩu nên tắt chế độ gõ tiếng Việt.

Cảm ơn,
Ban Quản Trị {$a->sitename}';