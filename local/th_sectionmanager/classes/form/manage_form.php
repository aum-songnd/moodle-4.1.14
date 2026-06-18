<?php
namespace local_th_sectionmanager\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class manage_form extends \moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
        
        $customdata = $this->_customdata;
        $courseid = $customdata['courseid'];

        // 1. Kiểm tra xem khóa học này đã có dữ liệu trong bảng map hay chưa
        // Nếu $courseid đã tồn tại trong bảng local_th_sectionmanager_map thì đây không phải lần đầu
        $has_existing_data = $DB->record_exists('local_th_sectionmanager_map', ['courseid' => $courseid]);
        
        $sections = $DB->get_records_select('course_sections', 'course = ? AND section > 0', [$courseid], 'section ASC');
        $section_options = [];
        foreach ($sections as $section) {
            $section_options[$section->id] = get_section_name($courseid, $section);
        }

        $all_section_ids = []; // Mảng lưu toàn bộ ID section để dùng cho việc "chọn hết"
        foreach ($sections as $section) {
            $section_options[$section->id] = get_section_name($courseid, $section);
            $all_section_ids[] = $section->id; 
        }

        $default_shortcode = get_config('local_th_sectionmanager', 'default_shortcode');
        if (empty($default_shortcode)) {
            $default_shortcode = 'LS1';
        }

        $types_db = $DB->get_records('local_th_sectionmanager_type', null, 'id ASC');

        $mform->addElement('header', 'mainheader', get_string('manage_title', 'local_th_sectionmanager'));
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        foreach ($types_db as $type) {
            $element_name = 'type_' . $type->id;

            $mform->addElement('autocomplete', $element_name, $type->name, $section_options, [
                'multiple' => true,
                'noselectionstring' => get_string('choose_type', 'local_th_sectionmanager'), 
                'placeholder' => get_string('search', 'core'), 
            ]);

            if (!$has_existing_data && !empty($type->shortname) && $type->shortname === $default_shortcode) {
                // Đặt giá trị mặc định cho ô autocomplete này là TẤT CẢ các ID section
                $mform->setDefault($element_name, $all_section_ids);
            }
        }

        $this->add_action_buttons(true, get_string('submit'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        $selected_sections = [];
        foreach ($data as $key => $values) {
            if (strpos($key, 'type_') === 0 && !empty($values) && is_array($values)) {
                foreach ($values as $sectionid) {
                    if (in_array($sectionid, $selected_sections)) {
                        $errors[$key] = get_string('error_duplicate_section', 'local_th_sectionmanager');
                        break; 
                    }
                    $selected_sections[] = $sectionid;
                }
                
            }
        }
        return $errors;
    }
}