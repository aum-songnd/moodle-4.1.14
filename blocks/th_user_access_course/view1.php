<?php

require_once(__DIR__ . '/../../config.php');
require_once('classes/form/myform1.php');

require_capability('block/th_user_access_course:view', context_course::instance($COURSE->id));

require_login();
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
$PAGE->set_url('/blocks/th_user_access_course/view1.php', $urlparams);
$PAGE->set_pagelayout('standard');


$strviewfeed = get_string('pluginname', 'block_th_user_access_course');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

$managefeeds = new moodle_url('/blocks/th_user_access_course/view1.php', $urlparams);
$PAGE->navbar->add('test', $managefeeds);

echo $OUTPUT->header();

global $DB , $PAGE , $OUTPUT, $COURSE;
$mform = new \block_th_user_access_course\form\myform();
$lang = current_language(); 



if ($mform->is_cancelled()) {
   
} else if ($fromform = $mform->get_data()) {

    if (!empty($fromform->intro)) {
        $mform->display();

        if (!empty($fromform->courseids) && is_array($fromform->courseids)) {
            $lang = current_language();
            foreach ($fromform->courseids as $courseid) {
                $coursecontext = context_course::instance($courseid);
                $course = $DB->get_record('course', ['id' => $courseid], 'fullname');
                
                
                    $table = new html_table();
                   
                    $table->attributes['class'] = 'generaltable';
                    echo html_writer::tag('h3', format_string($course->fullname));
                    $table->head = ['STT', 'Username', 'Fullname' , 'EndTime']; 
                   
                   
                    $stt = 1;
                $studentroleid = 5;
                if (!empty($fromform->userids) && is_array($fromform->userids)) {
                  
                    foreach ($fromform->userids as $userid) {
                        $sql = "SELECT 1
                            FROM {role_assignments}
                            WHERE roleid = :roleid
                              AND contextid = :contextid
                              AND userid = :userid";
                        $isstudent = $DB->record_exists_sql($sql, [
                            'roleid' => $studentroleid,
                            'contextid' => $coursecontext->id,
                            'userid' => $userid
                    ]);

                    if (!$isstudent) {
                        continue; 
                    }
                        $user = $DB->get_record('user', ['id' => $userid],'username, firstname,lastname');
                        
                        if (is_enrolled($coursecontext, $userid)) {
                            $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', [
                                'userid' => $userid,
                                'courseid' => $courseid
                            ]);
                            $endtime = $lastaccess ? userdate($lastaccess) : get_string('never');
                        } else {
                            $endtime = 'Not enrolled';
                        }
                        
                        $table->data[] = [
                            $stt++,
                            s($user->username),
                            s($user->firstname .' '.$user->lastname),
                            s($endtime)
                        ];
                    }
            
                }
                echo html_writer::table($table);
            }

        
        }
      
        
        echo '<style>div.dt-container {
                    margin: 0 auto;
                    width: 80%;
                }</style>';
        
          
        echo '<script>
        $(document).ready(function() {
            $(".generaltable").DataTable({
                dom: "lBfrtip",
                buttons: [      
                ],
                stateSave: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
                },
                
            });
        });
        </script>';
        
        
        echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
        $PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.generaltable', '123', $lang));
    
    }
}

else {
    $mform->display();
}


echo $OUTPUT->footer();