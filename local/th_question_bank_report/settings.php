<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_th_question_bank_report', get_string('title', 'local_th_question_bank_report'));
    $ADMIN->add('localplugins', $settings);

    $coursecategories = core_course_category::make_categories_list();

    $settings->add(new admin_setting_configmultiselect(
        'local_th_question_bank_report/coursecategories',
        get_string('coursecategories', 'local_th_question_bank_report'),
        get_string('coursecategories_desc', 'local_th_question_bank_report'),
        array(), 
        $coursecategories
    ));

    $version = substr($CFG->release, 0, 1);
    if ($version < 4) {
        $sql = "SELECT c.id AS courseid, c.fullname AS coursename, c.shortname AS courseshortname, 
                    u.id AS userid, u.email AS email, CONCAT(u.firstname, ' ', u.lastname) AS fullname,
                    SUM(CASE WHEN l.action = 'created' THEN 1 ELSE 0 END) AS questionscreated,
                    SUM(CASE WHEN l.action = 'updated' AND l.timecreated = q.timemodified THEN 1 ELSE 0 END) AS questionsedited,
                    (SELECT COUNT(q.id)
                        FROM {question_categories} qc
                        JOIN {question} q ON qc.id = q.category
                        JOIN {context} ctx ON ctx.id = qc.contextid
                        WHERE ctx.instanceid = c.id 
                        AND ctx.contextlevel = 50
                        AND q.parent = 0
                        AND q.hidden = 0) AS totalquestions
                FROM {logstore_standard_log} l
                JOIN {course} c ON l.courseid = c.id
                JOIN {user} u ON l.userid = u.id
                JOIN {course_categories} cat ON c.category = cat.id
                JOIN {question} q ON q.id = l.objectid
                WHERE l.target = 'question' 
                AND q.hidden = 0
                AND q.parent = 0
                AND l.action IN ('created', 'updated')
                AND {sqlcondition}
                GROUP BY c.id, c.fullname, c.shortname, u.id, u.email
                ORDER BY cat.name, c.fullname, u.email;";
    } else {
        // $sql = "SELECT 
        //         c.id AS courseid, 
        //         c.fullname AS coursename, 
        //         c.shortname AS courseshortname, 
        //         u.id AS userid, 
        //         u.email AS email,
        //         CONCAT(u.firstname, ' ', u.lastname) AS fullname,
        //         SUM(CASE 
        //                 WHEN l.action = 'created' 
        //                 AND (qv.status = 'ready' OR qv.status = 'draft') 
        //                 AND qv.version = 1
        //             THEN 1 ELSE 0 
        //         END) AS questionscreated,
        //         SUM(CASE 
        //                 WHEN l.action = 'created' 
        //                 AND (qv.status = 'ready' OR qv.status = 'draft') 
        //                 AND qv.version != 1 
        //             THEN 1 ELSE 0 
        //         END) AS questionsedited,
        //         (SELECT COUNT(*)
        //         FROM {question_versions} qv_sub
        //         JOIN {question_bank_entries} qbe_sub ON qbe_sub.id = qv_sub.questionbankentryid
        //         JOIN {question_categories} qc_sub ON qc_sub.id = qbe_sub.questioncategoryid
        //         JOIN {context} ctx_sub ON ctx_sub.id = qc_sub.contextid
        //         WHERE qv_sub.version = 1
        //         AND ctx_sub.contextlevel = 50  -- 50 = Course level context trong Moodle
        //         AND ctx_sub.instanceid = c.id  -- Liên kết context với courseid
        //         ) AS totalquestions
        //     FROM {logstore_standard_log} l
        //     JOIN {course} c ON l.courseid = c.id
        //     JOIN {user} u ON l.userid = u.id
        //     JOIN {course_categories} cat ON c.category = cat.id
        //     JOIN {question_versions} qv ON qv.questionid = l.objectid
        //     JOIN {question} q ON q.id = qv.questionid
        //     JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
        //     JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
        //     JOIN {context} ctx ON ctx.id = qc.contextid  -- Thêm context vào truy vấn chính
        //     WHERE l.target = 'question' 
        //     AND ctx.contextlevel = 50  -- Chỉ lấy context của course
        //     AND ctx.instanceid = c.id  -- Liên kết đúng khóa học
        //     AND {sqlcondition}
        //     GROUP BY c.id, c.fullname, c.shortname, u.id, u.email
        //     ORDER BY cat.name, c.fullname, u.email;";
        
        $sql = "SELECT 
                c.id AS courseid, 
                c.fullname AS coursename, 
                c.shortname AS courseshortname, 
                u.id AS userid, 
                u.email AS email,
                CONCAT(u.firstname, ' ', u.lastname) AS fullname,
                (SELECT COUNT(*)
                FROM {question_versions} qv_sub
                JOIN {question} q_sub ON q_sub.id = qv_sub.questionid
                JOIN {question_bank_entries} qbe_sub ON qbe_sub.id = qv_sub.questionbankentryid
                JOIN {question_categories} qc_sub ON qc_sub.id = qbe_sub.questioncategoryid
                JOIN {context} ctx_sub ON ctx_sub.id = qc_sub.contextid
                WHERE qv_sub.version = 1
                AND ctx_sub.contextlevel = 50  -- 50 = Course level context trong Moodle
                AND ctx_sub.instanceid = c.id  -- Liên kết context với courseid
                AND (qv_sub.status = 'ready' OR qv_sub.status = 'draft')
                AND q_sub.parent = 0
                ) AS totalquestions,
                SUM(CASE WHEN qv.version = 1 THEN 1 ELSE 0 END) AS questionscreated,  -- Câu hỏi gốc
                SUM(CASE WHEN qv.version > 1 THEN 1 ELSE 0 END) AS questionsedited  -- Câu hỏi đã chỉnh sửa
            FROM {course} c
            JOIN {context} ctx ON ctx.contextlevel = 50 AND ctx.instanceid = c.id
            JOIN {question_categories} qc ON qc.contextid = ctx.id
            JOIN {question_bank_entries} qbe ON qbe.questioncategoryid = qc.id
            JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
            JOIN {question} q ON q.id = qv.questionid
            JOIN {user} u ON u.id = q.createdby
            AND ctx.contextlevel = 50  -- Chỉ lấy context của course
            AND ctx.instanceid = c.id  -- Liên kết đúng khóa học
            AND {sqlcondition}
            WHERE (qv.status = 'ready' OR qv.status = 'draft') AND q.parent = 0
            GROUP BY c.id, c.fullname, c.shortname, u.id, u.email
            ORDER BY c.fullname, u.email;
            ";
    } 

    $settings->add(new admin_setting_configtextarea(
        'local_th_question_bank_report/queries',
        get_string('sql_query_question_bank_report', 'local_th_question_bank_report'),
        '',
        $sql, PARAM_TEXT));
}
