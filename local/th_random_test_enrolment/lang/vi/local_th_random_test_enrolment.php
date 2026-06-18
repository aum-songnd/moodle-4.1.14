<?php

defined('MOODLE_INTERNAL') || die();


$string['enrol_users_successful'] = 'Ghi danh theo lô thành công';
$string['enrol_users'] = 'Ghi danh các người dùng';
$string['error_enrol_users'] = 'Đã xảy ra sự cố khi đăng ký người dùng vào khóa học.';
$string['error_enrol_user'] = 'Đã xảy ra sự cố khi đăng ký khóa học cho người dùng bằng e-mail <em>{$a->email}</em>.';
$string['error_exception_info'] = 'Thông tin ngoại lệ';
$string['error_getting_user_for_email'] = 'Đã xảy ra sự cố khi lấy bản ghi người dùng cho địa chỉ email <em>{$a}</em> từ cơ sở dữ liệu.';
$string['error_group_add_members'] = 'Đã xảy ra sự cố khi thêm người dùng vào (các) nhóm khóa học.';
$string['error_group_add_member'] = 'Đã xảy ra sự cố khi thêm người dùng có e-mail <em>{$a->email}</em> vào nhóm khóa học <em>{$a->group}</em>.';
$string['error_invalid_email'] = 'Địa chỉ email không hợp lệ ở dòng {$a->row} (<em>{$a->email}</em>). Dòng này sẽ được bỏ qua.';
$string['error_more_than_one_record_for_email'] = 'Đã tìm thấy nhiều hơn một tài khoản người dùng Moodle hiện có với địa chỉ email <em>{$a}</em>em>.<br /> Dòng này sẽ bị bỏ qua, không có người dùng Moodle hiện tại nào được đăng ký.';
$string['error_no_email'] = 'Không tìm thấy địa chỉ email nào trong dòng {$a->line} (<em>{$a->content}</em>). Dòng này sẽ bị bỏ qua.';

$string['error_usermails_empty'] = 'Danh sách địa chỉ e-mail trống. Vui lòng thêm ít nhất một địa chỉ e-mail.';
$string['error_check_is_already_member'] = 'Lỗi khi kiểm tra xem người dùng (<em>{$a->email}</em>) đã là thành viên của nhóm (<em>{$a->groupname}</em>). {$a->error}';

$string['privacy:metadata'] = 'Plugin đăng ký hàng loạt người dùng hoạt động như một công cụ để đăng ký người dùng vào các khóa học, nhưng không lưu trữ bất kỳ dữ liệu cá nhân nào.';

$string['usermails'] = 'Danh sách địa chỉ email';
$string['usermails_help'] = 'Để đăng ký một người dùng Moodle hiện có vào khóa học này, hãy thêm địa chỉ e-mail của anh ấy vào biểu mẫu này, một người dùng / địa chỉ e-mail trên mỗi dòng. <br /> <br /> Ví dụ: <br /> alice@example.com < br />bob@example.com <br /> <br /> Theo tùy chọn, bạn có thể tạo nhóm và thêm người dùng đã đăng ký vào nhóm. Tất cả những gì bạn phải làm là thêm một dòng tiêu đề có dấu thăng và tên của nhóm, tách danh sách người dùng. <br /> <br /> Ví dụ: <br /> # Nhóm 1 <br /> alice@example.com <br /> bob@example.com <br /> # Nhóm 2 <br /> carol@example.com <br /> dave@example.com';
$string['users_to_enrol_in_course'] = 'Các người dùng sẽ được gán vào khóa học';

$string['user_enroled_yes'] = 'Người dùng sẽ được ghi danh';
$string['user_enroled_already'] = 'Người dùng đã ghi danh rồi';

$string['user_groups_yes'] = 'Người dùng sẽ được gán vào nhóm';
$string['user_groups_already'] = 'Người dùng đã được gán vào nhóm';
$string['parameter_empty'] = 'Tham số trống';


$string['pluginname'] = 'TH Ghi danh và gán nhóm';
$string['th_random_test_enrolment:enrolusers'] = 'TH Ghi danh và gán vào nhóm';
$string['th_random_test_enrolment_form_intro'] = 'Tại đây, bạn có thể đăng ký hàng loạt người dùng vào khóa học của mình. Người dùng được đăng ký được xác định bằng địa chỉ e-mail được lưu trữ trong tài khoản Moodle của anh ta.';
$string['list_of_user_identifications'] = 'Danh sách nhận dạng người dùng';
$string['identifiers_type'] = 'Loại định danh';
$string['email'] = 'Thư điện tử';
$string['group'] = 'Nhóm';
$string['group_description'] = 'Tên Nhóm sẽ bắt đầu bằng Listening, Reading, Writing, Speaking. Ví dụ: Listening 1, Listening 2,...';
$string['enrolplugin'] = 'Phương thức đăng ký';
$string['enrolplugin_desc'] = 'Phương thức đăng ký được sử dụng để đăng ký hàng loạt người dùng. Nếu phương thức đăng ký được định cấu hình không hoạt động/được thêm vào trong khóa học khi người dùng đăng ký hàng loạt, thì phương thức này sẽ tự động được thêm/kích hoạt.';
$string['role'] = 'Vai trò';
$string['role_description'] = 'Vai trò được sử dụng để đăng ký hàng loạt người dùng.';
$string['hints'] = 'Gợi ý';
$string['row'] = 'Dòng';
$string['role_assigned'] = 'Vai trò';
$string['enrolinfo_headline'] = 'Chi tiết ghi danh';
$string['type_enrol'] = 'Phương thức ghi danh';
$string['user_enroled'] = 'Đăng ký người dùng';
$string['user_groups'] = 'Thành viên nhóm';
$string['studentcode'] = 'Mã sinh viên';
$string['error_no_valid_email_in_list'] = 'Địa chỉ email không hợp lệ.<br />Vui lòng <a href=\'{$a->url}\'>quay lại và kiểm tra đầu vào của bạn</a>.';
$string['error_no_record_found_for_email'] = 'Không có tài khoản người dùng hiện có với địa chỉ e-mail <em>{$a}</em>.<br />Dòng này sẽ bị bỏ qua, sẽ không có tài khoản người dùng Moodle được tạo.';
$string['groupinfos_headline'] = 'Các nhóm có trong danh sách';
$string['group_name_headline'] = 'Tên nhóm';
$string['group_status_headline'] = 'Trạng thái nhóm';
$string['group_status_create'] = 'Nhóm sẽ được tạo';
$string['group_status_exists'] = 'Nhóm đã tồn tại';