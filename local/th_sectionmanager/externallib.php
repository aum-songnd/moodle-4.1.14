<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/externallib.php');

class local_th_sectionmanager_external extends external_api {

    
    public static function get_course_contents_parameters() {
        return core_course_external::get_course_contents_parameters();
    }
    
    public static function get_course_contents($courseid, $options = array()) {
        global $DB;

        // 1. Validate tham số đầu vào theo chuẩn Moodle
        $params = self::validate_parameters(self::get_course_contents_parameters(),
            array('courseid' => $courseid, 'options' => $options));

        // 2. Gọi hàm lõi của Moodle để lấy toàn bộ dữ liệu khóa học
        $contents = core_course_external::get_course_contents($params['courseid'], $params['options']);
        
        // Chạy qua màng lọc của Moodle để chuẩn hóa dữ liệu
        $contents = external_api::clean_returnvalue(core_course_external::get_course_contents_returns(), $contents);

        foreach ($contents as $key => $section) {
            $contents[$key]['section_type'] = '';
            
            if (!empty($section['id'])) {
                $sql = "SELECT t.shortname 
                        FROM {local_th_sectionmanager_map} m 
                        JOIN {local_th_sectionmanager_type} t ON m.typeid = t.id 
                        WHERE m.sectionid = ?";
                $type = $DB->get_record_sql($sql, array($section['id']));
                
                if ($type) {
                    $contents[$key]['section_type'] = $type->shortname;
                }
            }
        }

        return $contents;
    }
    
    public static function get_course_contents_returns() {        
        $core_returns = core_course_external::get_course_contents_returns();    
        $single_structure = $core_returns->content;
        $core_keys = $single_structure->keys;
        $core_keys['section_type'] = new external_value(PARAM_TEXT, 'Section Typename', VALUE_OPTIONAL);

        return new external_multiple_structure(
            new external_single_structure($core_keys)
        );
    }
}