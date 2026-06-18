<?php
namespace report_th_courseopening\task;

class report_th_courseopening extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('dosomething', 'report_th_courseopening');
    }

    public function execute() {
        global $DB;

        // Lấy tất cả startdate hiện tại từ bảng course (loại bỏ startdate = 0).
        $sql = "SELECT DISTINCT c.startdate
                  FROM {course} c
                 WHERE c.startdate > 0
              ORDER BY c.startdate ASC";
        $records = $DB->get_records_sql($sql);

        $currentdot = array_map(function($r) {
            return $r->startdate;
        }, $records);

        $inserted_count = 0;
        $skipped_count  = 0;
        $deleted_count  = 0;

        // Insert hoặc skip.
        foreach ($currentdot as $startdate) {
            $exists = $DB->record_exists('report_th_courseopening',
                                         ['dot_mo_mon' => $startdate]);

            if (!$exists) {
                $data = new \stdClass();
                $data->dot_mo_mon  = $startdate;
                $data->timecreated = time();

                $DB->insert_record('report_th_courseopening', $data);
                $inserted_count++;
                mtrace("Inserted new dot_mo_mon={$startdate}");
            } else {
                $skipped_count++;
            }
        }

        // Lấy danh sách đã lưu trong bảng tổng hợp.
        $existingdot = $DB->get_fieldset_select('report_th_courseopening',
                                                'dot_mo_mon',
                                                '1=1');

        // Tìm đợt cần xóa (có trong bảng tổng hợp nhưng không còn trong course).
        $todelete = array_diff($existingdot, $currentdot);

        if (!empty($todelete)) {
            list($insql, $params) = $DB->get_in_or_equal($todelete, SQL_PARAMS_NAMED);
            $DB->delete_records_select('report_th_courseopening', "dot_mo_mon $insql", $params);
            $deleted_count = count($todelete);
            mtrace("Deleted {$deleted_count} outdated dot_mo_mon");
        }

        mtrace("Process completed: {$inserted_count} inserted, {$skipped_count} skipped, {$deleted_count} deleted at " . date('Y-m-d H:i:s'));
    }
}