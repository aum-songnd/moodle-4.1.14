<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext(
        'block_th_exam_schedule/student_code',
        get_string('student_code', 'block_th_exam_schedule'),
        get_string('student_code_desc', 'block_th_exam_schedule'),
        'th_student_code', PARAM_TEXT
    ));
    
}