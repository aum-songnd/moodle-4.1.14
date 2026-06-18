<?php

defined('MOODLE_INTERNAL') || die;
global $COURSE;
$context = context_course::instance($COURSE->id);

if (has_capability('local/th_feedback_report:view', $context)) {

	$parent = $ADMIN->locate('reports');
	if ($parent) {
		$parent->add('reports', new admin_externalpage('th_feedback_report', get_string('title_log_feedback_report', 'local_th_feedback_report'), "$CFG->wwwroot/local/th_feedback_report/index.php", 'local/th_feedback_report:view'));
	}

	//setting

	$settings = new admin_settingpage('local_th_feedback_report', get_string('pluginname', 'local_th_feedback_report'));
    $ADMIN->add('localplugins', $settings);

    $configs   = array();
    $configs[] = new admin_setting_heading('local_th_feedback_report/heading1', get_string('settings', 'core'), '');

    $configs[] = new admin_setting_configtext(
        'local_th_feedback_report/email_address',
        get_string('email_address', 'local_th_feedback_report'),
        get_string('email_address_description', 'local_th_feedback_report'),
        '',
        PARAM_TEXT
    );
    

    //add config
    foreach ($configs as $config) {
        $config->plugin = 'local_th_feedback_report';
        $settings->add($config);
    }
}

?>