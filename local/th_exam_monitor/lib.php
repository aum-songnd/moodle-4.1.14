<?php
require_once $CFG->dirroot . '/lib/enrollib.php';
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
define('EXAM_ACTION_WARN', 10);
define('EXAM_ACTION_ALERT', 20);
define('EXAM_ACTION_SUSPEND', 30); 

function local_th_exam_monitor_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('local/th_exam_monitor:view', $context)) {
        $url1 = new moodle_url('/local/th_exam_monitor/view.php', ['id' => $course->id]);
        $navigation->add(
            get_string('studentlist', 'local_th_exam_monitor'),
            $url1,
            navigation_node::TYPE_SETTING,
            null,
            'th_exam_monitor',
            new pix_icon('i/report', '')
        );

        $url2 = new moodle_url('/local/th_exam_monitor/report.php', ['courseid' => $course->id]);
        $navigation->add(
            get_string('report_exam', 'local_th_exam_monitor'),
            $url2,
            navigation_node::TYPE_SETTING,
            null,
            'th_exam_monitor_log',
            new pix_icon('i/log', '')
        );
    }
}

function local_th_exam_monitor_save_action(
    int $userid,
    int $courseid,
    int $action_status,
    object $formdata
) {
    global $DB;

    $context = context_course::instance($courseid);

    // 1️⃣ Insert record trước
    $record = (object)[
        'userid'        => $userid,
        'courseid'      => $courseid,
        'action_status' => $action_status,
        'note'          => '',
        'timecreated'   => time(),
    ];

    $record->id = $DB->insert_record('local_th_exam_monitor', $record);

    // Lưu editor + file
    $formdata = file_postupdate_standard_editor(
        $formdata,
        'note',
        [
            'context' => $context,
            'trusttext' => true,
            'maxfiles' => 10,
        ],
        $context,
        'local_th_exam_monitor',
        'note',
        $record->id
    );

    $DB->update_record('local_th_exam_monitor', [
        'id'   => $record->id,
        'note' => $formdata->note,
    ]);

    // 4️⃣ GIỮ NGUYÊN LOGIC NGHIỆP VỤ CŨ
    switch ($action_status) {
        case EXAM_ACTION_WARN:
        case EXAM_ACTION_ALERT:
            local_th_exam_monitor_send_notification($userid, $courseid, $action_status);
            break;

        case EXAM_ACTION_SUSPEND:
            $attempt = $DB->get_record_sql("
                SELECT qa.*
                FROM {quiz_attempts} qa
                JOIN {quiz} q ON q.id = qa.quiz
                WHERE q.course = :courseid AND qa.userid = :userid AND qa.state = 'inprogress'
                ORDER BY qa.timemodified DESC
                LIMIT 1",
                ['courseid' => $courseid, 'userid' => $userid]);
        
            if ($attempt) {
                $quizobj = quiz::create($attempt->quiz, $userid);
                $attemptobj = quiz_attempt::create($attempt->id);
                $attemptobj->process_finish(time(), false);
            }
        
            $enrolplugin = enrol_get_plugin('manual');
            $manualinstance = $DB->get_record('enrol', [
                'courseid' => $courseid,
                'enrol' => 'manual'
            ], '*', MUST_EXIST);
        
            $enrolplugin->update_user_enrol($manualinstance, $userid, 1);

            local_th_exam_monitor_send_notification($userid, $courseid, EXAM_ACTION_SUSPEND);
            break;
    }

    return $record->id;
}


function local_th_exam_monitor_send_notification($userid, $courseid, $actionstatus) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/message/lib.php');

    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

    $data = (object)[
        'fullname' => fullname($user),
        'coursename' => format_string($course->fullname),
    ];

    switch ($actionstatus) {
        case EXAM_ACTION_WARN:
            $subject = get_string('warn_subject', 'local_th_exam_monitor');
            $messagecontent = get_string('warn_message', 'local_th_exam_monitor', $data);
            $shortmessage = get_string('warn_short', 'local_th_exam_monitor');
            break;

        case EXAM_ACTION_ALERT:
            $subject = get_string('alert_subject', 'local_th_exam_monitor');
            $messagecontent = get_string('alert_message', 'local_th_exam_monitor', $data);
            $shortmessage = get_string('alert_short', 'local_th_exam_monitor');
            break;

        case EXAM_ACTION_SUSPEND:
            $subject = get_string('suspend_notification_subject', 'local_th_exam_monitor');
            $messagecontent = get_string('suspend_notification_message', 'local_th_exam_monitor', $data);
            $shortmessage = get_string('suspend_notification_short', 'local_th_exam_monitor');
            break;
    }

    $eventdata = new \core\message\message();
    $eventdata->component         = 'moodle';
    $eventdata->name              = 'instantmessage';
    $eventdata->userfrom          = \core_user::get_noreply_user();
    $eventdata->userto            = $user;
    $eventdata->subject           = $subject;
    $eventdata->fullmessage       = html_to_text($messagecontent);
    $eventdata->fullmessageformat = FORMAT_HTML;
    $eventdata->fullmessagehtml   = $messagecontent;
    $eventdata->smallmessage      = $shortmessage;
    $eventdata->notification      = 1;

    message_send($eventdata);
}

function local_th_exam_monitor_before_http_headers() {
    global $USER, $_SERVER, $_POST, $DB;

    if (strpos($_SERVER['SCRIPT_NAME'], '/enrol/index.php') !== false) {
        $courseid = optional_param('id', 0, PARAM_INT);

        if($courseid) {
            $sql = "SELECT yt.*
                    FROM {local_th_exam_monitor} yt
                    WHERE yt.userid = $USER->id
                    AND yt.courseid = $courseid
                    AND yt.action_status = 30";
            $records = $DB->get_records_sql($sql);

            if($records) {
                redirect(new moodle_url('/local/th_exam_monitor/suspended.php', ['id' => $courseid]));
            }
        }
    }
}

function get_courseid_from_cmid($cmid) {
    global $DB;
    $cm = get_coursemodule_from_id(null, $cmid, 0, false, MUST_EXIST);
    return $cm->course;
}

function th_exam_monitor_get_enrolled_users(context $context, $wheresql, $withcapability = '', $groupid = 0, $userfields = 'u.*', $orderby = null,
        $limitfrom = 0, $limitnum = 0, $onlyactive = false) {
    global $DB;

    list($esql, $params) = get_enrolled_sql($context, $withcapability, $groupid, $onlyactive);
    $sql = "SELECT $userfields
              FROM {user} u
              JOIN ($esql) je ON je.id = u.id
             WHERE u.deleted = 0 $wheresql";

    if ($orderby) {
        $sql = "$sql ORDER BY $orderby";
    } else {
        list($sort, $sortparams) = users_order_by_sql('u');
        $sql = "$sql ORDER BY $sort";
        $params = array_merge($params, $sortparams);
    }

    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
}

/**
 * Serve files for local_th_exam_monitor plugin.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function local_th_exam_monitor_pluginfile(
    $course,
    $cm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
) {
    // Chỉ cho phép context course
    if ($context->contextlevel !== CONTEXT_COURSE) {
        return false;
    }

    // Chỉ cho phép filearea note
    if ($filearea !== 'note') {
        return false;
    }

    // Bắt buộc đăng nhập
    // require_login($course);

    // itemid = record id
    $itemid = array_shift($args);
    if (!$itemid) {
        return false;
    }

    $filename = array_pop($args);
    $filepath = '/' . implode('/', $args);
    if ($filepath === '/') {
        $filepath = '/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file(
        $context->id,
        'local_th_exam_monitor',
        'note',
        $itemid,
        $filepath,
        $filename
    );

    if (!$file || $file->is_directory()) {
        return false;
    }

    // Gửi file
    send_stored_file($file, 0, 0, $forcedownload, $options);
}
