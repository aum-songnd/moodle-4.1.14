<?php

use \local_th_bulk_enrolment_manager\libs;

if ($hassiteconfig) {
	$settings = new admin_settingpage(
		'local_th_bulk_enrolment_manager',
		get_string('pluginname','local_th_bulk_enrolment_manager')
	);

	$ADMIN->add('localplugins', $settings);

	$libs = new libs();
	$list_role = $libs->get_list_role2();

	// Tìm key và gán vào 3 biến, nếu không thấy sẽ mặc định là null
	$editingteacher_key = array_search('editingteacher', $list_role);
	$teacher_key        = array_search('teacher', $list_role);
	$qlht_aum_key       = array_search('qlht_aum', $list_role);

	// Ép về kiểu null hoặc giữ nguyên key nếu tìm thấy (đề phòng array_search trả về false)
	$editingteacher_key = ($editingteacher_key !== false) ? $editingteacher_key : null;
	$teacher_key        = ($teacher_key !== false) ? $teacher_key : null;
	$qlht_aum_key       = ($qlht_aum_key !== false) ? $qlht_aum_key : null;

	$settings->add(new admin_setting_heading('local_th_bulk_enrolment_manager/heading', get_string('role_description', 'local_th_bulk_enrolment_manager'), ''));

	$settings->add(
		new admin_setting_configselect(
			'local_th_bulk_enrolment_manager/role1',
			get_string('role1', 'local_th_bulk_enrolment_manager'),
			'',
			$editingteacher_key,
			$list_role)
	);

	$settings->add(
		new admin_setting_configselect(
			'local_th_bulk_enrolment_manager/role2',
			get_string('role2', 'local_th_bulk_enrolment_manager'),
			'',
			$teacher_key,
			$list_role)
	);

	$settings->add(
		new admin_setting_configselect(
			'local_th_bulk_enrolment_manager/role3',
			get_string('role3', 'local_th_bulk_enrolment_manager'),
			'',
			$qlht_aum_key,
			$list_role)
	);

	unset($list_role);
}