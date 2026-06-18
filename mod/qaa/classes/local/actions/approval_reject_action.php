<?php
namespace mod_qaa\local\actions;
defined('MOODLE_INTERNAL') || die();

use grade_item;

class approval_reject_action {
    /** @var \moodle_database */
    private $db;
    public function __construct(\moodle_database $db) { $this->db = $db; }

    /**
     * Phê duyệt root + tạo trả lời của GV (child)
     */
    public function execute(\stdClass $data, \stdClass $cm, \context_module $context): \moodle_url {
        global $USER, $DB;

        require_sesskey();
        require_capability('mod/qaa:approval', $context);

        $rootid  = (int)$data->questionid;
        $draftid = (int)($data->content['itemid'] ?? 0);
        $rawhtml = (string)($data->content['text'] ?? '');
        $now     = time();

        $root = $this->db->get_record('qaapairs', ['id'=>$rootid,'qaaid'=>$cm->instance], '*', MUST_EXIST);

        // Approve root
        $this->db->update_record('qaapairs', (object)[
            'id' => $rootid,
            'status' => 2
        ]);

        $moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course));

        // Create teacher reply (child)
        $child = (object)[
            'qaaid'             => $cm->instance,
            'parentid'          => $rootid,
            'studentid'         => null,
            'teacherid'         => $USER->id,
            'message'           => '',
            'status'            => 2,
            'timecreatedanswer' => $now,
            'timemodifiedanswer'=> $now
        ];
        $childid = (int)$this->db->insert_record('qaapairs', $child, true);

        // Files + rewrite + update
        if ($draftid) {
            file_save_draft_area_files($draftid, $context->id, 'mod_qaa', 'message', $childid);
            $processed = file_rewrite_urls_to_pluginfile($rawhtml, $draftid);
        } else {
            $processed = $rawhtml;
        }
        $this->db->update_record('qaapairs', (object)['id'=>$childid,'message'=>$processed,'timemodifiedanswer'=>time()]);

        $moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course));

        // Event (nếu có event "created"; nếu chưa định nghĩa có thể bỏ).
        if (class_exists('\\mod_qaa\\event\\qaapairs_reject')) {
            $event = \mod_qaa\event\qaapairs_reject::create(array(
                'objectid' => $rootid,
                'context' => $context
            ));
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('qaa', $moduleinstance);
            $event->trigger();
        }

        return new \moodle_url('/mod/qaa/approval.php', ['id'=>$cm->id]);
    }
}
