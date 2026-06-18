<?php

require_once '../../config.php';

global $DB, $CFG, $COURSE, $USER, $SITE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_feedback_report', $COURSE->id);
}
require_login($COURSE->id);

// Lấy dữ liệu từ form
$currentUrl = $_POST['currentUrl'];
$issueDescription = strip_tags(format_text($_POST['issueDescription']));

$data = new stdClass();
$data->userid = $USER->id;
$data->description = $issueDescription;
$data->url = $currentUrl;
$data->timecreated = time();
$data->status = 0;
$data->browser_name = $_POST['browserName'];
$data->os_name = $_POST['osName'];

$type1 = isset($_POST['supportType']) ? $_POST['supportType'] : null;
$type2 = isset($_POST['subcate_' . $type1]) ? $_POST['subcate_' . $type1] : null;

$data->type = $type2 ? $type2 : $type1;
// $data->code = $SITE->shortname;

$error_reporter_id = $DB->insert_record('local_th_feedback_report', $data, true);

if($error_reporter_id){
    $base64_image = $_POST['screenshotData'];
    // Tách phần dữ liệu base64
    $data = explode(',', $base64_image);

    // Lấy dữ liệu base64
    $image_data = base64_decode($data[1]);

    // Tên file
    $file_name = 'screenshot_' . $USER->id  . '_' . time() . '.png'; // Đổi tên file tùy theo nhu cầu

    // Tạo bản ghi tệp cho kho lưu trữ của Moodle
    $fs = get_file_storage();
    $context = context_system::instance();
    $file_record = array(
        'contextid' => $context->id,
        'component' => 'local_th_feedback_report', // Thay đổi 'local_yourcomponent' thành component của bạn
        'filearea' => 'screenshot', // Thay đổi 'yourfilearea' thành filearea của bạn
        'itemid' => $error_reporter_id, // Bạn có thể thay đổi itemid nếu cần
        'filepath' => '/',
        'filename' => $file_name,
        'timecreated' => time(),
        'timemodified' => time(),
        'userid' => $USER->id,
        'author' => fullname($USER),
        'license' => 'allrightsreserved'
    );

    // Lưu file vào kho lưu trữ của Moodle
    $file = $fs->create_file_from_string($file_record, $image_data);

    $file_url = moodle_url::make_pluginfile_url(
        $file->get_contextid(), 
        $file->get_component(), 
        $file->get_filearea(), 
        $file->get_itemid(), 
        $file->get_filepath(), 
        $file->get_filename()
    )->out();

    $subject = get_string('title_log_feedback_report', 'local_th_feedback_report') . ' ' . $SITE->shortname;

    $htmlmessage = "<div>
                        <div>" . get_string('sender', 'local_th_feedback_report') . ": {$USER->firstname} {$USER->lastname}</div>
                        <div>" . get_string('email', 'local_th_feedback_report') . ": {$USER->email}</div>
                        <div>" . get_string('description', 'local_th_feedback_report') . ": {$issueDescription}</div>
                        <div>" . get_string('url', 'local_th_feedback_report') . ": {$currentUrl}</div>
                        <div>" . get_string('attached_image', 'local_th_feedback_report') . ": {$file_url}</div>
                    </div>";
                    
    $supportemail = get_config('local_th_feedback_report', 'email_address');
    if (empty($supportemail)) {
        $supportemail = array();
    } elseif (strpos($supportemail, ',') !== false) {
        $supportemail = explode(',', $supportemail);
    } else {
        $supportemail = array($supportemail);
    }

    foreach ($supportemail as $email) {
        $user = \core_user::get_user_by_email($email);
        if ($user) {
            email_to_user($user, $USER, $subject, html_to_text($htmlmessage), $htmlmessage, '', '', true);
        }
    }
    
    redirect($currentUrl, get_string('notification_success', 'local_th_feedback_report'), null, \core\output\notification::NOTIFY_SUCCESS);
    
} else {
    redirect($currentUrl, get_string('notification_success', 'local_th_feedback_report'), null, \core\output\notification::NOTIFY_WARNING);
}




