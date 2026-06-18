<?php
namespace local_th_evstep_mailling_reminder\task;

defined('MOODLE_INTERNAL') || die();


class send_mail_graded_attemp extends \core\task\adhoc_task {

    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/th_evstep_mailling_reminder/locallib.php');

        $data = $this->get_custom_data(); // expected: ['approved_ids' => [...]]
        if (!empty($data->approved_ids) && is_array($data->approved_ids)) {
            error_log("[TASK] Running send mail graded attempt to student with attempt IDs: " . json_encode($data->approved_ids));
            send_mail_graded_attemp($data->approved_ids);
        } else {
            error_log("[TASK] No approved_ids provided or invalid data.");
        }
    }
}
