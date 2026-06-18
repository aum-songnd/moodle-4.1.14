<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_ops', get_string('pluginname', 'local_th_ops'));

    // Shortname của custom field (core_course/course) dùng để nhận diện "mã khóa học" khi clone.
    $settings->add(new admin_setting_configtext(
        'local_th_ops/coursecode_field_shortname',
        get_string('coursecode_field_shortname', 'local_th_ops'),
        get_string('coursecode_field_shortname_desc', 'local_th_ops'),
        'th_course_code' // Không đặt mặc định để tránh hard-code
    ));

    $ADMIN->add('localplugins', $settings);
}