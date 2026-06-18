<?php

require '../../config.php';
require_once $CFG->dirroot . '/blocks/th_roadmap_report/th_roadmap_report_form.php';
require_once $CFG->dirroot . '/blocks/th_roadmap_report/lib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse');
}
require_login($COURSE->id);
require_capability('block/th_roadmap_report:view', context_course::instance($COURSE->id));
$baseurl = new moodle_url('/blocks/th_roadmap_report/view.php');
$pluginname = get_string('pluginname', 'block_th_roadmap_report');

$context = context_system::instance();
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);
$PAGE->navbar->add($pluginname, $baseurl);
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array(".th_roadmap_report", 'Báo cáo Lộ trình', $lang));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_th_roadmap_report'));
$mform = new th_roadmap_report_form();
echo $mform->display();

if ($data = $mform->get_data()) {

    $config = trim(get_config('block_th_roadmap_report', 'idnumber'));

    if ($ccid = $DB->get_field('course_categories', 'id', array('idnumber' => $config))) {
        $option = $data->show_option;
        $summary_detail = $data->summary_detail;

        if ($option == 0) {
            $allcourse = $data->courseidarr;
            if (empty($allcourse)) {
                $sql = "SELECT c.id
                FROM {course} c
                JOIN {course_categories} cc ON cc.id = c.category
                where cc.idnumber = :idnumber";
                $courses = $DB->get_records_sql($sql, array('idnumber' =>  $config));

                $allcourse = array();
                foreach ($courses as $key => $course) {
                    $allcourse[] = $course->id;
                }
            }
            $selected_courses = [];
            foreach ($allcourse as $key => $value) {
                $selected_courses[$value] = $value; 
            }
        } else if ($option == 1) {

            $alluser = $data->userid;

            if (empty($alluser)) {
               
                $cat = core_course_category::get($ccid);
                $allcourse = $cat->get_courses(['idonly']);
                $selected_courses = [];
                foreach ($allcourse as $courseid => $value) {
                    $selected_courses[$courseid] = $courseid; 
                }
                $alluser = [];
                foreach ($allcourse as $courseid => $value) {
                    $context = context_course::instance($courseid);
                    $courseusers = get_enrolled_users($context, '', 0, 'u.id');
                    $alluser = array_merge($alluser, array_keys($courseusers));
                }
            } else {
                $cat = core_course_category::get($ccid);
                $allcourse = $cat->get_courses(['idonly']);
                $selected_courses = [];
                foreach ($allcourse as $courseid => $value) {
                    $selected_courses[$courseid] = $courseid; 
                }
            }
        } else if ($option == 2) {

            $startdate = $data->startdate;
            $enddate = $data->enddate;

            $sql = "SELECT c.id,fullname,shortname 
                FROM {course} c
                JOIN {course_categories} cc ON cc.id = c.category
                where cc.idnumber = :idnumber AND c.startdate >= :startdate AND c.startdate <= :enddate";
            $params = array(
                'startdate' => $startdate, 
                'enddate' => $enddate,
                'idnumber' => $config
            );
            $allcourse = $DB->get_records_sql($sql, $params);

            $selected_courses = [];
            foreach ($allcourse as $key => $value) {
                $selected_courses[$key] = $key; 
            }
        }
        $table = new html_table();
        $a = true;
        $tt = 0;
        if ($summary_detail == 0) {

            foreach ($selected_courses as $k => $courseid) {
                $context = context_course::instance($courseid);
                $USER->gradeediting[$courseid] = 0;
                $course = $DB->get_record('course', array('id' => $courseid));
                grade_regrade_final_grades_if_required($course);
                // return tracking object
                $gpr = new grade_plugin_return(
                    array(
                        'type' => 'report',
                        'plugin' => 'grader',
                        'course' => $course,
                    )
                );
                $report = new th_roadmap_summary_grade_report_grader($courseid, $gpr, $context, null, null, null);
                $userid_arr = $report->load_users();
                if ($option == 0) {
                    foreach ($userid_arr as $key => $value) {
                        $userid_arr[$key] = $key;
                    }
                } else if ($option == 1) {
                    $userid_arr = $alluser;
                } else if ($option == 2) {
                    foreach ($userid_arr as $key => $value) {
                        $userid_arr[$key] = $key;
                    }
                }
                // print_object($userid_arr);
                $report->load_final_grades();
                $displayaverages = true;
                $leftrows = $report->get_left_rows($displayaverages);
                $rightrows = $report->get_right_rows($displayaverages);
                $user_arr = $DB->get_records('user', array('deleted' => 0));

                $leftrows = get_left_rows($userid_arr, $user_arr);
                if (sizeof($rightrows) > 0) {
                    $lastrow = end($rightrows);
                    $classattr = $lastrow->attributes['class'];
                    if (strpos($classattr, 'avg') !== false) {
                        $studentsize = sizeof($rightrows) - 2;
                    } else {
                        $studentsize = sizeof($rightrows) - 1;
                    }
                }
                $rightrows_grades = array_slice($rightrows, 1, $studentsize);
                // if ($summary_detail == 0) {
                    // foreach ($rightrows_grades as $key => $rightrows_grade) {
                    //     $rightrows_grades[$key]->cells = array_slice($rightrows_grade->cells, -1);
                    // }
                // }
                $rightrows_html = [];
                $rightrows_header = array_shift($rightrows_grades);
                if ($a) {

                    $cell = new html_table_cell(get_string('shortname', 'block_th_roadmap_report'));
                    $cell->attributes['class'] = 'cell headingcell header';
                    array_unshift($rightrows_header->cells, $cell);
                    $cell = new html_table_cell(get_string('fullname', 'block_th_roadmap_report'));
                    $cell->attributes['class'] = 'cell headingcell header';
                    array_unshift($rightrows_header->cells, $cell);

                    foreach ($rightrows_header->cells as $key => $cell) {
                        $cell->attributes['class'] = 'cell headingcell header';
                        $cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
                    }
                    $cell = new html_table_cell(get_string('roadmap', 'block_th_roadmap_report'));
                    $cell->attributes['class'] = 'cell headingcell header';
                    $rightrows_header->cells[] = $cell;
                    $rightrows_html[0] = $rightrows_header;
                    $a = false;
                }
                
                foreach ($rightrows_grades as $key => $html_table_row) {
                    if (!isset($html_table_row->id)) {
                        continue;
                    }
                    $userid = explode("_", $html_table_row->id)[1];
                    // $cell = new html_table_cell($course->shortname);
                    // $cell->attributes['class'] = 'cell';
                    // array_unshift($html_table_row->cells, $cell);
                    // $url = new moodle_url('/course/view.php', array('id'=> $courseid));
                    // $fullname = html_writer::link($url, $course->fullname);
                    // $cell = new html_table_cell($fullname);
                    // $cell->attributes['class'] = 'cell';
                    // array_unshift($html_table_row->cells, $cell);
                    array_walk($html_table_row->cells, function (&$cell, &$key) {
                        $cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
                    });
                    $rightrows_html[$userid] = $html_table_row;
                }
                $left_rows_html = $leftrows[0];
                $count = 0;
                // print_object($rightrows_html);
                // print_object($left_rows_html);
                
                foreach ($rightrows_html as $key => $row) {
                    $count++;
                    if ($count > 1) {
                        if (isset($left_rows_html[$key])) {
                            $left_rows_html[$key]->cells[0]->text = $count - 1;
                        }
                    }
                    if (isset($left_rows_html[$key])) {
                        if ($tt != 0) {
                            $left_rows_html[$key]->cells[0]->text = $tt;
                        }
                        $row->cells = array_merge($left_rows_html[$key]->cells, $row->cells);
                        $table->data[] = $row;
                        $tt++;
                    }
                }
            }
        } else if ($summary_detail == 1) {

            foreach ($selected_courses as $k => $courseid) {
                $context = context_course::instance($courseid);
                $USER->gradeediting[$courseid] = 0;
                $course = $DB->get_record('course', array('id' => $courseid));
                grade_regrade_final_grades_if_required($course);
                // return tracking object
                $gpr = new grade_plugin_return(
                    array(
                        'type' => 'report',
                        'plugin' => 'grader',
                        'course' => $course,
                    )
                );
                $report = new th_roadmap_detail_grade_report_grader($courseid, $gpr, $context, null, null, null);
                $userid_arr = $report->load_users();
                if ($option == 0) {
                    foreach ($userid_arr as $key => $value) {
                        $userid_arr[$key] = $key;
                    }
                } else if ($option == 1) {
                    $userid_arr = $alluser;
                } else if ($option == 2) {
                    foreach ($userid_arr as $key => $value) {
                        $userid_arr[$key] = $key;
                    }
                }
                // print_object($userid_arr);
                $report->load_final_grades();
                $displayaverages = true;
                $leftrows = $report->get_left_rows($displayaverages);
                $rightrows = $report->get_right_rows($displayaverages);
                $user_arr = $DB->get_records('user', array('deleted' => 0));

                $leftrows = get_left_rows($userid_arr, $user_arr);
                if (sizeof($rightrows) > 0) {
                    $lastrow = end($rightrows);
                    $classattr = $lastrow->attributes['class'];
                    if (strpos($classattr, 'avg') !== false) {
                        $studentsize = sizeof($rightrows) - 2;
                    } else {
                        $studentsize = sizeof($rightrows) - 1;
                    }
                }
                $rightrows_grades = array_slice($rightrows, 1, $studentsize);
                // if ($summary_detail == 0) {
                    // foreach ($rightrows_grades as $key => $rightrows_grade) {
                    //     $rightrows_grades[$key]->cells = array_slice($rightrows_grade->cells, -1);
                    // }
                // }
                $rightrows_html = [];
                $rightrows_header = array_shift($rightrows_grades);
                if ($a) {

                    $cell = new html_table_cell(get_string('shortname', 'block_th_roadmap_report'));
                    $cell->attributes['class'] = 'cell headingcell header';
                    array_unshift($rightrows_header->cells, $cell);
                    $cell = new html_table_cell(get_string('fullname', 'block_th_roadmap_report'));
                    $cell->attributes['class'] = 'cell headingcell header';
                    array_unshift($rightrows_header->cells, $cell);

                    foreach ($rightrows_header->cells as $key => $cell) {
                        $cell->attributes['class'] = 'cell headingcell header';
                        $cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
                    }
                    $cell = new html_table_cell(get_string('roadmap', 'block_th_roadmap_report'));
                    $cell->attributes['class'] = 'cell headingcell header';
                    $rightrows_header->cells[] = $cell;
                    $rightrows_html[0] = $rightrows_header;
                    $a = false;
                }
                
                foreach ($rightrows_grades as $key => $html_table_row) {
                    if (!isset($html_table_row->id)) {
                        continue;
                    }
                    $userid = explode("_", $html_table_row->id)[1];
                    // $cell = new html_table_cell($course->shortname);
                    // $cell->attributes['class'] = 'cell';
                    // array_unshift($html_table_row->cells, $cell);
                    // $url = new moodle_url('/course/view.php', array('id'=> $courseid));
                    // $fullname = html_writer::link($url, $course->fullname);
                    // $cell = new html_table_cell($fullname);
                    // $cell->attributes['class'] = 'cell';
                    // array_unshift($html_table_row->cells, $cell);
                    array_walk($html_table_row->cells, function (&$cell, &$key) {
                        $cell->attributes['data-order'] = $cell->attributes['data-search'] = strip_tags($cell->text);
                    });
                    $rightrows_html[$userid] = $html_table_row;
                }
                $left_rows_html = $leftrows[0];
                $count = 0;
                // print_object($rightrows_html);
                // print_object($left_rows_html);
                
                foreach ($rightrows_html as $key => $row) {
                    $count++;
                    if ($count > 1) {
                        if (isset($left_rows_html[$key])) {
                            $left_rows_html[$key]->cells[0]->text = $count - 1;
                        }
                    }
                    if (isset($left_rows_html[$key])) {
                        if ($tt != 0) {
                            $left_rows_html[$key]->cells[0]->text = $tt;
                        }
                        $row->cells = array_merge($left_rows_html[$key]->cells, $row->cells);
                        $table->data[] = $row;
                        $tt++;
                    }
                }
            }
        
        }
        $headrows = array_shift($table->data);
        if (isset($headrows->cells)) {
            $table->head = $headrows->cells;
        }
        $table->attributes = array('class' => "th_roadmap_report");
        $html = html_writer::table($table);
        $html = $OUTPUT->container($html, 'gradeparent');
        echo $html;
    }
} else if ($mform->is_cancelled()) {

}
echo $OUTPUT->footer();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('input[type=radio][name=show_option]').change(function() {
            $('#fitem_id_courseidarr .col-form-label').removeAttr('hidden');
            $('#fitem_id_courseidarr .col-form-label .word-break').removeAttr('hidden');
            $('#fitem_id_userid .col-form-label').removeAttr('hidden');
            $('#fitem_id_userid .col-form-label .word-break').removeAttr('hidden');
        });
});
</script>