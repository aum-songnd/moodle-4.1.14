<?php
namespace mod_resource\external;

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use stdClass;

require_once("$CFG->libdir/externallib.php");

class save_video_progress extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'currenttime' => new external_value(PARAM_INT, 'Current time in seconds'),
            'duration' => new external_value(PARAM_INT, 'Duration in seconds'),
            'percent' => new external_value(PARAM_INT, 'Percentage viewed')
        ]);
    }

    public static function execute($cmid, $userid, $currenttime, $duration, $percent) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'userid' => $userid,
            'currenttime' => $currenttime,
            'duration' => $duration,
            'percent' => $percent
        ]);

        // Truy vấn xem học viên đã có bản ghi tiến trình video này chưa
        $record = $DB->get_record('resource_video_view', [
            'cm_id' => $params['cmid'], 
            'user_id' => $params['userid']
        ]);

        if ($record) {
            $record->currenttime = $params['currenttime'];
            $record->duration = $params['duration'];
            if ($params['percent'] > $record->percent) {
                $record->percent = $params['percent'];
            }
            $record->timemodified = time();
            $DB->update_record('resource_video_view', $record);
        } else {
            $newrecord = new stdClass();
            $newrecord->cm_id = $params['cmid'];
            $newrecord->user_id = $params['userid'];
            $newrecord->currenttime = $params['currenttime'];
            $newrecord->duration = $params['duration'];
            $newrecord->percent = $params['percent'];
            $newrecord->timecreated = time();
            $newrecord->timemodified = time();
            $DB->insert_record('resource_video_view', $newrecord);
        }

        return ['status' => true];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Success status')
        ]);
    }
}