<?php

require('../../config.php');

global $DB, $OUTPUT, $PAGE, $COURSE;

$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_plugin01', $courseid);
}

require_login($courseid);

$PAGE->set_url('/blocks/plugin01/view2.php');
$PAGE->set_pagelayout('standard');

$strviewfeed = get_string('pluginname', 'block_plugin01');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

echo $OUTPUT->header();

$rec = $DB->get_records_sql(
    'SELECT id, email, lastname, firstname , username
        FROM {user}
        WHERE deleted = 0 AND id <> :excludedid
        ORDER BY id ASC',
    ['excludedid' => 1],
);

$table = new html_table();

$headerrow = new html_table_row();

$cell = new html_table_cell('ID');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Full name');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Email');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$cell = new html_table_cell('Username');
$cell->header = true;
$cell->attributes['class'] = 'cell headingcell';
$headerrow->cells[] = $cell;

$table->data[] = $headerrow;


foreach ($rec as $item) {
    $row = new html_table_row();

    $row->cells[] = new html_table_cell(s($item->id));
    $row->cells[] = new html_table_cell(s($item->firstname . ' ' . $item->lastname));
    $row->cells[] = new html_table_cell(s($item->email));
    $row->cells[] = new html_table_cell(s($item->username));

    $table->data[] = $row;
}

echo html_writer::table($table);
echo $OUTPUT->footer();


