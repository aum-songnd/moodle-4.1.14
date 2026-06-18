<?php

require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_register/dang_ky_hoc/locallib.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION, $USER, $DB;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);

$PAGE->set_url(new moodle_url('/local/th_register/dang_ky_hoc/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('register_course', 'local_th_register'));
$PAGE->set_title(get_string('register_course', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/dang_ky_hoc/index.php');
$PAGE->settingsnav->add(get_string('register_course', 'local_th_register'), $editurl);

$id = optional_param('id', 0, PARAM_INT);
$nganh = optional_param('nganh', 0, PARAM_INT);
$mon_dang_ky_id = optional_param('mon_dang_ky_id', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$ds_dot_mo_mon = ds_dot_mo_mon_active();
$ds_nganh = th_register_ds_nganh();

echo $OUTPUT->header();
$home_dkh = $CFG->wwwroot . '/local/th_register/dang_ky_hoc/index.php';
echo $OUTPUT->heading("<center><a href='$home_dkh' style = 'color: darkgreen;'>" . get_string('register_course', 'local_th_register') . "</a></center>");
?>
<table style="margin-bottom: 5px;" border="0" cellspacing="1" cellpadding="0">
    <tr>
        <td>
			<?php
             echo "<li style='color: #000;position: relative;bottom: -5px;'>" . get_string('select_opening_batch', 'local_th_register') . "</li>";
			?>
        </td>
        <td>
            <select onchange="goto_url(this.value);" class="th_select" id="ds_dot_mo" onchange="javascript:handleSelect(this)">

                <?php
echo "<option value='index.php'>" . get_string('select_opening_batch', 'local_th_register') . "</option>";
foreach ($ds_dot_mo_mon as $k => $dot_mo) {
	echo "<option id ='$k' value='index.php?id=$k&nganh=$nganh'>$dot_mo</option>";
} ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>
			<?php
            echo "<li style='color: #000;position: relative;bottom: -5px;'>" . get_string('select_major', 'local_th_register') . "</li>";
			?>
        </td>
        <td>
            <select onchange="goto_url(this.value);" class="th_select" id="ds_nganh" onchange="javascript:handleSelect(this)">

                <?php
echo "<option value='index.php?id=$id&nganh=0'>" . get_string('select_major', 'local_th_register') . "</option>";
foreach ($ds_nganh as $k => $ten_nganh) {
	echo "<option id ='nganh$k' value='index.php?id=$id&nganh=$k'>$ten_nganh</option>";
}
?>
            </select>
        </td>
    </tr>
</table>

<link rel='stylesheet' type='text/css' href='./style.css'/>

<?php

$user_id = $USER->id;
$full_name = get_full_name($user_id);

echo "
    <div style = 'color: #000;'>
        <li class='thong_tin'>" . get_string('student_name', 'local_th_register') . ": <strong>$full_name</strong></li>
    </div>";

if ($id && $nganh) {

	$dot_mo_id = $id;
	$dot_mo = $DB->get_record_sql("SELECT * FROM {th_dot_mo_mon} WHERE id = '$dot_mo_id'");

	if ($nganh == 1) {
		$field_ma_hoc_vien = trim(get_config('local_th_register', 'ma_hoc_vien'));
		$field_ma_khoa = trim(get_config('local_th_register', 'ma_khoa'));
		$field_ma_lop = trim(get_config('local_th_register', 'ma_lop'));

	} else if ($nganh == 2) {
		$field_ma_hoc_vien = trim(get_config('local_th_register', 'ma_hoc_vien2'));
		$field_ma_khoa = trim(get_config('local_th_register', 'ma_khoa2'));
		$field_ma_lop = trim(get_config('local_th_register', 'ma_lop2'));
	} else {
		$field_ma_hoc_vien = '';
		$field_ma_khoa = '';
		$field_ma_lop = '';
	}

	$ma_hoc_vien = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_ma_hoc_vien' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
	$khoa = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_ma_khoa' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
	$lop = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_ma_lop' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");

	$start_split_nganh = trim(get_config('local_th_register', 'start_split_nganh'));
	$end_split_nganh = trim(get_config('local_th_register', 'end_split_nganh'));
 
	if (empty($ma_hoc_vien)) {
		echo "<div><li class='thong_tin'>" . get_string('ma_hoc_vien', 'local_th_register') . ": <strong>" . get_string('no_student_id', 'local_th_register') . "</strong></li>";
	} else {
		echo "<div><li class='thong_tin'>" . get_string('ma_hoc_vien', 'local_th_register') . ": <strong>$ma_hoc_vien</strong></li>";
	}

	if (empty($khoa)) {
		echo "<li class='thong_tin'>" . get_string('khoa_hoc', 'local_th_register') . ": <strong>" . get_string('no_course', 'local_th_register') . "</strong></li>";
	} else {
		echo "<li class='thong_tin'>" . get_string('khoa_hoc', 'local_th_register') . ": <strong>$khoa</strong></li>"; 
	}

	if (empty($lop)) {
		echo "<li class='thong_tin'>" . get_string('class', 'local_th_register') . ": <strong>" . get_string('no_class_id', 'local_th_register') . "</strong></li></div>";
	} else {
		echo "<li class='thong_tin'>" . get_string('class', 'local_th_register') . ": <strong>$lop</strong></li></div>";
	}

	$nganh_hoc = $DB->get_record_sql("SELECT * FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh'");
	$nganhid = $nganh_hoc->id;

	if (empty($dot_mo)) {
		echo "<center><h4 style = 'color: red;margin-top: 30px;margin-bottom: 15px;'>" . get_string('invalid_course_opening', 'local_th_register') . "</h4></center>";
	} else {
		if (empty($nganhid)) {
			echo "<center><h4 style = 'color: red;margin-top: 30px;margin-bottom: 15px;'>" . get_string('invalid_major', 'local_th_register') . "</h4></center>";
		} else {
			$tu_ngay = date("H:i:s - d/m/Y", $dot_mo->tu_ngay);
			$den_ngay = date("H:i:s - d/m/Y", $dot_mo->den_ngay);
			$hoc_phi_mot_tin_chi = number_format($nganh_hoc->hoc_phi_mot_tin_chi, 0, ',', '.');
			echo "
                <li style = 'color: #000;font-size: 14px;'>" . get_string('registration_time', 'local_th_register') . ": <strong style = 'color: red;'>" . get_string('from_to_date', 'local_th_register', [
					'start' => $tu_ngay,
					'end'   => $den_ngay
				]) . "</strong></li>
                <center><h4 style = 'color: darkgreen;margin-top: 30px;margin-bottom: 15px;'>" . get_string('available_courses_list', 'local_th_register') . " $dot_mo->ten_dot_mo</h4></center>
            ";

			$ds_mon_dk = ds_dk($user_id, $lop, $dot_mo, $nganhid);

			$stt = 0;
			$so_tin_chi = 0;
			$table = new html_table();
			$table->head = array(
				get_string('no.', 'local_th_register'), 
				get_string('ten_mon', 'local_th_register'), 
				get_string('ma_mon', 'local_th_register'), 
				get_string('batch_opening', 'local_th_register'), 
				get_string('hoc_phi_mot_tin_chi', 'local_th_register'),
				get_string('so_tin_chi', 'local_th_register'),
				get_string('register', 'local_th_register') . "<div><input type='checkbox' id='select-all'></div>"
			);
			foreach ($ds_mon_dk as $k => $mon_dk) {
				$mon_hoc_id = $k;
				$mon_hoc = $DB->get_record_sql("SELECT * FROM {th_mon_hoc} WHERE id = '$mon_hoc_id'");
				$dot_mo_has_mon_hoc_id = $DB->get_field_sql("SELECT id FROM {th_dot_mo_has_mon_hoc} WHERE mon_hoc_id = '$mon_hoc_id' AND dot_mo_mon_id = '$dot_mo_id'");
				$stt = $stt + 1;
				$so_tin_chi = $so_tin_chi + $mon_hoc->so_tin_chi;
				$row = new html_table_row();
				$cell = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell = new html_table_cell("<strong style='color: #000;'>$mon_hoc->ten_mon</strong>");
				$cell->style = 'text-align: left;';
				$row->cells[] = $cell;
				$cell = new html_table_cell("$mon_hoc->ma_mon");
				$row->cells[] = $cell;
				$cell = new html_table_cell(ten_dot_mo($dot_mo_id));
				$row->cells[] = $cell;
				$cell = new html_table_cell("$hoc_phi_mot_tin_chi");
				$row->cells[] = $cell;
				$cell = new html_table_cell("$mon_hoc->so_tin_chi");
				$row->cells[] = $cell;
				$cell = new html_table_cell("<input type='checkbox' id='check_box' class='check_box' name='mon_hoc[]' value='$dot_mo_has_mon_hoc_id,$nganhid'>");
				$row->cells[] = $cell;
				$table->data[] = $row;
			}

			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->colspan = 6;
			$row->cells[] = $cell;
			$cell = new html_table_cell("<input class='dangky' type='submit' id='dangky' value=" . get_string('register', 'local_th_register') . " name='dangky'>");
			$row->cells[] = $cell;
			$table->data[] = $row;

			$table->attributes = array('class' => 'th_register_mon_dk', 'border' => '1');
			$table->attributes['style'] = "width: 100%; text-align: center;";
			$html = html_writer::table($table);
			echo '<div style="overflow-x:auto;">';
			echo "<form action='xu_ly_dang_ky.php?id=$dot_mo_id&nganh=$nganh' method='post'>";
			echo "$html";
			echo '</form>';
			echo '</div>';

			$ds_mon_da_dk = ds_mon_dk($user_id, $dot_mo_id, $nganh_hoc->hoc_phi_mot_tin_chi, $nganhid);
			echo "<center><h4 style = 'color: darkgreen;margin-top: 30px;margin-bottom: 15px;'>" . get_string('registered_courses_list', 'local_th_register') . "</h4></center>";
			echo '<div style="overflow-x:auto;">';
			echo "<form action='xu_ly_dang_ky.php?id=$dot_mo_id&nganh=$nganh' method='post'>";
			echo "$ds_mon_da_dk";
			echo '</form>';
			echo '</div>';

			$all_mon_dk = $DB->get_records_sql("SELECT dk.*, mon.so_tin_chi, nganh.hoc_phi_mot_tin_chi FROM {th_dang_ky_mon_hoc} as dk,{th_dot_mo_has_mon_hoc} as dhm, {th_mon_hoc} as mon, {th_nganh_hoc} as nganh WHERE dk.userid_moodle = '$user_id' AND dhm.id = dk.dot_mo_has_mon_hoc_id AND dhm.dot_mo_mon_id = '$dot_mo_id' AND mon.id = dhm.mon_hoc_id AND dk.id_nganh = nganh.id ;");

			$tongtien = 0;
			$tong_tin_chi = 0;
			foreach($all_mon_dk as $mon_dk){
				$tong_tin_chi = $tong_tin_chi + $mon_dk->so_tin_chi;
				$tongtien = $tongtien + ($mon_dk->so_tin_chi * $mon_dk->hoc_phi_mot_tin_chi);
			}

			$tong_tien = number_format($tongtien, 0, ',', '.');
			$tien_da_dong = $DB->get_field_sql("SELECT so_tien_da_nop FROM {th_hoa_don} WHERE userid_moodle= '$user_id' AND dot_mo_mon_id = '$dot_mo_id'");
			$tien_da_dong_format = number_format($tien_da_dong, 0, ',', '.');
			$tien_no = number_format($tongtien - $tien_da_dong, 0, ',', '.');

			if ($tien_no < 0) {
				$tien_no = 0;
				$tien_du = number_format($tien_da_dong - $tongtien, 0, ',', '.');
			} else {
				$tien_du = 0;
			}

			
			echo "<div style='margin-top: 10px; color: #000'><strong>" . get_string('total_credits_registered', 'local_th_register', $tong_tin_chi) . "</strong></div>";
			echo "<div style='margin-top: 10px; color: #000'><strong>" . get_string('total_tuition_fee', 'local_th_register', $tong_tien) . "</strong></div>";
			echo "<div style='margin-top: 10px; color: #000'><strong>" . get_string('total_paid_tuition', 'local_th_register', $tien_da_dong_format) . "</strong></div>";
			echo "<div style='margin-top: 10px; color: #000'><strong>" . get_string('total_debt', 'local_th_register', $tien_no) . "</strong></div>";
			echo "<div style='margin-top: 10px; color: #000'><strong>" . get_string('total_surplus', 'local_th_register', $tien_du) . "</strong></div>";
			
			
			if ($tong_tin_chi != 0) {
				$link = $CFG->wwwroot . '/local/th_register/hoa_don/edit.php?id=' . $dot_mo_id;
				echo "<a style='margin-top: 10px' href='$link' class='btn btn-primary'> </a>";
			}
		}
	}
} else {
	echo "<center><h4 style = 'color: darkgreen;margin-top: 10px;margin-bottom: 20px;'>" . get_string('registered_courses_list', 'local_th_register') . "</h4></center>";
	$ds_mon_dk_all = ds_mon_dk_all($user_id);
	echo '<div style="overflow-x:auto;">';
	echo "$ds_mon_dk_all[0]";
	echo '</div>';
	echo "<div style='margin-top: 10px; color: #000'><strong>" . get_string('total_credits', 'local_th_register', $ds_mon_dk_all[1]) . "</strong></div>";
	echo "<div style='margin-top: 10px; color: #000'><strong>" . get_string('total_tuition_fees', 'local_th_register', $ds_mon_dk_all[2]) . "</strong></div>";
	
}
echo $OUTPUT->footer();
?>
<script type="text/javascript">
    $(document).ready(function() {
        var id = <?php echo json_encode($id); ?>;
        var nganh = <?php echo json_encode($nganh); ?>;

        if(id != 0){
            document.getElementById(id).selected = true;
        }

        if(nganh != 0){
            document.getElementById('nganh' + nganh).selected = true;
        }

        if(document.getElementById('select-all')){
            document.getElementById('select-all').onclick = function() {
                var checkboxes1 = document.querySelectorAll('.check_box');
                var listArray = [];
                for (var checkbox of checkboxes1) {
                    checkbox.checked = this.checked;
                }
            }
        }

        if(document.getElementById('select-all1')){
            document.getElementById('select-all1').onclick = function() {
                var checkboxes1 = document.querySelectorAll('.check_box1');
                var listArray = [];
                for (var checkbox of checkboxes1) {
                    checkbox.checked = this.checked;
                }
            }
        }

    });

    function goto_url(url)
    {
        if(url!="0"){
            window.location = url;
        }
    }

    document.getElementById('dangky').onclick = function() {
       // Hiển thị hộp thoại xác nhận và lấy kết quả từ người dùng
        var confirmation = confirm("<?php echo get_string('confirm_register_course', 'local_th_register');?>");

        // Kiểm tra kết quả
        if (confirmation) {
            return true;
        } else {
            return false;
        } 
    }

    document.getElementById('huydangky').onclick = function() {
           // Hiển thị hộp thoại xác nhận và lấy kết quả từ người dùng
            var confirmation = confirm("<?php echo get_string('confirm_cancel_register_course', 'local_th_register');?>");

            // Kiểm tra kết quả
            if (confirmation) {
                return true;
            } else {
                return false;
            } 
        }

</script>