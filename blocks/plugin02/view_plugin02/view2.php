<?php

require_once(__DIR__ . '/../../../config.php');
// require_once($CFG->libdir.'/adminlib.php');
require_once('../classes/form/myform.php');

global $DB,$OUTPUT, $CFG ,$PAGE;
require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed');
}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INT);
if ($courseid = SITEID) {
    $courseid = 0;
}
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$urlparams = array('rssid' => 1);
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}

$title = get_string('pluginname', 'block_plugin02');
$pagetitle = $title;
$url = new moodle_url("/blocks/plugin02/view_plugin02/view2.php");


$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
$lang = current_language(); 

$user = $DB->get_records_sql(
    "SELECT id,username,firstname  
    from {user} 
    Where username Not Like '%@%'
    ORDER BY id ASC"
    
);

$table = new html_table();

$table->head = ['STT', 'Userid', 'Username','Firstname' ]; 
$table->attributes['class'] = 'generaltable';
$stt = 1;
foreach ($user as $item) {
    $table->data[] = [
        $stt++,
        s($item->id),
        s($item->username),
        s($item->firstname)
    ];
}
echo html_writer::table($table);


echo '<script>
$(document).ready(function() {
$(".generaltable").DataTable({
    dom: "lBfrtip",
    buttons: [],
    stateSave: true,
    language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
    },
    
});
});
</script>';

echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.generaltable', '', $lang));
echo $OUTPUT->footer();