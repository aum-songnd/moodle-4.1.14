<?php
declare(strict_types=1);

namespace mod_resource\completion;

defined('MOODLE_INTERNAL') || die();

if (!class_exists('core_completion\activity_custom_completion')) {
    return;
}

use core_completion\activity_custom_completion;

class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        if ($rule != 'completionpercent') {
            return COMPLETION_UNKNOWN;
        }

        $userid = $this->userid;
        $cm     = $this->cm;

        $resource = $DB->get_record('resource', ['id' => $cm->instance], 'completionpercent');
        if (empty($resource->completionpercent)) {
            return COMPLETION_COMPLETE;
        }

        $progress = $DB->get_record_sql(
            "SELECT MAX(percent) as maxpercent
               FROM {resource_video_view}
              WHERE cm_id = :cmid AND user_id = :userid",
            ['cmid' => $cm->id, 'userid' => $userid]
        );

        if ($progress && (int)$progress->maxpercent >= (int)$resource->completionpercent) {
            return COMPLETION_COMPLETE;
        }

        return COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     */
    public static function get_defined_custom_rules(): array {
        return ['completionpercent'];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;
        $cm = $this->cm;
        $resource = $DB->get_record('resource', ['id' => $cm->instance], 'completionpercent');

        return [
            'completionpercent' => get_string('completiondetail:percent', 'mod_resource',
                $resource->completionpercent ?? 0),
        ];
    }

    /**
     * Show the manual completion or not regardless of the course's showcompletionconditions setting.
     */
    public function manual_completion_always_shown(): bool {
        $display = $this->cm->customdata['display'] ?? null;

        $displaytypes = [
                RESOURCELIB_DISPLAY_NEW,
                RESOURCELIB_DISPLAY_OPEN,
                RESOURCELIB_DISPLAY_DOWNLOAD,
                RESOURCELIB_DISPLAY_POPUP
        ];

        return in_array($display, $displaytypes);
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionusegrade',
            'completionpercent',
        ];
    }
}