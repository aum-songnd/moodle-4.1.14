<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configtext(
            'block_th_grades_approval/active_grades_approval',
            get_string('active_grades_approval', 'block_th_grades_approval'),
            get_string('active_grades_approval_desc', 'block_th_grades_approval'),
            '56',
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'block_th_grades_approval/send_mail_grades_approval',
            get_string('send_mail_grades_approval', 'block_th_grades_approval'),
            get_string('send_mail_grades_approval_desc', 'block_th_grades_approval'),
            '60',
            PARAM_INT
        )
    );

    $list_time = [];
    for ($i = 0; $i <= 23; $i++) {
        $list_time[] = $i;
    }

    $settings->add(
        new admin_setting_configselect(
            'block_th_grades_approval/time_send_mail',
            get_string('time_send_mail', 'block_th_grades_approval'),
            get_string('time_send_mail_desc', 'block_th_grades_approval'),
            22,
            $list_time
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'block_th_grades_approval/email_subject',
            get_string('email_subject', 'block_th_grades_approval'),
            '',
            get_string('default_email_subject', 'block_th_grades_approval'),
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'block_th_grades_approval/email_content',
            get_string('email_content', 'block_th_grades_approval'),
            '',
            get_string('default_email_content', 'block_th_grades_approval'),
            PARAM_TEXT
        )
    );
}

