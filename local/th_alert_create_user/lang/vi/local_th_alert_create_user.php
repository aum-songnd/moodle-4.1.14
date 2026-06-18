<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH Thông Báo Tạo Tài Khoản';
$string['newusernewpasswordsubj'] = 'Tài khoản thành viên mới';
$string['newusernewpasswordtext'] = '
{$a->firstname} {$a->lastname} thân mến!

Chào mừng đến với hệ thống \'{$a->sitename}\'.

Tài khoản để học tập của bạn đã được tạo với các thông tin sau:
- Tên đăng nhập: {$a->username}
- Mật khẩu: bấm vào {$a->link} ({$a->url}) để tạo mật khẩu.
- Địa chỉ đăng nhập: {$a->login}
Lưu ý: mật khẩu phân biệt chữ hoa, chữ thường và không chứa dấu cách ở hai đầu. Khi nhập mật khẩu nên tắt chế độ gõ tiếng Việt.

Cảm ơn,
Ban Quản Trị \'{$a->sitename}\'';
$string['here'] = 'ĐÂY';
$string['setpassword'] = 'Đặt mật khẩu';
$string['setting_heading'] = 'Thông báo tạo tài khoản';
$string['setting_createpassword'] = 'Người dùng tự tạo mật khẩu';
$string['des_setting_createpassword'] = 'Gửi liên kết cho người dùng để đặt mật khẩu';
$string['setpasswordinstructions'] = 'Hãy nhập và xác nhận lại mật khẩu của bạn bên dưới, sau đó bấm "Lưu thay đổi".';
$string['forgotteninvalidurl'] = 'URL đặt lại mật khẩu không hợp lệ';
$string['cannotresetguestpwd'] = 'Không thể đặt lại mật khẩu cho tài khoản khách';
$string['errorpasswordupdate'] = 'Lỗi khi cập nhật mật khẩu.';
$string['auth_forcepasswordchange'] = 'Bắt buộc đổi mật khẩu';
$string['create_password'] = 'Tạo mật khẩu';
