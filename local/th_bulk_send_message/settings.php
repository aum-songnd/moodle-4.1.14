<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_th_bulk_send_message', get_string('pluginname', 'local_th_bulk_send_message'));
    $ADMIN->add('localplugins', $settings);

    $configs   = array();
    $configs[] = new admin_setting_heading('local_th_bulk_send_message/heading1', 'local_th_bulk_send_message', '');

    $configs[] = new admin_setting_configtext(
        'local_th_bulk_send_message/ma_hoc_vien',
        get_string('ma_hoc_vien', 'local_th_bulk_send_message'),
        get_string('ma_hoc_vien_desc', 'local_th_bulk_send_message'),
        '',
        PARAM_NOTAGS
    );

    $configs[] = new admin_setting_configtext(
        'local_th_bulk_send_message/ma_hoc_vien2',
        get_string('ma_hoc_vien2', 'local_th_bulk_send_message'),
        get_string('ma_hoc_vien2_desc', 'local_th_bulk_send_message'),
        '',
        PARAM_NOTAGS
    );

    $month = array();
    for($i=1;$i<=12;$i++){
    	$month[$i] = $i;
    }

    $time_run = array();
    for($i=0;$i<=23;$i++){
    	if ($i < 10) {
    		$time_run['0' . $i] = get_string('hour', 'local_th_bulk_send_message', $i);
    	} else {
    		$time_run[$i] = get_string('hour', 'local_th_bulk_send_message', $i);
    	}
    }

    $configs[] = new admin_setting_configselect(
        'local_th_bulk_send_message/month',
        get_string('delete_log_after', 'local_th_bulk_send_message'),
        '',
        6,
        $month
    );

    $configs[] = new admin_setting_configselect(
        'local_th_bulk_send_message/time_run',
        get_string('delete_log_time', 'local_th_bulk_send_message'),
        '',
        22,
        $time_run
    );

    $configs[] = new admin_setting_configcheckbox(
        'local_th_bulk_send_message/show_email_content',
        get_string('show_email_content', 'local_th_bulk_send_message'),
        get_string('show_email_content_desc', 'local_th_bulk_send_message'),
        0
    );

    $configs[] = new admin_setting_configtext(
        'local_th_bulk_send_message/logperpage',
        get_string('logperpage', 'local_th_bulk_send_message'),
        get_string('logperpage_desc', 'local_th_bulk_send_message'),
        50,
        PARAM_INT
    );

    //add config
    foreach ($configs as $config) {
        $config->plugin = 'local_th_bulk_send_message';
        $settings->add($config);
    }
}
