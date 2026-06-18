<?php
require '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/th_register/nganh/lib.php';
require_once $CFG->dirroot . '/local/th_register/lib.php';

global $DB, $CFG, $COURSE, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/nganh/monhoc.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_majors', 'local_th_register'));
$PAGE->set_title(get_string('manage_majors', 'local_th_register'));

$id = required_param('id', PARAM_INT);

$nganh = $DB->get_record('th_nganh_hoc',array('id' => $id));
// exit;

if (!$nganh){
	echo $OUTPUT->header();
	echo html_writer::tag("h2", get_string('no_major', 'local_th_register'));
	echo $OUTPUT->footer();
	exit;
}
$lang = current_language();
$editurl = new moodle_url('/local/th_register/nganh/index.php');
$PAGE->navbar->add(get_string('manage_majors', 'local_th_register'), $editurl);
$PAGE->navbar->add(get_string('course_in_major', 'local_th_register'), $editurl);
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "Ngành $nganh->ten_nganh", $lang));

$sql = "SELECT nganhmon.*,mon.ma_mon,mon.ten_mon
		FROM {th_mon_hoc_has_nganh_hoc} nganhmon
		JOIN {th_nganh_hoc} nganh
		ON nganhmon.nganh_hoc_id = nganh.id
		JOIN {th_mon_hoc} mon
		ON nganhmon.mon_hoc_id = mon.id
		WHERE nganh.id = $id";
$records = $DB->get_records_sql($sql);

$html = get_string('no_courses_in_major', 'local_th_register');
if ($records) {
	$table = new html_table();
	$table->attributes = array('class' => 'table', 'border' => '1');
	$table->align[0] = 'center';

	$table->head = array(get_string('no.', 'local_th_register'),
		get_string('ma_mon', 'local_th_register'),
		get_string('ten_mon', 'local_th_register'),
		get_string('creator', 'local_th_register'),
		get_string('timecreated', 'local_th_register'),
		get_string('editor', 'local_th_register'),
		get_string('timemodified', 'local_th_register'),
		get_string('action')
	);
	$stt = 0;
	foreach ($records as $k => $record) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($record->ma_mon);
		$row->cells[] = $cell;
		$cell = new html_table_cell($record->ten_mon);
		$row->cells[] = $cell;
		$cell = new html_table_cell(th_register_get_user_fullname($record->nguoi_tao));
		$row->cells[] = $cell;
		$cell = new html_table_cell(date('d/m/Y', $record->ngay_tao));
		$row->cells[] = $cell;
		if ($record->nguoi_sua) {
			$cell = new html_table_cell(th_register_get_user_fullname($record->nguoi_sua));
		} else {
			$cell = new html_table_cell();
		}
		$row->cells[] = $cell;
		if ($record->ngay_sua) {
			$cell = new html_table_cell(date('d/m/Y', $record->ngay_sua));
		} else {
			$cell = new html_table_cell();
		}
		$row->cells[] = $cell;
		// $urlparams = array('id' => $record->id);
		// $edit = html_writer::link(new moodle_url('/local/th_register/nganh/edit.php', $urlparams),
		// 	$OUTPUT->pix_icon('t/edit', get_string('edit')),
		// 	array('title' => get_string('edit')));
		// $delete = html_writer::link(new moodle_url('/local/th_register/nganh/edit.php', $urlparams + array('delete' => 1)),
		// 	$OUTPUT->pix_icon('t/delete', get_string('delete')),
		// 	array('title' => get_string('delete')));

		$checkbox = "<input type='checkbox' id='check_box' class='check_box' name='mon_hoc[]' value='$record->id'>";
		$cell = new html_table_cell($checkbox);
		$cell->style = 'text-align: center';
		$row->cells[] = $cell;

		$table->data[] = $row;
		$html = html_writer::table($table);
	}
}

echo $OUTPUT->header();
echo html_writer::tag("h2", get_string('major_course_count', 'local_th_register', [
    'ten_nganh' => $nganh->ten_nganh,
    'count' => count($records)
]));
if ($records) {
 echo "<form action='xu_ly_xoa.php' method='post'>";
    echo "<div style='text-align: right;'>
    		<input type='hidden' id='idnganh' name='idnganh' value='$id'>
            <input id='select-all' type='button' value='".get_string('select_all', 'local_th_register')."'>
            <input class='delete-all' type='submit' id='delete-all' value='".get_string('delete_selected_courses', 'local_th_register')."' name='delete-all'>
        </div>";
    echo $html;
    echo '</form>';
}else{
	echo $html;
}

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
            var confirmation = confirm("<?php get_string('confirm_delete_selected_course', 'local_th_register'); ?>");

            // Kiểm tra kết quả
            if (confirmation) {
                return true;
            } else {
                return false;
            } 
        }
    });

</script>