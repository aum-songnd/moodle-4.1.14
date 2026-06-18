<?php
require '../../config.php';
require_once "$CFG->libdir/formslib.php";
require_once "edit_form.php";
require_once "lib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';

global $DB, $CFG, $COURSE;
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'block_th_report_total_enrolments', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_report_total_enrolments:view', context_course::instance($COURSE->id));
$PAGE->set_url(new moodle_url('/blocks/th_report_total_enrolments/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('namereport', 'block_th_report_total_enrolments'));
$PAGE->set_title(get_string('namereport', 'block_th_report_total_enrolments'));
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.reportenrollments-table', "report_totol_enrolment", current_language()));
$editurl = new moodle_url('/blocks/th_report_total_enrolments/view.php');
$settingsnode = $PAGE->settingsnav->add(get_string('pluginname', 'block_th_report_total_enrolments'), $editurl);

$edit_form = new edit_form();
$fromform = $edit_form->get_data();
if ($edit_form->is_submitted()) {
    $fromform = $edit_form->get_data();
    if ($fromform) {
        $startdate = $fromform->startdate;
        $enddate = $fromform->enddate;
        $enddate = $enddate + strtotime("+23 hours 59 minutes 59 seconds", 0);
    }
} else {
    $startdate = optional_param('startdate', 0, PARAM_INT);
    if (!$startdate) {
        $startdate = time() + strtotime("-6 months", 0);
    }
    $enddate = optional_param('enddate', time(), PARAM_INT);
    $edit_form->set_data(array('startdate' => $startdate, 'enddate' => $enddate));
}

if ($edit_form->get_data() != null) {

    $table = new html_table();
    $headrows = new html_table_row();
    $cell = new html_table_cell(get_string('timecreate', 'block_th_report_total_enrolments'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('time', 'block_th_report_total_enrolments'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('role', 'block_th_report_total_enrolments'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('coursename', 'block_th_report_total_enrolments'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('total', 'block_th_report_total_enrolments'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $cell = new html_table_cell(get_string('status'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;
    $rightrows = [];
    $rightrows[] = $headrows;

    $config = get_config('local_thlib');
    $strLeft = trim($config->custom_fields1, ' ');
    $strRight = trim($config->custom_fields2, ' ');
    if ($strLeft == '') {
        $cellLeft = 0;
    } else {
        $cellLeft = count(explode(',', trim($config->custom_fields1)));
    }
    if ($strRight == '') {
        $cellRight = 0;
    } else {
        $cellRight = count(explode(',', trim($config->custom_fields2)));
    }

    $tt = $DB->get_records_sql("SELECT DISTINCT u.id
		             FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra
		             WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND u.deleted=0 AND u.suspended=0 AND ue.timestart >= $startdate AND ue.timestart < $enddate");
    $userid_arr = [];
    if ($tt != null) {
        foreach ($tt as $key => $value) {
            $userid_arr[] = $value->id;
        }
    }
    // $extra = array_filter(explode(',', $CFG->showuseridentity));
    // $userfields = array_values($extra);

    // $usernamefield = get_all_user_name_fields();
    // $usernamefield = implode(",", $usernamefield);
    // $alluserfields = "id," . $usernamefield;

    // if (count($userfields) > 0) {
    //     $alluserfields .= "," . implode(',', $userfields);
    // }

    // $alluserfields .= "," . "email";

    // $user_arr = $DB->get_records('user', array('deleted' => 0), "", $alluserfields);

    $extra = array_filter(explode(',', $CFG->showuseridentity));
    $userfields = array_values($extra);

    // Phân loại trường
    $tablecolumns = $DB->get_columns('user');
    $tablecolumnskeys = array_keys($tablecolumns);

    $normalfields = [];
    $profilefields = [];

    foreach ($userfields as $field) {
        if (in_array($field, $tablecolumnskeys)) {
            $normalfields[] = $field;
        } elseif (strpos($field, 'profile_field_') === 0) {
            $profilefields[] = $field;
        }
    }

    $usernamefields = implode(',', get_all_user_name_fields());
    $alluserfields = "id,$usernamefields";

    if (!empty($normalfields)) {
        $alluserfields .= "," . implode(',', $normalfields);
    }
    $alluserfields .= ",email";

    // Lấy danh sách user đang hoạt động
    $user_arr = $DB->get_records('user', ['deleted' => 0, 'suspended' => 0], '', $alluserfields);

    // Lấy giá trị các profile field nếu có
    if (!empty($profilefields)) {
        $shortnames = array_map(function($f) {
            return str_replace('profile_field_', '', $f);
        }, $profilefields);

        list($inSql, $inParams) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
        $profiledata = $DB->get_records_sql("
            SELECT uid.userid, uif.shortname, uid.data
            FROM {user_info_data} uid
            JOIN {user_info_field} uif ON uid.fieldid = uif.id
            WHERE uif.shortname $inSql
        ", $inParams);

        foreach ($profiledata as $record) {
            $fieldname = 'profile_field_' . $record->shortname;
            if (isset($user_arr[$record->userid])) {
                $user_arr[$record->userid]->$fieldname = $record->data;
            }
        }

        // Gán giá trị rỗng nếu user không có dữ liệu
        foreach ($user_arr as &$u) {
            foreach ($profilefields as $field) {
                if (!isset($u->$field)) {
                    $u->$field = '';
                }
            }
        }
        unset($u);
    }
    list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);

    if ($fromform->filter == 0) {

        $thisdaystart = strtotime(date("m/d/Y", strtotime("this day", $enddate)));
        $thisdayend = $thisdaystart + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $thisdaystart1 = strtotime(date("m/d/Y", strtotime("last day", $enddate)));
        $thisdayend1 = $thisdaystart1 + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $thisdaystart2 = strtotime(date("m/d/Y", strtotime("last day -1 day", $enddate)));
        $thisdayend2 = $thisdaystart2 + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $thisdaystart3 = strtotime(date("m/d/Y", strtotime("last day -2 day", $enddate)));
        $thisdayend3 = $thisdaystart3 + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $demtkday1 = demtk1($thisdaystart, $thisdayend);
        $sotk1 = count($demtkday1);
        $keyarr = 0;
        $demtkday2 = [];
        $demtkday3 = [];
        $demtkday = [];
        if ($thisdaystart > $startdate) {
            $demtkday2 = demtk1($thisdaystart1, $thisdayend1);
            $sotk2 = count($demtkday2);
        }
        if ($thisdaystart1 > $startdate) {
            $demtkday3 = demtk1($thisdaystart2, $thisdayend2);
            $sotk3 = count($demtkday3);
        }
        if ($thisdaystart2 > $startdate) {
            $demtkday = demtk1($startdate, $thisdayend3);
            $sotk = count($demtkday);
        }
        $in = true;
        if ($demtkday1 != null) {
            foreach ($demtkday1 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();

                } else {
                    $cell = new html_table_cell(ngay($thisdayend));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk1);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk1;
                $cell->attributes['data-search'] = $sotk1;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($thisdayend));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk1);
            $cell->attributes['data-order'] = $sotk1;
            $cell->attributes['data-search'] = $sotk1;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkday2 != null) {
            foreach ($demtkday2 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($thisdayend1));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk2);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk2;
                $cell->attributes['data-search'] = $sotk2;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($thisdaystart <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($thisdayend1));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk2);
            $cell->attributes['data-order'] = $sotk2;
            $cell->attributes['data-search'] = $sotk2;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkday3 != null) {
            foreach ($demtkday3 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($thisdayend2));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk3);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk3;
                $cell->attributes['data-search'] = $sotk3;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($thisdaystart1 <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($thisdayend2));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk3);
            $cell->attributes['data-order'] = $sotk3;
            $cell->attributes['data-search'] = $sotk3;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkday != null) {
            foreach ($demtkday as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($startdate) . '-' . ngay($thisdayend3));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk;
                $cell->attributes['data-search'] = $sotk;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($thisdaystart2 <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($startdate) . '-' . ngay($thisdayend3));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk);
            $cell->attributes['data-order'] = $sotk;
            $cell->attributes['data-search'] = $sotk;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['b_' . $keyarr] = $row;
            $keyarr++;
        }
        foreach ($rightrows as $key => $row) {
            $hh = explode("_", $key);
            // print_object($hh);
            if (is_numeric($hh[0]) == 1) {
                $row->cells = array_merge($leftrows[$hh[0]]->cells, $row->cells);
            } else {
                $row->cells = array_merge(array(), $row->cells);
            }
            $table->data[] = $row;
        }
        $stt = 0;
        foreach ($table->data as $value) {
            if ($stt == 0) {
                $stt++;
                continue;
            }
            $value->cells[0] = $stt;
            $stt++;
        }
    }
    if ($fromform->filter == 1) {

        $thisWeekMonday = date("m/d/Y", strtotime("this week monday", $enddate));
        $thisWeekMonday = strtotime($thisWeekMonday);

        $thisWeekSunday = date("m/d/Y", strtotime("this week sunday", $enddate));
        $thisWeekSunday = strtotime($thisWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $thisWeekMonday1 = date("m/d/Y", strtotime("this week monday - 1 week", $enddate));
        $thisWeekMonday1 = strtotime($thisWeekMonday1);

        $thisWeekSunday1 = date("m/d/Y", strtotime("this week sunday - 1 week", $enddate));
        $thisWeekSunday1 = strtotime($thisWeekSunday1) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $thisWeekMonday2 = date("m/d/Y", strtotime("this week monday - 2 week", $enddate));
        $thisWeekMonday2 = strtotime($thisWeekMonday2);

        $thisWeekSunday2 = date("m/d/Y", strtotime("this week sunday - 2 week", $enddate));
        $thisWeekSunday2 = strtotime($thisWeekSunday2) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $thisWeekSunday3 = date("m/d/Y", strtotime("this week sunday - 3 week", $enddate));
        $thisWeekSunday3 = strtotime($thisWeekSunday3) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $demtkweek1 = demtk1($thisWeekMonday, $thisWeekSunday);
        $sotk1 = count($demtkweek1);

        $keyarr = 0;
        $demtkweek2 = [];
        $demtkweek3 = [];
        $demtkweek = [];
        if ($thisWeekMonday > $startdate) {
            $demtkweek2 = demtk1($thisWeekMonday1, $thisWeekSunday1);
            $sotk2 = count($demtkweek2);
        }
        if ($thisWeekMonday1 > $startdate) {
            $demtkweek3 = demtk1($thisWeekMonday2, $thisWeekSunday2);
            $sotk3 = count($demtkweek3);
        }
        if ($thisWeekMonday2 > $startdate) {
            $demtkweek = demtk1($startdate, $thisWeekSunday3);
            $sotk = count($demtkweek);
        }
        $in = true;
        if ($demtkweek1 != null) {
            foreach ($demtkweek1 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($thisWeekMonday) . '-' . ngay($thisWeekSunday));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk1);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk1;
                $cell->attributes['data-search'] = $sotk1;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($thisWeekMonday) . '-' . ngay($thisWeekSunday));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk1);
            $cell->attributes['data-order'] = $sotk1;
            $cell->attributes['data-search'] = $sotk1;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkweek2 != null) {
            foreach ($demtkweek2 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($thisWeekMonday1) . '-' . ngay($thisWeekSunday1));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk2);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk2;
                $cell->attributes['data-search'] = $sotk2;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($thisWeekMonday <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($thisWeekMonday1) . '-' . ngay($thisWeekSunday1));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk2);
            $row->cells[] = $cell;
            $cell->attributes['data-order'] = $sotk2;
            $cell->attributes['data-search'] = $sotk2;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkweek3 != null) {
            foreach ($demtkweek3 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($thisWeekMonday2) . '-' . ngay($thisWeekSunday2));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk3);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk3;
                $cell->attributes['data-search'] = $sotk3;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($thisWeekMonday1 <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($thisWeekMonday2) . '-' . ngay($thisWeekSunday2));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk3);
            $cell->attributes['data-order'] = $sotk3;
            $cell->attributes['data-search'] = $sotk3;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkweek != null) {
            foreach ($demtkweek as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($startdate) . '-' . ngay($thisWeekSunday3));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk;
                $cell->attributes['data-search'] = $sotk;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($thisWeekMonday2 <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($startdate) . '-' . ngay($thisWeekSunday3));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk);
            $row->cells[] = $cell;
            $cell->attributes['data-order'] = $sotk;
            $cell->attributes['data-search'] = $sotk;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }

        foreach ($rightrows as $key => $row) {
            $hh = explode("_", $key);
            // print_object($hh);
            if (is_numeric($hh[0]) == 1) {
                $row->cells = array_merge($leftrows[$hh[0]]->cells, $row->cells);
            } else {
                $row->cells = array_merge(array(), $row->cells);
            }
            $table->data[] = $row;
        }
        $stt = 0;
        foreach ($table->data as $value) {
            if ($stt == 0) {
                $stt++;
                continue;
            }
            $value->cells[0] = $stt;
            $stt++;
        }
    }
    if ($fromform->filter == 2) {

        $firstDayThisMonth = date("m/d/Y", strtotime("first day of this month", $enddate));
        $firstDayThisMonth = strtotime($firstDayThisMonth);

        $lastDayThisMonth = date("m/d/Y", strtotime("last day of this month", $enddate));
        $lastDayThisMonth = strtotime($lastDayThisMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $firstDayThisMonth1 = date("m/d/Y", strtotime("first day of this month - 1 month", $enddate));
        $firstDayThisMonth1 = strtotime($firstDayThisMonth1);

        $lastDayThisMonth1 = date("m/d/Y", strtotime("last day of this month - 1 month", $enddate));
        $lastDayThisMonth1 = strtotime($lastDayThisMonth1) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $firstDayThisMonth2 = date("m/d/Y", strtotime("first day of this month - 2 month", $enddate));
        $firstDayThisMonth2 = strtotime($firstDayThisMonth2);

        $lastDayThisMonth2 = date("m/d/Y", strtotime("last day of this month - 2 month", $enddate));
        $lastDayThisMonth2 = strtotime($lastDayThisMonth2) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $lastDayThisMonth3 = date("m/d/Y", strtotime("last day of this month - 3 month", $enddate));
        $lastDayThisMonth3 = strtotime($lastDayThisMonth3) + strtotime("+23 hours 59 minutes 59 seconds", 0);

        $demtkmonth1 = demtk1($firstDayThisMonth, $lastDayThisMonth);
        $sotk1 = count($demtkmonth1);

        $keyarr = 0;
        $demtkmonth2 = [];
        $demtkmonth3 = [];
        $demtkmonth = [];
        if ($firstDayThisMonth > $startdate) {
            $demtkmonth2 = demtk1($firstDayThisMonth1, $lastDayThisMonth1);
            $sotk2 = count($demtkmonth2);
        }
        if ($firstDayThisMonth1 > $startdate) {
            $demtkmonth3 = demtk1($firstDayThisMonth2, $lastDayThisMonth2);
            $sotk3 = count($demtkmonth3);
        }
        if ($firstDayThisMonth2 > $startdate) {
            $demtkmonth = demtk1($startdate, $lastDayThisMonth3);
            $sotk = count($demtkmonth);
        }
        $in = true;
        if ($demtkmonth1 != null) {
            foreach ($demtkmonth1 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($firstDayThisMonth) . '-' . ngay($lastDayThisMonth));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk1);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk1;
                $cell->attributes['data-search'] = $sotk1;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($firstDayThisMonth) . '-' . ngay($lastDayThisMonth));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk1);
            $cell->attributes['data-order'] = $sotk1;
            $cell->attributes['data-search'] = $sotk1;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkmonth2 != null) {
            foreach ($demtkmonth2 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($firstDayThisMonth1) . '-' . ngay($lastDayThisMonth1));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk2);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk2;
                $cell->attributes['data-search'] = $sotk2;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;

                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($firstDayThisMonth <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($firstDayThisMonth1) . '-' . ngay($lastDayThisMonth1));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk2);
            $cell->attributes['data-order'] = $sotk2;
            $cell->attributes['data-search'] = $sotk2;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkmonth3 != null) {
            foreach ($demtkmonth3 as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($firstDayThisMonth2) . '-' . ngay($lastDayThisMonth2));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk3);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk3;
                $cell->attributes['data-search'] = $sotk3;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($firstDayThisMonth1 <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($firstDayThisMonth2) . '-' . ngay($lastDayThisMonth2));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk3);
            $cell->attributes['data-order'] = $sotk3;
            $cell->attributes['data-search'] = $sotk3;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }
        $in = true;
        if ($demtkmonth != null) {
            foreach ($demtkmonth as $key => $course) {
                $row = new html_table_row();
                $cell = new html_table_cell(ngay($course->timestart));
                $cell->attributes['data-order'] = $course->timestart;
                $cell->attributes['data-search'] = ngay($course->timestart);
                $row->cells[] = $cell;
                if ($in == false) {
                    $cell = new html_table_cell();
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell();
                } else {
                    $cell = new html_table_cell(ngay($startdate) . '-' . ngay($lastDayThisMonth3));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($course->shortname);
                    $row->cells[] = $cell;
                    $cell = new html_table_cell(html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $course->courseid
                        , $course->fullname));
                    $row->cells[] = $cell;
                    $cell = new html_table_cell($sotk);
                    $in = false;
                }
                $cell->attributes['data-order'] = $sotk;
                $cell->attributes['data-search'] = $sotk;
                $row->cells[] = $cell;
                $status = get_string('active');
                if ($course->status == 1) {
                    $status = get_string('suspended');
                } else if ($course->timeend < time() and $course->timeend != 0) {
                    $status = get_string('expired', 'block_th_report_total_enrolments');
                }
                $cell = new html_table_cell($status);
                $row->cells[] = $cell;
                $rightrows[$course->userid . '_' . $course->courseid] = $row;
            }
        } elseif ($firstDayThisMonth2 <= $startdate) {

        } else {
            $row = new html_table_row();
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellLeft; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell('N/A');
            $row->cells[] = $cell;
            for ($i = 0; $i < $cellRight; $i++) {
                $cell = new html_table_cell();
                $row->cells[] = $cell;
            }
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell(ngay($startdate) . '-' . ngay($lastDayThisMonth3));
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $cell = new html_table_cell($sotk);
            $cell->attributes['data-order'] = $sotk;
            $cell->attributes['data-search'] = $sotk;
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $row->cells[] = $cell;
            $rightrows['a_' . $keyarr] = $row;
            $keyarr++;
        }

        foreach ($rightrows as $key => $row) {
            $hh = explode("_", $key);
            // print_object($hh);
            if (is_numeric($hh[0]) == 1) {
                $row->cells = array_merge($leftrows[$hh[0]]->cells, $row->cells);
            } else {
                $row->cells = array_merge(array(), $row->cells);
            }
            $table->data[] = $row;
        }
        $stt = 0;
        foreach ($table->data as $value) {
            if ($stt == 0) {
                $stt++;
                continue;
            }
            $value->cells[0] = $stt;
            $stt++;
        }
    }
    $headrows = array_shift($table->data);
    $table->head = $headrows->cells;
    $table->attributes = array('class' => 'reportenrollments-table', 'border' => '1');
    $table->align[0] = 'center';
    $table->align[count($leftrows[0]->cells) + 4] = 'center';
}
echo $OUTPUT->header();
$edit_form->display();
if (isset($table)) {
    echo html_writer::table($table);
}
echo $OUTPUT->footer();