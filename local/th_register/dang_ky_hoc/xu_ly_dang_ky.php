<?php
require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_register/classes/event/th_user_register_event.php';

$id = optional_param('id', 0, PARAM_INT);
$nganh = optional_param('nganh', 0, PARAM_INT);

global $USER, $DB;

$userid = $USER->id;

if (isset($_POST['dangky'])) {

	if (!empty($_POST['mon_hoc'])) {

		if ($nganh == 1) {
			$field_ma_lop = get_config('local_th_register', 'ma_lop');
		} else if ($nganh == 2) {
			$field_ma_lop = get_config('local_th_register', 'ma_lop2');
		}

		$lop = $DB->get_field_sql("SELECT info_data.data FROM {user_info_field} as field, {user_info_data} as info_data WHERE field.shortname = '$field_ma_lop' AND info_data.userid = '$userid' AND info_data.fieldid = field.id");
		$ma_nganh = substr($lop, -4, 2);
		$id_nganh = $DB->get_field_sql("SELECT id FROM {th_nganh_hoc} WHERE ma_nganh = '$ma_nganh'");

		$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		function generate_string($input, $strength = 16) {
			$input_length = strlen($input);
			$random_string = '';
			for ($i = 0; $i < $strength; $i++) {
				$random_character = $input[mt_rand(0, $input_length - 1)];
				$random_string .= $random_character;
			}
			return $random_string;
		}

		do {
			$ma_dang_ky = generate_string($permitted_chars, 20);
		} while ($DB->get_records_sql("SELECT * FROM {th_dang_ky_mon_hoc} WHERE ma_dang_ky = '$ma_dang_ky'"));

		$ngay_tao = time();

		$list_ma_mon = [];
		foreach ($_POST['mon_hoc'] as $value) {
			$value_arr = explode(',', $value);
			$dot_mo_has_mon_hoc_id = $value_arr[0];
			$ma_mon = $DB->get_field_sql("SELECT m.ma_mon FROM {th_mon_hoc} as m, {th_dot_mo_has_mon_hoc} as dm WHERE dm.id = $dot_mo_has_mon_hoc_id AND m.id = dm.mon_hoc_id");
			$id_nganh = $value_arr[1];
			$dataobjects = new stdClass();
			$dataobjects->userid_moodle = $userid;
			$dataobjects->dot_mo_has_mon_hoc_id = $dot_mo_has_mon_hoc_id;
			$dataobjects->id_nganh = $id_nganh;
			$dataobjects->ma_dang_ky = $ma_dang_ky;
			$dataobjects->ngay_tao = $ngay_tao;

			$sql = "SELECT * FROM {th_dang_ky_mon_hoc} WHERE userid_moodle = '$USER->id' AND dot_mo_has_mon_hoc_id = '$dot_mo_has_mon_hoc_id' AND id_nganh = '$id_nganh'";
			if (empty($DB->get_records_sql($sql))) {
				$list_ma_mon[] = $ma_mon;
				$DB->insert_record('th_dang_ky_mon_hoc', $dataobjects, false);
			}
		}

		$data_event = array('other' => $list_ma_mon); 
		$context = context_user::instance($userid);
		$data_event['userid'] = $userid;
		$data_event['context'] = $context;

		$event = \local_th_register\event\th_user_register_event::create($data_event);
		$event->trigger();

		redirect($CFG->wwwroot . "/local/th_register/dang_ky_hoc/index.php?id=$id&nganh=$nganh", get_string('course_registration_success', 'local_th_register'), null, \core\output\notification::NOTIFY_SUCCESS);
	} else {
		redirect($CFG->wwwroot . "/local/th_register/dang_ky_hoc/index.php?id=$id&nganh=$nganh", get_string('please_select_course', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
	}
}

if (isset($_POST['huy'])) {
	$list_ma_mon = [];
	if (!empty($_POST['mon_huy'])) {
		foreach ($_POST['mon_huy'] as $value) {
			$mon_dang_ky_id = $value;
			$ma_mon = $DB->get_field_sql("SELECT m.ma_mon FROM {th_mon_hoc} as m, {th_dot_mo_has_mon_hoc} as dm, {th_dang_ky_mon_hoc} as dk WHERE dk.id = $mon_dang_ky_id AND m.id = dm.mon_hoc_id AND dm.id = dk.dot_mo_has_mon_hoc_id AND dk.userid_moodle = '$userid'");
			$list_ma_mon[] = $ma_mon;
			$DB->delete_records('th_dang_ky_mon_hoc', ['id' => $mon_dang_ky_id]);
		}

		$data_event = array('other' => $list_ma_mon); 
		$context = context_user::instance($userid);
		$data_event['userid'] = $userid;
		$data_event['context'] = $context;

		$event = \local_th_register\event\th_user_cancel_register_event::create($data_event);
		$event->trigger();

		redirect($CFG->wwwroot . "/local/th_register/dang_ky_hoc/index.php?id=$id&nganh=$nganh", get_string('course_unregistration_success', 'local_th_register'), null, \core\output\notification::NOTIFY_SUCCESS);
	} else {
		redirect($CFG->wwwroot . "/local/th_register/dang_ky_hoc/index.php?id=$id&nganh=$nganh", get_string('please_select_course_to_cancel', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
	}
}
?>
