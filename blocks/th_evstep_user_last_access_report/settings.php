<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {  
	$settings = new admin_settingpage('block_th_evstep_user_last_access_report', new lang_string('pluginname', 'block_th_evstep_user_last_access_report')); 
	
	if ($ADMIN->fulltree) { 
		$configs[] = new admin_setting_configtext('block_th_evstep_user_last_access_report/th_crm_code', 
		get_string('crm', 'block_th_evstep_user_last_access_report'), 
		get_string('des_crm', 'block_th_evstep_user_last_access_report'), 'th_crm_code', PARAM_TEXT, 15); 
		
		$configs[] = new admin_setting_configtext('block_th_evstep_user_last_access_report/th_student_class', 
		get_string('student_class_setting', 'block_th_evstep_user_last_access_report'), 
		get_string('des_student_class_setting', 'block_th_evstep_user_last_access_report'), 'th_student_class', PARAM_TEXT, 15); 

		$configs[] = new admin_setting_configtext('block_th_evstep_user_last_access_report/th_user_role', 
		get_string('user_role_setting', 'block_th_evstep_user_last_access_report'), 
		get_string('des_user_role_setting', 'block_th_evstep_user_last_access_report'), 'th_user_role', PARAM_TEXT, 15);
		
		//add config 
		foreach ($configs as $config) { 
			$settings->add($config); 
		} 
	} 
	
}

?>
