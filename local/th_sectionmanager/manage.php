<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/th_sectionmanager/classes/form/manage_form.php');
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

global $PAGE, $DB, $OUTPUT;

$courseid = required_param('courseid', PARAM_INT);
$deleteid = optional_param('deleteid', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('local/th_sectionmanager:manage', $context);

$baseurl = new moodle_url('/local/th_sectionmanager/manage.php', array('courseid' => $courseid));
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_title', 'local_th_sectionmanager'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

if ($deleteid > 0) {
    require_sesskey();
    $DB->delete_records('local_th_sectionmanager_map', array('id' => $deleteid, 'courseid' => $courseid));
    \core\notification::success(get_string('delete_success', 'local_th_sectionmanager'));
    redirect($baseurl);
}

$mform = new \local_th_sectionmanager\form\manage_form(null, ['courseid' => $courseid]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
} 
else if ($data = $mform->get_data()) {
    
    $DB->delete_records('local_th_sectionmanager_map', ['courseid' => $courseid]);

    foreach ($data as $key => $values) {
        if (strpos($key, 'type_') === 0 && !empty($values) && is_array($values)) {
            $typeid = (int) str_replace('type_', '', $key);

            foreach ($values as $sectionid) {
                $newrecord = new stdClass();
                $newrecord->courseid = $courseid;
                $newrecord->sectionid = $sectionid;
                $newrecord->typeid = $typeid;
                $DB->insert_record('local_th_sectionmanager_map', $newrecord);
            }
        }
    }
    \core\notification::success(get_string('save_section_success','local_th_sectionmanager'));
    redirect($baseurl);
}

$current_mappings = $DB->get_records('local_th_sectionmanager_map', ['courseid' => $courseid]);
if ($current_mappings) {
    $formdata = new stdClass();
    foreach ($current_mappings as $map) {
        $fieldname = 'type_' . $map->typeid;
        
        if (!isset($formdata->$fieldname)) {
            $formdata->$fieldname = [];
        }
        
        $formdata->$fieldname[] = $map->sectionid;
    }
    $mform->set_data($formdata);
}

echo $OUTPUT->header();

if (function_exists('local_th_sectionmanager_controls')) {
    $tabtree = local_th_sectionmanager_controls($context, $PAGE->url, $courseid);
    if ($tabtree) {
        echo $OUTPUT->render($tabtree);
    }
}

$mform->display();

echo $OUTPUT->heading(get_string('current_mapping', 'local_th_sectionmanager'), 3);

// $sql = "SELECT m.id, s.section, t.name as typename, t.shortname 
//         FROM {local_th_sectionmanager_map} m
//         JOIN {course_sections} s ON m.sectionid = s.id
//         JOIN {local_th_sectionmanager_type} t ON m.typeid = t.id
//         WHERE m.courseid = ?
//         ORDER BY s.section ASC";
// $mappings = $DB->get_records_sql($sql, [$courseid]);

// Sửa SQL thành LEFT JOIN từ bảng course_sections
$sql = "SELECT s.id as sectionid, s.section, t.name as typename, t.shortname, m.id as mapid
        FROM {course_sections} s
        LEFT JOIN {local_th_sectionmanager_map} m ON s.id = m.sectionid
        LEFT JOIN {local_th_sectionmanager_type} t ON m.typeid = t.id
        WHERE s.course = ? AND s.section > 0
        ORDER BY s.section ASC";

$mappings = $DB->get_records_sql($sql, [$courseid]);

if ($mappings) {
    $table = new html_table();
    $table->attributes = [
        'class'  => 'generaltable current_mapping',
        'border' => '1',
        'style'  => 'width:100%; text-align:center;'
    ];
    
    $table->head = array(
        get_string('section_name', 'local_th_sectionmanager'), 
        get_string('section_type', 'local_th_sectionmanager'), 
        get_string('shortname', 'local_th_sectionmanager'),
        get_string('action', 'local_th_sectionmanager')
    );
    
    $table->align = ['center', 'center', 'center', 'center'];
    
    // foreach ($mappings as $map) {
    //     $section_name = get_section_name($courseid, $map->section);
        
    //     $deleteurl = new moodle_url($baseurl, [
    //         'deleteid' => $map->id, 
    //         'sesskey' => sesskey()
    //     ]);
        
    //     $deleteicon = $OUTPUT->pix_icon('t/delete', get_string('delete', 'core'));
    //     $deletebtn = html_writer::link($deleteurl, $deleteicon, [
    //         'title' => get_string('delete', 'core'),
    //         'onclick' => 'return confirm("' . get_string('delete_confirm', 'local_th_sectionmanager') . '");'
    //     ]);
        
    //     $table->data[] = array(
    //         html_writer::tag('span', $section_name), 
    //         $map->typename, 
    //         $map->shortname,
    //         $deletebtn 
    //     );
    // }
    foreach ($mappings as $map) {
        $section_name = get_section_name($courseid, $map->section);
        
        // Nếu mapid trống -> chưa phân loại
        if (empty($map->mapid)) {
            $typename = '-';
            $shortname = '-';
            $deletebtn = '';
        } else {
            $typename = $map->typename;
            $shortname = $map->shortname;
            
            // Chỉ hiện nút xóa cho những section đã phân loại
            $deleteurl = new moodle_url($baseurl, [
                'deleteid' => $map->mapid, 
                'sesskey' => sesskey()
            ]);
            $deleteicon = $OUTPUT->pix_icon('t/delete', get_string('delete', 'core'));
            $deletebtn = html_writer::link($deleteurl, $deleteicon, [
                'title' => get_string('delete', 'core'),
                'onclick' => 'return confirm("' . get_string('delete_confirm', 'local_th_sectionmanager') . '");'
            ]);
        }
        
        $table->data[] = array(
            html_writer::tag('span', $section_name), 
            $typename, 
            $shortname,
            $deletebtn 
        );
    }
    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('none_section_saved','local_th_sectionmanager'), 'warning');
}

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.current_mapping', 'Bang_section_trong_khoa_hoc', $lang));
echo $OUTPUT->footer();