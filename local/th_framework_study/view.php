<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/user_select_form.php');
require_login();

global $DB, $USER, $PAGE, $OUTPUT;

$context = context_system::instance();
//require_capability('local/th_framework_study:viewmyown', $context);

$canviewothers = has_capability('local/th_framework_study:viewothers', $context);
$userid = optional_param('userid', $USER->id, PARAM_INT);

if (!$canviewothers) {
    $userid = $USER->id;
}

$subject   = optional_param('subject', '', PARAM_TEXT);
$action    = optional_param('action', '', PARAM_TEXT);
$course_id = optional_param('courseid', 0, PARAM_INT);

$tvhl = get_config('local_th_framework_study', 'tvhl') ?: 'TVHL';

$enable_train_code = (bool)get_config('local_th_framework_study', 'enable_train_code');
$skip = (bool)get_config('local_th_framework_study', 'skip_slot_target');

if (!empty($subject) && !empty($action)) {

    if ($action === 'notstarted') {
        \core\notification::warning(get_string('msg_notstarted', 'local_th_framework_study'));
        redirect(new moodle_url('/local/th_framework_study/view.php', ['userid' => $userid]));
    } else if ($action === 'inprogress') {
        if ($course_id) {
            redirect(new moodle_url('/course/view.php', ['id' => $course_id]));
        }

        \core\notification::error(
            get_string('error_course_notfound', 'local_th_framework_study', $subject)
        );
        return;
    } else if ($action === 'passed') {
        if ($course_id) {
            redirect(new moodle_url('/course/view.php', ['id' => $course_id]));
        }

        \core\notification::error(
            get_string('msg_course_notfound_tvhl', 'local_th_framework_study', $subject)
        );
        return;
    } else if ($action === 'notenrol') {
        if ($course_id) {
            $context_course = context_course::instance($course_id);
            if (!is_enrolled($context_course, $userid)) {
                if ($enrol = enrol_get_plugin('manual')) {
                    $instance = $DB->get_record('enrol', ['courseid' => $course_id, 'enrol' => 'manual'], '*', MUST_EXIST);
                    $roleshortname = 'student';
                    $roleid = $DB->get_field('role', 'id', ['shortname' => $roleshortname], IGNORE_MISSING);
                    $enrol->enrol_user($instance, $userid, $roleid);
                }
            }
            redirect(new moodle_url('/course/view.php', ['id' => $course_id]));
        }

        \core\notification::error(
            get_string('msg_course_notfound_tvhl', 'local_th_framework_study', $subject)
        );
    }
}

// PAGE SETTINGS
$PAGE->set_url(new moodle_url('/local/th_framework_study/view.php', ['userid' => $userid]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('myframework', 'local_th_framework_study'));
$PAGE->set_heading(get_string('myframework', 'local_th_framework_study'));
$PAGE->navbar->add(get_string('myframework', 'local_th_framework_study'));

if ($canviewothers) {
    // Lấy danh sách user có role student
    $sql = "SELECT DISTINCT 
                u.id,
                u.firstname,
                u.lastname,
                u.firstnamephonetic,
                u.lastnamephonetic,
                u.middlename,
                u.alternatename
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE r.shortname = 'student'
            ORDER BY u.firstname, u.lastname";

    $records = $DB->get_records_sql($sql);

    // Lấy cấu hình các customfield mã học viên
    $setting = get_config('local_thlib', 'studentcodeshortname');
    $shortnames = array_filter(array_map('trim', explode(',', $setting)));

    $users = [];

    foreach ($records as $u) {

        $studentcode = '';

        // Tìm mã học viên theo từng field shortname
        foreach ($shortnames as $sn) {
            $sqlcode = "SELECT d.data
                        FROM {user_info_data} d
                        JOIN {user_info_field} f ON f.id = d.fieldid
                        WHERE d.userid = :userid
                          AND f.shortname = :sn";

            $code = $DB->get_field_sql($sqlcode, [
                'userid' => $u->id,
                'sn' => $sn
            ]);

            if (!empty($code)) {
                $studentcode = trim($code);
                break;
            }
        }

        // Ghép tên + mã học viên
        $displayname = fullname($u);
        if (!empty($studentcode)) {
            $displayname .= " ($studentcode)";
        }

        $users[$u->id] = $displayname;
    }

    // Tạo form
    $form = new th_user_select_form($users, $userid);

    // Xử lý submit form
    if ($data = $form->get_data()) {
        $userid = $data->userid;
        redirect(new moodle_url('/local/th_framework_study/view.php', ['userid' => $userid]));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('myframework', 'local_th_framework_study'));

if ($canviewothers) {
    $form->display();
}

// ===== 1. Lấy config field =====
$major1field  = get_config('local_th_framework_study', 'default_major1') ?: 'th_major_code';
$target1field = get_config('local_th_framework_study', 'default_target1') ?: 'th_target_code';
$train1field  = get_config('local_th_framework_study', 'train_code1') ?: 'th_train_code1';

$major2field  = get_config('local_th_framework_study', 'default_major2') ?: 'th_major_code2';
$target2field = get_config('local_th_framework_study', 'default_target2') ?: 'th_target_code2';
$train2field  = get_config('local_th_framework_study', 'train_code2') ?: 'th_train_code2';

// ===== 2. Lấy dữ liệu user profile =====
$major1  = th_get_user_profile_field_value($userid, $major1field);
$target1 = th_get_user_profile_field_value($userid, $target1field);
$train1  = th_get_user_profile_field_value($userid, $train1field);

$major2  = th_get_user_profile_field_value($userid, $major2field);
$target2 = th_get_user_profile_field_value($userid, $target2field);
$train2  = th_get_user_profile_field_value($userid, $train2field);

// ===== 3. Check ngành hợp lệ =====
// Nếu skip_slot_target bật: KHÔNG bắt target_code nữa
if ($enable_train_code) {
    $hasMajor1 = !empty($major1) && !empty($train1) && !empty($target1);
    $hasMajor2 = !empty($major2) && !empty($train2) && !empty($target2);
} else {
    $hasMajor1 = !empty($major1) && !empty($target1);
    $hasMajor2 = !empty($major2) && !empty($target2);
}


if (!$hasMajor1 && !$hasMajor2) {
    echo $OUTPUT->notification(
        get_string('missing_majortarget', 'local_th_framework_study'),
        'warning'
    );
    echo $OUTPUT->footer();
    exit;
}

// ===== 4. Xác định ngành được chọn (ưu tiên ngành 1) =====
$selectedMajor = optional_param('selected_major', null, PARAM_INT);
$finalSelectedMajor = null;

// Default
if ($hasMajor1) {
    $finalSelectedMajor = 1;
} elseif ($hasMajor2) {
    $finalSelectedMajor = 2;
}

// Cho phép user override sang ngành 2 nếu hợp lệ
if ($selectedMajor == 2 && $hasMajor2) {
    $finalSelectedMajor = 2;
}

// ===== 5. Hiển thị select nếu có cả 2 ngành =====
if ($hasMajor1 && $hasMajor2) {

    $major1_name_field = get_config('local_th_framework_study', 'study_field1') ?: 'th_study_field';
    $major2_name_field = get_config('local_th_framework_study', 'study_field2') ?: 'th_study_field2';

    $major1_name = th_get_user_profile_field_value($userid, $major1_name_field);
    $major2_name = th_get_user_profile_field_value($userid, $major2_name_field);
    ?>
    <form method="get">
        <input type="hidden" name="userid" value="<?= (int)optional_param('userid', 0, PARAM_INT); ?>">
        <label for="select_major">
            <?= get_string('choose_major', 'local_th_framework_study'); ?>
        </label>

        <select name="selected_major" id="select_major" onchange="this.form.submit()">
            <option value="1" <?= $finalSelectedMajor == 1 ? 'selected' : '' ?>>
                <?= format_string($major1_name); ?>
            </option>
            <option value="2" <?= $finalSelectedMajor == 2 ? 'selected' : '' ?>>
                <?= format_string($major2_name); ?>
            </option>
        </select>
    </form>
    <?php
}

// ===== 6. Build pairInputs =====
$pairInputs = [];
if ($finalSelectedMajor == 1) {
    $pairInputs = $enable_train_code ? [$major1, $target1, $train1] : [$major1, $target1];
} elseif ($finalSelectedMajor == 2) {
    $pairInputs = $enable_train_code ? [$major2, $target2, $train2] : [$major2, $target2];
}

if (empty($pairInputs)) {
    echo $OUTPUT->notification(
        get_string('missing_majortarget', 'local_th_framework_study'),
        'warning'
    );
    echo $OUTPUT->footer();
    exit;
}

// Load danh sách môn học
$subjects = th_load_framework_for_pairs($pairInputs, $enable_train_code);

echo html_writer::tag('div',
    get_string('totalsubjects', 'local_th_framework_study') . ': ' . count($subjects),
    ['style' => 'font-size:16px;margin-bottom:15px;font-weight:bold;']
);

if (empty($subjects)) {
    echo $OUTPUT->notification(get_string('no_framework_for_pairs', 'local_th_framework_study'), 'info');
    echo $OUTPUT->footer();
    exit;
}

$coursecode = get_config('local_th_framework_study', 'coursecode') ?: 'th_course_code';
$fieldid = $DB->get_field('customfield_field', 'id', ['shortname' => $coursecode], IGNORE_MISSING);
if (!$fieldid) {
    echo $OUTPUT->notification(
        get_string('error_missing_coursecode_field', 'local_th_framework_study'),
        'info'
    );
    echo $OUTPUT->footer();
    exit;
}

$catid = $DB->get_field('course_categories', 'id', ['idnumber' => $tvhl], IGNORE_MISSING);
if (!$catid) {
    echo $OUTPUT->notification(
        get_string('error_missing_tvhl_category', 'local_th_framework_study'),
        'info'
    );
    echo $OUTPUT->footer();
    exit;
}

$category_ids = array_merge(
    [$catid],
    array_keys(\core_course\management\helper::get_category_children_visibility($catid))
);

$category_ids_str = implode(',', array_map('intval', $category_ids));

// Lấy category id từ setting
$excludedidnumbers = get_config(
    'local_th_framework_study',
    'excluded_category_idnumber'
);

$excluded_category_ids = [];

if (!empty($excludedidnumbers)) {

    $idnumbers = array_filter(array_map('trim', explode(',', $excludedidnumbers)));

    foreach ($idnumbers as $idnumber) {

        $catid = $DB->get_field('course_categories', 'id', ['idnumber' => $idnumber], IGNORE_MISSING);

        if ($catid) {
            // add chính category
            $excluded_category_ids[] = $catid;
            // add toàn bộ subcategory
            $children = array_keys(
                \core_course\management\helper::get_category_children_visibility($catid)
            );
            $excluded_category_ids = array_merge($excluded_category_ids, $children);
        }
    }
}

// MERGE
$category_ids = array_values(array_unique(array_merge(
    $category_ids,
    $excluded_category_ids
)));

$category_ids_str1 = implode(',', array_map('intval', $category_ids));

$enriched = [];

foreach ($subjects as $r) {

    // 1. Tìm course chính (không thuộc TVHL) mà user đã được enrol
    $course = th_framework_get_main_course_for_subject($fieldid, $r->subject_code, $userid, $category_ids_str1);

    if ($course && $course->visible == 1) {

        if ($DB->record_exists('th_subject_exemption', ['userid' => $userid, 'subject_code' => $r->subject_code])) {
            $status = get_string('status_exempted', 'local_th_framework_study');
            $action = 'exempted';
            $enriched[] = th_framework_build_enriched_record($r, $status, $action, null, null);
        } else {
            $status = th_framework_get_ddk_status_for_course($course->id, $userid);
            $enriched[] = th_framework_build_enriched_record($r, $status, 'passed', $course->id, $course->startdate);
        }

    } else if ($course && $course->visible == 0) {

        if ($DB->record_exists('th_subject_exemption', ['userid' => $userid, 'subject_code' => $r->subject_code])) {
            $status = get_string('status_exempted', 'local_th_framework_study');
            $action = 'exempted';
            $enriched[] = th_framework_build_enriched_record($r, $status, $action, null, $course->startdate);
        } else {
            $status = th_framework_get_ddk_status_for_course($course->id, $userid);

            // Tìm course trong thư viện học liệu
            $course_tvhl = th_framework_get_tvhl_course_for_subject($fieldid, $r->subject_code, $category_ids_str);

            if ($course_tvhl) {
                $enrolled = th_is_user_enrolled_in_course($userid, $course_tvhl->id);
                $action   = $enrolled ? 'passed' : 'notenrol';

                $enriched[] = th_framework_build_enriched_record(
                    $r,
                    $status,
                    $action,
                    $course_tvhl->id,
                    $course->startdate
                );
            } else {
                $enriched[] = th_framework_build_enriched_record($r, $status, 'passed', null, $course->startdate);
            }
        }

    } else {

        if ($DB->record_exists('th_subject_exemption', ['userid' => $userid, 'subject_code' => $r->subject_code])) {
            $status = get_string('status_exempted', 'local_th_framework_study');
            $action = 'exempted';
            $enriched[] = th_framework_build_enriched_record($r, $status, $action, null, null);
        } else {
            $course_tvhl = th_framework_get_tvhl_course_for_subject($fieldid, $r->subject_code, $category_ids_str);

            if ($course_tvhl) {
                $enrolled  = th_is_user_enrolled_in_course($userid, $course_tvhl->id);
                $gradebook = th_framework_get_gradebook($r->subject_code, $userid);
                $open_date = ($gradebook && $gradebook->startdate) ? $gradebook->startdate : null;

                if ($enrolled) {
                    $status = get_string('status_passed', 'local_th_framework_study');
                    $enriched[] = th_framework_build_enriched_record($r, $status, 'passed', $course_tvhl->id, $open_date);
                } else {
                    if ($gradebook && $gradebook->dieukien == 1) {
                        $status = get_string('status_passed', 'local_th_framework_study');
                        $enriched[] = th_framework_build_enriched_record($r, $status, 'notenrol', $course_tvhl->id, $open_date);
                    } else {
                        $status = get_string('status_notstarted', 'local_th_framework_study');
                        $enriched[] = th_framework_build_enriched_record($r, $status, 'notstarted', null, $open_date);
                    }
                }
            } else {
                $gradebook = th_framework_get_gradebook($r->subject_code, $userid);
                $open_date = ($gradebook && $gradebook->startdate) ? $gradebook->startdate : null;

                if ($gradebook && (int)$gradebook->dieukien == 1) {
                    $status = get_string('status_passed', 'local_th_framework_study');
                    $enriched[] = th_framework_build_enriched_record($r, $status, 'notenrol', $course_tvhl->id, $open_date);
                } else {
                    $status = get_string('status_notstarted', 'local_th_framework_study');
                    $enriched[] = th_framework_build_enriched_record($r, $status, 'notstarted', null, $open_date);
                }
            }
        }
    }
}

// Sort: nếu skip → sort theo subject_code; không skip → slot rồi subject_code
usort($enriched, fn($a, $b) =>
    $a->slot == $b->slot ? strcmp((string)$a->subject_code, (string)$b->subject_code) : ($a->slot <=> $b->slot)
);


$fmtdate = fn($ts) => $ts ? date('d/m/Y', $ts) : '-';

// Hiển thị bảng
$table = new html_table();
$table->attributes = ['class' => 'th_myown display', 'border' => '1', 'style' => 'width:100%;text-align:center;'];

$table->head = [];
$table->head[] = get_string('slot', 'local_th_framework_study');
$table->head[] = get_string('subjectname', 'local_th_framework_study');
$table->head[] = get_string('subjectcode', 'local_th_framework_study');
$table->head[] = get_string('credit', 'local_th_framework_study');
$table->head[] = get_string('status', 'local_th_framework_study');
$table->head[] = get_string('opendate', 'local_th_framework_study');

$status_notstarted = get_string('status_notstarted', 'local_th_framework_study');
$status_inprogress = get_string('status_inprogress', 'local_th_framework_study');
$status_passed     = get_string('status_passed', 'local_th_framework_study');
$status_exempted   = get_string('status_exempted', 'local_th_framework_study');

$total_credits    = 0;
$total_passed     = 0;
$total_inprogress = 0;
$total_notstarted = 0;

foreach ($enriched as $it) {

    if ($it->action === 'exempted') {
        $subjectdisplay = format_string($it->subject_name);
    } else {
        $url = new moodle_url('/local/th_framework_study/view.php', [
            'subject'  => $it->subject_code,
            'action'   => $it->action,
            'userid'   => $userid,
            'courseid' => $it->course_id
        ]);
        $subjectdisplay = html_writer::link($url, format_string($it->subject_name));
    }

    $row = [];
    $slot_display = ($it->slot == 0) ? '-' : $it->slot;
    $row[] = $slot_display;
    $row[] = $subjectdisplay;
    $row[] = s($it->subject_code);
    $row[] = $it->credit;
    $row[] = s($it->status);
    $row[] = s($fmtdate($it->opendate));

    $table->data[] = $row;

    // Tổng
    $total_credits += $it->credit;
    if ($it->status === $status_passed) {
        $total_passed += $it->credit;
    } else if ($it->status === $status_inprogress) {
        $total_inprogress += $it->credit;
    } else if ($it->status === $status_notstarted) {
        $total_notstarted += $it->credit;
    }
}

echo html_writer::table($table);

echo html_writer::tag('div',
    "<strong>" . get_string('total_credits', 'local_th_framework_study') . ":</strong> $total_credits
     | <strong>" . get_string('total_passed', 'local_th_framework_study') . ":</strong> $total_passed
     | <strong>" . get_string('total_inprogress', 'local_th_framework_study') . ":</strong> $total_inprogress
     | <strong>" . get_string('total_notstarted', 'local_th_framework_study') . ":</strong> $total_notstarted",
    ['style' => 'margin-top:15px;font-weight:bold;font-size:16px;']
);

$lang = current_language();
echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', ['.th_myown', get_string('pluginname', 'local_th_framework_study'), $lang]);

echo $OUTPUT->footer();
