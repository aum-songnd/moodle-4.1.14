<?php
require('../../config.php');

require_login();
$context = context_system::instance();
require_capability('report/th_courseopening:view', $context);

$startdate = required_param('startdate', PARAM_INT);

$url = new moodle_url('/report/th_courseopening/detail.php', ['startdate' => $startdate]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);

$title = get_string('pluginname', 'report_th_courseopening');
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('#detailtable', $title, $lang));

$PAGE->navbar->add(get_string('report'));
$PAGE->navbar->add(get_string('name', 'report_th_courseopening'));
$PAGE->navbar->add(get_string('viewdetail', 'report_th_courseopening'));
echo $OUTPUT->header();

$format_date = get_config('local_thlib','format_date');

$sql_detail = "SELECT c.id,
                      c.shortname,
                      c.fullname,
                      c.visible,
                      cc.name AS category,
                      COUNT(DISTINCT CASE WHEN r.shortname = 'student' THEN ue.id END) AS enrolments
               FROM {course} c
               JOIN {course_categories} cc ON cc.id = c.category
               LEFT JOIN {enrol} e ON e.courseid = c.id
               LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id
               LEFT JOIN {role_assignments} ra 
                      ON ra.userid = ue.userid
                     AND ra.contextid = (
                          SELECT ctx.id 
                          FROM {context} ctx 
                          WHERE ctx.contextlevel = 50 
                            AND ctx.instanceid = c.id
                      )
               LEFT JOIN {role} r ON r.id = ra.roleid
               WHERE c.startdate = :startdate
               GROUP BY c.id, c.shortname, c.fullname, c.visible, cc.name
               ORDER BY c.shortname ASC";
$courses = $DB->get_records_sql($sql_detail, ['startdate' => $startdate]);

echo html_writer::tag('h3', get_string('viewdetail', 'report_th_courseopening') . date($format_date, $startdate));
    if ($courses) {
        $table = new html_table();
        $table->id = 'detailtable';
        $table->head = [
            get_string('stt','report_th_courseopening'),
            get_string('course_name','report_th_courseopening'),
            get_string('shortname_course','report_th_courseopening'),
            get_string('category','report_th_courseopening'),
            get_string('opening_day','report_th_courseopening'),
            get_string('status','report_th_courseopening'),
            get_string('total_students','report_th_courseopening'),

    ];
    $i = 1;
    foreach ($courses as $c) {
        $status = $c->visible ? 'Hiện' : 'Ẩn';

        // Link đến trang khóa học
        $courselink_fullname = html_writer::link(
            new moodle_url('/course/view.php', ['id' => $c->id]),
            format_string($c->fullname)
        );

        $row = new html_table_row([
            $i++,
            $courselink_fullname,
            $c->shortname,
            $c->category,
            date($format_date, $startdate),
            $status,
            $c->enrolments
        ]);
        $table->data[] = $row;
    }

    echo html_writer::start_div('report_th_courseopening');
    echo html_writer::table($table);
    echo html_writer::end_div();
}

// Nút quay lại
echo html_writer::link(
    new moodle_url('/report/th_courseopening/report.php'),
    get_string('back','report_th_courseopening'),
    ['class' => 'btn btn-secondary mt-3']
);

echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';

echo $OUTPUT->footer();
?>
<script>
$(document).ready(function() {
    $("#detailtable").DataTable({
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