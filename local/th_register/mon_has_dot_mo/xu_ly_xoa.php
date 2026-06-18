<?php
require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';

global $USER, $DB;


if (isset($_POST['mon_has_dot_mo'])) {
	$list_delete = $_POST['mon_has_dot_mo'];
	foreach($list_delete as $delete){
		$DB->delete_records('th_dot_mo_has_mon_hoc', ['id' => $delete]);
		$DB->delete_records('th_dang_ky_mon_hoc', ['dot_mo_has_mon_hoc_id' => $delete]);
	}
	redirect($CFG->wwwroot . "/local/th_register/mon_has_dot_mo/index.php", get_string('delete_course_opening_success', 'local_th_register'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
	redirect($CFG->wwwroot . "/local/th_register/mon_has_dot_mo/index.php", get_string('select_course_opening_delete', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
}

?>
