<?php

namespace mod_thurl\task;

require_once $CFG->dirroot . '/mod/thurl/classes/th_messagelib.php';
require_once $CFG->dirroot . '/mod/thurl/classes/th_manager.php';


defined('MOODLE_INTERNAL') || die();

class send_notification extends \core\task\scheduled_task {

    public static function send_notification() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/message/lib.php');
        
        $hour = get_config('thurl', 'scheduled_hour');
        $minute = get_config('thurl', 'scheduled_minute');
        $date = date("d/m/Y");
        $turnoff =  get_config('thurl', 'enable_taskschedule');
        mtrace(get_string('task_started', 'thurl'));
        
        if (!$turnoff) {
            mtrace(get_string('task_turned_off', 'thurl'));
            return;
        }

        $sql = "SELECT cm.id, c.id AS courseid, c.fullname AS coursename, cm.id AS cmid, cm.availability, u.externalurl as url
                FROM {course} c
                JOIN {course_modules} cm ON cm.course = c.id
                JOIN {modules} m ON m.id = cm.module
                JOIN {thurl} u ON u.id = cm.instance
                WHERE m.name = 'thurl' 
                AND u.name LIKE :urlname";
        $params = [
            'urlname' => '%lớp học trực tuyến%'
        ];
    
        $courses = $DB->get_records_sql($sql, $params);
    
        if (!$courses) {
            mtrace(get_string('no_online_class', 'thurl'));
            return;
        }
    
        $title = get_string('eteaching_class_notification', 'thurl');
        $user_from = \core_user::get_support_user();

        $student_role = $DB->get_field('role', 'id', ['shortname' => 'student']);
        if (!$student_role) {
            mtrace(get_string('role_not_found_student', 'thurl'));
            return;
        }
    
        foreach ($courses as $course) {
            $availability = json_decode($course->availability);
    
            if (isset($availability->c)) {
                $start_time = null;
                foreach ($availability->c as $condition) {
                    if ($condition->type === 'date' && $condition->d === '>=') {
                        $start_time = $condition->t;
                        $date_course = date('d/m/Y', $start_time);
                        $hour_course = date('H', $start_time);
                        $minute_course = date('i', $start_time);
                    }
                }
                if($date == $date_course && $hour == $hour_course && $minute == $minute_course){
                    $coursecontext = \context_course::instance($course->courseid);
                    $students = get_enrolled_users($coursecontext, 'mod/url:view');
                    //Lọc chỉ lấy học viên (roleid = 5)
                    $filtered_students = [];
                    foreach ($students as $student) {
                        $roleassignments = $DB->get_records('role_assignments', ['userid' => $student->id, 'contextid' => $coursecontext->id]);
                        foreach ($roleassignments as $roleassignment) {
                            if ($roleassignment->roleid == $student_role) {
                                $filtered_students[] = $student;
                                break;
                            }
                        }
                    }
                    foreach ($filtered_students as $user_to) {
                        $url = new \moodle_url('/course/view.php', array('id' => $course->courseid));
                        $a = new \stdClass();
                        $start_time_plus_30 = strtotime('+30 minutes', $start_time);
                        $a->date = date('d/m/Y', $start_time);
                        $a->course = $course->coursename;
                        $a->time_30 = date('H:i', $start_time_plus_30);
                        $a->time = date('H:i', $start_time);
                        $a->url = $course->url;
                        $content1 = get_string('notificationmessage', 'mod_thurl', $a);
                        $content1_html = str_replace("\n", "<br>", $content1);
                        // Tạo dữ liệu thông báo Moodle
                        $eventdata = new \core\message\message();
                        $eventdata->component         = 'moodle';
                        $eventdata->name              = 'instantmessage';
                        $eventdata->userfrom          = $user_from;
                        $eventdata->userto            = $user_to;
                        $eventdata->subject           = $title;
                        $eventdata->fullmessage = get_string('fullmessage_with_url', 'thurl', (object)[
                            'content' => $content1,
                            'url' => $url
                        ]);                        
                        $eventdata->fullmessageformat = FORMAT_MARKDOWN;
                        $eventdata->fullmessagehtml = get_string('fullmessagehtml_with_url', 'thurl', (object)[
                            'content' => $content1_html,
                            'url' => $url
                        ]);                        
                        $eventdata->smallmessage = get_string('smallmessage_online_class', 'thurl');
                        $eventdata->notification      = 1;
                        $eventdata->contexturl        = $url;
                        $eventdata->contexturlname = get_string('contexturlname_online_class', 'thurl');
                        
                        // Tiêu đề và nội dung email
                        $email_subject = $title;
                        $email_content = $content1;
                 
                        //Gửi thông báo qua Moodle
                        $messageid = message_send2($eventdata);
                        if ($messageid) {
                            mtrace(get_string('notification_sent', 'thurl', $user_to->email));
                        } else {
                            mtrace(get_string('notification_failed', 'thurl', $user_to->email));
                        }
                        // Gửi email
                        if (email_to_user($user_to, $user_from, $email_subject, $email_content)) {
                            mtrace(get_string('email_sent', 'thurl', $user_to->email));
                        } else {
                            mtrace(get_string('email_send_failed', 'thurl', $user_to->email));
                        }
                    }
                }
            }
        }
    }

    public function get_name() {
        return get_string('remind_student_online_class', 'thurl');
    }

    public function execute() {
        self::send_notification();
    }
}
