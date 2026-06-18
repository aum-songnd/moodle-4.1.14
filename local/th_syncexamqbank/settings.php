<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // Chỉ hiển thị cho quản trị viên
    $settings = new admin_settingpage(
        'local_th_syncexamqbank',
        get_string('pluginname', 'local_th_syncexamqbank')
    );

    // --- Danh mục NHKT ---
    $settings->add(new admin_setting_configtext(
        'local_th_syncexamqbank/category_nhkt_idnumber',
        get_string('setting_category_nhkt', 'local_th_syncexamqbank'),
        get_string('setting_category_nhkt_desc', 'local_th_syncexamqbank'),
        'NHKT', // Giá trị mặc định
        PARAM_RAW_TRIMMED
    ));

    // --- Danh mục THI ---
    $settings->add(new admin_setting_configtext(
        'local_th_syncexamqbank/category_thi_idnumber',
        get_string('setting_category_thi', 'local_th_syncexamqbank'),
        get_string('setting_category_thi_desc', 'local_th_syncexamqbank'),
        'THI',
        PARAM_RAW_TRIMMED
    ));
    // ID number cho danh mục câu hỏi Dễ.
    $settings->add(new admin_setting_configtext(
        'local_th_syncexamqbank/category_easy_idnumber',
        get_string('setting_category_easy', 'local_th_syncexamqbank'),
        get_string('setting_category_easy_desc', 'local_th_syncexamqbank'),
        'EASY',
        PARAM_RAW_TRIMMED
    ));

    // ID number cho danh mục câu hỏi Trung bình.
    $settings->add(new admin_setting_configtext(
        'local_th_syncexamqbank/category_medium_idnumber',
        get_string('setting_category_medium', 'local_th_syncexamqbank'),
        get_string('setting_category_medium_desc', 'local_th_syncexamqbank'),
        'MEDIUM',
        PARAM_RAW_TRIMMED
    ));

    // ID number cho danh mục câu hỏi Khó.
    $settings->add(new admin_setting_configtext(
        'local_th_syncexamqbank/category_hard_idnumber',
        get_string('setting_category_hard', 'local_th_syncexamqbank'),
        get_string('setting_category_hard_desc', 'local_th_syncexamqbank'),
        'HARD',
        PARAM_RAW_TRIMMED
    ));

    $ADMIN->add('localplugins', $settings);
}
