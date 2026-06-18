<?php

defined('MOODLE_INTERNAL') || die;
global $COURSE;
$context = context_course::instance($COURSE->id);

if (has_capability('local/th_sectionmanager:manage', $context)) {
	$parent = $ADMIN->locate('courses');
	if ($parent) {
		$parent->add('courses', new admin_externalpage('local_th_sectionmanager_types', get_string('manage_types_title', 'local_th_sectionmanager'), new moodle_url('/local/th_sectionmanager/manage_types.php'), 'local/th_sectionmanager:manage'));
	}
}

if ($hassiteconfig) {

    // 1. Khởi tạo trang cài đặt cho plugin
    $settings = new admin_settingpage(
        'local_th_sectionmanager', 
        get_string('pluginname', 'local_th_sectionmanager') // Tên plugin hiển thị trên menu sidebar
    );
    
    // ĐƯA VÀO LOCAL: Thêm trang này vào nhánh 'localplugins' của hệ thống
    $ADMIN->add('localplugins', $settings);

    // 2. Khai báo mảng chứa các ô cấu hình
    $configs = array();

    // Tiêu đề phân khu cài đặt (Heading)
    $configs[] = new admin_setting_heading(
        'local_th_sectionmanager/heading1', 
        get_string('manage_types_title', 'local_th_sectionmanager'), 
        ''
    );

    // Ô nhập text cấu hình mã rút gọn mặc định
    $configs[] = new admin_setting_configtext(
        'local_th_sectionmanager/default_shortcode', 
        get_string('default_shortcode_setting', 'local_th_sectionmanager'), 
        get_string('default_shortcode_desc', 'local_th_sectionmanager'),    
        'SEC_EXM', 
        PARAM_TEXT   
    );

    // 3. Vòng lặp tự động gắn tên plugin và add cấu hình vào trang
    foreach ($configs as $config) {
        $config->plugin = 'local_th_sectionmanager';
        $settings->add($config);
    }
}