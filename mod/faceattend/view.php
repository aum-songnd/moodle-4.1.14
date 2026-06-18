<?php
/**
 * view.php — Trang chính hiển thị khi sinh viên/giảng viên mở activity
 *
 * Luồng xử lý:
 *   1. Lấy tham số từ URL (?id=...)
 *   2. Load thông tin course, activity từ DB
 *   3. Kiểm tra quyền hạn
 *   4. Render giao diện phù hợp (sinh viên: camera UI | giảng viên: báo cáo)
 */

// Bắt buộc: load Moodle core
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/faceattend/lib.php');

// ─────────────────────────────────────────────────────────
// 1. ĐỌC THAM SỐ URL
// ─────────────────────────────────────────────────────────
// Moodle truyền ?id=<course_module_id> khi mở activity
$id      = optional_param('id', 0, PARAM_INT);   // course module id
$action  = optional_param('action', '', PARAM_ALPHA); // 'report' | 'export' | ''

if (!$id) {
    throw new moodle_exception('invalidcoursemodule');
}

// ─────────────────────────────────────────────────────────
// 2. LOAD DỮ LIỆU TỪ DB
// ─────────────────────────────────────────────────────────
// get_coursemodule_from_id() trả về object có: instance, course, ...
$cm      = get_coursemodule_from_id('faceattend', $id, 0, false, MUST_EXIST);
$course  = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$faceattend = $DB->get_record('faceattend', ['id' => $cm->instance], '*', MUST_EXIST);

// ─────────────────────────────────────────────────────────
// 3. XÁC THỰC ĐĂNG NHẬP & QUYỀN HẠN
// ─────────────────────────────────────────────────────────
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/faceattend:view', $context);

// ─────────────────────────────────────────────────────────
// 4. THIẾT LẬP PAGE CONTEXT (Moodle cần trước khi output)
// ─────────────────────────────────────────────────────────
$PAGE->set_url('/mod/faceattend/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($faceattend->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Load JavaScript module của plugin
$PAGE->requires->js_call_amd('mod_faceattend/camera', 'init', [[
    'cmid'       => $cm->id,
    'sesskey'    => sesskey(),
    'apiUrl'     => $faceattend->api_url,
    'maxAttempts' => (int) $faceattend->max_attempts,
]]);

// ─────────────────────────────────────────────────────────
// 5. RENDER TRANG
// ─────────────────────────────────────────────────────────
echo $OUTPUT->header();

// ── Tên activity + mô tả ──
echo $OUTPUT->heading(format_string($faceattend->name));

if (!empty($faceattend->intro)) {
    echo $OUTPUT->box(
        format_module_intro('faceattend', $faceattend, $cm->id),
        'generalbox mod_introbox'
    );
}

// ── Thông tin buổi học ──
$session_time = userdate($faceattend->session_date, get_string('strftimedatetime', 'langconfig'));
echo html_writer::div(
    html_writer::tag('strong', '📅 Buổi học: ') . $session_time,
    'alert alert-info'
);

// ─────────────────────────────────────────────────────────
// 6. PHÂN NHÁNH: GIẢNG VIÊN vs SINH VIÊN
// ─────────────────────────────────────────────────────────

if (has_capability('mod/faceattend:viewreport', $context)) {
    // ── GIẢNG VIÊN: hiển thị báo cáo điểm danh ──
    render_teacher_view($faceattend, $cm, $context);

} else if (has_capability('mod/faceattend:attend', $context)) {
    // ── SINH VIÊN: hiển thị giao diện camera ──
    render_student_view($faceattend, $cm, $context);

} else {
    echo $OUTPUT->notification('Bạn không có quyền truy cập trang này.', 'error');
}

echo $OUTPUT->footer();


// ─────────────────────────────────────────────────────────
// HÀM RENDER GIAO DIỆN SINH VIÊN
// ─────────────────────────────────────────────────────────
function render_student_view($faceattend, $cm, $context) {
    global $DB, $USER, $OUTPUT;

    // Kiểm tra buổi điểm danh còn mở không
    if (!$faceattend->is_open) {
        echo $OUTPUT->notification(get_string('session_closed', 'faceattend'), 'warning');
        return;
    }

    // Kiểm tra sinh viên đã điểm danh chưa
    $existing = $DB->get_record('faceattend_log', [
        'faceattendid' => $faceattend->id,
        'userid'       => $USER->id,
        'status'       => 'present',
    ]);

    if ($existing) {
        $time_attended = userdate($existing->timeattended, '%H:%M %d/%m/%Y');
        echo $OUTPUT->notification(
            get_string('already_attended', 'faceattend') .
            "<br>Thời gian: <strong>{$time_attended}</strong>" .
            "<br>Độ chính xác: <strong>{$existing->confidence}%</strong>",
            'success'
        );
        return;
    }

    // Kiểm tra sinh viên đã đăng ký khuôn mặt chưa
    $enrollment = $DB->get_record('faceattend_enrollment', [
        'userid'    => $USER->id,
        'is_active' => 1,
    ]);

    if (!$enrollment) {
        $enroll_url = new moodle_url('/mod/faceattend/enroll.php', ['userid' => $USER->id]);
        echo $OUTPUT->notification(
            get_string('not_registered', 'faceattend') .
            '<br><a href="' . $enroll_url . '" class="btn btn-primary mt-2">👤 Đăng ký khuôn mặt ngay</a>',
            'warning'
        );
        return;
    }

    // ── Render giao diện camera ──
    // Dữ liệu này sẽ được JavaScript đọc qua data attributes
    $attrs = [
        'id'               => 'faceattend-container',
        'data-cmid'        => $cm->id,
        'data-sesskey'     => sesskey(),
        'data-face-token'  => $enrollment->face_token,
        'data-max-attempts' => $faceattend->max_attempts,
        'data-threshold'   => $faceattend->confidence_threshold,
    ];

    echo html_writer::start_div('', $attrs);

    // Khu vực camera
    echo html_writer::start_div('text-center my-3');
    echo html_writer::tag('video', '', [
        'id'       => 'face-video',
        'width'    => '480',
        'height'   => '360',
        'autoplay' => 'autoplay',
        'muted'    => 'muted',
        'style'    => 'border-radius:12px; border:3px solid #0d6efd; background:#000;',
    ]);
    echo html_writer::empty_tag('canvas', [
        'id'    => 'face-canvas',
        'style' => 'display:none',
    ]);
    echo html_writer::end_div();

    // Trạng thái & thông báo
    echo html_writer::div('', 'alert alert-secondary text-center', ['id' => 'face-status-box']);
    echo html_writer::tag('p', '', ['id' => 'face-status', 'class' => 'text-center fw-bold fs-5']);

    // Các nút bấm
    echo html_writer::start_div('d-flex justify-content-center gap-3 mt-3');
    echo html_writer::tag('button',
        get_string('start_camera', 'faceattend'),
        ['id' => 'btn-start-camera', 'class' => 'btn btn-outline-primary btn-lg']
    );
    echo html_writer::tag('button',
        get_string('capture_face', 'faceattend'),
        ['id' => 'btn-capture', 'class' => 'btn btn-primary btn-lg', 'disabled' => 'disabled']
    );
    echo html_writer::end_div();

    // Hiển thị số lần thử còn lại
    echo html_writer::tag('p',
        "Số lần thử còn lại: <span id='attempts-left'>{$faceattend->max_attempts}</span>",
        ['class' => 'text-center text-muted mt-2']
    );

    echo html_writer::end_div(); // #faceattend-container
}


// ─────────────────────────────────────────────────────────
// HÀM RENDER GIAO DIỆN GIẢNG VIÊN
// ─────────────────────────────────────────────────────────
function render_teacher_view($faceattend, $cm, $context) {
    global $DB, $OUTPUT, $PAGE;

    // Lấy tất cả sinh viên enrolled trong course
    $enrolled_users = get_enrolled_users($context, 'mod/faceattend:attend');

    // Lấy tất cả log điểm danh của buổi này
    $attendance_logs = $DB->get_records('faceattend_log',
        ['faceattendid' => $faceattend->id],
        '',
        'userid, status, confidence, timeattended, attempt_count'
    );
    // Chuyển sang dạng userid => record để tra nhanh
    $logs_by_user = [];
    foreach ($attendance_logs as $log) {
        $logs_by_user[$log->userid] = $log;
    }

    $total_present = 0;
    $total_absent  = 0;

    // ── Nút toggle mở/đóng điểm danh ──
    $toggle_url    = new moodle_url('/mod/faceattend/toggle.php', ['id' => $cm->id, 'sesskey' => sesskey()]);
    $toggle_label  = $faceattend->is_open ? '🔴 Đóng điểm danh' : '🟢 Mở điểm danh';
    $toggle_class  = $faceattend->is_open ? 'btn btn-danger mb-3' : 'btn btn-success mb-3';
    echo html_writer::tag('a', $toggle_label, ['href' => $toggle_url, 'class' => $toggle_class]);

    // ── Nút xuất CSV ──
    $export_url = new moodle_url('/mod/faceattend/export.php', ['id' => $cm->id, 'sesskey' => sesskey()]);
    echo html_writer::tag('a', '⬇️ Xuất CSV', ['href' => $export_url, 'class' => 'btn btn-outline-success mb-3 ms-2']);

    // ── Bảng báo cáo ──
    echo html_writer::start_tag('table', ['class' => 'table table-bordered table-hover']);
    echo html_writer::start_tag('thead', ['class' => 'table-primary']);
    echo html_writer::tag('tr',
        html_writer::tag('th', '#') .
        html_writer::tag('th', 'Tên sinh viên') .
        html_writer::tag('th', 'Email') .
        html_writer::tag('th', 'Thời gian điểm danh') .
        html_writer::tag('th', 'Độ chính xác') .
        html_writer::tag('th', 'Số lần thử') .
        html_writer::tag('th', 'Trạng thái')
    );
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    $i = 1;
    foreach ($enrolled_users as $user) {
        $log = $logs_by_user[$user->id] ?? null;

        if ($log && $log->status === 'present') {
            $total_present++;
            $status_badge = '<span class="badge bg-success">✅ Có mặt</span>';
            $attend_time  = userdate($log->timeattended, '%H:%M %d/%m/%Y');
            $confidence   = number_format($log->confidence, 1) . '%';
            $attempts     = $log->attempt_count;
            $row_class    = '';
        } else {
            $total_absent++;
            $status_badge = '<span class="badge bg-danger">❌ Vắng mặt</span>';
            $attend_time  = '—';
            $confidence   = '—';
            $attempts     = $log ? $log->attempt_count : 0;
            $row_class    = 'table-light';
        }

        echo html_writer::tag('tr',
            html_writer::tag('td', $i++) .
            html_writer::tag('td', fullname($user)) .
            html_writer::tag('td', $user->email) .
            html_writer::tag('td', $attend_time) .
            html_writer::tag('td', $confidence) .
            html_writer::tag('td', $attempts) .
            html_writer::tag('td', $status_badge),
            ['class' => $row_class]
        );
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');

    // ── Tổng kết ──
    $total = $total_present + $total_absent;
    $rate  = $total > 0 ? round($total_present / $total * 100, 1) : 0;
    echo html_writer::div(
        "📊 Tổng kết: <strong>{$total_present}/{$total}</strong> có mặt ({$rate}%)",
        'alert alert-info'
    );
}
