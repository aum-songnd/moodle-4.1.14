<?php

require_once(__DIR__ . '/../../config.php');
require_once('classes/form/myform2.php');

global $DB , $PAGE , $OUTPUT, $COURSE, $USER;


require_login();
// require_capability('block/th_user_access_course:view', context_course::instance($COURSE->id));
require_capability('block/th_user_access_course:teacher', context_course::instance($COURSE->id));
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
$PAGE->set_url('/blocks/th_user_access_course/view2.php', $urlparams);
$PAGE->set_pagelayout('standard');


$strviewfeed = get_string('tittle', 'block_th_user_access_course');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

$managefeeds = new moodle_url('/blocks/th_user_access_course/view2.php', $urlparams);
$PAGE->navbar->add(get_string('reset','block_th_user_access_course'), $managefeeds);

echo $OUTPUT->header();



$mform = new \block_th_user_access_course\form\myform2();
$lang = current_language(); 


if ($mform->is_cancelled()) {
    
} else if ($fromform = $mform->get_data()) {

    if (!empty($fromform->intro)) {
        $mform->display();
        require_once($CFG->dirroot . '/local/message/lib.php');
        local_success();
        
        if (!empty($fromform->courseids) && is_array($fromform->courseids)) {
            foreach ($fromform->courseids as $courseid) {
                $coursecontext = context_course::instance($courseid);
    
                // Lấy tên khóa học
                $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    
                // Lấy danh sách người dùng ghi danh
                $users = $DB->get_records_sql("
                    SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.username, ue.timeend
                    FROM {user} u
                    JOIN {user_enrolments} ue ON u.id = ue.userid
                    JOIN {enrol} e ON ue.enrolid = e.id
                    WHERE e.courseid = :idcourse AND e.status = 0
                    ORDER BY u.id ASC
                ", ['idcourse' => $courseid]);
                
                if (!empty($users)) {
                    $table = new html_table();
                    $table->attributes['class'] = 'generaltable';
                    echo html_writer::tag('h3', "<a href='$CFG->wwwroot/course/view.php?id=$courseid'>$course->fullname</a>");
                    $table->head = ['STT', 'Username', 'Fullname', 'EndTime','Role'];
    
                    $stt = 1;
                    if (!empty($fromform->roleids) && is_array($fromform->roleids)) {
                        foreach ($fromform->roleids as $roleid) {
                            $studentroleid = $roleid;
                            foreach ($users as $user) {
                                
                                $sql = "SELECT 1
                                    FROM {role_assignments}
                                    WHERE roleid = :roleid
                                    AND contextid = :contextid
                                    AND userid = :userid";
                                $isstudent = $DB->record_exists_sql($sql, [
                                    'roleid' => $studentroleid,
                                    'contextid' => $coursecontext->id,
                                    'userid' => $user->id
                            ]);

                            if (!$isstudent) {
                                continue; 
                            }
                                
                                if (is_enrolled($coursecontext, $user->id)) {
                                    $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', [
                                        'userid' => $user->id,
                                        'courseid' => $courseid
                                    ]);
                                    $endtime = $lastaccess ? userdate($lastaccess) : get_string('never');
                                } else {
                                    $endtime = 'not enrol';
                                }

                            
                                $table->data[] = [
                                    $stt++,
                                    "<a href='$CFG->wwwroot/user/view.php?id=$user->id&course=$courseid'>$user->username</a>",
                                    s($user->fullname),
                                    s($endtime),
                                    s($roleid)
                                ];
                        
                            }
                                }

                            }
                    
                    echo html_writer::table($table);
                } else {
                    echo html_writer::tag('p', "Không có người dùng ghi danh trong khóa học: " . format_string($course->fullname));
                }
            }
        }
        if (!empty($fromform->userids) && is_array($fromform->userids)) {
            foreach ($fromform->userids as $userid) {
                $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        
                // Lấy danh sách khóa học mà người dùng đã ghi danh
                $courses = $DB->get_records_sql("
                    SELECT c.id, c.fullname
                    FROM {course} c
                    JOIN {enrol} e ON e.courseid = c.id
                    JOIN {user_enrolments} ue ON ue.enrolid = e.id
                    WHERE ue.userid = :userid AND c.category <> 0
                    ORDER BY c.id ASC
                ", ['userid' => $userid]);
        
                if (!empty($courses)) {
                    echo html_writer::tag('h3',"<a href='$CFG->wwwroot/user/view.php?id=$userid'>$user->username</a>");
        
                    $table = new html_table();
                    $table->attributes['class'] = 'generaltable';
                    $table->head = ['STT', 'Course ID', 'Course Name','Endtime'];
        
                    $stt = 1;
        
                    if (!empty($fromform->roleids) && is_array($fromform->roleids)) {
                        foreach ($courses as $course) {
                            $coursecontext = context_course::instance($course->id);
        
                            foreach ($fromform->roleids as $roleid) {
                                // Kiểm tra xem người dùng có vai trò này trong context khóa học không
                                $sql = "SELECT 1
                                        FROM {role_assignments}
                                        WHERE roleid = :roleid
                                          AND contextid = :contextid
                                          AND userid = :userid";
                                $params = [
                                    'roleid' => $roleid,
                                    'contextid' => $coursecontext->id,
                                    'userid' => $userid
                                ];
        
                                $hasrole = $DB->record_exists_sql($sql, $params);
                                if (is_enrolled($coursecontext, $userid)) {
                                    $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', [
                                        'userid' => $userid,
                                        'courseid' => $course->id
                                    ]);
                                    $endtime = $lastaccess ? userdate($lastaccess) : get_string('never');
                                } else {
                                    $endtime = 'not enrol';
                                }
                            
                                if ($hasrole) {
                                    $table->data[] = [
                                        $stt++,
                                        s($course->id),
                                        "<a href='$CFG->wwwroot/course/view.php?id=$course->id'>$course->fullname</a>",
                                        s($endtime)
                                    ];
                                    break; // tránh nhân bản dòng nếu user có nhiều role trong khóa đó
                                }
                            }
                        }
                    }
        
                    if (!empty($table->data)) {
                        echo html_writer::table($table);
                    } else {
                        echo html_writer::tag('p', "Người dùng không có vai trò được chọn trong bất kỳ khóa học nào.");
                    }
        
                } else {
                    echo html_writer::tag('p', "Người dùng không ghi danh vào khóa học nào.");
                }
            }
        }
        echo '<script>
    setTimeout(function() {
        var notifs = document.querySelectorAll(".alert, .alert-success, .alert-warning, .alert-danger, .alert-info, .notifyproblem, .notifysuccess, .notifymessage");

        notifs.forEach(function(notif) {
            notif.style.transition = "opacity 1s ease-out";
            notif.style.opacity = "0";

            setTimeout(function() {
                notif.style.display = "none";
            }, 1000);
        });
    }, 3000);
</script>';
    }}
else {
    $mform->display();
}



echo $OUTPUT->footer();