<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->libdir . '/pdflib.php');
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

    $settings->add(new admin_setting_heading('thpagemodeditdefaults1', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new \mod_thpage\admin_setting_thpagestoredfile('mod_thpage/documents',
                get_string('documents', 'thpage', null, true),
                get_string('documents_desc', 'thpage', null, true),
                'documents',
                0,
                array('maxfiles' => -1, 'accepted_types' => '.html')));
    $settings->add(new \mod_thpage\admin_setting_thpagestoredfile('mod_thpage/logo',
                get_string('logo', 'thpage', null, true),
                get_string('logo_desc', 'thpage', null, true),
                'logo',
                0,
                array('maxfiles' => 1, 'accepted_types' => '.png')));

    $settings->add(new \mod_thpage\admin_setting_thpagestoredfile('mod_thpage/example_pdf',
                get_string('example_pdf', 'thpage', null, true),
                get_string('example_pdf_desc', 'thpage', null, true),
                'example_pdf',
                0,
                array('maxfiles' => 1, 'accepted_types' => '.pdf')));
    $settings->add(new admin_setting_configtext('thpage/pdf_header',
        get_string('header_pdf', 'thpage'), get_string('header_pdf_desc', 'thpage'), '', PARAM_TEXT, 60));
    $settings->add(new admin_setting_configtext('thpage/pdf_header_en',
        get_string('header_pdf_english', 'thpage'), get_string('header_pdf_english_desc', 'thpage'), '', PARAM_TEXT, 60));
    // $settings->add(new admin_setting_configcheckbox('thpage/use_font_support_chinese',
    //     get_string('use_font_support_chinese', 'thpage'), get_string('use_font_support_chinese_desc', 'thpage'), 0));
    // $settings->add(new admin_setting_configtext('thpage/font_chinese',
    //     get_string('font_chinese', 'thpage'), get_string('font_chinese_desc', 'thpage'), 'kozminproregular', PARAM_TEXT, 20));
    // $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO
    //         | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
    // $dh = new RecursiveDirectoryIterator(K_PATH_FONTS, $flags);
    // foreach ($dh as $fi) {
    //     if ($fi->getExtension() !== 'php') {
    //         continue;
    //     }
    //     $fontname = $fi->getBasename('.' . $fi->getExtension());
    //     include($fi->getPathname());
    //     $fontinfo = [
    //         'name' => $name,
    //         'family' => '',
    //     ];

    //     $family = strtoupper($fontname);
    //     if (substr($family, -1) == 'I') {
    //         $family = substr($family, 0, -1);
    //     }
    //     if (substr($family, -1) == 'B') {
    //         $family = substr($family, 0, -1);
    //     }
    //     $fontinfo['family'] = strtolower($family); // Normalize family name.
    //     $info[$fontname] = $fontinfo;
    // }
    // array_multisort(array_column($info, 'name'), SORT_NATURAL, $info);


    // $fontslist = $info;

    // $fonts = [];
    // foreach ($fontslist as $font) {
    //     if (!isset($fonts[$font['family']])) {
    //         $fonts[$font['family']] = $font['family'];
    //     }
    // } 
    // unset($font); // Always unset references!
    // ksort($fonts);
    // $settings->add(new admin_setting_configselect('thpage/font',
    //     get_string('font_chinese', 'thpage'), get_string('font_chinese_desc', 'thpage'), 'freeserif', $fonts));

    $default = '#ffae00';
    $previewconfig = null;
    $settings->add(new admin_setting_configcolourpicker('thpage/color_table', get_string('color_table', 'thpage'), get_string('color_table_desc', 'thpage'), $default, $previewconfig));

    $default = '#ffffff';
    $settings->add(new admin_setting_configcolourpicker('thpage/color_word', get_string('color_word', 'thpage'), get_string('color_word_desc', 'thpage'), $default, $previewconfig));

    // $settings->add(new admin_setting_confightmleditor('tableofcontent', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin'), ''));
   
   
    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('thpagemodeditdefaults2', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));
    $settings->add(new admin_setting_configtext('thpage/study_time',
        get_string('study_time', 'thpage'), get_string('study_time', 'thpage'), 'study_time', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/teacher',
        get_string('teacher', 'thpage'), get_string('teacher', 'thpage'), 'teacher', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week1',
        get_string('week1', 'thpage'), get_string('week1', 'thpage'), 'week1', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week2',
        get_string('week2', 'thpage'), get_string('week2', 'thpage'), 'week2', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week3',
        get_string('week3', 'thpage'), get_string('week3', 'thpage'), 'week3', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week4',
        get_string('week4', 'thpage'), get_string('week4', 'thpage'), 'week4', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week5',
        get_string('week5', 'thpage'), get_string('week5', 'thpage'), 'week5', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week6',
        get_string('week6', 'thpage'), get_string('week6', 'thpage'), 'week6', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week7',
        get_string('week7', 'thpage'), get_string('week7', 'thpage'), 'week7', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week8',
        get_string('week8', 'thpage'), get_string('week8', 'thpage'), 'week8', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week9',
        get_string('week9', 'thpage'), get_string('week9', 'thpage'), 'week9', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/week10',
        get_string('week10', 'thpage'), get_string('week10', 'thpage'), 'week10', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/kt1',
        get_string('kt1', 'thpage'), get_string('kt1', 'thpage'), 'kt1', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/kt2',
        get_string('kt2', 'thpage'), get_string('kt2', 'thpage'), 'kt2', PARAM_TEXT, 10));
    $settings->add(new admin_setting_configtext('thpage/thi',
        get_string('thi', 'thpage'), get_string('thi', 'thpage'), 'thi', PARAM_TEXT, 10));

    $settings->add(new admin_setting_configtext('thpage/tuan_thi',
        get_string('tuan_thi', 'thpage'), get_string('tuan_thi_desc', 'thpage'), 10, PARAM_INT, 10));

    $url = new moodle_url('/mod/thpage/viewall.php');
    $item = new admin_setting_heading('thpage/about',
        '',
        new lang_string('viewall', 'thpage', $url->out()));
    $settings->add($item);
    

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configmultiselect('thpage/displayoptions',
        get_string('displayoptions', 'thpage'), get_string('configdisplayoptions', 'thpage'),
        $defaultdisplayoptions, $displayoptions));
    $settings->add(new admin_setting_heading('thpagemodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));
    $settings->add(new admin_setting_configcheckbox('thpage/printheading',
        get_string('printheading', 'thpage'), get_string('printheadingexplain', 'thpage'), 1));
    $settings->add(new admin_setting_configcheckbox('thpage/printintro',
        get_string('printintro', 'thpage'), get_string('printintroexplain', 'thpage'), 0));
    $settings->add(new admin_setting_configcheckbox('thpage/printlastmodified',
        get_string('printlastmodified', 'thpage'), get_string('printlastmodifiedexplain', 'thpage'), 1));
    $settings->add(new admin_setting_configselect('thpage/display',
        get_string('displayselect', 'thpage'), get_string('displayselectexplain', 'thpage'), RESOURCELIB_DISPLAY_OPEN, $displayoptions));
    $settings->add(new admin_setting_configtext('thpage/popupwidth',
        get_string('popupwidth', 'thpage'), get_string('popupwidthexplain', 'thpage'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('thpage/popupheight',
        get_string('popupheight', 'thpage'), get_string('popupheightexplain', 'thpage'), 450, PARAM_INT, 7));
}
