<?php

defined('MOODLE_INTERNAL') || die;
global $COURSE;
$context = context_course::instance($COURSE->id);

if (has_capability('local/th_search_courses:seeallthings', $context)) {
    $parent = $ADMIN->locate('courses');
    if ($parent) {
        $parent->add('courses', new admin_externalpage('th_search_courses', get_string('pluginname', 'local_th_search_courses'), "$CFG->wwwroot/local/th_search_courses/view.php", 'local/th_search_courses:seeallthings'));
    }
}

?>
