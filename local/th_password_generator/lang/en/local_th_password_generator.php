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
 * @package     local_th_password_generator
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH password generator';

$string['passworddefaultdesc'] = 'Thay thế default password generator, nếu tick tính năng này được bật';
$string['autopassworddesc'] = 'Gửi email tự sinh password cho học viên, nếu tick tính năng này được bật';
$string['passwordsymbolsdesc'] = 'Tự động sinh ra ký tự đặc biệt, nếu tick tính năng này được bật';
$string['passwordnumbersdesc'] = 'Tự động sinh ra dãy số, nếu tick tính năng này được bật';
$string['passwordlowercasedesc'] = 'Tự động sinh ra dãy chữ thường, nếu tick tính năng này được bật';
$string['passworduppercasedesc'] = 'Tự động sinh ra dãy chữ hoa, nếu tick tính năng này được bật';
$string['passwordsimilardesc'] = 'Ví dụ: i, l, 1, L, o, 0, O sẽ tự động không sinh ra, nếu tick tính năng này được bật';
$string['passwordambiguousdesc'] = 'Ví dụ:  { } [ ] ( ) / \  " ` ~ , ; : . < > sẽ tự động không sinh ra, nếu tick tính năng này được bật';

//

$string['setting_passworddefault'] = 'Thay thế defaul password generator';
$string['setting_passwordsymbols'] = 'Bao gồm các kí tự đặc biệt';
$string['setting_passwordnumbers'] = 'Bao gồm dãy số';
$string['setting_passwordlowercase'] = 'Bao gồm chữ thường';
$string['setting_passworduppercase'] = 'Bao gồm chữ hoa';
$string['setting_passwordsimilar'] = 'Không bao gồm một vài chữ hoặc số có thể giống nhau';
$string['setting_passwordambiguous'] = 'Không bao gồm một vài kí tự không rõ ràng';

////
$string['setting_dpasswordlen'] = 'Độ dài password tự động';
$string['setting_lenpasswordsymbols'] = 'Độ dài chứa kí tự đặc biệt';
$string['setting_lenpasswordnumbers'] = 'Độ dài của dãy số';
$string['setting_lenpasswordlowercase'] = 'Độ dài chữ thường';
$string['setting_lenpassworduppercase'] = 'Độ dài chữ hoa';

$string['setting_addpasswordsimilar'] = 'Nhập chữ số mà bạn không mong muốn trong mật khẩu';

$string['setting_addpasswordambiguous'] = 'Nhập kí tự mà bạn không mong muốn trong mật khẩu';
