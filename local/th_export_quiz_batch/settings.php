<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_export_quiz_batch', get_string('pluginname', 'local_th_export_quiz_batch'));

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_th_export_quiz_batch/categoryidnumber',
        get_string('categoryidnumber', 'local_th_export_quiz_batch'),
        get_string('categoryidnumber_desc', 'local_th_export_quiz_batch'),
        'THI',
        PARAM_ALPHANUMEXT
    ));
}
