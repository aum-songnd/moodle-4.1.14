<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Thêm phần IELTS Listening resume vào form cấu hình activity Quiz.
 */
function local_th_ieltsquizaudio_coursemodule_standard_elements(
    moodleform_mod $formwrapper,
    MoodleQuickForm $mform
): void {
    global $DB;

    $current = $formwrapper->get_current();

    if (empty($current->modulename) || $current->modulename !== 'quiz') {
        return;
    }

    $defaultoffset = (int) get_config(
        'theme_th_lambda_st_ielts',
        'audio_resume_offset'
    );

    if ($defaultoffset <= 0) {
        $defaultoffset = 5;
    }

    // Giá trị mặc định
    $defaults = [
        'resumeoffset' => $defaultoffset,
    ];

    if (!empty($current->instance)) {
        $settings = $DB->get_record('local_th_ieltsquizaudio', ['quizid' => (int)$current->instance]);
        if ($settings) {
            $defaults = [
                'resumeoffset' => max(0, (int)$settings->resumeoffset),
            ];
        }
    }

    // Header trong Quiz Settings
    $mform->addElement(
        'header',
        'th_ieltsquizaudio_header',
        get_string('ieltslisteningsettings', 'local_th_ieltsquizaudio')
    );

    // Resume offset
    $mform->addElement(
        'text',
        'th_ieltsquizaudio_resumeoffset',
        get_string('resumeoffset', 'local_th_ieltsquizaudio'),
        ['size' => 6]
    );
    $mform->setType('th_ieltsquizaudio_resumeoffset', PARAM_INT);
    $mform->setDefault('th_ieltsquizaudio_resumeoffset', $defaults['resumeoffset']);
    $mform->addHelpButton('th_ieltsquizaudio_resumeoffset', 'resumeoffset', 'local_th_ieltsquizaudio');
}

/**
 * Lưu cấu hình resume sau khi Quiz được tạo hoặc cập nhật.
 */
function local_th_ieltsquizaudio_coursemodule_edit_post_actions($moduleinfo, $course)
{
    global $DB;

    $defaultoffset = (int) get_config(
        'theme_th_lambda_st_ielts',
        'audio_resume_offset'
    );

    if ($defaultoffset <= 0) {
        $defaultoffset = 5;
    }

    if (empty($moduleinfo->modulename) || $moduleinfo->modulename !== 'quiz') {
        return $moduleinfo;
    }

    if (empty($moduleinfo->instance)) {
        return $moduleinfo;
    }

    $quizid = (int)$moduleinfo->instance;

    $record = new stdClass();
    $record->quizid = $quizid;
    $record->resumeoffset = isset($moduleinfo->th_ieltsquizaudio_resumeoffset)
        ? max(0, (int)$moduleinfo->th_ieltsquizaudio_resumeoffset)
        : $defaultoffset;
    $record->timemodified = time();

    $exist = $DB->get_record('local_th_ieltsquizaudio', ['quizid' => $quizid]);
    if ($exist) {
        $record->id = $exist->id;
        $DB->update_record('local_th_ieltsquizaudio', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('local_th_ieltsquizaudio', $record);
    }

    return $moduleinfo;
}
