<?php
namespace mod_resource\report;

use html_writer;
use moodle_url;

class resource_view extends \table_sql {

    public $cmid   = 0;
    public $userid = false;

    public function __construct($uniqueid, $cmid, $userid, $resource) {
        global $DB;
        parent::__construct($uniqueid);
        $this->collapsible(false);

        $this->cmid   = $cmid;
        $this->userid = $userid;

        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        $download = optional_param('download', null, PARAM_ALPHA);
        if ($download) {
            raise_memory_limit(MEMORY_EXTRA);
            if ($this->userid) {
                $user     = $DB->get_record('user', ['id' => $this->userid]);
                $filename = get_string('report_title_user', 'mod_resource', fullname($user));
            } else {
                $filename = get_string('report_title', 'mod_resource', $resource->name);
            }
            $this->is_downloading($download, $filename, $resource->name);
        }

        if ($this->userid) {
            $columns = [
                'user_id',
                'fullname',
                'email',
                'currenttime',
                'duration',
                'percent',
                'mapa',
                'timecreated',
                'timemodified',
            ];
            $headers = [
                get_string('report_userid', 'mod_resource'),
                get_string('report_nome', 'mod_resource'),
                get_string('report_email', 'mod_resource'),
                get_string('report_tempo', 'mod_resource'),
                get_string('report_duracao', 'mod_resource'),
                get_string('report_porcentagem', 'mod_resource'),
                get_string('report_mapa', 'mod_resource'),
                get_string('report_comecou', 'mod_resource'),
                get_string('report_terminou', 'mod_resource'),
            ];

            if ($this->is_downloading()) {
                unset($columns[6]);
                unset($headers[6]);
            }
        } else {
            $columns = [
                'user_id',
                'fullname',
                'email',
                'currenttime',
                'duration',
                'percent',
                'quantidade',
                'timecreated',
            ];
            $headers = [
                get_string('report_userid', 'mod_resource'),
                get_string('report_nome', 'mod_resource'),
                get_string('report_email', 'mod_resource'),
                get_string('report_tempo', 'mod_resource'),
                get_string('report_duracao', 'mod_resource'),
                get_string('report_porcentagem', 'mod_resource'),
                get_string('report_visualizacoes', 'mod_resource'),
                get_string('report_assistiu', 'mod_resource'),
            ];

            if (!$this->is_downloading()) {
                $columns[] = 'extra';
                $headers[] = '';
            }
        }

        $this->define_columns($columns);
        $this->define_headers($headers);
    }

    public function col_fullname($row) {
        global $COURSE;
        $name = fullname($row);
        if ($this->download) {
            return $name;
        }
        if ($COURSE->id == SITEID) {
            $url = new moodle_url('/user/profile.php', ['id' => $row->user_id]);
        } else {
            $url = new moodle_url('/user/view.php', ['id' => $row->user_id, 'course' => $COURSE->id]);
        }
        return html_writer::link($url, $name);
    }

    public function col_currenttime($row) {
        return $this->format_seconds((int)$row->currenttime);
    }

    public function col_duration($row) {
        return $this->format_seconds((int)$row->duration);
    }

    public function col_percent($row) {
        return "{$row->percent}%";
    }

    public function col_mapa($row) {
        if (empty($row->mapa)) {
            return '';
        }
        $mapas = json_decode($row->mapa);
        if (!is_array($mapas)) {
            return '';
        }
        $html = "<div id='mapa-visualizacao' class='report'>";
        foreach ($mapas as $id => $val) {
            if ($id == 0) {
                continue;
            }
            if ($val) {
                $html .= "<div id='mapa-visualizacao-{$id}' style='opacity:1'></div>";
            } else {
                $html .= "<div id='mapa-visualizacao-{$id}'></div>";
            }
        }
        $html .= "</div>";
        return $html;
    }

    public function col_timecreated($row) {
        return userdate($row->timecreated);
    }

    public function col_timemodified($row) {
        return userdate($row->timemodified);
    }

    public function col_quantidade($row) {
        return $row->quantidade;
    }

    public function col_extra($row) {
        $url = new moodle_url('/mod/resource/report.php', ['id' => $row->cm_id, 'u' => $row->user_id]);
        return html_writer::link($url, get_string('report_all', 'mod_resource'));
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $params   = ['cm_id' => $this->cmid];
        $sqlwhere = $this->get_sql_where();
        $where    = $sqlwhere[0] ? "AND {$sqlwhere[0]}" : '';
        $params   = array_merge($params, $sqlwhere[1]);

        $order = $this->get_sort_for_table($this->uniqueid);
        if (!$order) {
            $order = 'sv.user_id';
        }

        if ($this->userid) {
            // Chi tiết: tất cả các lần xem của 1 user
            $params['user_id'] = $this->userid;

            $this->sql = "SELECT sv.id, sv.user_id, sv.cm_id,
                                 sv.currenttime, sv.duration, sv.percent,
                                 sv.mapa, sv.timecreated, sv.timemodified,
                                 u.firstname, u.lastname, u.email,
                                 u.firstnamephonetic, u.lastnamephonetic,
                                 u.middlename, u.alternatename
                            FROM {resource_video_view} sv
                            JOIN {user} u ON u.id = sv.user_id
                           WHERE sv.cm_id   = :cm_id
                             AND sv.user_id = :user_id
                             AND sv.percent > 0
                             {$where}
                        ORDER BY {$order}";

            if ($pagesize != -1) {
                $total = $DB->count_records_sql(
                    "SELECT COUNT(*) FROM {resource_video_view} sv
                       JOIN {user} u ON u.id = sv.user_id
                      WHERE sv.cm_id = :cm_id
                        AND sv.user_id = :user_id
                        AND sv.percent > 0
                        {$where}",
                    $params
                );
                $this->pagesize($pagesize, $total);
            } else {
                $this->pageable(false);
            }
        } else {
            // Tổng quát: mỗi user 1 dòng, lấy max + đếm số lần xem
            $this->sql = "SELECT sv.user_id, sv.cm_id,
                                 MAX(sv.currenttime) AS currenttime,
                                 MAX(sv.duration) AS duration,
                                 MAX(sv.percent) AS percent,
                                 MAX(sv.timecreated) AS timecreated,
                                 (
                                     SELECT COUNT(*)
                                       FROM {resource_video_view} sv1
                                      WHERE sv1.cm_id    = sv.cm_id
                                        AND sv1.user_id  = sv.user_id
                                        AND sv1.percent  > 0
                                 ) AS quantidade,
                                 u.firstname, u.lastname, u.email,
                                 u.firstnamephonetic, u.lastnamephonetic,
                                 u.middlename, u.alternatename
                            FROM {resource_video_view} sv
                            JOIN {user} u ON u.id = sv.user_id
                           WHERE sv.cm_id = :cm_id
                             {$where}
                        GROUP BY sv.user_id, sv.cm_id,
                                 u.firstname, u.lastname, u.email,
                                 u.firstnamephonetic, u.lastnamephonetic,
                                 u.middlename, u.alternatename
                        ORDER BY {$order}";

            if ($pagesize != -1) {
                $countsql = "SELECT COUNT(*)
                               FROM (
                                    SELECT COUNT(sv.id) AS cont
                                      FROM {resource_video_view} sv
                                      JOIN {user} u ON u.id = sv.user_id
                                     WHERE sv.cm_id = :cm_id {$where}
                                  GROUP BY sv.user_id
                               ) AS c";
                $total = $DB->count_records_sql($countsql, $params);
                $this->pagesize($pagesize, $total);
            } else {
                $this->pageable(false);
            }
        }

        if ($useinitialsbar && !$this->is_downloading()) {
            $this->initialbars(true);
        }

        $this->rawdata = $DB->get_recordset_sql(
            $this->sql,
            $params,
            $this->get_page_start(),
            $this->get_page_size()
        );
    }

    private function format_seconds($seconds) {
        $s = $seconds % 60;
        $m = (floor($seconds / 60)) % 60;
        $h = floor($seconds / 3600);
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}