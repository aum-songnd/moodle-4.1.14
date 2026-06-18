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
 * Strings for component 'quiz_thessaygrader', language 'en'
 *
 * @package   quiz_thessaygrader
 * @copyright 2020 CBlue Srl
 * @copyright based on work by 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'TH Chấm điểm thi online';
$string['thessaygrader'] = 'Chấm điểm thi online'; // displayed in the navigation menu
$string['thessaygraderreport'] = 'TH Chấm điểm thi online';

$string['thessaygraderselected'] = 'Tải tập tin đính kèm';

$string['thessaygraderattemptcheck'] = 'Tải các bài đã chọn';

$string['thessaygraderattempt'] = 'Thessaygrader attempt';

// thessaygrader_form
$string['thessaygradersettings'] = 'Cài đặt chấm điểm';
$string['pagemode'] = 'Chế độ trang (Chế độ ngắt trang trong khi hiển thị bài kiểm tra xem lại trước khi chuyển đổi sang PDF)';
$string['thessaygradermodetruepage'] = 'Phân công trang câu hỏi thực tế';
$string['thessaygradermodequestionperpage'] = 'Một câu hỏi trên mỗi trang';
$string['thessaygradermodesinglepage'] = 'Tất cả các câu hỏi trên một trang';

// Inside the pdf
$string['documenttitle'] = '{$a->coursename} <br> {$a->quizname} <br> - <br> Summary of {$a->firstname} {$a->lastname}\'s attempt';

$string['role_hidden'] = 'Chọn vai trò người dùng';
$string['role_hidden_desc'] = 'Chọn vai trò người dùng bị ẩn thông tin';

$string['thessaygrader:view'] = 'Cho phép chấm điểm';
$string['identity'] = 'ID';

$string['customfield_shortname'] = 'Tên rút gọn của custom field mã học viên';
$string['customfield_description'] = 'Ngăn cách nhau bằng dấu ,';
$string['attempt_secret_key'] = 'Khóa bí mật cho attempt';
$string['attempt_secret_key_description'] = 'Khóa bí mật được sử dụng để mã hóa/giải mã ID của attempt một cách an toàn.';
$string['attempt_secret_iv'] = 'Chuỗi IV bí mật cho attempt';
$string['attempt_secret_iv_description'] = 'Chuỗi khởi tạo (IV) bí mật dùng để mã hóa ID của attempt.';
