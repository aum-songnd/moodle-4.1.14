<?php

require_once "$CFG->libdir/externallib.php";
require_once "lib.php";
require_once $CFG->dirroot . "/course/externallib.php";

class local_th_mobile_service_external extends external_api {

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
							'dcc' => new external_value(PARAM_FLOAT, 'điểm chuyên cần', VALUE_OPTIONAL),
							'dkt' => new external_value(PARAM_FLOAT, 'điểm kiểm tra', VALUE_OPTIONAL),
							'xetdkdt' => new external_value(PARAM_FLOAT, 'xét điều kiện dự thi', VALUE_OPTIONAL),
							'courseshortname' => new external_value(PARAM_RAW, 'Mã khóa học', VALUE_OPTIONAL),
							// 'resultcode' => new external_value(PARAM_INT, '1: success, 0: cannot find student with provided mhv', VALUE_OPTIONAL),
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

		$params = array_merge($inparams2, $inparams);

		$sql = "SELECT userid, {user_info_data}.data
			from {user_info_field}
			inner join {user_info_data}
			on {user_info_field}.id = {user_info_data}.fieldid and {user_info_field}.shortname $insql2
			and {user_info_data}.data $insql";

		$records = $DB->get_records_sql($sql, $params);

		if (count($records) == 0) {
			return $result;
		}

		$userid_arr = [];
		$mhv_arr = [];

		foreach ($records as $key => $record) {
			$mhv_arr[$key] = $record->data;
			$userid_arr[$record->data] = $key;
		}

		if (count($userid_arr) > 0) {
			list($insql, $inparams) = $DB->get_in_or_equal($userid_arr);
		} else {
			list($insql, $inparams) = $DB->get_in_or_equal(['']);
		}

		// $sql = "SELECT {grade_grades}.id, finalgrade, itemname, idnumber,userid, courseid
		// 	from {grade_grades}, {grade_items}
		// 	where userid $insql and courseid = $courseid
		// 	and {grade_grades}.itemid = {grade_items}.id and idnumber in ('cc', 'kt', 'dkt','kt.1','kt.2')";

		$DIC = ['cc' => 'dcc', 'kt' => 'dkt', 'ddk' => 'xetdkdt', 'kt.1' => 'kt1', 'kt.2' => 'kt2', 'kt1' => 'kt1', 'kt2' => 'kt2'];
		$array_idnumber = array_keys($DIC);

		if (count($array_idnumber) > 0) {
			list($insql2, $inparams2) = $DB->get_in_or_equal($array_idnumber);
		} else {
			list($insql2, $inparams2) = $DB->get_in_or_equal(['']);
		}

		$sql = "SELECT gg.id, c.shortname, gg.userid, gi.idnumber, gg.finalgrade
				from {grade_grades} as gg, {grade_items} as gi, {user_enrolments} as ue, {enrol} as e, {course} as c
				where  e.courseid = c.id and e.id = ue.enrolid and ue.userid = gg.userid and gg.userid $insql and c.shortname LIKE '%$ma_mon%'
					and gg.itemid = gi.id and gi.idnumber $insql2 and gi.courseid = c.id
				    and ue.status = 0 and c.visible = 1";

		$inparams = array_merge($inparams, $inparams2);

		$records = $DB->get_records_sql($sql, $inparams);

		$ketqua = [];
		$isCanfindDKT = false;
		foreach ($records as $key => $record) {
			$isCanfindDKT = false;
			$userid = $record->userid;
			$msv = $mhv_arr[$userid];
			$finalgrade = $record->finalgrade;
			$idnumber = $record->idnumber;
			if ($idnumber == 'ddk') {
				$isCanfindDKT = true;
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
				$ob->isCanfindDKT = false;
			}
			$ob->$itemname = $finalgrade;

			if ($isCanfindDKT) {
				$ob->isCanfindDKT = true;
			}

		}

		foreach ($ketqua as $key => $ob) {
			if (!$ob->isCanfindDKT) {

				if ($ob->dcc >= 5 && $ob->dkt >= 5 && $ob->kt1 > -1 && $ob->kt2 > -1) {
					$ob->xetdkdt = 1;
				} else {
					$ob->xetdkdt = 0;
				}
			}

			$ob->dcc = round($ob->dcc, 2);
			$ob->dkt = round($ob->dkt, 2);
			$ob->xetdkdt = round($ob->xetdkdt, 2);
		}

		$result->ketqua = $ketqua;

		return $result;
	}

}
