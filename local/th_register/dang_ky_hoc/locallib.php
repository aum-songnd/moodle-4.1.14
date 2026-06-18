<?php

function get_full_name($id) {
	global $DB;
	$sql = "SELECT firstname FROM {user} WHERE id = $id";
	$firstname = $DB->get_field_sql($sql);
	$sql1 = "SELECT lastname FROM {user} WHERE id = $id";
	$lastname = $DB->get_field_sql($sql1);
	$fullname = $firstname . ' ' . $lastname;
	return $fullname;
}

function ds_dot_mo_mon() {
	global $DB;
	$sql = "SELECT * FROM {th_dot_mo_mon}";
	$ds_dot_mo_mon = $DB->get_records_sql($sql);

	if (!empty($ds_dot_mo_mon)) {
		foreach ($ds_dot_mo_mon as $id => $dot_mo_mon) {
			$ds[$id] = $dot_mo_mon->ten_dot_mo;
		}
		return $ds;
	}
}

function ds_dot_mo_mon_active() {
	global $DB;
	$sql = "SELECT * FROM {th_dot_mo_mon}";
	$ds_dot_mo_mon = $DB->get_records_sql($sql);

	if (!empty($ds_dot_mo_mon)) {
		$ds = [];
		foreach ($ds_dot_mo_mon as $id => $dot_mo_mon) {
			if (time() >= $dot_mo_mon->tu_ngay && time() <= $dot_mo_mon->den_ngay) {
				$ds[$id] = $dot_mo_mon->ten_dot_mo;
			}
		}
		return $ds;
	}
}

function th_register_ds_nganh1() {
	global $DB, $USER;
	$user_id = $USER->id;
	$nganh_hoc = [];
	$field_nganh_hoc = get_config('local_th_register', 'nganh_hoc');
	$field_ma_lop = get_config('local_th_register', 'ma_lop');
	if (!empty($field_nganh_hoc) && !empty($field_ma_lop)) {
		$lop = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_ma_lop' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
		$start_split_nganh = trim(get_config('local_th_register', 'start_split_nganh'));
		$end_split_nganh = trim(get_config('local_th_register', 'end_split_nganh'));
		$ma_nganh = mb_substr($lop, $start_split_nganh, $end_split_nganh, 'UTF-8');

		$id_nganh = $DB->get_field_sql("SELECT id FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh'");
		if (!empty($id_nganh)) {
			$nganh_hoc[$id_nganh] = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_nganh_hoc' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
		}
	}

	$field_nganh_hoc2 = get_config('local_th_register', 'nganh_hoc2');
	$field_ma_lop2 = get_config('local_th_register', 'ma_lop2');
	if (!empty($field_nganh_hoc2) && !empty($field_ma_lop2)) {
		$lop2 = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_ma_lop2' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
		$start_split_nganh = trim(get_config('local_th_register', 'start_split_nganh'));
		$end_split_nganh = trim(get_config('local_th_register', 'end_split_nganh'));
		$ma_nganh2 = mb_substr($lop, $start_split_nganh, $end_split_nganh, 'UTF-8');
		$id_nganh2 = $DB->get_field_sql("SELECT id FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh2'");
		if (!empty($id_nganh2)) {
			$nganh_hoc[$id_nganh2] = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_nganh_hoc2' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
		}
	}

	return $nganh_hoc;
}

function th_register_ds_nganh() {
	global $DB, $USER;
	$user_id = $USER->id;
	$nganh_hoc = [];
	$field_nganh_hoc = get_config('local_th_register', 'nganh_hoc');
	$nganh = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_nganh_hoc' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
	if (!empty($nganh)) {
		$nganh_hoc[1] = $nganh;
	}

	$field_nganh_hoc2 = get_config('local_th_register', 'nganh_hoc2');
	$nganh2 = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_nganh_hoc2' AND info_data.userid = '$user_id' AND info_data.fieldid = field.id");
	if (!empty($nganh2)) {
		$nganh_hoc[2] = $nganh2;
	}

	return $nganh_hoc;
}

function th_ds_mon() {
	global $DB;
	$sql = "SELECT * FROM {th_mon_hoc}";
	$mon_hoc = $DB->get_records_sql($sql);

	if (!empty($mon_hoc)) {
		foreach ($mon_hoc as $id => $mon) {
			$ds[$id] = $mon->ten_mon . ',' . $mon->ma_mon;
		}
	}
	return $ds;
}

function ten_dot_mo($id) {
	global $DB;
	$sql = "SELECT * FROM {th_dot_mo_mon} where id = '$id'";
	$dot_mo_mon = $DB->get_record_sql($sql);
	$ten_dot_mo = $dot_mo_mon->ten_dot_mo;
	return $ten_dot_mo;
}

function ten_nganh($id) {
	global $DB;
	$sql = "SELECT * FROM {th_nganh_hoc} where id = '$id'";
	$nganh = $DB->get_record_sql($sql);
	$ten_nganh = $nganh->ten_nganh;
	return $ten_nganh;
}

function ten_mon($id) {
	global $DB;
	$sql = "SELECT * FROM {th_mon_hoc} where id = '$id'";
	$mon_hoc = $DB->get_record_sql($sql);
	$ten_mon = $mon_hoc->ten_mon;
	return $ten_mon;
}

function th_check_register($ds_mon_hoc_id, $dot_mo_id) {
	global $DB, $USER;
	$checked_register = new stdClass();
	$checked_register->error_messages = array();
	$checked_register->register = array();
	$checked_register->valid_register_found = 0;
	$checked_register->tong_tien = 0;

	$user_id = $USER->id;
	foreach ($ds_mon_hoc_id as $k => $mon_hoc_id) {
		$dot_mo_has_mon_hoc_id = $DB->get_field_sql("SELECT id FROM {th_dot_mo_has_mon_hoc} WHERE mon_hoc_id = '$mon_hoc_id' AND dot_mo_mon_id = '$dot_mo_id'");
		$sql = "SELECT * FROM {th_dang_ky_mon_hoc} WHERE userid_moodle = '$user_id' AND dot_mo_has_mon_hoc_id = '$dot_mo_has_mon_hoc_id'";
		$dang_ky = $DB->get_record_sql($sql);
		$ten_mon = ten_mon($mon_hoc_id);
		$ten_dot_mo = ten_dot_mo($dot_mo_id);
		if (empty($dang_ky)) {
			$checked_register->valid_register_found += 1;
			$mon_hoc = $DB->get_record_sql("SELECT * FROM {th_mon_hoc} WHERE id = '$mon_hoc_id'");
			$mon_hoc->dot_mo_id = $dot_mo_id;
			$mon_hoc->dot_mo_has_mon_hoc_id = $dot_mo_has_mon_hoc_id;
			$checked_register->register[] = $mon_hoc;
			$hoc_phi_1_tin = $DB->get_field_sql("SELECT hoc_phi_mot_tin_chi FROM {th_dot_mo_mon} WHERE id = '$dot_mo_id'");
			$hoc_phi = $hoc_phi_1_tin * $mon_hoc->so_tin_chi;
			$checked_register->tong_tien += $hoc_phi;
		} else {
			$checked_register->error_messages[] = get_string('course_already_registered', 'local_th_register', [
				'course' => $ten_mon,
				'batch'  => $ten_dot_mo
			]);			
		}
	}
	return $checked_register;
}

function ds_dk($user_id, $lop, $dot_mo, $nganhid) {
	global $DB;
	$ds_mon_dot_mo = $DB->get_records_sql("SELECT mon_hoc_id FROM {th_dot_mo_has_mon_hoc} WHERE dot_mo_mon_id = '$dot_mo->id'");
	if (!empty($lop) && time() >= $dot_mo->tu_ngay && time() <= $dot_mo->den_ngay) {
		$start_split_doi_tuong = trim(get_config('local_th_register', 'start_split_doi_tuong'));
		$end_split_doi_tuong = trim(get_config('local_th_register', 'end_split_doi_tuong'));
		$ma_doi_tuong = mb_substr($lop, $start_split_doi_tuong, $end_split_doi_tuong, 'UTF-8');
		
		$doi_tuong_id = $DB->get_field_sql("SELECT id FROM {th_doi_tuong} WHERE ma_doi_tuong = '$ma_doi_tuong'");
		$nganh_hoc_id = $nganhid;

		$sql = "SELECT {th_dot_mo_has_mon_hoc}.mon_hoc_id FROM {th_dang_ky_mon_hoc},{th_dot_mo_has_mon_hoc} WHERE {th_dang_ky_mon_hoc}.userid_moodle = '$user_id'
                AND {th_dot_mo_has_mon_hoc}.dot_mo_mon_id = '$dot_mo->id' AND {th_dot_mo_has_mon_hoc}.id = {th_dang_ky_mon_hoc}.dot_mo_has_mon_hoc_id";
		$ds_mon_da_dk = $DB->get_records_sql($sql);

		if (!empty($doi_tuong_id) && !empty($ds_mon_dot_mo) && !empty($nganh_hoc_id)) {
			$ds_mon_doi_tuong = $DB->get_records_sql("SELECT mon_hoc_id FROM {th_mon_hoc_has_doi_tuong} WHERE doi_tuong_id = '$doi_tuong_id'");
			$ds_mon_has_nganh = $DB->get_records_sql("SELECT mon_hoc_id FROM {th_mon_hoc_has_nganh_hoc} WHERE nganh_hoc_id = '$nganh_hoc_id'");
			if (!empty($ds_mon_doi_tuong) && !empty($ds_mon_has_nganh)) {
				$arrs = array_intersect_key($ds_mon_dot_mo, $ds_mon_doi_tuong);
				$arrs1 = array_intersect_key($arrs, $ds_mon_has_nganh);
				$arrs2 = array_diff_key($arrs1, $ds_mon_da_dk);
				$ds_mon_dk = [];
				foreach ($arrs2 as $k => $arr) {
					$mon_hoc_id = $arr->mon_hoc_id;
					$mon_hoc = $DB->get_record_sql("SELECT * FROM {th_mon_hoc} WHERE id = '$mon_hoc_id'");
					$ds_mon_dk[$k] = $mon_hoc->ten_mon;
				}
				return $ds_mon_dk;
			} else {
				$ds_mon_dk = [];
				return $ds_mon_dk;
			}
		} else {
			$ds_mon_dk = [];
			return $ds_mon_dk;
		}
	} else {
		$ds_mon_dk = [];
		return $ds_mon_dk;
	}
}

function ds_mon_dk($userid, $dot_mo_mon_id, $hoc_phi_1_tin, $nganhid) {
	global $DB;
	$sql = "SELECT {th_dot_mo_has_mon_hoc}.mon_hoc_id FROM {th_dang_ky_mon_hoc},{th_dot_mo_has_mon_hoc} WHERE {th_dang_ky_mon_hoc}.userid_moodle = '$userid'
    AND {th_dot_mo_has_mon_hoc}.dot_mo_mon_id = '$dot_mo_mon_id' AND {th_dot_mo_has_mon_hoc}.id = {th_dang_ky_mon_hoc}.dot_mo_has_mon_hoc_id AND {th_dang_ky_mon_hoc}.id_nganh = '$nganhid'";
	$ds_mon = $DB->get_records_sql($sql);

	$table = new html_table();
	$table->head = array(
		get_string('no.', 'local_th_register'), 
		get_string('ten_mon', 'local_th_register'), 
		get_string('ma_mon', 'local_th_register'), 
		get_string('batch_opening', 'local_th_register'), 
		get_string('hoc_phi_mot_tin_chi', 'local_th_register'),
		get_string('so_tin_chi', 'local_th_register'), 
		get_string('tuition_fee', 'local_th_register'),
		get_string('cancel_registration', 'local_th_register') . "<div><input type='checkbox' id='select-all1'></div>"
	);
	$stt = 0;
	$so_tin_chi = 0;
	foreach ($ds_mon as $k => $mon) {
		$mon_hoc_id = $mon->mon_hoc_id;
		$mon_hoc = $DB->get_record_sql("SELECT * FROM {th_mon_hoc} WHERE id = '$mon_hoc_id'");

		$stt = $stt + 1;
		$so_tin_chi = $so_tin_chi + $mon_hoc->so_tin_chi;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell("<strong>$mon_hoc->ten_mon</strong>");
		$cell->style = 'text-align: left;';
		$row->cells[] = $cell;
		$cell = new html_table_cell($mon_hoc->ma_mon);
		$row->cells[] = $cell;
		$cell = new html_table_cell(ten_dot_mo($dot_mo_mon_id));
		$row->cells[] = $cell;
		$cell = new html_table_cell(number_format($hoc_phi_1_tin, 0, ',', '.'));
		$row->cells[] = $cell;
		$cell = new html_table_cell($mon_hoc->so_tin_chi);
		$row->cells[] = $cell;
		$cell = new html_table_cell(number_format($mon_hoc->so_tin_chi * $hoc_phi_1_tin, 0, ',', '.'));
		$row->cells[] = $cell;
		$dot_mo_has_mon_hoc_id = $DB->get_field_sql("SELECT id FROM {th_dot_mo_has_mon_hoc} WHERE dot_mo_mon_id = '$dot_mo_mon_id' AND mon_hoc_id = '$mon_hoc_id'");
		$mon_dang_ky_id = $DB->get_field_sql("SELECT id FROM {th_dang_ky_mon_hoc} WHERE userid_moodle = '$userid' AND dot_mo_has_mon_hoc_id = '$dot_mo_has_mon_hoc_id'");
		$cell = new html_table_cell("<input type='checkbox' class='check_box1' name='mon_huy[]' value='$mon_dang_ky_id'>");
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$row = new html_table_row();
	$cell = new html_table_cell("<strong>" . get_string('total', 'local_th_register') . "</strong>");
	$cell->colspan = 5;
	$row->cells[] = $cell;
	$cell = new html_table_cell("<strong>$so_tin_chi</strong>");
	$row->cells[] = $cell;
	$tong_tien = number_format($so_tin_chi * $hoc_phi_1_tin, 0, ',', '.');
	$cell = new html_table_cell("<strong>$tong_tien</strong>");
	$row->cells[] = $cell;
	$cell = new html_table_cell("<input class='huydangky' type='submit' id='huydangky' value=".get_string('cancel_registration', 'local_th_register')." name='huy'>");
	$row->cells[] = $cell;
	$table->data[] = $row;

	$table->attributes = array('class' => 'th_table_dk', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align: center; background: azure; color: #000";
	$html = html_writer::table($table);
	return $html;
}

function ds_mon_dk_all($userid) {
	global $DB;
	$sql = "SELECT dhm.id, dhm.mon_hoc_id, dhm.dot_mo_mon_id, dk.id_nganh, nganh.hoc_phi_mot_tin_chi FROM {th_dang_ky_mon_hoc} as dk,{th_dot_mo_has_mon_hoc} as dhm, {th_nganh_hoc} as nganh WHERE dk.userid_moodle = '$userid'
    AND dhm.id = dk.dot_mo_has_mon_hoc_id AND dk.id_nganh = nganh.id ORDER BY dk.id_nganh";
	$ds_mon = $DB->get_records_sql($sql);

	$table = new html_table();
	$table->head = array(
		get_string('no.', 'local_th_register'), 
		get_string('ten_mon', 'local_th_register'), 
		get_string('ma_mon', 'local_th_register'), 
		get_string('batch_opening', 'local_th_register'), 
		get_string('majors', 'local_th_register'),
		get_string('so_tin_chi', 'local_th_register'),
		get_string('tuition_fee', 'local_th_register')
	);
	$stt = 0;
	$so_tin_chi = 0;
	$tong_tien = 0;
	if (!empty($ds_mon)) {
		foreach ($ds_mon as $k => $mon) {
			$mon_hoc_id = $mon->mon_hoc_id;
			$dot_mo_mon_id = $mon->dot_mo_mon_id;
			$dot_mo = $DB->get_record_sql("SELECT * FROM {th_dot_mo_mon} WHERE id = '$dot_mo_mon_id'");
			$hoc_phi_1_tin = $mon->hoc_phi_mot_tin_chi;
			$mon_hoc = $DB->get_record_sql("SELECT * FROM {th_mon_hoc} WHERE id = '$mon_hoc_id'");

			$stt = $stt + 1;
			$so_tin_chi = $so_tin_chi + $mon_hoc->so_tin_chi;
			$tong_tien = $tong_tien + $mon_hoc->so_tin_chi * $hoc_phi_1_tin;
			$row = new html_table_row();
			$cell = new html_table_cell($stt);
			$row->cells[] = $cell;
			$cell = new html_table_cell("<strong>$mon_hoc->ten_mon</strong>");
			$cell->style = 'text-align: left;';
			$row->cells[] = $cell;
			$cell = new html_table_cell($mon_hoc->ma_mon);
			$row->cells[] = $cell;
			$cell = new html_table_cell(ten_dot_mo($dot_mo_mon_id));
			$row->cells[] = $cell;
			$cell = new html_table_cell(ten_nganh($mon->id_nganh));
			$row->cells[] = $cell;
			$cell = new html_table_cell($mon_hoc->so_tin_chi);
			$row->cells[] = $cell;
			$cell = new html_table_cell(number_format($mon_hoc->so_tin_chi * $hoc_phi_1_tin, 0, ',', '.'));
			$row->cells[] = $cell;
			$table->data[] = $row;
		}
		$row = new html_table_row();
		$cell = new html_table_cell("<strong>" . get_string('total', 'local_th_register') . "</strong>");
		$cell->colspan = 4;
		$row->cells[] = $cell;
		$cell = new html_table_cell("<strong>$so_tin_chi</strong>");
		$row->cells[] = $cell;
		$tong_tien = number_format($tong_tien, 0, ',', '.');
		$cell = new html_table_cell("<strong>$tong_tien</strong>");
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$row->cells[] = $cell;
		$table->data[] = $row;

		$table->attributes = array('class' => 'th_table_dk_all', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align: center; color: #000";
		$html = html_writer::table($table);
		return [$html, $so_tin_chi, $tong_tien];
	} else {
		$table = new html_table();
		$table->head = array(
			get_string('no.', 'local_th_register'), 
			get_string('ten_mon', 'local_th_register'), 
			get_string('ma_mon', 'local_th_register'), 
			get_string('batch_opening', 'local_th_register'), 
			get_string('majors', 'local_th_register'),
			get_string('so_tin_chi', 'local_th_register'),
			get_string('tuition_fee', 'local_th_register')
		);
		$row = new html_table_row();
		$cell = new html_table_cell("<strong>" . get_string('no_registered_courses', 'local_th_register') . "</strong>");
		$cell->colspan = 7;
		$row->cells[] = $cell;
		$table->data[] = $row;
		$stt = 0;
		$so_tin_chi = 0;
		$tong_tien = 0;
		$table->attributes = array('class' => 'th_table_dk_all', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align: center; color: #000";
		$html = html_writer::table($table);
		return [$html, $so_tin_chi, $tong_tien];
	}
}
