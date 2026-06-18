<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/filelib.php');
require_once($CFG->libdir . '/pdflib.php');
require_once($CFG->dirroot.'/mod/thpage/thpagepdf.php');
require_once($CFG->dirroot.'/mod/thpage/locallib.php');

function thpage_extend_settings_navigation(settings_navigation $settings, navigation_node $thpagenode) {
    global $PAGE, $DB, $CFG, $USER;

    // $mode = optional_param('mode', '', PARAM_ALPHA);
    // $hook = optional_param('hook', 'ALL', PARAM_CLEAN);

    if (has_capability('mod/thpage:viewall', $PAGE->cm->context)) {
        $thpagenode->add(get_string('addentry', 'glossary'), new moodle_url('/mod/thpage/view.php', array('cmid'=>$PAGE->cm->id)));
    }
}

function thpage_create_content($course, $thpage, $cm) {
    global $DB, $SITE, $CFG;

    $lang = $course->lang;
    if ('en' !== $lang) {
        $lang = 'vi';
    }
    $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
    $context = context_course::instance($course->id);
    $teachers = get_role_users($role->id, $context);

    $thlib_config = get_config('local_thlib', 'enrollmentcourseshortname');
    $where = '';
    if (!empty($thlib_config)) {

        $shortname_student_cohorts = array_map('trim', explode(',', $thlib_config));
        $where = ' WHERE ';
        foreach ($shortname_student_cohorts as $key => $value) {
            $where .= 'f.shortname=\'' . $value .'\' OR ';
        }
    }
    $where = substr($where, 0, -4);
    $students = get_enrolled_sql($context);
    $sql = "SELECT DISTINCT a.data
            FROM {user} eu1_u
            JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = eu1_u.id
            JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = $course->id)
            JOIN (SELECT d.* 
                FROM {user_info_data} d
                JOIN {user_info_field} f ON d.fieldid = f.id 
                $where
                ) a ON a.userid = eu1_u.id
            WHERE eu1_u.deleted = 0 and a.data <> ''
            group by a.data";
    $student_cohorts = $DB->get_records_sql($sql);
    $student_cohorts = array_map('trim', array_keys($student_cohorts));
    $gv = $DB->get_records('th_teacher_infor');

    $cohort[] = []; 
    $qlht[] = [];
    foreach ($student_cohorts as $student_cohort) {
        // $t = [];
        foreach ($gv as $key => $g) {
            if (strpos($g->cohorts, $student_cohort) !== false) {
                if ($g->role == 1) {
                    if (!isset($cohort[$key])) {
                        $cohort[$key] = [];
                    }
                    array_push($cohort[$key], $student_cohort);
                } elseif ($g->role == 0) {
                     if (!isset($qlht[$key])) {
                        $qlht[$key] = [];
                    }
                    array_push($qlht[$key], $student_cohort);
                }
            } else if (strpos($g->cohorts, substr($student_cohort, 0, 7)) !== false) {
                if ($SITE->shortname == 'eTNU') {
                    $pos = strpos($student_cohort, 'AUM0120');
                    $pos1 = strpos($student_cohort, 'AUM0220');
                    $pos2 = strpos($student_cohort, 'AUM0520');
                    $pos3 = strpos($student_cohort, 'AUM0320');
                    $pos4 = strpos($student_cohort, 'AUM0420');
                    
                    if ($pos !== false || $pos1 !== false || $pos2 !== false || $pos3 !== false || $pos4 !== false) {
                        $student_cohort = substr($student_cohort, 0, 7);
                        if ($g->role == 1) {
                            if (!isset($cohort[$key])) {
                            $cohort[$key] = [];
                        }
                        array_push($cohort[$key], $student_cohort);
                        } elseif ($g->role == 0) {
                             if (!isset($qlht[$key])) {
                                $qlht[$key] = [];
                            }
                            array_push($qlht[$key], $student_cohort);
                        }
                    }
                    
                } else if ($SITE->shortname == 'eAOF') {
                    $cohorts1 = trim($cohorts);
                } else {
                    $cohorts1 = substr($cohorts, 0, 7);
                }
            }
        }
    }
    unset($cohort[0]);

    $table = new html_table();
    $table->attributes = array('nobr' => "true",'border' => '1');
    $table->cellpadding = 5;
    $table->cellspacing = 0;
    $table->width = '100%';
    $background_color = get_config('thpage', 'color_table');
    $color_word = get_config('thpage', 'color_word');
    $style = "text-align: center; background-color: $background_color; color: $color_word;";
    $head = new html_table_row();
    $head->attributes = array('nobr' => "true", 'class'=> '');
    $cell = new html_table_cell(new lang_string('class', 'thpage', null, $lang));
    $cell->header = true;
    $cell->style = $style . 'width: 30%;';
    $head->cells[] = $cell;
    $cell = new html_table_cell(new lang_string('teacher', 'thpage', null, $lang));
    $cell->header = true;
    $cell->style = $style . 'width: 20%;';
    $head->cells[] = $cell;
    $cell = new html_table_cell(new lang_string('cellphone', 'thpage', null, $lang));
    $cell->header = true;
    $cell->style = $style . 'width: 20%;';
    $head->cells[] = $cell;
    $cell = new html_table_cell(new lang_string('email', 'thpage', null, $lang));
    $cell->header = true;
    $cell->style = $style . 'width: 30%;';
    $head->cells[] = $cell;
    $table->data[] = $head;


    foreach ($cohort as $key => $value) {

        if (!empty($value)) {
         
            $row = new html_table_row();
            $row->attributes = array('nobr' => "true", 'class' => "");
            $cell = new html_table_cell(implode(",", $value));
            $cell->style = 'text-align: center';
            $row->cells[] = $cell;
            $cell = new html_table_cell($gv[$key]->fullname);
            $cell->style = 'text-align: center';
            $row->cells[] = $cell;
            if (substr(0 != $gv[$key]->phone, 0, 1)) {
                $gv[$key]->phone = '0' . $gv[$key]->phone;
            }
            $cell = new html_table_cell($gv[$key]->phone);
            $cell->style = 'text-align: center';
            $row->cells[] = $cell;
            $cell = new html_table_cell('<a>' .$gv[$key]->email . '</a>');
            $cell->style = 'text-align: center';
            $row->cells[] = $cell;

            $table->data[] = $row;
        }
    }


    $thongtin_giangvien = html_writer::start_tag('p');
    $thongtin_giangvien .= html_writer::tag('b', new lang_string('specialized_lecturer', 'thpage', null, $lang));
    $thongtin_giangvien .= html_writer::end_tag('p');

    foreach ($teachers as $key => $teacher) {
        $a = new stdClass();
        $a->teachername = fullname($teacher);
        $a->email = $teacher->email;
        $a->phone = $DB->get_field('user', 'phone2', array('id' => $key));
        $thongtin_giangvien .= new lang_string('teacher_information', 'thpage', $a, $lang);
        $thongtin_giangvien .= html_writer::empty_tag('br');
    }
    $thongtin_giangvien = rtrim($thongtin_giangvien, ', ');
    $thongtin_giangvien .= html_writer::start_tag('p');
    $thongtin_giangvien .= html_writer::tag('b', new lang_string('support_to_answer_questions', 'thpage', null, $lang));
    $thongtin_giangvien .= html_writer::end_tag('p');

    $thongtin_giangvien .= html_writer::table($table);

    $thongtin_giangvien .= html_writer::start_tag('p');
    $thongtin_giangvien .= html_writer::tag('b', new lang_string('learning_management', 'thpage', null, $lang));;
    $thongtin_giangvien .= html_writer::end_tag('p');
    foreach ($qlht as $key => $value) {
        if (!empty($value)) {
           $a = new stdClass();
           $a->class = implode(",", $value);
           $a->fullname = $gv[$key]->fullname;
           if (substr(0 != $gv[$key]->phone, 0, 1)) {
                $gv[$key]->phone = '0' . $gv[$key]->phone;
           }
           $a->phone = $gv[$key]->phone;
           $a->email = $gv[$key]->email;
           $thongtin_giangvien .= new lang_string('qlht', 'thpage', $a, $lang);
        }
    }

    $week = 3600 * 24 * 7;
    $day = 3600 * 24;
    $startdate =  $course->startdate;
    $tuan1_from = date('d/m/Y', $startdate);
    $tuan1_to = date('d/m/Y', $startdate + $week - $day);
    // $week1 = 'Tuần 01 (' . $tuan1_from . ' - ' . $tuan1_to . ')';
    $a = new stdClass();
    $a->no = 1;
    $a->date = $tuan1_from . ' - ' . $tuan1_to;
    $week1 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan2_from = date('d/m/Y', $startdate + $week);
    $tuan2_to = date('d/m/Y', $startdate + 2 * $week - $day);
    // $week2 = 'Tuần 02 (' . $tuan2_from . ' - ' . $tuan2_to . ')';
    $a = new stdClass();
    $a->no = 2;
    $a->date = $tuan2_from . ' - ' . $tuan2_to;
    $week2 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan3_from = date('d/m/Y', $startdate + 2 * $week);
    $tuan3_to = date('d/m/Y', $startdate + 3 * $week - $day);
    // $week3 = 'Tuần 03 (' . $tuan3_from . ' - ' . $tuan3_to . ')';
    $a = new stdClass();
    $a->no = 3;
    $a->date = $tuan3_from . ' - ' . $tuan3_to;
    $week3 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan4_from = date('d/m/Y', $startdate + 3 * $week);
    $tuan4_to = date('d/m/Y', $startdate + 4 * $week - $day);
    // $week4 = 'Tuần 04 (' . $tuan4_from . ' - ' . $tuan4_to . ')';
    $a = new stdClass();
    $a->no = 4;
    $a->date = $tuan4_from . ' - ' . $tuan4_to;
    $week4 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan5_from = date('d/m/Y', $startdate + 4 * $week);
    $tuan5_to = date('d/m/Y', $startdate + 5 * $week - $day);
    // $week5 =  'Tuần 05 (' . $tuan5_from . ' - ' . $tuan5_to . ')';
    $a = new stdClass();
    $a->no = 5;
    $a->date = $tuan5_from . ' - ' . $tuan5_to;
    $week5 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan6_from = date('d/m/Y', $startdate + 5 * $week);
    $tuan6_to = date('d/m/Y', $startdate + 6 * $week - $day);
    // $week6 = 'Tuần 06 (' . $tuan6_from . ' - ' . $tuan6_to .')';
    $a = new stdClass();
    $a->no = 6;
    $a->date = $tuan6_from . ' - ' . $tuan6_to;
    $week6 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan7_from = date('d/m/Y', $startdate + 6 * $week);
    $tuan7_to = date('d/m/Y', $startdate + 7 * $week - $day);
    // $week7 = 'Tuần 07 (' . $tuan7_from . ' - ' . $tuan7_to . ')';
    $a = new stdClass();
    $a->no = 7;
    $a->date = $tuan7_from . ' - ' . $tuan7_to;
    $week7 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan8_from = date('d/m/Y', $startdate + 7 * $week);
    $tuan8_to = date('d/m/Y', $startdate + 8 * $week - $day);
    // $week8 = 'Tuần 08 (' . $tuan8_from . ' - ' . $tuan8_to .')';
    $a = new stdClass();
    $a->no = 8;
    $a->date = $tuan8_from . ' - ' . $tuan8_to;
    $week8 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));

    $tuan9_from = date('d/m/Y', $startdate + 8 * $week);
    $tuan9_to = date('d/m/Y', $startdate + 9 * $week);
    $week9 = html_writer::tag('b', $tuan9_to);

    $tuan10_to = date('d/m/Y', $startdate + 10 * $week - $day);
    $a = new stdClass();
    $a->no = '9+10';
    $a->date = $tuan9_from . ' - ' . $tuan10_to;
    $week10 = html_writer::tag('b', new lang_string('week', 'thpage', $a, $lang));;

    $kt1 = date('d/m/Y', $startdate + $thpage->exam1 * $week);
    $kt2 = date('d/m/Y', $startdate + $thpage->exam2 * $week);
    $study_time = $tuan1_from . '-' . $tuan8_to;

    $thi = html_writer::tag('b', date('d/m/Y', $startdate + get_config('thpage', 'tuan_thi') * $week));

    $config = get_config('thpage');
    $thpage->content = str_replace('[['.trim($config->study_time).']]', $study_time, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->teacher).']]', $thongtin_giangvien, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week1).']]', $week1, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week2).']]', $week2, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week3).']]', $week3, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week4).']]', $week4, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week5).']]', $week5, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week6).']]', $week6, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week7).']]', $week7, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week8).']]', $week8, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week9).']]', $week9, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->week10).']]', $week10, $thpage->content);
    $thpage->content = str_replace('[['.trim($config->kt1).']]', '<b>'.$kt1.'</b>', $thpage->content);
    $thpage->content = str_replace('[['.trim($config->kt2).']]', '<b>'.$kt2.'</b>', $thpage->content);
    $thpage->content = str_replace('[['.trim($config->thi).']]', $thi, $thpage->content);
    $thpage->content = '<style>'.file_get_contents($CFG->dirroot . '/mod/thpage/styles.css').'</style>' . $thpage->content;

    // require_course_login($course, true, $cm);
    $context = context_module::instance($cm->id);
    require_capability('mod/thpage:view', $context);
    $content = file_rewrite_pluginfile_urls($thpage->content, 'pluginfile.php', $context->id, 'mod_thpage', 'content', $thpage->revision);

    return $content;
}

function thpage_create_pdf($course, $content, $action, $path='', $font = 'freeserif') {
    global $SITE, $CFG;

    $pattern = '/<div\s+id="th_table_content"\s+[^>]*>(.*?)<\/div>/s';
    $content = preg_replace($pattern, '', $content);

    $config = get_config('thpage');
    $fontfamily = PDF_FONT_NAME_MAIN;
    $pdf_header = get_config('thpage', 'pdf_header');

    $doc = new thpagepdf();
    $doc->SetMargins(15, 30);

    $doc->setPrintHeader(true);
    $doc->setHeaderMargin(10);
    $doc->setHeaderFont(array($fontfamily, 'b', 10));

    $doc->setPrintFooter(true);
    $doc->setFooterMargin(10);
    $doc->setFooterFont(array($fontfamily, '', 8));

    $doc->AddPage();
    $doc->SetFont($font, '', 12);
    // -- set new background ---
    $bMargin = $doc->getBreakMargin();
    // get current auto-page-break mode
    $auto_page_break = $doc->getAutoPageBreak();
    // disable auto-page-break
    $doc->SetAutoPageBreak(false, 0);
    // set bacground image
    $img_file = $CFG->dirroot .'/mod/thpage/pix/aum.png';
    // restore auto-page-break status
    $doc->SetAutoPageBreak($auto_page_break, $bMargin);
    // set the starting point for the page content
    $doc->setPageMark();

    $doc->writeHTML($content);
    $total_pages = $doc->getNumPages();
    unset($doc);


    $doc = new thpagepdf();
    $doc->SetTitle($course->fullname);
    $doc->SetAuthor('AUM-TH');
    $doc->SetMargins(15, 30);

    $doc->setPrintHeader(true);
    $doc->setHeaderMargin(10);
    $doc->setHeaderFont(array($fontfamily, 'b', 10));
    $fs = get_file_storage();
    $files = $fs->get_area_files(SYSCONTEXTID, 'mod_thpage', 'logo', 0, 'sortorder DESC, id ASC', false);
    
    foreach ($files as $key => $f) {
        $contenthash = $f->get_contenthash();
        $dir1 = substr($contenthash, 0, 2);
        $dir2 = substr($contenthash, 2, 2);
        $contentpath = $dir1 . "/" . $dir2 . "/" . $contenthash;
    }

    if (isset($contentpath)) {
        $doc->setHeaderData($CFG->dataroot . '/filedir/' . $contentpath, 10, $pdf_header);
    } else {
        $doc->setHeaderData('', 10, $SITE->fullname, $CFG->wwwroot);
    }

    $doc->setPrintFooter(true);
    $doc->setFooterMargin(10);
    $doc->setFooterFont(array($fontfamily, '', 8));

    $doc->SetFont($font, '', 12);
    $doc->Ln(6);
    $doc->SetTextColor(0,0,0);

    // $doc->SetFont($font, '', 12);

    $bMargin = $doc->getBreakMargin();
    $auto_page_break = $doc->getAutoPageBreak();

    for ($i = 1; $i <= $total_pages; $i++) {
        $doc->AddPage();
        $doc->SetAutoPageBreak(false, 0);
        $doc->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        $doc->SetAutoPageBreak($auto_page_break, $bMargin);
        $doc->setPageMark();
    }
    $doc->setPage(1);
    $doc->writeHTML($content);
   
    $doc->Output($path . '/' . get_string('dcct', 'mod_thpage') .'.pdf', $action);
    // exit;
}

/**
 * List of features supported in thpage module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function thpage_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        // case FEATURE_MOD_PURPOSE:             return MOD_PURPOSE_ASSESSMENT;
        default: return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function thpage_reset_userdata($data) {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function thpage_get_view_actions() {
    return array('view','view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function thpage_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add thpage instance.
 * @param stdClass $data
 * @param mod_thpage_mod_form $mform
 * @return int new thpage instance id
 */
function thpage_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro']   = $data->printintro;
    $displayoptions['printlastmodified'] = $data->printlastmodified;
    $data->displayoptions = serialize($displayoptions);

    if ($mform) {
        $data->content       = $data->thpage['text'];
        $data->contentformat = $data->thpage['format'];
    }

    $data->id = $DB->insert_record('thpage', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

    if ($mform and !empty($data->thpage['itemid'])) {
        $draftitemid = $data->thpage['itemid'];
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_thpage', 'content', 0, thpage_get_editor_options($context), $data->content);
        $DB->update_record('thpage', $data);
    }

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'thpage', $data->id, $completiontimeexpected);

    // $event = \mod_thpage\event\course_thpage_created::create(array('context' => $context, 'objectid' => $data->id, 'other' => ['cmid' => $cmid]));
    // $event->trigger();

    return $data->id;
}

/**
 * Update thpage instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function thpage_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;
    $draftitemid = $data->thpage['itemid'];

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->revision++;

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro']   = $data->printintro;
    $displayoptions['printlastmodified'] = $data->printlastmodified;
    $data->displayoptions = serialize($displayoptions);

    $data->content       = $data->thpage['text'];
    $data->contentformat = $data->thpage['format'];

    $DB->update_record('thpage', $data);

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_thpage', 'content', 0, thpage_get_editor_options($context), $data->content);
        $DB->update_record('thpage', $data);
    }

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'thpage', $data->id, $completiontimeexpected);

    // $event = \mod_thpage\event\course_thpage_updated::create(array('context' => $context, 'objectid' => $data->id, 'other' => ['cmid' => $cmid]));
    // $event->trigger();

    return true;
}

/**
 * Delete thpage instance.
 * @param int $id
 * @return bool true
 */
function thpage_delete_instance($id) {
    global $DB;

    if (!$thpage = $DB->get_record('thpage', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('thpage', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'thpage', $id, null);

    // note: all context files are deleted automatically

    $DB->delete_records('thpage', array('id'=>$thpage->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info Info to customise main thpage display
 */
function thpage_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if (!$thpage = $DB->get_record('thpage', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $thpage->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('thpage', $thpage, $coursemodule->id, false);
    }

    if ($thpage->display != RESOURCELIB_DISPLAY_POPUP) {
        return $info;
    }

    $fullurl = "$CFG->wwwroot/mod/thpage/view.php?id=$coursemodule->id&amp;inpopup=1";
    $options = empty($thpage->displayoptions) ? array() : unserialize($thpage->displayoptions);
    $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
    $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
    $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    return $info;
}


/**
 * Lists all browsable file areas
 *
 * @package  mod_thpage
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array
 */
function thpage_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('content', 'thpage');
    return $areas;
}

/**
 * File browsing support for thpage module content area.
 *
 * @package  mod_thpage
 * @category files
 * @param stdClass $browser file browser instance
 * @param stdClass $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function thpage_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_thpage', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_thpage', 'content', 0);
            } else {
                // not found
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/thpage/locallib.php");
        return new thpage_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
    }

    // note: thpage_intro handled in file_browser automatically

    return null;
}

/**
 * Serves the thpage files.
 *
 * @package  mod_thpage
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function thpage_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

// print_object($context);
    if ($context->contextlevel != CONTEXT_MODULE and $context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    require_course_login($course, true, $cm);
    if (!has_capability('mod/thpage:view', $context)) {
        return false;
    }

    if ($filearea !== 'content' and $filearea !== 'pdf' and $filearea !== 'example_pdf') {
        // intro is handled automatically in pluginfile.php
        return false;
    }
// exit;
    // $arg could be revision number or index.html
    $arg = array_shift($args);

    if ($filearea == 'content') {
        if ($arg == 'index.html' || $arg == 'index.htm') {
            // serve page content
            $filename = $arg;

            if (!$page = $DB->get_record('thpage', array('id'=>$cm->instance), '*', MUST_EXIST)) {
                return false;
            }

            // We need to rewrite the pluginfile URLs so the media filters can work.
            $content = file_rewrite_pluginfile_urls($page->content, 'webservice/pluginfile.php', $context->id, 'mod_thpage', 'content',
                                                    $page->revision);
            $formatoptions = new stdClass;
            $formatoptions->noclean = true;
            $formatoptions->overflowdiv = true;
            $formatoptions->context = $context;
            $content = format_text($content, $page->contentformat, $formatoptions);

            // Remove @@PLUGINFILE@@/.
            $options = array('reverse' => true);
            $content = file_rewrite_pluginfile_urls($content, 'webservice/pluginfile.php', $context->id, 'mod_thpage', 'content',
                                                    $page->revision, $options);
            $content = str_replace('@@PLUGINFILE@@/', '', $content);

            send_file($content, $filename, 0, 0, true, true);
        } else {
            $fs = get_file_storage();
            $relativepath = implode('/', $args);
            $fullpath = "/$context->id/mod_thpage/$filearea/0/$relativepath";
            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                $page = $DB->get_record('thpage', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
                if ($page->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                    return false;
                }
                if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_thpage', 'content', 0)) {
                    return false;
                }
                //file migrate - update flag
                $page->legacyfileslast = time();
                $DB->update_record('thpage', $page);
            }

            // finally send the file
            send_stored_file($file, null, 0, $forcedownload, $options);
        }
    }
    if ($filearea == 'pdf') {
        if ($arg == 'index.html' || $arg == 'index.htm') {
            // serve page content
            $filename = $arg;

            if (!$page = $DB->get_record('thpage', array('id'=>$cm->instance), '*', MUST_EXIST)) {
                return false;
            }

            // We need to rewrite the pluginfile URLs so the media filters can work.
            $content = file_rewrite_pluginfile_urls($page->content, 'webservice/pluginfile.php', $context->id, 'mod_thpage', 'pdf',
                                                    $page->revision);
            $formatoptions = new stdClass;
            $formatoptions->noclean = true;
            $formatoptions->overflowdiv = true;
            $formatoptions->context = $context;
            $content = format_text($content, $page->contentformat, $formatoptions);

            // Remove @@PLUGINFILE@@/.
            $options = array('reverse' => true);
            $content = file_rewrite_pluginfile_urls($content, 'webservice/pluginfile.php', $context->id, 'mod_thpage', 'pdf',
                                                    $page->revision, $options);
            $content = str_replace('@@PLUGINFILE@@/', '', $content);

            send_file($content, $filename, 0, 0, true, true);
        } else {
            $fs = get_file_storage();
            $relativepath = implode('/', $args);
            $fullpath = "/$context->id/mod_thpage/$filearea/0/$relativepath";
            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                $page = $DB->get_record('thpage', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
                if ($page->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                    return false;
                }
                if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_thpage', 'pdf', 0)) {
                    return false;
                }
                //file migrate - update flag
                $page->legacyfileslast = time();
                $DB->update_record('thpage', $page);
            }

            // finally send the file
            send_stored_file($file, null, 0, $forcedownload, $options);
        }
    }

    if ($filearea == 'example_pdf') {
        if ($arg == 'index.html' || $arg == 'index.htm') {
            // serve page content
            $filename = $arg;

            if (!$page = $DB->get_record('thpage', array('id'=>$cm->instance), '*', MUST_EXIST)) {
                return false;
            }

            // We need to rewrite the pluginfile URLs so the media filters can work.
            $content = file_rewrite_pluginfile_urls($page->content, 'webservice/pluginfile.php', SYSCONTEXTID, 'mod_thpage', 'example_pdf',
                                                    $page->revision);
            $formatoptions = new stdClass;
            $formatoptions->noclean = true;
            $formatoptions->overflowdiv = true;
            $formatoptions->context = $context;
            $content = format_text($content, $page->contentformat, $formatoptions);

            // Remove @@PLUGINFILE@@/.
            $options = array('reverse' => true);
            $content = file_rewrite_pluginfile_urls($content, 'webservice/pluginfile.php', SYSCONTEXTID, 'mod_thpage', 'example_pdf',
                                                    $page->revision, $options);
            $content = str_replace('@@PLUGINFILE@@/', '', $content);

            send_file($content, $filename, 0, 0, true, true);
        } else {
            $fs = get_file_storage();
            $relativepath = implode('/', $args);
            $fullpath = "/$context->id/mod_thpage/$filearea/0/$relativepath";
            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                $page = $DB->get_record('thpage', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
                if ($page->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                    return false;
                }
                if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_thpage', 'example_pdf', 0)) {
                    return false;
                }
                //file migrate - update flag
                $page->legacyfileslast = time();
                $DB->update_record('thpage', $page);
            }

            // finally send the file
            send_stored_file($file, null, 0, $forcedownload, $options);
        }
    }
}
/**
 * Return a list of thpage types
 * @param string $thpagetype current thpage type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function thpage_thpage_type_list($thpagetype, $parentcontext, $currentcontext) {
    $module_thpagetype = array('mod-thpage-*'=>get_string('thpage-mod-thpage-x', 'thpage'));
    return $module_thpagetype;
}

/**
 * Export thpage resource contents
 *
 * @return array of file content
 */
function thpage_export_contents($cm, $baseurl) {

    global $CFG, $DB;
    $contents = array();
    $context = context_module::instance($cm->id);
    $thpage = $DB->get_record('thpage', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_thpage', 'pdf', 0, 'sortorder DESC, id ASC', false);

    if ($files) {
        foreach ($files as $fileinfo) {
            $file = array();
            $file['type'] = 'file';
            $file['filename']     = $fileinfo->get_filename();
            $file['filepath']     = $fileinfo->get_filepath();
            $file['filesize']     = $fileinfo->get_filesize();
            $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_thpage/pdf/'.$thpage->revision.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
            $file['timecreated']  = $fileinfo->get_timecreated();
            $file['timemodified'] = $fileinfo->get_timemodified();
            $file['sortorder']    = $fileinfo->get_sortorder();
            $file['userid']       = $fileinfo->get_userid();
            $file['author']       = $fileinfo->get_author();
            $file['license']      = $fileinfo->get_license();
            $file['mimetype']     = $fileinfo->get_mimetype();
            $file['isexternalfile'] = $fileinfo->is_external_file();
            if ($file['isexternalfile']) {
                $file['repositorytype'] = $fileinfo->get_repository_type();
            }
            $contents[] = $file;
        }
    } else {
        $files = $fs->get_area_files(SYSCONTEXTID, 'mod_thpage', 'example_pdf', 0, 'sortorder DESC, id ASC', false);

        foreach ($files as $fileinfo) {
            $file = array();
            $file['type'] = 'file';
            $file['filename']     = $fileinfo->get_filename();
            $file['filepath']     = $fileinfo->get_filepath();
            $file['filesize']     = $fileinfo->get_filesize();
            $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'. SYSCONTEXTID .'/mod_thpage/example_pdf/'.$thpage->revision.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
            $file['timecreated']  = $fileinfo->get_timecreated();
            $file['timemodified'] = $fileinfo->get_timemodified();
            $file['sortorder']    = $fileinfo->get_sortorder();
            $file['userid']       = $fileinfo->get_userid();
            $file['author']       = $fileinfo->get_author();
            $file['license']      = $fileinfo->get_license();
            $file['mimetype']     = $fileinfo->get_mimetype();
            $file['isexternalfile'] = $fileinfo->is_external_file();
            if ($file['isexternalfile']) {
                $file['repositorytype'] = $fileinfo->get_repository_type();
            }
            $contents[] = $file;
        }
    }

    

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function thpage_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'text/html', 'message' => get_string('createthpage', 'thpage')),
                     array('identifier' => 'text', 'message' => get_string('createthpage', 'thpage'))
                 ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function thpage_dndupload_handle($uploadinfo) {
    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    if ($uploadinfo->type == 'text/html') {
        $data->contentformat = FORMAT_HTML;
        $data->content = clean_param($uploadinfo->content, PARAM_CLEANHTML);
    } else {
        $data->contentformat = FORMAT_PLAIN;
        $data->content = clean_param($uploadinfo->content, PARAM_TEXT);
    }
    $data->coursemodule = $uploadinfo->coursemodule;

    // Set the display options to the site defaults.
    $config = get_config('thpage');
    $data->display = $config->display;
    $data->popupheight = $config->popupheight;
    $data->popupwidth = $config->popupwidth;
    $data->printheading = $config->printheading;
    $data->printintro = $config->printintro;
    $data->printlastmodified = $config->printlastmodified;

    return thpage_add_instance($data, null);
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $thpage       thpage object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function thpage_view($thpage, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $thpage->id
    );

    $event = \mod_thpage\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('thpage', $thpage);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function thpage_check_updates_since(cm_info $cm, $from, $filter = array()) {
    $updates = course_check_module_updates_since($cm, $from, array('content'), $filter);
    return $updates;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_thpage_core_calendar_provide_event_action(calendar_event $event,
                                                      \core_calendar\action_factory $factory, $userid = 0) {
    global $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['thpage'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/thpage/view.php', ['id' => $cm->id]),
        1,
        true
    );
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param  string $filearea The filearea.
 * @param  array  $args The path (the part after the filearea and before the filename).
 * @return array The itemid and the filepath inside the $args path, for the defined filearea.
 */
function mod_thpage_get_path_from_pluginfile(string $filearea, array $args) : array {
    // thpage never has an itemid (the number represents the revision but it's not stored in database).
    array_shift($args);

    // Get the filepath.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}


function mod_thpage_controls(context $context, moodle_url $currenturl)
{
    $tabs       = array();
    $currenttab = 'view';
    $view       = new moodle_url('/mod/thpage/index.php');

    if (has_capability('mod/thpage:view', $context)) {
        $addurl = new moodle_url('/mod/thpage/viewall.php');
        $tabs[] = new tabobject('view', $addurl, get_string('all'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view';
        }
    }

    if (has_capability('mod/thpage:view', $context)) {
        $addurl = new moodle_url('/mod/thpage/edit.php');
        $tabs[] = new tabobject('edit', $addurl, get_string('add'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'edit';
        }
    }
    if (has_capability('mod/thpage:view', $context)) {
        $addurl = new moodle_url('/mod/thpage/import.php');
        $tabs[] = new tabobject('import', $addurl, get_string('import', 'mod_thpage'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'import';
        }
    }
    

    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}


function thpage_export_parse($list_data)
{
    if (empty($list_data)) {
        return array();
    } else {
        $rawlines = explode(PHP_EOL, $list_data);
        $result   = array();
        foreach ($rawlines as $rawline) {
            $result[] = trim($rawline);
        }
        return $result;
    }
}

function thpage_export_get_content($contents)
{
    $max = count($contents) - 1;
    for ($i = 1; $i < $max; ++$i) {
        $listcourses_new[] = $contents[$i];
    }
    return $listcourses_new;
}

function thpage_display_table_import_dcct($data_import_dcct)
{
    global $DB;
    $table = new html_table();
    // $table->head = array('STT', 'Mã lớp', 'Họ tên', 'Sđt', 'Email', 'Chức vụ', 'Giới tính');
    $table->head = array('STT',
                    get_string('cohorts', 'mod_thpage'),
                    get_string('fullname'),
                    get_string('phone'),
                    get_string('email'),
                    get_string('role'), 
                    get_string('gender', 'mod_thpage'));
    $stt = 0;
    foreach ($data_import_dcct as $k => $data) {

        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;

        $cell = new html_table_cell($data->cohorts);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->fullname);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->phone);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->email);
        $row->cells[] = $cell;
        
        if ($data->role == 1) {
            $role = 'GVCN';
        } else {
            $role = 'QLHT';
        }

        $cell = new html_table_cell($role);
        $row->cells[] = $cell;

        if ($data->gender == 1) {
            $gender = 'Nam';
        } else {
            $gender = 'Nữ';
        }

        $cell = new html_table_cell($gender);
        $row->cells[] = $cell;

        $table->data[] = $row;
        
    }

    $html = html_writer::table($table);
    return $html;
}

function thpage_display_table_export_dcct_error($errors)
{
    $table1 = new html_table($errors);
    $table1->head = array(get_string('stt', 'mod_thpage'), get_string('hints', 'mod_thpage'));
    $stt = 0;
    foreach ($errors as $k => $error) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($error);
        $row->cells[] = $cell;
        $table1->data[] = $row;
    }
    $html1 = html_writer::table($table1);
    return $html1;
}

function thpage_create_pdf_task() {
    global $CFG, $DB;

    // get all course
    $params = array('now' => time());
    $sql = "SELECT id
            FROM {course}
            WHERE startdate > :now";
    $courses = $DB->get_records_sql($sql, $params);
    mtrace(json_encode($courses));
    $path = $CFG->tempdir;
    $tempdir = make_temp_directory('thpage_create_pdf');
    check_dir_exists($tempdir);
    foreach ($courses as $key => $c) {
        $courseid = $c->id;

        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            mtrace("No course id: " . json_encode($courseid));
            mtrace("My ad-hoc task ended");
            exit;
        }

        $sql = "SELECT cm.id,cm.instance,ctx.id as contextid,p.name
            from {course_modules} cm
            join {thpage} p on p.id=cm.instance
            join {context} ctx on ctx.instanceid=cm.id
            where cm.course=:course and cm.module=(select id from {modules} where name=:name)";
        $params = array('course' => $courseid, 'name' => 'thpage');
        $records = $DB->get_records_sql($sql, $params);
         mtrace(json_encode($records));

        foreach ($records as $key => $record) {
            if (!$thpage = $DB->get_record('thpage', array('id' => $record->instance))) {
                mtrace("No thpage id: " . json_encode($record->instance));
                mtrace("My ad-hoc task ended");
                exit;
            }
            if (!$cm = $DB->get_record('course_modules', array('id' => $record->id))) {
                mtrace("No course_modules id: " . json_encode($record->id));
                mtrace("My ad-hoc task ended");
                exit;
            }

            $thpage->content = thpage_create_content($course, $thpage, $cm);
            mtrace($record->name);
            try {
                thpage_create_pdf($course, $thpage->content, 'F', $tempdir, $thpage->font);
                $filename = get_string('dcct', 'mod_thpage') . '.pdf';
                $fs = get_file_storage();
                $fileinfo = [
                    'contextid' => $record->contextid,
                    'component' => 'mod_thpage',
                    'filearea'  => 'pdf',
                    'itemid'    => 0,
                    'filepath'  => '/',
                    'filename'  => $filename,
                ];
                mtrace('fileinfo');
                mtrace(json_encode($fileinfo));

                $files = $fs->get_area_files(
                    $fileinfo['contextid'],
                    $fileinfo['component'],
                    $fileinfo['filearea'],
                    $fileinfo['itemid']
                );
                foreach ($files as $file) {
                    $file->delete();
                    mtrace("Delete old file");
                }

                $fs->create_file_from_pathname($fileinfo, $tempdir . '/' . $filename);
                @unlink($tempdir . '/' . $filename);
                mtrace("Create pdf success");
            } catch (Exception $e) {
                mtrace(json_encode($e));
                mtrace("Create pdf error");
            }

            mtrace("My ad-hoc task ended");
        }
    }
}

function thpage_create_pdf_adhoc_task($courseid, $cmid, $thpageid, $contextid) {
    global $CFG, $DB;

    $path = $CFG->tempdir;
    $tempdir = make_temp_directory('thpage_create_pdf');
    check_dir_exists($tempdir);

    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        // mtrace("No course id: " . json_encode($courseid));
        // mtrace("My ad-hoc task ended");
        return false;
    }

    if (!$thpage = $DB->get_record('thpage', array('id' => $thpageid))) {
        // mtrace("No thpage id: " . json_encode($thpageid));
        // mtrace("My ad-hoc task ended");
       return false;
    }
    if (!$cm = $DB->get_record('course_modules', array('id' => $cmid))) {
        // mtrace("No thpage id: " . json_encode($cmid));
        // mtrace("My ad-hoc task ended");
        return false;
    }

    $thpage->content = thpage_create_content($course, $thpage, $cm);

    try {
        thpage_create_pdf($course, $thpage->content, 'F', $tempdir, $thpage->font);
        $filename = get_string('dcct', 'mod_thpage') . '.pdf';
        $fs = get_file_storage();
        $fileinfo = [
            'contextid' => $contextid,
            'component' => 'mod_thpage',
            'filearea'  => 'pdf',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => $filename,
        ];
        // mtrace('fileinfo');
        // mtrace(json_encode($fileinfo));

        $files = $fs->get_area_files(
            $fileinfo['contextid'],
            $fileinfo['component'],
            $fileinfo['filearea'],
            $fileinfo['itemid']
        );
        foreach ($files as $file) {
            $file->delete();
            // mtrace("Delete old file");
        }

        $fs->create_file_from_pathname($fileinfo, $tempdir . '/' . $filename);
        @unlink($tempdir . '/' . $filename);
        // mtrace("Create pdf success");
        return true;
    } catch (Exception $e) {
        // mtrace(json_encode($e));
        // mtrace("Create pdf error");
        return false;
    }

    // mtrace("My ad-hoc task ended");
}