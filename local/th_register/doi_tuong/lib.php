<?php
function local_th_register_doi_tuong_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'view';
	$view = new moodle_url('/local/th_register/doi_tuong/index.php');

	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/doi_tuong/index.php');
		$tabs[] = new tabobject('view', $addurl, get_string('all'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view';
		}
	}
	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/doi_tuong/edit.php');
		$tabs[] = new tabobject('edit', $addurl, get_string('add'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'edit';
		}
	}
	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/doi_tuong/add.php');
		$tabs[] = new tabobject('add', $addurl, get_string('them_doi_tuong_bulk', 'local_th_register'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'add';
		}
	}

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

function th_register_check_add_doi_tuong($lists) {
	global $DB, $USER, $CFG;

	$checked = new stdClass();
	$checked->error = array();
	$checked->add = array();
	$checked->valid_time_found = 0;

	if (!empty($lists)) {

		foreach ($lists as $k => $tr) {
			$line = $k + 2;
			$list = explode(",", $tr);
			$ma_doi_tuong = trim($list[0]);
			$ten_doi_tuong = trim($list[1]);
			if ($ma_doi_tuong == '') {
				$checked->error[] =  get_string('error_empty_object_code', 'local_th_register', ['code' => $ma_doi_tuong, 'line' => $line]);
				continue;
			}
			if ($ten_doi_tuong == '') {
				$checked->error[] =  get_string('error_empty_object_name', 'local_th_register', ['name' => $ten_doi_tuong, 'line' => $line]);
				continue;
			}
			if (strlen($ma_doi_tuong) > 10) {
				$checked->error[] = get_string('error_object_code_length', 'local_th_register', ['code' => $ma_doi_tuong, 'line' => $line]);
				continue;
			}
			if (strlen($ten_doi_tuong) > 45) {
				$checked->error[] = get_string('error_object_name_length', 'local_th_register', ['name' => $ten_doi_tuong, 'line' => $line]);
				continue;
			}

			$sql = "SELECT * FROM {th_doi_tuong} WHERE ma_doi_tuong = '$ma_doi_tuong'";
			$check = $DB->get_record_sql($sql);

			if (!empty($check)) {
				$checked->error[] =  get_string('error_object_exists', 'local_th_register', ['code' => $ma_doi_tuong, 'line' => $line]);
			} else {

				$checked->valid_time_found += 1;
				$record = new stdClass();
				$record->ma_doi_tuong = $ma_doi_tuong;
				$record->ten_doi_tuong = $ten_doi_tuong;
				$checked->add[] = $record;
			}
		}
	}
	return $checked;
}

function th_register_table_add_doi_tuong($data_override) {
	global $DB;
	$table = new html_table();
	$table->head = array(get_string('no.', 'local_th_register'),
		get_string('ma_doi_tuong', 'local_th_register'),
		get_string('ten_doi_tuong', 'local_th_register'),
		get_string('status'));
	$stt = 0;
	foreach ($data_override as $k => $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$cell = new html_table_cell($data->ma_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ten_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = html_writer::tag('span', get_string('se_duoc_them', 'local_th_register'),
			array('class' => 'badge badge-success'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_register_table_add_doi_tuong_error($errors) {
	$table1 = new html_table($errors);
	$table1->head = array(get_string('no.', 'local_th_register'), get_string('hints', 'local_th_register'));
	$stt = 0;
	foreach ($errors as $k => $error) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($error);
		$row->cells[] = $cell;
		$table1->data[] = $row;
	}
	$html1 = html_writer::table($table1);
	return $html1;
}

function th_register_check_update_doi_tuong($lists) {
	global $DB, $USER, $CFG;

	$checked = new stdClass();
	$checked->error = array();
	$checked->update = array();
	$checked->no_update = array();
	$checked->valid_time_found = 0;

	if (!empty($lists)) {

		foreach ($lists as $k => $tr) {
			$line = $k + 2;
			$list = explode(",", $tr);
			$ma_doi_tuong = trim($list[0]);
			$ten_doi_tuong = trim($list[1]);
			if ($ma_doi_tuong == '') {
				$checked->error[] = get_string('error_empty_object_code', 'local_th_register', ['code' => $ma_doi_tuong, 'line' => $line]);
				continue;
			}
			if ($ten_doi_tuong == '') {
				$checked->error[] = get_string('error_empty_object_name', 'local_th_register', ['name' => $ten_doi_tuong, 'line' => $line]);
				continue;
			}
			if (strlen($ma_doi_tuong) > 10) {
				$checked->error[] = get_string('error_object_code_length', 'local_th_register', ['code' => $ma_doi_tuong, 'line' => $line]);
				continue;
			}
			if (strlen($ten_doi_tuong) > 45) {
				$checked->error[] = get_string('error_object_name_length', 'local_th_register', ['name' => $ten_doi_tuong, 'line' => $line]);
				continue;
			}
			$sql = "SELECT * FROM {th_doi_tuong} WHERE ma_doi_tuong = '$ma_doi_tuong'";
			$check = $DB->get_record_sql($sql);

			if (empty($check)) {

				$checked->error[] = get_string('error_object_not_exist', 'local_th_register', [
					'name' => $ten_doi_tuong,
					'code' => $ma_doi_tuong,
					'line' => $line
				]);
				
			} else {
				$checked->valid_time_found += 1;
				$record = new stdClass();
				$record->id = $check->id;
				$record->ma_doi_tuong = $ma_doi_tuong;
				$record->ten_doi_tuong = $ten_doi_tuong;
				if ($DB->record_exists('th_doi_tuong', ['ma_doi_tuong' => $ma_doi_tuong, 'ten_doi_tuong' => $ten_doi_tuong])) {
					$checked->no_update[] = $record;
				} else {
					$checked->update[] = $record;
				}
			}
		}
	}
	return $checked;
}

function th_register_table_update_doi_tuong($update, $no_update) {
	global $DB;
	$table = new html_table();
	$table->head = array(get_string('no.', 'local_th_register'),
		get_string('ma_doi_tuong', 'local_th_register'),
		get_string('ten_doi_tuong', 'local_th_register'),
		get_string('status'));
	$stt = 1;
	foreach ($update as $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$cell = new html_table_cell($data->ma_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ten_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = html_writer::tag('span', get_string('se_duoc_cap_nhat', 'local_th_register'),
			array('class' => 'badge badge-success'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	foreach ($no_update as $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$cell = new html_table_cell($data->ma_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ten_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = html_writer::tag('span',
			get_string('khong_cap_nhat', 'local_th_register'),
			array('class' => 'badge badge-secondary'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_register_check_add_and_update_doi_tuong($lists) {
	global $DB, $USER, $CFG;

	$checked = new stdClass();
	$checked->add = array();
	$checked->update = array();
	$checked->no_update = array();
	$checked->valid_time_found = 0;

	if (!empty($lists)) {

		foreach ($lists as $k => $tr) {
			$line = $k + 2;
			$list = explode(",", $tr);
			$ma_doi_tuong = trim($list[0]);
			$ten_doi_tuong = trim($list[1]);
			if ($ma_doi_tuong == '') {
				$checked->error[] = get_string('error_empty_object_code', 'local_th_register', ['code' => $ma_doi_tuong, 'line' => $line]);
				continue;
			}
			if ($ten_doi_tuong == '') {
				$checked->error[] = get_string('error_empty_object_name', 'local_th_register', ['name' => $ten_doi_tuong, 'line' => $line]);
				continue;
			}
			if (strlen($ma_doi_tuong) > 10) {
				$checked->error[] = get_string('error_object_code_length', 'local_th_register', ['code' => $ma_doi_tuong, 'line' => $line]);
				continue;
			}
			if (strlen($ten_doi_tuong) > 45) {
				$checked->error[] = get_string('error_object_name_length', 'local_th_register', ['name' => $ten_doi_tuong, 'line' => $line]);
				continue;
			}
			$sql = "SELECT * FROM {th_doi_tuong} WHERE ma_doi_tuong = '$ma_doi_tuong'";
			$check = $DB->get_record_sql($sql);

			if (empty($check)) {
				$checked->valid_time_found += 1;
				$record = new stdClass();
				$record->ma_doi_tuong = $ma_doi_tuong;
				$record->ten_doi_tuong = $ten_doi_tuong;
				$checked->add[] = $record;

			} else {

				$checked->valid_time_found += 1;
				$record = new stdClass();
				$record->ma_doi_tuong = $ma_doi_tuong;
				$record->ten_doi_tuong = $ten_doi_tuong;
				if ($DB->record_exists('th_doi_tuong', ['ma_doi_tuong' => $ma_doi_tuong, 'ten_doi_tuong' => $ten_doi_tuong])) {
					$checked->no_update[] = $record;
				} else {
					$record->id = $check->id;
					$checked->update[] = $record;
				}
			}
		}
	}
	return $checked;
}

function th_register_table_add_and_update_doi_tuong($add, $update, $no_update) {
	global $DB;
	$table = new html_table();
	$table->head = array(get_string('no.', 'local_th_register'),
		get_string('ma_doi_tuong', 'local_th_register'),
		get_string('ten_doi_tuong', 'local_th_register'),
		get_string('status'));
	$stt = 0;
	foreach ($add as $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ma_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ten_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = html_writer::tag('span', get_string('se_duoc_them', 'local_th_register'),
			array('class' => 'badge badge-success'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	foreach ($update as $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ma_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ten_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = html_writer::tag('span', get_string('se_duoc_cap_nhat', 'local_th_register'),
			array('class' => 'badge badge-success'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	foreach ($no_update as $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ma_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->ten_doi_tuong);
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = html_writer::tag('span',
			get_string('khong_cap_nhat', 'local_th_register'),
			array('class' => 'badge badge-secondary'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}
