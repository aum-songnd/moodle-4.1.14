<?php
namespace local_th_qaa_manager\task;

defined('MOODLE_INTERNAL') || die();

use local_th_qaa_manager\service\ticket_service;
use local_th_qaa_manager\service\notification_service;

class weekly_question_notify_task extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('task_weekly_question_notify', 'local_th_qaa_manager');
    }

    public function execute(): void {
        global $DB;

        $userfields = \core_user\fields::for_name()
            ->get_sql('u', false, '', '', false)
            ->selects;

        // Query all questions matching the "Phân công CTV" tab logic in manage.php.
        $sql = "
            (SELECT qp.id AS id,
                   t.id AS ticketid,
                   t.status AS status,
                   t.assigneduserid,
                   t.reviewerid,
                   qp.id AS qaapairid,
                   qp.message AS question,
                   qp.timecreatedquestion,
                   qp.parentid,
                   qp.timecreatedanswer AS qp_timecreatedanswer,
                   q.course AS courseid,
                   c.fullname AS coursefullname,
                   c.idnumber AS courseidnumber,
                   c.shortname AS courseshortname,
                   {$userfields}
              FROM {local_th_qaa_ticket} t
              JOIN {qaapairs} qp ON qp.id = t.qaapairid
              JOIN {qaa} q ON q.id = qp.qaaid
              JOIN {course} c ON c.id = q.course
              JOIN {user} u ON u.id = qp.studentid
             WHERE (t.status = :status_approved OR t.status = :status_rejected OR t.status = :status_recalled)
               AND t.id = (SELECT MAX(id) FROM {local_th_qaa_ticket} t2 WHERE t2.qaapairid = t.qaapairid))
            UNION ALL
            (SELECT qp.id AS id,
                   0 AS ticketid,
                   0 AS status,
                   0 AS assigneduserid,
                   0 AS reviewerid,
                   qp.id AS qaapairid,
                   qp.message AS question,
                   qp.timecreatedquestion,
                   qp.parentid,
                   qp.timecreatedanswer AS qp_timecreatedanswer,
                   q.course AS courseid,
                   c.fullname AS coursefullname,
                   c.idnumber AS courseidnumber,
                   c.shortname AS courseshortname,
                   {$userfields}
              FROM {qaapairs} qp
              JOIN {qaa} q ON q.id = qp.qaaid
              JOIN {course} c ON c.id = q.course
              JOIN {user} u ON u.id = qp.studentid
             WHERE qp.status = 0)
          ORDER BY timecreatedquestion DESC";

        $tickets = $DB->get_records_sql($sql, [
            'status_approved' => ticket_service::STATUS_APPROVED,
            'status_rejected' => ticket_service::STATUS_ANSWER_REJECTED,
            'status_recalled' => ticket_service::STATUS_RECALLED,
        ]);

        if (!$tickets) {
            mtrace('No pending questions found for collaborator assignment.');
            return;
        }
        mtrace('Found ' . count($tickets) . ' pending questions for collaborator assignment.');
        notification_service::send_weekly_new_questions_digest($tickets);
    }
}