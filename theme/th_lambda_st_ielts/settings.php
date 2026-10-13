<?php

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Audio settings.
    $settings->add(new admin_setting_heading(
        'theme_th_lambda_st_ielts/audiosettings',
        get_string('audiosettings', 'theme_th_lambda_st_ielts'),
        ''
    ));

    // Resume offset (seconds).
    $settings->add(new admin_setting_configtext(
        'theme_th_lambda_st_ielts/audio_resume_offset',
        get_string('default_audio_resume_offset', 'theme_th_lambda_st_ielts'),
        get_string('default_audio_resume_offset', 'theme_th_lambda_st_ielts'),
        5,
        PARAM_INT,
        3
    ));
}