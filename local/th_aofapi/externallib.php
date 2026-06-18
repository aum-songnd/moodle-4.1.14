<?php

require_once "$CFG->libdir/externallib.php";
require_once "$CFG->dirroot/user/profile/lib.php";

use gradereport_user\external\user as user_external;

class local_th_aofapi_external extends external_api {

	public static function getScore_parameters() {
		return new external_function_parameters(
			array(
				'ma_mon' => new external_value(PARAM_RAW, 'string, mã môn học', VALUE_REQUIRED),
				'dot_mo' => new external_value(PARAM_RAW, 'string, đợt mở môn', VALUE_REQUIRED),
				'ma_lop' => new external_value(PARAM_RAW, 'string, mã lớp', VALUE_DEFAULT, null),
			)
		);
	}

	public static function getScore_returns() {
		return new external_single_structure(
			array(
				'ma_mon' => new external_value(PARAM_RAW, 'string, Mã Môn học', VALUE_OPTIONAL),
				'dot_mo' => new external_value(PARAM_RAW, 'string, Đợt mở môn', VALUE_OPTIONAL),
				'ketqua' => new external_multiple_structure(
					new external_single_structure(
						array(
							'mhv' => new external_value(PARAM_RAW, 'Mã Học viên', VALUE_OPTIONAL),
							'makhoa' => new external_value(PARAM_RAW, 'Mã Khóa', VALUE_OPTIONAL),
							'malop' => new external_value(PARAM_RAW, 'Mã Lớp', VALUE_OPTIONAL),
							'cc' => new external_value(PARAM_RAW, 'Điểm chuyên cần', VALUE_OPTIONAL),
							'kt1' => new external_value(PARAM_RAW, 'Điểm kiểm tra 1', VALUE_OPTIONAL),
							'kt2' => new external_value(PARAM_RAW, 'Điểm kiểm tra 2', VALUE_OPTIONAL),
							'kt' => new external_value(PARAM_RAW, 'Điểm kiểm tra', VALUE_OPTIONAL),
							'dkdt' => new external_value(PARAM_RAW, 'Điều kiện dự thi', VALUE_OPTIONAL),
							'courseshortname' => new external_value(PARAM_RAW, 'Mã khóa học', VALUE_OPTIONAL)
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

	public static function getScore($ma_mon, $dot_mo, $ma_lop) {
		global $DB, $COURSE, $CFG;

		if ($ma_mon === '') {
			throw new invalid_parameter_exception('ma_mon không được để trống');
		}

		if (!ctype_digit($dot_mo)) {
			throw new invalid_parameter_exception('dot_mo phải là timestamp hợp lệ');
		}

		$result = new stdClass();
		$result->ma_mon = $ma_mon;
		$result->dot_mo = $dot_mo;
		$result->ketqua = [];

		$dicjson = get_config('local_th_aofapi', 'dic_mapping');
		$DIC = json_decode($dicjson, true) ?? [];
		$array_idnumber = array_keys($DIC);

		if (count($array_idnumber) > 0) {
			list($insql, $inparams) = $DB->get_in_or_equal(
				$array_idnumber,
				SQL_PARAMS_NAMED,
				'idn'
			);
		} else {
			list($insql, $inparams) = $DB->get_in_or_equal(['']);
		}

		$config = get_config('local_thlib');
		$studentfields = array_map('trim', explode(',', $config->studentcodeshortname));
		$makhoafields  = array_map('trim', explode(',', $config->enrollmentcourseshortname));
		$classfields   = array_map('trim', explode(',', $config->classcodeshortname));

		$studentfield = $studentfields[0] ?? '';
		$makhoafield  = $makhoafields[0] ?? '';
		$classfield   = $classfields[0] ?? '';

		// Mapping mã môn của Trường và AUM
		$codes = [$ma_mon];
		$records = $DB->get_records(
			'th_subject_code_mapping',
			['old_code' => $ma_mon],
			'',
			'current_code'
		);

		foreach ($records as $r) {
			$codes[] = $r->current_code;
		}

		$coursecode = get_config('local_th_framework_study', 'coursecode') ?: 'th_course_code';
		$fieldid = $DB->get_field('customfield_field', 'id', ['shortname' => $coursecode], IGNORE_MISSING);
		$mapping = array_unique($codes);
		list($insql_mapping, $params_mapping) = $DB->get_in_or_equal($mapping, SQL_PARAMS_NAMED);
		$compare  = $DB->sql_compare_text('d.value') . " $insql_mapping";

		$coursesql = "SELECT c.id, c.shortname
              FROM {customfield_data} d
              JOIN {course} c ON c.id = d.instanceid
             WHERE d.fieldid = :fid
               AND {$compare}
               AND startdate = :dotmo";

		$params_mapping['fid']    = $fieldid;
		$params_mapping['dotmo']  = $dot_mo;
		$courses = $DB->get_records_sql($coursesql, $params_mapping);

		// if (empty($courses)) {
		// 	throw new invalid_parameter_exception('Không tìm thấy khóa học tương ứng với mã môn và đợt mở');
		// }

		require_once($CFG->libdir . '/gradelib.php');

		$ketqua = [];
		foreach ($courses as $course) {
			$courseid = $course->id;
			$shortname = $course->shortname;

			/* =====================================================
			* 2. LẤY DANH SÁCH GRADE ITEM TỒN TẠI TRONG COURSE
			* ===================================================== */
			$sql_items = "
				SELECT DISTINCT gi.idnumber
				FROM {grade_items} gi
				JOIN {course} c ON c.id = gi.courseid
				WHERE
					c.visible = 1
					AND c.id = :courseid
					AND gi.idnumber $insql
			";

			$params_items = array_merge(
				$inparams,
				[
					'courseid' => $courseid,
					'dotmo' => $dot_mo
				]
			);

			$existingitems = $DB->get_fieldset_sql($sql_items, $params_items);

			/* =====================================================
			* 3. DEFAULT ĐIỂM THEO TỪNG CẤU PHẦN
			* ===================================================== */
			$defaultgrades = [];
			foreach ($DIC as $idnumber => $field) {
				$defaultgrades[$field] = in_array($idnumber, $existingitems)
					? number_format(0, 2, '.', '')     // có mục điểm
					: number_format(-1, 2, '.', '');   // không tồn tại mục điểm
			}
			// ✅ LẤY TOÀN BỘ HỌC VIÊN + ĐIỂM (userid = 0)
			$version = substr($CFG->release, 0, 1);
			if ($version == 3) {
				require_once($CFG->dirroot . '/grade/report/user/externallib.php');
				$grades = gradereport_user_external::get_grade_items($courseid, 0);
			} else if ($version == 4) {
				require_once($CFG->dirroot . '/grade/report/user/classes/external/user.php');
				$grades = user_external::get_grade_items($courseid, 0);
			} else {
				$grades = array();
			}
			// $grades = external_api::clean_returnvalue(gradereport_user_external::get_grade_items_returns(), $grades);

			foreach ($grades['usergrades'] as $usergrade) {
				$userid = $usergrade['userid'];
				$profile = profile_user_record($userid);

				$studentcode = (!empty($studentfield) && !empty($profile->$studentfield))
					? $profile->$studentfield
					: '';

				if ($studentcode === '') {
					continue;
				}

				$makhoacode = (!empty($makhoafield) && !empty($profile->$makhoafield))
					? $profile->$makhoafield
					: '';

				$classcode = (!empty($classfield) && !empty($profile->$classfield))
					? $profile->$classfield
					: '';

				// Lọc lớp nếu có
				if ($ma_lop && $classcode !== $ma_lop) {
					continue;
				}

				$key = $userid . '_' . $courseid;
				if (!isset($ketqua[$key])) {
					$ketqua[$key] = [
						'mhv'   => $studentcode,
						'makhoa'=> $makhoacode,
						'malop' => $classcode,
						'cc'    => $defaultgrades['cc'],
						'kt1'   => $defaultgrades['kt1'],
						'kt2'   => $defaultgrades['kt2'],
						'kt'    => $defaultgrades['kt'],
						'dkdt'  => $defaultgrades['dkdt'],
						'courseshortname' => $shortname
					];
				}

				// Gán điểm từ grade items
				foreach ($usergrade['gradeitems'] as $gi) {
					if (!empty($gi['idnumber']) && isset($DIC[$gi['idnumber']])) {
						if ($gi['graderaw'] !== null) {
							$ketqua[$key][$DIC[$gi['idnumber']]] = number_format($gi['graderaw'], 2, '.', '');
						}
					}
				}
			}
		}

		$result->ketqua = $ketqua;

		return $result;
	}
}
