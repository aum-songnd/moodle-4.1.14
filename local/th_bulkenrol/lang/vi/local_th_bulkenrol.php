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
 * Local plugin "th_bulkenrol" - Language pack
 *
 * @package   local_th_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['th_bulkenrol:enrolusers'] = 'TH Ghi danh theo lô';
$string['th_bulkenrol_form_intro'] = 'Tại đây, bạn có thể đăng ký hàng loạt người dùng vào khóa học của mình. Người dùng được đăng ký được xác định bằng địa chỉ e-mail được lưu trữ trong tài khoản Moodle của anh ta.';
$string['enrol_users_successful'] = 'Ghi danh theo lô thành công';
$string['suspend_users_successful'] = 'Tạm dừng người dùng theo lô thành công';
$string['unenrol_users_successful'] = 'Hủy ghi danh người dùng thành công';

$string['enrol_users'] = 'Ghi danh các người dùng';
$string['suspend_users'] = 'Đình chỉ ghi danh các người dùng';
$string['unenrol_users'] = 'Hủy ghi danh người dùng';

$string['enrolinfo_headline'] = 'Enrolment details';
$string['enrolplugin'] = 'Phương thức ghi danh';
$string['enrolplugin_desc'] = 'The enrolment method to be used to bulk enrol the users. If the configured enrolment method is not active / added in the course when the users are bulk-enrolled, it is automatically added / activated.';
$string['error_enrol_users'] = 'There was a problem when enrolling the users to the course.';
$string['error_enrol_user'] = 'There was a problem when enrolling the user with e-mail <em>{$a->email}</em> to the course.';
$string['error_exception_info'] = 'Exception information';
$string['error_getting_user_for_email'] = 'There was a problem when getting the user record for e-mail address <em>{$a}</em> from the database.';
$string['error_group_add_members'] = 'There was a problem when adding the users to the course group(s).';
$string['error_group_add_member'] = 'There was a problem when adding the user with e-mail <em>{$a->email}</em> to the course group <em>{$a->group}</em>.';
$string['error_invalid_email'] = 'Địa chỉ email không hợp lệ ở dòng {$a->row} (<em>{$a->email}</em>). Dòng này sẽ được bỏ qua.';
$string['error_more_than_one_record_for_email'] = 'More than one existing Moodle user account with e-mail address <em>{$a}</em>em> found.<br /> This line will be ignored, none of the existing Moodle users will be enrolled.';
$string['error_no_email'] = 'No e-mail address found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_no_valid_email_in_list'] = 'Địa chỉ email không hợp lệ.<br />Please <a href=\'{$a->url}\'>go back and check your input</a>.';
$string['error_no_record_found_for_email'] = 'Không có tài khoản người dùng hiện có với địa chỉ e-mail <em>{$a}</em>.<br />This line will be ignored, there won\'t be a Moodle user account created on-the-fly.';
$string['error_usermails_empty'] = 'Danh sách địa chỉ e-mail trống. Vui lòng thêm ít nhất một địa chỉ e-mail.';
$string['error_check_is_already_member'] = 'Error checking if the user (<em>{$a->email}</em>) is already a member of group (<em>{$a->groupname}</em>). {$a->error}';
$string['groupinfos_headline'] = 'Groups included in the list';
$string['group_name_headline'] = 'Group name';
$string['group_status_create'] = 'Group will be created';
$string['group_status_exists'] = 'Group already exists';
$string['group_status_headline'] = 'Group status';
$string['hints'] = 'Hints';
$string['pluginname'] = 'TH Ghi danh theo lô';
$string['privacy:metadata'] = 'The th user bulk enrolment plugin acts as a tool to enrol users into courses, but does not store any personal data.';
$string['role'] = 'Vai trò';
$string['role_assigned'] = 'Assigned role';
$string['role_description'] = 'The role to be used to bulk enrol the users.';
$string['row'] = 'Row';
$string['usermails'] = 'Danh sách địa chỉ email';
$string['usermails_help'] = 'Để đăng ký một người dùng Moodle hiện có vào khóa học này, hãy thêm địa chỉ e-mail của anh ấy vào biểu mẫu này, một người dùng / địa chỉ e-mail trên mỗi dòng. <br /> <br /> Ví dụ: <br /> alice@example.com < br />bob@example.com <br /> <br /> Theo tùy chọn, bạn có thể tạo nhóm và thêm người dùng đã đăng ký vào nhóm. Tất cả những gì bạn phải làm là thêm một dòng tiêu đề có dấu thăng và tên của nhóm, tách danh sách người dùng. <br /> <br /> Ví dụ: <br /> # Nhóm 1 <br /> alice@example.com <br /> bob@example.com <br /> # Nhóm 2 <br /> carol@example.com <br /> dave@example.com';
$string['users_to_enrol_in_course'] = 'Các người dùng sẽ được gán vào khóa học';
$string['user_enroled'] = 'User enrolment';

$string['user_enroled_yes'] = 'Người dùng sẽ được ghi danh';
$string['user_enroled_already'] = 'Người dùng đã ghi danh rồi';
$string['user_suspended_already'] = 'Người dùng đã bị tạm dừng ghi danh';
$string['user_will_be_unenrolled'] = 'Người dùng sẽ được hủy ghi danh';
$string['user_notincourse_already'] = 'Người dùng hiện chưa được ghi danh vào khóa học này';
$string['user_will_be_suspend'] = 'Người dùng sẽ bị tạm dừng ghi danh';

$string['user_groups'] = 'Group membership';
$string['user_groups_yes'] = 'Người dùng sẽ được gán vào nhóm';
$string['user_groups_already'] = 'Người dùng đã được gán vào nhóm';
$string['parameter_empty'] = 'Parameter empty';
$string['type_enrol'] = 'Enrolment method';
$string['studentcode'] = 'Mã học viên';
$string['error_user_suspend_for_email'] = 'Tài khoản người dùng gắn với địa chỉ e-mail <em>{$a}</em> hiện tại đang bị suspended hoặc deleted.<br />Dòng này sẽ bị bỏ qua, sẽ không có tài khoản người dùng nào được tạo.';
$string['error_user_suspend_for_msv'] = 'Tài khoản người dùng gắn với mã sinh viên <em>{$a}</em> hiện tại đang bị suspended hoặc deleted.<br />Dòng này sẽ bị bỏ qua, sẽ không có tài khoản người dùng nào được tạo.';
$string['list_user_identifications'] = 'Danh sách định danh người dùng';
$string['identifiertype'] = 'Loại định danh';
$string['options'] = 'Tùy chọn:';
