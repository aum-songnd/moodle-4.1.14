<?php

require_once '../../config.php';

global $DB, $CFG, $COURSE, $USER, $SITE;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $error = $DB->get_record('local_th_feedback_report', array('id' => $id));

    if ($error) {
        // Cập nhật trạng thái
        $data = new stdClass();
        $data->id = $id;
        $data->userid = $error->userid;
        $data->description = $error->description;
        $data->url = $error->url;
        $data->timecreated = $error->timecreated;
        $data->timemodified = time();
        $type1 = isset($_POST['supportType']) ? $_POST['supportType'] : null;
        $type2 = isset($_POST['subcate_' . $type1]) ? $_POST['subcate_' . $type1] : null;
        $data->type = $type2 ? $type2 : $type1;

        $error_reporter_id = $DB->update_record('local_th_feedback_report', $data);
        redirect(new moodle_url('/local/th_feedback_report/index.php'), get_string('update_status_success', 'local_th_feedback_report'), null, \core\notification::SUCCESS);
    } else {
        redirect(new moodle_url('/local/th_feedback_report/index.php'), get_string('update_status_error', 'local_th_feedback_report'), null, \core\notification::ERROR);
    }
}
?>