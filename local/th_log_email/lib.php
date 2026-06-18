<?php

defined('MOODLE_INTERNAL') || die();

function th_log_email($date, $sender, $receiver, $subject, $body)
{
    global $DB;

    if($sender == null){
        $sender = " ";
    }

    $dataobjects = new stdClass();
    $dataobjects->date = $date;
    $dataobjects->sender = $sender;
    $dataobjects->receiver = $receiver;
    $dataobjects->subject = $subject;
    $dataobjects->body = $body;

    $DB->insert_record('local_th_log_email', $dataobjects, false);
}
?>