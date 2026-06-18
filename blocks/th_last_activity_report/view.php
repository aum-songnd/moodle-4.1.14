<?php
require '../../config.php';
require_once $CFG->dirroot . '/blocks/th_last_activity_report/th_last_activity_report_form.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

global $DB, $CFG, $COURSE;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'blocks_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_last_activity_report:view', context_course::instance($COURSE->id));

$baseurl = new moodle_url('/blocks/th_last_activity_report/view.php');
$pluginname = get_string('pluginname', 'block_th_last_activity_report');

$PAGE->set_url($baseurl);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);
$PAGE->navbar->add($pluginname, $baseurl);
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Báo cáo hoạt động lần cuối của người dùng", $lang));

$mform = new th_last_activity_report_form();

if ($data = $mform->get_data()) {

    $sql = "SELECT a.id, MAX(lsl.timecreated) as lastactivity
    FROM (SELECT id
            FROM {user}
            WHERE deleted = 0 AND (id <> 1 OR firstaccess = 0)
            EXCEPT
            SELECT DISTINCT u.id
            FROM {user} u
            JOIN {logstore_standard_log} lsl ON lsl.userid = u.id
            WHERE lsl.timecreated >= $data->startdate AND u.deleted = 0 AND lsl.realuserid IS NULL AND lsl.action <> 'loggedout') a
            LEFT JOIN {logstore_standard_log} lsl ON lsl.userid = a.id AND lsl.realuserid IS NULL AND lsl.action <> 'loggedout'
        GROUP BY a.id
        HAVING a.id <> 1";
    $logs = $DB->get_records_sql($sql);

    // print_object($logs);
    // exit;

    if (!empty($logs)) {
        $userid_arr = [];
        foreach ($logs as $user) {
            $userid_arr[] = $user->id;
        }
    } else {

    }

    $extra = array_filter(explode(',', $CFG->showuseridentity));
    $userfields = array_values($extra);

    $usernamefield = get_all_user_name_fields();
    $usernamefield = implode(",", $usernamefield);
    $alluserfields = "id," . $usernamefield;

    if (count($userfields) > 0) {
        $alluserfields .= "," . implode(',', $userfields);
    }

    $alluserfields .= "," . "email";
    $user_arr = $DB->get_records('user', array('deleted' => 0), "", $alluserfields);

    $table = new html_table();
    $table->attributes = array('class' => 'table', 'border' => '1');

    $headrows = new html_table_row();
    $cell = new html_table_cell(get_string('lastactivity', 'block_th_last_activity_report'));
    $cell->attributes['class'] = 'cell headingcell';
    $cell->header = true;
    $headrows->cells[] = $cell;

    $rightrows = [];
    $rightrows[] = $headrows;
    list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);

    foreach ($logs as $user) {
        if (is_siteadmin($user->id)) {
            continue;
        }
        $row = new html_table_row();
        if (isset($user->lastactivity)) {
            $cell = new html_table_cell(format_time(time() - $user->lastactivity));
        } else {
            $cell = new html_table_cell(get_string('never'));
        }
        $row->cells[] = $cell;
        $rightrows[$user->id] = $row;
    }
    $stt = 0;
    foreach ($rightrows as $key => $row) {
        $row->cells = array_merge($leftrows[$key]->cells, $row->cells);
        if ($stt != 0) {
            $c = new html_table_cell($stt);
            $row->cells[0] = $c;
        }
        $stt++;
        $table->data[] = $row;
    }
    $headrows = array_shift($table->data);
    $table->head = $headrows->cells;
    $table->align[0] = 'center';

} else if ($mform->is_cancelled()) {

    redirect($baseurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_th_last_activity_report'));
echo $mform->display();

if (isset($logs)) {
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
