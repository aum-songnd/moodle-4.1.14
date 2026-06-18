<?php
require('../../config.php');

require_login();
$context=context_system::instance();
require_capability('report/th_courseopening:view', $context);

$url = new moodle_url('/report/th_courseopening/report.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);

$title = get_string('pluginname', 'report_th_courseopening');
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('#mydatatable', $title, $lang));
$PAGE->requires->css(new moodle_url('/report/th_courseopening/styles.css'));

$PAGE->navbar->add(get_string('report'));
$PAGE->navbar->add(get_string('name', 'report_th_courseopening'));
$PAGE->navbar->add(get_string('viewreport', 'report_th_courseopening'));
echo $OUTPUT->header();

$sql = "SELECT c.startdate,
               COUNT(DISTINCT c.id) AS total_courses,
               COUNT(DISTINCT CASE WHEN c.visible = 1 THEN c.id END) AS visible_courses,
               COUNT(DISTINCT CASE WHEN c.visible = 0 THEN c.id END) AS hidden_courses,
               COUNT(DISTINCT CASE WHEN r.shortname = 'student' THEN ue.id END) AS total_enrolments
        FROM {course} c
        LEFT JOIN {enrol} e ON e.courseid = c.id
        LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id
        LEFT JOIN {context} ctx 
               ON ctx.contextlevel = 50 
              AND ctx.instanceid = c.id
        LEFT JOIN {role_assignments} ra 
               ON ra.userid = ue.userid 
              AND ra.contextid = ctx.id
        LEFT JOIN {role} r 
               ON r.id = ra.roleid
        WHERE c.startdate > 0
        GROUP BY c.startdate
        ORDER BY c.startdate DESC";
$records = $DB->get_records_sql($sql);

echo html_writer::tag('h3', get_string('viewreport', 'report_th_courseopening'));
$table = new html_table();
$table->id = 'mydatatable';

$table->head = [
     get_string('stt','report_th_courseopening'),
    get_string('opening_session','report_th_courseopening'),
    get_string('total_subject','report_th_courseopening'),
    get_string('subjects_displayed','report_th_courseopening'),
    get_string('hidden_subjects','report_th_courseopening'),
    get_string('total_enrollment','report_th_courseopening'),
  
];
$stt = 1;

$format_date = get_config('local_thlib','format_date');
foreach ($records as $r ) {
    // Link sang detail.php với tham số startdate
    $detailurl = new moodle_url('/report/th_courseopening/detail.php', [
        'startdate' => $r->startdate
    ]);
    $link = html_writer::link($detailurl, date($format_date, $r->startdate));

    $row = new html_table_row();
    $row->cells[] = new html_table_cell($stt++);
    $row->cells[] = new html_table_cell($link);
    $row->cells[] = new html_table_cell($r->total_courses);
    $row->cells[] = new html_table_cell($r->visible_courses);
    $row->cells[] = new html_table_cell($r->hidden_courses);
    $row->cells[] = new html_table_cell($r->total_enrolments);

    $table->data[] = $row;
}
echo html_writer::start_div('report_th_courseopening');
echo html_writer::table($table);
echo html_writer::end_div();

echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
echo $OUTPUT->footer();
?>
<script>
$(document).ready(function() {
    $("#mydatatable").DataTable({
        dom: "lBfrtip",
        buttons: [{
                extend: "csvHtml5",
                text: "Xuất CSV",
                title: "baocao_enrol_users"
            },
            {
                extend: "excelHtml5",
                text: "Xuất Excel",
                title: "baocao_enrol_users"
            },

        ],
        stateSave: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
        },

    });
});
</script>