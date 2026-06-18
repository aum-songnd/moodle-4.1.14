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
 * Strings for component 'thurl', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_thurl
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Click {$a} link to open resource.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Content';
$string['createurl'] = 'Create a THURL';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the URL file type and whether the browser allows embedding, determines how the URL is displayed. Options may include:

* Automatic - The best display option for the URL is selected automatically
* Embed - The URL is displayed within the page below the navigation bar together with the URL description and any blocks
* Open - Only the URL is displayed in the browser window
* In pop-up - The URL is displayed in a new browser window without menus or an address bar
* In frame - The URL is displayed within a frame below the navigation bar and URL description
* New window - The URL is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all URLs.';
$string['externalurl'] = 'External THURL';
$string['framesize'] = 'Frame height';
$string['invalidstoredurl'] = 'Cannot display this resource, URL is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['indicator:cognitivedepth'] = 'URL cognitive';
$string['indicator:cognitivedepth_help'] = 'This indicator is based on the cognitive depth reached by the student in a URL resource.';
$string['indicator:cognitivedepthdef'] = 'THURL cognitive';
$string['indicator:cognitivedepthdef_help'] = 'The participant has reached this percentage of the cognitive engagement offered by the URL resources during this analysis interval (Levels = No view, View)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'THURL social';
$string['indicator:socialbreadth_help'] = 'This indicator is based on the social breadth reached by the student in a URL resource.';
$string['indicator:socialbreadthdef'] = 'THURL social';
$string['indicator:socialbreadthdef_help'] = 'The participant has reached this percentage of the social engagement offered by the URL resources during this analysis interval (Levels = No participation, Participant alone)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['invalidurl'] = 'Entered URL is invalid';
$string['modulename'] = 'THURL';
$string['modulename_help'] = 'The URL module enables a teacher to provide a web link as a course resource. Anything that is freely available online, such as documents or images, can be linked to; the URL doesn’t have to be the home page of a website. The URL of a particular web page may be copied and pasted or a teacher can use the file picker and choose a link from a repository such as Flickr, YouTube or Wikimedia (depending upon which repositories are enabled for the site).

There are a number of display options for the URL, such as embedded or opening in a new window and advanced options for passing information, such as a student\'s name, to the URL if required.

Note that URLs can also be added to any other resource or activity type through the text editor.';
$string['modulename_link'] = 'mod/thurl/view';
$string['modulenameplural'] = 'THURLs';
$string['page-mod-url-x'] = 'Any THURL module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'THURL variables';
$string['parametersheader_help'] = 'This section allows you to pass internal information as part of the URL. This is useful if the URL is an interactive web page that takes parameters, and you want to pass something like the name of the current user, for example. Enter the name of the URL\'s parameter in the text box then select the corresponding site variable.';
$string['pluginadministration'] = 'THURL module administration';
$string['pluginname'] = 'THURL';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printintro'] = 'Display URL description';
$string['printintroexplain'] = 'Display URL description below content? Some display types may not display description even if enabled.';
$string['privacy:metadata'] = 'The URL resource plugin does not store any personal data.';
$string['rolesinparams'] = 'Include role names in parameters';
$string['search:activity'] = 'THURL';
$string['serverurl'] = 'Server THURL';
$string['url:addinstance'] = 'Add a new THURL resource';
$string['url:view'] = 'View THURL';

//

$string['enable_taskschedule'] = 'Bật/tắt tính năng';
$string['enable_taskschedule_desc'] = 'Bật/tắt tính năng';

$string['taskschedule'] = 'Thời gian nhắc học viên truy cập lớp học trực tuyến';
$string['scheduled_time'] = 'Thời gian chạy task';
$string['scheduled_time_desc'] = 'Chọn thời gian mà task sẽ được chạy mỗi ngày.';
$string['scheduled_hour'] = 'Giờ';
$string['scheduled_minute'] = 'Phút';


$string['taskstimerun'] = 'Thời gian chạy task';
$string['task_hour'] = 'Giờ';
$string['task_minute'] = 'Phút';


$string['notificationmessage'] = 'Anh/Chị học viên thân mến,
Anh/Chị học viên sẽ có Buổi học trực tuyến cùng Giảng viên bộ môn: {$a->course}.
- Thời gian bắt đầu lớp học: {$a->time_30} ngày {$a->date}
- Nội dung: Tổng hợp kiến thức môn học và giải đáp thắc mắc của học viên
- Link zoom: {$a->url}  
- Thời gian mở link học: {$a->time} - anh/chị chủ động vào môn học sẽ thấy thông tin.
Anh/chị học viên sắp xếp thời gian tham gia học tập để được trao đổi trực tiếp cùng Giảng viên, giải đáp thắc mắc, ôn luyện trước kỳ thi kết thúc môn.
Chúc Anh/Chị học viên học tập tốt!';
$string['no_online_class'] = 'Không có khóa học nào có lớp học trực tuyến.';
$string['task_turned_off'] = 'Tác vụ đã bị tắt. Thoát...';
$string['task_started'] = 'Tác vụ đã bắt đầu';
$string['eteaching_class_notification'] = 'THÔNG BÁO LỚP HỌC E-TEACHING';
$string['role_not_found_student'] = 'Không tìm thấy role là student';
$string['fullmessage_with_url'] = '{$a->content} Vui lòng truy cập tại: {$a->url}';
$string['fullmessagehtml_with_url'] = '<p>{$a->content} <br>Vui lòng truy cập tại: <a href="{$a->url}">lớp học trực tuyến</a></p>';
$string['smallmessage_online_class'] = 'Bạn có thông báo mới từ lớp học trực tuyến.';
$string['contexturlname_online_class'] = 'Lớp học trực tuyến';
$string['notification_sent'] = 'Thông báo đã gửi đến {$a}';
$string['notification_failed'] = 'Không thể gửi thông báo đến {$a}';
$string['email_sent'] = 'Email đã được gửi đến {$a}';
$string['email_send_failed'] = 'Không thể gửi email đến {$a}';
$string['remind_student_online_class'] = 'Nhắc học viên truy cập lớp học trực tuyến';
$string['message_not_sent'] = 'Tin nhắn không được gửi vào cuộc trò chuyện. Vui lòng kiểm tra dữ liệu sự kiện.';
$string['conversation_not_found'] = 'Cuộc trò chuyện không có thành viên hoặc không tồn tại.';
$string['attempt_send_internal_user'] = 'Cố gắng gửi tin nhắn đến người dùng nội bộ (noreply).';
$string['could_not_load_preference'] = 'Không thể tải tùy chọn {$a}. Hãy chắc chắn rằng thành phần và tên bạn cung cấp cho message_send() là hợp lệ.';
$string['force_message_delivery_unconfigured'] = 'Cố gắng gửi tin nhắn đến người dùng có đầu ra "{$a}" chưa được cấu hình.';
$string['error_calling_message_processor'] = 'Lỗi khi gọi bộ xử lý tin nhắn: {$a}';
$string['attempt_send_msg_unknown_user'] = 'Cố gắng gửi tin nhắn từ người dùng không xác định';
$string['attempt_send_msg_inactive_provider'] = 'Cố gắng gửi tin nhắn từ nhà cung cấp {$a->component}/{$a->name}, nhưng không hoạt động hoặc không được phép cho người dùng id={$a->userid}';
$string['missing_properties_userto'] = 'Thiếu thuộc tính cần thiết trong đối tượng userto, đang tìm nạp bản ghi đầy đủ';
$string['attempt_send_msg_from_inactive_provider'] = 'Cố gắng gửi tin nhắn từ nhà cung cấp {$a->component}/{$a->name} không hoạt động hoặc không được phép cho người dùng id={$a->userid}';
$string['necessary_properties_missing'] = 'Thiếu thuộc tính cần thiết trong đối tượng userto, đang lấy bản ghi đầy đủ';
$string['error_load_preference'] = 'Không thể tải tùy chọn {$a}. Hãy đảm bảo rằng thành phần và tên bạn cung cấp cho message_send() là hợp lệ.';
$string['attempt_force_msg_unconfigured'] = 'Cố gắng gửi tin nhắn cho người dùng nhưng đầu ra "{$a}" chưa được cấu hình';
$string['invalid_component_message_send'] = 'Thành phần không hợp lệ được chỉ định trong message-send(): {$a}';
$string['missing_messages_file'] = '{$a} không chứa db/messages.php cần thiết cho message_send()';
$string['missing_messaging_defaults'] = 'Thiếu thiết lập mặc định cho tin nhắn của sự kiện "{$a->name}" trong tệp "{$a->component}/messages.php".';
$string['invalid_mod_thurl_id'] = "Lỗi ngoại lệ do ID instance của mod_thurl không hợp lệ.";
$string['exception_not_enrolled'] = "Lỗi ngoại lệ do người dùng chưa được ghi danh.";
$string['exception_missing_capability'] = "Lỗi ngoại lệ do thiếu quyền hạn.";
$string['field_name'] = 'Tên';
$string['field_url'] = 'URL';
$string['error_empty_name'] = 'Không được bỏ trống tên';
$string['error_empty_url'] = 'Không được bỏ trống URL';
$string['field_desc'] = 'Mô tả';
$string['field_display'] = 'Hiển thị mô tả ở trang khoá học';
$string['time_open'] = 'Cho phép truy cập từ:';
$string['time_close'] = 'Không phép truy cập từ:';
$string['han_che'] = 'Hạn chế truy cập';
$string['han_che_time'] = 'Thời gian không cho phép truy cập không được nhỏ hơn thời gian cho phép truy cập.';
$string['editurl'] = 'Chỉnh sửa URL';
$string['url_not_found'] = 'Không tìm thấy URL muốn sửa';
$string['url_edit_success'] = 'Chỉnh sửa URL thành công!';
$string['url_edit_failed_missing_param'] = 'Chỉnh sửa URL thất bại vì thiếu tham số trên đường dẫn!';
