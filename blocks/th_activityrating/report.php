<?php

require('../../config.php');
require($CFG->dirroot . '/course/lib.php');

require_login();
$context = context_system::instance();
require_capability('block/th_activityrating:report', $context);

$url = new moodle_url('/blocks/th_activityrating/report.php');
$title = get_string('name', 'block_th_activityrating');
$lang = current_language();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_heading($title);
$PAGE->set_title($title);
$PAGE->navbar->add(get_string('report'));
$PAGE->navbar->add($title);
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('#th_activityrating', $title, $lang));


$mform = new \block_th_activityrating\form\report_th_activityrating_form();
if ($mform->is_cancelled()) {
  
} else if ($fromform = $mform->get_data()) {

    if (!empty($fromform->courseids) && is_array($fromform->courseids)) {

        $courseids = $fromform->courseids;
        if (in_array('all', $courseids)) {
           $courseids = $DB->get_fieldset_sql("SELECT id FROM {course} WHERE id > 1 and visible = 1");
        }
        $course_str = implode(', ', $courseids);

        $choices = get_module_types_names();
        $choices = array_keys($choices);
        $module_str = "'" . implode("', '", $choices). "'";

        $table = new html_table();
        $table->id = 'th_activityrating';
        $table->head = [
            get_string('stt', 'block_th_activityrating'),
            get_string('course', 'block_th_activityrating'),
            get_string('module', 'block_th_activityrating'),
            get_string('total_ratings', 'block_th_activityrating'),
            get_string('average_ratings', 'block_th_activityrating'),
            get_string('rating_counts_1', 'block_th_activityrating'),
            get_string('rating_counts_2', 'block_th_activityrating'),
            get_string('rating_counts_3', 'block_th_activityrating'),
            get_string('rating_counts_4', 'block_th_activityrating'),
            get_string('rating_counts_5', 'block_th_activityrating'),
        ];
        $table->align = ['center', 'left', 'left', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        $stt = 1;
        $sql = "SELECT ROW_NUMBER() OVER () AS stt, c.id, m.name,
                 c.fullname, COUNT(tec.rating) AS total, ROUND(AVG(tec.rating), 2) AS avg,
                    SUM(CASE WHEN tec.rating = 1 THEN 1 ELSE 0 END) AS rate1,
                    SUM(CASE WHEN tec.rating = 2 THEN 1 ELSE 0 END) AS rate2,
                    SUM(CASE WHEN tec.rating = 3 THEN 1 ELSE 0 END) AS rate3,
                    SUM(CASE WHEN tec.rating = 4 THEN 1 ELSE 0 END) AS rate4,
                    SUM(CASE WHEN tec.rating = 5 THEN 1 ELSE 0 END) AS rate5
                FROM {block_th_activityrating} tec 
                JOIN {course} c ON c.id = tec.course
                JOIN {course_modules} cm ON cm.id = tec.cmid AND cm.course = tec.course 
                    AND cm.deletioninprogress = 0 AND cm.visible = 1
                JOIN {modules} m ON m.id = cm.module
                WHERE c.id IN ($course_str) AND m.name IN ($module_str)
                GROUP BY c.id, m.name";

        $records = $DB->get_records_sql($sql);
  
        foreach ($records as $r) {

            $row = new html_table_row();
            $row->cells[] = new html_table_cell($stt++);
            $row->cells[] = new html_table_cell($r->fullname);
            $row->cells[] = new html_table_cell(ucfirst($r->name));
            $row->cells[] = new html_table_cell($r->total);
            $row->cells[] = new html_table_cell($r->avg);
            $row->cells[] = new html_table_cell($r->rate1);
            $row->cells[] = new html_table_cell($r->rate2);
            $row->cells[] = new html_table_cell($r->rate3);
            $row->cells[] = new html_table_cell($r->rate4);
            $row->cells[] = new html_table_cell($r->rate5);
        
            $table->data[] = $row;
        }   
    }        
}
echo $OUTPUT->header($title);
echo html_writer::tag('h3', $title);
$mform->display();
if (isset($table)) {
    echo html_writer::start_div('block_th_activityrating');
    echo html_writer::table($table);
    echo html_writer::end_div();
}
echo $OUTPUT->footer();