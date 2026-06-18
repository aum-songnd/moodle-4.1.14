<?php
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_th_sectionmanager_course_get_contents' => array(
        'classname'     => 'local_th_sectionmanager_external',
        'methodname'    => 'get_course_contents',
        'classpath'     => 'local/th_sectionmanager/externallib.php',
        'description'   => 'Lấy nội dung khóa học, có kèm theo section_type.',
        'type'          => 'read',
        'ajax'          => true,
        'services' => ['moodle_mobile_app']
    )
);