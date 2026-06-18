<?php
require_once '../../config.php';
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

$courseid = $COURSE->id;

if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($courseid);
require_capability('local/th_feedback_report:view', context_course::instance($courseid));

$baseurl = new moodle_url('/local/th_feedback_report/index.php');
$title_log = get_string('title_log_feedback_report', 'local_th_feedback_report');

$PAGE->set_url($baseurl);
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title($title_log);
$PAGE->set_heading($title_log);
$PAGE->navbar->add($title_log, $baseurl);

$list_error = $DB->get_records_sql("SELECT e.*, CONCAT(u.firstname, ' ', u.lastname) as fullname, u.email, ec.name as typename, ec.id as typeid FROM {local_th_feedback_report} e
                                        JOIN {user} u ON u.id = e.userid
                                        LEFT JOIN {th_error_categories} ec ON ec.id = e.type
                                        ORDER BY id DESC");

$component = 'local_th_feedback_report';
$filearea = 'screenshot';

$table = new html_table();
$table->head = array(
    get_string('index', 'local_th_feedback_report'),
    get_string('sender', 'local_th_feedback_report'),
    get_string('email', 'local_th_feedback_report'),
    get_string('description', 'local_th_feedback_report'), 
    get_string('url', 'local_th_feedback_report'),
    get_string('attached_image', 'local_th_feedback_report'), 
    get_string('os', 'local_th_feedback_report'),
    get_string('browser', 'local_th_feedback_report'),
    get_string('sent_time', 'local_th_feedback_report'), 
    get_string('status', 'local_th_feedback_report'),
    get_string('response_content', 'local_th_feedback_report'),
    get_string('processing_time', 'local_th_feedback_report'),
    get_string('type', 'local_th_feedback_report'),
    get_string('action', 'local_th_feedback_report')
);
$count = 0;

foreach($list_error as $error) {
    $count++;
    $row = new html_table_row();
    $row->cells[] = new html_table_cell($count);
    $row->cells[] = new html_table_cell($error->fullname);
    $row->cells[] = new html_table_cell($error->email);

    $descriptionCell = new html_table_cell();
    $descriptionCell->text = format_string($error->description, true);
    $descriptionCell->style = 'max-width: 250px; word-wrap: break-word; white-space: normal;';
    $row->cells[] = $descriptionCell;

    $urlCell = new html_table_cell();
    $urlCell->text = $error->url;
    $urlCell->style = 'max-width: 250px; word-wrap: break-word; white-space: normal;';
    $row->cells[] = $urlCell;
    
    $itemid = $error->id;
    // Lấy file từ kho lưu trữ của Moodle
    $fs = get_file_storage();
    $context = context_system::instance();
    $files = $fs->get_area_files($context->id, $component, $filearea, $itemid, 'itemid', false);

    $url = '';
    // Kiểm tra và hiển thị file
    if (!empty($files)) {
        foreach ($files as $file) {
            // Tạo URL cho file
            $file_url = moodle_url::make_pluginfile_url(
                $file->get_contextid(), 
                $file->get_component(), 
                $file->get_filearea(), 
                $file->get_itemid(), 
                $file->get_filepath(), 
                $file->get_filename()
            )->out();

            $url .= "<a href='$file_url' target='_blank'>" . get_string('view_image', 'local_th_feedback_report') . "</a>";
        }
    } else {
        $url = 'N/A';
    }

    $row->cells[] = new html_table_cell($url);
    $row->cells[] = new html_table_cell($error->os_name);
    $row->cells[] = new html_table_cell($error->browser_name);
    $row->cells[] = new html_table_cell(date('H:i:s d-m-Y', $error->timecreated));

    $statusOptions = [
        '0' => get_string('not_processed', 'local_th_feedback_report'),
        '1' => get_string('processing', 'local_th_feedback_report'),
        '2' => get_string('processed', 'local_th_feedback_report'),
    ];

    $selectHtml = '<select name="status" id="status" onchange="updateStatus('.$error->id.', this.value)">';

    foreach ($statusOptions as $value => $text) {
        $selected = $error->status == $value ? ' selected' : '';
        $selectHtml .= '<option value="'.$value.'"'.$selected.'>'.$text.'</option>';
    }

    $selectHtml .= '</select>';
    $row->cells[] = new html_table_cell($selectHtml);

    $historyurl = new moodle_url('/local/th_feedback_report/response_history.php', [
        'id' => $error->id,
        'returnurl' => (new moodle_url('/local/th_feedback_report/index.php'))->out(false)
    ]);
    
    $responsehtml = html_writer::start_tag('form', [
        'action' => new moodle_url('/local/th_feedback_report/update_response.php'),
        'method' => 'post'
    ]);
    
    $responsehtml .= html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'id',
        'value' => $error->id
    ]);
    
    $responsehtml .= html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'sesskey',
        'value' => sesskey()
    ]);
    
    $responsehtml .= html_writer::tag('textarea', s($error->response ?? ''), [
        'name' => 'response',
        'rows' => 3,
        'cols' => 30,
        'required' => 'required',
        'placeholder' => get_string('response_required_placeholder', 'local_th_feedback_report'),
        'style' => 'width: 220px;'
    ]);
    
    $responsehtml .= html_writer::start_tag('div', [
        'style' => 'display: flex; gap: 6px; align-items: center; margin-top: 5px;'
    ]);
    
    $responsehtml .= html_writer::tag(
        'button',
        get_string('update_response', 'local_th_feedback_report'),
        [
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'style' => 'white-space: nowrap;'
        ]
    );
    
    $responsehtml .= html_writer::link(
        $historyurl,
        get_string('view_response_history', 'local_th_feedback_report'),
        [
            'class' => 'btn btn-secondary',
            'style' => 'white-space: nowrap;'
        ]
    );
    
    $responsehtml .= html_writer::end_tag('div');
    
    $responsehtml .= html_writer::end_tag('form');
    
    $row->cells[] = new html_table_cell($responsehtml);
    
    if($error->timemodified){
        $row->cells[] = new html_table_cell(date('H:i:s d-m-Y', $error->timemodified));
    } else {
        $row->cells[] = new html_table_cell('N/A');
    }
    
    $row->cells[] = new html_table_cell($error->typename);
    $selectHtml1 = '<select id="supportType" name="supportType" onchange="showSubcategories_Error(this.value)" required>';
    $selectHtml1 .= '<option value="">' . get_string('select_feedback_type', 'local_th_feedback_report') . '</option>';

    $parentid = $DB->get_field(
        'th_error_categories',
        'parentid',
        ['id' => $error->type]
    );
    $categories = $DB->get_records('th_error_categories');
    foreach ($categories as $category) {
        if ($category->parentid == null) {
            if($category->id == $error->type || $parentid == $category->id){
                $selectHtml1 .= '<option value="' . $category->id . '" selected>' . $category->name . '</option>';
            } else {
                $selectHtml1 .= '<option value="' . $category->id . '">' . $category->name . '</option>';
            }
        }
    }
    $selectHtml1 .= '</select>';

    foreach ($categories as $category) {
        if ($category->parentid == null) {
            $subcategories = $DB->get_records('th_error_categories', array('parentid' => $category->id));
            if (!empty($subcategories)) {
                if($category->id == $parentid){
                    $selectHtml1 .= '<select id="subcate_'.$category->id.'" name="subcate_'.$category->id.'" required>';
                } else {
                    $selectHtml1 .= '<select id="subcate_'.$category->id.'" name="subcate_'.$category->id.'" style="display: none;">';
                }
                $selectHtml1 .= '<option value="">' . get_string('select_feedback_type', 'local_th_feedback_report') . '</option>';
                foreach ($subcategories as $subcategory) {
                    if($subcategory->id == $error->type){
                        $selectHtml1 .= '<option value="' . $subcategory->id . '" selected>' . $subcategory->name . '</option>';
                    } else {
                        $selectHtml1 .= '<option value="' . $subcategory->id . '">' . $subcategory->name . '</option>';
                    }
                }
                $selectHtml1 .= '</select>';
            }
        }
    }
    $row->cells[] = new html_table_cell("<a class='update_type' onclick='showUpdateForm($error->id, `$selectHtml1`)'>" . get_string('error_classification', 'local_th_feedback_report') . "</a>");

    $table->data[] = $row;
}

$table->attributes = array('class' => 'th_feedback_report_table', 'border' => 1);
$table->width = '100%';
$html = html_writer::table($table);

echo $OUTPUT->header();
if ($editcontrols = local_th_feedback_report_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}
echo $OUTPUT->heading("<center><h1>$title_log</h1></center>");
echo '<style>
    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
        justify-content: flex-end;
    }
    .filter-select {
        flex: 1;
        min-width: 200px;
        max-width: 300px;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        height: 40px;
    }

    @media (max-width: 768px) {
        .filter-select {
            width: 100%;
            max-width: none;
        }
    }

    .update_type {
        cursor: pointer;
    }
</style>';

echo '<div class="filter-container">';
echo '<select id="statusFilter" class="filter-select">
        <option value="">' . get_string('all_statuses', 'local_th_feedback_report') . '</option>
        <option value="0">' . get_string('not_processed', 'local_th_feedback_report') . '</option>
        <option value="1">' . get_string('processing', 'local_th_feedback_report') . '</option>
        <option value="2">' . get_string('processed', 'local_th_feedback_report') . '</option>
      </select>';

echo '<select id="timeFilter" class="filter-select">
      <option value="">' . get_string('all_times', 'local_th_feedback_report') . '</option>
      <option value="today">' . get_string('today', 'local_th_feedback_report') . '</option>
      <option value="yesterday">' . get_string('yesterday', 'local_th_feedback_report') . '</option>
      <option value="thisweek">' . get_string('this_week', 'local_th_feedback_report') . '</option>
      <option value="lastweek">' . get_string('last_week', 'local_th_feedback_report') . '</option>
      <option value="thismonth">' . get_string('this_month', 'local_th_feedback_report') . '</option>
      <option value="lastmonth">' . get_string('last_month', 'local_th_feedback_report') . '</option>
    </select>';
echo '<a id="th_export_xls" onClick="javascript:fnExcelReport_xls();"><button style="height: 40px;">' . get_string('export_excel', 'local_th_feedback_report') . '</button></a>';
echo '</div>';

echo $html;

$PAGE->requires->js('/local/th_feedback_report/script.js');
$PAGE->requires->strings_for_js(['error_classification', 'select_feedback_type', 'update', 'update_status_success', 'update_status_error'], 'local_th_feedback_report');

echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.1.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>';

echo '
<script>
$(document).ready(function() {
    $("#DataTables_Table_0").DataTable({
        autoWidth: false,
        responsive: false,
        scrollX: true
    });
});
</script>

<style>
table.dataTable {
    width: 100% !important;
}

table.dataTable th,
table.dataTable td {
    white-space: nowrap;
}

#region-main,
#region-main-box {
    min-width: 0;
}

.table-responsive {
    overflow-x: auto;
}
</style>
';

echo $OUTPUT->footer();

?>