<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();

if (has_capability('local/th_qaa_manager:manage', $context)) {
    redirect(new moodle_url('/local/th_qaa_manager/manage.php'));
}

if (has_capability('local/th_qaa_manager:answer', $context)) {
    redirect(new moodle_url('/local/th_qaa_manager/question.php'));
}

throw new required_capability_exception($context, 'local/th_qaa_manager:answer', 'nopermissions', '');