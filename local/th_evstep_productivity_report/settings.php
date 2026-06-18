<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

	$settings = new admin_settingpage('local_th_evstep_productivity_report', get_string('pluginname', 'local_th_evstep_productivity_report'));
	$ADMIN->add('localplugins', $settings);

	$configs = array();

	$configs[] = new admin_setting_heading('local_th_evstep_productivity_report/heading1', get_string('setting_heading', 'local_th_evstep_productivity_report'), '');
	//

	$configs[] = new admin_setting_configtextarea('local_th_evstep_productivity_report/list_email',
		get_string('list_email', 'local_th_evstep_productivity_report'),
		get_string('des_list_email', 'local_th_evstep_productivity_report'),
		'thuydb@aum.edu.vn,lanhh@aum.edu.vn', PARAM_RAW);

	$configs[] = new admin_setting_configtext('local_th_evstep_productivity_report/th_crm_code',
		get_string('crm', 'local_th_evstep_productivity_report'),
		get_string('des_crm', 'local_th_evstep_productivity_report'),
		'th_crm_code', PARAM_RAW, 15);

	// $configs[] = new admin_setting_configselect('local_th_evstep_productivity_report/time', get_string('time', 'local_th_evstep_productivity_report'), get_string('des_time', 'local_th_evstep_productivity_report'), 7, array(
	// 	0 => new lang_string('never'),
	// 	1 => new lang_string('numdays', '', 1),
	// 	2 => new lang_string('numdays', '', 2),
	// 	3 => new lang_string('numdays', '', 3),
	// 	5 => new lang_string('numdays', '', 5),
	// 	7 => new lang_string('numdays', '', 7),
	// 	10 => new lang_string('numdays', '', 10),
	// 	14 => new lang_string('numdays', '', 14),
	// 	20 => new lang_string('numdays', '', 20),
	// 	30 => new lang_string('numdays', '', 30),
	// 	60 => new lang_string('numdays', '', 60),
	// 	90 => new lang_string('numdays', '', 90),
	// 	120 => new lang_string('numdays', '', 120),
	// 	180 => new lang_string('numdays', '', 180),
	// 	365 => new lang_string('numdays', '', 365),
	// ));

	//add config
	foreach ($configs as $config) {
		$config->pin = 'local_thlib';
		$settings->add($config);
	}

}
