<?php
function delete_mon_has_nganh($mon_hoc_id) {
	global $DB;
	$nganh_hoc_arr = $DB->get_records_sql("SELECT id FROM {th_mon_hoc_has_nganh_hoc} WHERE mon_hoc_id = '$mon_hoc_id'");

	if (!empty($nganh_hoc_arr)) {
		foreach ($nganh_hoc_arr as $k => $nganh_hoc) {
			$DB->delete_records('th_mon_hoc_has_nganh_hoc', ['id' => $nganh_hoc->id]);
		}
	}
}

function delete_mon_has_dot($mon_hoc_id) {
	global $DB;
	$mon_has_dot_arr = $DB->get_records_sql("SELECT id FROM {th_dot_mo_has_mon_hoc} WHERE mon_hoc_id = '$mon_hoc_id'");

	if (!empty($mon_has_dot_arr)) {
		foreach ($mon_has_dot_arr as $k => $mon_has_dot) {
			$DB->delete_records('th_dot_mo_has_mon_hoc', ['id' => $mon_has_dot->id]);
		}
	}
}

function delete_mon_has_doi_tuong($mon_hoc_id) {
	global $DB;
	$mon_has_doi_tuong_arr = $DB->get_records_sql("SELECT id FROM {th_mon_hoc_has_doi_tuong} WHERE mon_hoc_id = '$mon_hoc_id'");

	if (!empty($mon_has_doi_tuong_arr)) {
		foreach ($mon_has_doi_tuong_arr as $k => $doi_tuong) {
			$DB->delete_records('th_mon_hoc_has_doi_tuong', ['id' => $doi_tuong->id]);
		}
	}
}

function delete_dang_ky_mon_hoc($mon_hoc_id) {
	global $DB;
	$mon_has_dot_arr = $DB->get_records_sql("SELECT id FROM {th_dot_mo_has_mon_hoc} WHERE mon_hoc_id = '$mon_hoc_id'");

	if (!empty($mon_has_dot_arr)) {
		foreach ($mon_has_dot_arr as $k => $mon_has_dot) {
			$DB->delete_records('th_dang_ky_mon_hoc', ['dot_mo_has_mon_hoc_id' => $mon_has_dot->id]);
		}
	}
}

function update_mon_has_nganh($mon_hoc_id, $nganh_hoc_edit) {
	global $DB, $USER;
	$nganh_hoc_arr = $DB->get_records_sql("SELECT nganh_hoc_id FROM {th_mon_hoc_has_nganh_hoc} WHERE mon_hoc_id = '$mon_hoc_id'");

	if (!empty($nganh_hoc_arr)) {
		foreach ($nganh_hoc_arr as $k => $nganh_hoc) {
			$ma_nganh_id_arr[] = $nganh_hoc->nganh_hoc_id;
		}

		foreach ($ma_nganh_id_arr as $k => $ma_nganh_id1) {
			if (!in_array($ma_nganh_id1, $nganh_hoc_edit)) {
				$id_nganh = $DB->get_field_sql("SELECT * FROM {th_mon_hoc_has_nganh_hoc} WHERE mon_hoc_id = '$mon_hoc_id' AND nganh_hoc_id = '$ma_nganh_id1'");
				$DB->delete_records('th_mon_hoc_has_nganh_hoc', ['id' => $id_nganh]);
			}
		}

		foreach ($nganh_hoc_edit as $k => $nganh_hoc_edit1) {
			if (!in_array($nganh_hoc_edit1, $ma_nganh_id_arr)) {
				$dataobjects1 = new stdClass();
				$dataobjects1->mon_hoc_id = $mon_hoc_id;
				$dataobjects1->nganh_hoc_id = $nganh_hoc_edit1;
				$dataobjects1->ngay_tao = time();
				$dataobjects1->nguoi_tao = $USER->id;
				$dataobjects1->ngay_sua = null;
				$dataobjects1->nguoi_sua = null;
				$DB->insert_record('th_mon_hoc_has_nganh_hoc', $dataobjects1, false);
			}
		}
	} else {
		foreach ($nganh_hoc_edit as $k => $nganh_hoc_edit1) {
			$dataobjects1 = new stdClass();
			$dataobjects1->mon_hoc_id = $mon_hoc_id;
			$dataobjects1->nganh_hoc_id = $nganh_hoc_edit1;
			$dataobjects1->ngay_tao = time();
			$dataobjects1->nguoi_tao = $USER->id;
			$dataobjects1->ngay_sua = null;
			$dataobjects1->nguoi_sua = null;
			$DB->insert_record('th_mon_hoc_has_nganh_hoc', $dataobjects1, false);

		}
	}

}

function update_mon_has_doi_tuong($mon_hoc_id, $doi_tuong_edit) {
	global $DB, $USER;
	$mon_has_doi_tuong_arr = $DB->get_records_sql("SELECT doi_tuong_id FROM {th_mon_hoc_has_doi_tuong} WHERE mon_hoc_id = '$mon_hoc_id'");

	if (!empty($mon_has_doi_tuong_arr)) {
		foreach ($mon_has_doi_tuong_arr as $k => $doi_tuong) {
			$doi_tuong_id_arr[] = $doi_tuong->doi_tuong_id;
		}

		foreach ($doi_tuong_id_arr as $k => $doi_tuong_id1) {
			if (!in_array($doi_tuong_id1, $doi_tuong_edit)) {
				$id_doi_tuong = $DB->get_field_sql("SELECT * FROM {th_mon_hoc_has_doi_tuong} WHERE mon_hoc_id = '$mon_hoc_id' AND doi_tuong_id = '$doi_tuong_id1'");
				$DB->delete_records('th_mon_hoc_has_doi_tuong', ['id' => $id_doi_tuong]);
			}
		}

		foreach ($doi_tuong_edit as $k => $doi_tuong_edit1) {
			if (!in_array($doi_tuong_edit1, $doi_tuong_id_arr)) {
				$dataobjects2 = new stdClass();
				$dataobjects2->mon_hoc_id = $mon_hoc_id;
				$dataobjects2->doi_tuong_id = $doi_tuong_edit1;
				$dataobjects2->ngay_tao = time();
				$dataobjects2->nguoi_tao = $USER->id;
				$dataobjects2->ngay_sua = null;
				$dataobjects2->nguoi_sua = null;
				$DB->insert_record('th_mon_hoc_has_doi_tuong', $dataobjects2, false);
			}
		}
	} else {
		foreach ($doi_tuong_edit as $k => $doi_tuong_edit1) {
			$dataobjects2 = new stdClass();
			$dataobjects2->mon_hoc_id = $mon_hoc_id;
			$dataobjects2->doi_tuong_id = $doi_tuong_edit1;
			$dataobjects2->ngay_tao = time();
			$dataobjects2->nguoi_tao = $USER->id;
			$dataobjects2->ngay_sua = null;
			$dataobjects2->nguoi_sua = null;
			$DB->insert_record('th_mon_hoc_has_doi_tuong', $dataobjects2, false);
		}
	}

}
