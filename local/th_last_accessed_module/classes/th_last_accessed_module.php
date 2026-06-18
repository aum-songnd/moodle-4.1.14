<?php
namespace local_th_last_accessed_module;

defined('MOODLE_INTERNAL') || die();

use context_course;

class th_last_accessed_module {

    public static function get_last_accessed_module(int $courseid, int $userid): array {
        global $DB;

        // ✅ Check course tồn tại (TRÁNH CRASH)
        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course) {
            return [
                'status' => false,
                'message' => 'Course not found',
            ];
        }

        // ✅ Validate context
        $context = context_course::instance($courseid);
        self::validate_context($context);

        // ==============================
        // 🔥 CHECK FIRST ACCESS
        // ==============================
        $hasLog = $DB->record_exists('logstore_standard_log', [
            'userid' => $userid,
            'courseid' => $courseid
        ]);

        $lastaccess = $DB->get_record('user_lastaccess', [
            'userid' => $userid,
            'courseid' => $courseid
        ]);

        $isFirstAccess = !$hasLog && !$lastaccess;

        // ==============================
        // 🔍 LẤY MODULE GẦN NHẤT
        // ==============================
        $record = $DB->get_record_sql("
        SELECT contextinstanceid, timecreated
        FROM {logstore_standard_log}
        WHERE userid = ?
          AND courseid = ?
          AND action = 'viewed'
          AND target = 'course_module'
          AND component LIKE 'mod_%'
        ORDER BY timecreated DESC
        LIMIT 1
    ", [$userid, $courseid]);

      // ❗ Không có activity
        if (!$record) {
            return [
                'status' => true,
                'isfirstaccess' => $isFirstAccess,
                'message' => 'No activity found',
            ];
        }

        // ==============================
        // 🔗 MAP CMID → MODULE
        // ==============================
        $cmid = $record->contextinstanceid;
        
        $cm = get_coursemodule_from_id(null, $cmid, $courseid, false, IGNORE_MISSING);

        if (!$cm) {
            return [
                'status' => false,
                'isfirstaccess' => $isFirstAccess,
                'message' => 'Course module not found',
            ];
        }

        $module = $DB->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
        $instance = $DB->get_record($module->name, ['id' => $cm->instance]);

        $name = $instance->name ?? '';
        $url = (new \moodle_url('/mod/' . $module->name . '/view.php', [
            'id' => $cm->id,
        ]))->out(false);
        return [
            'status' => true,
            'isfirstaccess' => $isFirstAccess,
            'data' => [
                'cmid' => $cm->id,
                'courseid' => $cm->course,
                'modname' => $module->name,
                'instance' => $cm->instance,
                'name' => $name,
                'url' => $url,
                'timecreated' => $record->timecreated,
            ]
        ];
    }

    protected static function validate_context($context) {
        \external_api::validate_context($context);
    }
}