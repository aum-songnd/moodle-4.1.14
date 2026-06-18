<?php
namespace local_th_framework_study\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use external_api;
use stdClass;

class create_update_framework extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'slot'        => new external_value(PARAM_INT,  'Thứ tự học phần'),
                    'subject_name'=> new external_value(PARAM_TEXT, 'Tên học phần'),
                    'subject_code'=> new external_value(PARAM_TEXT, 'Mã học phần'),
                    'credit'      => new external_value(PARAM_INT,  'Số tín chỉ'),
                    'major_code'  => new external_value(PARAM_TEXT, 'Mã ngành'),
                    'target_code' => new external_value(PARAM_TEXT, 'Mã đối tượng')
                ])
            )
        ]);
    }
    public static function execute($data) {
        global $DB, $USER;
    
        $params = self::validate_parameters(self::execute_parameters(), ['data' => $data]);
        $results = [];
    
        foreach ($params['data'] as $item) {
            $slot         = $item['slot'];
            $subject_name = trim($item['subject_name']);
            $subject_code = trim($item['subject_code']);
            $credit       = $item['credit'];
            $major_code   = trim($item['major_code']);
            $target_code  = trim($item['target_code']);
    
            $errors = [];
    
            // Validation
            if ($subject_code === '') { $errors[] = "Subject code is required"; }
            if ($subject_name === '') { $errors[] = "Subject name is required"; }
            if ($slot < 0) { $errors[] = "Slot must be non-negative"; }
            if ($credit < 0) { $errors[] = "Credit must be non-negative"; }
    
            if (!empty($errors)) {
                $results[] = [
                    'subject_code' => $subject_code,
                    'major_code'   => $major_code,
                    'target_code'  => $target_code,
                    'status'       => 'failure',
                    'message'      => implode("; ", $errors)
                ];
                continue;
            }
    
            //Tìm bản ghi theo 3 khóa
            $existing = $DB->get_record('th_framework_study', [
                'subject_code' => $subject_code,
                'major_code'   => $major_code,
                'target_code'  => $target_code
            ]);
    
            // Chuẩn bị object dữ liệu
            $record = new stdClass();
            $record->slot         = $slot;
            $record->subject_name = $subject_name;
            $record->subject_code = $subject_code;
            $record->credit       = $credit;
            $record->major_code   = $major_code;
            $record->target_code  = $target_code;
    
            // INSERT: Nếu không tìm thấy bản ghi trùng 3 khóa
            if (!$existing) {
                $record->timecreated  = time();
                $record->timemodified = 0;
                $record->created_by   = $USER->id;
    
                $DB->insert_record('th_framework_study', $record);
    
                $results[] = [
                    'subject_code' => $subject_code,
                    'major_code'   => $major_code,
                    'target_code'  => $target_code,
                    'status'       => 'success',
                    'message'      => 'Inserted'
                ];
                continue;
            }

            //ĐÃ TỒN TẠI → kiểm tra thay đổi
            $changed =
                (int)$existing->slot         !== (int)$slot ||
                trim($existing->subject_name) !== $subject_name ||
                (int)$existing->credit       !== (int)$credit;
    
            // Không thay đổi thì bor qua
            if (!$changed) {
                $results[] = [
                    'subject_code' => $subject_code,
                    'major_code'   => $major_code,
                    'target_code'  => $target_code,
                    'status'       => 'success',
                    'message'      => 'Record already exists (no changes)'
                ];
                continue;
            }
    
            // UPDATE (khi 3 khóa giống nhau và có thay đổi)
            $record->id = $existing->id;
            $record->timemodified = time();
            $record->updated_by   = $USER->id;
    
            $DB->update_record('th_framework_study', $record);
    
            $results[] = [
                'subject_code' => $subject_code,
                'major_code'   => $major_code,
                'target_code'  => $target_code,
                'status'       => 'success',
                'message'      => 'Updated'
            ];
        }
    
        return $results;
    }
    

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'subject_code' => new external_value(PARAM_TEXT, 'Mã học phần'),
                'major_code'   => new external_value(PARAM_TEXT, 'Mã ngành'),
                'target_code'  => new external_value(PARAM_TEXT, 'Mã đối tượng'),
                'status'       => new external_value(PARAM_TEXT, 'success/failure'),
                'message'      => new external_value(PARAM_TEXT, 'Thông báo kết quả')
            ])
        );
    }
}
