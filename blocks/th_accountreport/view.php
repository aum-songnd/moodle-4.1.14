<?php
require_once "../../config.php";
require_once "$CFG->libdir/formslib.php";
require_once "th_accountreport_form.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once "lib.php";
require_once($CFG->dirroot . '/user/lib.php');

global $DB, $CFG, $COURSE;
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_accountreport', $COURSE->id);
}
require_login($COURSE->id);
require_capability('block/th_accountreport:view', context_course::instance($COURSE->id));
$PAGE->set_url(new moodle_url('/blocks/th_accountreport/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('namereport', 'block_th_accountreport'));
$PAGE->set_title(get_string('namereport', 'block_th_accountreport'));
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.reportaccount-table', "Báo cáo tài khoản", $lang));
$editurl = new moodle_url('/blocks/th_accountreport/view.php');
$settingsnode = $PAGE->navbar->add(get_string('namereport', 'block_th_accountreport'), $editurl);

echo $OUTPUT->header();

$mform = new th_accountreport_form();
$fromform = $mform->get_data();

$mform->display();
if ($mform->get_data() != null && $fromform->startdate <= $fromform->enddate) {
	$from = $fromform->startdate;
	$to = $fromform->enddate + strtotime("+23 hours 59 minutes 59 seconds", 0);
	$acc = null;
	$acc1 = null;
	$acc2 = null;
	$accDayLeft = null;
	$last = 0;
	if ($fromform->filter == 'day') {
		$firstDayStart = strtotime(date("m/d/Y", strtotime("this day", $to)));

		$secondsDayStart = strtotime(date("m/d/Y", strtotime("last day", $to)));
		$secondsDayEnd = $secondsDayStart + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$thirdDayStart = strtotime(date("m/d/Y", strtotime("last day -1 day", $to)));
		$thirdDayEnd = $thirdDayStart + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$fourthDayEnd = strtotime(date("m/d/Y", strtotime("last day -2 day", $to))) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$acc = getUser($firstDayStart, $to);
		$sotk = count($acc);

		if ($secondsDayStart >= $from) {
			$acc1 = getUser($secondsDayStart, $secondsDayEnd);
			$sotk1 = count($acc1);
		}
		if ($thirdDayStart >= $from) {
			$acc2 = getUser($thirdDayStart, $thirdDayEnd);
			$sotk2 = count($acc2);
		}
		if ($fourthDayEnd >= $from) {
			$last = $fourthDayEnd;
		}
	}
	if ($fromform->filter == 'week') {
		//Tuan
		$thisWeekMonday = date("m/d/Y", strtotime("this week monday", $to));
		$thisWeekMonday = strtotime($thisWeekMonday);

		$thisWeekSunday = date("m/d/Y", strtotime("this week sunday", $to));
		$thisWeekSunday = strtotime($thisWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$lastWeekMonday = date("m/d/Y", strtotime("last week monday", $to));
		$lastWeekMonday = strtotime($lastWeekMonday);

		$lastWeekSunday = date("m/d/Y", strtotime("last week sunday", $to));
		$lastWeekSunday = strtotime($lastWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$lastTwoWeekMonday = date("m/d/Y", strtotime("last week monday -1 week", $to));
		$lastTwoWeekMonday = strtotime($lastTwoWeekMonday);

		$lastTwoWeekSunday = date("m/d/Y", strtotime("last week sunday -1 week", $to));
		$lastTwoWeekSunday = strtotime($lastTwoWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$lastThreeWeekSunday = date("m/d/Y", strtotime("last week sunday -2 week", $to));
		$lastThreeWeekSunday = strtotime($lastThreeWeekSunday) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$acc = getUser($thisWeekMonday, $thisWeekSunday);
		$sotk = count($acc);

		if ($thisWeekMonday > $from) {
			$acc1 = getUser($lastWeekMonday, $lastWeekSunday);
			$sotk1 = count($acc1);
		}

		if ($lastWeekMonday > $from) {
			$acc2 = getUser($lastTwoWeekMonday, $lastTwoWeekSunday);
			$sotk2 = count($acc2);
		}

		if ($lastTwoWeekMonday > $from) {
			$last = $lastThreeWeekSunday;
		}
	}
	if ($fromform->filter == 'month') {
		//Thang
		$firstDayThisMonth = date("m/d/Y", strtotime("first day of this month", $to));
		$firstDayThisMonth = strtotime($firstDayThisMonth);

		$lastDayThisMonth = date("m/d/Y", strtotime("last day of this month", $to));
		$lastDayThisMonth = strtotime($lastDayThisMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$firstDayAfterOneMonth = date("m/d/Y", strtotime("first day of last month", $to));
		$firstDayAfterOneMonth = strtotime($firstDayAfterOneMonth);

		$lastDayAfterOneMonth = date("m/d/Y", strtotime("last day of last month", $to));
		$lastDayAfterOneMonth = strtotime($lastDayAfterOneMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$firstDayAfterTwoMonth = date("m/d/Y", strtotime("first day of last month -1 month", $to));
		$firstDayAfterTwoMonth = strtotime($firstDayAfterTwoMonth);

		$lastDayAfterTwoMonth = date("m/d/Y", strtotime("last day of last month -1 month", $to));
		$lastDayAfterTwoMonth = strtotime($lastDayAfterTwoMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$lastDayAfterThreeMonth = date("m/d/Y", strtotime("last day of last month -2 month", $to));
		$lastDayAfterThreeMonth = strtotime($lastDayAfterThreeMonth) + strtotime("+23 hours 59 minutes 59 seconds", 0);

		$acc = getUser($firstDayThisMonth, $lastDayThisMonth);
		$sotk = count($acc);

		if ($firstDayThisMonth > $from) {
			$acc1 = getUser($firstDayAfterOneMonth, $lastDayAfterOneMonth);
			$sotk1 = count($acc1);
		}
		//sau ngay ket thuc 3 thang
		if ($firstDayAfterOneMonth > $from) {
			$acc2 = getUser($firstDayAfterTwoMonth, $lastDayAfterTwoMonth);
			$sotk2 = count($acc2);
		}

		if ($firstDayAfterTwoMonth > $from) {
			$last = $lastDayAfterThreeMonth;
		}
	}
	//lay du lieu Con lai
	if ($last >= $from) {
		$accDayLeft = getUser($from, $last);
		$countAccDayLeft = count($accDayLeft);
	}
	$userid_arr = [];
	if ($acc != null) {
		foreach ($acc as $key => $value) {
			$userid_arr[] = $value->id;
		}
	}
	if ($acc1 != null) {
		foreach ($acc1 as $key => $value) {
			$userid_arr[] = $value->id;
		}
	}
	if ($acc2 != null) {
		foreach ($acc2 as $key => $value) {
			$userid_arr[] = $value->id;
		}
	}
	if ($accDayLeft != null) {
		foreach ($accDayLeft as $key => $value) {
			$userid_arr[] = $value->id;
		}
	}

	// $extra = array_filter(explode(',', $CFG->showuseridentity));
	// $userfields = array_values($extra);

	// $usernamefield = get_all_user_name_fields();
	// $usernamefield = implode(",", $usernamefield);
	// $alluserfields = "id," . $usernamefield;

	// if (count($userfields) > 0) {
	// 	$alluserfields .= "," . implode(',', $userfields);
	// }

	// $alluserfields .= "," . "email";

	// $user_arr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), "", $alluserfields);


	$extra = array_filter(explode(',', $CFG->showuseridentity));
	$userfields = array_values($extra);

	// Phân loại trường
	$tablecolumns = $DB->get_columns('user');
	$tablecolumnskeys = array_keys($tablecolumns);

	$normalfields = [];
	$profilefields = [];

	foreach ($userfields as $field) {
		if (in_array($field, $tablecolumnskeys)) {
			$normalfields[] = $field;
		} elseif (strpos($field, 'profile_field_') === 0) {
			$profilefields[] = $field;
		}
	}

	$usernamefields = implode(',', get_all_user_name_fields());
	$alluserfields = "id,$usernamefields";

	if (!empty($normalfields)) {
		$alluserfields .= "," . implode(',', $normalfields);
	}
	$alluserfields .= ",email";

	// Lấy danh sách user đang hoạt động
	$user_arr = $DB->get_records('user', ['deleted' => 0, 'suspended' => 0], '', $alluserfields);

	// Lấy giá trị các profile field nếu có
	if (!empty($profilefields)) {
		$shortnames = array_map(function($f) {
			return str_replace('profile_field_', '', $f);
		}, $profilefields);

		list($inSql, $inParams) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
		$profiledata = $DB->get_records_sql("
			SELECT uid.userid, uif.shortname, uid.data
			FROM {user_info_data} uid
			JOIN {user_info_field} uif ON uid.fieldid = uif.id
			WHERE uif.shortname $inSql
		", $inParams);

		foreach ($profiledata as $record) {
			$fieldname = 'profile_field_' . $record->shortname;
			if (isset($user_arr[$record->userid])) {
				$user_arr[$record->userid]->$fieldname = $record->data;
			}
		}

		// Gán giá trị rỗng nếu user không có dữ liệu
		foreach ($user_arr as &$u) {
			foreach ($profilefields as $field) {
				if (!isset($u->$field)) {
					$u->$field = '';
				}
			}
		}
		unset($u);
	}

	$rightrows = [];

	$table = new html_table();
	$headrows = new html_table_row();
	$cell = new html_table_cell(get_string('timecreated', 'block_th_accountreport'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('time'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;
	$cell = new html_table_cell(get_string('total', 'block_th_accountreport'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;

	$rightrows[] = $headrows;
	list($leftrows, $rows_ex_left) = get_left_rows($userid_arr, $user_arr);

	$config = get_config('local_thlib');
	$custom_fields1 = array_map('trim', explode(",", $config->custom_fields1));
	$custom_fields1 = array_unique($custom_fields1);
	$custom_fields1 = array_filter($custom_fields1);
	$cellLeft = count($custom_fields1);

	$custom_fields2 = array_map('trim', explode(",", $config->custom_fields2));
	$custom_fields2 = array_unique($custom_fields2);
	$custom_fields2 = array_filter($custom_fields2);
	$cellRight = count($custom_fields2);

	// if (!in_array('username', $custom_fields1) and !in_array('username', $custom_fields2)) {
	// 	foreach ($leftrows as $key => $r) {
	// 		$u = $DB->get_record('user', array('id' => $key));
	// 		if ($u) {
	// 			$cell = new html_table_cell($u->username);
	// 		} elseif ($key == 0) {
	// 			$cell = new html_table_cell(get_string('username'));
	// 		} else {
	// 			$cell = new html_table_cell();
	// 		}
	// 		$result = array_merge(array_slice($r->cells, 0, 1), array($cell), array_slice($r->cells, 1));
	// 		$leftrows[$key]->cells = $result;
	// 	}
	// 	$cellLeft++;
	// }

	$soCot = count($leftrows[0]->cells);
	//do du lieu ra bang
	if ($fromform->filter == 'day') {
		if ($acc != null) {
			$stt = true;
			foreach ($acc as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($to));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($to);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($to);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('1');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;

			$cell = new html_table_cell(getIntTypeDate($to));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($to);

			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
			$row->cells[] = $cell;
			$rightrows['s1'] = $row;
		}
		if ($acc1 != null) {
			$stt = true;
			foreach ($acc1 as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($secondsDayEnd));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($secondsDayEnd);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk1);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($secondsDayEnd);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} elseif ($secondsDayStart < $from) {

		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('2');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($secondsDayEnd));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($secondsDayEnd);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk1);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
			$row->cells[] = $cell;
			$rightrows['s2'] = $row;
		}
		if ($acc2 != null) {
			$stt = true;
			foreach ($acc2 as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($thirdDayEnd));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($thirdDayEnd);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk2);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
				} else {

					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($thirdDayEnd);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;

				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} elseif ($thirdDayStart < $from) {

		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('3');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($thirdDayEnd));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($thirdDayEnd);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk2);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
			$row->cells[] = $cell;
			$rightrows['s3'] = $row;
		}
	}
	if ($fromform->filter == 'week') {
		if ($acc != null) {
			$stt = true;
			foreach ($acc as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($thisWeekMonday) . ' - ' . getIntTypeDate($thisWeekSunday));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($thisWeekMonday) . ' - ' . getIntTypeDate($thisWeekSunday);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($thisWeekMonday) . ' - ' . getIntTypeDate($thisWeekSunday);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('1');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($thisWeekMonday) . ' - ' . getIntTypeDate($thisWeekSunday));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($thisWeekMonday) . ' - ' . getIntTypeDate($thisWeekSunday);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
			$row->cells[] = $cell;
			$rightrows['s1'] = $row;
		}
		if ($acc1 != null) {
			$stt = true;
			foreach ($acc1 as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($fromform->filter == 'week' && $stt) {
					$cell = new html_table_cell(getIntTypeDate($lastWeekMonday) . ' - ' . getIntTypeDate($lastWeekSunday));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($lastWeekMonday) . ' - ' . getIntTypeDate($lastWeekSunday);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk1);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($lastWeekMonday) . ' - ' . getIntTypeDate($lastWeekSunday);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} elseif ($thisWeekMonday <= $from) {

		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('2');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($lastWeekMonday) . ' - ' . getIntTypeDate($lastWeekSunday));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($lastWeekMonday) . ' - ' . getIntTypeDate($lastWeekSunday);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk1);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
			$row->cells[] = $cell;
			$rightrows['s2'] = $row;
		}
		if ($acc2 != null) {
			$stt = true;
			foreach ($acc2 as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($lastTwoWeekMonday) . ' - ' . getIntTypeDate($lastTwoWeekSunday));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($lastTwoWeekMonday) . ' - ' . getIntTypeDate($lastTwoWeekSunday);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk2);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($lastTwoWeekMonday) . ' - ' . getIntTypeDate($lastTwoWeekSunday);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} elseif ($lastWeekMonday <= $from) {

		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('3');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($lastTwoWeekMonday) . ' - ' . getIntTypeDate($lastTwoWeekSunday));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($lastTwoWeekMonday) . ' - ' . getIntTypeDate($lastTwoWeekSunday);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk2);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
			$row->cells[] = $cell;
			$rightrows['s3'] = $row;
		}
	}
	if ($fromform->filter == 'month') {
		if ($acc != null) {
			$stt = true;
			foreach ($acc as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($firstDayThisMonth) . ' - ' . getIntTypeDate($lastDayThisMonth));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayThisMonth) . ' - ' . getIntTypeDate($lastDayThisMonth);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayThisMonth) . ' - ' . getIntTypeDate($lastDayThisMonth);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('1');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($firstDayThisMonth) . ' - ' . getIntTypeDate($lastDayThisMonth));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayThisMonth) . ' - ' . getIntTypeDate($lastDayThisMonth);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk;
			$row->cells[] = $cell;
			$rightrows['s1'] = $row;
		}
		if ($acc1 != null) {
			$stt = true;
			foreach ($acc1 as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($firstDayAfterOneMonth) . ' - ' . getIntTypeDate($lastDayAfterOneMonth));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayAfterOneMonth) . ' - ' . getIntTypeDate($lastDayAfterOneMonth);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk1);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayAfterOneMonth) . ' - ' . getIntTypeDate($lastDayAfterOneMonth);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} elseif ($firstDayThisMonth <= $from) {

		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('2');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($firstDayAfterOneMonth) . ' - ' . getIntTypeDate($lastDayAfterOneMonth));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayAfterOneMonth) . ' - ' . getIntTypeDate($lastDayAfterOneMonth);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk1);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk1;
			$row->cells[] = $cell;
			$rightrows['s2'] = $row;
		}
		if ($acc2 != null) {
			$stt = true;
			foreach ($acc2 as $key => $value) {
				$row = new html_table_row();
				$cell = new html_table_cell(getIntTypeDate($value->timecreated));
				$cell->attributes['data-order'] = $value->timecreated;
				$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
				$row->cells[] = $cell;
				if ($stt) {
					$cell = new html_table_cell(getIntTypeDate($firstDayAfterTwoMonth) . ' - ' . getIntTypeDate($lastDayAfterTwoMonth));
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayAfterTwoMonth) . ' - ' . getIntTypeDate($lastDayAfterTwoMonth);
					$row->cells[] = $cell;
					$cell = new html_table_cell($sotk2);
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
				} else {
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayAfterTwoMonth) . ' - ' . getIntTypeDate($lastDayAfterTwoMonth);
					$row->cells[] = $cell;
					$cell = new html_table_cell("");
					$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
				}
				$row->cells[] = $cell;
				$rightrows[$key] = $row;
				$stt = false;
			}
		} elseif ($firstDayAfterOneMonth <= $from) {

		} else {
			$row = new html_table_row();
			$cell = new html_table_cell('3');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellLeft; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('N/A');
			$row->cells[] = $cell;
			for ($i = 0; $i < $cellRight; $i++) {
				$cell = new html_table_cell('');
				$row->cells[] = $cell;
			}
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
			$cell = new html_table_cell(getIntTypeDate($firstDayAfterTwoMonth) . ' - ' . getIntTypeDate($lastDayAfterTwoMonth));
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($firstDayAfterTwoMonth) . ' - ' . getIntTypeDate($lastDayAfterTwoMonth);
			$row->cells[] = $cell;
			$cell = new html_table_cell($sotk2);
			$cell->attributes['data-order'] = $cell->attributes['data-search'] = $sotk2;
			$row->cells[] = $cell;
			$rightrows['s3'] = $row;
		}
	}
	if ($accDayLeft != null) {
		$stt = true;
		foreach ($accDayLeft as $key => $value) {
			$row = new html_table_row();
			$cell = new html_table_cell(getIntTypeDate($value->timecreated));
			$cell->attributes['data-order'] = $value->timecreated;
			$cell->attributes['data-search'] = getIntTypeDate($value->timecreated);
			$row->cells[] = $cell;
			if ($stt) {
				$cell = new html_table_cell(getIntTypeDate($from) . ' - ' . getIntTypeDate($last));
				$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($from) . ' - ' . getIntTypeDate($last);
				$row->cells[] = $cell;
				$cell = new html_table_cell($countAccDayLeft);
				$cell->attributes['data-order'] = $cell->attributes['data-search'] = $countAccDayLeft;
			} else {
				$cell = new html_table_cell("");
				$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($from) . ' - ' . getIntTypeDate($last);
				$row->cells[] = $cell;
				$cell = new html_table_cell("");
				$cell->attributes['data-order'] = $cell->attributes['data-search'] = $countAccDayLeft;
			}
			$row->cells[] = $cell;
			$rightrows[$key] = $row;
			$stt = false;
		}
	} elseif ($last <= $from) {

	} else {
		$row = new html_table_row();
		$cell = new html_table_cell('4');
		$row->cells[] = $cell;
		for ($i = 0; $i < $cellLeft; $i++) {
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
		}
		$cell = new html_table_cell('N/A');
		$row->cells[] = $cell;
		for ($i = 0; $i < $cellRight; $i++) {
			$cell = new html_table_cell('');
			$row->cells[] = $cell;
		}
		$cell = new html_table_cell('');
		$row->cells[] = $cell;
		$cell = new html_table_cell(getIntTypeDate($from) . ' - ' . getIntTypeDate($last));
		$cell->attributes['data-order'] = $cell->attributes['data-search'] = getIntTypeDate($from) . ' - ' . getIntTypeDate($last);
		$row->cells[] = $cell;
		$cell = new html_table_cell($countAccDayLeft);
		$cell->attributes['data-order'] = $cell->attributes['data-search'] = $countAccDayLeft;
		$row->cells[] = $cell;
		$rightrows['s4'] = $row;
	}

	$tt = 0;
	foreach ($rightrows as $key => $row) {
		if (!array_key_exists($key, $leftrows)) {
			$row->cells = array_merge(array(), $row->cells);
		} else {
			$row->cells = array_merge($leftrows[$key]->cells, $row->cells);
		}
		if ($tt != 0) {
			$row->cells[0]->text = $tt;
		}
		$tt++;
		$table->data[] = $row;
	}
	$headrows = array_shift($table->data);
	$table->head = $headrows->cells;
	$table->attributes = array('class' => 'reportaccount-table', 'border' => '1');
	$table->align[0] = 'center';
	$table->align[$soCot + 2] = 'center';
	echo html_writer::table($table);
}
echo $OUTPUT->footer();