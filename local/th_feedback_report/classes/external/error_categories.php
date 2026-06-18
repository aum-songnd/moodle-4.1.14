<?php
namespace local_th_feedback_report\external; // Thay local_th_feedback_report bằng frankenstyle của bạn

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

class error_categories extends external_api {

    /**
     * Tham số đầu vào (ví dụ lọc theo parentid)
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'parentid' => new external_value(PARAM_INT, 'Lọc theo ID cha', VALUE_DEFAULT, null)
        ]);
    }

    /**
     * Xử lý logic lấy dữ liệu
     */
    public static function execute($parentid) {
        global $DB;

        // Validate tham số
        $params = self::validate_parameters(self::execute_parameters(), ['parentid' => $parentid]);

        // Kiểm tra quyền (Context hệ thống hoặc khóa học tùy bạn)
        $context = \context_system::instance();
        self::validate_context($context);

        $conditions = [];
        if ($params['parentid'] !== null) {
            $conditions['parentid'] = $params['parentid'];
        }

        // Lấy dữ liệu từ bảng th_error_categories
        $records = $DB->get_records('th_error_categories', $conditions, 'id ASC');

        $result = [];
        foreach ($records as $record) {
            $result[] = [
                'id'          => $record->id,
                'parentid'    => $record->parentid,
                'name'        => $record->name,
                'description' => $record->description,
                'code'        => $record->code,
            ];
        }

        return $result;
    }

    /**
     * Định nghĩa cấu trúc trả về (Output)
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id'          => new external_value(PARAM_INT, 'ID'),
                'parentid'    => new external_value(PARAM_INT, 'Parent ID', VALUE_OPTIONAL),
                'name'        => new external_value(PARAM_TEXT, 'Category name'),
                'description' => new external_value(PARAM_RAW, 'Description'),
                'code'        => new external_value(PARAM_ALPHANUMEXT, 'Error code')
            ])
        );
    }
}