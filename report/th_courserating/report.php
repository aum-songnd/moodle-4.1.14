<?php

require('../../config.php');

require_login();


$url = new moodle_url('/report/th_courserating/report.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');

$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report',));
$PAGE->navbar->add(get_string('name','report_th_courserating'));
$PAGE->navbar->add(get_string('viewrating', 'report_th_courserating'));

$title = get_string('pluginname', 'report_th_courserating');
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('#mydatatable', $title, $lang));
$PAGE->requires->css(new moodle_url('/report/th_courseopening/styles.css'));


$mform = new \report_th_courserating\form\report_th_courserating();
if ($mform->is_cancelled()) {
  
} else if ($fromform = $mform->get_data()) {

    // if (!empty($fromform->intro)) {
        // $mform->display();
        if (!empty($fromform->courseids) && is_array($fromform->courseids)) {
            
            $table = new html_table();
            $table->id = 'mydatatable';
            $table->head = [
                get_string('stt','report_th_courserating'),
                get_string('course','report_th_courserating'),
                get_string('total_ratings','report_th_courserating'),
                get_string('average_ratings','report_th_courserating'),
                get_string('rating_counts_1','report_th_courserating'),
                get_string('rating_counts_2','report_th_courserating'),
                get_string('rating_counts_3','report_th_courserating'),
                get_string('rating_counts_4','report_th_courserating'),
                get_string('rating_counts_5','report_th_courserating'),
            ];
             $courseids=$fromform->courseids;
                if (in_array('all', $courseids)) {
                    
                   $courseids = $DB->get_fieldset_sql("SELECT id FROM {course} WHERE id > 1");
                }
            $stt = 1;  
             $sql = "select min(tec.id) , c.fullname as fullname, count(tec.rating) as total , ROUND(avg(tec.rating), 2) as avg ,
                                SUM(CASE WHEN tec.rating = 1 THEN 1 ELSE 0 END) AS rate1,
                                SUM(CASE WHEN tec.rating = 2 THEN 1 ELSE 0 END) AS rate2,
                                SUM(CASE WHEN tec.rating = 3 THEN 1 ELSE 0 END) AS rate3,
                                SUM(CASE WHEN tec.rating = 4 THEN 1 ELSE 0 END) AS rate4,
                                SUM(CASE WHEN tec.rating = 5 THEN 1 ELSE 0 END) AS rate5
                        from {theme_edumy_courserate} tec 
                        join {course} c on c.id = tec.course
                        where c.id = :courseid
                        group by c.id";  
            foreach ($courseids as $courseid) {
               
               

                $records = $DB->get_records_sql($sql,['courseid'=>$courseid]); 
          

            foreach ($records as $r ) {

                $row = new html_table_row();
                $row->cells[] = new html_table_cell($stt++);
                $row->cells[] = new html_table_cell($r->fullname);
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
        
        
    }
echo $OUTPUT->header();
echo html_writer::tag('h3', get_string('viewrating', 'report_th_courserating'));
// } else {

    $mform->display();
// }
if (isset($table)) {
    echo html_writer::start_div('report_th_courseopening');
        echo html_writer::table($table);
        echo html_writer::end_div();
        // echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
}
echo $OUTPUT->footer();