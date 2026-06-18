<?php

require_once "$CFG->libdir/externallib.php";
require_once "lib.php";
require_once $CFG->dirroot . "/course/externallib.php";

class local_th_tnuapi_external extends external_api {

	public static function xet_dkdt_parameters() {
		return new external_function_parameters(
			array(
				'ma_mon' => new external_value(PARAM_RAW, 'string, mã môn học'),
				'dshv' => new external_multiple_structure(
					new external_value(PARAM_RAW, 'string, mã học viên'), 'array mã học viên'
				),
			)
		);
	}

	public static function xet_dkdt_returns() {

		return new external_single_structure(
			array(
				'ma_mon' => new external_value(PARAM_RAW, 'string, mã môn học', VALUE_OPTIONAL),
				'ketqua' => new external_multiple_structure(
					new external_single_structure(
						array(
							'mhv' => new external_value(PARAM_RAW, 'mã học viên', VALUE_OPTIONAL),
							'dcc' => new external_value(PARAM_RAW, 'điểm chuyên cần', VALUE_OPTIONAL),
							'dkt' => new external_value(PARAM_RAW, 'điểm kiểm tra', VALUE_OPTIONAL),
							'kt1' => new external_value(PARAM_RAW, 'điểm kiểm tra 1', VALUE_OPTIONAL),
							'kt2' => new external_value(PARAM_RAW, 'điểm kiểm tra 2', VALUE_OPTIONAL),
							'xetdkdt' => new external_value(PARAM_FLOAT, 'xét điều kiện dự thi', VALUE_OPTIONAL),
							'courseshortname' => new external_value(PARAM_RAW, 'Mã khóa học', VALUE_OPTIONAL),
						),
						'array grades information',
						VALUE_OPTIONAL
					)
				),
			),
			'',
			VALUE_OPTIONAL
		);
	}

	public static function xet_dkdt($ma_mon, $dshv) {
		global $DB;

		$context = context_system::instance();
		require_capability('local/th_tnuapi:seeallthings', $context);

		$result = new stdClass();
		$result->ma_mon = $ma_mon;
		$result->ketqua = [];

		if (count($dshv) > 0) {
			list($insql, $inparams) = $DB->get_in_or_equal($dshv);
		} else {
			list($insql, $inparams) = $DB->get_in_or_equal(['']);
		}

		$config = get_config('local_thlib');
		$studentcode = trim($config->studentcodeshortname);
		$studentcode = explode(',', $studentcode);

		if (count($studentcode) > 0) {
			list($insql2, $inparams2) = $DB->get_in_or_equal($studentcode);
		} else {
			list($insql2, $inparams2) = $DB->get_in_or_equal(['']);
		}

		$user_params = array_merge($inparams2, $inparams);

		$sql = "SELECT {user_info_data}.userid, {user_info_data}.data
				FROM {user_info_field}
				INNER JOIN {user_info_data}
				ON {user_info_field}.id = {user_info_data}.fieldid 
				AND {user_info_field}.shortname $insql2
				AND {user_info_data}.data $insql";

		$records = $DB->get_records_sql($sql, $user_params);

		if (count($records) == 0) {
			return $result;
		}

		$userid_arr = [];
		$mhv_arr = [];

		foreach ($records as $key => $record) {
			$mhv_arr[$record->userid] = $record->data;
			$userid_arr[] = $record->userid;
		}

		if (count($userid_arr) > 0) {
			list($insql, $inparams) = $DB->get_in_or_equal($userid_arr, SQL_PARAMS_NAMED, 'u_id');
		} else {
			list($insql, $inparams) = $DB->get_in_or_equal([''], SQL_PARAMS_NAMED, 'u_id');
		}

		$DIC = ['cc' => 'dcc', 'kt' => 'dkt', 'ddk' => 'xetdkdt', 'kt.1' => 'kt1', 'kt.2' => 'kt2', 'kt1' => 'kt1', 'kt2' => 'kt2'];
		$array_idnumber = array_keys($DIC);

		if (count($array_idnumber) > 0) {
			list($insql2, $inparams2) = $DB->get_in_or_equal($array_idnumber, SQL_PARAMS_NAMED, 'id_num');
		} else {
			list($insql2, $inparams2) = $DB->get_in_or_equal([''], SQL_PARAMS_NAMED, 'id_num');
		}

		// Escape giá trị tìm kiếm tương đối
		$likeparam = '%' . $DB->sql_like_escape($ma_mon) . '%';

		$sql = "SELECT gg.id, c.shortname, gg.userid, gi.idnumber, gg.finalgrade
				FROM {grade_grades} gg
				JOIN {grade_items} gi ON gg.itemid = gi.id
				JOIN {course} c ON gi.courseid = c.id
				JOIN {enrol} e ON e.courseid = c.id
				JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = gg.userid
				WHERE gg.userid $insql
				AND " . $DB->sql_like('c.shortname', ':mamon', false) . "
				AND gi.idnumber $insql2
				AND ue.status = 0
				AND c.visible = 1";

		// Gộp tất cả các mảng tham số định danh (Named parameters) lại với nhau
		$grade_params = array_merge(
			$inparams,
			['mamon' => $likeparam],
			$inparams2
		);

		$grade_records = $DB->get_records_sql($sql, $grade_params);

		$ketqua = [];
		foreach ($grade_records as $key => $record) { 
			$userid = $record->userid;
			
			// Kiểm tra xem userid này có tồn tại trong mảng ánh xạ mã học viên không
			if (!isset($mhv_arr[$userid])) {
				continue;
			}
			
			$msv = $mhv_arr[$userid];
			$finalgrade = $record->finalgrade;
			$idnumber = $record->idnumber;

			if (!isset($DIC[$idnumber])) {
				continue;
			}

			$itemname = $DIC[$idnumber];

			if (array_key_exists($msv, $ketqua)) {
				$ob = $ketqua[$msv];
			} else {
				$ketqua[$msv] = new stdClass();
				$ob = $ketqua[$msv];
				$ob->mhv = $msv;
				$ob->dcc = -1;
				$ob->dkt = -1;
				$ob->kt1 = -1;
				$ob->kt2 = -1;
				$ob->xetdkdt = -1;
				$ob->courseshortname = $record->shortname;
			}

			if ($finalgrade !== null) {
				$ob->$itemname = $finalgrade;
			}
		}

		foreach ($ketqua as $key => $ob) {
			$ob->dcc = ($ob->dcc !== null && $ob->dcc != -1) ? number_format((float)$ob->dcc, 2, '.', '') : -1;
			$ob->dkt = ($ob->dkt !== null && $ob->dkt != -1) ? number_format((float)$ob->dkt, 2, '.', '') : -1;
			$ob->kt1 = ($ob->kt1 !== null && $ob->kt1 != -1) ? number_format((float)$ob->kt1, 2, '.', '') : -1;
			$ob->kt2 = ($ob->kt2 !== null && $ob->kt2 != -1) ? number_format((float)$ob->kt2, 2, '.', '') : -1;
			$ob->xetdkdt = ($ob->xetdkdt !== null && $ob->xetdkdt != -1) ? number_format((float)$ob->xetdkdt, 2, '.', '') : -1;
		}

		$result->ketqua = $ketqua;

		return $result;
	}

}
