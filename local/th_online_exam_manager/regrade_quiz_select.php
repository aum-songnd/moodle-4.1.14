<?php
require_once('../../config.php');
require_once('lib.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/lib.php');

$courseid = $COURSE->id;
$status = optional_param('status', 'pending', PARAM_TEXT);
$context = context_course::instance($courseid);

require_login($courseid);
require_capability('local/th_online_exam_manager:manager_regrade', $context);

$PAGE->set_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => $status]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('select_quiz_to_assign_regrade', 'local_th_online_exam_manager'));
$PAGE->set_pagelayout('admin');

$baseurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', ['status' => $status]);
$settingsnode = $PAGE->settingsnav->add(get_string('select_quiz_to_assign_regrade', 'local_th_online_exam_manager'), $baseurl);
$settingsnode->make_active();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('all_assigned_regrade_requests', 'local_th_online_exam_manager'));

$baseurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => $status));

if ($editcontrols = th_regrade_quiz_select_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}

if($status == 'waitingreview' || $status == 'reviewed') {

    if($status == 'reviewed') {
        $regrades = $DB->get_records_select('th_exam_regrade_requests', "status = 'reviewed'");
    } else {
        $regrades = $DB->get_records_select('th_exam_regrade_requests', "status = 'regraded'");
    }

    // Gom theo attemptid
    $groupedregrades = [];

    foreach ($regrades as $r) {
        $attemptid = $r->attemptid;
        if (!isset($groupedregrades[$attemptid])) {
            $groupedregrades[$attemptid] = [];
        }
        $groupedregrades[$attemptid][] = $r;
    }
} else if ($status == 'waiting') {
    $regrades = $DB->get_records('th_exam_regrade_assign');
    $url = new moodle_url('/local/th_online_exam_manager/quiz_select.php');
    $button = html_writer::link($url, '✔️ Gán bài phúc khảo', [
        'class' => 'btn btn-primary btn-sm',
        'style' => 'margin-bottom: 10px;'
    ]);
    echo $button;
} else {
    $regrades = $DB->get_records_select('th_exam_regrade_requests', "status = '$status'");
}

if (empty($regrades)) {
    echo $OUTPUT->notification(get_string('noregraderequestsfound', 'local_th_online_exam_manager'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

$PAGE->requires->js_call_amd('local_th_online_exam_manager/status_toggle', 'init');

$table = new html_table();

if($status == 'waitingreview') {
    $table->head = [
        get_string('course', 'local_th_online_exam_manager'),
        get_string('quiz', 'local_th_online_exam_manager'),
        get_string('student', 'local_th_online_exam_manager'),
        get_string('attemptid', 'local_th_online_exam_manager'),
        get_string('reviewer', 'local_th_online_exam_manager'),
        get_string('status', 'local_th_online_exam_manager'),
        get_string('note', 'local_th_online_exam_manager'),
        get_string('grade', 'local_th_online_exam_manager'),
        get_string('timeassigned', 'local_th_online_exam_manager'),
        get_string('review', 'local_th_online_exam_manager'),
        get_string('actions', 'local_th_online_exam_manager')
    ];
    $table->data = [];
    
    foreach ($groupedregrades as $attemptid => $records) {
        if (empty($records)) {
            continue;
        }
    
        // Dữ liệu chung cho attempt
        $first = $records[0];
        $quiz_data = $DB->get_record_sql("
            SELECT q.name AS quizname, c.fullname AS coursename
            FROM {quiz} q
            JOIN {course} c ON q.course = c.id
            WHERE q.id = :quizid
        ", ['quizid' => $first->quizid]);

        $quizname = $quiz_data->quizname;
        $coursename = $quiz_data->coursename;
        $student = core_user::get_user($first->userid);
    
        $rowspan = count($records);
        $cellcourse = new html_table_cell(format_string($coursename));
        $cellcourse->rowspan = $rowspan;

        $cellquiz = new html_table_cell(format_string($quizname));
        $cellquiz->rowspan = $rowspan;

        $cellstudent = new html_table_cell(fullname($student));
        $cellstudent->rowspan = $rowspan;
    
        $cellattemptid = new html_table_cell($attemptid);
        $cellattemptid->rowspan = $rowspan;
        
        $formid = 'updategradeform_' . $first->id;
        $form = html_writer::start_tag('form', [
            'id' => $formid,
            'method' => 'post',
            'action' => new moodle_url('/local/th_online_exam_manager/update_grade.php')
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'userid',
            'value' => $first->userid
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'quizid',
            'value' => $first->quizid
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'attemptid',
            'value' => $first->attemptid
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'number',
            'name' => 'grade',
            'min' => 0,
            'max' => 10,
            'step' => '0.01',
            'placeholder' => 'Nhập điểm',
            'style' => 'width: 80px; margin-right: 5px;'
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'submit',
            'value' => 'Cập nhật',
            'class' => 'btn btn-primary btn-sm'
        ]);

        $form .= html_writer::end_tag('form');

        $actioncell = new html_table_cell($form);
        $actioncell->rowspan = $rowspan;

        $token = encrypt_attempt_id($attemptid);
        $review_url = new moodle_url('/mod/quiz/report/thessaygrader/review.php', ['attempt_token' => $token, 'action' => 'regrade', 'status' => $status]);
        $reviewaction = html_writer::link($review_url, get_string('review', 'local_th_online_exam_manager'), ['class' => 'btn btn-secondary btn-sm']);
        
        $reviewaction_cell = new html_table_cell($reviewaction);
        $reviewaction_cell->rowspan = $rowspan;

        foreach ($records as $index => $r) {
            $reviewer = $r->reviewerid ? core_user::get_user($r->reviewerid) : null;
    
            $statusoptions = [
                'pending' => get_string('pending', 'local_th_online_exam_manager'),
                'regraded' => get_string('regraded', 'local_th_online_exam_manager'),
                // 'waitingreview' => get_string('waitingreview', 'local_th_online_exam_manager'),
                'reviewed' => get_string('reviewed', 'local_th_online_exam_manager'),
            ];
    
            $statusselect = html_writer::select(
                $statusoptions,
                'status_' . $r->id,
                $r->status,
                false,
                [
                    'class' => 'regrade-status-select',
                    'data-id' => $r->id
                ]
            );
    
            $note = $r->note ?: '-';
            $grade = $r->grade ? format_float($r->grade, 2) : get_string('nochange', 'local_th_online_exam_manager');
            $time = userdate($r->timerequested);
    
            $row = [];
    
            if ($index == 0) {
                // Dòng đầu tiên thêm 3 cột rowspan
                $row[] = $cellcourse;
                $row[] = $cellquiz;
                $row[] = $cellstudent;
                $row[] = $cellattemptid;
            }
    
            $row[] = new html_table_cell($reviewer ? fullname($reviewer) : '-');
            $row[] = new html_table_cell($statusselect);
            $row[] = new html_table_cell($note);
            $row[] = new html_table_cell($grade);
            $row[] = new html_table_cell($time);

            if ($index == 0) {
                $row[] = $reviewaction_cell;
            }
    
            if ($index == 0) {
                $row[] = $actioncell;
            }
    
            $table->data[] = $row;
        }
    }
    
} else if($status == 'reviewed') {
    $table->head = [
        get_string('course', 'local_th_online_exam_manager'),
        get_string('quiz', 'local_th_online_exam_manager'),
        get_string('student', 'local_th_online_exam_manager'),
        get_string('attemptid', 'local_th_online_exam_manager'),
        get_string('reviewer', 'local_th_online_exam_manager'),
        get_string('status', 'local_th_online_exam_manager'),
        get_string('note', 'local_th_online_exam_manager'),
        get_string('grade', 'local_th_online_exam_manager'),
        get_string('grade_pk', 'local_th_online_exam_manager'),
        get_string('timeassigned', 'local_th_online_exam_manager'),
        get_string('actions', 'local_th_online_exam_manager')
    ];
    $table->data = [];
    
    foreach ($groupedregrades as $attemptid => $records) {
        if (empty($records)) {
            continue;
        }
    
        // Dữ liệu chung cho attempt
        $first = $records[0];
        $quiz_data = $DB->get_record_sql("
            SELECT q.name AS quizname, c.fullname AS coursename
            FROM {quiz} q
            JOIN {course} c ON q.course = c.id
            WHERE q.id = :quizid
        ", ['quizid' => $first->quizid]);

        $quizname = $quiz_data->quizname;
        $coursename = $quiz_data->coursename;
        $student = core_user::get_user($first->userid);
    
        $rowspan = count($records);
        $cellcourse = new html_table_cell(format_string($coursename));
        $cellcourse->rowspan = $rowspan;

        $cellquiz = new html_table_cell(format_string($quizname));
        $cellquiz->rowspan = $rowspan;

        $cellstudent = new html_table_cell(fullname($student));
        $cellstudent->rowspan = $rowspan;
    
        $cellattemptid = new html_table_cell($attemptid);
        $cellattemptid->rowspan = $rowspan;

        // ----- GỘP CỘT GRADE -----
        $attemptgrade = 'Không thay đổi';
        $rec = $records[0];
        $params = [
            'attemptid' => $rec->attemptid,
            'userid'    => $rec->userid,
            'idnumber'  => 'pk', // đổi thành idnumber thực tế của cột điểm pk
        ];
        $grade_pk = $DB->get_record_sql("SELECT gg.id, gg.finalgrade
                        FROM {grade_grades} gg
                        JOIN {grade_items} gi       ON gi.id = gg.itemid
                        JOIN {quiz_attempts} qa     ON qa.id = :attemptid
                        JOIN {quiz} q               ON q.id = qa.quiz
                        WHERE gi.courseid = q.course
                        AND gg.userid = :userid
                        AND gi.itemtype = 'manual'
                        AND gi.idnumber = :idnumber      -- ví dụ: 'pk' hoặc 'regrade'
                        LIMIT 1;", $params);
        
        if ($grade_pk->finalgrade !== null && $grade_pk->finalgrade !== '') {
            $attemptgrade = format_float($grade_pk->finalgrade, 2);
        }

        $cellgrade = new html_table_cell($attemptgrade);
        $cellgrade->rowspan = $rowspan;
        
        $formid = 'updategradeform_' . $first->id;
        $form = html_writer::start_tag('form', [
            'id' => $formid,
            'method' => 'post',
            'action' => new moodle_url('/local/th_online_exam_manager/update_grade.php')
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'userid',
            'value' => $first->userid
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'quizid',
            'value' => $first->quizid
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'attemptid',
            'value' => $first->attemptid
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'number',
            'name' => 'grade',
            'min' => 0,
            'max' => 10,
            'step' => '0.01',
            'placeholder' => 'Nhập điểm',
            'style' => 'width: 80px; margin-right: 5px;'
        ]);

        $form .= html_writer::empty_tag('input', [
            'type' => 'submit',
            'value' => 'Cập nhật',
            'class' => 'btn btn-primary btn-sm'
        ]);

        $form .= html_writer::end_tag('form');

        $actioncell = new html_table_cell($form);
        $actioncell->rowspan = $rowspan;
    
        foreach ($records as $index => $r) {
            $reviewer = $r->reviewerid ? core_user::get_user($r->reviewerid) : null;
    
            $statusoptions = [
                'pending' => get_string('pending', 'local_th_online_exam_manager'),
                'regraded' => get_string('regraded', 'local_th_online_exam_manager'),
                'reviewed' => get_string('reviewed', 'local_th_online_exam_manager'),
            ];
    
            $statusselect = html_writer::select(
                $statusoptions,
                'status_' . $r->id,
                $r->status,
                false,
                [
                    'class' => 'regrade-status-select',
                    'data-id' => $r->id
                ]
            );
    
            $note = $r->note ?: '-';
            $grade = $r->grade ? format_float($r->grade, 2) : get_string('nochange', 'local_th_online_exam_manager');
            $time = userdate($r->timerequested);
    
            $row = [];
    
            if ($index == 0) {
                // Dòng đầu tiên thêm 3 cột rowspan
                $row[] = $cellcourse;
                $row[] = $cellquiz;
                $row[] = $cellstudent;
                $row[] = $cellattemptid;
            }
    
            $row[] = new html_table_cell($reviewer ? fullname($reviewer) : '-');
            $row[] = new html_table_cell($statusselect);
            $row[] = new html_table_cell($note);
            $row[] = new html_table_cell($grade);

            if ($index == 0) {
                // Chỉ thêm cột GRADE đã gộp ở dòng đầu tiên (đúng vị trí cột grade theo header)
                $row[] = $cellgrade;
            }

            $row[] = new html_table_cell($time);
    
            if ($index == 0) {
                $row[] = $actioncell;
            }
    
            $table->data[] = $row;
        }
    }
    
} else if ($status == 'waiting') {
    $table->head = [
        get_string('course', 'local_th_online_exam_manager'),
        get_string('quiz', 'local_th_online_exam_manager'),
        get_string('student', 'local_th_online_exam_manager'),
        get_string('attemptid', 'local_th_online_exam_manager'),
        get_string('actions', 'local_th_online_exam_manager')
    ];
    $table->data = [];

    foreach ($regrades as $r) {
        $quiz_data = $DB->get_record_sql("
            SELECT q.name AS quizname, c.fullname AS coursename
            FROM {quiz} q
            JOIN {course} c ON q.course = c.id
            WHERE q.id = :quizid
        ", ['quizid' => $r->quizid]);

        $quizname = $quiz_data->quizname;
        $coursename = $quiz_data->coursename;
        
        $student = core_user::get_user($r->userid);
        $deleteurl = new moodle_url('/local/th_online_exam_manager/regrade_assign_delete.php', ['id' => $r->id, 'status' => $status]);
        $deletebtn = html_writer::link($deleteurl, '🗑️ ' . get_string('delete', 'local_th_online_exam_manager'), ['class' => 'btn btn-danger btn-sm', 'id' => 'regrade_delete']);
        
        $assignurl = new moodle_url('/local/th_online_exam_manager/regrade_attempt_assign.php', [
            'attemptid' => $r->attemptid
        ]);
        $assignbtn = html_writer::link($assignurl, 'Gán giáo viên phúc khảo', ['class' => 'btn btn-success btn-sm', 'id' => 'regrade_assign']);
    
        $actions = $deletebtn . $assignbtn;
    
        $table->data[] = [
            format_string($coursename),
            format_string($quizname),
            fullname($student),
            $r->attemptid,
            $actions
        ];
    }
} else {
    $table->head = [
        get_string('course', 'local_th_online_exam_manager'),
        get_string('quiz', 'local_th_online_exam_manager'),
        get_string('student', 'local_th_online_exam_manager'),
        get_string('attemptid', 'local_th_online_exam_manager'),
        get_string('reviewer', 'local_th_online_exam_manager'),
        get_string('status', 'local_th_online_exam_manager'),
        get_string('note', 'local_th_online_exam_manager'),
        get_string('grade', 'local_th_online_exam_manager'),
        get_string('timeassigned', 'local_th_online_exam_manager')
    ];

    if($status != 'reviewed') {
        $table->head[] = get_string('actions', 'local_th_online_exam_manager');
    }

    $table->data = [];

    foreach ($regrades as $r) {
        $quiz_data = $DB->get_record_sql("
            SELECT q.name AS quizname, c.fullname AS coursename
            FROM {quiz} q
            JOIN {course} c ON q.course = c.id
            WHERE q.id = :quizid
        ", ['quizid' => $r->quizid]);

        $quizname = $quiz_data->quizname;
        $coursename = $quiz_data->coursename;
        
        $student = core_user::get_user($r->userid);
        $reviewer = $r->reviewerid ? core_user::get_user($r->reviewerid) : null;
    
        $statusoptions = [
            'pending' => get_string('pending', 'local_th_online_exam_manager'),
            'regraded' => get_string('regraded', 'local_th_online_exam_manager'),
            // 'waitingreview' => get_string('waitingreview', 'local_th_online_exam_manager'),
            'reviewed' => get_string('reviewed', 'local_th_online_exam_manager'),
            
        ];
        
        $statusselect = html_writer::select(
            $statusoptions,                            // options
            'status_' . $r->id,                        // name
            $r->status,                                // default selected
            false,                                     // no 'choose'
            [
                'class' => 'regrade-status-select',
                'data-id' => $r->id
            ]
        );
    
        // Nút xóa (nếu chưa reviewed)
        if ($r->status !== 'reviewed' && $r->status !== 'rejected') {
            $deleteurl = new moodle_url('/local/th_online_exam_manager/regrade_delete.php', ['id' => $r->id, 'status' => $status]);
            $deletebtn = html_writer::link($deleteurl, '🗑️ ' . get_string('delete', 'local_th_online_exam_manager'), ['class' => 'btn btn-danger btn-sm', 'id' => 'regrade_delete']);
        } else {
            $deletebtn = '';
        }
        $actions = $deletebtn;
        
        if($status == 'reviewed') {
            $table->data[] = [
                format_string($coursename),
                format_string($quizname),
                fullname($student),
                $r->attemptid,
                $reviewer ? fullname($reviewer) : '-',
                $statusselect,
                // $r->reason,
                $r->note,
                $r->grade ? format_float($r->grade, 2) : get_string('nochange', 'local_th_online_exam_manager'),
                userdate($r->timerequested)
            ];
        } else {
            $table->data[] = [
                format_string($coursename),
                format_string($quizname),
                fullname($student),
                $r->attemptid,
                $reviewer ? fullname($reviewer) : '-',
                $statusselect,
                // $r->reason,
                $r->note,
                $r->grade ? format_float($r->grade, 2) : get_string('nochange', 'local_th_online_exam_manager'),
                userdate($r->timerequested),
                $actions
            ];
        }
    }
}


$table->attributes['class'] = 'report_table';
$table->attributes['style'] = 'width: 100%; text-align: center;';
$table->attributes['border'] = 1;

echo html_writer::table($table);

$lang = current_language();
echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.report_table', 'Report', $lang));

echo $OUTPUT->footer();