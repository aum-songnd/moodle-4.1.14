<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Hiển thị các tab cho trang quản lý khung chương trình học.
 *
 * @param context $context Ngữ cảnh hệ thống.
 * @param moodle_url $currenturl URL hiện tại của trang.
 */
function local_th_framework_study_tabs(context $context, moodle_url $currenturl) {
    global $OUTPUT;

    $tabs = [];
    $currenttab = '';

    // Tab 1: Danh sách tất cả khung chương trình.
    if (has_capability('local/th_framework_study:manageframeworkstudy', $context)) {
        $allurl = new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php');
        $tabs[] = new tabobject('all', $allurl, get_string('allframeworks', 'local_th_framework_study'));
        if ($currenturl->get_path() === $allurl->get_path()) {
            $currenttab = 'all';
        }
    }

    // Tab 2: Thêm mới khung chương trình.
    if (has_capability('local/th_framework_study:manageframeworkstudy', $context)) {
        $addurl = new moodle_url('/local/th_framework_study/manageframeworkstudy/edit.php');
        $tabs[] = new tabobject('add', $addurl, get_string('addnewframework', 'local_th_framework_study'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'add';
        }
    }

    // Tab 3: Thêm theo lô (upload excel).
    if (has_capability('local/th_framework_study:manageframeworkstudy', $context)) {
        $batchurl = new moodle_url('/local/th_framework_study/manageframeworkstudy/upload.php');
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

function local_th_exemption_tabs(context $context, moodle_url $currenturl) {
    global $OUTPUT;

    $tabs = [];
    $currenttab = '';

    // Tab 1: Danh sách tất cả khung chương trình.
    if (has_capability('local/th_framework_study:manageexemption', $context)) {
        $allurl = new moodle_url('/local/th_framework_study/manageexemption/manage.php');
        $tabs[] = new tabobject('all', $allurl, get_string('allframeworks', 'local_th_framework_study'));
        if ($currenturl->get_path() === $allurl->get_path()) {
            $currenttab = 'all';
        }
    }

    // Tab 2: Thêm mới khung chương trình.
    if (has_capability('local/th_framework_study:manageexemption', $context)) {
        $addurl = new moodle_url('/local/th_framework_study/manageexemption/edit.php');
        $tabs[] = new tabobject('add', $addurl, get_string('addnewframework', 'local_th_framework_study'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'add';
        }
    }

    // Tab 3: Thêm theo lô (upload excel).
    if (has_capability('local/th_framework_study:manageexemption', $context)) {
        $batchurl = new moodle_url('/local/th_framework_study/manageexemption/upload.php');
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

/**
 * Lấy giá trị custom profile field của user theo shortname.
 */
function th_get_user_profile_field_value(int $userid, string $shortname): ?string {
    global $DB;
    $sql = "SELECT d.data
              FROM {user_info_data} d
              JOIN {user_info_field} f ON f.id = d.fieldid
             WHERE d.userid = :uid AND f.shortname = :sname";
    return $DB->get_field_sql($sql, ['uid' => $userid, 'sname' => $shortname]) ?: null;
}

/**
 * Lấy danh sách môn theo danh sách cặp (major_code, target_code [, training_type_code]).
 *
 * NOTE: Nếu bật setting skip_slot_target thì sẽ:
 * - KHÔNG bắt buộc target_code
 * - Query KHÔNG filter target_code
 * - Order KHÔNG dựa vào slot
 */

function th_load_framework_for_pairs(array $pairs, $enable_train_code): array {
    global $DB;

    $all = [];
    $seen = [];

    if ($enable_train_code) {
        $major = $pairs[0] ?? '';
        $target = $pairs[1] ?? '';
        $train = $pairs[2] ?? '';

        // Khi có training_type_code thì major + train là tối thiểu
        if (empty($major) || empty($train)) {
            return $all;
        }

        // Câu điều kiện cơ bản
        $conditions = [
            'major_code' => $major,
            'training_type_code' => $train,
        ];

        // Lọc target_code nếu có và target_code = ''
        if (!empty($target)) {
            // Thêm điều kiện lọc target_code = $target và target_code = ''
            $sql_condition = "AND (target_code = :target OR target_code = '')";
            $params = ['target' => $target];
        } else {
            $sql_condition = "AND target_code = ''"; // Chỉ lọc target_code trống
            $params = [];
        }

        // Sắp xếp không cần điều kiện skip
        $sort = 'subject_code ASC, subject_name ASC, id ASC';

        // Truy vấn với điều kiện SQL bổ sung
        $sql = "SELECT * FROM {th_framework_study} WHERE major_code = :major_code AND training_type_code = :training_type_code $sql_condition ORDER BY $sort";
        $params = array_merge($params, [
            'major_code' => $major,
            'training_type_code' => $train,
        ]);
        $recs = $DB->get_records_sql($sql, $params);

    } else {
        $major = $pairs[0] ?? '';
        $target = $pairs[1] ?? '';

        // Khi không có training_type_code thì major là tối thiểu
        if (empty($major)) {
            return $all;
        }

        // Câu điều kiện cơ bản
        $conditions = [
            'major_code' => $major,
        ];

        // Lọc target_code nếu có và target_code = ''
        if (!empty($target)) {
            // Thêm điều kiện lọc target_code = $target và target_code = ''
            $sql_condition = "AND (target_code = :target OR target_code = '')";
            $params = ['target' => $target];
        } else {
            $sql_condition = "AND target_code = ''"; // Chỉ lọc target_code trống
            $params = [];
        }

        // Sắp xếp không cần điều kiện skip
        $sort = 'subject_code ASC, subject_name ASC, id ASC';

        // Truy vấn với điều kiện SQL bổ sung
        $sql = "SELECT * FROM {th_framework_study} WHERE major_code = :major_code $sql_condition ORDER BY $sort";
        $params = array_merge($params, [
            'major_code' => $major,
        ]);
        $recs = $DB->get_records_sql($sql, $params);
    }

    // Dedupe theo subject_code (giữ logic cũ)
    foreach ($recs as $r) {
        $code = (string)($r->subject_code ?? '');
        if ($code === '') {
            // Nếu DB có record thiếu subject_code thì bỏ qua
            continue;
        }
        if (!isset($seen[$code])) {
            $all[] = $r;
            $seen[$code] = true;
        }
    }

    return $all;
}


/**
 * Kiểm tra user đã enrol vào course chưa.
 */
function th_is_user_enrolled_in_course(int $userid, int $courseid): bool {
    $context = context_course::instance($courseid, IGNORE_MISSING);
    if (!$context) {
        return false;
    }
    return is_enrolled($context, $userid, '', true);
}

/**
 * Lấy khóa học chính (KHÔNG thuộc thư viện học liệu) theo mã môn + user.
 */
function th_framework_get_main_course_for_subject(
    int $fieldid,
    string $subjectcode,
    int $userid,
    string $category_ids_str
) {
    global $DB;

    $subjectcodes = th_framework_subjectcode_get_all_codes($subjectcode);

    list($insql, $params) = $DB->get_in_or_equal($subjectcodes, SQL_PARAMS_NAMED);

    $where_in = "AND c.category NOT IN ($category_ids_str)";
    $compare  = $DB->sql_compare_text('d.value') . " $insql";

    $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber, c.startdate, c.visible
              FROM {customfield_data} d
              JOIN {course} c ON c.id = d.instanceid
              JOIN {enrol} e ON e.courseid = c.id
              JOIN {user_enrolments} ue ON ue.enrolid = e.id
             WHERE d.fieldid = :fid
               AND {$compare}
               AND ue.userid = :userid
               $where_in
          ORDER BY c.startdate DESC, c.id DESC LIMIT 1";

    $params['fid']    = $fieldid;
    $params['userid'] = $userid;

    return $DB->get_record_sql($sql, $params, IGNORE_MISSING);
}

/**
 * Lấy khóa học trong thư viện học liệu (TVHL) theo mã môn.
 * Chỉ lấy khóa visible = 1 và thuộc cây danh mục TVHL.
 */
function th_framework_get_tvhl_course_for_subject(
    int $fieldid,
    string $subjectcode,
    string $category_ids_str
) {
    global $DB;

    $subjectcodes = th_framework_subjectcode_get_all_codes($subjectcode);
    list($insql, $params) = $DB->get_in_or_equal($subjectcodes, SQL_PARAMS_NAMED);

    $where_in_tvhl = "AND c.category IN ($category_ids_str)";
    $compare       = $DB->sql_compare_text('d.value') . " $insql";

    $sql = "SELECT c.id
              FROM {customfield_data} d
              JOIN {course} c ON c.id = d.instanceid
             WHERE d.fieldid = :fid
               AND {$compare}
               AND c.visible = 1
               $where_in_tvhl
          ORDER BY c.startdate DESC, c.id DESC";

    $params['fid'] = $fieldid;

    return $DB->get_record_sql($sql, $params, IGNORE_MISSING);
}

/**
 * Tính status theo đầu điểm điều kiện thi (ddk) trong gradebook của 1 course.
 * - Mặc định: status_inprogress
 * - finalgrade == 1: status_passed
 */
function th_framework_get_ddk_status_for_course(int $courseid, int $userid): string {
    global $DB;

    $params = [
        'courseid' => $courseid,
        'userid'   => $userid,
        'idnumber' => 'ddk',
    ];

    $grade = $DB->get_record_sql("
        SELECT gg.*
          FROM {grade_items} gi
          JOIN {grade_grades} gg ON gg.itemid = gi.id
         WHERE gi.courseid = :courseid
           AND gi.idnumber = :idnumber
           AND gg.userid = :userid
    ", $params);

    $status = get_string('status_inprogress', 'local_th_framework_study');

    if ($grade) {
        $final = (float)$grade->finalgrade;
        if ($final == 1.0) {
            $status = get_string('status_passed', 'local_th_framework_study');
        }
    }

    return $status;
}

/**
 * Lấy bản ghi gradebook của user + mã môn (bảng local_th_gradebook).
 */
function th_framework_get_gradebook(string $subjectcode, int $userid) {
    global $DB;

    $subjectcodes = th_framework_subjectcode_get_all_codes($subjectcode);
    list($insql, $params) = $DB->get_in_or_equal($subjectcodes, SQL_PARAMS_NAMED);

    $sql = "SELECT *
              FROM {local_th_gradebook}
             WHERE shortname $insql
               AND userid = :userid
               AND startdate IS NOT NULL
          ORDER BY startdate DESC, id DESC";

    $params['userid'] = $userid;

    return $DB->get_record_sql($sql, $params, IGNORE_MISSING);
}

/**
 * Build object enriched cho 1 dòng môn học.
 */
function th_framework_build_enriched_record(stdClass $r, string $status, string $action, ?int $courseid, $opendate) : stdClass {
    return (object) [
        'slot'         => (int)($r->slot ?? 0),
        'subject_code' => $r->subject_code,
        'subject_name' => $r->subject_name,
        'credit'       => (int)$r->credit,
        'status'       => $status,
        'action'       => $action,
        'course_id'    => $courseid,
        'opendate'     => $opendate,
    ];
}

/**
 * Group các môn theo slot để render.
 * Trả về: [slot => [ [record...], ... ]]
 *
 * NOTE: Nếu bật skip_slot_target thì slot không còn ý nghĩa → gom chung 1 nhóm (key = 0)
 * và sort theo subject_code/subject_name.
 */
function th_group_by_slot(array $subjects): array {
    $skip = (bool)get_config('local_th_framework_study', 'skip_slot_target');

    if ($skip) {
        usort($subjects, function($a, $b) {
            $ac = (string)($a->subject_code ?? '');
            $bc = (string)($b->subject_code ?? '');
            $cmp = strcmp($ac, $bc);
            if ($cmp !== 0) {
                return $cmp;
            }
            $an = (string)($a->subject_name ?? '');
            $bn = (string)($b->subject_name ?? '');
            return strcmp($an, $bn);
        });

        return [0 => $subjects];
    }

    $out = [];
    foreach ($subjects as $r) {
        $slot = (int)($r->slot ?? 0);
        if (!isset($out[$slot])) {
            $out[$slot] = [];
        }
        $out[$slot][] = $r;
    }
    ksort($out, SORT_NUMERIC);

    return $out;
}

function th_framework_subjectcode_get_all_codes(string $currentcode): array {
    global $DB;

    $codes = [$currentcode];

    $records = $DB->get_records(
        'th_subject_code_mapping',
        ['current_code' => $currentcode],
        '',
        'old_code'
    );

    foreach ($records as $r) {
        $codes[] = $r->old_code;
    }

    return array_unique($codes);
}
