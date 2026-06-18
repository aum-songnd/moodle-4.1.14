<?php
namespace block_th_exam_schedule\output;

defined('MOODLE_INTERNAL') || die();

use html_table;
use html_writer;
use moodle_url;

class table {

    /** @var int */
    private $userid;

    /** @var int|null */
    private $limit;

    /** @var bool */
    private $showdownload;

    public function __construct($userid, $limit = null, $showdownload = true) {
        $this->userid = (int)$userid;
        $this->limit = ($limit === null) ? null : (int)$limit;
        $this->showdownload = (bool)$showdownload;
    }

    public function render(): string {
        global $DB;

        $shortname = trim((string)get_config('block_th_exam_schedule', 'student_code'));
        $studentcode = '';
        if ($shortname !== '') {
            $studentcode = (string)$DB->get_field_sql("
                SELECT uid.data
                  FROM {user_info_data} uid
                  JOIN {user_info_field} uif ON uif.id = uid.fieldid
                 WHERE uid.userid = :userid AND uif.shortname = :shortname
            ", ['userid' => $this->userid, 'shortname' => $shortname]);
            $studentcode = trim($studentcode);
        }

        if ($studentcode === '') {
            return html_writer::div(get_string('nostudentcode', 'block_th_exam_schedule'), 'alert alert-warning');
        }

        $todaystart = usergetmidnight(time());

        // Nếu có limit (block) => chỉ hiện các môn có ngày thi >= hôm nay.
        $isblock = ($this->limit !== null);

        $where = "e.studentcode = :studentcode";
        $params = ['studentcode' => $studentcode];

        if ($isblock) {
            $where .= " AND e.examdate >= :todaystart";   // ✅ đổi từ > thành >=
            $params['todaystart'] = $todaystart;
        }

        // Full view => sắp xếp ngày thi mới nhất trước
        $orderby = ($this->limit === null)
        ? "e.examdate DESC, e.examslot ASC"
        : "e.examdate ASC, e.examslot ASC";
    
    
        $sql = "
            SELECT e.id, e.coursecode, c.coursename, e.examdate, e.examslot
              FROM {block_thexamschedule_exam} e
         LEFT JOIN {block_thexamschedule_course} c ON c.coursecode = e.coursecode
             WHERE {$where}
          ORDER BY {$orderby}
        ";

        $rows = $DB->get_records_sql($sql, $params, 0, $this->limit ?? 0);

        if (!$rows) {
            return html_writer::div(get_string('nocontent', 'block_th_exam_schedule'), 'alert alert-info');
        }

        $out = '';

        $t = new html_table();
        $t->head = [
            get_string('col_no', 'block_th_exam_schedule'),
            get_string('col_coursecode', 'block_th_exam_schedule'),
            get_string('col_coursename', 'block_th_exam_schedule'),
            get_string('col_examdate', 'block_th_exam_schedule'),
            get_string('col_examslot', 'block_th_exam_schedule'),
        ];

        $i = 1;
        foreach ($rows as $r) {
            // Full view: làm mờ các môn đã qua (< hôm nay)
            $ispast = ((int)$r->examdate < $todaystart);

            $cells = [
                (string)$i++,
                s($r->coursecode),
                s(isset($r->coursename) ? $r->coursename : ''),
                userdate((int)$r->examdate, '%d/%m/%Y'),
                s($r->examslot),
            ];

            if ($this->limit === null && $ispast) {
                foreach ($cells as $k => $v) {
                    $cells[$k] = html_writer::span($v, 'text-muted', ['style' => 'opacity:0.55;']);
                }
            }

            $t->data[] = $cells;
        }

        if ($this->showdownload) {
            $downloadurl = new moodle_url('/blocks/th_exam_schedule/download.php', ['format' => 'doc']);
            $downloadbtn = html_writer::link(
                $downloadurl,
                get_string('download', 'block_th_exam_schedule'),
                ['class' => 'btn btn-secondary']
            );
            $out .= html_writer::div($downloadbtn, 'mb-3');
        }

        $out .= html_writer::table($t);
        return $out;
    }
}
