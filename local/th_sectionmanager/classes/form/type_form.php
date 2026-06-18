<?php
namespace local_th_sectionmanager\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class type_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        // Thêm thẻ header để tạo khung đóng mở chứa các input
        $mform->addElement('header', 'mainheader', get_string('section_type_title', 'local_th_sectionmanager'));
        
        // Lấy cờ editid truyền từ ngoài vào
        $editid = $this->_customdata['editid'] ?? 0;

        // [FIX LỖI 1]: Trực tiếp gán $editid làm giá trị mặc định cho thẻ hidden
        $mform->addElement('hidden', 'id', $editid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('type_name', 'local_th_sectionmanager'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('shortname', 'local_th_sectionmanager'));
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');

        $description_string = get_string('app_section_desc_help', 'local_th_sectionmanager');
        $mform->addElement('static', 'app_section_description', '', $description_string);

        if ($editid > 0) {
            $this->add_action_buttons(true, get_string('update_changes', 'local_th_sectionmanager'));
        } else {
            $this->add_action_buttons(false, get_string('add_type','local_th_sectionmanager'));
        }
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        // Kiểm tra xem Tên đã tồn tại chưa (loại trừ chính nó đang sửa)
        if ($DB->record_exists_select('local_th_sectionmanager_type', 'name = ? AND id != ?', [$data['name'], $id])) {
            $errors['name'] = get_string('msg_name_exists', 'local_th_sectionmanager');;
        }

        // Kiểm tra xem Mã viết tắt đã tồn tại chưa (loại trừ chính nó)
        if ($DB->record_exists_select('local_th_sectionmanager_type', 'shortname = ? AND id != ?', [$data['shortname'], $id])) {
            $errors['shortname'] = get_string('msg_shortname_exists', 'local_th_sectionmanager');;
        }

        return $errors;
    }
}