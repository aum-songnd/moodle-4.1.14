<?php

require_once(__DIR__ . '/../../config.php');


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
$PAGE->set_url('/blocks/rss_client/viewfeed.php', $urlparams);
$PAGE->set_pagelayout('standard');


$strviewfeed = get_string('viewfeed', 'block_rss_client');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

$managefeeds = new moodle_url('/blocks/rss_client/managefeeds.php', $urlparams);
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_rss_client'));
$PAGE->navbar->add(get_string('managefeeds', 'block_rss_client'), $managefeeds);
$PAGE->navbar->add($strviewfeed);
echo $OUTPUT->header();



$rec = $DB->get_records_sql(
    'SELECT id , fullname , shortname, startdate, enddate
        FROM {course}
        WHERE id <> :excludedid
        ORDER BY id ASC',
    ['excludedid' => 1]
  
);
foreach ($rec as $course) {
    echo "ID: " . $course->id . "<br>";
    echo "Tên đầy đủ: " . $course->fullname . "<br>";
    echo "Tên ngắn: " . $course->shortname . "<br>";
    echo "Ngày bắt đầu: " . date("d/m/Y H:i:s", $course->startdate) . "<br>";
    echo "Ngày kết thúc: " . date("d/m/Y H:i:s", $course->enddate) . "<br><hr>";
}

print_object($rec);

$table = new html_table();

$headerrow = new html_table_row();

$cell = new html_table_cell('ID');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Tên đầy đủ');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Tên ngắn');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Ngày bắt đầu');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Ngày kết thúc');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;
$table->data[] = $headerrow;


foreach ($rec as $item) {
    $row = new html_table_row();

    $row->cells[] = new html_table_cell(s($item->id));
    $row->cells[] = new html_table_cell(s($item->fullname));
    $row->cells[] = new html_table_cell(s($item->shortname));
    $row->cells[] = new html_table_cell(date('d/m/Y H:i:s', $item->startdate));
    $row->cells[] = new html_table_cell(date('d/m/Y H:i:s', $item->enddate));
    
    $table->data[] = $row;
}

echo html_writer::table($table);
echo $OUTPUT->footer();

