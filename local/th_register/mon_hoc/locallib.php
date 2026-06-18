<?php

defined('MOODLE_INTERNAL') || die();

function th_check_add_course($list_courses)
{
	global $DB, $USER;

	$checkedcourses                     = new stdClass();
	$checkedcourses->error_messages     = array();
	$checkedcourses->courses            = array();
	$checkedcourses->valid_course_found = 0;

	if (!empty($list_courses)) {

		foreach ($list_courses as $k => $list_course) {
			$line           = $k + 2;

			if(substr_count($list_course, ',') == 2){
				$listcourse_arr = explode(",", $list_course);
				$fullname    = trim($listcourse_arr[0]);
				$ma_mon         = trim($listcourse_arr[1]);
				$tin_chi        = trim($listcourse_arr[2]);
			} else if (substr_count($list_course, ',') == 3) {
					$listcourse_arr = explode(",", $list_course);
					$name   = trim($listcourse_arr[0]). ', ' . trim($listcourse_arr[1]);
					$fullname = str_replace('"', '', $name);
					$ma_mon         = trim($listcourse_arr[2]);
					$tin_chi        = trim($listcourse_arr[3]);
			}

			$sql    = "SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon'";
			$course = $DB->get_record_sql($sql);

			if (strlen($fullname) > 500 || strlen($ma_mon) > 10 || strlen($tin_chi) > 10 || is_numeric($tin_chi) != 1 || $tin_chi > 10) {
				$checkedcourses->error_messages[] = get_string('error_invalid_data', 'local_th_register', ['line' => $line]);
			} else {
				if (empty($course)) {
					$checkedcourses->valid_course_found += 1;
					$data                      = new stdClass();
					$data->ma_mon              = $ma_mon;
					$data->ten_mon             = $fullname;
					$data->so_tin_chi          = $tin_chi;
					$data->ngay_tao            = time();
					$data->nguoi_tao           = $USER->id;
					$data->ngay_sua            = null;
					$data->nguoi_sua           = null;
					$checkedcourses->courses[] = $data;
				} else {
					$checkedcourses->error_messages[] = get_string('error_course_exists', 'local_th_register', [
						'fullname' => $fullname,
						'ma_mon'   => $ma_mon,
						'line'     => $line
					]);
					
				}
			}
		}
	}
	return $checkedcourses;
}

function th_check_add_course_has_nganh($list_courses)
{
	global $DB, $USER;

	$checkedcourses                     = new stdClass();
	$checkedcourses->error_messages     = array();
	$checkedcourses->courses_has_nganh            = array();
	$checkedcourses->valid_course_found = 0;

	if (!empty($list_courses)) {
		foreach ($list_courses as $k => $list_course) {
			$line           = $k + 2;
			$listcourse_arr = explode(",", $list_course);
			$ma_mon         = trim($listcourse_arr[0]);
			$ds_ma_nganh    = trim($listcourse_arr[1]);
			$arr_ma_nganh = explode(";", $ds_ma_nganh);

			foreach ($arr_ma_nganh as $ma_nganh) {
				$sql    = "SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon'";
				$course = $DB->get_record_sql($sql);
				$ma_nganh = trim($ma_nganh);

				if (strlen($ma_mon) > 10 || strlen($ma_nganh) > 10) {
					$checkedcourses->error_messages[] = get_string('error_invalid_data1', 'local_th_register', ['line' => $line]);
				} else {
					if (empty($course)) {
						$checkedcourses->error_messages[] = get_string('error_course_not_found', 'local_th_register', ['ma_mon' => $ma_mon, 'line' => $line]);
					} else {
						$nganh = $DB->get_record_sql("SELECT * FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh'");
						if (empty($nganh)) {
							$checkedcourses->error_messages[] = get_string('error_major_not_found', 'local_th_register', ['ma_nganh' => $ma_nganh, 'line' => $line]);
						} else {
							$mon_has_nganh = $DB->get_record_sql("SELECT * FROM {th_mon_hoc_has_nganh_hoc} WHERE mon_hoc_id = '$course->id' AND nganh_hoc_id = '$nganh->id'");
							if (empty($mon_has_nganh)) {
								$checkedcourses->valid_course_found += 1;
								$data                      = new stdClass();
								$data->ma_mon              = $ma_mon;
								$data->ten_mon             = $course->ten_mon;
								$data->mon_hoc_id          = $course->id;
								$data->ma_nganh            = $ma_nganh;
								$data->ten_nganh           = $nganh->ten_nganh;
								$data->nganh_hoc_id        = $nganh->id;
								$data->ngay_tao            = time();
								$data->nguoi_tao           = $USER->id;
								$data->ngay_sua            = null;
								$data->nguoi_sua           = null;
								$checkedcourses->courses_has_nganh[] = $data;
							} else {
								$checkedcourses->error_messages[] = get_string('error_course_exists1', 'local_th_register', [
									'ten_mon'  => $course->ten_mon,
									'ma_mon'   => $ma_mon,
									'ten_nganh'=> $nganh->ten_nganh,
									'ma_nganh' => $ma_nganh,
									'line'     => $line
								]);								
							}
						}
					}
				}
			}
		}
	}
	return $checkedcourses;
}

function th_check_add_course_has_doi_tuong($list_courses)
{
	global $DB, $USER;

	$checkedcourses                     = new stdClass();
	$checkedcourses->error_messages     = array();
	$checkedcourses->courses_has_doi_tuong = array();
	$checkedcourses->valid_course_found = 0;

	if (!empty($list_courses)) {
		foreach ($list_courses as $k => $list_course) {
			$line           = $k + 2;
			$listcourse_arr = explode(",", $list_course);
			$ma_mon         = trim($listcourse_arr[0]);
			$ds_ma_doi_tuong    = trim($listcourse_arr[1]);
			$arr_ma_doi_tuong = explode(";", $ds_ma_doi_tuong);

			foreach ($arr_ma_doi_tuong as $ma_doi_tuong) {
				$sql    = "SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon'";
				$course = $DB->get_record_sql($sql);
				$ma_doi_tuong = trim($ma_doi_tuong);

				if (strlen($ma_mon) > 10 || strlen($ma_doi_tuong) > 10) {
					$checkedcourses->error_messages[] = get_string('error_invalid_data1', 'local_th_register', ['line' => $line]);
				} else {
					if (empty($course)) {
						$checkedcourses->error_messages[] = get_string('error_course_not_found', 'local_th_register', ['ma_mon' => $ma_mon, 'line' => $line]);
					} else {
						$doi_tuong = $DB->get_record_sql("SELECT * FROM {th_doi_tuong} WHERE ma_doi_tuong = '$ma_doi_tuong'");
						if (empty($doi_tuong)) {
							$checkedcourses->error_messages[] = get_string('error_object_not_found', 'local_th_register', [
								'ma_doi_tuong' => $ma_doi_tuong,
								'line'         => $line
							]);
							
						} else {
							$mon_has_doi_tuong = $DB->get_record_sql("SELECT * FROM {th_mon_hoc_has_doi_tuong} WHERE mon_hoc_id = '$course->id' AND doi_tuong_id = '$doi_tuong->id'");
							if (empty($mon_has_doi_tuong)) {
								$checkedcourses->valid_course_found += 1;
								$data                      = new stdClass();
								$data->ma_mon              = $ma_mon;
								$data->ten_mon             = $course->ten_mon;
								$data->mon_hoc_id          = $course->id;
								$data->ma_doi_tuong        = $ma_doi_tuong;
								$data->ten_doi_tuong       = $doi_tuong->ten_doi_tuong;
								$data->doi_tuong_id        = $doi_tuong->id;
								$data->ngay_tao            = time();
								$data->nguoi_tao           = $USER->id;
								$data->ngay_sua            = null;
								$data->nguoi_sua           = null;
								$checkedcourses->courses_has_doi_tuong[] = $data;
							} else {
								$checkedcourses->error_messages[] = get_string('error_course_target_exists', 'local_th_register', [
									'ten_mon'       => $course->ten_mon,
									'ma_mon'        => $ma_mon,
									'ten_doi_tuong' => $doi_tuong->ten_doi_tuong,
									'ma_doi_tuong'  => $ma_doi_tuong,
									'line'          => $line
								]);								
							}
						}
					}
				}
			}
		}
	}
	return $checkedcourses;
}

function th_display_table_upload_course($courses)
{
	global $DB;
	$table       = new html_table();
	$table->head = array(
		get_string('no.', 'local_th_register'),
		get_string('ten_mon', 'local_th_register'),
		get_string('ma_mon', 'local_th_register'),
		get_string('so_tin_chi', 'local_th_register'), 
		get_string('status', 'local_th_register'),
		);
	$stt         = 0;
	foreach ($courses as $k => $course) {
		$stt          = $stt + 1;
		$row          = new html_table_row();
		$cell         = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course->ten_mon);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course->ma_mon);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course->so_tin_chi);
		$row->cells[] = $cell;
		$cell         = new html_table_cell();
		$cell->text   = html_writer::tag(
			'span',
			get_string('course_will_be_added', 'local_th_register'),
			array('class' => 'badge badge-success')
		);
		$row->cells[]  = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_upload_course_has_nganh($courses_has_nganh)
{
	$table       = new html_table();
	$table->head = array(
	get_string('no.', 'local_th_register'),
	get_string('ten_mon', 'local_th_register'),
	get_string('ma_mon', 'local_th_register'),
	get_string('ma_nganh', 'local_th_register'), 
	get_string('ten_nganh', 'local_th_register'),
	get_string('status', 'local_th_register')
);
	$stt         = 0;
	foreach ($courses_has_nganh as $k => $course_has_nganh) {
		$stt          = $stt + 1;
		$row          = new html_table_row();
		$cell         = new html_table_cell($stt);
		$row->cells[] = $cell;

		$cell         = new html_table_cell($course_has_nganh->ten_mon);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course_has_nganh->ma_mon);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course_has_nganh->ma_nganh);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course_has_nganh->ten_nganh);
		$row->cells[] = $cell;
		$cell         = new html_table_cell();
		$cell->text   = html_writer::tag(
			'span',
			get_string('course_will_be_added_to_major', 'local_th_register'),
			array('class' => 'badge badge-success')
		);
		$row->cells[]  = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_upload_course_has_doi_tuong($courses_has_doi_tuong)
{
	$table       = new html_table();
	$table->head = array(
		get_string('no.', 'local_th_register'), 
		get_string('ten_mon', 'local_th_register'), 
		get_string('ma_mon', 'local_th_register'), 
		get_string('ma_doi_tuong', 'local_th_register'),
		get_string('ten_doi_tuong', 'local_th_register'),
		get_string('status', 'local_th_register')
	);
	$stt         = 0;
	foreach ($courses_has_doi_tuong as $course_has_doi_tuong) {
		$stt          = $stt + 1;
		$row          = new html_table_row();
		$cell         = new html_table_cell($stt);
		$row->cells[] = $cell;

		$cell         = new html_table_cell($course_has_doi_tuong->ten_mon);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course_has_doi_tuong->ma_mon);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course_has_doi_tuong->ma_doi_tuong);
		$row->cells[] = $cell;
		$cell         = new html_table_cell($course_has_doi_tuong->ten_doi_tuong);
		$row->cells[] = $cell;
		$cell         = new html_table_cell();
		$cell->text   = html_writer::tag(
			'span',
			get_string('doi_tuong_duoc_them', 'local_th_register'),
			array('class' => 'badge badge-success')
		);
		$row->cells[]  = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error($errors)
{
	$table1       = new html_table($errors);
	$table1->head = array(get_string('no.', 'local_th_register'), get_string('hints', 'local_th_register'));
	$stt          = 0;
	foreach ($errors as $k => $error) {
		$stt            = $stt + 1;
		$row            = new html_table_row();
		$cell           = new html_table_cell($stt);
		$row->cells[]   = $cell;
		$cell           = new html_table_cell($error);
		$row->cells[]   = $cell;
		$table1->data[] = $row;
	}
	$html1 = html_writer::table($table1);
	return $html1;
}

function th_ds_nganh()
{
	global $DB;
	$ds       = [];
	$sql      = "SELECT * FROM {th_nganh_hoc}";
	$ds_nganh = $DB->get_records_sql($sql);
	if (!empty($ds_nganh)) {
		foreach ($ds_nganh as $id => $nganh) {
			$ds[$id] = $nganh->ten_nganh . ',' . $nganh->ma_nganh;
		}
	}
	return $ds;
}

function local_th_register_course_controls(context $context, moodle_url $currenturl)
{
	$tabs       = array();
	$currenttab = 'view';
	$view       = new moodle_url('/local/th_register/mon_hoc/index.php');

	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/mon_hoc/index.php');
		$tabs[] = new tabobject('view', $addurl,  get_string('tab_all', 'local_th_register'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view';
		}
	}
	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/mon_hoc/edit.php');
		$tabs[] = new tabobject('edit', $addurl, get_string('tab_add', 'local_th_register'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'edit';
		}
	}
	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/mon_hoc/th_upload_course.php');
		$tabs[] = new tabobject('add', $addurl, get_string('tab_add_bulk', 'local_th_register'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'add';
		}
	}
	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/mon_hoc/th_upload_course_has_nganh.php');
		$tabs[] = new tabobject('add_mon_has_nganh', $addurl, get_string('tab_add_to_nganh', 'local_th_register'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'add_mon_has_nganh';
		}
	}
	if (has_capability('local/th_register:view', $context)) {
		$addurl = new moodle_url('/local/th_register/mon_hoc/th_upload_course_has_doi_tuong.php');
		$tabs[] = new tabobject('add_mon_has_doi_tuong', $addurl, get_string('tab_add_to_doi_tuong', 'local_th_register'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'add_mon_has_doi_tuong';
		}
	}

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

function get_full_name($id)
{
	global $DB;
	$sql       = "SELECT firstname FROM {user} WHERE id = $id";
	$firstname = $DB->get_field_sql($sql);
	$sql1      = "SELECT lastname FROM {user} WHERE id = $id";
	$lastname  = $DB->get_field_sql($sql1);
	$fullname  = $firstname . ' ' . $lastname;
	return $fullname;
}

function upload_add_course($courses)
{
	global $DB;
	foreach ($courses as $course) {
		$ma_mon       = trim($course->ma_mon);
		$dataobjects             = new stdClass();
		$dataobjects->ma_mon     = $ma_mon;
		$dataobjects->ten_mon    = $course->ten_mon;
		$dataobjects->so_tin_chi = $course->so_tin_chi;
		$dataobjects->ngay_tao   = $course->ngay_tao;
		$dataobjects->nguoi_tao  = $course->nguoi_tao;
		$dataobjects->ngay_sua   = $course->ngay_sua;
		$dataobjects->nguoi_sua  = $course->nguoi_sua;

		$sql     = "SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon'";
		$mon_hoc = $DB->get_record_sql($sql);

		if (empty($mon_hoc)) {
			$DB->insert_record('th_mon_hoc', $dataobjects, false);
		}
	}
}

function upload_course_has_nganh($courses_has_nganh)
{
	global $DB;
	foreach ($courses_has_nganh as $course_has_nganh) {
		$mon_hoc_id = $course_has_nganh->mon_hoc_id;
		$nganh_hoc_id = $course_has_nganh->nganh_hoc_id;
		$dataobjects             = new stdClass();
		$dataobjects->mon_hoc_id = $course_has_nganh->mon_hoc_id;
		$dataobjects->nganh_hoc_id = $course_has_nganh->nganh_hoc_id;
		$dataobjects->ngay_tao   = $course_has_nganh->ngay_tao;
		$dataobjects->nguoi_tao  = $course_has_nganh->nguoi_tao;
		$dataobjects->ngay_sua   = $course_has_nganh->ngay_sua;
		$dataobjects->nguoi_sua  = $course_has_nganh->nguoi_sua;

		$mon_has_nganh = $DB->get_record_sql("SELECT * FROM {th_mon_hoc_has_nganh_hoc} WHERE mon_hoc_id = '$mon_hoc_id' AND nganh_hoc_id = '$nganh_hoc_id'");

		if (empty($mon_has_nganh)) {
			$DB->insert_record('th_mon_hoc_has_nganh_hoc', $dataobjects, false);
		}
	}
}

function upload_course_has_doi_tuong($courses_has_doi_tuong)
{
	global $DB;
	foreach ($courses_has_doi_tuong as $course_has_doi_tuong) {
		$mon_hoc_id = $course_has_doi_tuong->mon_hoc_id;
		$doi_tuong_id = $course_has_doi_tuong->doi_tuong_id;
		$dataobjects             = new stdClass();
		$dataobjects->mon_hoc_id = $mon_hoc_id;
		$dataobjects->doi_tuong_id = $doi_tuong_id;
		$dataobjects->ngay_tao   = $course_has_doi_tuong->ngay_tao;
		$dataobjects->nguoi_tao  = $course_has_doi_tuong->nguoi_tao;
		$dataobjects->ngay_sua   = $course_has_doi_tuong->ngay_sua;
		$dataobjects->nguoi_sua  = $course_has_doi_tuong->nguoi_sua;

		$mon_has_doi_tuong = $DB->get_record_sql("SELECT * FROM {th_mon_hoc_has_doi_tuong} WHERE mon_hoc_id = '$mon_hoc_id' AND doi_tuong_id = '$doi_tuong_id'");

		if (empty($mon_has_doi_tuong)) {
			$DB->insert_record('th_mon_hoc_has_doi_tuong', $dataobjects, false);
		}
	}
}

function ds_doi_tuong()
{
	global $DB;
	$sql          = "SELECT * FROM {th_doi_tuong}";
	$ds_doi_tuong = $DB->get_records_sql($sql);

	if (!empty($ds_doi_tuong)) {
		foreach ($ds_doi_tuong as $id => $doi_tuong) {
			$ds[$id] = $doi_tuong->ten_doi_tuong . ',' . $doi_tuong->ma_doi_tuong;
		}
		return $ds;
	}
}
