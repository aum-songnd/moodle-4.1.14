<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_th_studynotify', get_string('pluginname', 'local_th_studynotify'));

    $settings->add(new admin_setting_configcheckbox(
        'local_th_studynotify/enable_course_opened',
        get_string('enable_course_opened', 'local_th_studynotify'),
        get_string('enable_course_opened_desc', 'local_th_studynotify'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_th_studynotify/enable_inactive_students',
        get_string('enable_inactive_students', 'local_th_studynotify'),
        get_string('enable_inactive_students_desc', 'local_th_studynotify'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_th_studynotify/enable_quiz_kt_reminder',
        get_string('enable_quiz_kt_reminder', 'local_th_studynotify'),
        get_string('enable_quiz_kt_reminder_desc', 'local_th_studynotify'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_th_studynotify/enable_weekly_study',
        get_string('enable_weekly_study', 'local_th_studynotify'),
        get_string('enable_weekly_study_desc', 'local_th_studynotify'),
        1
    ));
    
    $settings->add(new admin_setting_configtext(
        'local_th_studynotify/quizidnumber',
        get_string('quizidnumber', 'local_th_studynotify'),
        get_string('quizidnumberdesc', 'local_th_studynotify'),
        'kt.1, kt.2',
        PARAM_TEXT
    )); 
    
    $settings->add(new admin_setting_configtext(
        'local_th_studynotify/days_before_quiz',
        get_string('days_before_quiz', 'local_th_studynotify'),
        get_string('days_before_quiz_desc', 'local_th_studynotify'),
        '7,1',  // giá trị mặc định
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_studynotify/inactive_days',
        get_string('inactive_days', 'local_th_studynotify'),
        get_string('inactive_days_desc', 'local_th_studynotify'),
        '28,14',  // giá trị mặc định
        PARAM_TEXT
    ));

    // $settings->add(new admin_setting_configtext(
    //     'local_th_studynotify/notification_weeks', 
    //     get_string('notification_weeks', 'local_th_studynotify'),
    //     get_string('notification_weeks_desc', 'local_th_studynotify'),
    //     '2,3,5,6,7',
    //     PARAM_TEXT
    // ));

    // Chuỗi dạng: 2:8,3:15,5:29,6:36,7:43
    $settings->add(new admin_setting_configtext(
        'local_th_studynotify/notification_weeks',
        get_string('notification_weeks', 'local_th_studynotify'),
        'Format: week:daysAfterT, ví dụ: 2:8,3:15,5:29,6:36,7:43',
        '2:8,3:15,5:29,6:36,7:43',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_studynotify/testdate',
        get_string('testdate', 'local_th_studynotify'),
        get_string('testdate_desc', 'local_th_studynotify'),
        '',
        PARAM_TEXT
    ));
    
    $settings->add(new admin_setting_configtextarea(
        'local_th_studynotify/courseopen_template_batch',
        get_string('courseopen_template_batch_title', 'local_th_studynotify'),
        get_string('courseopen_template_batch_desc', 'local_th_studynotify'),
        get_string('courseopen_template_batch_default', 'local_th_studynotify')
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_th_studynotify/inactive_template_batch',
        get_string('inactive_template_batch_title', 'local_th_studynotify'),
        get_string('inactive_template_batch_desc', 'local_th_studynotify'),
        get_string('inactive_template_batch_default', 'local_th_studynotify')
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_th_studynotify/quiz_reminder_template',
        get_string('quiz_reminder_template_title', 'local_th_studynotify'),
        get_string('quiz_reminder_template_desc', 'local_th_studynotify'),
        get_string('quiz_reminder_template_default', 'local_th_studynotify')
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_th_studynotify/weekly_template',
        get_string('weekly_template', 'local_th_studynotify'),
        get_string('weekly_template_desc', 'local_th_studynotify'),
        get_string('weekly_template_default', 'local_th_studynotify')
    ));
    
    $ADMIN->add('localplugins', $settings);
    $settings->add(new admin_setting_heading('local_studentnotify_weeklyhdr',
        get_string('local_studentnotify_weeklyhdr', 'local_th_studynotify'),
        get_string('local_studentnotify_weeklyhdr_desc', 'local_th_studynotify')
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_th_studynotify/common_template',
        get_string('common_template', 'local_th_studynotify'),
        get_string('common_template_desc', 'local_th_studynotify'),
        get_string('common_template_default', 'local_th_studynotify')
    ));

    $settings->add(new admin_setting_configtext(
        'local_th_studynotify/feedback_link',
        get_string('feedback_link', 'local_th_studynotify'),
        get_string('feedback_link_desc', 'local_th_studynotify'),
        ''
    ));
}
