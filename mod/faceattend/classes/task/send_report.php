<?php
/**
 * classes/task/send_report.php
 * Scheduled task: Gửi báo cáo điểm danh hàng ngày cho giảng viên
 * (Chức năng này sẽ được implement ở phiên bản sau)
 */
namespace mod_faceattend\task;

defined('MOODLE_INTERNAL') || die();

class send_report extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('pluginname', 'faceattend') . ' - Send daily report';
    }

    public function execute() {
        // TODO: implement gửi email báo cáo
        mtrace('mod_faceattend: send_report task ran (not yet implemented)');
    }
}
