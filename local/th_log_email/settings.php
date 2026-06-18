<?php

defined('MOODLE_INTERNAL') || die;
global $COURSE;
$context = context_course::instance($COURSE->id);

if (has_capability('local/th_log_email:view', $context)) {

	$parent = $ADMIN->locate('server');
	if ($parent) {
		$parent->add('server', new admin_externalpage('th_log_email', get_string('pluginname', 'local_th_log_email'), "$CFG->wwwroot/local/th_log_email/index.php", 'local/th_log_email:view'));
	}

	//setting

	$settings = new admin_settingpage('local_th_log_email', get_string('pluginname', 'local_th_log_email'));
    $ADMIN->add('localplugins', $settings);

    $configs   = array();
    $configs[] = new admin_setting_heading('local_th_log_email/heading1', get_string('delete_email_log_setting', 'local_th_log_email'), '');

    $month = array();
    for($i=1;$i<=12;$i++){
    	$month[$i] = $i . get_string('months', 'local_th_log_email');
    }

    $time_run = array();
    for($i=0;$i<=23;$i++){
    	if ($i < 10) {
    		$time_run['0' . $i] = $i . get_string('hours', 'local_th_log_email');
    	} else {
    		$time_run[$i] = $i . get_string('hours', 'local_th_log_email');
    	}
    }

    $configs[] = new admin_setting_configselect(
        'local_th_log_email/month',
        get_string('delete_email_log_after', 'local_th_log_email'),
        '',
        6,
        $month
    );

    $configs[] = new admin_setting_configselect(
        'local_th_log_email/time_run',
        get_string('delete_email_log_at', 'local_th_log_email'),
        '',
        22,
        $time_run
    );

    //add config
    foreach ($configs as $config) {
        $config->plugin = 'local_th_log_email';
        $settings->add($config);
    }
}

?>