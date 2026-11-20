<?php

namespace local_th_qaa_manager\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use PARAM_RAW;
use PARAM_INT;
use PARAM_TEXT;

class load_courses extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'term' => new external_value(PARAM_RAW, 'Search query term', VALUE_DEFAULT, ''),
        ]);
    }

    public static function execute($term = '') {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'term' => $term,
        ]);

        $term = trim($params['term']);

        $is_iomad = class_exists('iomad');
        $companyid = 0;
        if ($is_iomad) {
            $companyid = (int)\iomad::get_my_companyid(\context_system::instance(), false);
            if ($companyid <= 0) {
                $companyid = (int)$DB->get_field('company_users', 'companyid', ['userid' => $USER->id], IGNORE_MISSING);
            }
        }

        $searchsql = '';
        $sqlparams = [];
        if ($term !== '') {
            $like1 = $DB->sql_like('c.fullname', ':term1', false);
            $like2 = $DB->sql_like('c.shortname', ':term2', false);
            $searchsql = " AND ($like1 OR $like2)";
            $sqlparams['term1'] = '%' . $term . '%';
            $sqlparams['term2'] = '%' . $term . '%';
        }

        if ($is_iomad) {
            $companysql = " 1 = 1";
            if (!empty($companyid)) {
                if ($companyid == -1) {
                    $companysql = " c.id NOT IN (SELECT courseid FROM {company_course}) ";
                } else {
                    $companysql = " (c.id IN (
                                      SELECT courseid FROM {company_course}
                                      WHERE companyid = :companyid)
                                     OR ic.shared = 1) ";
                    $sqlparams['companyid'] = $companyid;
                }
            }

            $sql = "SELECT c.id, c.shortname, c.fullname
                      FROM {iomad_courses} ic
                      JOIN {course} c ON (ic.courseid = c.id)
                     WHERE c.id != 1 AND {$companysql} {$searchsql}
                  ORDER BY c.fullname ASC";
        } else {
            // Fallback for standard Moodle without IOMAD.
            $sql = "SELECT c.id, c.shortname, c.fullname
                      FROM {course} c
                     WHERE c.id != 1 {$searchsql}
                  ORDER BY c.fullname ASC";
        }

        $courses = $DB->get_records_sql($sql, $sqlparams, 0, 100);

        $results = [];
        foreach ($courses as $course) {
            $results[] = [
                'value' => (int)$course->id,
                'label' => $course->shortname . ', ' . $course->fullname,
            ];
        }

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'value' => new external_value(PARAM_INT),
                'label' => new external_value(PARAM_TEXT),
            ])
        );
    }
}
