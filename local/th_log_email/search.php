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

$PAGE->set_url(new moodle_url('/local/th_log_email/search.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'local_th_log_email'));
$PAGE->set_title(get_string('pluginname', 'local_th_log_email'));
$editurl = new moodle_url('/local/th_log_email/search.php');
$PAGE->settingsnav->add(get_string('pluginname', 'local_th_log_email'), $editurl);

$page = optional_param('page', 0, PARAM_INT);
$option = optional_param('option', '', PARAM_TEXT);
$thoi_gian = optional_param('thoi_gian', '', PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);

echo $OUTPUT->header();

echo "<h1><center><a href='$CFG->wwwroot/local/th_log_email/index.php'>" . get_string('email_log', 'local_th_log_email') . "</a></center></h1>";

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

$search = addslashes($_GET['search']);
$option = addslashes($_GET['option']);
$thoi_gian = addslashes($_GET['thoi_gian']);

$thoi_gian1 = strtotime("$thoi_gian");

if (empty($search) && empty($thoi_gian)) {
    echo get_string('empty_field_warning', 'local_th_log_email');
} else {
    $limit = 100;
    if ($option == 'date') {
        $date = strtotime("$thoi_gian");
        $date1 = $date + 60 * 60 * 24 - 1;
        $total_records = $DB->get_field_sql("SELECT COUNT(*) FROM {local_th_log_email} WHERE $option >= $date AND $option <= $date1 ");
        $total_page = ceil($total_records / $limit);
        $current_page = $page;

        if ($current_page > $total_page) {
            $current_page = $total_page;
        } else if ($current_page < 1) {
            $current_page = 1;
        }
        $start = ($current_page - 1) * $limit;
        $sql = $DB->get_records_sql("SELECT * FROM {local_th_log_email} WHERE $option >= $date AND $option <= $date1 ORDER BY id DESC LIMIT $start, $limit");
    } else {
        $total_records = $DB->get_field_sql("SELECT COUNT(*) FROM {local_th_log_email} WHERE $option LIKE '%$search%'");
        $total_page = ceil($total_records / $limit);
        $current_page = $page;

        if ($current_page > $total_page) {
            $current_page = $total_page;
        } else if ($current_page < 1) {
            $current_page = 1;
        }
        $start = ($current_page - 1) * $limit;
        $sql = $DB->get_records_sql("SELECT * FROM {local_th_log_email} WHERE $option LIKE '%$search%' ORDER BY id DESC LIMIT $start, $limit");
    }

    $num = count($sql);

    if ($num > 0) {
        if ($option == 'date') {
            echo "<div style='margin-bottom: 5px;'>" . get_string('search_result_date', 'local_th_log_email', ['total' => $total_records, 'date' => $thoi_gian]) . "</div>";
        } else {
            echo "<div style='margin-bottom: 5px;'>" . get_string('search_result_keyword', 'local_th_log_email', ['total' => $total_records, 'keyword' => $search]) . "</div>";
        }        

        echo '<table class = "th_table_log_email_search" border="1" cellspacing="0" cellpadding="10" width="100%">';
        echo '<tr>';
        echo "<th><strong>" . get_string('stt', 'local_th_log_email') . "</strong></th>";
        echo "<th><strong>" . get_string('send_date', 'local_th_log_email') . "</strong></th>";
        echo "<th><strong>" . get_string('sender', 'local_th_log_email') . "</strong></th>";
        echo "<th><strong>" . get_string('receiver', 'local_th_log_email') . "</strong></th>";
        echo "<th><strong>" . get_string('subject', 'local_th_log_email') . "</strong></th>";
        echo "<th><strong>" . get_string('content', 'local_th_log_email') . "</strong></th>";
        echo '</tr>';
        foreach ($sql as $row) {
            $start++;
            $thoi_gian_gui = date("d-m-Y H:i", $row->date);
            echo '<tr>';
            echo "<td>$start</td>";
            echo "<td>$thoi_gian_gui</td>";
            echo "<td>$row->sender</td>";
            echo "<td>$row->receiver</td>";
            echo "<td>$row->subject</td>";

            $noi_dung = text_to_html($row->body);
            echo "<td><a class='show_content'>" . get_string('show_content', 'local_th_log_email') . "</a>$noi_dung</td>";
            echo '</tr>';
        }
        echo '</table>';

        echo '<div class="pagination1">';
        $config = [
            'total' => $total_records,
            'limit' => $limit,
            'full' => false,
            'querystring' => "option=$option&thoi_gian=$thoi_gian&search=$search&page",
        ];
        $page = new Pagination($config);
        echo $page->getPagination1();
        echo '</div>';
        echo "<strong>" . get_string('total_items', 'local_th_log_email', $total_records) . "</strong>";
        echo '<br><br><br><div align="right">
                <form action="index.php" method="post">
                    <input type="hidden" id="delete" name="delete" value="1">
                <b>' . get_string('delete_from_date', 'local_th_log_email') . '</b>
                    <input type="date" id="startdate" name="startdate">
                <b>' . get_string('delete_to_date', 'local_th_log_email') . '</b>
                    <input type="date" id="enddate" name="enddate">
                    <input type="submit" name="xoa" value="' . get_string('delete_log', 'local_th_log_email') . '" />
                </form>
            </div>';
    } else {
        echo "<div><strong>" . get_string('no_results_found', 'local_th_log_email') . "</strong></div>";
    }
}

echo $OUTPUT->footer();
?>

<script type="text/javascript">
    $(document).ready(function() {

        var input = document.getElementById("tim_kiem");
        var thoi_gian = document.getElementById("thoi_gian");
        var date = <?php echo json_encode($thoi_gian); ?>;
        var search = <?php echo json_encode($search); ?>;
        var select = document.getElementById("option");
        var option = <?php echo json_encode($option); ?>;
        var num = <?php echo json_encode($num); ?>;

        thoi_gian.value = date;
        input.value = search;

        if(num != 0) {
            document.getElementById("startdate").valueAsDate = new Date();
            document.getElementById("enddate").valueAsDate = new Date();
        }

    	document.getElementById(option).selected = true;

        if (option == 'date'){
            input.setAttribute("hidden", "hidden");
            thoi_gian.removeAttribute("hidden");
        } else {
            input.removeAttribute("hidden");
            thoi_gian.setAttribute("hidden", "hidden");
        }

        document.getElementById("option").onchange = function() {
            run();
        };

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