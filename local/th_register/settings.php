<?php

defined('MOODLE_INTERNAL') || die;
require_once $CFG->dirroot . '/local/th_register/dang_ky_hoc/locallib.php';

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_th_register', get_string('pluginname', 'local_th_register'));
    $ADMIN->add('localplugins', $settings);

    $configs   = array();
    $configs[] = new admin_setting_heading('local_th_register/heading1', 'local_th_register', '');

    $configs[] = new admin_setting_configtext(
        'local_th_register/nganh_hoc',
        get_string('nganh_hoc', 'local_th_register'),
        get_string('nganh_hoc_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );    

    $configs[] = new admin_setting_configtext(
        'local_th_register/ma_hoc_vien',
        get_string('ma_hoc_vien', 'local_th_register'),
        get_string('ma_hoc_vien_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );

    $configs[] = new admin_setting_configtext(
        'local_th_register/ma_khoa',
        get_string('ma_khoa', 'local_th_register'),
        get_string('ma_khoa_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );
    

    $configs[] = new admin_setting_configtext(
        'local_th_register/ma_lop',
        get_string('ma_lop', 'local_th_register'),
        get_string('ma_lop_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );    

    $configs[] = new admin_setting_configtext(
        'local_th_register/nganh_hoc2',
        get_string('nganh_hoc2', 'local_th_register'),
        get_string('nganh_hoc2_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );
    

    $configs[] = new admin_setting_configtext(
        'local_th_register/ma_hoc_vien2',
        get_string('ma_hoc_vien2', 'local_th_register'),
        get_string('ma_hoc_vien2_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );
    
    $configs[] = new admin_setting_configtext(
        'local_th_register/ma_khoa2',
        get_string('ma_khoa2', 'local_th_register'),
        get_string('ma_khoa2_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );
    
    $configs[] = new admin_setting_configtext(
        'local_th_register/ma_lop2',
        get_string('ma_lop2', 'local_th_register'),
        get_string('ma_lop2_desc', 'local_th_register'),
        '',
        PARAM_NOTAGS
    );
    

    $mot_megabyte = 1024 * 1024;
    $hai_muoi_mb  = $mot_megabyte * 20;
    $nam_muoi_mb  = $mot_megabyte * 50;
    $gioi_han     = array(
        $hai_muoi_mb => "20 MB",
        $nam_muoi_mb => "50 MB",
    );

    $configs[] = new admin_setting_configselect(
        'local_th_register/maxbytes',
        get_string('maxbytes', 'local_th_register'),
        get_string('maxbytes_desc', 'local_th_register'),
        $gioi_han[$hai_muoi_mb],
        $gioi_han
    );
    
    $configs[] = new admin_setting_configtextarea(
        'local_th_register/stk',
        get_string('stk', 'local_th_register'),
        get_string('stk_desc', 'local_th_register'),
        get_string('stk_default', 'local_th_register'),
        PARAM_RAW
    );
    

    $configs[] = new admin_setting_configtext(
        'local_th_register/start_split_nganh',
        get_string('start_split_nganh', 'local_th_register'),
        '',
        '',
        PARAM_NOTAGS
    );
    
    $configs[] = new admin_setting_configtext(
        'local_th_register/end_split_nganh',
        get_string('end_split_nganh', 'local_th_register'),
        '',
        '',
        PARAM_NOTAGS
    );
    
    $configs[] = new admin_setting_configtext(
        'local_th_register/start_split_doi_tuong',
        get_string('start_split_doi_tuong', 'local_th_register'),
        '',
        '',
        PARAM_NOTAGS
    );
    
    $configs[] = new admin_setting_configtext(
        'local_th_register/end_split_doi_tuong',
        get_string('end_split_doi_tuong', 'local_th_register'),
        '',
        '',
        PARAM_NOTAGS
    );
    

    //add config
    foreach ($configs as $config) {
        $config->plugin = 'local_th_register';
        $settings->add($config);
    }
}
