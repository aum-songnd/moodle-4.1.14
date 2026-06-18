<?php

require_once '../../config.php';

global $DB, $CFG, $COURSE, $USER, $SITE;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $error = $DB->get_record('local_th_feedback_report', array('id' => $id));

    if ($error) {
        // Chỉ cập nhật trạng thái bảng chính.
        $data = new stdClass();
        $data->id = $id;
        $data->userid = $error->userid;
        $data->description = $error->description;
        $data->url = $error->url;
        $data->timecreated = $error->timecreated;
        $data->timemodified = time();
        $data->status = $status;

        $DB->update_record('local_th_feedback_report', $data);

        echo get_string('update_status_success', 'local_th_feedback_report');
    } else {
        echo get_string('update_status_error', 'local_th_feedback_report');
    }
}
?>