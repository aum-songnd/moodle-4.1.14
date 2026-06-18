<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/csvlib.class.php';
require_once $CFG->dirroot . '/local/th_log_email/lib.php';
require_once $CFG->dirroot . '/local/th_log_email/pagination.php';

global $DB, $CFG, $COURSE, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_log_email', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_log_email:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_log_email/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'local_th_log_email'));
$PAGE->set_title(get_string('pluginname', 'local_th_log_email'));
$editurl = new moodle_url('/local/th_log_email/index.php');
$PAGE->settingsnav->add(get_string('pluginname', 'local_th_log_email'), $editurl);

$page = optional_param('page', 0, PARAM_INT);
$search_page = optional_param('search_page', 0, PARAM_INT);
$option = optional_param('option', '', PARAM_TEXT);
$thoi_gian = optional_param('thoi_gian', '', PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$startdate = optional_param('startdate', 0, PARAM_TEXT);
$enddate = optional_param('enddate', 0, PARAM_TEXT);

if ($delete) {
    $baseurl = new moodle_url('/local/th_log_email/index.php');
    if ($confirm and confirm_sesskey()) {

        if (is_numeric($startdate) and is_numeric($enddate)) {
            $select = "date >= $startdate AND date <= $enddate";
            $count = $DB->count_records_select('local_th_log_email', $select, null, "COUNT('id')");
            $DB->delete_records_select('local_th_log_email', $select);
            $message = get_string('delete_success', 'local_th_log_email', $count);
            redirect($baseurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            $message = get_string('invalid_data', 'local_th_log_email');
            redirect($baseurl, $message, null, \core\output\notification::NOTIFY_ERROR);
        }
    }

    $startdate = strtotime($startdate);
    $enddate = strtotime($enddate) + 60 * 60 * 24 - 1;

    if (!is_numeric($startdate) or !is_numeric($enddate)) {
        echo "<b>" . get_string('invalid_value', 'local_th_log_email') . "</b>";
        echo $OUTPUT->footer();
        die;
    }

    $select = "date >= $startdate AND date <= $enddate";
    $count = $DB->count_records_select('local_th_log_email', $select, null, "COUNT('id')");

    if ($count) {
        $strheading = get_string('delete');
        $PAGE->set_title($strheading);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);

        $yesurl = new moodle_url('/local/th_log_email/index.php',
            array(
                'confirm' => 1,
                'delete' => 1,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'sesskey' => sesskey(),
            )
        );
        $message = get_string('confirm_delete_records', 'local_th_log_email', [
            'startdate' => date("d/m/Y H:i:s", $startdate),
            'enddate'   => date("d/m/Y H:i:s", $enddate),
            'count'     => $count
        ]);        
        echo $OUTPUT->confirm($message, $yesurl, $baseurl);
    } else {
        $message = get_string('no_records_in_time_range', 'local_th_log_email', [
            'startdate' => date("d/m/Y H:i:s", $startdate),
            'enddate'   => date("d/m/Y H:i:s", $enddate)
        ]);
        redirect($baseurl, $message, null, \core\output\notification::NOTIFY_WARNING);
        
    }

    echo $OUTPUT->footer();
    die;
}

echo $OUTPUT->header();

$total_records = $DB->get_field_sql("SELECT COUNT(*) FROM {local_th_log_email}");
$limit = 100;
$total_page = ceil($total_records / $limit);
$current_page = $page;

echo "<h1><center><a href='$CFG->wwwroot/local/th_log_email/index.php'>" . 
     get_string('email_log', 'local_th_log_email') . 
     "</a></center></h1>";

if ($current_page > $total_page) {
    $current_page = $total_page;
} else if ($current_page < 1) {
    $current_page = 1;
}

$start = ($current_page - 1) * $limit;

$result = $DB->get_records_sql("SELECT * FROM {local_th_log_email} ORDER BY id DESC LIMIT $start, $limit ");

$table = new html_table();
$table->head = array(
    get_string('stt', 'local_th_log_email'),
    get_string('send_date', 'local_th_log_email'),
    get_string('sender', 'local_th_log_email'),
    get_string('receiver', 'local_th_log_email'),
    get_string('subject', 'local_th_log_email'),
    get_string('content', 'local_th_log_email')
);

foreach ($result as $data) {
    $start++;
    $row = new html_table_row();
    $cell = new html_table_cell($start);
    $row->cells[] = $cell;
    $thoi_gian_gui = date("d/m/Y H:i", $data->date);
    $cell = new html_table_cell($thoi_gian_gui);
    $row->cells[] = $cell;
    $cell = new html_table_cell($data->sender);
    $row->cells[] = $cell;
    $cell = new html_table_cell($data->receiver);
    $row->cells[] = $cell;
    $cell = new html_table_cell($data->subject);
    $row->cells[] = $cell;

    $body = text_to_html($data->body);
    $cell = new html_table_cell(
        "<a class='show_content'>" . get_string('show_content', 'local_th_log_email') . "</a>$body"
    );    
    $row->cells[] = $cell;
    $table->data[] = $row;
}
;

$table->attributes = array('class' => 'th_table_log_email', 'border' => '1');
$table->attributes['style'] = "width: 100%; text-align: center;";
$html = html_writer::table($table);

echo '<div align="right">
		<form action="search.php" method="get">
			<b>' . get_string('search_by', 'local_th_log_email') . '</b> <select id="option" name="option">
				<option id="date" value="date">' . get_string('send_date', 'local_th_log_email') . '</option>
				<option id="sender" value="sender">' . get_string('sender', 'local_th_log_email') . '</option>
				<option id="receiver" value="receiver">' . get_string('receiver', 'local_th_log_email') . '</option>
				<option id="subject" value="subject">' . get_string('subject', 'local_th_log_email') . '</option>
				<option id="body" value="body">' . get_string('content', 'local_th_log_email') . '</option>
			</select>
			<input type="date" id="thoi_gian" name="thoi_gian">
			<input id="tim_kiem" type="text" name="search"/>
			<input type="submit" name="ok" value="' . get_string('search', 'local_th_log_email') . '" />
		</form>
	</div>';

echo "<link rel='stylesheet' type='text/css' href='./style.css'/>";
echo $html;
echo '<div class="pagination1">';
$config = [
    'total' => $total_records,
    'limit' => $limit,
    'full' => false,
    'querystring' => 'page',
];
$page = new Pagination($config);
echo $page->getPagination();
echo '</div>';
echo get_string('total_emails', 'local_th_log_email', number_format($total_records));
echo '<br><br><br><div align="right">
        <form method="post">
            <input type="hidden" id="delete" name="delete" value="1">
            <b>' . get_string('delete_from_date', 'local_th_log_email') . '</b>
            <input type="date" id="startdate" name="startdate">
            <b>' . get_string('to', 'local_th_log_email') . '</b>
            <input type="date" id="enddate" name="enddate">
            <input type="submit" name="xoa" value="' . get_string('delete_log', 'local_th_log_email') . '" />
        </form>
    </div>';

echo $OUTPUT->footer();
?>
<script type="text/javascript">
$(document).ready(function() {

    var input = document.getElementById("tim_kiem");
    var thoi_gian = document.getElementById("thoi_gian");
    document.getElementById("thoi_gian").valueAsDate = new Date();
    document.getElementById("startdate").valueAsDate = new Date();
    document.getElementById("enddate").valueAsDate = new Date();

    document.getElementById("option").onchange = function() {
        run();
    };

    input.setAttribute("hidden", "hidden");

    function run() {
        var value = document.getElementById("option").value;

        if (value != 'date') {
            thoi_gian.setAttribute("hidden", "hidden");
            input.removeAttribute("hidden");
        } else {
            input.setAttribute("hidden", "hidden");
            thoi_gian.removeAttribute("hidden", "hidden");
        }
    }


	var nodeList2 = document.querySelectorAll(".text_to_html")
	var nodeList = document.querySelectorAll(".show_content")

	for (let i = 0; i < nodeList.length; i++) {
	  	nodeList2[i].style.display = "none";
	  	nodeList[i].onclick = function() {
        	nodeList[i].style.display = "block";
        	for (let j = 0; j < nodeList2.length; j++) {
		        nodeList2[i].style.display = "block";
		        nodeList[i].style.display = "none";
			}
    	};
	}

});
</script>