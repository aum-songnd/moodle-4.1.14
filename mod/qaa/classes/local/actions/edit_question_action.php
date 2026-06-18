<?php
namespace mod_qaa\local\actions;

defined('MOODLE_INTERNAL') || die();

use context_module;
use moodle_url;

/**
 * Handle editing a root question by the student (only if no teacher reply).
 *
 * Usage:
 *   $action = new \mod_qaa\local\actions\edit_question_action($DB);
 *   $returnurl = $action->execute($fromform, $cm, $modulecontext);
 *   redirect($returnurl);
 */
class edit_question_action {
    /** @var \moodle_database */
    private $db;

    public function __construct(\moodle_database $db) {
        $this->db = $db;
    }

    /**
     * @param \stdClass      $fromform     From question_form (fields: questionid, question[text,itemid], isedit=true)
     * @param \cm_info       $cm
     * @param context_module $modulecontext
     * @return moodle_url
     * @throws \moodle_exception
     */
    public function execute(\stdClass $fromform,
                            \stdClass $cm,
                            context_module $modulecontext): moodle_url {
        global $USER, $DB;

        require_sesskey();
        require_capability('mod/qaa:makequest', $modulecontext);
        
        $rootid  = (int)($fromform->questionid ?? 0);
        $draftid = (int)($fromform->question['itemid'] ?? 0);
        $rawhtml = (string)($fromform->question['text'] ?? '');
        
        $hasgvreply = $this->db->record_exists_select('qaapairs', 'parentid = ?', [$rootid]);
        if ($hasgvreply) {
            throw new \moodle_exception('cannoteditquestionwithreply', 'mod_qaa');
        }

        // Lưu file draft vào itemid của root, rồi rewrite nội dung.
        file_save_draft_area_files($draftid, $modulecontext->id, 'mod_qaa', 'message', $rootid);
        $question = file_rewrite_urls_to_pluginfile($rawhtml, $draftid);

        $this->db->update_record('qaapairs', (object)[
            'id'                     => $rootid,
            'message'                => $question,
            'timemodifiedquestion'   => time()
        ]);

        $moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course));

        // Trigger event giữ nguyên tên cũ để tương thích thống kê.
        if (class_exists('\\mod_qaa\\event\\qaapairs_question_edited')) {
            $event = \mod_qaa\event\qaapairs_question_edited::create([
                'objectid' => $rootid,
                'context'  => $modulecontext,
            ]);
            $event->add_record_snapshot('course', $course);
			$event->add_record_snapshot('qaa', $moduleinstance);
            $event->trigger();
        }

        return new moodle_url('/mod/qaa/view.php', ['id' => $cm->id, 'questionid' => $rootid, 'embed' => 1]);
    }
}
