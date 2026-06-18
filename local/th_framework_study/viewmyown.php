<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/user_select_form.php');
require_login();

global $DB, $USER, $PAGE, $OUTPUT;

$context = context_system::instance();
require_capability('local/th_framework_study:viewmyown', $context);

$canviewothers = has_capability('local/th_framework_study:viewothers', $context);
$userid = optional_param('userid', $USER->id, PARAM_INT);

if (!$canviewothers) {
    $userid = $USER->id;
}

$subject = optional_param('subject', '', PARAM_TEXT);
$action  = optional_param('action', '', PARAM_TEXT);
$course_id  = optional_param('courseid', 0, PARAM_INT);

$tvhl = get_config('local_th_framework_study', 'tvhl') ?: 'TVHL';

if (!empty($subject) && !empty($action)) {

    if ($action === 'notstarted') {
        \core\notification::warning(get_string('msg_notstarted', 'local_th_framework_study'));
        redirect(new moodle_url('/local/th_framework_study/viewmyown.php', ['userid' => $userid]));
    }

    else if ($action === 'inprogress') {
        if ($course_id) {
            redirect(new moodle_url('/course/view.php', ['id' => $course_id]));
        }

        \core\notification::error(
            get_string('error_course_notfound', 'local_th_framework_study', $subject)
        );        
        return;
    }

    else if ($action === 'passed') {
        if ($course_id) {
            redirect(new moodle_url('/course/view.php', ['id' => $course_id]));
        }

        \core\notification::error(
            get_string('msg_course_notfound_tvhl', 'local_th_framework_study', $subject)
        );        
        return;
    }

    else if ($action === 'notenrol') {
        if ($course_id) {
            $context = context_course::instance($course_id);
            if (!is_enrolled($context, $userid)) {
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
$PAGE->set_url(new moodle_url('/local/th_framework_study/viewmyown.php', ['userid' => $userid]));
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
                break; // dùng mã đầu tiên tìm được
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
        redirect(new moodle_url('/local/th_framework_study/viewmyown.php', ['userid'=>$userid]));
    }
}


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('myframework', 'local_th_framework_study'));

if ($canviewothers) {
    $form->display();
}


// Lấy profile major – target
$major1field  = get_config('local_th_framework_study', 'default_major1') ?: 'th_major_code';
$target1field = get_config('local_th_framework_study', 'default_target1') ?: 'th_target_code';
$major2field  = get_config('local_th_framework_study', 'default_major2') ?: 'th_major_code2';
$target2field = get_config('local_th_framework_study', 'default_target2') ?: 'th_target_code2';

$major1  = th_get_user_profile_field_value($userid, $major1field);
$target1 = th_get_user_profile_field_value($userid, $target1field);
$major2  = th_get_user_profile_field_value($userid, $major2field);
$target2 = th_get_user_profile_field_value($userid, $target2field);

$pairInputs = [];
if (!empty($major1) && !empty($target1)) $pairInputs[] = [$major1, $target1];
if (!empty($major2) && !empty($target2)) $pairInputs[] = [$major2, $target2];

if (empty($pairInputs)) {
    echo $OUTPUT->notification(get_string('missing_majortarget', 'local_th_framework_study'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

// Load danh sách môn học
$subjects = th_load_framework_for_pairs($pairInputs);

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

// Chuỗi category id cho mệnh đề IN / NOT IN
$category_ids_str = implode(',', array_map('intval', $category_ids));

$enriched = [];

foreach ($subjects as $r) {

    // 1. Tìm course chính (không thuộc TVHL) mà user đã được enrol
    $course = th_framework_get_main_course_for_subject($fieldid, $r->subject_code, $userid, $category_ids_str);

    if ($course && $course->visible == 1) {

        if ($DB->record_exists('th_subject_exemption', ['userid' => $userid, 'subject_code' => $r->subject_code])) {
            $status = get_string('status_exempted', 'local_th_framework_study');
            $action = 'exempted';
            $enriched[] = th_framework_build_enriched_record(
                $r,
                $status,
                $action,
                null,
                null
            );
        } else {
            // Case 1: Có course chính, đang mở (visible = 1)
            $status   = th_framework_get_ddk_status_for_course($course->id, $userid);
            $enriched[] = th_framework_build_enriched_record(
                $r,
                $status,
                'passed',
                $course->id,
                $course->startdate
            );
        }
        

    } else if ($course && $course->visible == 0) {
        if ($DB->record_exists('th_subject_exemption', ['userid' => $userid, 'subject_code' => $r->subject_code])) {
            $status = get_string('status_exempted', 'local_th_framework_study');
            $action = 'exempted';
            $enriched[] = th_framework_build_enriched_record(
                $r,
                $status,
                $action,
                null,
                $course->startdate
            );
        } else {
            // Case 2: Có course chính nhưng đang ẩn (visible = 0)
            $status = th_framework_get_ddk_status_for_course($course->id, $userid);

            // Tìm course trong thư viện học liệu
            $course_tvhl = th_framework_get_tvhl_course_for_subject($fieldid, $r->subject_code, $category_ids_str);

            if ($course_tvhl) {
                $enrolled = th_is_user_enrolled_in_course($userid, $course_tvhl->id);
                $action   = $enrolled ? 'passed' : 'notenrol';

                // opendate vẫn lấy startdate của course chính (giống code cũ)
                $enriched[] = th_framework_build_enriched_record(
                    $r,
                    $status,
                    $action,
                    $course_tvhl->id,
                    $course->startdate
                );
            } else {
                // Không tìm thấy khóa thư viện → giữ nguyên status, action = passed, course_id = null
                $enriched[] = th_framework_build_enriched_record(
                    $r,
                    $status,
                    'passed',
                    null,
                    $course->startdate
                );
            }
        }
    } else {
        if ($DB->record_exists('th_subject_exemption', ['userid' => $userid, 'subject_code' => $r->subject_code])) {
            $status = get_string('status_exempted', 'local_th_framework_study');
            $action = 'exempted';
            $enriched[] = th_framework_build_enriched_record(
                $r,
                $status,
                $action,
                null,
                null
            );
        } else {
            // Case 3: Không có course chính (user chưa enrol khóa học "thực chiến")
            $course_tvhl = th_framework_get_tvhl_course_for_subject($fieldid, $r->subject_code, $category_ids_str);

            if ($course_tvhl) {
                $enrolled  = th_is_user_enrolled_in_course($userid, $course_tvhl->id);
                $gradebook = th_framework_get_gradebook($r->subject_code, $userid);
                $open_date = ($gradebook && $gradebook->startdate) ? $gradebook->startdate : null;

                if ($enrolled) {
                    // Đã enrol thư viện + có khóa => coi như passed
                    $status = get_string('status_passed', 'local_th_framework_study');
                    $enriched[] = th_framework_build_enriched_record(
                        $r,
                        $status,
                        'passed',
                        $course_tvhl->id,
                        $open_date
                    );

                } else {
                    // Chưa enrol thư viện
                    if ($gradebook && $gradebook->dieukien == 1) {
                        // Đã đủ điều kiện (dieukien = 1) nhưng chưa enrol
                        $status = get_string('status_passed', 'local_th_framework_study');
                        $enriched[] = th_framework_build_enriched_record(
                            $r,
                            $status,
                            'notenrol',
                            $course_tvhl->id,
                            $open_date
                        );
                    } else {
                        // Chưa đủ điều kiện hoặc không có gradebook
                        $status = get_string('status_notstarted', 'local_th_framework_study');
                        $enriched[] = th_framework_build_enriched_record(
                            $r,
                            $status,
                            'notstarted',
                            null,
                            $open_date
                        );
                    }
                }

            } else {
                // Không có course TVHL luôn
                $status = get_string('status_notstarted', 'local_th_framework_study');
                $enriched[] = th_framework_build_enriched_record(
                    $r,
                    $status,
                    'notstarted',
                    null,
                    null
                );
            }
        }
    }
}

usort($enriched, fn($a,$b) =>
    $a->slot == $b->slot ? strcmp($a->subject_code,$b->subject_code) : $a->slot <=> $b->slot
);

$fmtdate = fn($ts) => $ts ? date('d/m/Y', $ts) : '-';

// Hiển thị bảng
$table = new html_table();
$table->attributes = ['class' => 'th_myown display', 'border'=>'1', 'style'=>'width:100%;text-align:center;'];

$table->head = [
    get_string('slot', 'local_th_framework_study'),
    get_string('subjectname', 'local_th_framework_study'),
    get_string('subjectcode', 'local_th_framework_study'),
    get_string('credit', 'local_th_framework_study'),
    get_string('status', 'local_th_framework_study'),
    get_string('opendate', 'local_th_framework_study')
];

$status_notstarted = get_string('status_notstarted', 'local_th_framework_study');
$status_inprogress = get_string('status_inprogress', 'local_th_framework_study');
$status_passed     = get_string('status_passed', 'local_th_framework_study');
$status_exempted   = get_string('status_exempted', 'local_th_framework_study');

$total_credits      = 0;
$total_passed       = 0;
$total_inprogress   = 0;
$total_notstarted   = 0;

foreach ($enriched as $it) {

    if ($it->action === 'exempted') {
        $subjectdisplay = format_string($it->subject_name);
    } else {

        $action = $it->action;

        $url = new moodle_url('/local/th_framework_study/viewmyown.php', [
            'subject' => $it->subject_code,
            'action'  => $action,
            'userid'  => $userid,
            'courseid' => $it->course_id
        ]);

        $subjectdisplay = html_writer::link($url, format_string($it->subject_name));
    }

    $row = [
        $it->slot,
        $subjectdisplay,
        s($it->subject_code),
        $it->credit,
        s($it->status),
        s($fmtdate($it->opendate))
    ];

    $table->data[] = $row;

    // Tổng
    $total_credits += $it->credit;
    if ($it->status === $status_passed) $total_passed += $it->credit;
    else if ($it->status === $status_inprogress) $total_inprogress += $it->credit;
    else if ($it->status === $status_notstarted) $total_notstarted += $it->credit;
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
$PAGE->requires->js_call_amd('local_thlib/main', 'init', ['.th_myown', get_string('pluginname','local_th_framework_study'), $lang]);


echo $OUTPUT->footer();
