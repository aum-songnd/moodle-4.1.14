<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_th_daily_grade_update',
        get_string('pluginname', 'local_th_daily_grade_update')
    );

    $settings->add(new admin_setting_heading(
        'local_th_daily_grade_update/task_notice',
        '⚙️ ' . get_string('task_config_heading', 'local_th_daily_grade_update'),
        get_string('task_config_description', 'local_th_daily_grade_update')
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_daily_grade_update/cc1_idnumber',
        get_string('cc1_idnumber', 'local_th_daily_grade_update'),
        '',
        'bg'
    ));
    $settings->add(new admin_setting_configtext(
        'local_th_daily_grade_update/qaa_idnumber',
        get_string('qaa_idnumber', 'local_th_daily_grade_update'),
        '',
        'qa'
    ));
    $settings->add(new admin_setting_configtext(
        'local_th_daily_grade_update/cc3_idnumber',
        get_string('cc3_idnumber', 'local_th_daily_grade_update'),
        '',
        'hd'
    ));

    $monthoptions = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthoptions[$i] = $i . ' ' . get_string('month', 'local_th_daily_grade_update');
    }
    $settings->add(new admin_setting_configselect(
        'local_th_daily_grade_update/select_month',
        get_string('select_month', 'local_th_daily_grade_update'),
        '',
        6,
        $monthoptions
    ));

    // $day_delay_options = [];
    // for ($i = 0; $i <= 100; $i++) {
    //     $day_delay_options[$i] = get_string('xdays', 'local_th_daily_grade_update', $i);
    // }
    $settings->add(new admin_setting_configtext(
        'local_th_daily_grade_update/select_day_delay',
        get_string('select_day_delay', 'local_th_daily_grade_update'),
        '',        // mô tả, nếu cần thì thêm get_string(...)
        1,         // giá trị mặc định
        PARAM_INT  // bắt buộc là số nguyên
    ));

    $completion_question = [
        'all'   => get_string('completion_all', 'local_th_daily_grade_update'),
        'scorm' => get_string('completion_scorm', 'local_th_daily_grade_update'),
    ];
    
    $settings->add(new admin_setting_configselect(
        'local_th_daily_grade_update/completion',
        get_string('completion_method', 'local_th_daily_grade_update'),
        '',
        'all',
        $completion_question
    ));

    $completion_question = [
        'all'   => get_string('completion_all', 'local_th_daily_grade_update'),
        'scorm' => get_string('completion_scorm', 'local_th_daily_grade_update'),
    ];
    
    $settings->add(new admin_setting_configselect(
        'local_th_daily_grade_update/completion',
        get_string('completion_method', 'local_th_daily_grade_update'),
        '',
        'all',
        $completion_question
    ));

    $graderoundingoptions = [
        'round'    => get_string('rounding_round_1_decimal', 'local_th_daily_grade_update'),
        'round_05' => get_string('rounding_round_05', 'local_th_daily_grade_update'),
    ];
    
    $settings->add(new admin_setting_configselect(
        'local_th_daily_grade_update/graderounding',
        get_string('grade_rounding', 'local_th_daily_grade_update'),
        get_string('grade_rounding_desc', 'local_th_daily_grade_update'),
        'round',
        $graderoundingoptions
    ));
    
    $enddateoptions = [
        'cc1' => get_string('enddate_from_cc1', 'local_th_daily_grade_update'),
        'th'  => get_string('enddate_from_th',  'local_th_daily_grade_update'),
    ];
    
    $settings->add(new admin_setting_configselect(
        'local_th_daily_grade_update/enddate_method',
        get_string('enddate_method', 'local_th_daily_grade_update'),
        get_string('enddate_method_desc', 'local_th_daily_grade_update'),
        'cc1', // mặc định lấy theo bài luyện tập 1
        $enddateoptions
    ));

    // Nhập idnumber bài luyện tập 1.
    $settings->add(new admin_setting_configtext(
        'local_th_daily_grade_update/lt1_idnumber',
        get_string('lt1_idnumber', 'local_th_daily_grade_update'),
        '',
        'cc1',
        PARAM_RAW,
        30
    ));

    // Nhập idnumber bài thực hành.
    $settings->add(new admin_setting_configtext(
        'local_th_daily_grade_update/th_idnumber',
        get_string('th_idnumber', 'local_th_daily_grade_update'),
        '',
        'th',
        PARAM_RAW,
        30
    ));

    $ADMIN->add('localplugins', $settings);
}
