<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_online_exam_manager', get_string('pluginname', 'local_th_online_exam_manager'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_th_online_exam_manager/categoryidnumber',
        get_string('categoryidnumber', 'local_th_online_exam_manager'),
        get_string('categoryidnumber_desc', 'local_th_online_exam_manager'),
        'THI',
        PARAM_ALPHANUMEXT
    ));

    global $DB;
    $role = $DB->get_records_sql("SELECT * FROM {role} WHERE NOT id = 1");
    $listroles = [];
    foreach ($role as $id => $role1) {
        $context = context_system::instance();
        $rolename = role_get_name($role1, $context);
        $listroles[$id] = $rolename . ' , ' . $role1->shortname;
    }

    // $settings->add(new admin_setting_configselect(
    //     'local_th_online_exam_manager/regrade_roles',
    //     get_string('regrade_roles', 'local_th_online_exam_manager'),
    //     get_string('regrade_roles_desc', 'local_th_online_exam_manager'),
    //     0,
    //     $listroles
    // ));

    $settings->add(new admin_setting_configtext(
        'local_th_online_exam_manager/regrade_role',
        get_string('regrade_role', 'local_th_online_exam_manager'),
        get_string('regrade_role_desc', 'local_th_online_exam_manager'),
        'regrade_examiner',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_online_exam_manager/regrade_grade_itemid',
        get_string('regrade_grade_itemid', 'local_th_online_exam_manager'),
        get_string('regrade_grade_itemid_desc', 'local_th_online_exam_manager'),
        ''
    ));
}
