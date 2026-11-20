<?php
namespace local_th_qaa_manager\task;

defined('MOODLE_INTERNAL') || die();

use local_th_qaa_manager\service\ticket_service;
use local_th_qaa_manager\service\notification_service;

class daily_answer_notify_task extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('task_daily_answer_notify', 'local_th_qaa_manager');
    }

    public function execute(): void {
        global $DB;

        $userfields = \core_user\fields::for_name()
            ->get_sql('u', false, '', '', false)
            ->selects;

        // Query all tickets answered by CTV pending QTCTV approval from the beginning until now.
        $sql = "SELECT t.id, t.timemodified, c.fullname AS coursefullname,
                       qp.message AS question,
                       {$userfields}
                  FROM {local_th_qaa_ticket} t
                  JOIN {qaapairs} qp ON qp.id = t.qaapairid
                  JOIN {qaa} q ON q.id = qp.qaaid
                  JOIN {course} c ON c.id = q.course
                  JOIN {user} u ON u.id = qp.studentid
                 WHERE t.status = :status
              ORDER BY t.id DESC";

        $tickets = $DB->get_records_sql($sql, [
            'status' => ticket_service::STATUS_ANSWERED,
        ]);

        if (!$tickets) {
            mtrace('No answered questions found needing approval.');
            return;
        }

        mtrace('Found ' . count($tickets) . ' answered questions needing approval.');
        notification_service::send_daily_answered_digest($tickets);
    }
}
