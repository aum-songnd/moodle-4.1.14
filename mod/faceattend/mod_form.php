<?php
/**
 * mod_form.php — Form tạo/chỉnh sửa activity
 *
 * Đây là form giảng viên thấy khi click "Add activity → Face Attendance"
 * hoặc khi chỉnh sửa activity đã có.
 * Moodle tự động load file này và render form.
 */
defined('MOODLE_INTERNAL') || die();

// Kế thừa class form chuẩn của Moodle
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_faceattend_mod_form extends moodleform_mod {

    /**
     * definition() — Khai báo các trường trong form
     * Moodle gọi hàm này để build form tự động
     */
    public function definition() {
        $mform = $this->_form; // Đây là object MoodleQuickForm

        // ── SECTION 1: Thông tin cơ bản ──────────────────────────
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Tên activity (bắt buộc)
        $mform->addElement(
            'text',
            'name',
            get_string('faceattendname', 'faceattend'),
            ['size' => '64', 'maxlength' => '255']
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addHelpButton('name', 'faceattendname', 'faceattend');

        // Phần mô tả (intro editor chuẩn của Moodle)
        $this->standard_intro_elements();

        // ── SECTION 2: Cài đặt buổi điểm danh ───────────────────
        $mform->addElement('header', 'session_settings', 'Cài đặt buổi điểm danh');
        $mform->setExpanded('session_settings', true);

        // Ngày/giờ buổi học
        $mform->addElement(
            'date_time_selector',
            'session_date',
            get_string('session_date', 'faceattend')
        );
        $mform->addHelpButton('session_date', 'session_date', 'faceattend');
        $mform->setDefault('session_date', time()); // Mặc định: ngay bây giờ

        // Ngưỡng confidence (%)
        $mform->addElement(
            'text',
            'confidence_threshold',
            get_string('confidence_threshold', 'faceattend'),
            ['size' => '5']
        );
        $mform->setType('confidence_threshold', PARAM_FLOAT);
        $mform->addRule('confidence_threshold', null, 'required', null, 'client');
        $mform->addRule('confidence_threshold', null, 'numeric', null, 'client');
        $mform->addHelpButton('confidence_threshold', 'confidence_threshold', 'faceattend');
        $mform->setDefault('confidence_threshold', 80.00);

        // Số lần thử tối đa
        $mform->addElement(
            'select',
            'max_attempts',
            get_string('max_attempts', 'faceattend'),
            [1 => '1 lần', 2 => '2 lần', 3 => '3 lần', 5 => '5 lần']
        );
        $mform->addHelpButton('max_attempts', 'max_attempts', 'faceattend');
        $mform->setDefault('max_attempts', 3);

        // ── SECTION 3: Cài đặt API ────────────────────────────────
        $mform->addElement('header', 'api_settings', 'Cài đặt Face Recognition API');

        // URL của Face API server
        $mform->addElement(
            'text',
            'api_url',
            get_string('api_url', 'faceattend'),
            ['size' => '64']
        );
        $mform->setType('api_url', PARAM_URL);
        $mform->addHelpButton('api_url', 'api_url', 'faceattend');
        $mform->setDefault('api_url', 'http://localhost:8000');

        // Trạng thái mở/đóng điểm danh
        $mform->addElement(
            'advcheckbox',
            'is_open',
            'Trạng thái',
            'Cho phép sinh viên điểm danh'
        );
        $mform->setDefault('is_open', 1);

        // ── Các cài đặt chuẩn của Moodle (grade, completion, v.v.) ──
        $this->standard_coursemodule_elements();

        // ── Nút submit ──
        $this->add_action_buttons();
    }

    /**
     * validation() — Kiểm tra dữ liệu form phía server
     * Chạy sau khi client submit, trước khi lưu vào DB
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Kiểm tra confidence_threshold trong khoảng hợp lệ
        if (isset($data['confidence_threshold'])) {
            $val = (float) $data['confidence_threshold'];
            if ($val < 50 || $val > 100) {
                $errors['confidence_threshold'] = 'Ngưỡng phải từ 50% đến 100%';
            }
        }

        // Kiểm tra api_url có định dạng hợp lệ
        if (!empty($data['api_url'])) {
            if (!filter_var($data['api_url'], FILTER_VALIDATE_URL)) {
                $errors['api_url'] = 'URL không hợp lệ. Ví dụ: http://localhost:8000';
            }
        }

        // Kiểm tra session_date không ở quá khứ quá xa (hơn 7 ngày)
        if (!empty($data['session_date'])) {
            if ($data['session_date'] < strtotime('-7 days')) {
                $errors['session_date'] = 'Ngày buổi học không được quá 7 ngày trong quá khứ';
            }
        }

        return $errors;
    }
}
