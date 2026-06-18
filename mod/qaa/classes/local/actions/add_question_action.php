<?php
namespace mod_qaa\local\actions;

defined('MOODLE_INTERNAL') || die();

use context_module;
use moodle_url;
use grade_item;
use coding_exception;
use context_course;

/**
 * Handle adding a root question (student).
 *
 * Usage (from view.php):
 *   $action = new \mod_qaa\local\actions\add_question_action($DB);
 *   $returnurl = $action->execute($fromform, $cm, $course, $modulecontext, $moduleinstance);
 *   redirect($returnurl);
 */
class add_question_action {
    /** @var \moodle_database */
    private $db;

    public function __construct(\moodle_database $db) {
        $this->db = $db;
    }

    /**
     * Execute the add question flow.
     *
     * @param \stdClass     $fromform        Form data from question_form (fields: question[text,itemid], isedit=false)
     * @param \cm_info      $cm
     * @param \stdClass     $course
     * @param context_module $modulecontext
     * @param \stdClass     $moduleinstance  Row from {qaa}
     * @return moodle_url
     * @throws \moodle_exception
     */
    public function execute(\stdClass $fromform,
                            \stdClass $cm,
                            \stdClass $course,
                            context_module $modulecontext,
                            \stdClass $moduleinstance): moodle_url {
        global $USER, $SESSION, $CFG;

        require_capability('mod/qaa:makequest', $modulecontext);

        // Lấy nội dung từ editor.
        $rawhtml = (string)($fromform->question['text'] ?? '');
        $draftid = (int)($fromform->question['itemid'] ?? 0);
        if (trim($rawhtml) === '') {
            return new moodle_url('/mod/qaa/view.php', ['id' => $cm->id, 'embed' => 1]);
        }

        $now = time();

        // Ghi file từ draft vào filearea 'message' trước, rồi rewrite URL.
        file_save_draft_area_files($draftid, $modulecontext->id, 'mod_qaa', 'message', 0);
        // Sau khi có itemid thực (id bản ghi mới) chúng ta sẽ save lại một lần nữa để set đúng itemid.

        $transaction = $this->db->start_delegated_transaction();
        try {
            $isTeacher = has_capability('mod/qaa:approval', $modulecontext);
            // Tạo bản ghi root (câu hỏi gốc từ HV).
            $rec = (object)[
                'qaaid'                 => $cm->instance,
                'parentid'              => null,
                'studentid'             => $USER->id,
                'teacherid'             => null,
                'message'               => '',   // set sau khi rewrite
                'status'                => $isTeacher ? 1 : 0,    // pending duyệt (tuỳ policy có thể auto=1)
                'timecreatedquestion'   => $now,
                'timemodifiedquestion'  => $now,
            ];
            $rootid = (int)$this->db->insert_record('qaapairs', $rec, true);

            // Lưu lại file đúng itemid thực sự.
            file_save_draft_area_files($draftid, $modulecontext->id, 'mod_qaa', 'message', $rootid);

            // Rewrite pluginfile URL & cập nhật message.
            $question = file_rewrite_urls_to_pluginfile($rawhtml, $draftid);
            $this->db->update_record('qaapairs', (object)[
                'id' => $rootid,
                'message' => $question
            ]);

            // Event (nếu có event "created"; nếu chưa định nghĩa có thể bỏ).
            if (class_exists('\\mod_qaa\\event\\qaapairs_created')) {
                $event = \mod_qaa\event\qaapairs_created::create(array(
                    'objectid' => $rootid,
                    'context' => $modulecontext
                ));
                $event->add_record_snapshot('course', $course);
                $event->add_record_snapshot('qaa', $moduleinstance);
                $event->trigger();
            }

            $SESSION->mod_qaa = ['mod_qaakey' => time()];
            $transaction->allow_commit();

            return new moodle_url('/mod/qaa/view.php', ['id' => $cm->id, 'embed' => 1]);

        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }
}
