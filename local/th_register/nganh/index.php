<?php
require '../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/th_register/nganh/lib.php';
require_once $CFG->dirroot . '/local/th_register/lib.php';

global $DB, $CFG, $COURSE, $USER;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'local_th_register', $COURSE->id);
}
require_login($COURSE->id);
require_capability('local/th_register:view', context_course::instance($COURSE->id));

$PAGE->set_url(new moodle_url('/local/th_register/nganh/index.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('manage_majors', 'local_th_register'));
$PAGE->set_title(get_string('manage_majors', 'local_th_register'));
$editurl = new moodle_url('/local/th_register/nganh/index.php');
$PAGE->navbar->add(get_string('manage_majors', 'local_th_register'), $editurl);
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', get_string('nganh_hoc_list', 'local_th_register'), $lang));

$id = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/local/th_register/nganh/index.php');

$records = $DB->get_records('th_nganh_hoc');

$html = get_string('no_nganh_hoc', 'local_th_register');
if ($records) {
	$table = new html_table();
	$table->attributes = array('class' => 'table', 'border' => '1');
	$table->align[0] = 'center';

	$table->head = array(get_string('no.', 'local_th_register'),
		get_string('ma_nganh', 'local_th_register'),
		get_string('ten_nganh', 'local_th_register'),
		get_string('hoc_phi_mot_tin_chi', 'local_th_register'),
		get_string('creator', 'local_th_register'),
		get_string('timecreated', 'local_th_register'),
		get_string('editor', 'local_th_register'),
		get_string('timemodified', 'local_th_register'),
		get_string('action'));
	$stt = 0;
	foreach ($records as $k => $record) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($record->ma_nganh);
		$row->cells[] = $cell;
		$ten_nganh = html_writer::link(new moodle_url('/local/th_register/nganh/monhoc.php?id='. $record->id), $record->ten_nganh);
		$cell = new html_table_cell($ten_nganh);
		$row->cells[] = $cell;
		$cell = new html_table_cell($record->hoc_phi_mot_tin_chi);
		$row->cells[] = $cell;
		$cell = new html_table_cell(th_register_get_user_fullname($record->nguoi_tao));
		$row->cells[] = $cell;
		$cell = new html_table_cell(date('d/m/Y', $record->ngay_tao));
		$row->cells[] = $cell;
		if ($record->nguoi_sua) {
			$cell = new html_table_cell(th_register_get_user_fullname($record->nguoi_sua));
		} else {
			$cell = new html_table_cell();
		}
		$row->cells[] = $cell;
		if ($record->ngay_sua) {
			$cell = new html_table_cell(date('d/m/Y', $record->ngay_sua));
		} else {
			$cell = new html_table_cell();
		}
		$row->cells[] = $cell;
		$urlparams = array('id' => $record->id);
		$edit = html_writer::link(new moodle_url('/local/th_register/nganh/edit.php', $urlparams),
			$OUTPUT->pix_icon('t/edit', get_string('edit')),
			array('title' => get_string('edit')));
		$delete = html_writer::link(new moodle_url('/local/th_register/nganh/edit.php', $urlparams + array('delete' => 1)),
			$OUTPUT->pix_icon('t/delete', get_string('delete')),
			array('title' => get_string('delete')));

		$cell = new html_table_cell($edit . $delete);
		$row->cells[] = $cell;
		$table->data[] = $row;
		$html = html_writer::table($table);
	}
}

echo $OUTPUT->header();
if ($editcontrols = local_th_register_nganh_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}
echo html_writer::tag("h2", get_string('so_nganh_hoc', 'local_th_register') . count($records));
echo $html;

echo $OUTPUT->footer();
