<?php
defined('MOODLE_INTERNAL') || die();
if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_th_exam_grade_sync',
        get_string('pluginname', 'local_th_exam_grade_sync')
    );

    $settings->add(new admin_setting_configtext(
        'local_th_exam_grade_sync/categoryidnumber',
        get_string('category_idnumber', 'local_th_exam_grade_sync'),
        get_string('category_idnumber_desc', 'local_th_exam_grade_sync'),
        'THI', PARAM_TEXT
    ));
    
    $settings->add(new admin_setting_configtext(
        'local_th_exam_grade_sync/category_nhkt',
        get_string('category_nhkt', 'local_th_exam_grade_sync'),
        get_string('category_nhkt_desc', 'local_th_exam_grade_sync'),
        'NHKT', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_exam_grade_sync/gradeitem',
        get_string('grade_item', 'local_th_exam_grade_sync'),
        get_string('grade_item_desc', 'local_th_exam_grade_sync'),
        'thi', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_exam_grade_sync/gradeitem_pk',
        'Grade item phúc khảo',
        'Idnumber của điểm phúc khảo',
        'pk'
    ));
    
    $ADMIN->add('localplugins', $settings);
}
