<?php
// Yêu cầu các file cấu hình và thư viện cần thiết
require_once('../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/user/renderer.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/report/grader/lib.php');
require_once($CFG->dirroot . '/local/thlib/lib.php');
require_once($CFG->dirroot . '/local/thlib/externallib.php');
require_once($CFG->dirroot . '/local/thlib/th_form.php');
require_once($CFG->libdir . '/excellib.class.php');

// Khai báo biến toàn cục
global $DB, $OUTPUT, $PAGE, $COURSE, $SITE;
$courseid = $COURSE->id;

// Lấy tham số từ request
$downloadtype = optional_param('downloadtype', 0, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
$monids_str = optional_param('dot_mon_monids', '', PARAM_RAW_TRIMMED);
$reporttype = optional_param('reporttype', 'detail', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);

// Kiểm tra khóa học hợp lệ
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    print_error('invalidcourse', 'report_th_gradereport', $courseid);
}

// Yêu cầu đăng nhập và kiểm tra quyền truy cập
require_login($courseid);
require_capability('report/th_gradereport:report', context_course::instance($courseid));

// Thiết lập trang
$url = new moodle_url('/report/th_gradereport/report.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('report');
$PAGE->navbar->add(get_string('report'));
$PAGE->navbar->add(get_string('breadcrumb', 'report_th_gradereport'));
$PAGE->navbar->add(get_string('title', 'report_th_gradereport'));
$PAGE->requires->css(new moodle_url('/report/th_gradereport/styles.css'));

// Lấy cấu hình từ hệ thống
$format_date = get_config('local_thlib', 'format_date');
$th_student_code = get_config('report_th_gradereport', 'th_student_code');
$th_student_class = get_config('report_th_gradereport', 'th_student_class');
$th_student_class2 = get_config('report_th_gradereport', 'th_student_class2');
$th_crm_code = get_config('report_th_gradereport', 'th_crm_code');
$th_crm_cohort2 = get_config('report_th_gradereport', 'th_crm_cohort2');
$th_user_role = get_config('report_th_gradereport', 'th_user_role');
$th_dob = get_config('report_th_gradereport', 'th_dob');
$th_study_field = get_config('report_th_gradereport', 'th_study_field');
$th_gender = get_config('report_th_gradereport', 'th_gender');

// Hàm tạo nhãn cho grade item
function label_for_item(stdClass $gi): string {
    if (!empty($gi->itemname)) {
        return $gi->itemname;
    }
    switch ($gi->itemtype) {
        case 'course':
            return get_string('course_total', 'report_th_gradereport', 'Tổng khóa học');
        case 'category':
            return get_string('category_average', 'report_th_gradereport', 'Điểm trung bình thành phần');
        default:
            return ucfirst((string)$gi->itemtype);
    }
}

// Hàm kiểm tra tiêu đề cơ bản
function is_basehead($label): bool {
    $baseheads = [
        get_string('stt', 'report_th_gradereport'),
        get_string('student_name', 'report_th_gradereport'),
        get_string('student_code', 'report_th_gradereport'),
        get_string('course_code', 'report_th_gradereport'),
        get_string('class_code', 'report_th_gradereport'),
        get_string('course_code_2', 'report_th_gradereport'),
        get_string('class_code_2', 'report_th_gradereport'),
        get_string('dob', 'report_th_gradereport'),
        get_string('role', 'report_th_gradereport'),
        get_string('gender', 'report_th_gradereport'),
        get_string('major', 'report_th_gradereport'),
        get_string('course_shortname', 'report_th_gradereport'),
        get_string('course_name', 'report_th_gradereport')
    ];
    return in_array($label, $baseheads, true);
}

// Hàm tạo tiêu đề cho cột điểm
function create_grade_header($label, $itemtype, $itemmodule = ''): string {
    global $OUTPUT;
    $truncated_label = is_basehead($label) ? $label : (strlen($label) > 40 ? substr($label, 0, 40) . '...' : $label);
    $icon_html = '';
    if ($itemtype === 'mod' && $itemmodule) {
        $icon_html = $OUTPUT->pix_icon('icon', '', 'mod_' . $itemmodule, ['class' => 'th-col-ico']);
    } else {
        switch ($itemtype) {
            case 'course':
                $icon_html = $OUTPUT->pix_icon('i/course', '', 'moodle', ['class' => 'th-col-ico']); break;
            case 'category':
                $icon_html = $OUTPUT->pix_icon('i/category', '', 'moodle', ['class' => 'th-col-ico']); break;
            case 'manual':
                $icon_html = $OUTPUT->pix_icon('i/manual_item', '', 'moodle', ['class' => 'th-col-ico']); break;
            default:
                $icon_html = $OUTPUT->pix_icon('i/grades', '', 'moodle', ['class' => 'th-col-ico']); break;
        }
    }
    $header_html = '<div class="th-header-content" title="' . htmlspecialchars($label) . '">';
    $header_html .= '<span class="th-header-text">' . htmlspecialchars($truncated_label) . '</span>';
    $header_html .= $icon_html;
    $header_html .= '</div>';
    return $header_html;
}

// Xử lý dữ liệu từ form hoặc tham số
$mform = new \report_th_gradereport\form\report_th_gradereport();
if ($fromform = $mform->get_data()) {
    $dot_mo_monids = array_map('intval', (array)$fromform->dot_mon_monids);
    $reporttype = $fromform->reporttype ?? 'detail';
} else {
    $dot_mo_monids = $monids_str ? array_map('intval', explode(',', $monids_str)) : [];
}

// Nếu có dữ liệu dot_mo_monids thì xử lý
if (!empty($dot_mo_monids)) {
    $startdate_str = implode(',', $dot_mo_monids);

    // Truy vấn SQL lấy thông tin người dùng và khóa học
    $sql = "SELECT
                CONCAT(u.id, '_', c.id) AS uniqueid,
                u.id AS userid,
                u.username,
                CONCAT(u.firstname, ' ', u.lastname) AS fullname,
                c.id AS courseid,
                c.fullname AS course_fullname,
                c.shortname AS course_shortname,
                MAX(CASE WHEN uif.shortname = :student_code THEN uid.data END) AS ma_hoc_vien,
                MAX(CASE WHEN uif.shortname = :student_class THEN uid.data END) AS ma_lop,
                MAX(CASE WHEN uif.shortname = :student_class2 THEN uid.data END) AS ma_lop_2,
                MAX(CASE WHEN uif.shortname = :crm_code THEN uid.data END) AS ma_khoa,
                MAX(CASE WHEN uif.shortname = :crm_cohort2 THEN uid.data END) AS ma_khoa_2,
                MAX(CASE WHEN uif.shortname = :user_role THEN uid.data END) AS vai_tro,
                MAX(CASE WHEN uif.shortname = :dob THEN uid.data END) AS ngay_sinh,
                MAX(CASE WHEN uif.shortname = :study_field THEN uid.data END) AS nganh_hoc,
                MAX(CASE WHEN uif.shortname = :gender THEN uid.data END) AS gioi_tinh
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {course} c ON c.id = e.courseid
            JOIN {role_assignments} ra on ra.userid = u.id 
            JOIN {role} r on ra.roleid = r.id
            JOIN {context} ctx on ctx.id = ra.contextid
            LEFT JOIN {user_info_data} uid ON uid.userid = u.id
            LEFT JOIN {user_info_field} uif ON uid.fieldid = uif.id
            WHERE c.startdate IN (:startdates) AND r.shortname = 'student' AND ctx.contextlevel=50 AND ctx.instanceid = c.id
            GROUP BY u.id, u.username, u.firstname, u.lastname, c.id, c.fullname, c.shortname 
            ORDER BY c.fullname, u.username";

    $params = [
        'startdates' => $startdate_str,
        'student_code' => $th_student_code,
        'student_class' => $th_student_class,
        'student_class2' => $th_student_class2,
        'crm_code' => $th_crm_code,
        'crm_cohort2' => $th_crm_cohort2,
        'user_role' => $th_user_role,
        'dob' => $th_dob,
        'study_field' => $th_study_field,
        'gender' => $th_gender,
    ];

    $allrecords = $DB->get_records_sql($sql, $params);
    $courseids = array_unique(array_map(fn($r) => $r->courseid, $allrecords));

    // Lấy danh sách grade items
    $sortedLabels = [];
    $gradeItemsByLabel = [];
    $uniqueLabels = [];
    if (!empty($courseids)) {
        $gi_sql = ($reporttype === 'detail')
            ? "SELECT gi.id, gi.courseid, gi.itemname, gi.itemtype, gi.itemmodule, gi.iteminstance
               FROM {grade_items} gi
               WHERE gi.courseid IN (" . implode(',', $courseids) . ")
                 AND gi.itemtype <> 'category'
               ORDER BY gi.courseid, gi.sortorder"
            : "SELECT gi.id, gi.courseid, gi.itemname, gi.itemtype, gi.itemmodule, gi.iteminstance
               FROM {grade_items} gi
               WHERE gi.courseid IN (" . implode(',', $courseids) . ")
                 AND gi.itemtype <> 'category' AND gi.itemmodule = 'quiz'
               ORDER BY gi.courseid, gi.sortorder";

        $gradeitems = $DB->get_records_sql($gi_sql);

        foreach ($gradeitems as $gi) {
            $label = label_for_item($gi);
            $uniqueLabels[$label] = $gi->itemtype;
            $gradeItemsByLabel[$label][] = $gi;
        }

        // Sắp xếp nhãn
        $groupedLabels = ['mod' => [], 'manual' => [], 'others' => [], 'course' => []];
        foreach ($uniqueLabels as $label => $itemtype) {
            if ($itemtype === 'mod') {
                $groupedLabels['mod'][] = $label;
            } elseif ($itemtype === 'manual') {
                $groupedLabels['manual'][] = $label;
            } elseif ($itemtype === 'course') {
                $groupedLabels['course'][] = $label;
            } else {
                $groupedLabels['others'][] = $label;
            }
        }
        if ($reporttype === 'status') {
            $sortedLabels = array_merge(
                $groupedLabels['mod'],
                $groupedLabels['manual'],
                $groupedLabels['others'],
                $groupedLabels['course']
            );
        } else {
            $sortedLabels = array_merge(
                $groupedLabels['mod'],
                $groupedLabels['manual'],
                $groupedLabels['others'],
                [get_string('dkt','report_th_gradereport')],
                $groupedLabels['course']
            );
        }
    }

    // === Export Excel ===
    if ($download === 'excel') {
        $filename = 'gradereport_' . date('Ymd_His') . '.xlsx';
        $workbook = new MoodleExcelWorkbook($filename);
        $workbook->send($filename);
        $worksheet = $workbook->add_worksheet('Grades');

        // Header
        $headers = [
            get_string('stt', 'report_th_gradereport'),
            get_string('student_name', 'report_th_gradereport'),
            get_string('student_code', 'report_th_gradereport'),
            get_string('course_code', 'report_th_gradereport'),
            get_string('class_code', 'report_th_gradereport'),
            get_string('course_code_2', 'report_th_gradereport'),
            get_string('class_code_2', 'report_th_gradereport'),
            get_string('dob', 'report_th_gradereport'),
            get_string('role', 'report_th_gradereport'),
            get_string('gender', 'report_th_gradereport'),
            get_string('major', 'report_th_gradereport'),
            get_string('course_shortname', 'report_th_gradereport'),
            get_string('course_name', 'report_th_gradereport')
        ];
        foreach ($sortedLabels as $label) {
            $headers[] = $label;
        }
        $col = 0;
        foreach ($headers as $head) {
            $worksheet->write(0, $col++, $head);
        }

        // Data
        $rownum = 1;
        $stt = 1;
        foreach ($allrecords as $r) {
            $row = [
                $stt++,
                $r->fullname,
                $r->ma_hoc_vien,
                $r->ma_khoa,
                $r->ma_lop,
                $r->ma_khoa_2,
                $r->ma_lop_2,
                $r->ngay_sinh,
                $r->vai_tro,
                $r->gioi_tinh,
                $r->nganh_hoc,
                $r->course_shortname,
                $r->course_fullname
            ];

            foreach ($sortedLabels as $label) {
                $grade = '';
                if ($label === get_string('dkt', 'report_th_gradereport')) {
                    $condition_item = $DB->get_record('grade_items', [
                        'courseid' => $r->courseid,
                        'idnumber' => 'ddk',
                        'itemtype' => 'manual'
                    ]);
                    if ($condition_item) {
                        $final = $DB->get_field('grade_grades', 'finalgrade', [
                            'itemid' => $condition_item->id,
                            'userid' => $r->userid
                        ]);
                        if ($final !== false && $final !== null) {
                            $grade = ($final == 1)
                                ? get_string('meet_standards', 'report_th_gradereport')
                                : get_string('not_meeting_standards', 'report_th_gradereport');
                        } else {
                            $grade = get_string('not_meeting_standards', 'report_th_gradereport');
                        }
                    } else {
                        $grade = get_string('not_available', 'report_th_gradereport');
                    }
                } else {
                    if (isset($gradeItemsByLabel[$label])) {
                        foreach ($gradeItemsByLabel[$label] as $gi) {
                            if ($gi->courseid == $r->courseid) {
                                $grade = '-';
                                $final = $DB->get_field('grade_grades', 'finalgrade', [
                                    'itemid' => $gi->id,
                                    'userid' => $r->userid
                                ]);
                                if ($reporttype === 'status' && $gi->itemmodule === 'quiz') {
                                    $quizid = $gi->iteminstance;
                                    $total = $DB->count_records('quiz_attempts', [
                                        'quiz' => $quizid,
                                        'userid' => $r->userid,
                                        'preview' => 0
                                    ]);
                                    if ($total == 0) {
                                        $grade = get_string('not_submitted', 'report_th_gradereport', 'Chưa nộp');
                                    } else {
                                        $notfinished = $DB->count_records_select('quiz_attempts',
                                            "quiz = :quiz AND userid = :userid AND preview = 0 AND state <> 'finished'",
                                            ['quiz' => $quizid, 'userid' => $r->userid]
                                        );
                                        if ($notfinished > 0) {
                                            $grade = get_string('not_submitted', 'report_th_gradereport', 'Chưa nộp');
                                        } else {
                                            $grade = ($final !== false && $final !== null)
                                                ? get_string('submitted', 'report_th_gradereport', 'Đã nộp') . ' (' . format_float($final, 2) . ')'
                                                : get_string('submitted_no_grade', 'report_th_gradereport', 'Đã nộp (-)');
                                        }
                                    }
                                } else {
                                    if ($final !== false && $final !== null) {
                                        $grade = format_float($final, 2);
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                $row[] = $grade;
            }

            $col = 0;
            foreach ($row as $cell) {
                $worksheet->write($rownum, $col++, $cell);
            }
            $rownum++;
        }

        $workbook->close();
        exit;
    }
}

// Hiển thị HTML
echo $OUTPUT->header();
echo html_writer::tag('h3', get_string('title', 'report_th_gradereport'));
$mform->display();

if (!empty($dot_mo_monids)) {
    $config = get_config('report_th_gradereport');
    $perpage = $config->number_page_record;
    $offset = $page * $perpage;
    $countsql = "SELECT COUNT(DISTINCT CONCAT(u.id, '-', c.id))
                 FROM {user} u
                 JOIN {user_enrolments} ue ON ue.userid = u.id
                 JOIN {enrol} e ON e.id = ue.enrolid
                 JOIN {course} c ON c.id = e.courseid
                 WHERE c.startdate IN (:startdates)";
    $totalcount = $DB->count_records_sql($countsql, ['startdates' => $startdate_str]);
    $records = $DB->get_records_sql($sql, $params, $offset, $perpage);

    if (!$records) {
        echo $OUTPUT->notification(get_string('norecords', 'report_th_gradereport'), 'notifyproblem');
    } else {
        
        echo $OUTPUT->single_button(
            new moodle_url('/report/th_gradereport/report.php', [
                'dot_mon_monids' => implode(',', $dot_mo_monids),
                'download' => 'excel',
                'reporttype' => $reporttype
            ]),
            get_string('download_excel', 'report_th_gradereport'),
            'get'
        );

        // Render table (giữ nguyên logic gốc)
        $table = new html_table();
        $table->id = 'mydatatable';
        $table->attributes['class'] = 'generaltable boxaligncenter';
        $table->attributes['style'] = 'border:1px solid #000; border-collapse:collapse;';
        $table->head = [
            get_string('stt', 'report_th_gradereport'),
            get_string('student_name', 'report_th_gradereport'),
            get_string('student_code', 'report_th_gradereport'),
            get_string('course_code', 'report_th_gradereport'),
            get_string('class_code', 'report_th_gradereport'),
            get_string('course_code_2', 'report_th_gradereport'),
            get_string('class_code_2', 'report_th_gradereport'),
            get_string('dob', 'report_th_gradereport'),
            get_string('role', 'report_th_gradereport'),
            get_string('gender', 'report_th_gradereport'),
            get_string('major', 'report_th_gradereport'),
            get_string('course_shortname', 'report_th_gradereport'),
            get_string('course_name', 'report_th_gradereport')
        ];
        foreach ($sortedLabels as $label) {
            $itemmodule = '';
            if ($label !== get_string('dkt','report_th_gradereport') && !empty($gradeItemsByLabel[$label])) {
                $first = reset($gradeItemsByLabel[$label]);
                $itemmodule = $first->itemmodule ?? '';
            }
            $table->head[] = create_grade_header($label, $label === get_string('dkt','report_th_gradereport') ? 'manual' : ($uniqueLabels[$label] ?? ''), $itemmodule);
        }

        $stt = $offset + 1;
        foreach ($records as $r) {
            $row = [
                $stt++,
                $r->fullname,
                $r->ma_hoc_vien,
                $r->ma_khoa,
                $r->ma_lop,
                $r->ma_khoa_2,
                $r->ma_lop_2,
                $r->ngay_sinh,
                $r->vai_tro,
                $r->gioi_tinh,
                $r->nganh_hoc,
                $r->course_shortname,
                $r->course_fullname
            ];
            foreach ($sortedLabels as $label) {
                $grade = '';
                if ($label === get_string('dkt', 'report_th_gradereport')) {
                    $condition_item = $DB->get_record('grade_items', [
                        'courseid' => $r->courseid,
                        'idnumber' => 'ddk',
                        'itemtype' => 'manual'
                    ]);
                    if ($condition_item) {
                        $final = $DB->get_field('grade_grades', 'finalgrade', [
                            'itemid' => $condition_item->id,
                            'userid' => $r->userid
                        ]);
                        if ($final !== false && $final !== null) {
                            $grade = ($final == 1)
                                ? get_string('meet_standards', 'report_th_gradereport')
                                : get_string('not_meeting_standards', 'report_th_gradereport');
                        } else {
                            $grade = get_string('not_meeting_standards', 'report_th_gradereport');
                        }
                    } else {
                        $grade = get_string('not_available', 'report_th_gradereport');
                    }
                } else {
                    if (isset($gradeItemsByLabel[$label])) {
                        foreach ($gradeItemsByLabel[$label] as $gi) {
                            if ($gi->courseid == $r->courseid) {
                                $grade = '-';
                                $final = $DB->get_field('grade_grades', 'finalgrade', [
                                    'itemid' => $gi->id,
                                    'userid' => $r->userid
                                ]);
                                if ($reporttype === 'status' && $gi->itemmodule === 'quiz') {
                                    $quizid = $gi->iteminstance;
                                    $total = $DB->count_records('quiz_attempts', [
                                        'quiz' => $quizid,
                                        'userid' => $r->userid,
                                        'preview' => 0
                                    ]);
                                    if ($total == 0) {
                                        $grade = get_string('not_submitted', 'report_th_gradereport', 'Chưa nộp');
                                    } else {
                                        $notfinished = $DB->count_records_select('quiz_attempts',
                                            "quiz = :quiz AND userid = :userid AND preview = 0 AND state <> 'finished'",
                                            ['quiz' => $quizid, 'userid' => $r->userid]
                                        );
                                        if ($notfinished > 0) {
                                            $grade = get_string('not_submitted', 'report_th_gradereport', 'Chưa nộp');
                                        } else {
                                            $grade = ($final !== false && $final !== null)
                                                ? get_string('submitted', 'report_th_gradereport', 'Đã nộp') . ' (' . format_float($final, 2) . ')'
                                                : get_string('submitted_no_grade', 'report_th_gradereport', 'Đã nộp (-)');
                                        }
                                    }
                                } else {
                                    if ($final !== false && $final !== null) {
                                        $grade = format_float($final, 2);
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                $row[] = $grade;
            }
            $table->data[] = $row;
        }

        echo html_writer::start_div('report_th_gradereport');
        echo html_writer::table($table);
        echo html_writer::end_div();

        $baseurl = new moodle_url('/report/th_gradereport/report.php', [
            'dot_mon_monids' => implode(',', $dot_mo_monids),
            'reporttype' => $reporttype
        ]);
        echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    }
}

echo $OUTPUT->footer();