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
$string['no_online_class'] = 'No courses have online classes.';
$string['task_turned_off'] = 'Task is turned off. Exiting...';
$string['task_started'] = 'Task started';
$string['eteaching_class_notification'] = 'E-Teaching Class Notification';
$string['role_not_found_student'] = 'Role "student" not found';
$string['fullmessage_with_url'] = '{$a->content} Please visit: {$a->url}';
$string['fullmessagehtml_with_url'] = '<p>{$a->content} <br>Please visit: <a href="{$a->url}">online class</a></p>';
$string['smallmessage_online_class'] = 'You have a new notification from the online class.';
$string['contexturlname_online_class'] = 'Online Class';
$string['notification_sent'] = 'Notification sent to {$a}';
$string['notification_failed'] = 'Failed to send notification to {$a}';
$string['email_sent'] = 'Email sent to {$a}';
$string['email_send_failed'] = 'Failed to send email to {$a}';
$string['remind_student_online_class'] = 'Remind students to access the online class';
$string['message_not_sent'] = 'Message is not being sent to a conversation. Please check event data.';
$string['conversation_not_found'] = 'Conversation has no members or does not exist.';
$string['attempt_send_internal_user'] = 'Attempt to send a message to an internal (noreply) user.';
$string['could_not_load_preference'] = 'Could not load preference {$a}. Make sure the component and name you supplied to message_send() are valid.';
$string['force_message_delivery_unconfigured'] = 'Attempt to force message delivery to user who has "{$a}" output unconfigured.';
$string['error_calling_message_processor'] = 'Error calling message processor: {$a}';
$string['attempt_send_msg_unknown_user'] = 'Attempt to send message from unknown user';
$string['attempt_send_msg_inactive_provider'] = 'Attempt to send msg from a provider {$a->component}/{$a->name} that is inactive or not allowed for the user id={$a->userid}';
$string['missing_properties_userto'] = 'Necessary properties missing in userto object, fetching full record';
$string['attempt_send_msg_from_inactive_provider'] = 'Attempt to send message from a provider {$a->component}/{$a->name} that is inactive or not allowed for the user id={$a->userid}';
$string['necessary_properties_missing'] = 'Necessary properties missing in userto object, fetching full record';
$string['error_load_preference'] = 'Could not load preference {$a}. Make sure the component and name you supplied to message_send() are valid.';
$string['attempt_force_msg_unconfigured'] = 'Attempt to force message delivery to user who has "{$a}" output unconfigured';
$string['invalid_component_message_send'] = 'Invalid component specified in message-send(): {$a}';
$string['missing_messages_file'] = '{$a} does not contain db/messages.php necessary for message_send()';
$string['missing_messaging_defaults'] = 'Missing messaging defaults for event "{$a->name} in "{$a->component}" messages.php file';
$string['invalid_mod_thurl_id'] = "Exception expected due to invalid mod_thurl instance id.";
$string['exception_not_enrolled'] = "Exception expected due to not enrolled user.";
$string['exception_missing_capability'] = "Exception expected due to missing capability.";
$string['field_name'] = 'Name';
$string['field_url'] = 'URL';
$string['error_empty_name'] = 'Name cannot be empty';
$string['error_empty_url'] = 'URL cannot be empty';
$string['field_desc'] = 'Description';
$string['field_display'] = 'Display description on course page';
$string['time_open'] = 'Allow access from:';
$string['time_close'] = 'Deny access from:';
$string['han_che'] = 'Access restriction';
$string['han_che_time'] = 'Restricted access time cannot be earlier than the allowed access time.';
$string['editurl'] = 'Edit URL';
$string['url_not_found'] = 'URL not found for editing';
$string['url_edit_success'] = 'URL edited successfully!';
$string['url_edit_failed_missing_param'] = 'URL edit failed due to missing parameters in the URL!';
