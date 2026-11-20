<?php
namespace local_th_qaa_manager\task;

defined('MOODLE_INTERNAL') || die();

use local_th_qaa_manager\service\notification_service;

class send_assignment_notification_task extends \core\task\adhoc_task {
    public function execute(): void {
        $data = $this->get_custom_data();

        if (empty($data->ticketid)) {
            return;
        }

        notification_service::send_assignment_notification((int)$data->ticketid);
    }
}