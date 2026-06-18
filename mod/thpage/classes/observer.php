<?php

defined('MOODLE_INTERNAL') || die();

class mod_thpage_observer {

    /**
     * Store all actions about modules create/update/delete in own table.
     *
     * @param \core\event\base $event
     */
    public static function pdf_created(\core\event\base $event) {

        $data = array(
            'course' => $event->courseid,
            'thpageid' => $event->objectid,
            'contextid' => $event->contextid,
            'cmid' => $event->other['cmid'],
            // 'type' => 1
        );

        $task = new mod_thpage\task\create_pdf_adhoc_task();
        $task->set_custom_data($data);
        \core\task\manager::queue_adhoc_task($task);

    }

    // public static function user_enrolment_pdf_created(\core\event\base $event) {

    //     $data = array(
    //         'course' => $event->courseid,
    //         'type' => 0,
    //     );

    //     $task = new mod_thpage\task\create_pdf_adhoc_task();
    //     $task->set_custom_data($data);
    //     \core\task\manager::queue_adhoc_task($task);
    // }
}