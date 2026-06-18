<?php
namespace local_th_submission_notify\task;

defined('MOODLE_INTERNAL') || die();


class send_result_task extends \core\task\adhoc_task {

    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/th_submission_notify/locallib.php');

        $data = $this->get_custom_data(); // expected: ['approved_ids' => [...]]
        if (!empty($data->approved_ids) && is_array($data->approved_ids)) {
            error_log("[TASK] Running send_result_to_student with IDs: " . json_encode($data->approved_ids));
            send_result_to_student($data->approved_ids);
        } else {
            error_log("[TASK] No approved_ids provided or invalid data.");
        }
    }
}
