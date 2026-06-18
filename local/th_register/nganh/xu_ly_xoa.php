<?php
require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';

global $USER, $DB;


if (isset($_POST['mon_hoc'])) {
	$list_course_delete = $_POST['mon_hoc'];
	foreach($list_course_delete as $course_delete){
		$DB->delete_records('th_mon_hoc_has_nganh_hoc', ['id' => $course_delete]);
	}
	if(isset($_POST['idnganh'])) {
		redirect($CFG->wwwroot . "/local/th_register/nganh/monhoc.php?id=".$_POST['idnganh'], get_string('delete_course_success', 'local_th_register'), null, \core\output\notification::NOTIFY_SUCCESS);
	}
	redirect($CFG->wwwroot . "/local/th_register/nganh/index.php", get_string('delete_course_success', 'local_th_register'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
	if(isset($_POST['idnganh'])) {
		redirect($CFG->wwwroot . "/local/th_register/nganh/monhoc.php?id=".$_POST['idnganh'], get_string('select_course_to_delete', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
	}
	redirect($CFG->wwwroot . "/local/th_register/nganh/index.php", get_string('select_course_to_delete', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
}

?>
