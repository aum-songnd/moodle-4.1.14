<?php

require_once(__DIR__ . '/../../../../config.php');

function thessaygrader_question_pluginfile($course, $context, $component,
        $filearea, $qubaid, $slot, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    $attemptobj = quiz_attempt::create_from_usage_id($qubaid);
    //require_login($attemptobj->get_course(), false, $attemptobj->get_cm());

    if ($attemptobj->is_own_attempt() && !$attemptobj->is_finished()) {
        // In the middle of an attempt.
        if (!$attemptobj->is_preview_user()) {
            $attemptobj->require_capability('mod/quiz:attempt');
        }
        $isreviewing = false;
    } else {
        // Reviewing an attempt.
        $attemptobj->check_review_capability();
        $isreviewing = true;
    }

    // if (!$attemptobj->check_file_access($slot, $isreviewing, $context->id,
    //         $component, $filearea, $args, $forcedownload)) {
    //     send_file_not_found();
    // }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/$component/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

$attempt_secret_key = get_config('quiz_thessaygrader', 'attempt_secret_key');
$attempt_secret_iv = get_config('quiz_thessaygrader', 'attempt_secret_iv');
// Key bí mật bạn giữ an toàn
define('ATTEMPT_SECRET_KEY', $attempt_secret_key);
define('ATTEMPT_SECRET_IV', $attempt_secret_iv);

function encrypt_attempt_id($attemptid) {
    $key = hash('sha256', ATTEMPT_SECRET_KEY);
    $iv = substr(hash('sha256', ATTEMPT_SECRET_IV), 0, 16);
    return base64_encode(openssl_encrypt($attemptid, 'AES-256-CBC', $key, 0, $iv));
}

function decrypt_attempt_token($token) {
    $key = hash('sha256', ATTEMPT_SECRET_KEY);
    $iv = substr(hash('sha256', ATTEMPT_SECRET_IV), 0, 16);
    return openssl_decrypt(base64_decode($token), 'AES-256-CBC', $key, 0, $iv);
}