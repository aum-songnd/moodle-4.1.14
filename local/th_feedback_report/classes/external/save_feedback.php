<?php
namespace local_th_feedback_report\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use stdClass;
use moodle_url;

class save_feedback extends external_api {

    /**
     * Tham số đầu vào từ Client
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'description'  => new external_value(PARAM_TEXT, 'Nội dung phản hồi', VALUE_DEFAULT, ''),
                'url'          => new external_value(PARAM_URL, 'URL nơi xảy ra lỗi', VALUE_REQUIRED),
                'status'       => new external_value(PARAM_INT, 'Trạng thái', VALUE_DEFAULT, 0),
                'type'         => new external_value(PARAM_INT, 'Loại lỗi', VALUE_DEFAULT, 0),
                'os_name'      => new external_value(PARAM_TEXT, 'Hệ điều hành', VALUE_DEFAULT, null),
                'browser_name' => new external_value(PARAM_TEXT, 'Trình duyệt', VALUE_DEFAULT, null),
                'base64_image' => new external_value(PARAM_RAW, 'Chuỗi ảnh Base64', VALUE_DEFAULT, null) // thêm
            ])
        ]);
    }

    /**
     * Thực thi lưu dữ liệu
     */
    public static function execute($data) {
        global $DB, $USER, $SITE;

        // 1. Validate tham số
        $params = self::validate_parameters(self::execute_parameters(), ['data' => $data]);
        $feedbackdata = $params['data'];

        // 2. Validate Context & Quyền (Người dùng đã đăng nhập mới được gửi feedback)
        $context = \context_system::instance();
        self::validate_context($context);
        require_login();

        // 3. Chuẩn bị đối tượng để insert
        $record = new stdClass();
        $record->userid       = $USER->id;
        $record->description  = $feedbackdata['description'];
        $record->url          = $feedbackdata['url'];
        $record->status       = $feedbackdata['status'];
        $record->type         = $feedbackdata['type'];
        $record->os_name      = $feedbackdata['os_name'];
        $record->browser_name = $feedbackdata['browser_name'];
        $record->timecreated  = time();
        $record->timemodified = time();

        $id = $DB->insert_record('local_th_feedback_report', $record);
        
        // 2. XỬ LÝ LƯU ẢNH BASE64
        $file_url = '';

        if (!empty($feedbackdata['base64_image'])) {
            // Lọc bỏ phần header "data:image/png;base64," nếu App vô tình gửi kèm
            $base64_string = $feedbackdata['base64_image'];
            if (strpos($base64_string, ',') !== false) {
                $base64_parts = explode(',', $base64_string);
                $base64_string = $base64_parts[1];
            }

            $image_data = base64_decode($base64_string);
            $file_name = 'screenshot_' . $USER->id . '_' . time() . '.png';

            $fs = get_file_storage();
            $file_record = array(
                'contextid' => $context->id,
                'component' => 'local_th_feedback_report',
                'filearea'  => 'screenshot',
                'itemid'    => $id,
                'filepath'  => '/',
                'filename'  => $file_name,
                'timecreated'  => time(),
                'timemodified' => time(),
                'userid'    => $USER->id,
                'author'    => fullname($USER),
                'license'   => 'allrightsreserved'
            );

            $file = $fs->create_file_from_string($file_record, $image_data);

            $file_url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            )->out();
        }

        // GỬI EMAIL THÔNG BÁO CHO ADMIN
        try {
            $subject = get_string('title_log_feedback_report', 'local_th_feedback_report') . ' - ' . $SITE->shortname;

            // Xây dựng nội dung email
            $htmlmessage = "<div>" .
                "<div>" . get_string('sender', 'local_th_feedback_report') . ": " . fullname($USER) . "</div>" .
                "<div>" . get_string('email', 'local_th_feedback_report') . ": " . $USER->email . "</div>" .
                "<div>" . get_string('description', 'local_th_feedback_report') . ": " . $feedbackdata['description'] . "</div>" .
                "<div>" . get_string('url', 'local_th_feedback_report') . ": " . $feedbackdata['url'] . "</div>";

            if (!empty($file_url)) {
                $htmlmessage .= "<div>Ảnh đính kèm: <a href='" . $file_url . "'>Xem ảnh</a></div>";
            }
            $htmlmessage .= "</div>";

            $supportemail = get_config('local_th_feedback_report', 'email_address');

            if (empty($supportemail)) {
                $supportemail = array();
            } elseif (strpos($supportemail, ',') !== false) {
                $supportemail = explode(',', $supportemail);
            } else {
                $supportemail = array($supportemail);
            }

            global $DB;
            $noreply_user = \core_user::get_noreply_user(); // Khai báo 1 lần cho tối ưu

            foreach ($supportemail as $email) {
                $email = trim($email);
                $admin_user = $DB->get_record('user', array('email' => $email, 'deleted' => 0), '*', IGNORE_MULTIPLE);
                
                if ($admin_user) {
                    email_to_user($admin_user, $noreply_user, $subject, html_to_text($htmlmessage), $htmlmessage);
                }
            }
        } catch (\Exception $e) {
            error_log("Lỗi gửi mail báo cáo feedback: " . $e->getMessage());
        }

        return [
            'status' => true,
            'id'     => $id,
            'message' => 'Gửi báo cáo lỗi thành công!' 
        ];
    }

    /**
     * Cấu trúc kết quả trả về
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status'  => new external_value(PARAM_BOOL, 'Trạng thái thành công'),
            'id'      => new external_value(PARAM_INT, 'ID của record vừa tạo'),
            'message' => new external_value(PARAM_TEXT, 'Thông báo')
        ]);
    }
}