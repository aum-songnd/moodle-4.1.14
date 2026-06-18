<?php
require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_register/mon_hoc/trigger.php';

global $USER, $DB;


if (isset($_POST['mon_hoc'])) {
	$list_course_delete = $_POST['mon_hoc'];
	foreach($list_course_delete as $course_delete){
		$DB->delete_records('th_mon_hoc', ['id' => $course_delete]);
		delete_mon_has_nganh($course_delete);
		delete_mon_has_dot($course_delete);
		delete_mon_has_doi_tuong($course_delete);
		delete_dang_ky_mon_hoc($course_delete);
	}
	redirect($CFG->wwwroot . "/local/th_register/mon_hoc/index.php", get_string('delete_course_success', 'local_th_register'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
	redirect($CFG->wwwroot . "/local/th_register/mon_hoc/index.php", get_string('select_course_to_delete', 'local_th_register'), null, \core\output\notification::NOTIFY_WARNING);
}

?>
