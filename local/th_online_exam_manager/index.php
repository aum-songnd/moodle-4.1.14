<?php
require('../../config.php');
require_once(__DIR__ . '/classes/form/report_form.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
require_capability('local/th_online_exam_manager:view_report', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/th_online_exam_manager/index.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('reporttitle', 'local_th_online_exam_manager'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('reporttitle', 'local_th_online_exam_manager'));

$baseurl = new moodle_url('/local/th_online_exam_manager/index.php');
$settingsnode = $PAGE->settingsnav->add(get_string('reporttitle', 'local_th_online_exam_manager'), $baseurl);
$settingsnode->make_active();

/* ===============================
 * INIT
 * =============================== */
$errors    = '';
$courseids = [];
$quizids   = [];
$userids   = [];
$enrolmap  = [];
$initusers = [];

$mform = new report_form(null, ['initusers' => $initusers]);

/* ===============================
 * FORM SUBMIT
 * =============================== */
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
}

if ($data = $mform->get_data()) {

    $isexporthcct = ($data->action === 'resulthcct');

    /* ===============================
     * CATEGORY CONFIG
     * =============================== */
    $categoryidnumber = get_config('local_th_online_exam_manager', 'categoryidnumber');
    if (empty($categoryidnumber)) {
        throw new moodle_exception('defaultcategory', 'local_th_online_exam_manager');
    }

    $category = $DB->get_record(
        'course_categories',
        ['idnumber' => $categoryidnumber],
        'id',
        MUST_EXIST
    );

    $setting_categoryid = $category->id;

    /* ===============================
     * FILTER MODE
     * =============================== */
    switch ($data->filtermode) {

        /* ---------- CATEGORY ---------- */
        case 'category':
            if (empty($data->courseids)) {
                if ($isexporthcct) {
                    throw new moodle_exception('selectcourses', 'local_th_online_exam_manager');
                }
                $errors .= html_writer::div(
                    get_string('selectcourses', 'local_th_online_exam_manager'),
                    'alert alert-danger'
                );
                break;
            }

            $courseids = $data->courseids;
            $quizids   = load_quizzes_in_courses($courseids);
            break;

        /* ---------- EXAM DATE ---------- */
        case 'examdate':

            if ($data->action === 'result' || $data->action === 'resulthcct') {

                // $courses = $DB->get_records('course', [
                //     'category'  => $setting_categoryid,
                //     'startdate' => $data->examdate
                // ]);

                // $courseids = array_keys($courses);
                $cat = \core_course_category::get($setting_categoryid);

                // Lấy toàn bộ course trong category + subcategory
                $courseobjects = $cat->get_courses(['recursive' => true]);

                $courseids = [];
                foreach ($courseobjects as $course) {
                    $selectedday = usergetdate($data->examdate);
                    $courseday   = usergetdate($course->startdate);

                    if ($selectedday['year'] == $courseday['year']
                        && $selectedday['mon'] == $courseday['mon']
                        && $selectedday['mday'] == $courseday['mday']) {
                        $courseids[] = $course->id;
                    }
                }

                if (empty($courseids)) {
                    // if ($isexporthcct) {
                    //     throw new moodle_exception('nocoursefound', 'local_th_online_exam_manager');
                    // }
                    $errors .= html_writer::div(
                        get_string('nocoursefound', 'local_th_online_exam_manager'),
                        'alert alert-danger'
                    );
                    break;
                }

                $quizids = load_quizzes_in_courses($courseids);

            } else {
                // $courses = $DB->get_records('course', ['category' => $setting_categoryid]);
                $cat = \core_course_category::get($setting_categoryid);
                $courseobjects = $cat->get_courses(['recursive' => true]);

                $courses = [];
                foreach ($courseobjects as $c) {
                    $courses[$c->id] = $c;
                }
                $courseids = array_keys($courses);
                $matchedquizzes = [];
                if (!empty($courseids)) {
                    list($in, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
                    $quizzes = $DB->get_records_select('quiz', "course $in", $params);

                    $selectedday = usergetdate($data->examdate);
                    foreach ($quizzes as $quiz) {
                        if (!$quiz->timeopen) {
                            continue;
                        }
                        $qd = usergetdate($quiz->timeopen);
                        if ($qd['year'] == $selectedday['year']
                            && $qd['mon'] == $selectedday['mon']
                            && $qd['mday'] == $selectedday['mday']) {
                            $matchedquizzes[$quiz->id] = $quiz;
                        }
                    }
                }

                $quizids   = array_keys($matchedquizzes);
                $courseids = array_unique(array_map(fn($q) => $q->course, $matchedquizzes));

                if (empty($quizids)) {
                    $errors .= html_writer::div(
                        get_string('noquizzesfound', 'local_th_online_exam_manager'),
                        'alert alert-danger'
                    );
                }
            }
            break;

        /* ---------- USER ---------- */
        case 'user':

            if (empty($data->userids)) {
                $errors .= html_writer::div(
                    get_string('selectusers', 'local_th_online_exam_manager'),
                    'alert alert-danger'
                );
                break;
            }

            list($uinsql, $uparams) = $DB->get_in_or_equal($data->userids, SQL_PARAMS_NAMED);

            //$courses = $DB->get_records('course', ['category' => $setting_categoryid]);
            $cat = \core_course_category::get($setting_categoryid);
            $courseobjects = $cat->get_courses(['recursive' => true]);

            $courses = [];
            foreach ($courseobjects as $c) {
                $courses[$c->id] = $c;
            }

            $allcourseids = array_keys($courses);

            if (empty($allcourseids)) {
                $errors .= html_writer::div(
                    get_string('nocoursesincategory', 'local_th_online_exam_manager'),
                    'alert alert-danger'
                );
                break;
            }

            list($cinsql, $cparams) = $DB->get_in_or_equal($allcourseids, SQL_PARAMS_NAMED, 'cid');

            $sql = "
                SELECT DISTINCT e.courseid
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE ue.userid $uinsql AND e.courseid $cinsql
            ";

            $records   = $DB->get_records_sql($sql, array_merge($uparams, $cparams));
            $courseids = array_keys($records);

            $quizids = load_quizzes_in_courses($courseids);
            break;
    }

    if (!empty($errors)) {
        echo $OUTPUT->header();
        $mform->display();
        echo $errors;
        echo $OUTPUT->footer();
        exit;
    }
    

    /* ===============================
     * ENROL MAP
     * =============================== */
    if (empty($errors)) {

        if ($data->filtermode === 'user') {
            $userids = $data->userids;
            foreach ($userids as $uid) {
                foreach ($courseids as $cid) {
                    $enrolmap[$uid][$cid] = true;
                }
            }
        } else {
            $enrolmap = load_users_enrolled_in_courses($courseids);
            $userids  = array_keys($enrolmap);
        }
    }

    if ($isexporthcct) {

        require_once(__DIR__ . '/classes/report/report_result_hcct_table.php');
    
        $tempdir = make_temp_directory('hcct_' . time());
        $docs = [];
    
        foreach ($courseids as $courseid) {
    
            $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname', MUST_EXIST);
            $coursename = clean_filename($course->fullname);
    
            $table = new \local_th_online_exam_manager\report\report_result_hcct_table(
                'hcct_' . $courseid,
                $userids,
                [$courseid],
                $data->examdate,
                $quizids,
                $enrolmap
            );

           // $table->set_empty_string('Không có dữ liệu');
            $table->reset_stt();
            $table->attributes['border'] = 1;
            $table->define_baseurl(new moodle_url('/local/th_online_exam_manager/index.php'));
            $table->setup();
            $table->query_db(0);
    
            /* ================= HEADER ================= */
            $headerhtml = '
            <table class="header-table" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="text-align:center; font-size:12px;">
                        ỦY BAN NHÂN DÂN THÀNH PHỐ HÀ NỘI<br>
                        <strong>TRƯỜNG CAO ĐẲNG</strong><br>
                        <strong>THƯƠNG MẠI VÀ DU LỊCH HÀ NỘI</strong><br>
                        ──────────────
                    </td>
                    <td style="text-align:center; font-weight:bold; font-size:14px;">
                        DANH SÁCH THI KẾT THÚC MÔN HỌC
                    </td>
                </tr>
            </table>
    
            <br>
    
            <div style="text-align:center; font-size:12px;">
                <strong>Tên phòng thi:</strong> ' . s($course->fullname) . '
            </div>
    
            <br>
            ';
    
            /* ================= TABLE HTML ================= */
            ob_start();
            $table->out(0, false);
            $tablehtml = ob_get_clean();
    
            // ÉP WIDTH CỘT – WORD CHỈ HIỂU CÁCH NÀY
            $tablehtml = preg_replace(
                '/<table([^>]*)>/',
                '<table$1 width="100%" cellpadding="0" cellspacing="0">
                <colgroup>
                    <col width="30">   <!-- TT -->
                    <col width="45">   <!-- SBD -->
                    <col width="70">   <!-- Mã HV -->
                    <col width="300">  <!-- Họ và tên (RỘNG) -->
                    <col width="70">   <!-- Ngày sinh -->
                    <col width="120">  <!-- Mã lớp -->
                    <col width="40">   <!-- Điểm số -->
                    <col width="40">   <!-- Điểm chữ -->
                    <col width="60">   <!-- Ghi chú -->
                </colgroup>',
                $tablehtml,
                1
            );
    
            /* ================= SUMMARY ================= */
            $summaryhtml = $table->get_summary_html();
            $signaturehtml = '
            <br><br>
            <table width="100%" cellpadding="0" cellspacing="0" style="border:none;">
                <tr>
                    <td style="border:none; width:55%;"></td>
                    <td style="border:none; width:45%; text-align:center;">
                        <div style="font-style:italic;">
                            Hà Nội, Ngày........tháng........năm 20.....
                        </div>
                        <div style="margin-top:10px; font-weight:bold;">
                            Giảng viên chấm điểm trực tiếp
                        </div>
                        <div style="font-weight:bold;">
                            (hoặc cán bộ nhập điểm)
                        </div>
                        <div style="margin-top:15px; font-style:italic;">
                            (ký ghi rõ họ tên)
                        </div>
                    </td>
                </tr>
            </table>
            ';
            $html = '
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    @page {
                        size: A4;
                        margin: 1.5cm;
                    }
                    body {
                        font-family: "Times New Roman";
                        font-size: 12px;
                    }
                    table {
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid #000;
                        padding: 4px;
                        text-align: center;
                        vertical-align: middle;
                    }
                    th:nth-child(4), td:nth-child(4) {
                        text-align: left;
                    }
                    .header-table,
                    .header-table td,
                    .header-table th {
                        border: none !important;
                    }
                </style>
            </head>
            <body>
    
            ' . $headerhtml . '
    
            ' . $tablehtml . '
    
            <br>
    
            ' . $summaryhtml . '
            ' . $signaturehtml . '
    
            </body>
            </html>';
    
            $filepath = $tempdir . '/ket_qua_' . $coursename . '.doc';
            file_put_contents($filepath, $html);
            $docs[] = $filepath;
        }
    
        /* ================= ZIP ================= */
        $zipname = 'ket_qua_hcct_' . date('Ymd_His') . '.zip';
        $zippath = $tempdir . '/' . $zipname;
    
        $zip = new ZipArchive();
        $zip->open($zippath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($docs as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    
        send_temp_file($zippath, $zipname);
        exit;
    }
    

    /* ============================================================
 * DISPLAY TABLE (ENROLL / RESULT)
 * ============================================================ */
if (empty($errors) && !$isexporthcct) {

    $needusers = in_array($data->action, ['enroll', 'result'], true);

    if ($needusers && empty($userids)) {
        $errors .= html_writer::div(
            get_string('selectusers', 'local_th_online_exam_manager'),
            'alert alert-danger'
        );
    } else {

        echo $OUTPUT->header();

        echo html_writer::tag('h2', get_string('contestreport', 'local_th_online_exam_manager'), [
            'id' => 'report-title',
            'class' => 'text-center mb-4'
        ]);

        $mform->display();

        if ($data->action === 'enroll') {

            require_once(__DIR__ . '/classes/report/report_enroll_table.php');

            $table = new \local_th_online_exam_manager\report\report_enroll_table(
                'report_enroll_table',
                $userids,      // <<< QUAN TRỌNG
                $courseids,
                $data->examdate,
                $quizids,
                $enrolmap
            );

        } else if ($data->action === 'result') {

            require_once(__DIR__ . '/classes/report/report_result_table.php');

            $table = new \local_th_online_exam_manager\report\report_result_table(
                'report_result_table',
                $userids,      // <<< QUAN TRỌNG
                $courseids,
                $data->examdate,
                $quizids,
                $enrolmap
            );
        }

        if (!empty($table)) {
            $table->attributes['id'] = 'report_table';
            $table->attributes['style'] = 'width: 100%; text-align: center;';
            $table->attributes['border'] = 1;
            $table->define_baseurl(new moodle_url('/local/th_online_exam_manager/index.php'));
            $table->setup();
            $table->query_db(0);
            $table->out(0, false);

            $lang = current_language();
            echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
            $PAGE->requires->js_call_amd('local_thlib/main', 'init', ['#report_table', 'Report', $lang]);
            echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
        }

        echo $OUTPUT->footer();
        exit;
    }
}

}

/* ===============================
 * DISPLAY
 * =============================== */
echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('contestreport', 'local_th_online_exam_manager'), [
    'class' => 'text-center mb-4'
]);

$mform->display();

if (!empty($errors)) {
    echo $errors;
}

echo $OUTPUT->footer();

?>

<script type="text/javascript">
  	$(document).ready(function() {
		$('input[type=radio][name=filtermode]').change(function() {
			$('#fitem_id_courseids .col-form-label label').removeAttr('hidden');
		    $('#fitem_id_examdate .col-form-label label').removeAttr('hidden');
		    $('#fitem_id_userids .col-form-label label').removeAttr('hidden');
		});
});
</script>