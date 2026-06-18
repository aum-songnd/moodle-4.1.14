<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Thêm mapping mã môn
 */
function th_framework_subjectcode_add(string $currentcode, string $oldcode) {
    global $DB;

    if ($DB->record_exists('th_subject_code_mapping', [
        'current_code' => $currentcode,
        'old_code'     => $oldcode
    ])) {
        return false;
    }

    $record = (object)[
        'current_code' => $currentcode,
        'old_code'     => $oldcode,
        'timecreated'  => time()
    ];

    return $DB->insert_record('th_subject_code_mapping', $record);
}

/**
 * Cập nhật mapping
 */
function th_framework_subjectcode_update(int $id, string $currentcode, string $oldcode): bool {
    global $DB;

    if (!$DB->record_exists('th_subject_code_mapping', ['id' => $id])) {
        return false;
    }

    $sql = "SELECT 1
              FROM {th_subject_code_mapping}
             WHERE current_code = :current
               AND old_code = :old
               AND id <> :id";

    if ($DB->record_exists_sql($sql, [
        'current' => $currentcode,
        'old'     => $oldcode,
        'id'      => $id
    ])) {
        return false;
    }

    $record = (object)[
        'id'           => $id,
        'current_code' => $currentcode,
        'old_code'     => $oldcode
    ];

    return $DB->update_record('th_subject_code_mapping', $record);
}

/**
 * Xóa mapping
 */
function th_framework_subjectcode_delete(int $id): bool {
    global $DB;

    return $DB->delete_records('th_subject_code_mapping', ['id' => $id]);
}

/**
 * Lấy danh sách mapping (phục vụ quản trị)
 */
function th_framework_subjectcode_get_list(?string $currentcode = null): array {
    global $DB;

    if ($currentcode) {
        return $DB->get_records(
            'th_subject_code_mapping',
            ['current_code' => $currentcode],
            'timecreated DESC'
        );
    }

    return $DB->get_records(
        'th_subject_code_mapping',
        null,
        'current_code, timecreated DESC'
    );
}

function local_th_subjectcode_tabs(context $context, moodle_url $currenturl) {
    global $OUTPUT;

    $tabs = [];
    $currenttab = '';

    // Tab 1: Danh sách tất cả mapping.
    if (has_capability('local/th_framework_study:manageexemption', $context)) {
        $allurl = new moodle_url('/local/th_framework_study/subjectcode/manage.php');
        $tabs[] = new tabobject('all', $allurl, get_string('allframeworks', 'local_th_framework_study'));
        if ($currenturl->get_path() === $allurl->get_path()) {
            $currenttab = 'all';
        }
    }

    // Tab 2: Thêm mới mapping
    if (has_capability('local/th_framework_study:manageexemption', $context)) {
        $addurl = new moodle_url('/local/th_framework_study/subjectcode/edit.php');
        $tabs[] = new tabobject('add', $addurl, get_string('addnewframework', 'local_th_framework_study'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'add';
        }
    }

    // Tab 3: Thêm theo lô (upload excel).
    if (has_capability('local/th_framework_study:manageexemption', $context)) {
        $batchurl = new moodle_url('/local/th_framework_study/subjectcode/upload.php');
        $tabs[] = new tabobject('batch', $batchurl, get_string('batchupload', 'local_th_framework_study'));
        if ($currenturl->get_path() === $batchurl->get_path()) {
            $currenttab = 'batch';
        }
    }

    // Hiển thị thanh tab.
    if (!empty($tabs)) {
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    return null;
}
