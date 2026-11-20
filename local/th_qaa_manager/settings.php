<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_th_qaa_manager',
        get_string('pluginname', 'local_th_qaa_manager')
    );

    $roles = role_fix_names(
        get_all_roles(context_system::instance())
    );

    $systemroleids = get_roles_for_contextlevels(CONTEXT_SYSTEM);

    $options = [
        0 => get_string('none')
    ];

    foreach ($roles as $role) {
        if (in_array($role->id, $systemroleids)) {
            $options[$role->id] = $role->localname;
        }
    }

    // 1. General Settings
    $settings->add(new admin_setting_heading(
        'local_th_qaa_manager/setting_heading_general',
        get_string('setting_heading_general', 'local_th_qaa_manager'),
        get_string('setting_heading_general_desc', 'local_th_qaa_manager')
    ));

    $settings->add(new admin_setting_configselect(
        'local_th_qaa_manager/assignee_role',
        get_string('assigneerole', 'local_th_qaa_manager'),
        get_string('assigneerole_desc', 'local_th_qaa_manager'),
        0,
        $options
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_qaa_manager/manager_emails',
        get_string('manageremails', 'local_th_qaa_manager'),
        get_string('manageremails_desc', 'local_th_qaa_manager'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_qaa_manager/auto_recall_days',
        get_string('autorecalldays', 'local_th_qaa_manager'),
        get_string('autorecalldays_desc', 'local_th_qaa_manager'),
        7,
        PARAM_INT
    ));

    // 2. Notification: Questions Needing Assignment
    $settings->add(new admin_setting_heading(
        'local_th_qaa_manager/setting_heading_weekly_notify',
        get_string('setting_heading_weekly_notify', 'local_th_qaa_manager'),
        get_string('setting_heading_weekly_notify_desc', 'local_th_qaa_manager')
    ));

    $defaultbody = "Xin chào anh/chị!\n\n"
        . "Anh/chị vui lòng truy cập Báo cáo hỏi đáp để duyệt các câu hỏi từ học viên.\n"
        . "Đường dẫn: {\$a->url}\n\n"
        . "Xin cảm ơn.";

    $settings->add(new admin_setting_configtext(
        'local_th_qaa_manager/email_weekly_subject',
        get_string('email_weekly_subject', 'local_th_qaa_manager'),
        get_string('email_weekly_subject_desc', 'local_th_qaa_manager'),
        get_string('email_weekly_new_questions_subject', 'local_th_qaa_manager'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_th_qaa_manager/email_weekly_body',
        get_string('email_weekly_body', 'local_th_qaa_manager'),
        get_string('email_weekly_body_desc', 'local_th_qaa_manager'),
        $defaultbody,
        PARAM_RAW,
        '60',
        '8'
    ));

    // 3. Notification: CTV Answered Questions
    $settings->add(new admin_setting_heading(
        'local_th_qaa_manager/setting_heading_daily_notify',
        get_string('setting_heading_daily_notify', 'local_th_qaa_manager'),
        get_string('setting_heading_daily_notify_desc', 'local_th_qaa_manager')
    ));

    $defaultdailybody = "Xin chào anh/chị!\n\n"
        . "CTV đã hoàn thành trả lời câu hỏi, vui lòng kiểm tra và hoàn thành phê duyệt câu trả lời.\n"
        . "{\$a->url}\n\n"
        . "Nếu cần hỗ trợ anh/chị vui lòng liên hệ kỹ thuật (hotline 0948.293.794 ).\n\n"
        . "Xin cảm ơn.";

    $settings->add(new admin_setting_configtext(
        'local_th_qaa_manager/email_daily_subject',
        get_string('email_daily_subject', 'local_th_qaa_manager'),
        get_string('email_daily_subject_desc', 'local_th_qaa_manager'),
        get_string('email_daily_answered_subject', 'local_th_qaa_manager'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_th_qaa_manager/email_daily_body',
        get_string('email_daily_body', 'local_th_qaa_manager'),
        get_string('email_daily_body_desc', 'local_th_qaa_manager'),
        $defaultdailybody,
        PARAM_RAW,
        '60',
        '8'
    ));

    $ADMIN->add('localplugins', $settings);
}