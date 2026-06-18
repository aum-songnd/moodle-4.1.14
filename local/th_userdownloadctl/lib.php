<?php
defined('MOODLE_INTERNAL') || die();

function local_th_userdownloadctl_before_http_headers() {
    global $SCRIPT;

    if ($SCRIPT !== '/admin/user/user_bulk.php') {
        return;
    }

    if (!get_config('local_th_userdownloadctl', 'enable')) {
        return;
    }

    $context = context_system::instance();
    if (has_capability('local/th_userdownloadctl:download', $context)) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && strtolower(optional_param('action', '', PARAM_ALPHANUMEXT)) === 'download') {
        redirect(
            new moodle_url('/admin/user/user_bulk.php'),
            get_string('nopermission', 'local_th_userdownloadctl'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_start('local_th_userdownloadctl_strip_download_option');
    }
}

function local_th_userdownloadctl_strip_download_option($html) {
    return preg_replace('/<option[^>]*value=["\']download["\'][^>]*>.*?<\/option>/is', '', $html);
}
