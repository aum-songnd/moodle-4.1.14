<?php

require_once '../../../config.php';
require_once $CFG->libdir.'/adminlib.php';
require_once $CFG->libdir.'/csvlib.class.php';
require_once $CFG->dirroot.'/local/th_register/mon_has_dot_mo/th_upload_form.php';
require_once $CFG->dirroot.'/local/th_register/mon_has_dot_mo/lib.php';
require_once $CFG->dirroot.'/local/th_register/lib.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/mon_has_dot_mo/edit.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_course_opening', 'local_th_register'));
$PAGE->set_title(get_string('manage_course_opening', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/mon_has_dot_mo/edit.php');
$PAGE->navbar->add(get_string('manage_course_opening', 'local_th_register'), $editurl);

$id        = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if ($id) {

	$mon_hoc_id          = $DB->get_field_sql("SELECT mon_hoc_id FROM {th_dot_mo_has_mon_hoc} WHERE id = '$id'");
	$mon_has_dot_mo_edit = $DB->get_records_sql("SELECT * FROM {th_dot_mo_has_mon_hoc} WHERE mon_hoc_id = '$mon_hoc_id'");

	$dot_mo_mon_id = [];
	foreach ($mon_has_dot_mo_edit as $k => $mon_has_dotmo) {
		$dot_mo_mon_id[] = $mon_has_dotmo->dot_mo_mon_id;
	}

	$mon_dotmo_edit             = new stdClass();
	$mon_dotmo_edit->dot_mo_id  = $dot_mo_mon_id;
	$mon_dotmo_edit->mon_hoc_id = $mon_hoc_id;

} else {
	$mon_dotmo_edit             = new stdClass();
	$mon_dotmo_edit->dot_mo_id  = '';
	$mon_dotmo_edit->mon_hoc_id = '';
}

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/local/th_register/mon_has_dot_mo/index.php');
}

$edit = new edit_form();
$edit->set_data(array('dot_mo_id' => $mon_dotmo_edit->dot_mo_id, 'mon_hoc_id' => $mon_dotmo_edit->mon_hoc_id));

if ($delete) {

	$PAGE->url->param('delete', 1);
	if ($confirm and confirm_sesskey()) {

		$DB->delete_records('th_dot_mo_has_mon_hoc', ['id' => $id]);
		$DB->delete_records('th_dang_ky_mon_hoc', ['dot_mo_has_mon_hoc_id' => $id]);

		redirect($CFG->wwwroot.'/local/th_register/mon_has_dot_mo/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
	}
	$strheading = get_string('delete');
	$PAGE->navbar->add($strheading);
	$PAGE->set_title($strheading);
	$PAGE->set_heading($COURSE->fullname);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strheading);
	$record = $DB->get_record('th_dot_mo_has_mon_hoc', array('id' => $id));

	$ten_mon    = ten_mon($record->mon_hoc_id);
	$ten_dot_mo = ten_dot_mo($record->dot_mo_mon_id);

	$yesurl  = new moodle_url('/local/th_register/mon_has_dot_mo/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
	$a = new stdClass();
	$a->ten_mon = $ten_mon;
	$a->ten_dot_mo = $ten_dot_mo;
	$message = get_string('confirm_delete_course_in_batch', 'local_th_register', $a);
	//$message = "Bạn có chắc chắn muốn xóa môn học $ten_mon trong đợt mở ($ten_dot_mo)";
	echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_register/mon_has_dot_mo/index.php'));
	echo $OUTPUT->footer();
	die;
}

if ($edit->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
} else if ($fromform = $edit->get_data()) {

	$ds_dot_mo = $fromform->dot_mo_id;
	$ds_mon    = $fromform->mon_hoc_id;

	$table       = new html_table();
	$table->head = array(
		get_string('no.', 'local_th_register'),
		get_string('ten_mon', 'local_th_register'),
		get_string('ten_dot_mo_mon', 'local_th_register'),
		get_string('status', 'local_th_register')
	);
	$stt         = 0;
	foreach ($ds_dot_mo as $k => $dot_mo_id) {
		foreach ($ds_mon as $j   => $mon_id) {

			$sql                = "SELECT * FROM {th_dot_mo_has_mon_hoc} WHERE mon_hoc_id = '$mon_id' AND dot_mo_mon_id = '$dot_mo_id'";
			$dot_mo_has_mon_hoc = $DB->get_record_sql($sql);
			if (empty($dot_mo_has_mon_hoc)) {
				$dataobjects                = new stdClass();
				$dataobjects->dot_mo_mon_id = $dot_mo_id;
				$dataobjects->mon_hoc_id    = $mon_id;
				$dataobjects->ngay_tao      = time();
				$dataobjects->nguoi_tao     = $USER->id;
				$dataobjects->ngay_sua      = null;
				$dataobjects->nguoi_sua     = null;

				$DB->insert_record('th_dot_mo_has_mon_hoc', $dataobjects, false);

				$stt        = $stt+1;
				$ten_mon    = ten_mon($mon_id);
				$ten_dot_mo = ten_dot_mo($dot_mo_id);

				$row          = new html_table_row();
				$cell         = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell         = new html_table_cell($ten_mon);
				$row->cells[] = $cell;
				$cell         = new html_table_cell($ten_dot_mo);
				$row->cells[] = $cell;
				$cell         = new html_table_cell();
				$cell->text   = html_writer::tag('span', get_string('course_added_to_batch', 'local_th_register'),
					array('class' => 'badge badge-success'));
				$row->cells[]  = $cell;
				$table->data[] = $row;

			} else {
				$stt        = $stt+1;
				$ten_mon    = ten_mon($mon_id);
				$ten_dot_mo = ten_dot_mo($dot_mo_id);

				$row          = new html_table_row();
				$cell         = new html_table_cell($stt);
				$row->cells[] = $cell;
				$cell         = new html_table_cell($ten_mon);
				$row->cells[] = $cell;
				$cell         = new html_table_cell($ten_dot_mo);
				$row->cells[] = $cell;
				$cell         = new html_table_cell();
				$cell->text   = html_writer::tag('span',  get_string('course_already_in_batch', 'local_th_register'),
					array('class' => 'badge badge-secondary'));
				$row->cells[]  = $cell;
				$table->data[] = $row;
			}
		}
	}

	$html = html_writer::table($table);

	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('add_new_course_to_batch', 'local_th_register'));
	echo "</br>";
	$edit->display();
	echo "</br>";
	echo $OUTPUT->heading(get_string('add_course_to_batch_success', 'local_th_register'));
	echo "</br>";
	echo $html;
	echo $OUTPUT->footer();

} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();
	$baseurl = new moodle_url('/local/th_register/mon_has_dot_mo/edit.php');
	if ($editcontrols = local_th_mon_has_dot_mo_controls($context, $baseurl)) {
		echo $OUTPUT->render($editcontrols);
	}
	echo $OUTPUT->heading(get_string('add_new_course_to_batch', 'local_th_register'));
	echo "</br>";
	$edit->display();
	echo $OUTPUT->footer();
}
