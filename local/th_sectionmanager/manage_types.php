<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/th_sectionmanager/classes/form/type_form.php');
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';
require_once 'lib.php';

global $DB, $PAGE, $OUTPUT;
$context = context_system::instance();

require_login();
require_capability('local/th_sectionmanager:manage', $context);

$editid = optional_param('id', 0, PARAM_INT); // Lấy ID nếu đang ấn nút Sửa
$deleteid = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$PAGE->set_url('/local/th_sectionmanager/manage_types.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin'); 
$PAGE->set_title(get_string('manage_types_title', 'local_th_sectionmanager'));
$PAGE->set_heading(get_string('manage_types_title', 'local_th_sectionmanager'));

if ($deleteid > 0) {
    require_sesskey();
    if ($confirm) {
        $DB->delete_records('local_th_sectionmanager_map', ['typeid' => $deleteid]);
        $DB->delete_records('local_th_sectionmanager_type', ['id' => $deleteid]);
        \core\notification::success(get_string('delete_success','local_th_sectionmanager'));
        redirect(new moodle_url('/local/th_sectionmanager/manage_types.php'));
    }
}

$mform = new \local_th_sectionmanager\form\type_form(null, ['editid' => $editid]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_sectionmanager/manage_types.php'));
} 
else if ($data = $mform->get_data()) {
    if (!empty($data->id)) {
        $DB->update_record('local_th_sectionmanager_type', $data);
        \core\notification::success(get_string('update_success','local_th_sectionmanager'));
    } else {
        $newtype = new stdClass();
        $newtype->name = $data->name;
        $newtype->shortname = $data->shortname;
        $DB->insert_record('local_th_sectionmanager_type', $newtype);
        \core\notification::success(get_string('add_new_type', 'local_th_sectionmanager'));
    }
    redirect(new moodle_url('/local/th_sectionmanager/manage_types.php'));
} 
else if ($editid > 0) {
    $record = $DB->get_record('local_th_sectionmanager_type', ['id' => $editid]);
    if ($record) {
        $mform->set_data($record);
    }
}
// ================= GIAO DIỆN =================
echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->heading(get_string('table_section_type', 'local_th_sectionmanager'), 3);

$types = $DB->get_records('local_th_sectionmanager_type', null, 'id DESC');

if ($types) {
    $table = new html_table();
    $table->attributes = ['class' => 'generaltable th_section_table', 'border' => '1', 'style' => 'width:100%; text-align:center;'];
    
    $table->head = array('STT', get_string('type_name', 'local_th_sectionmanager'), get_string('shortname', 'local_th_sectionmanager'), get_string('action', 'local_th_sectionmanager'));
    $table->align = ['center', 'center', 'center', 'center'];
    $stt = 0;
    foreach ($types as $type) {
        $stt = $stt + 1;
        $editicon = $OUTPUT->pix_icon('t/edit', get_string('edit','core'));
        $editurl = new moodle_url('/local/th_sectionmanager/manage_types.php', ['id' => $type->id]);
        $editbtn = html_writer::link($editurl, $editicon, ['title' => get_string('edit','core')]);

        $deleteicon = $OUTPUT->pix_icon('t/delete', get_string('delete','local_th_sectionmanager'));
        $deleteurl = new moodle_url('/local/th_sectionmanager/manage_types.php', ['delete' => $type->id, 'confirm' => 1, 'sesskey' => sesskey()]);
        $deletebtn = html_writer::link($deleteurl, $deleteicon, ['title' => get_string('delete','core'), 'style' => 'margin-left: 15px;', 'onclick' => 'return confirm("'.get_string('delete_confirm','local_th_sectionmanager').'");']);

        $typename_styled = html_writer::tag('span', format_string($type->name));
        
        $table->data[] = array($stt, $typename_styled, format_string($type->shortname), $editbtn . $deletebtn);
    }
    echo html_writer::table($table);
} else {
    echo html_writer::tag('p', get_string('none_section_type','local_th_sectionmanager'), ['style' => 'text-align:center;']);
}

// --- Gọi thư viện nội bộ (local_thlib) ---
$lang = current_language();

echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_section_table', 'Bang_phan_loai_section', $lang));
echo $OUTPUT->footer();