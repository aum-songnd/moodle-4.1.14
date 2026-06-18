<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_th_essay_checker_report', get_string('pluginname', 'local_th_essay_checker_report'));
    $ADMIN->add('localplugins', $settings);

    $configs   = array();

    $configs[] = new admin_setting_configtext(
        'local_th_essay_checker_report/email',
        get_string('email_setting', 'local_th_essay_checker_report'),
        get_string('email_setting_desc', 'local_th_essay_checker_report'),
        '',
        PARAM_NOTAGS
    );    

    // Tạo mảng giờ: từ 0 đến 23 (định dạng có số 0 đầu nếu cần)
    $hour = array();
    for ($i = 0; $i <= 23; $i++) {
        $key = ($i < 10) ? '0' . $i : $i;
        $hour[$key] = get_string('hour_label', 'local_th_essay_checker_report', $i);
    }
    
    $configs[] = new admin_setting_configselect(
        'local_th_essay_checker_report/hour',
        get_string('select_hour', 'local_th_essay_checker_report'),
        get_string('select_hour_desc', 'local_th_essay_checker_report'),
        22,  // Giá trị mặc định: 22 (22:00 hay 10 PM)
        $hour
    );    

    //add config
    foreach ($configs as $config) {
        $config->plugin = 'local_th_essay_checker_report';
        $settings->add($config);
    }
}