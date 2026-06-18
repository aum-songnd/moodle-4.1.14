<?php

require_once '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/th_register/mon_hoc/locallib.php';
require_once $CFG->dirroot . '/local/th_register/mon_hoc/trigger.php';
require_once $CFG->dirroot . '/local/th_register/mon_hoc/edit_form.php';
require_once $CFG->dirroot . '/local/th_register/lib.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION, $DB, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/mon_hoc/edit.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_courses', 'local_th_register'));
$PAGE->set_title(get_string('manage_courses', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/mon_hoc/edit.php');
$PAGE->settingsnav->add(get_string('manage_courses', 'local_th_register'), $editurl);

$id        = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if ($id) {
	$mon_hoc_edit = $DB->get_record('th_mon_hoc', array('id' => $id), '*', MUST_EXIST);
	$sql          = "SELECT nganh_hoc_id FROM {th_mon_hoc_has_nganh_hoc} WHERE mon_hoc_id = '$id'";
	$nganh_hoc_id = $DB->get_records_sql($sql);
	$ma_nganh_id = [];
	foreach ($nganh_hoc_id as $k => $nganh_hoc) {
		$ma_nganh_id[] = $nganh_hoc->nganh_hoc_id;
	}
	$mon_hoc_edit->ma_nganh_id = $ma_nganh_id;
	$sql1      = "SELECT doi_tuong_id  FROM {th_mon_hoc_has_doi_tuong} WHERE mon_hoc_id = '$id'";
	$doi_tuong = $DB->get_records_sql($sql1);
	$doi_tuong_id = [];
	foreach ($doi_tuong as $k => $doi_tuong1) {
		$doi_tuong_id[] = $doi_tuong1->doi_tuong_id;
	}
	$mon_hoc_edit->doi_tuong_id = $doi_tuong_id;
} else {
	$mon_hoc_edit              = new stdClass();
	$mon_hoc_edit->ma_mon      = '';
	$mon_hoc_edit->ten_mon     = '';
	$mon_hoc_edit->so_tin_chi  = '';
	$mon_hoc_edit->ma_nganh_id = '';
	$mon_hoc_edit->doi_tuong_id = '';
}

if ($returnurl) {
	$returnurl = new moodle_url($returnurl);
} else {
	$returnurl = new moodle_url('/local/th_register/mon_hoc/index.php');
}

$edit = new edit_form();

$edit->set_data(array('ma_mon' => $mon_hoc_edit->ma_mon, 'ten_mon' => $mon_hoc_edit->ten_mon, 'so_tin_chi' => $mon_hoc_edit->so_tin_chi, 'ma_nganh_id' => $mon_hoc_edit->ma_nganh_id, 'doi_tuong_id' => $mon_hoc_edit->doi_tuong_id, 'id' => $id));

if ($delete) {
	$PAGE->url->param('delete', 1);
	if ($confirm and confirm_sesskey()) {

		$DB->delete_records('th_mon_hoc', ['id' => $id]);
		delete_mon_has_nganh($id);
		delete_mon_has_dot($id);
		delete_mon_has_doi_tuong($id);
		delete_dang_ky_mon_hoc($id);
		redirect($CFG->wwwroot . '/local/th_register/mon_hoc/index.php', get_string('success'), null, \core\output\notification::NOTIFY_SUCCESS);
	}
	$strheading = get_string('delete');
	$PAGE->navbar->add($strheading);
	$PAGE->set_title($strheading);
	$PAGE->set_heading($COURSE->fullname);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strheading);
	$record  = $DB->get_record('th_mon_hoc', array('id'                         => $id));
	$yesurl  = new moodle_url('/local/th_register/mon_hoc/edit.php', array('id' => $id, 'confirm' => 1, 'delete' => 1, 'sesskey' => sesskey()));
	//$message = "Bạn có chắc chắn muốn xóa Môn học " . html_writer::tag('b', $record->ten_mon . "($record->ma_mon)");
	$message = get_string('confirm_delete_course', 'local_th_register',[
		'ten_mon' => $record->ten_mon,
		'ma_mon' => $record->ma_mon
	]);
	echo $OUTPUT->confirm($message, $yesurl, new moodle_url('/local/th_register/mon_hoc/index.php'));
	echo $OUTPUT->footer();
	die;
}

if ($edit->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/local/th_register/mon_hoc/index.php');
	redirect($courseurl);
} else if ($fromform = $edit->get_data()) {

	if ($id) {
		$ma_mon        = $fromform->ma_mon;
		$nganh_hoc_arr = $fromform->ma_nganh_id;
		$sql1   = "SELECT * FROM {th_mon_hoc} WHERE id = '$id'";
		$course = $DB->get_record_sql($sql1);
		$ma_mon_id = $course->id;
		$dataobjects             = new stdClass();
		$dataobjects->id         = $ma_mon_id;
		$dataobjects->ma_mon     = $ma_mon;
		$dataobjects->ten_mon    = $fromform->ten_mon;
		$dataobjects->so_tin_chi = $fromform->so_tin_chi;
		$dataobjects->ngay_tao   = $course->ngay_tao;
		$dataobjects->nguoi_tao  = $course->nguoi_tao;
		$dataobjects->ngay_sua   = time();
		$dataobjects->nguoi_sua  = $USER->id;
		$nganh_hoc_edit = $fromform->ma_nganh_id;
		update_mon_has_nganh($ma_mon_id, $nganh_hoc_edit);
		$doi_tuong_edit = $fromform->doi_tuong_id;
		update_mon_has_doi_tuong($ma_mon_id, $doi_tuong_edit);
		$DB->update_record('th_mon_hoc', $dataobjects);

		redirect($returnurl, get_string('update_success', 'local_th_register'), null, \core\output\notification::NOTIFY_SUCCESS);
	} else {
		$ma_mon        = $fromform->ma_mon;
		$nganh_hoc_arr = $fromform->ma_nganh_id;
		$doi_tuong_arr = $fromform->doi_tuong_id;
		$dataobjects             = new stdClass();
		$dataobjects->ma_mon     = $ma_mon;
		$dataobjects->ten_mon    = $fromform->ten_mon;
		$dataobjects->so_tin_chi = $fromform->so_tin_chi;
		$dataobjects->ngay_tao   = time();
		$dataobjects->nguoi_tao  = $USER->id;
		$dataobjects->ngay_sua   = null;
		$dataobjects->nguoi_sua  = null;
		$DB->insert_record('th_mon_hoc', $dataobjects, false);
		$sql = "SELECT * FROM {th_mon_hoc} WHERE ma_mon = '$ma_mon'";
		$id  = $DB->get_field_sql($sql);

		$table       = new html_table();
		$table->head = array(
			get_string('no.', 'local_th_register'),
			get_string('ten_mon', 'local_th_register'), 
			get_string('ma_mon', 'local_th_register'), 
			get_string('notify', 'local_th_register')
		);

		foreach ($nganh_hoc_arr as $k => $nganh_hoc_id) {
			$nganh                      = $DB->get_record_sql("SELECT * FROM {th_nganh_hoc} WHERE id = '$nganh_hoc_id'");
			$ma_nganh                   = $nganh->ma_nganh;
			$dataobjects1               = new stdClass();
			$dataobjects1->mon_hoc_id   = $id;
			$dataobjects1->nganh_hoc_id = $nganh_hoc_id;
			$dataobjects1->ngay_tao     = time();
			$dataobjects1->nguoi_tao    = $USER->id;
			$dataobjects1->ngay_sua     = null;
			$dataobjects1->nguoi_sua    = null;

			$DB->insert_record('th_mon_hoc_has_nganh_hoc', $dataobjects1, false);

			$stt           = $k + 1;
			$ten_mon       = $fromform->ten_mon;
			$ma_mon        = $fromform->ma_mon;
			$row           = new html_table_row();
			$cell          = new html_table_cell($stt);
			$row->cells[]  = $cell;
			$cell          = new html_table_cell($ten_mon);
			$row->cells[]  = $cell;
			$cell          = new html_table_cell($ma_mon);
			$row->cells[]  = $cell;
			$cell = new html_table_cell(get_string('success_add_course', 'local_th_register', [
				'ma_mon' => $ma_mon,
				'ma_nganh' => $ma_nganh
			]));
			
			$row->cells[]  = $cell;
			$table->data[] = $row;
		}

		foreach ($doi_tuong_arr as $k => $doi_tuong) {
			$mon_has_doi_tuong = $DB->get_record_sql("SELECT * FROM {th_mon_hoc_has_doi_tuong} WHERE mon_hoc_id = '$id' AND doi_tuong_id = '$doi_tuong'");
			if (empty($mon_has_doi_tuong)) {
				$dataobjects2               = new stdClass();
				$dataobjects2->mon_hoc_id   = $id;
				$dataobjects2->doi_tuong_id = $doi_tuong;
				$dataobjects2->ngay_tao     = time();
				$dataobjects2->nguoi_tao    = $USER->id;
				$dataobjects2->ngay_sua     = null;
				$dataobjects2->nguoi_sua    = null;
				$DB->insert_record('th_mon_hoc_has_doi_tuong', $dataobjects2, false);
			}
		}

		$table->attributes          = array('class' => 'th_table', 'border' => '1');
		$table->attributes['style'] = "width: 100%; text-align:center;";

		$html = html_writer::table($table);

		echo $OUTPUT->header();
		$baseurl = new moodle_url('/local/th_register/mon_hoc/edit.php');
		if ($editcontrols = local_th_register_course_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}
		echo $OUTPUT->heading(get_string('add_new_course', 'local_th_register'));
		echo "</br>";
		$edit->display();
		echo "</br>";
		echo $html;
		echo $OUTPUT->footer();
	}
} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();
	$baseurl = new moodle_url('/local/th_register/mon_hoc/edit.php');
	if ($editcontrols = local_th_register_course_controls($context, $baseurl)) {
		echo $OUTPUT->render($editcontrols);
	}

	if ($id) {
		echo $OUTPUT->heading(get_string('edit_course', 'local_th_register'));
	} else {
		echo $OUTPUT->heading(get_string('add_new_course', 'local_th_register'));
	}
	echo "</br>";
	$edit->display();
	echo $OUTPUT->footer();
}
