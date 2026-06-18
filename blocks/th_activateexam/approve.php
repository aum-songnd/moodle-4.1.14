<?php
require('../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$action   = required_param('action', PARAM_ALPHA);

$course  = get_course($courseid);
$context = context_course::instance($courseid);
require_capability('block/th_activateexam:active', $context);

$quizzes = $DB->get_records('quiz', ['course' => $courseid]);

foreach ($quizzes as $quiz) {
    $cm = get_coursemodule_from_instance('quiz', $quiz->id, $courseid);

    if (!$cm) {
        continue;
    }

    if ($action === 'approve') {
        if (!empty($cm->availability)) {
            $oldavailability = $cm->availability;

            // gỡ bỏ giới hạn truy cập
            $DB->set_field('course_modules', 'availability', '', ['id' => $cm->id]);

            // ghi log cho hành động approve
            $record = (object)[
                'courseid'       => $courseid,
                'quizid'         => $quiz->id,
                'userid'         => $USER->id,
                'action'         => $action,
                'timecreated'    => time(),
                'oldavailability'=> $oldavailability
            ];
            $DB->insert_record('block_th_activateexam_log', $record);
        }
    } else if ($action === 'unapprove') {
        $approves = $DB->get_records_select(
            'block_th_activateexam_log',
            'courseid = :courseid AND quizid = :quizid AND action = :action',
            ['courseid' => $courseid, 'quizid' => $quiz->id, 'action' => 'approve'],
            'timecreated DESC',
            '*',
            0, 1
        );
        $lastapprove = reset($approves);

        if (!$lastapprove || empty($lastapprove->oldavailability)) {
            continue;
        }

        // lấy availability hiện tại trong DB
        $currentavailability = $DB->get_field('course_modules', 'availability', ['id' => $cm->id]);

        // nếu availability hiện tại khác availability đã lưu trong log thì cập nhật log
        if ($currentavailability !== '' && $currentavailability !== $lastapprove->oldavailability) {
            $lastapprove->oldavailability = $currentavailability;
            $DB->update_record('block_th_activateexam_log', $lastapprove);
        }
        $availability = json_decode($lastapprove->oldavailability, true);
        if ($availability === null) {
            continue;
        }

        $DB->set_field('course_modules', 'availability', json_encode($availability), ['id' => $cm->id]);

        // ghi log 
        $record = (object)[
            'courseid'    => $courseid,
            'quizid'      => $quiz->id,
            'userid'      => $USER->id,
            'action'      => $action,
            'timecreated' => time()
        ];
        $DB->insert_record('block_th_activateexam_log', $record);
    }

}

// Làm mới cache của khóa học
rebuild_course_cache($courseid);

// Chuyển hướng về trang khóa học
redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
