<?php

namespace local_th_qaa_manager\service;

defined('MOODLE_INTERNAL') || die();

class notification_service
{

    public static function get_managers(): array
    {
        global $DB;

        $configured = get_config('local_th_qaa_manager', 'manager_emails');

        if (!empty($configured)) {
            $emails = array_map('trim', explode(',', $configured));
            $emails = array_filter($emails);
            $managers = [];

            foreach ($emails as $email) {
                if ($user = $DB->get_record('user', ['email' => $email, 'deleted' => 0, 'suspended' => 0])) {
                    $managers[] = $user;
                } else {
                    $user = new \stdClass();
                    $user->id = -1;
                    $user->email = $email;
                    $user->firstname = get_string('administrator', 'local_th_qaa_manager');
                    $user->lastname = 'QTCTV';
                    $user->mailformat = 1;
                    $user->maildisplay = 1;
                    $user->deleted = 0;
                    $user->suspended = 0;
                    $managers[] = $user;
                }
            }

            return $managers;
        }

        return [];
    }

    public static function send_weekly_new_questions_digest(array $tickets): void
    {
        if (empty($tickets)) {
            return;
        }

        $managers = self::get_managers();

        if (empty($managers)) {
            return;
        }

        $supportuser = \core_user::get_support_user();

        $customsubject = get_config('local_th_qaa_manager', 'email_weekly_subject');
        $subject = !empty($customsubject)
            ? $customsubject
            : get_string('email_weekly_new_questions_subject', 'local_th_qaa_manager');

        $url = new \moodle_url('/local/th_qaa_manager/manage.php', [
            'status' => ticket_service::STATUS_APPROVED,
        ]);

        $custombody = get_config('local_th_qaa_manager', 'email_weekly_body');
        $count = count($tickets);
        $urlstr = $url->out(false);

        if (!empty($custombody)) {
            $message = str_replace(
                ['{$a->url}', '{$a->count}'],
                [$urlstr, $count],
                $custombody
            );
        } else {
            $lines = [];
            $lines[] = 'Xin chào anh/chị!';
            $lines[] = '';
            $lines[] = get_string('email_weekly_new_questions_intro', 'local_th_qaa_manager');
            $lines[] = 'Đường dẫn: ' . $urlstr;
            $lines[] = '';
            $lines[] = 'Xin cảm ơn.';
            $message = implode("\n", $lines);
        }

        $messagehtml = nl2br($message);

        foreach ($managers as $manager) {
            email_to_user($manager, $supportuser, $subject, $message, $messagehtml);
        }
    }

    public static function send_assignment_notification(int $ticketid): void
    {
        $ticket = self::get_ticket_with_course_and_student($ticketid);

        if (empty($ticket->assigneduserid)) {
            return;
        }

        $assignee = \core_user::get_user($ticket->assigneduserid, '*', MUST_EXIST);

        if (empty($assignee->email) || !empty($assignee->suspended) || !empty($assignee->deleted)) {
            return;
        }

        $supportuser = \core_user::get_support_user();

        $url = new \moodle_url('/local/th_qaa_manager/view.php', [
            'id' => $ticket->id,
        ]);

        $subject = get_string('email_assignment_subject', 'local_th_qaa_manager');

        $message = get_string('email_assignment_body', 'local_th_qaa_manager', (object)[
            'assignee' => fullname($assignee),
            'course' => $ticket->coursefullname,
            'student' => fullname($ticket),
            'question' => trim(strip_tags((string)$ticket->question)),
            'url' => $url->out(false),
        ]);

        $messagehtml = nl2br(s($message));

        email_to_user($assignee, $supportuser, $subject, $message, $messagehtml);
    }

    public static function send_answer_approved_notification(int $ticketid): void
    {
        $ticket = self::get_ticket_with_course_and_student($ticketid);
        $answer = answer_service::get_latest_answer($ticketid);

        if (!$answer) {
            return;
        }

        $student = \core_user::get_user($ticket->studentid, '*', MUST_EXIST);
        $ctv = \core_user::get_user($answer->userid, '*', MUST_EXIST);
        $supportuser = \core_user::get_support_user();

        $subject = get_string('email_answer_approved_subject', 'local_th_qaa_manager');

        $studenturl = new \moodle_url('/mod/qaa/view.php', [
            'id' => self::get_cmid_from_ticket($ticket),
            'embed' => 1,
        ]);

        $ctvurl = new \moodle_url('/local/th_qaa_manager/view.php', [
            'id' => $ticket->id,
        ]);

        $a = (object)[
            'course' => $ticket->coursefullname,
            'student' => fullname($student),
            'ctv' => fullname($ctv),
            'question' => trim(strip_tags((string)$ticket->question)),
            'answer' => trim(strip_tags((string)$answer->content)),
            'studenturl' => $studenturl->out(false),
            'ctvurl' => $ctvurl->out(false),
        ];

        if (!empty($student->email) && empty($student->deleted) && empty($student->suspended)) {
            $message = get_string('email_answer_approved_student_body', 'local_th_qaa_manager', $a);
            email_to_user($student, $supportuser, $subject, $message, nl2br($message));
        }

        if (!empty($ctv->email) && empty($ctv->deleted) && empty($ctv->suspended)) {
            $message = get_string('email_answer_approved_ctv_body', 'local_th_qaa_manager', $a);
            email_to_user($ctv, $supportuser, $subject, $message, nl2br($message));
        }
    }

    private static function get_ticket_with_course_and_student(int $ticketid): \stdClass
    {
        global $DB;

        $userfields = \core_user\fields::for_name()
            ->get_sql('u', false, '', '', false)
            ->selects;

        $sql = "SELECT t.*,
                       qp.qaaid,
                       qp.studentid,
                       qp.message AS question,
                       q.course AS courseid,
                       c.fullname AS coursefullname,
                       {$userfields}
                  FROM {local_th_qaa_ticket} t
                  JOIN {qaapairs} qp ON qp.id = t.qaapairid
                  JOIN {qaa} q ON q.id = qp.qaaid
                  JOIN {course} c ON c.id = q.course
                  JOIN {user} u ON u.id = qp.studentid
                 WHERE t.id = :ticketid";

        return $DB->get_record_sql($sql, [
            'ticketid' => $ticketid,
        ], MUST_EXIST);
    }

    private static function get_cmid_from_ticket(\stdClass $ticket): int
    {
        $cm = get_coursemodule_from_instance(
            'qaa',
            $ticket->qaaid,
            $ticket->courseid,
            false,
            MUST_EXIST
        );

        return (int)$cm->id;
    }

    public static function queue_assignment_notification(int $ticketid): void
    {
        $task = new \local_th_qaa_manager\task\send_assignment_notification_task();
        $task->set_custom_data((object)[
            'ticketid' => $ticketid,
        ]);

        \core\task\manager::queue_adhoc_task($task);
    }

    public static function queue_answer_approved_notification(int $ticketid): void
    {
        $task = new \local_th_qaa_manager\task\send_answer_approved_notification_task();
        $task->set_custom_data((object)[
            'ticketid' => $ticketid,
        ]);

        \core\task\manager::queue_adhoc_task($task);
    }

    public static function send_daily_answered_digest(array $tickets): void
    {
        if (empty($tickets)) {
            return;
        }

        $managers = self::get_managers();
        if (empty($managers)) {
            return;
        }

        $supportuser = \core_user::get_support_user();

        $customsubject = get_config('local_th_qaa_manager', 'email_daily_subject');
        $subject = !empty($customsubject)
            ? $customsubject
            : get_string('email_daily_answered_subject', 'local_th_qaa_manager');

        $url = new \moodle_url('/local/th_qaa_manager/manage.php', [
            'status' => 10,
        ]);

        $custombody = get_config('local_th_qaa_manager', 'email_daily_body');
        $count = count($tickets);
        $urlstr = $url->out(false);

        if (!empty($custombody)) {
            $message = str_replace(
                ['{$a->url}', '{$a->count}'],
                [$urlstr, $count],
                $custombody
            );
        } else {
            $lines = [];
            $lines[] = 'Xin chào anh/chị!';
            $lines[] = '';
            $lines[] = 'CTV đã hoàn thành trả lời câu hỏi, vui lòng kiểm tra và hoàn thành phê duyệt câu trả lời.';
            $lines[] = $urlstr;
            $lines[] = '';
            $lines[] = 'Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline 0948.293.794 ).';
            $lines[] = '';
            $lines[] = 'Xin cảm ơn.';
            $message = implode("\n", $lines);
        }

        $messagehtml = nl2br($message);

        foreach ($managers as $manager) {
            email_to_user($manager, $supportuser, $subject, $message, $messagehtml);
        }
    }

    public static function send_recalled_notification(int $ticketid, int $ctvid): void
    {
        global $DB;
        $ctv = \core_user::get_user($ctvid, '*', MUST_EXIST);
        if (empty($ctv->email) || !empty($ctv->suspended) || !empty($ctv->deleted)) {
            return;
        }

        $ticket = $DB->get_record_sql("
            SELECT t.id, qp.message AS question
              FROM {local_th_qaa_ticket} t
              JOIN {qaapairs} qp ON qp.id = t.qaapairid
             WHERE t.id = :id", ['id' => $ticketid]);

        if (!$ticket) {
            return;
        }

        $supportuser = \core_user::get_support_user();
        $subject = get_string('email_recalled_subject', 'local_th_qaa_manager');
        $questiontext = trim(strip_tags((string)$ticket->question));

        $days = get_config('local_th_qaa_manager', 'auto_recall_days');
        if ($days === false || $days === '') {
            $days = 7;
        } else {
            $days = (int)$days;
        }

        $message = get_string('email_recalled_body', 'local_th_qaa_manager', (object)[
            'question' => $questiontext,
            'days' => $days,
        ]);

        email_to_user($ctv, $supportuser, $subject, $message, nl2br(s($message)));
    }
}
