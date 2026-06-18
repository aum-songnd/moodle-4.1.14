<?php
namespace local_th_syncexamqbank\task;

defined('MOODLE_INTERNAL') || die();

class sync_exam_qbank_task extends \core\task\adhoc_task {

    public function get_name() {
        return get_string('taskname', 'local_th_syncexamqbank');
    }

    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        if (empty($data->startdate)) {
            mtrace('❌ startdate not provided.');
            return;
        }

        require_once(__DIR__ . '/../../classes/sync_manager.php');

        mtrace('🚀 Starting background sync for startdate=' . userdate($data->startdate));

        $logs = \local_th_syncexamqbank\sync_manager::sync_by_startdate($data->startdate);

        mtrace('✅ Sync done.');
    }
}
