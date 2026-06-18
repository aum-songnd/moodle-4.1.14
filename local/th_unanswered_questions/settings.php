<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_th_unanswered_questions',
        get_string('pluginname', 'local_th_unanswered_questions')
    );

    $settings->add(new admin_setting_configtext(
        'local_th_unanswered_questions/monthlimit',
        get_string('monthlimit', 'local_th_unanswered_questions'),
        get_string('monthlimit_desc', 'local_th_unanswered_questions'),
        2,          // mặc định 2 tháng
        PARAM_INT
    ));

    $ADMIN->add('localplugins', $settings);
}
