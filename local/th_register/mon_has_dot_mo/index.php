<?php

require_once '../../../config.php';
require_once $CFG->libdir.'/adminlib.php';
require_once $CFG->dirroot.'/local/th_register/mon_has_dot_mo/lib.php';
require_once $CFG->dirroot.'/local/th_register/lib.php';

global $PAGE, $COURSE, $OUTPUT, $DB;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/mon_has_dot_mo/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_course_opening', 'local_th_register'));
$PAGE->set_title(get_string('manage_course_opening', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/mon_has_dot_mo/index.php');
$PAGE->settingsnav->add(get_string('manage_course_opening', 'local_th_register'), $editurl);

$id = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/local/th_register/mon_has_dot_mo/index.php');

if ($id) {
	$ds_mon_has_dot = $DB->get_records_sql("SELECT * FROM {th_dot_mo_has_mon_hoc} WHERE dot_mo_mon_id=$id");
} else {
	$ds_mon_has_dot = $DB->get_records_sql("SELECT * FROM {th_dot_mo_has_mon_hoc}");
}

if (!empty($ds_mon_has_dot)) {
	$so_mon_has_dot = count($ds_mon_has_dot);
} else {
	$so_mon_has_dot = 0;
}

$table       = new html_table();
$table->head = array(
    get_string('no.', 'local_th_register'), 
    get_string('batch_opening', 'local_th_register'), 
    get_string('course', 'local_th_register'), 
    get_string('timecreated', 'local_th_register'), 
    get_string('creator', 'local_th_register'),
    get_string('action', 'local_th_register')
);
$stt         = 0;

foreach ($ds_mon_has_dot as $k => $mon_has_dot) {

	$stt = $stt+1;

	$urlparams = array('id' => $mon_has_dot->id, 'returnurl' => $baseurl->out_as_local_url(false));

	$link_delete = new moodle_url('/local/th_register/mon_has_dot_mo/edit.php', $urlparams+array('delete' => 1));
	$delete      = html_writer::link($link_delete, $OUTPUT->pix_icon('t/delete', get_string('delete')),
		array('title' => get_string('delete')));

	$row          = new html_table_row();
	$cell         = new html_table_cell($stt);
	$row->cells[] = $cell;
	$cell         = new html_table_cell(th_register_get_ten_dot_mo_mon($mon_has_dot->dot_mo_mon_id));
	$row->cells[] = $cell;
	$cell         = new html_table_cell(th_register_get_ten_mon_hoc($mon_has_dot->mon_hoc_id));
	$row->cells[] = $cell;
	$cell         = new html_table_cell(date('d/m/Y', $mon_has_dot->ngay_tao));
	$row->cells[] = $cell;
	$fullname     = th_register_get_user_fullname($mon_has_dot->nguoi_tao);
	$cell         = new html_table_cell($fullname);
	$row->cells[] = $cell;

	$checkbox = "<input type='checkbox' id='check_box' class='check_box' name='mon_has_dot_mo[]' value='$mon_has_dot->id'>";
	$cell          = new html_table_cell($delete . $checkbox);
	$row->cells[]  = $cell;
	$table->data[] = $row;
}

$table->attributes = array('class' => 'th_register_table', 'border' => '1');
$table->attributes['style'] = "width: 100%; text-align:center;";

$table->align[0] = 'center';
$html            = html_writer::table($table);

echo $OUTPUT->header();
echo "</br>";
if ($editcontrols = local_th_mon_has_dot_mo_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}
if ($id) {
	$ten_dot_mo = th_register_get_ten_dot_mo_mon($id);
} else {
	$ten_dot_mo = get_string('all_batches', 'local_th_register');
}

// echo $OUTPUT->heading('Danh sách các môn học');
$a = new stdClass();
$a->batch = $ten_dot_mo;
$a->count = $so_mon_has_dot;

echo $OUTPUT->heading(get_string('total_courses_in_batch', 'local_th_register', $a));
echo "</br>";
if (empty($ds_mon_has_dot)) {
	echo get_string('no_courses_found', 'local_th_register');
} else {
	echo "<form action='xu_ly_xoa.php' method='post'>";
    echo "<div style='text-align: right;'>
            <input id='select-all' type='button' value='".get_string('select_all', 'local_th_register')."'>
            <input class='delete-all' type='submit' id='delete-all' value='".get_string('delete_selected_courses', 'local_th_register')."' name='delete-all'>
        </div>";
	echo $html;
	echo '</form>';
}
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_register_table', get_string('list_course', 'local_th_register'), $lang));
echo $OUTPUT->footer();

?>

<script type="text/javascript">
    $(document).ready(function() {
        if(document.getElementById('select-all')){
            var checkboxes1 = document.querySelectorAll('.check_box');
            document.getElementById('select-all').onclick = function() {

                var listArray = [];

                if(document.getElementById('select-all').value == "<?php echo get_string('select_all', 'local_th_register'); ?>"){
                    for (var checkbox of checkboxes1) {
                        checkbox.checked = true;
                    }
                    document.getElementById('select-all').value = "<?php echo get_string('deselect_all', 'local_th_register'); ?>";
                    
                } else {
                    for (var checkbox of checkboxes1) {
                        checkbox.checked = false;
                    }
                    document.getElementById('select-all').value = "<?php echo get_string('select_all', 'local_th_register'); ?>";    
                }
            }
        }

        document.getElementById('delete-all').onclick = function() {
           // Hiển thị hộp thoại xác nhận và lấy kết quả từ người dùng
            var confirmation = confirm("<?php echo get_string('confirm_delete', 'local_th_register'); ?>");

            // Kiểm tra kết quả
            if (confirmation) {
                return true;
            } else {
                return false;
            } 
        }
    });

</script>

