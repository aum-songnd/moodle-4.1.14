<?php
namespace mod_qaa\local\repository;

defined('MOODLE_INTERNAL') || die();

class qaapairs_repository {
    /** @var \moodle_database */
    private $db;
    public function __construct(\moodle_database $db) { $this->db = $db; }

    public function get_all_for_manager(int $qaaid): array {
        $sql = "SELECT m.*,
                      CASE WHEN m.parentid IS NULL THEN m.timecreatedquestion ELSE m.timecreatedanswer END AS dispcreated,
                      CASE WHEN m.parentid IS NULL THEN m.timemodifiedquestion ELSE m.timemodifiedanswer END AS dispmodified
                  FROM {qaapairs} m
                WHERE m.qaaid = ?
              ORDER BY dispcreated ASC";
        return $this->db->get_records_sql($sql, [$qaaid]);
    }
    
    public function get_all_for_student(int $qaaid, int $userid): array {
        $sql = "SELECT m.*,
                      CASE WHEN m.parentid IS NULL THEN m.timecreatedquestion ELSE m.timecreatedanswer END AS dispcreated,
                      CASE WHEN m.parentid IS NULL THEN m.timemodifiedquestion ELSE m.timemodifiedanswer END AS dispmodified
                  FROM {qaapairs} m
                WHERE m.qaaid = ?
                  AND (m.status = 1 OR m.studentid = ? OR m.teacherid = ?)
              ORDER BY dispcreated ASC";
        return $this->db->get_records_sql($sql, [$qaaid, $userid, $userid]);
    }
  

    public function root_has_reply(int $rootid): bool {
        return $this->db->record_exists_select('qaapairs', 'parentid = ?', [$rootid]);
    }  

    // public function get_threads_summary(int $qaaid, int $userid, bool $ismanager): array {
    //     global $DB;
    
    //     $wherevisible = $ismanager
    //         ? '1=1'
    //         : '(r.status = 1 OR r.studentid = :u1 OR r.teacherid = :u2)';
    
    //     $sql = "
    //         WITH RECURSIVE all_replies AS (
    //             -- Lấy các câu hỏi gốc (root)
    //             SELECT
    //                 id,
    //                 parentid,
    //                 id AS rootid,
    //                 COALESCE(teacherid, studentid) AS authorid,
    //                 timecreatedanswer,
    //                 status
    //             FROM {qaapairs}
    //             WHERE parentid IS NULL
    
    //             UNION ALL
    
    //             -- Lấy các câu trả lời con (mọi cấp)
    //             SELECT
    //                 c.id,
    //                 c.parentid,
    //                 p.rootid,
    //                 COALESCE(c.teacherid, c.studentid) AS authorid,
    //                 c.timecreatedanswer,
    //                 c.status
    //             FROM {qaapairs} c
    //             INNER JOIN all_replies p ON c.parentid = p.id
    //         ),
    
    //         -- Chỉ giữ các câu trả lời đã phê duyệt (status = 1) để tìm người trả lời cuối cùng
    //         latest_approved AS (
    //             SELECT
    //                 rootid,
    //                 MAX(timecreatedanswer) AS maxtime,
    //                 SUBSTRING_INDEX(
    //                     GROUP_CONCAT(authorid ORDER BY timecreatedanswer DESC SEPARATOR ','),
    //                     ',', 1
    //                 ) AS latestauthorid
    //             FROM all_replies
    //             WHERE parentid IS NOT NULL
    //               AND status = 1
    //             GROUP BY rootid
    //         )
    
    //         SELECT
    //             r.id AS rootid,
    //             r.message AS rootmessage,
    //             r.studentid AS rootstudentid,
    //             r.teacherid AS rootteacherid,
    //             r.status,
    //             COALESCE(ru.id, tu.id) AS rootauthorid,
    
    //             -- thời điểm mới nhất (nếu không có reply thì lấy thời gian tạo root)
    //             COALESCE(lat.maxtime, r.timecreatedquestion) AS latesttime,
    
    //             -- người trả lời cuối cùng đã được phê duyệt
    //             lat.latestauthorid AS latestauthorid,
    
    //             -- thêm thời điểm tạo root
    //             r.timecreatedquestion AS rootcreated
    
    //         FROM {qaapairs} r
    //         LEFT JOIN {user} ru ON ru.id = r.studentid
    //         LEFT JOIN {user} tu ON tu.id = r.teacherid
    //         LEFT JOIN latest_approved lat ON lat.rootid = r.id
    
    //         WHERE r.qaaid = :qaaid
    //           AND r.parentid IS NULL
    //           AND {$wherevisible}
    
    //         ORDER BY r.id DESC
    //     ";
    
    //     $params = ['qaaid' => $qaaid];
    //     if (!$ismanager) {
    //         $params += ['u1' => $userid, 'u2' => $userid];
    //     }
    
    //     return $DB->get_records_sql($sql, $params);
    // }

    public function get_threads_summary(int $qaaid, int $userid, bool $ismanager, int $page, int $perpage): array {
        global $DB;
    
        $wherevisible = $ismanager
            ? '1=1'
            : '(r.status = 1 OR r.studentid = :u1 OR r.teacherid = :u2)';
    
        $sql = "
            WITH RECURSIVE all_replies AS (
                SELECT id, parentid, id AS rootid,
                       COALESCE(teacherid, studentid) AS authorid,
                       timecreatedanswer, status
                FROM {qaapairs}
                WHERE parentid IS NULL
                UNION ALL
                SELECT c.id, c.parentid, p.rootid,
                       COALESCE(c.teacherid, c.studentid) AS authorid,
                       c.timecreatedanswer, c.status
                FROM {qaapairs} c
                INNER JOIN all_replies p ON c.parentid = p.id
            ),
            latest_approved AS (
                SELECT
                    rootid,
                    MAX(timecreatedanswer) AS maxtime,
                    SUBSTRING_INDEX(GROUP_CONCAT(authorid ORDER BY timecreatedanswer DESC SEPARATOR ','), ',', 1) AS latestauthorid
                FROM all_replies
                WHERE parentid IS NOT NULL AND status = 1
                GROUP BY rootid
            )
            SELECT
                r.id AS rootid,
                r.message AS rootmessage,
                r.studentid AS rootstudentid,
                r.teacherid AS rootteacherid,
                r.status,
                COALESCE(ru.id, tu.id) AS rootauthorid,
                COALESCE(lat.maxtime, r.timecreatedquestion) AS latesttime,
                lat.latestauthorid AS latestauthorid,
                r.timecreatedquestion AS rootcreated
            FROM {qaapairs} r
            LEFT JOIN {user} ru ON ru.id = r.studentid
            LEFT JOIN {user} tu ON tu.id = r.teacherid
            LEFT JOIN latest_approved lat ON lat.rootid = r.id
            WHERE r.qaaid = :qaaid
              AND r.parentid IS NULL
              AND {$wherevisible}
            ORDER BY COALESCE(lat.maxtime, r.timecreatedquestion) DESC, r.id DESC
        ";
    
        $params = ['qaaid' => $qaaid];
        if (!$ismanager) {
            $params += ['u1' => $userid, 'u2' => $userid];
        }
    
        $limitfrom = $page * $perpage;
        $limitnum  = $perpage;
    
        return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    public function count_threads(int $qaaid, int $userid, bool $ismanager): int {
        global $DB;
    
        $wherevisible = $ismanager
            ? '1=1'
            : '(r.status = 1 OR r.studentid = :u1 OR r.teacherid = :u2)';
    
        $sql = "SELECT COUNT(1)
                FROM {qaapairs} r
                WHERE r.qaaid = :qaaid
                  AND r.parentid IS NULL
                  AND {$wherevisible}";
        $params = ['qaaid' => $qaaid];
        if (!$ismanager) {
            $params += ['u1' => $userid, 'u2' => $userid];
        }
        return (int)$DB->count_records_sql($sql, $params);
    }
  
}
