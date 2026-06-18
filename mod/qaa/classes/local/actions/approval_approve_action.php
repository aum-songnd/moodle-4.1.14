<?php
namespace mod_qaa\local\actions;

require_once($CFG->dirroot . '/mod/qaa/classes/local/repository/qaapairs_repository.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/service/feed_service.php');
require_once($CFG->dirroot . '/mod/qaa/classes/local/mapper/comment_mapper.php');
defined('MOODLE_INTERNAL') || die();

use grade_item;
use mod_qaa\local\repository\qaapairs_repository;
use mod_qaa\local\service\feed_service;

class approval_approve_action {
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

        $repo  = new qaapairs_repository($DB);
        $feed  = new feed_service($repo);
        $roots_all = $feed->get_feed_tree($cm->instance, $context);
        $result = qaa_find_root_for_id($roots_all, (int)$data->questionid);
        $parents = $result['root'];

        $rootid  = (int)$data->questionid;
        $draftid = (int)($data->content['itemid'] ?? 0);
        $rawhtml = (string)($data->content['text'] ?? '');
        $now     = time();

        $root = $this->db->get_record('qaapairs', ['id'=>$rootid,'qaaid'=>$cm->instance], '*', MUST_EXIST);

        // Approve root
        $this->db->update_record('qaapairs', (object)[
            'id' => $rootid,
            'status' => 1
        ]);

        $moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course));

        // Create teacher reply (child)
        $child = (object)[
            'qaaid'             => $cm->instance,
            'parentid'          => $parents->id,
            'studentid'         => null,
            'teacherid'         => $USER->id,
            'message'           => '',
            'status'            => 1,
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

        // Event (nếu có)
        if (class_exists('\\mod_qaa\\event\\qaapairs_answered')) {
            $event = \mod_qaa\event\qaapairs_answered::create(['objectid'=>$childid,'context'=>$context]);
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('qaa', $moduleinstance);
            $event->trigger();
        }

        // Cập nhật điểm (đếm số câu hỏi theo luồng của HV).
        $gradeitem = grade_item::fetch([
            'itemtype'    => 'mod',
            'itemmodule'  => $cm->modname,
            'iteminstance'=> $cm->instance,
            'itemnumber'  => 0,
            'courseid'    => $course->id
        ]);
        if ($gradeitem) {
            $count = $this->db->count_records('qaapairs', [
                'qaaid' => $cm->instance, 'studentid' => $root->studentid, 'status' => 1
            ]);

            $rawgrade = min($count, (float)$gradeitem->grademax);
            $grade = (object)[
                'userid'        => $root->studentid,
                'rawgrade'      => $rawgrade,
                'dategraded'    => $now,
                'datesubmitted' => $now
            ];
            // Hàm từ lib.php của plugin.
            if (function_exists('qaa_grade_item_update')) {
                qaa_grade_item_update($moduleinstance, [$root->studentid => $grade]);
            }
        }

        return new \moodle_url('/mod/qaa/approval.php', ['id'=>$cm->id]);
    }
}
