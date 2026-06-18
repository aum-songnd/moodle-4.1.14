<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once 'edit_form.php';
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse');
}

$context = context_system::instance();
require_login($courseid);
require_capability('mod/thpage:viewall', $context);

$pageurl = '/mod/thpage/viewall.php';
$title = get_string('pluginname', 'mod_thpage');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'mod_thpage'));
$PAGE->set_title(get_string('pluginname', 'mod_thpage'));

$editurl = new moodle_url($pageurl);
$settingsnode = $PAGE->navbar->add(get_string('pluginname', 'mod_thpage'), $editurl);
$settingsnode->make_active();

$list_data = $DB->get_records_sql("SELECT * FROM {th_teacher_infor} ORDER BY role");

$baseurl = new moodle_url('/mod/thpage/edit.php');

$table       = new html_table();
$table->head = array(
                    get_string('stt', 'mod_thpage'),
                    get_string('cohorts', 'mod_thpage'),
                    get_string('fullname'),
                    get_string('phone'),
                    get_string('email'),
                    get_string('role'), 
                    get_string('gender', 'mod_thpage'), 
                    get_string('action', 'mod_thpage')
                );
$stt         = 0;

foreach ($list_data as $k => $data) {
    $stt = $stt + 1;
    $urlparams = array('id' => $data->id, 'returnurl' => $baseurl->out_as_local_url(false));
    $link_edit = new moodle_url('/mod/thpage/edit.php', $urlparams,);
    $edit      = html_writer::link($link_edit, $OUTPUT->pix_icon('t/edit', get_string('edit')), array('title' => get_string('edit')));
    $link_delete = new moodle_url('/mod/thpage/edit.php', $urlparams + array('delete' => 1));
    $delete      = html_writer::link(
        $link_delete,
        $OUTPUT->pix_icon('t/delete', get_string('delete')),
        array('title' => get_string('delete'))
    );

    $row          = new html_table_row();
    $cell         = new html_table_cell($stt);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($data->cohorts);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($data->fullname);
    $row->cells[] = $cell;
    if (substr(0 != $data->phone, 0, 1)) {
        $data->phone = '0' . $data->phone;
    }
    $cell         = new html_table_cell($data->phone);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($data->email);
    $row->cells[] = $cell;
    if ($data->role == 1) {
        $cell         = new html_table_cell(get_string('role_gvcn', 'mod_thpage'));
        $row->cells[] = $cell;
    } else {
        $cell         = new html_table_cell(get_string('role_qlht', 'mod_thpage'));
        $row->cells[] = $cell;
    }

    if ($data->gender == 1) {
        $cell         = new html_table_cell(get_string('gender_male', 'mod_thpage'));
        $row->cells[] = $cell;
    } else {
        $cell         = new html_table_cell(get_string('gender_female', 'mod_thpage'));
        $row->cells[] = $cell;
    }

    $cell          = new html_table_cell($edit . $delete);
    $row->cells[]  = $cell;
    $table->data[] = $row;
}

$table->attributes          = array('class' => 'thpage', 'border' => '1');
$table->attributes['style'] = "width: 100%; text-align:center;";
$html = html_writer::table($table);
echo $OUTPUT->header();
$baseurl = new moodle_url('/mod/thpage/viewall.php');
if ($editcontrols = mod_thpage_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading(get_string('list_teacher', 'mod_thpage'));

echo "<style>
    table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        width: 100% !important;
        border: 1px solid #ddd !important;
    }

    td {
        word-break: break-all;
    }

    /* Style table headers and table data */
    th, td {
        text-align: left !important;
    }

    th:first-child, td:first-child {
        text-align: left !important;
    }

    tr {
        border-bottom: 1px solid #ddd;
    }

    thead {
        background: #f2f2f2;
    }

    table.dataTable thead th, table.dataTable thead td {
        padding: 10px 18px;
        border-bottom: 1px solid #ddd !important;
    }

    /* Zebra-striped table rows */
    tr:nth-child(even) {
        background-color: #f2f2f2 !important
    }
</style>";

echo $html;
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.thpage', get_string('list_teacher', 'mod_thpage'), $lang));
echo $OUTPUT->footer();