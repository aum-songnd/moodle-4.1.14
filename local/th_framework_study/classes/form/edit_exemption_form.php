<?php
namespace local_th_framework_study\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class edit_exemption_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // ID ẩn để phục vụ chỉnh sửa.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Mã học viên (idnumber).
        $mform->addElement('text', 'student_code', get_string('studentcode', 'local_th_framework_study'));
        $mform->setType('student_code', PARAM_RAW_TRIMMED);
        $mform->addRule('student_code', null, 'required', null, 'client');

        // Mã môn học được miễn.
        $mform->addElement('text', 'subject_code', get_string('subjectcode', 'local_th_framework_study'));
        $mform->setType('subject_code', PARAM_RAW_TRIMMED);
        $mform->addRule('subject_code', null, 'required', null, 'client');

        // Nút lưu.
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validate form input
     */ 
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
    
        $student_code = trim($data['student_code']);
        $subject_code = trim($data['subject_code']);
    
        // Lấy danh sách shortname từ setting của plugin local_thlib
        $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname'); // vd: "th_student_code,th_student_code2"
        $ma_hoc_vien_list = array_map('trim', explode(',', $setting_ma_hoc_vien)); // ['th_student_code', 'th_student_code2']
    
        $userid = null;
        $major_code = null;

        foreach ($ma_hoc_vien_list as $fieldshortname) {
            if (!$fieldshortname) {
                continue;
            }

            // Lấy user_info_data
            $record = $DB->get_record_sql("
                SELECT d.userid, f.categoryid
                  FROM {user_info_data} d
                  JOIN {user_info_field} f ON f.id = d.fieldid
                 WHERE f.shortname = :fieldshortname
                   AND d.data = :studentcode
                 LIMIT 1
            ", ['fieldshortname' => $fieldshortname, 'studentcode' => $student_code]);

            if ($record) {
                $userid = $record->userid;
                // Lấy categoryid -> chính là major_code
                $major_code = $record->categoryid;
                break;
            }
        }

        if (!$userid) {
            $errors['student_code'] = get_string('error_student_code_notfound', 'local_th_framework_study');
            return $errors;
        }

        // Kiểm tra mã môn học có tồn tại trong bảng th_framework_study không
        $subject_exists = $DB->record_exists('th_framework_study', ['subject_code' => $subject_code]);
        if (!$subject_exists) {
            $errors['subject_code'] = get_string('error_subject_notfound', 'local_th_framework_study');
        }

        // Kiểm tra trùng trong bảng th_subject_exemption
        if (empty($errors)) {
            $exists = $DB->record_exists('th_subject_exemption', [
                'userid' => $userid,
                'subject_code' => $subject_code,
                'major_code' => $major_code  // kiểm tra trùng theo cả ngành học
            ]);

            if ($exists) {
                $errors['subject_code'] = get_string('error_exemption_exists', 'local_th_framework_study');
            }
        }

        // Lưu userid và major_code vào dữ liệu để controller có thể insert
        $data['userid'] = $userid;
        $data['major_code'] = $major_code;
        $this->_form->_submitValues['userid'] = $userid;
        $this->_form->_submitValues['major_code'] = $major_code;

        return $errors;
    }
}
