<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/adminlib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_th_userdownloadctl',
        get_string('pluginname', 'local_th_userdownloadctl')
    );

    $settings->add(new admin_setting_configcheckbox(
        'local_th_userdownloadctl/enable',
        get_string('enable', 'local_th_userdownloadctl'),
        get_string('enable_desc', 'local_th_userdownloadctl'),
        0
    ));

    $ADMIN->add('localplugins', $settings);
}
