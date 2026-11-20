<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Phục vụ file và hình ảnh trong nội dung câu trả lời.
 *
 * Cấu trúc vùng file:
 * - context: system
 * - component: local_th_qaa_manager
 * - filearea: answer
 * - itemid: ticket id
 *
 * @param stdClass|null $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function local_th_qaa_manager_pluginfile(
    $course,
    $cm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
) {
    global $DB, $USER;

    require_login();

    // File của plugin đang được lưu tại system context.
    if ($context->contextlevel !== CONTEXT_SYSTEM) {
        return false;
    }

    // Chỉ phục vụ file trong vùng answer.
    if ($filearea !== 'answer') {
        return false;
    }

    if (empty($args)) {
        return false;
    }

    /*
     * URL có dạng:
     *
     * /pluginfile.php/{contextid}/local_th_qaa_manager/answer/
     * {ticketid}/{filepath}/{filename}
     *
     * Phần tử đầu tiên của $args là itemid, tức ticket id.
     */
    $ticketid = (int)array_shift($args);

    if ($ticketid <= 0) {
        return false;
    }

    $sql = "SELECT t.id,
                   t.assigneduserid,
                   qp.studentid
              FROM {local_th_qaa_ticket} t
              JOIN {qaapairs} qp
                ON qp.id = t.qaapairid
             WHERE t.id = :ticketid";

    $ticket = $DB->get_record_sql(
        $sql,
        ['ticketid' => $ticketid],
        IGNORE_MISSING
    );

    if (!$ticket) {
        return false;
    }

    $canmanage = has_capability(
        'local/th_qaa_manager:manage',
        $context
    );

    $cananswer = has_capability(
        'local/th_qaa_manager:answer',
        $context
    ) && (int)$ticket->assigneduserid === (int)$USER->id;

    $isstudent = (int)$ticket->studentid === (int)$USER->id;

    if (!$canmanage && !$cananswer && !$isstudent) {
        return false;
    }

    // Sau ticket id phải còn ít nhất tên file.
    if (empty($args)) {
        return false;
    }

    $filename = array_pop($args);

    if ($filename === '' || $filename === null) {
        return false;
    }

    $filepath = '/';

    if (!empty($args)) {
        $filepath .= implode('/', $args) . '/';
    }

    $fs = get_file_storage();

    $file = $fs->get_file(
        $context->id,
        'local_th_qaa_manager',
        'answer',
        $ticketid,
        $filepath,
        $filename
    );

    if (!$file || $file->is_directory()) {
        return false;
    }

    // Không ép tải xuống để ảnh có thể hiển thị trực tiếp trong nội dung.
    send_stored_file(
        $file,
        86400,
        0,
        false,
        $options
    );

    return true;
}

/**
 * Extract user fields with prefix from stdClass record.
 *
 * @param stdClass|array $record
 * @param string $prefix
 * @return stdClass
 */
function local_th_qaa_manager_get_user_from_prefix($record, $prefix) {
    $user = new \stdClass();
    $prefixlen = strlen($prefix);
    foreach ($record as $key => $value) {
        if (strpos($key, $prefix) === 0) {
            $prop = substr($key, $prefixlen);
            $user->$prop = $value;
        }
    }
    return $user;
}

/**
 * Load DataTables CSS, JS assets and initialize DataTable on plugin tables using Moodle Page Requirements API.
 */
function local_th_qaa_manager_require_datatable() {
    global $PAGE;

    $PAGE->requires->jquery();
    $PAGE->requires->js(new moodle_url('https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js'), true);

    $searchlabel = get_string('search_keyword', 'local_th_qaa_manager') . ':';
    $searchplaceholder = get_string('search_keyword_placeholder', 'local_th_qaa_manager');
    $nothingstr = get_string('nothingtodisplay', 'local_th_qaa_manager');

    $jscode = "
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('.th_qaa_manager, .generaltable').each(function() {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            paging: false,
                            info: false,
                            searching: true,
                            ordering: false,
                            autoWidth: false,
                            language: {
                                search: " . json_encode($searchlabel) . ",
                                searchPlaceholder: " . json_encode($searchplaceholder) . ",
                                emptyTable: " . json_encode($nothingstr) . ",
                                zeroRecords: " . json_encode($nothingstr) . "
                            }
                        });
                    }
                });
            }
        });
    ";
    $PAGE->requires->js_init_code($jscode);
}