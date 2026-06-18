<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Thêm link vào navigation của course
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context_course $context
 */
function local_th_sectionmanager_extend_navigation_course(navigation_node $navigation, stdClass $course, context_course $context) {
    
    if (!has_capability('local/th_sectionmanager:manage', $context)) {
        return;
    }

    $url = new moodle_url('/local/th_sectionmanager/manage.php', [
        'courseid' => $course->id
    ]);

    $navigation->add(
        get_string('pluginname', 'local_th_sectionmanager'), 
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_th_sectionmanager'
    );
}