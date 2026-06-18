<?php

require_once('../../config.php');

require_login();
require_sesskey();

$filename = required_param('file', PARAM_RAW); 
$filepath = $CFG->dataroot . '/quiz_export/' . $filename;

if (!file_exists($filepath)) {
    print_error('File not found: ' . $filename);
}

while (ob_get_level()) {
    ob_end_clean();
}

// Thiết lập header để tải file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Expires: 0');

flush();
readfile($filepath);
exit;
