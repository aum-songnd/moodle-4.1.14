<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH Create User Notification';
$string['newusernewpasswordsubj'] = 'New user account';
$string['newusernewpasswordtext'] = '
{$a->firstname} {$a->lastname} thân mến!

Chào mừng bạn đến với hệ thống \'{$a->sitename}\'

Tài khoản để học tập của bạn đã được tạo với các thông tin sau:
- Tên đăng nhập: {$a->username}
- Mật khẩu: Bấm vào {$a->link} ({$a->url}) để tạo mật khẩu đăng nhập vào hệ thống.
- Địa chỉ đăng nhập tại: {$a->login}
Lưu ý: mật khẩu phân biệt chữ hoa, chữ thường và không chứa dấu cách ở hai đầu. Khi nhập mật khẩu nên tắt chế độ gõ tiếng Việt.

Cảm ơn,
Ban Quản Trị \'{$a->sitename}\'';
$string['here'] = 'HERE';
$string['setpassword'] = 'Set Password';
$string['setting_heading'] = 'TH Create User Notification';
$string['setting_createpassword'] = 'User to create password';
$string['des_setting_createpassword'] = 'Send a link to users to reset password';
$string['setpasswordinstructions'] = 'Please enter your password and confirm password below, then save changes.';
$string['forgotteninvalidurl'] = 'Invalid reset password URL';
$string['cannotresetguestpwd'] = 'Guest user password cannot be reset';
$string['errorpasswordupdate'] = 'Error while updating password.';
$string['auth_forcepasswordchange'] = 'Force password change';
$string['create_password'] = 'Create password';
