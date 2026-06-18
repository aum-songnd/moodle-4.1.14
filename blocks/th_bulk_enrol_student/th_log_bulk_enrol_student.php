<?php

require_once '../../config.php';
require_once 'blocklib.php';

global $DB,$OUTPUT,$PAGE, $COURSE;

$courseid = $COURSE->id;

$page = optional_param('page', 0, PARAM_INT);
$ma_ghi_danh = optional_param('ma_ghi_danh', '', PARAM_TEXT);
$status = optional_param('status', '', PARAM_TEXT);
$itemsPerPage = optional_param('itemsperpage', 100, PARAM_INT);

require_login($courseid);
require_capability('block/th_bulk_enrol_student:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_bulk_enrol_student/th_log_bulk_enrol_student.php';
$title = get_string('report_bulk_enrol', 'block_th_bulk_enrol_student');

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($SITE->fullname . ': ' . $title);

$editurl = new moodle_url('/blocks/th_bulk_enrol_student/th_log_bulk_enrol_student.php');
$settingsnode = $PAGE->navbar->add(get_string('report_bulk_enrol', 'block_th_bulk_enrol_student'), $editurl);
$settingsnode->make_active();
	
if($ma_ghi_danh){
    if($status == 'success'){
        $list_data = $DB->get_records_sql("SELECT * FROM {th_log_bulk_enrol_student} WHERE ma_ghi_danh = '$ma_ghi_danh' AND status = 1");
    } else if ($status == 'error') {
        $list_data = $DB->get_records_sql("SELECT * FROM {th_log_bulk_enrol_student} WHERE ma_ghi_danh = '$ma_ghi_danh' AND status = 0");
    } else {
        $list_data = $DB->get_records_sql("SELECT * FROM {th_log_bulk_enrol_student} WHERE ma_ghi_danh = '$ma_ghi_danh'");
    }
} else {
    if($status == 'success'){
        $list_data = $DB->get_records_sql("SELECT * FROM {th_log_bulk_enrol_student} WHERE status = 1 ORDER BY timecreated DESC");
    } else if ($status == 'error') {
        $list_data = $DB->get_records_sql("SELECT * FROM {th_log_bulk_enrol_student} WHERE status = 0 ORDER BY timecreated DESC");
    } else {
        $list_data = $DB->get_records_sql("SELECT * FROM {th_log_bulk_enrol_student} ORDER BY timecreated DESC");
    }
}

// Lấy mục dữ liệu tương ứng với trang hiện tại
$start = $page * $itemsPerPage;
$currentPageData = array_slice($list_data, $start, $itemsPerPage);

$table = new html_table();
$table->head = array(
    get_string('stt', 'block_th_bulk_enrol_student'),
    get_string('student', 'block_th_bulk_enrol_student'),
    get_string('course', 'block_th_bulk_enrol_student'),
    get_string('notification', 'block_th_bulk_enrol_student'),
    get_string('status', 'block_th_bulk_enrol_student'),
    get_string('assigned_by', 'block_th_bulk_enrol_student'),
    get_string('enrolment_code', 'block_th_bulk_enrol_student')
);
$count = 0;

foreach($currentPageData as $data) {
    
    $count++;
    $row = new html_table_row();

    $user_id = $data->user_id;
    $course_id = $data->course_id;
    $notification = $data->notifications;
    $status_value = $data->status;
    $nguoi_tao = $data->nguoi_tao;
    $ma_ghi_danh_value = $data->ma_ghi_danh;
    $timecreated = $data->timecreated;

    if($user_id){
    	$user = $DB->get_record('user', array('id' => $user_id));
    	$username = html_writer::link(new moodle_url('/user/view.php', array('id' => $user_id)), $user->firstname . ' ' . $user->lastname);
    } else {
    	$username = 'N/A';
    }

    if($course_id){
    	$course = $DB->get_record('course', array('id' => $course_id));
    	$coursename = html_writer::link(new moodle_url('/course/view.php', array('id' => $course_id)), $course->fullname);
    } else {
    	$coursename = 'N/A';
    }

    if($nguoi_tao){
    	$tt_nguoi_tao = $DB->get_record('user', array('id' => $nguoi_tao));
    	$nguoi_tao_name = html_writer::link(new moodle_url('/user/view.php', array('id' => $nguoi_tao)), $tt_nguoi_tao->firstname . ' ' . $tt_nguoi_tao->lastname);
    } else {
    	$nguoi_tao_name = 'N/A';
    }

    if ($status_value == 0) {
        $status_text = html_writer::tag('span', get_string('assign_failed', 'block_th_bulk_enrol_student'), array('class' => 'badge badge-warning'));
    } else {
        $status_text = html_writer::tag('span', get_string('assign_success', 'block_th_bulk_enrol_student'), array('class' => 'badge badge-success'));
    }

    $row->cells[] = new html_table_cell($count);
    $row->cells[] = new html_table_cell($username);
    $row->cells[] = new html_table_cell($coursename);
    
    $cell = new html_table_cell($notification);
	$row->cells[] = $cell;
    if($status_value == 0){
    	$cell->attributes = array('class' => "bg-danger", 'style' => 'color:#fff;');
    } else {
    	$cell->attributes = array('class' => "bg-success");
    }
    $cell->style = 'color: #fff; width: 30%';

    $row->cells[] = new html_table_cell($status_text);
    $row->cells[] = new html_table_cell($nguoi_tao_name);
    $row->cells[] = new html_table_cell(date('H:i:s d-m-Y', $ma_ghi_danh_value));

    $table->data[] = $row;
    
}

$table->attributes = array('class' => 'th_log_bulk_enrol_student_table', 'border' => 1);
$html = html_writer::table($table);
echo $OUTPUT->header();

$context = context_system::instance();
$baseurl = new moodle_url('/blocks/th_bulk_enrol_student/th_log_bulk_enrol_student.php');
if ($editcontrols = local_th_bulk_enrol_student_controls($context, $baseurl)) {
    echo $OUTPUT->render($editcontrols);
}

echo $OUTPUT->heading('<center>' . html_writer::link(new moodle_url('/blocks/th_bulk_enrol_student/th_log_bulk_enrol_student.php', array()), "Báo cáo gán học viên hàng loạt") . '</center>');
echo '</br>';
$list_ma_ghi_danh = $DB->get_records_sql("SELECT DISTINCT(ma_ghi_danh) FROM {th_log_bulk_enrol_student}");
$option_ma_ghi_danh = '<option id="ma_ghi_danh" value="">Chưa chọn</option>';

foreach($list_ma_ghi_danh as $ma_gd){
    $code = $ma_gd->ma_ghi_danh;
    $option_ma_ghi_danh .= '<option id="ma_ghi_danh" value="'.$code.'">'.date('H:i:s d-m-Y', $code).'</option>';
}

echo '
    <form method="get">
        <div id="th_search_advanced">
            <div>
                <b style="vertical-align: text-bottom;">' . get_string('select_enrolment_code', 'block_th_bulk_enrol_student') . '</b> 
                <select id="option_ma_ghi_danh" name="ma_ghi_danh">
                    ' . $option_ma_ghi_danh . '
                </select>
            </div>
            <div>
                <b style="vertical-align: text-bottom;">' . get_string('select_status', 'block_th_bulk_enrol_student') . '</b> 
                <select id="option_status" name="status">
                    <option id="status_option" value="">' . get_string('status_not_selected', 'block_th_bulk_enrol_student') . '</option>
                    <option id="status_option" value="success">' . get_string('assign_success', 'block_th_bulk_enrol_student') . '</option>
                    <option id="status_option" value="error">' . get_string('assign_failed', 'block_th_bulk_enrol_student') . '</option>
                </select>
            </div>
            <input type="hidden" id="page" name="page" value="' . $page . '">
            <input type="submit" value="' . get_string('search', 'block_th_bulk_enrol_student') . '"/>
        </div>
    </form>';
echo $html;

echo '<div id="th_itemsperpage">
    <b style="vertical-align: text-bottom;">' . get_string('view', 'block_th_bulk_enrol_student') . '</b>
        <select id="itemsperpage" name="itemsperpage">
            <option value="10">10</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="500">500</option>
            <option value="1000">1000</option>
        </select>
        <b style="vertical-align: text-bottom;">' . get_string('items', 'block_th_bulk_enrol_student') . '</b>
    </div>';

// panigation
$baseurl = new moodle_url('/blocks/th_bulk_enrol_student/th_log_bulk_enrol_student.php', array('status' => $status,'ma_ghi_danh' => $ma_ghi_danh, 'page' => $page, 'itemsperpage' => $itemsPerPage));
echo $OUTPUT->paging_bar(count($list_data), $page, $itemsPerPage, $baseurl);

echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>';

echo $OUTPUT->footer();

?>

<script type="text/javascript">
    $(document).ready(function() {

        $('.th_log_bulk_enrol_student_table').DataTable( {
            paging: false,
            dom: 'fBrtip',
            buttons: [
                'excelHtml5',   
            ]
        });

        var value_ma_ghi_danh = <?php echo json_encode($ma_ghi_danh); ?>;
        var value_status = <?php echo json_encode($status); ?>;
        var value_itemsperpage = <?php echo json_encode($itemsPerPage); ?>;

        var option_ma_ghi_danh = document.getElementById("option_ma_ghi_danh");
        var option_status = document.getElementById("option_status");
        var itemsperpage = document.getElementById("itemsperpage");

        option_ma_ghi_danh.value = value_ma_ghi_danh;
        option_status.value = value_status;
        itemsperpage.value = value_itemsperpage;

        $('#itemsperpage').on('change', function() {
            var selectedValue = $(this).val();
            
            let url = window.location.href;
            let urlParts = url.split("?");
            let baseUrl = urlParts[0];
            let queryString = urlParts[1] || "";
            let params = {};
            queryString.split("&").forEach(param => {
                let parts = param.split("=");
                let key = parts[0];
                let value = parts[1];

                if(key){
                    if(value){
                        params[key] = value;
                    }
                } 
            });

            params['itemsperpage'] = selectedValue;
            params['page'] = 0;
            let newQueryString = Object.keys(params).map(key => key + "=" + params[key]).join("&");
            let newUrl = baseUrl + "?" + newQueryString;

            window.location.href = newUrl;

        });
    });
</script>