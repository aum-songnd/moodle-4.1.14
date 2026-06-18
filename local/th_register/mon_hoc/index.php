<?php

require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_register/mon_hoc/locallib.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION, $DB;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/mon_hoc/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_courses', 'local_th_register'));
$PAGE->set_title(get_string('manage_courses', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/mon_hoc/index.php');
$PAGE->settingsnav->add(get_string('manage_courses', 'local_th_register'), $editurl);

$page = optional_param('page', 0, PARAM_INT);
$id   = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/local/th_register/mon_hoc/index.php');

$ds_monhoc = $DB->get_records_sql("SELECT * FROM {th_mon_hoc}");
if (!empty($ds_monhoc)) {
    $so_mon = count($ds_monhoc);
} else {
    $so_mon = 0;
}

$table       = new html_table();
$table->head = array(
    get_string('no.', 'local_th_register'),
    get_string('ten_mon', 'local_th_register'),
    get_string('ma_mon', 'local_th_register'),
    get_string('so_tin_chi', 'local_th_register'),
    get_string('ngay_tao', 'local_th_register'),
    get_string('nguoi_tao', 'local_th_register'),
    get_string('ngay_sua', 'local_th_register'),
    get_string('nguoi_sua', 'local_th_register'),
    get_string('action', 'local_th_register')
);
$stt         = 0;

foreach ($ds_monhoc as $k => $mon_hoc) {
    $stt = $stt + 1;
    $urlparams = array('id' => $mon_hoc->id, 'returnurl' => $baseurl->out_as_local_url(false));
    $link_edit = new moodle_url('/local/th_register/mon_hoc/edit.php', $urlparams,);
    $edit      = html_writer::link($link_edit, $OUTPUT->pix_icon('t/edit', get_string('edit')), array('title' => get_string('edit')));
    $link_delete = new moodle_url('/local/th_register/mon_hoc/edit.php', $urlparams + array('delete' => 1));
    $delete      = html_writer::link(
        $link_delete,
        $OUTPUT->pix_icon('t/delete', get_string('delete')),
        array('title' => get_string('delete'))
    );

    $mon_hoc_id = $mon_hoc->id;

    $row          = new html_table_row();
    $cell         = new html_table_cell($stt);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($mon_hoc->ten_mon);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($mon_hoc->ma_mon);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($mon_hoc->so_tin_chi);
    $row->cells[] = $cell;
    $cell         = new html_table_cell(date('d/m/Y', $mon_hoc->ngay_tao));
    $row->cells[] = $cell;

    $fullname     = get_full_name($mon_hoc->nguoi_tao);
    $cell         = new html_table_cell($fullname);
    $row->cells[] = $cell;

    if ($mon_hoc->ngay_sua == null) {
        $cell         = new html_table_cell('');
        $row->cells[] = $cell;
    } else {
        $cell         = new html_table_cell(date('d/m/Y', $mon_hoc->ngay_sua));
        $row->cells[] = $cell;
    }

    if ($mon_hoc->nguoi_sua == null) {
        $cell         = new html_table_cell('');
        $row->cells[] = $cell;
    } else {
        $fullname1    = get_full_name($mon_hoc->nguoi_sua);
        $cell         = new html_table_cell($fullname1);
        $row->cells[] = $cell;
    }

    $checkbox = "<input type='checkbox' id='check_box' class='check_box' name='mon_hoc[]' value='$mon_hoc_id'>";
    $cell          = new html_table_cell($edit . $delete .  $checkbox);
    $row->cells[]  = $cell;
    $table->data[] = $row;
}

$table->attributes          = array('class' => 'th_register_course', 'border' => '1');
// $table->attributes['style'] = "width: 100%; text-align:center;";

$html = html_writer::table($table);

echo $OUTPUT->header();
echo "</br>";
if ($editcontrols = local_th_register_course_controls($context, $baseurl)) {
    echo $OUTPUT->render($editcontrols);
}

echo $OUTPUT->heading(get_string('danh_sach_mon_hoc', 'local_th_register'));
echo $OUTPUT->heading(get_string('tong_so_mon_hoc', 'local_th_register', $so_mon));
echo "</br>";
if (empty($ds_monhoc)) {
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
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_register_course', 'LIST COURSE', $lang));
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