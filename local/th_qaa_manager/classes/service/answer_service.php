<?php
namespace local_th_qaa_manager\service;

defined('MOODLE_INTERNAL') || die();

class answer_service {
    public const STATUS_DRAFT     = 0;
    public const STATUS_SUBMITTED = 1;
    public const STATUS_APPROVED  = 2;
    public const STATUS_REJECTED  = 3;

    public static function submit_answer(int $ticketid, int $userid, string $content, int $format = FORMAT_HTML): int {
        global $DB;

        $ticket = $DB->get_record('local_th_qaa_ticket', ['id' => $ticketid], '*', MUST_EXIST);

        if ((int)$ticket->assigneduserid !== $userid) {
            throw new \moodle_exception('notassignedtoyou', 'local_th_qaa_manager');
        }

        if (!in_array((int)$ticket->status, [
            ticket_service::STATUS_ASSIGNED,
            ticket_service::STATUS_ANSWER_REJECTED,
        ], true)) {
            throw new \moodle_exception('invalidticket', 'local_th_qaa_manager');
        }

        $now = time();

        $transaction = $DB->start_delegated_transaction();

        try {
            $answer = $DB->get_record('local_th_qaa_answer', [
                'ticketid' => $ticketid,
            ]);

            if ($answer) {
                $answerid = (int)$answer->id;

                $DB->update_record('local_th_qaa_answer', (object)[
                    'id' => $answerid,
                    'userid' => $userid,
                    'content' => $content,
                    'contentformat' => $format,
                    'status' => self::STATUS_SUBMITTED,
                    'rejectreason' => null,
                    'timemodified' => $now,
                ]);
            } else {
                $answerid = (int)$DB->insert_record('local_th_qaa_answer', (object)[
                    'ticketid' => $ticketid,
                    'userid' => $userid,
                    'content' => $content,
                    'contentformat' => $format,
                    'status' => self::STATUS_SUBMITTED,
                    'rejectreason' => null,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ], true);
            }

            $DB->update_record('local_th_qaa_ticket', (object)[
                'id' => $ticketid,
                'status' => ticket_service::STATUS_ANSWERED,
                'timemodified' => $now,
            ]);

            $transaction->allow_commit();

            return $answerid;
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    public static function get_latest_answer(int $ticketid, int $userid = 0): ?\stdClass {
        global $DB;

        $params = ['ticketid' => $ticketid];
        if ($userid > 0) {
            $params['userid'] = $userid;
        }

        $records = $DB->get_records('local_th_qaa_answer', $params, 'id DESC', '*', 0, 1);

        return $records ? reset($records) : null;
    }

    private static function get_ticket_with_qaapair(int $ticketid): \stdClass {
        global $DB;

        $sql = "SELECT t.*,
                       qp.qaaid,
                       qp.studentid,
                       q.course AS courseid
                  FROM {local_th_qaa_ticket} t
                  JOIN {qaapairs} qp ON qp.id = t.qaapairid
                  JOIN {qaa} q ON q.id = qp.qaaid
                 WHERE t.id = :ticketid";

        return $DB->get_record_sql($sql, [
            'ticketid' => $ticketid,
        ], MUST_EXIST);
    }

    private static function copy_answer_files_to_qaa(
        int $ticketid,
        int $childid,
        \context_module $modulecontext
    ): void {
        $sourcecontext = \context_system::instance();
        $fs = get_file_storage();

        $sourcefiles = $fs->get_area_files(
            $sourcecontext->id,
            'local_th_qaa_manager',
            'answer',
            $ticketid,
            'filepath ASC, filename ASC',
            false
        );

        // Đảm bảo vùng đích không còn file cũ.
        $fs->delete_area_files(
            $modulecontext->id,
            'mod_qaa',
            'message',
            $childid
        );

        foreach ($sourcefiles as $sourcefile) {
            if ($sourcefile->is_directory()) {
                continue;
            }

            $filerecord = [
                'contextid' => $modulecontext->id,
                'component' => 'mod_qaa',
                'filearea' => 'message',
                'itemid' => $childid,
                'filepath' => $sourcefile->get_filepath(),
                'filename' => $sourcefile->get_filename(),
                'userid' => $sourcefile->get_userid(),
                'author' => $sourcefile->get_author(),
                'license' => $sourcefile->get_license(),
                'source' => $sourcefile->get_source(),
                'timecreated' => $sourcefile->get_timecreated(),
                'timemodified' => time(),
            ];

            $fs->create_file_from_storedfile(
                $filerecord,
                $sourcefile
            );
        }
    }

    public static function approve_answer(
        int $ticketid,
        int $reviewerid
    ): int {
        global $DB;

        $ticket = self::get_ticket_with_qaapair($ticketid);
        $answer = self::get_latest_answer($ticketid);

        if (!$answer) {
            throw new \moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        if ((int)$ticket->status !== ticket_service::STATUS_ANSWERED) {
            throw new \moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        if ((int)$answer->status !== self::STATUS_SUBMITTED) {
            throw new \moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        $cm = get_coursemodule_from_instance(
            'qaa',
            $ticket->qaaid,
            $ticket->courseid,
            false,
            MUST_EXIST
        );

        $modulecontext = \context_module::instance($cm->id);

        $now = time();
        $childid = 0;

        $transaction = $DB->start_delegated_transaction();

        try {
            // Công khai câu hỏi gốc.
            $DB->update_record('qaapairs', (object)[
                'id' => $ticket->qaapairid,
                'status' => 1,
            ]);

            /*
            * Tạo câu trả lời chính thức trong mod_qaa.
            *
            * Nội dung $answer->content phải giữ nguyên các URL dạng:
            * @@PLUGINFILE@@/ten-anh.png
            */
            $child = (object)[
                'qaaid' => $ticket->qaaid,
                'parentid' => $ticket->qaapairid,
                'studentid' => null,
                'teacherid' => $answer->userid,
                'message' => $answer->content,
                'status' => 1,
                'timecreatedanswer' => $now,
                'timemodifiedanswer' => $now,
            ];

            $childid = (int)$DB->insert_record(
                'qaapairs',
                $child,
                true
            );

            /*
            * Sao chép ảnh sang đúng filearea của mod_qaa.
            *
            * itemid phải là childid, vì qaa_pluginfile() sẽ tìm file
            * theo ID bản ghi câu trả lời trong bảng qaapairs.
            */
            self::copy_answer_files_to_qaa(
                $ticketid,
                $childid,
                $modulecontext
            );

            $DB->update_record('local_th_qaa_answer', (object)[
                'id' => $answer->id,
                'status' => self::STATUS_APPROVED,
                'rejectreason' => null,
                'timemodified' => $now,
            ]);

            $DB->update_record('local_th_qaa_ticket', (object)[
                'id' => $ticketid,
                'status' => ticket_service::STATUS_CLOSED,
                'rejectreason' => null,
                'reviewerid' => $reviewerid,
                'timemodified' => $now,
            ]);

            if (class_exists('\\mod_qaa\\event\\qaapairs_answered')) {
                $course = $DB->get_record(
                    'course',
                    ['id' => $ticket->courseid],
                    '*',
                    MUST_EXIST
                );

                $moduleinstance = $DB->get_record(
                    'qaa',
                    ['id' => $ticket->qaaid],
                    '*',
                    MUST_EXIST
                );

                $event = \mod_qaa\event\qaapairs_answered::create([
                    'objectid' => $childid,
                    'context' => $modulecontext,
                ]);

                $event->add_record_snapshot(
                    'course',
                    $course
                );

                $event->add_record_snapshot(
                    'qaa',
                    $moduleinstance
                );

                $event->trigger();
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            /*
            * File API không nằm trong transaction database.
            * Nếu có lỗi sau khi copy, cần xóa file đã tạo tại vùng QAA.
            */
            if ($childid > 0) {
                $fs = get_file_storage();

                $fs->delete_area_files(
                    $modulecontext->id,
                    'mod_qaa',
                    'message',
                    $childid
                );
            }

            $transaction->rollback($e);
            throw $e;
        }

        notification_service::queue_answer_approved_notification(
            $ticketid
        );

        return $childid;
    }

    public static function reject_answer(
        int $ticketid,
        int $reviewerid,
        string $reason = ''
    ): void {
        global $DB;

        $ticket = $DB->get_record(
            'local_th_qaa_ticket',
            ['id' => $ticketid],
            '*',
            MUST_EXIST
        );

        $answer = self::get_latest_answer($ticketid);

        if (!$answer) {
            throw new \moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        if ((int)$ticket->status !== ticket_service::STATUS_ANSWERED) {
            throw new \moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        if ((int)$answer->status !== self::STATUS_SUBMITTED) {
            throw new \moodle_exception(
                'invalidticket',
                'local_th_qaa_manager'
            );
        }

        $reason = trim($reason);
        $now = time();

        /*
        * Giữ người đang được giao.
        *
        * Trường hợp assigneduserid bị thiếu do dữ liệu cũ,
        * sử dụng người đã gửi câu trả lời gần nhất.
        */
        // $assigneduserid = !empty($ticket->assigneduserid)
        //     ? (int)$ticket->assigneduserid
        //     : (int)$answer->userid;

        $transaction = $DB->start_delegated_transaction();

        try {
            $DB->update_record('local_th_qaa_answer', (object)[
                'id' => $answer->id,
                'status' => self::STATUS_REJECTED,
                'rejectreason' => $reason !== '' ? $reason : null,
                'timemodified' => $now,
            ]);

            /*
            * Đây là trạng thái từ chối câu trả lời, không phải từ chối câu hỏi.
            *
            * Ticket vẫn giữ assigneduserid để CTV có thể chỉnh sửa
            * và gửi lại câu trả lời.
            */
            $DB->update_record('local_th_qaa_ticket', (object)[
                'id' => $ticketid,
                'status' => ticket_service::STATUS_ANSWER_REJECTED,
                'rejectreason' => $reason !== '' ? $reason : null,
                'timemodified' => $now,
            ]);
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }
}