<?php
namespace local_th_qaa_manager\service;

defined('MOODLE_INTERNAL') || die();

class ticket_service {
    public const STATUS_NEW               = 0;
    public const STATUS_APPROVED          = 1;
    public const STATUS_ASSIGNED          = 2;
    public const STATUS_ANSWERED          = 3;
    public const STATUS_CLOSED            = 4;
    public const STATUS_QUESTION_REJECTED = 5;
    public const STATUS_ANSWER_REJECTED   = 6;
    public const STATUS_RECALLED          = 7;
    public const STATUS_REJECTED = self::STATUS_QUESTION_REJECTED;
    
    public static function get_assignee_role(): int {
        return (int)get_config(
            'local_th_qaa_manager',
            'assignee_role'
        );
    }

    public static function get_assignee_users(): array {
        global $DB;

        $roleid = self::get_assignee_role();

        if (!$roleid) {
            return [];
        }

        $sql = "SELECT DISTINCT u.*
                FROM {user} u
                JOIN {role_assignments} ra
                    ON ra.userid = u.id
                WHERE ra.roleid = :roleid
                AND u.deleted = 0
                AND u.suspended = 0
            ORDER BY u.lastname ASC, u.firstname ASC";

        return $DB->get_records_sql(
            $sql,
            [
                'roleid' => $roleid
            ]
        );
    }

    // public static function status_label(int $status): string {
    //     switch ($status) {
    //         case self::STATUS_NEW:
    //             return get_string('status_new', 'local_th_qaa_manager');
    //         case self::STATUS_APPROVED:
    //             return get_string('status_approved', 'local_th_qaa_manager');
    //         case self::STATUS_ASSIGNED:
    //             return get_string('status_assigned', 'local_th_qaa_manager');
    //         case self::STATUS_ANSWERED:
    //             return get_string('status_answered', 'local_th_qaa_manager');
    //         case self::STATUS_CLOSED:
    //             return get_string('status_closed', 'local_th_qaa_manager');
    //         case self::STATUS_REJECTED:
    //             return get_string('status_rejected', 'local_th_qaa_manager');
    //         default:
    //             return '-';
    //     }
    // }
    public static function status_label(int $status): string {
        switch ($status) {
            case self::STATUS_NEW:
                return get_string(
                    'status_new',
                    'local_th_qaa_manager'
                );

            case self::STATUS_APPROVED:
                return get_string(
                    'status_approved',
                    'local_th_qaa_manager'
                );

            case self::STATUS_ASSIGNED:
                return get_string(
                    'status_assigned',
                    'local_th_qaa_manager'
                );

            case self::STATUS_ANSWERED:
                return get_string(
                    'status_answered',
                    'local_th_qaa_manager'
                );

            case self::STATUS_CLOSED:
                return get_string(
                    'status_closed',
                    'local_th_qaa_manager'
                );

            case self::STATUS_QUESTION_REJECTED:
                return get_string(
                    'status_question_rejected',
                    'local_th_qaa_manager'
                );

            case self::STATUS_ANSWER_REJECTED:
                return get_string(
                    'status_answer_rejected',
                    'local_th_qaa_manager'
                );

            case self::STATUS_RECALLED:
                return get_string(
                    'status_recalled',
                    'local_th_qaa_manager'
                );

            default:
                return '-';
        }
    }

    /**
     * Create local workflow ticket from qaapairs after QTV approves question.
     */
    public static function create_from_qaapair(int $qaapairid): int {
        global $DB;

        $existing = $DB->get_record('local_th_qaa_ticket', [
            'qaapairid' => $qaapairid,
        ]);

        if ($existing) {
            return (int)$existing->id;
        }

        // Make sure qaapair exists.
        $DB->get_record('qaapairs', ['id' => $qaapairid], 'id', MUST_EXIST);

        $now = time();

        return (int)$DB->insert_record('local_th_qaa_ticket', (object)[
            'qaapairid' => $qaapairid,
            'assigneduserid' => null,
            'status' => self::STATUS_APPROVED,
            'rejectreason' => null,
            'timecreated' => $now,
            'timemodified' => $now,
        ], true);
    }

    /**
     * Approve a pending qaapairs question and create local ticket.
     *
     * Important: $qaapairid, not ticketid.
     */
    public static function approve_question(int $qaapairid, int $userid = 0): int {
        global $DB;

        $qaapair = $DB->get_record('qaapairs', [
            'id' => $qaapairid,
        ], '*', MUST_EXIST);

        if ((int)$qaapair->status !== 0) {
            $existing = $DB->get_record('local_th_qaa_ticket', [
                'qaapairid' => $qaapairid,
            ]);

            return $existing ? (int)$existing->id : self::create_from_qaapair($qaapairid);
        }

        $transaction = $DB->start_delegated_transaction();

        try {
            $DB->update_record('qaapairs', (object)[
                'id' => $qaapairid,
                'status' => 1,
            ]);

            $ticketid = self::create_from_qaapair($qaapairid);

            $DB->update_record('local_th_qaa_ticket', (object)[
                'id' => $ticketid,
                'status' => self::STATUS_APPROVED,
                'rejectreason' => null,
                'timemodified' => time(),
            ]);

            $transaction->allow_commit();

            return $ticketid;
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Bulk approve qaapairs questions.
     *
     * Important: array of qaapair ids, not ticket ids.
     */
    public static function approve_questions(array $qaapairids, int $userid = 0): int {
        $qaapairids = array_values(array_unique(array_filter(array_map('intval', $qaapairids))));

        if (empty($qaapairids)) {
            return 0;
        }

        $count = 0;

        foreach ($qaapairids as $qaapairid) {
            self::approve_question($qaapairid, $userid);
            $count++;
        }

        return $count;
    }

    /**
     * Reject a pending qaapairs question.
     *
     * Important: $qaapairid, not ticketid.
     */
    public static function reject_question(int $qaapairid, int $userid = 0, string $reason = ''): void {
        global $DB;

        $qaapair = $DB->get_record('qaapairs', [
            'id' => $qaapairid,
        ], '*', MUST_EXIST);

        if (!in_array((int)$qaapair->status, [0, 1], true)) {
            return;
        }

        $now = time();

        $transaction = $DB->start_delegated_transaction();

        try {
            // Mark root question as rejected, but keep original question message.
            $DB->update_record('qaapairs', (object)[
                'id' => $qaapairid,
                'status' => 2,
            ]);

            // Store reject reason as a child qaapair answer from QTV.
            if (trim($reason) !== '') {
                $rejectanswer = (object)[
                    'qaaid' => $qaapair->qaaid,
                    'parentid' => $qaapairid,
                    'studentid' => null,
                    'teacherid' => $userid,
                    'message' => $reason,
                    'status' => 2,
                    'timecreatedanswer' => $now,
                    'timemodifiedanswer' => $now,
                ];

                $DB->insert_record('qaapairs', $rejectanswer);
            }

            // Keep local workflow record for rejected tab and reject reason.
            $existing = $DB->get_record('local_th_qaa_ticket', [
                'qaapairid' => $qaapairid,
            ]);

            if ($existing) {
                $DB->update_record('local_th_qaa_ticket', (object)[
                    'id' => $existing->id,
                    'assigneduserid' => null,
                    'status' => self::STATUS_QUESTION_REJECTED,
                    'rejectreason' => $reason,
                    'timemodified' => $now,
                ]);
            } else {
                $DB->insert_record('local_th_qaa_ticket', (object)[
                    'qaapairid' => $qaapairid,
                    'assigneduserid' => null,
                    'status' => self::STATUS_QUESTION_REJECTED,
                    'rejectreason' => $reason,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ]);
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Assign an approved ticket to CTV.
     *
     * This still uses ticket id because ticket already exists after approval.
     */
    // public static function assign(int $ticketid, int $assigneeid, int $assignedby = 0): void {
    //     global $DB;

    //     $ticket = $DB->get_record('local_th_qaa_ticket', [
    //         'id' => $ticketid,
    //     ], '*', MUST_EXIST);

    //     if (!in_array((int)$ticket->status, [
    //         self::STATUS_APPROVED,
    //         self::STATUS_ASSIGNED,
    //     ], true)) {
    //         return;
    //     }

    //     $DB->update_record('local_th_qaa_ticket', (object)[
    //         'id' => $ticketid,
    //         'assigneduserid' => $assigneeid,
    //         'status' => self::STATUS_ASSIGNED,
    //         'rejectreason' => null,
    //         'timemodified' => time(),
    //     ]);

    //     \local_th_qaa_manager\service\notification_service::queue_assignment_notification($ticketid);
    // }
    public static function assign(
        int $ticketid,
        int $assigneeid,
        int $assignedby = 0
    ): void {
        global $DB;

        $ticket = $DB->get_record(
            'local_th_qaa_ticket',
            ['id' => $ticketid],
            '*',
            MUST_EXIST
        );

        if (!in_array((int)$ticket->status, [
            self::STATUS_APPROVED,
            self::STATUS_ASSIGNED,
            self::STATUS_ANSWER_REJECTED,
            self::STATUS_RECALLED,
        ], true)) {
            return;
        }

        $targetticketid = $ticketid;
        $is_reassign = ((int)$ticket->status === self::STATUS_ANSWER_REJECTED
                     || (int)$ticket->status === self::STATUS_RECALLED
                     || (int)$ticket->status === self::STATUS_ASSIGNED);

        $has_existing_answer = $DB->record_exists('local_th_qaa_answer', ['ticketid' => $ticketid]);
        $should_create_new = ((int)$ticket->status === self::STATUS_ANSWER_REJECTED)
            || $has_existing_answer
            || ($is_reassign && !empty($ticket->assigneduserid) && (int)$ticket->assigneduserid !== $assigneeid);

        if ($should_create_new) {
            $targetticketid = (int)$DB->insert_record('local_th_qaa_ticket', (object)[
                'qaapairid' => $ticket->qaapairid,
                'assigneduserid' => $assigneeid,
                'status' => self::STATUS_ASSIGNED,
                'rejectreason' => null,
                'timecreated' => time(),
                'timemodified' => time(),
            ]);
        } else {
            $DB->update_record('local_th_qaa_ticket', (object)[
                'id' => $ticketid,
                'assigneduserid' => $assigneeid,
                'status' => self::STATUS_ASSIGNED,
                'rejectreason' => null,
                'timemodified' => time(),
            ]);
        }

        self::log_assignment($targetticketid, $assigneeid, $assignedby);

        notification_service::queue_assignment_notification(
            $targetticketid
        );
    }

    /**
     * Bulk assign tickets.
     *
     * Important: array of ticket ids, not qaapair ids.
     */
    public static function assign_questions(array $ticketids, int $assigneeid, int $assignedby = 0): int {
        global $DB;

        $ticketids = array_values(array_unique(array_filter(array_map('intval', $ticketids))));
        $assigneeid = (int)$assigneeid;

        if (empty($ticketids) || $assigneeid <= 0) {
            return 0;
        }

        [$insql, $params] = $DB->get_in_or_equal($ticketids, SQL_PARAMS_NAMED, 'ticketid');
        $params['statusapproved'] = self::STATUS_APPROVED;
        $params['statusassigned'] = self::STATUS_ASSIGNED;
        $params['statusanswerrejected'] = self::STATUS_ANSWER_REJECTED;
        $params['statusrecalled'] = self::STATUS_RECALLED;

        $tickets = $DB->get_records_select(
            'local_th_qaa_ticket',
            "id {$insql}
                AND status IN (
                    :statusapproved,
                    :statusassigned,
                    :statusanswerrejected,
                    :statusrecalled
                )",
            $params
        );

        if (!$tickets) {
            return 0;
        }

        $now = time();
        $count = 0;
        $assignedticketids = [];

        $transaction = $DB->start_delegated_transaction();

        try {
            foreach ($tickets as $ticket) {
                $targetticketid = $ticket->id;
                $tstatus = (int)($ticket->status ?? 0);
                $is_reassign = ($tstatus === self::STATUS_ANSWER_REJECTED
                             || $tstatus === self::STATUS_RECALLED
                             || $tstatus === self::STATUS_ASSIGNED);

                $has_existing_answer = $DB->record_exists('local_th_qaa_answer', ['ticketid' => $ticket->id]);
                $should_create_new = ($tstatus === self::STATUS_ANSWER_REJECTED)
                    || $has_existing_answer
                    || ($is_reassign && !empty($ticket->assigneduserid) && (int)$ticket->assigneduserid !== $assigneeid);

                if ($should_create_new) {
                    $targetticketid = (int)$DB->insert_record('local_th_qaa_ticket', (object)[
                        'qaapairid' => $ticket->qaapairid,
                        'assigneduserid' => $assigneeid,
                        'status' => self::STATUS_ASSIGNED,
                        'rejectreason' => null,
                        'timecreated' => $now,
                        'timemodified' => $now,
                    ]);
                } else {
                    $DB->update_record('local_th_qaa_ticket', (object)[
                        'id' => $ticket->id,
                        'assigneduserid' => $assigneeid,
                        'status' => self::STATUS_ASSIGNED,
                        'rejectreason' => null,
                        'timemodified' => $now,
                    ]);
                }

                self::log_assignment($targetticketid, $assigneeid, $assignedby);

                $assignedticketids[] = (int)$targetticketid;
                $count++;
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }

        foreach ($assignedticketids as $assignedticketid) {
            \local_th_qaa_manager\service\notification_service::queue_assignment_notification($assignedticketid);
        }

        return $count;
    }

    public static function close_ticket(int $ticketid, int $closedby = 0): void {
        global $DB;

        $DB->update_record('local_th_qaa_ticket', (object)[
            'id' => $ticketid,
            'status' => self::STATUS_CLOSED,
            'rejectreason' => null,
            'timemodified' => time(),
        ]);
    }

    /**
     * Delete assignment and reset ticket.
     */
    public static function delete_assignment(int $ticketid): void {
        global $DB;

        $ticket = $DB->get_record('local_th_qaa_ticket', ['id' => $ticketid], '*', MUST_EXIST);

        $transaction = $DB->start_delegated_transaction();

        try {
            // Delete collaborator answer if any.
            $DB->delete_records('local_th_qaa_answer', ['ticketid' => $ticketid]);

            // Mark the assignment as recalled manually in history.
            $DB->execute("UPDATE {local_th_qaa_assign_history}
                             SET timerecalled = :time, recalltype = 1
                           WHERE ticketid = :ticketid AND userid = :userid AND timerecalled IS NULL",
                ['time' => time(), 'ticketid' => $ticketid, 'userid' => $ticket->assigneduserid]
            );

            // Revert ticket to recalled state and keep assignee.
            $DB->update_record('local_th_qaa_ticket', (object)[
                'id' => $ticketid,
                'status' => self::STATUS_RECALLED,
                'rejectreason' => null,
                'reviewerid' => null,
                'timemodified' => time(),
            ]);

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Cancel question rejection and return to pending approval state.
     */
    public static function cancel_reject_question(int $ticketid): void {
        global $DB;

        $ticket = $DB->get_record('local_th_qaa_ticket', ['id' => $ticketid], '*', MUST_EXIST);
        $qaapairid = $ticket->qaapairid;

        $transaction = $DB->start_delegated_transaction();
        try {
            // Revert parent question status to 0 (pending)
            $DB->update_record('qaapairs', (object)[
                'id' => $qaapairid,
                'status' => 0,
            ]);

            // Delete child answer/reject reason records from qaapairs
            $DB->delete_records('qaapairs', [
                'parentid' => $qaapairid,
                'status' => 2,
            ]);

            // Delete the ticket since pending questions are queried directly from qaapairs
            $DB->delete_records('local_th_qaa_ticket', ['id' => $ticketid]);

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Auto recall assignment.
     */
    public static function auto_recall_assignment(int $ticketid): void {
        global $DB;

        $ticket = $DB->get_record('local_th_qaa_ticket', ['id' => $ticketid], '*', MUST_EXIST);

        $transaction = $DB->start_delegated_transaction();

        try {
            // Delete collaborator answer if any.
            $DB->delete_records('local_th_qaa_answer', ['ticketid' => $ticketid]);

            // Mark the assignment as auto recalled in history.
            $DB->execute("UPDATE {local_th_qaa_assign_history}
                             SET timerecalled = :time, recalltype = 2
                           WHERE ticketid = :ticketid AND userid = :userid AND timerecalled IS NULL",
                ['time' => time(), 'ticketid' => $ticketid, 'userid' => $ticket->assigneduserid]
            );

            // Revert ticket to recalled state and keep assignee.
            $DB->update_record('local_th_qaa_ticket', (object)[
                'id' => $ticketid,
                'status' => self::STATUS_RECALLED,
                'rejectreason' => null,
                'reviewerid' => null,
                'timemodified' => time(),
            ]);

            $transaction->allow_commit();

            if (!empty($ticket->assigneduserid)) {
                try {
                    notification_service::send_recalled_notification($ticketid, (int)$ticket->assigneduserid);
                } catch (\Throwable $e) {
                    debugging('Failed to send recalled notification: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Log assignment into history table.
     */
    private static function log_assignment(int $ticketid, int $assigneeid, int $assignedby): void {
        global $DB;
        $now = time();

        // Close any active assignments just in case.
        $DB->execute("UPDATE {local_th_qaa_assign_history}
                         SET timerecalled = :time, recalltype = 1
                       WHERE ticketid = :ticketid AND timerecalled IS NULL",
            ['time' => $now, 'ticketid' => $ticketid]
        );

        $DB->insert_record('local_th_qaa_assign_history', (object)[
            'ticketid' => $ticketid,
            'userid' => $assigneeid,
            'assignedby' => $assignedby,
            'timeassigned' => $now,
            'timerecalled' => null,
            'recalltype' => 0,
        ]);
    }
}