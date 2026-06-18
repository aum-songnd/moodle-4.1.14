<?php

defined('MOODLE_INTERNAL') || die();

global $ADMIN, $DB;

if ($hassiteconfig) {
    $roles = $DB->get_records_menu('role', null, 'shortname ASC', 'id, shortname');
    $choices = !empty($roles) ? $roles : [];
    $settings->add(new admin_setting_configselect(
        'quiz_thessaygrader/role_hidden',
        get_string('role_hidden', 'quiz_thessaygrader'),
        get_string('role_hidden_desc', 'quiz_thessaygrader'),
        '',
        $choices
    ));

    $settings->add(new admin_setting_configtext(
        'quiz_thessaygrader/customfield_student_code',
        get_string('customfield_shortname', 'quiz_thessaygrader'),
        get_string('customfield_description', 'quiz_thessaygrader'),
        'th_student_code'
    ));

    $settings->add(new admin_setting_configtext(
        'quiz_thessaygrader/attempt_secret_key',
        get_string('attempt_secret_key', 'quiz_thessaygrader'),
        get_string('attempt_secret_key_description', 'quiz_thessaygrader'),
        'my_secret_key'
    ));

    $settings->add(new admin_setting_configtext(
        'quiz_thessaygrader/attempt_secret_iv',
        get_string('attempt_secret_iv', 'quiz_thessaygrader'),
        get_string('attempt_secret_iv_description', 'quiz_thessaygrader'),
        'my_secret_iv'
    ));
}
