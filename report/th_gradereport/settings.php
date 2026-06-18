<?php

defined('MOODLE_INTERNAL') || die;

if (has_capability('report/th_gradereport:view', context_system::instance())) {

	$configs = array();
	$ADMIN->add('reports', new admin_externalpage('report_th_gradereport', get_string('pluginname1', 'report_th_gradereport'),
    "$CFG->wwwroot/report/th_gradereport/report.php", 'report/th_gradereport:report'));

	$ADMIN->add('reports', new admin_externalpage('report_th_gradereport1', get_string('pluginname', 'report_th_gradereport'),
    "$CFG->wwwroot/report/th_gradereport/view.php", 'report/th_gradereport:view'));

	$ADMIN->add('reports', new admin_externalpage('report_th_conditionalgradereport', get_string('conditional_grade_report', 'report_th_gradereport'),
    "$CFG->wwwroot/report/th_gradereport/exam_eligibility.php", 'report/th_gradereport:exam_eligibility'));

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/avgScore',
		get_string('avgscore', 'report_th_gradereport'),
		get_string('avgscore_desc', 'report_th_gradereport'),
		'$cc * 0.1 + $kt * 0.3', PARAM_TEXT);

	$configs[] = new admin_setting_configcheckbox(
            'report_th_gradereport/show_avgScore',
			get_string('show_avgscore', 'report_th_gradereport'),
            '',
            1);

	$choices = array(
		"0" => "0",
		"1" => "1",
		"2" => "2",
		"3" => "3",
		"4" => "4",
		"5" => "5"
	);
	$configs[] = new admin_setting_configselect(
		'report_th_gradereport/round', 
		get_string('round', 'report_th_gradereport'),
		'', 
		"1", 
		$choices);
		
	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_student_code',
		get_string('ma_hoc_vien', 'report_th_gradereport'),
		get_string('ma_hoc_vien', 'report_th_gradereport'),
		'th_student_code', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_student_class',
		get_string('ma_lop', 'report_th_gradereport'),
		get_string('ma_lop', 'report_th_gradereport'),
		'th_student_class', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_student_class2',
		get_string('ma_lop_2', 'report_th_gradereport'),
		get_string('ma_lop_2', 'report_th_gradereport'),
		'th_student_class2', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_crm_code',
		get_string('ma_khoa', 'report_th_gradereport'),
		get_string('ma_khoa', 'report_th_gradereport'),
		'th_crm_code', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_crm_cohort2',
		get_string('ma_khoa_2', 'report_th_gradereport'),
		get_string('ma_khoa_2', 'report_th_gradereport'),
		'th_crm_cohort2', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_user_role',
		get_string('vai_tro', 'report_th_gradereport'),
		get_string('vai_tro', 'report_th_gradereport'),
		'th_user_role', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_dob',
		get_string('ngay_sinh', 'report_th_gradereport'),
		get_string('ngay_sinh', 'report_th_gradereport'),
		'th_dob', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_study_field',
		get_string('nganh_hoc', 'report_th_gradereport'),
		get_string('nganh_hoc', 'report_th_gradereport'),
		'th_study_field', PARAM_TEXT);

	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/th_gender',
		get_string('gioi_tinh', 'report_th_gradereport'),
		get_string('gioi_tinh', 'report_th_gradereport'),
		'th_gender', PARAM_TEXT);

	
	$configs[] = new admin_setting_configtext(
		'report_th_gradereport/number_page_record',
		get_string('number_page_record', 'report_th_gradereport'),
		get_string('number_page_record_desc', 'report_th_gradereport'),
		100, PARAM_INT);


	foreach ($configs as $config) {
		$config->plugin = 'report_th_gradereport';
		$settings->add($config);
	}


	
}