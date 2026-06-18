<?php
namespace mod_qaa\local\actions;

defined('MOODLE_INTERNAL') || die();

use context_module;
use moodle_url;

/**
 * Handle adding a reply (answer or student follow-up).
 *
 * Usage:
 *   $action = new \mod_qaa\local\actions\add_answer_action($DB);
 *   $returnurl = $action->execute($fromform, $cm, $modulecontext, $questionid, $replytomsg);
 *   redirect($returnurl);
 */
class add_answer_action {
    /** @var \moodle_database */
    private $db;

    public function __construct(\moodle_database $db) {
        $this->db = $db;
    }

    /**
     * @param \stdClass      $fromform     From answer_form (fields: answer[text,itemid])
     * @param \cm_info       $cm
     * @param context_module $modulecontext
     * @param int            $questionid   Root question id (UI context)
     * @param int            $replytomsg   Parent comment id
     * @return moodle_url
     * @throws \moodle_exception
     */
    public function execute(\stdClass $fromform,
                            \stdClass $cm,                 // <— đổi từ \cm_info
                            \context_module $modulecontext,
                            int $questionid,
                            int $replytomsg): \moodle_url {
        global $USER, $DB;

        require_sesskey();
        // Cả GV (answerquest) lẫn HV (makequest) đều có thể reply.
        if (!has_any_capability(['mod/qaa:makequest'], $modulecontext)) {
            throw new \required_capability_exception($modulecontext, 'mod/qaa:makequest', 'nopermissions', '');
        }

        $rawhtml = (string)($fromform->answer['text'] ?? '');
        $draftid = (int)($fromform->answer['itemid'] ?? 0);
        $now     = time();

        // Parent phải thuộc activity này.
        $parent = $this->db->get_record('qaapairs', ['id' => $replytomsg, 'qaaid' => $cm->instance], '*', MUST_EXIST);

        $isTeacher = has_capability('mod/qaa:approval', $modulecontext);
        $authorStudentId = $isTeacher ? null : $USER->id;
        $authorTeacherId = $isTeacher ? $USER->id : null;

        // Tạo child (message rỗng, set sau khi xử lý file/rewrite).
        $child = (object)[
            'qaaid'             => $cm->instance,
            'parentid'          => $replytomsg,
            'studentid'         => $authorStudentId,
            'teacherid'         => $authorTeacherId,
            'message'           => '',
            'status'            => $isTeacher ? 1 : 0, // GV auto-approve, HV pending
            'timecreatedanswer' => $now,
            'timemodifiedanswer'=> $now,
        ];

        $childid = (int)$this->db->insert_record('qaapairs', $child, true);

        // Lưu file + rewrite.
        file_save_draft_area_files($draftid, $modulecontext->id, 'mod_qaa', 'message', $childid);
        $processed = file_rewrite_urls_to_pluginfile($rawhtml, $draftid);

        $this->db->update_record('qaapairs', (object)[
            'id' => $childid,
            'message' => $processed,
            'timemodifiedanswer' => time()
        ]);

        // $draftitemid = $fromform->attachments;
        // file_save_draft_area_files($draftitemid, $modulecontext->id, 'mod_qaa', 'attachments', $childid);

        $moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course));

        // Event
        if (class_exists('\\mod_qaa\\event\\qaapairs_answered')) {
            $event = \mod_qaa\event\qaapairs_answered::create([
                'objectid' => $childid,
                'context'  => $modulecontext
            ]);
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('qaa', $moduleinstance);
            $event->trigger();
        }

        return new moodle_url('/mod/qaa/view.php', ['id' => $cm->id, 'questionid' => $questionid, 'embed' => 1]);
    }
}
