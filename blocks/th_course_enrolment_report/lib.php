<?php
    function th_course_enrolment_report_table($object_table, $courseid, $course_name, $shortname, $userid, $Completion_Status, 
    $user_role, $timecreated, $time_start, $time_end) {

        $link = new moodle_url('/user/index.php', ['id' => $courseid]);
        $link_course = html_writer::link($link, $course_name);

        $row = new html_table_row();
        $cell = new html_table_cell($user_role);
        $row->cells[] = $cell;
        $cell = new html_table_cell($link_course);
        $row->cells[] = $cell;
        $cell = new html_table_cell($shortname);
        $row->cells[] = $cell;
        if ($timecreated == null) {
            if ($time_start != 0) {
                $cell = new html_table_cell(date('d/m/Y', $time_start));
            } else {
                $cell = new html_table_cell('N/A');
            }
        } else {
            $cell = new html_table_cell(date('d/m/Y', $timecreated));
        }
        $row->cells[] = $cell;

        if ($time_start != 0) {
            $cell = new html_table_cell(date('d/m/Y', $time_start));
        } else {
             $cell = new html_table_cell('N/A');
        }
        $row->cells[] = $cell;

        if ($time_end == 0) {
            $cell = new html_table_cell(get_string('unlimited', 'block_th_course_enrolment_report'));
        } elseif ($time_end > 0) {
            $cell = new html_table_cell(date('d/m/Y', $time_end));
        } else {
            $cell = new html_table_cell('N/A');
        }
            $row->cells[] = $cell;

        if (time() <= $time_end or $time_end == 0) {
            $Enrolment_Status = get_string('active');
        } elseif (time() > $time_end and $time_end != -1) {
            $Enrolment_Status = get_string('expired', 'block_th_course_enrolment_report');
        } else {
            $Enrolment_Status = get_string('not_activated', 'block_th_course_enrolment_report');
        }

        $cell = new html_table_cell($Enrolment_Status);
        $row->cells[] = $cell;

        if (!empty($userenroled)) {
            $cell = new html_table_cell("<b style='color: #fff !important'>$Completion_Status</b>");
        } else {
            $cell = new html_table_cell("<b style='color: #fff !important'>$Completion_Status</b>");
        }
        $row->cells[] = $cell;

        if ($Completion_Status == 'Chưa hoàn thành' || $Completion_Status == 'Unfinished') {
            $cell->attributes = array('class' => "bg-danger");
        } else {
            $cell->attributes = array('class' => "bg-success");
        }

        $object_table->rightrows[$userid . "_" . $courseid] = $row;
        
        return $object_table;
    }
?>