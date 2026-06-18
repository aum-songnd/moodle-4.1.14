<?php

require_once(__DIR__ . '/../../config.php');
require_once('classes/form/myform.php');

global $DB, $OUTPUT, $PAGE;

require_login();
require_capability('block/plugin01:view8', context_course::instance($COURSE->id));
if (isguestuser()) {
    print_error('guestsarenotallowed');
}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INT);


if ($courseid == SITEID) {
    $courseid = 0;
}

if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$urlparams = array('rssid' => 1);
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}

$PAGE->set_url('/blocks/plugin01/view8.php', $urlparams);
$PAGE->set_pagelayout('standard');

$strviewfeed = get_string('viewfeed', 'block_plugin01');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

$managefeeds = new moodle_url('/blocks/plugin01/view8.php', $urlparams);
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('managefeeds', 'block_plugin01'), $managefeeds);
// $PAGE->navbar->add($strviewfeed);


echo $OUTPUT->header();

$mform = new \block_plugin01\form\myform();

$now = time();
echo userdate($now);
$lang = current_language(); 
if ($mform->is_cancelled()) {
    // redirect(new moodle_url('/blocks/plugin01/view9.php'), 'Đã hủy thao tác', 5);
} else if ($fromform = $mform->get_data()) {

    if (!empty($fromform->intro)) {
        $mform->display();

        if (!empty($fromform->courseids) && is_array($fromform->courseids)) {
            $lang = current_language();
            $table = new html_table();
            $table->id = 'mydatatable'; // ✅ ID cho DataTables
            $table->head = ['STT', 'Userid', 'ND']; // ✅ Dùng head thay vì dòng header thủ công
            $table->attributes['class'] = 'generaltable'; // Moodle style
        
            $stt = 1;
            foreach ($fromform->courseids as $courseid) {
                $course = $DB->get_records('course', ['id' => $courseid]);
        
                if (enrol_is_enabled('manual')) {
                    $instances = enrol_get_instances($courseid, false);
                    $manualinstance = null;
                    foreach ($instances as $instance) {
                        if ($instance->enrol === 'manual') {
                            $manualinstance = $instance;
                            break;
                        }
                    }
                    if ($manualinstance) {
                        $manualenrol = enrol_get_plugin('manual');
                        $coursecontext = context_course::instance($courseid);
                    }
                }
        
                if (!empty($fromform->userids) && is_array($fromform->userids)) {
                    foreach ($fromform->userids as $userid) {
                        $user = $DB->get_records('user', ['id' => $userid]);
        
                        if ($fromform->yesno == 1) { // đăng ký
                            if ($coursecontext and !empty($fromform->roleids) && is_array($fromform->roleids)) {
                                foreach ($fromform->roleids as $roleid) {
                                    $manualenrol->enrol_user($manualinstance, $userid, $roleid);
                                    foreach ($user as $item) {
                                        $table->data[] = [
                                            $stt++,
                                            s($item->id),
                                            s("Vai trò $roleid được gán với user $userid vào khóa học $courseid")
                                        ];
                                    }
                                }
                            }
                        } else if ($fromform->yesno == 0) { // hủy đăng ký
                            $manualenrol->unenrol_user($manualinstance, $userid);
                            foreach ($user as $item) {
                                $table->data[] = [
                                    $stt++,
                                    s($item->id),
                                    s("User $userid đã xóa khỏi khóa học $courseid")
                                ];
                            }
                        }
                    }
                }
            }
        
            echo html_writer::table($table);

            echo '<style>div.dt-container {
                    margin: 0 auto;
                    width: 80%;
                }</style>';
        
            echo '<script>
            $(document).ready(function() {
                $("#mydatatable").DataTable({
                    dom: "lBfrtip",
                    buttons: [
                        {   
                            extend: "csvHtml5",
                            text: "Xuất CSV",
                            title: "baocao_enrol_users"
                        },
                        {   
                            extend: "excelHtml5",
                            text: "Xuất Excel",
                            title: "baocao_enrol_users"
                        },
                                        
                    ],
                    stateSave: true,
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
                    },
                    
                });
            });
        </script>';
        
        }
        echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
        $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('#mydatatable', '123', $lang));
        
    }


} else {
    $mform->display();
}

echo $OUTPUT->footer();