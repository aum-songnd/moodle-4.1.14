<?php
function ngay($date) {
    return date('d/m/Y', $date);
}

/**
 * demtk1 function Lấy các Học viên được ghi danh trong khoảng thời gian
 *
 * @param [type] $startdate     ngày bắt đầu
 * @param [type] $enddate       ngày kết thúc
 * @return [array]
 */
function demtk1($startdate, $enddate) {
    global $DB;
    //echo date('d/m/Y H:i:s', $startdate) . ' - ' . date('d/m/Y H:i:s', $enddate) . '<br>';
    return $DB->get_records_sql("SELECT DISTINCT row_number() OVER() as stt,u.id as userid,
		 c.id as courseid, c.fullname,r.shortname as shortname,ue.status,ue.timestart,ue.timeend
                FROM {user_enrolments} ue, {course} c, {user} u, {enrol} e, {role_assignments} ra, {context} cnt, {role} r
                WHERE c.id=e.courseid AND u.id=ue.userid AND e.id=ue.enrolid AND u.id=ra.userid AND u.suspended=0  AND ra.contextid = cnt.id AND cnt.instanceid = c.id AND r.id = ra.roleid AND u.deleted=0 AND ue.timestart>=? AND ue.timestart<=? ORDER BY ue.timestart DESC", [$startdate, $enddate]);

}