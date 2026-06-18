<?php
defined('MOODLE_INTERNAL') || die();
if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_th_framework_study',
        get_string('pluginname', 'local_th_framework_study')
    );

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/train_code1',
        get_string('train_code', 'local_th_framework_study'),
        get_string('train_code_desc', 'local_th_framework_study'),
        'th_train_code', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/train_code2',
        get_string('train_code2', 'local_th_framework_study'),
        get_string('train_code_desc2', 'local_th_framework_study'),
        'th_train_code2', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/study_field1',
        get_string('study_field1_name', 'local_th_framework_study'),
        get_string('study_field1_desc', 'local_th_framework_study'),
        'th_study_field', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/study_field2',
        get_string('study_field2_name', 'local_th_framework_study'),
        get_string('study_field2_desc', 'local_th_framework_study'),
        'th_study_field2', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/default_major1',
        get_string('default_major1', 'local_th_framework_study'),
        get_string('default_major1_desc', 'local_th_framework_study'),
        'th_major_code', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/default_target1',
        get_string('default_target1', 'local_th_framework_study'),
        get_string('default_target1_desc', 'local_th_framework_study'),
        'th_target_code', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/default_major2',
        get_string('default_major2', 'local_th_framework_study'),
        get_string('default_major2_desc', 'local_th_framework_study'),
        'th_major_code2', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/default_target2',
        get_string('default_target2', 'local_th_framework_study'),
        get_string('default_target2_desc', 'local_th_framework_study'),
        'th_target_code2', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/tvhl',
        get_string('tvhl', 'local_th_framework_study'),
        get_string('tvhl_desc', 'local_th_framework_study'),
        'TVHL', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/coursecode',
        get_string('coursecode', 'local_th_framework_study'),
        get_string('coursecode_desc', 'local_th_framework_study'),
        'th_course_code', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_th_framework_study/enable_train_code',
        get_string('enabletraincode', 'local_th_framework_study'),
        get_string('enabletraincode_desc', 'local_th_framework_study'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_th_framework_study/skip_slot_target',
        get_string('skip_slot_target', 'local_th_framework_study'),
        get_string('skip_slot_target_desc', 'local_th_framework_study'),
        0
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_framework_study/excluded_category_idnumber',
        get_string('excludedcategoryidnumber', 'local_th_framework_study'),
        get_string('excludedcategoryidnumber_desc', 'local_th_framework_study'),
        'THI,NHKT',
        PARAM_TEXT
    ));
    
    $ADMIN->add('localplugins', $settings);
}
