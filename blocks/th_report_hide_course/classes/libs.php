<?php

function get_list_hide_courses($startdate) {
    global $DB;
    $listcourses = [];
    $sql = "SELECT * FROM {course} WHERE NOT id = 1 AND startdate = '$startdate' ORDER BY fullname";
    $listcourses = $DB->get_records_sql($sql);
    return $listcourses;
}

function get_list_courses_status($startdate, $enddate) {
    global $DB;
    $listcourses = [];
    $sql = "SELECT c.*, cs.timecreated AS timecreated_status, cs.timemodified AS timemodified_status, cs.ishidden
            FROM {course} c
            LEFT JOIN (
                SELECT cs.course, cs.timecreated, cs.timemodified, cs.ishidden
                FROM {block_th_course_status} cs
                INNER JOIN (
                    SELECT course, MAX(timecreated) AS maxtimecreated
                    FROM {block_th_course_status}
                    GROUP BY course
                ) cs_max ON cs.course = cs_max.course AND cs.timecreated = cs_max.maxtimecreated
            ) cs ON c.id = cs.course
            WHERE NOT c.id = 1 AND c.startdate >= '$startdate' AND c.startdate <= '$enddate'
            ORDER BY c.fullname
            ";
                
    $listcourses = $DB->get_records_sql($sql);
    return $listcourses;
}

function get_list_courses_teacher_status($startdate, $enddate) {
    global $DB, $USER;
    $listcourses = [];
    $sql = "SELECT c.*, cs.timecreated AS timecreated_status, cs.timemodified AS timemodified_status, cs.ishidden
                FROM {course} c
                LEFT JOIN (
                    SELECT cs.course, cs.timecreated, cs.timemodified, cs.ishidden
                    FROM {block_th_course_status} cs
                    INNER JOIN (
                        SELECT course, MAX(timecreated) AS maxtimecreated
                        FROM {block_th_course_status}
                        GROUP BY course
                    ) cs_max ON cs.course = cs_max.course AND cs.timecreated = cs_max.maxtimecreated
                ) cs ON c.id = cs.course
                INNER JOIN (
                    SELECT DISTINCT ctx.instanceid AS course_id
                    FROM {role_assignments} ra
                    JOIN {context} ctx ON ra.contextid = ctx.id
                    JOIN {role} r ON ra.roleid = r.id
                    WHERE ra.userid = $USER->id
                    AND (r.shortname = 'editingteacher' OR r.shortname = 'teacher')
                    AND ctx.contextlevel = 50
                ) user_courses ON c.id = user_courses.course_id
                WHERE NOT c.id = 1 AND c.startdate >= '$startdate' AND c.startdate <= '$enddate'
                ORDER BY c.fullname;
            ";
                
    $listcourses = $DB->get_records_sql($sql);
    return $listcourses;
}

?>