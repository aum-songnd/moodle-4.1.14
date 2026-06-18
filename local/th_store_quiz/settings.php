<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_th_store_quiz', get_string('pluginname', 'local_th_store_quiz'));
    $ADMIN->add('localplugins', $settings);

    $configs   = array();

    // Ghi chú giới thiệu chức năng.
    $configs[] = new admin_setting_heading(
        'local_th_store_quiz/description',
        '',
        html_writer::div(
            get_string('featuredesc', 'local_th_store_quiz'),
            'alert alert-info'
        )
    );

    $configs[] = new admin_setting_heading(
        'local_th_store_quiz/description1',
        get_string('settings', 'local_th_store_quiz'),
        ''
    );

    $configs[] = new admin_setting_configcheckbox(
        'active',
        get_string('turnon', 'local_th_store_quiz'),
        '',
        0
    );

    $days = array();
    $time = get_string('day', 'local_th_store_quiz');
    for($i=1;$i<=30;$i++){
    	$days[$i] = $i . $time;
    }
    $configs[] = new admin_setting_configselect(
        'deletion_date',
        get_string('timedeleted', 'local_th_store_quiz'),
        '',
        7,
        $days
    );

    $time_run = array();
    for($i=0;$i<=23;$i++){
    	$time_run[$i] = $i . ':00';
    }

    $configs[] = new admin_setting_configselect(
        'time_run',
        get_string('timeauto', 'local_th_store_quiz'),
        '',
        '2',
        $time_run
    );

    $day_run = array(
        0 => get_string('sunday', 'local_th_store_quiz'),
        1 => get_string('monday', 'local_th_store_quiz'),
        2 => get_string('tuesday', 'local_th_store_quiz'),
        3 => get_string('wednesday', 'local_th_store_quiz'),
        4 => get_string('thursday', 'local_th_store_quiz'),
        5 => get_string('friday', 'local_th_store_quiz'),
        6 => get_string('saturday', 'local_th_store_quiz')
    );
    
    $configs[] = new admin_setting_configselect(
        'day_run',
        get_string('dayauto', 'local_th_store_quiz'),
        '',
        '2',
        $day_run
    );

    $configs[] = new admin_setting_configtext(
        'get_quiz',
        get_string('enterquiz', 'local_th_store_quiz'),
        get_string('saveattempt', 'local_th_store_quiz'),
        2
    );

    $configs[] = new admin_setting_configtext(
        'local_th_store_quiz/course_startdate_from',
        get_string('course_startdate_from', 'local_th_store_quiz'),
        get_string('course_startdate_from_desc', 'local_th_store_quiz'),
        date('d-m-Y'),
        PARAM_TEXT
    );

    //add config
    foreach ($configs as $config) {
        $config->plugin = 'local_th_store_quiz';
        $settings->add($config);
    }
}
