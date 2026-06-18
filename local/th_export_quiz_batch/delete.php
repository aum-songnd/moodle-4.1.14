<?php

require_once('../../config.php');
require_login();
require_sesskey();

$id = required_param('id', PARAM_INT);
$record = $DB->get_record('local_th_export_quiz_batch', ['id' => $id], '*', MUST_EXIST);

require_capability('local/th_export_quiz_batch:view', context_system::instance());

if ($record->filepath && file_exists($record->filepath)) {
    unlink($record->filepath);
}

$DB->delete_records('local_th_export_quiz_batch', ['id' => $id]);

redirect(new moodle_url('/local/th_export_quiz_batch/view.php', ['tab' => 'exported']));
