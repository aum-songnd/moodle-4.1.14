<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External API for local_thcourse.
 *
 * @package    local_thcourse
 * @copyright  2024 TH Course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core libs — luon can thiet.
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/course/lib.php");

// QUAN TRONG:
// backup_includes.php da load /backup/util/helper/copy_helper.class.php (dinh nghia class copy_helper).
// KHONG bao gio require /backup/copy_helper.php them — hai path khac nhau nhung cung class
// se gay "PHP Fatal: Cannot redeclare class copy_helper" -> 500.
// Lazy-load backup libs ben trong tung function, chi khi thuc su can.

class local_thcourse_external extends external_api {

    // =========================================================================
    // create_or_duplicate_course
    // =========================================================================

    public static function create_or_duplicate_course_parameters() {
        return new external_function_parameters([
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'mode'             => new external_value(PARAM_ALPHA,      'template | new | clone', VALUE_DEFAULT, 'clone'),
                    'sourceshortname'  => new external_value(PARAM_TEXT,       'Shortname khoa nguon', VALUE_DEFAULT, null),
                    'fullname'         => new external_value(PARAM_TEXT,       'Ten day du khoa moi'),
                    'shortname'        => new external_value(PARAM_TEXT,       'Shortname khoa moi'),
                    'idnumber'         => new external_value(PARAM_RAW,        'ID number khoa moi', VALUE_OPTIONAL),
                    'categoryidnumber' => new external_value(PARAM_TEXT,       'ID number danh muc'),
                    'visible'          => new external_value(PARAM_INT,        'Visible (0/1)', VALUE_DEFAULT, 0),
                    'startdate'        => new external_value(PARAM_INT,        'Startdate UTC timestamp', VALUE_OPTIONAL),
                    'options'          => new external_multiple_structure(
                        new external_single_structure([
                            'name'  => new external_value(PARAM_ALPHAEXT, 'Ten option'),
                            'value' => new external_value(PARAM_RAW,      'Gia tri option'),
                        ]),
                        'Duplication options (users=0|1, role_X=roleid, ...)', VALUE_DEFAULT, []
                    ),
                    'courseformatoptions' => new external_multiple_structure(
                        new external_single_structure([
                            'name'  => new external_value(PARAM_ALPHANUMEXT, 'Ten format option'),
                            'value' => new external_value(PARAM_RAW,         'Gia tri format option'),
                        ]),
                        'Course format options (chi dung mode=new)', VALUE_DEFAULT, []
                    ),
                    'customfields' => new external_multiple_structure(
                        new external_single_structure([
                            'shortname' => new external_value(PARAM_ALPHANUMEXT, 'Shortname custom field'),
                            'value'     => new external_value(PARAM_RAW,         'Gia tri custom field'),
                        ]),
                        'Custom fields', VALUE_DEFAULT, []
                    ),
                ])
            ),
        ]);
    }

    public static function create_or_duplicate_course($courses) {
        global $DB, $CFG, $USER;

        require_once($CFG->dirroot . '/course/externallib.php');

        $params = self::validate_parameters(
            self::create_or_duplicate_course_parameters(),
            ['courses' => $courses]
        );

        // validate_context chi goi MOT LAN trong external API.
        // Dung system context; cap phep cu the kiem tra bang require_capability
        // / require_all_capabilities ben trong vong lap.
        self::validate_context(\context_system::instance());

        $results = [];

        foreach ($params['courses'] as $course) {
            try {

                // -----------------------------------------------------------------
                // 1. Validate cac truong bat buoc
                // -----------------------------------------------------------------
                if (empty($course['fullname'])) {
                    self::set_last_copy_error($course['shortname'] ?? '', 'Thieu truong fullname.', 'missing_fullname');
                    $results[] = self::fail_result($course['shortname'] ?? '', 'Thieu truong fullname.');
                    continue;
                }

                if (empty($course['shortname'])) {
                    $results[] = self::fail_result('', 'Thieu truong shortname.');
                    continue;
                }

                if (empty($course['categoryidnumber'])) {
                    self::set_last_copy_error($course['shortname'], 'Thieu categoryidnumber.', 'missing_categoryidnumber');
                    $results[] = self::fail_result($course['shortname'], 'Thieu categoryidnumber.');
                    continue;
                }

                $mode = trim(strtolower($course['mode'] ?? 'clone'));
                if (!in_array($mode, ['template', 'new', 'clone'], true)) {
                    self::set_last_copy_error($course['shortname'], "Gia tri mode khong hop le: {$course['mode']}.", 'invalid_mode');
                    $results[] = self::fail_result($course['shortname'], "Gia tri mode khong hop le: {$course['mode']}.");
                    continue;
                }

                $startdate = isset($course['startdate']) ? (int)$course['startdate'] : 0;

                // -----------------------------------------------------------------
                // 2. Resolve category
                // -----------------------------------------------------------------
                $categoryid = $DB->get_field(
                    'course_categories', 'id',
                    ['idnumber' => $course['categoryidnumber']],
                    IGNORE_MISSING
                );

                if (!$categoryid) {
                    self::set_last_copy_error(
                        $course['shortname'],
                        "Khong tim thay category idnumber='{$course['categoryidnumber']}'.",
                        'category_not_found'
                    );
                    $results[] = self::fail_result(
                        $course['shortname'],
                        "Khong tim thay category voi idnumber: {$course['categoryidnumber']}"
                    );
                    continue;
                }

                $catctx = \context_coursecat::instance($categoryid);
                require_capability('moodle/course:create', $catctx);

                // -----------------------------------------------------------------
                // 3. Kiem tra shortname chua ton tai
                // -----------------------------------------------------------------
                if ($DB->record_exists('course', ['shortname' => $course['shortname']])) {
                    self::set_last_copy_error(
                        $course['shortname'],
                        "Shortname '{$course['shortname']}' da ton tai.",
                        'shortname_exists'
                    );
                    $results[] = self::fail_result(
                        $course['shortname'],
                        "Khoa hoc voi shortname '{$course['shortname']}' da ton tai."
                    );
                    continue;
                }

                // =================================================================
                // MODE: new — tao khoa hoc rong, khong can backup libs
                // =================================================================
                if ($mode === 'new') {
                    if (empty($startdate) || $startdate <= 0) {
                        self::set_last_copy_error($course['shortname'], 'Thieu startdate cho mode=new.', 'missing_startdate');
                        $results[] = self::fail_result($course['shortname'], "Thieu 'startdate' (UNIX timestamp, UTC) cho mode=new.");
                        continue;
                    }

                    $newcourse = [
                        'fullname'            => $course['fullname'],
                        'shortname'           => $course['shortname'],
                        'categoryid'          => $categoryid,
                        'visible'             => (int)$course['visible'],
                        'startdate'           => $startdate,
                        'idnumber'            => !empty($course['idnumber']) ? $course['idnumber'] : $course['shortname'],
                        'courseformatoptions' => !empty($course['courseformatoptions'])
                            ? $course['courseformatoptions']
                            : [['name' => 'numsections', 'value' => get_config('moodlecourse', 'numsections') ?: 4]],
                    ];

                    if (!empty($course['customfields'])) {
                        $newcourse['customfields'] = $course['customfields'];
                    }

                    $created = \core_course_external::create_courses([$newcourse]);
                    self::clear_last_copy_error($course['shortname']);
                    $results[] = [
                        'status'    => 'Done',
                        'id'        => (int)$created[0]['id'],
                        'shortname' => (string)$created[0]['shortname'],
                        'message'   => 'Empty course created (mode=new).',
                    ];
                    continue;
                }

                // =================================================================
                // MODE: template | clone — can backup libs, lazy-load tai day.
                //
                // backup_includes.php se load copy_helper.class.php (dinh nghia class copy_helper).
                // KHONG require /backup/copy_helper.php them — se gay "Cannot redeclare class".
                // =================================================================
                require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
                require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

                // -----------------------------------------------------------------
                // 4. Xac dinh khoa nguon
                // -----------------------------------------------------------------
                $sourcecourse = null;

                if ($mode === 'template') {
                    $srcshort = trim($course['sourceshortname'] ?? '');
                    if ($srcshort === '') {
                        self::set_last_copy_error($course['shortname'], "mode=template yeu cau 'sourceshortname'.", 'missing_sourceshortname');
                        $results[] = self::fail_result($course['shortname'], "mode=template yeu cau 'sourceshortname'.");
                        continue;
                    }

                    $sourcecourse = $DB->get_record('course', ['shortname' => $srcshort], '*', IGNORE_MISSING);
                    if (!$sourcecourse) {
                        self::set_last_copy_error($course['shortname'], "Khong tim thay khoa nguon '{$srcshort}'.", 'source_not_found');
                        $results[] = self::fail_result($course['shortname'], "Khong tim thay khoa nguon voi sourceshortname='{$srcshort}'.");
                        continue;
                    }

                    if (empty($startdate) || $startdate <= 0) {
                        self::set_last_copy_error($course['shortname'], 'Thieu startdate cho mode=template.', 'missing_startdate');
                        $results[] = self::fail_result($course['shortname'], "Thieu 'startdate' (UNIX timestamp, UTC) cho mode=template.");
                        continue;
                    }

                } else {
                    // mode=clone: sourceshortname tuy chon; neu bo trong thi tim qua custom field.
                    $srcshort = trim($course['sourceshortname'] ?? '');

                    if ($srcshort !== '') {
                        $sourcecourse = $DB->get_record('course', ['shortname' => $srcshort], '*', IGNORE_MISSING);
                        if (!$sourcecourse) {
                            self::set_last_copy_error($course['shortname'], "Khong tim thay khoa nguon '{$srcshort}'.", 'source_not_found');
                            $results[] = self::fail_result($course['shortname'], "Khong tim thay khoa nguon voi sourceshortname='{$srcshort}'.");
                            continue;
                        }
                    } else {
                        // Tim khoa nguon moi nhat co cung course code qua custom field.
                        $coursecodefield = self::get_coursecode_field_shortname();
                        if (empty($coursecodefield)) {
                            self::set_last_copy_error($course['shortname'], 'Chua cau hinh coursecode_field_shortname.', 'missing_coursecode_setting');
                            $results[] = self::fail_result($course['shortname'], 'Chua cau hinh truong course code (local_thcourse/coursecode_field_shortname).');
                            continue;
                        }

                        $coursecode = self::extract_customfield_value($course['customfields'] ?? [], $coursecodefield);
                        if ($coursecode === null || $coursecode === '') {
                            self::set_last_copy_error($course['shortname'], "Thieu custom field '{$coursecodefield}'.", 'missing_coursecode_value');
                            $results[] = self::fail_result($course['shortname'], "Thieu custom field '{$coursecodefield}' de xac dinh khoa nguon (mode=clone).");
                            continue;
                        }

                        $sql = "
                            SELECT c.*
                              FROM {course} c
                              JOIN {customfield_data} d       ON d.instanceid = c.id
                              JOIN {customfield_field} f      ON f.id = d.fieldid
                              JOIN {customfield_category} cat ON cat.id = f.categoryid
                             WHERE cat.component  = :component
                               AND cat.area       = :area
                               AND f.shortname    = :fieldshort
                               AND c.id          <> 1
                               AND c.shortname   <> :newshortname
                               AND (d.shortcharvalue = :code1 OR d.charvalue = :code2 OR d.value = :code3)
                          ORDER BY
                               CASE WHEN c.startdate IS NULL OR c.startdate = 0
                                    THEN c.timecreated ELSE c.startdate END DESC,
                               c.id DESC
                             LIMIT 1";

                        $sourcecourse = $DB->get_record_sql($sql, [
                            'component'    => 'core_course',
                            'area'         => 'course',
                            'fieldshort'   => $coursecodefield,
                            'newshortname' => $course['shortname'],
                            'code1'        => $coursecode,
                            'code2'        => $coursecode,
                            'code3'        => $coursecode,
                        ], IGNORE_MISSING);

                        if (!$sourcecourse) {
                            self::set_last_copy_error(
                                $course['shortname'],
                                "Khong tim thay khoa nguon co {$coursecodefield}='{$coursecode}'.",
                                'source_not_found'
                            );
                            $results[] = self::fail_result(
                                $course['shortname'],
                                "Khong tim thay khoa nguon co {$coursecodefield}='{$coursecode}'."
                            );
                            continue;
                        }
                    }

                    if (empty($startdate) || $startdate <= 0) {
                        self::set_last_copy_error($course['shortname'], 'Thieu startdate cho mode=clone.', 'missing_startdate');
                        $results[] = self::fail_result($course['shortname'], "Thieu 'startdate' (UNIX timestamp, UTC) cho mode=clone.");
                        continue;
                    }
                }

                // -----------------------------------------------------------------
                // 5. Kiem tra quyen sao chep tren khoa nguon (giong copy.php core)
                // -----------------------------------------------------------------
                $sourcectx = \context_course::instance($sourcecourse->id);
                $copycaps  = \core_course\management\helper::get_course_copy_capabilities();

                require_all_capabilities($copycaps, $sourcectx);

                // -----------------------------------------------------------------
                // 6. Tinh enddate theo offset (giong copy.php core)
                // -----------------------------------------------------------------
                $sourcestart = (int)$sourcecourse->startdate;
                $sourceend   = (int)($sourcecourse->enddate ?? 0);
                $enddate     = ($sourcestart > 0 && $startdate > 0 && $sourceend > 0)
                    ? $sourceend + ($startdate - $sourcestart)
                    : 0;

                // -----------------------------------------------------------------
                // 7. Giai ma options (users, roles)
                // -----------------------------------------------------------------
                $userdata  = 0;
                $keptroles = [];
                foreach ($course['options'] ?? [] as $opt) {
                    if (($opt['name'] ?? '') === 'users') {
                        $userdata = (int)($opt['value'] ?? 0);
                    }
                    if (substr($opt['name'] ?? '', 0, 5) === 'role_' && (int)($opt['value'] ?? 0) !== 0) {
                        $keptroles[] = (int)$opt['value'];
                    }
                }

                // -----------------------------------------------------------------
                // 8. Build formdata giong het copy.php core
                // -----------------------------------------------------------------
                $formdata = (object)[
                    'courseid'  => (int)$sourcecourse->id,
                    'fullname'  => $course['fullname'],
                    'shortname' => $course['shortname'],
                    'category'  => $categoryid,
                    'visible'   => (int)$course['visible'],
                    'startdate' => $startdate,
                    'enddate'   => $enddate,
                    'idnumber'  => !empty($course['idnumber']) ? $course['idnumber'] : $course['shortname'],
                    'userdata'  => $userdata,
                ];
                foreach ($keptroles as $roleid) {
                    $formdata->{'role_' . $roleid} = $roleid;
                }

                // -----------------------------------------------------------------
                // 9. Kiem tra task sao chep cho destination shortname nay
                //    da co trong hang doi chua (tranh tao trung lap).
                //    get_copies() tra ve cac ban ghi dang backup/restore.
                // -----------------------------------------------------------------
                $inProgressBefore = \copy_helper::get_copies($USER->id);
                foreach ($inProgressBefore as $existingCopy) {
                    if (!empty($existingCopy->destination) && $existingCopy->destination === $course['shortname']) {
                        $label = ($existingCopy->operation === \backup::OPERATION_BACKUP) ? 'backup' : 'restore';
                        $results[] = [
                            'status'    => 'Done',
                            'id'        => 0,
                            'shortname' => $course['shortname'],
                            'message'   => "Task sao chep ({$label}) cho shortname '{$course['shortname']}' da ton tai trong hang doi — bo qua de tranh trung lap.",
                        ];
                        continue 2; // bo qua, chuyen sang course tiep theo trong vong lap ngoai
                    }
                }

                // -----------------------------------------------------------------
                // 10. Chuan bi moi truong truoc khi chay backup controller:
                //     - write_close : tranh session conflict voi RELEASESESSION_YES
                //     - MEMORY_EXTRA: backup/restore can nhieu RAM
                //     - time_limit 0: tranh timeout
                //     - ob_start    : nuot moi output rong backup/restore co the echo
                // -----------------------------------------------------------------
                \core\session\manager::write_close();
                raise_memory_limit(MEMORY_EXTRA);
                \core_php_time_limit::raise(0);

                ob_start();
                try {
                    $copydata = \copy_helper::process_formdata($formdata);
                    \copy_helper::create_copy($copydata);
                    ob_end_clean();
                } catch (\Throwable $inner) {
                    ob_end_clean();
                    throw $inner;
                }

                self::clear_last_copy_error($course['shortname']);
                $results[] = [
                    'status'    => 'Done',
                    'id'        => 0,
                    'shortname' => $course['shortname'],
                    'message'   => "Copy task queued (mode={$mode}). Poll get_multi_copy_status to track completion.",
                ];

            } catch (\required_capability_exception $e) {
                self::set_last_copy_error($course['shortname'] ?? '', 'Khong du quyen tao/sao chep khoa hoc.', 'no_capability');
                $results[] = self::fail_result($course['shortname'] ?? '', 'Khong du quyen tao/sao chep khoa hoc.');
            } catch (\Throwable $e) {
                $msg = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
                self::set_last_copy_error($course['shortname'] ?? '', $msg, 'exception');
                $results[] = self::fail_result($course['shortname'] ?? '', $msg);
            }
        }

        return $results;
    }

    public static function create_or_duplicate_course_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'status'    => new external_value(PARAM_TEXT, 'Done or Failed'),
                'id'        => new external_value(PARAM_INT,  'Course ID (0 neu async hoac loi)'),
                'shortname' => new external_value(PARAM_TEXT, 'Shortname khoa hoc'),
                'message'   => new external_value(PARAM_TEXT, 'Ket qua chi tiet'),
            ])
        );
    }

    // =========================================================================
    // get_multi_copy_status
    //
    // Su dung copy_helper::get_copies() — cung API ma copyprogress.php dung —
    // thay vi tu viet SQL. Match tac vu dang chay theo truong $copy->destination
    // (= shortname khoa dich).
    // =========================================================================

    public static function get_multi_copy_status_parameters() {
        return new external_function_parameters([
            'shortnames' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Shortname khoa hoc dich can kiem tra')
            ),
        ]);
    }

    public static function get_multi_copy_status($shortnames) {
        global $DB, $CFG, $USER;

        $params = self::validate_parameters(
            self::get_multi_copy_status_parameters(),
            ['shortnames' => $shortnames]
        );
        self::validate_context(\context_system::instance());

        // backup_includes load copy_helper.class.php can thiet cho get_copies().
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Lay tat ca ban sao dang xu ly cua user hien tai.
        // Day chinh la ham ma copyprogress.php su dung.
        // Tra ve cac copy records voi:
        //   ->destination : shortname khoa dich
        //   ->source      : shortname khoa nguon
        //   ->operation   : 'backup' hoac 'restore'
        $inProgressCopies = \copy_helper::get_copies($USER->id);

        // Index theo destination shortname de lookup O(1).
        $copyByDest = [];
        foreach ($inProgressCopies as $copy) {
            if (!empty($copy->destination)) {
                $copyByDest[$copy->destination] = $copy;
            }
        }

        $results = [];

        foreach ($params['shortnames'] as $shortname) {

            // 1. Khoa dich da ton tai -> completed.
            if ($DB->record_exists('course', ['shortname' => $shortname])) {
                self::clear_last_copy_error($shortname);
                $results[] = [
                    'shortname' => $shortname,
                    'status'    => 'completed',
                    'message'   => 'Target course already exists.',
                ];
                continue;
            }

            // 2. asynchronous_copy_task dang chay -> in_progress.
            if (isset($copyByDest[$shortname])) {
                $copy  = $copyByDest[$shortname];
                $label = $copy->operation === \backup::OPERATION_BACKUP ? 'Backing up' : 'Restoring';
                $results[] = [
                    'shortname' => $shortname,
                    'status'    => 'in_progress',
                    'message'   => "{$label} in progress (source: {$copy->source}).",
                ];
                continue;
            }

            // 3. Khong co task va khoa chua ton tai -> error.
            $last = self::get_last_copy_error($shortname);
            $results[] = [
                'shortname' => $shortname,
                'status'    => 'error',
                'message'   => $last
                    ? (string)($last['message'] ?? 'Unknown error')
                    : 'Khong tim thay khoa hoc dich va khong co tac vu sao chep tuong ung.',
            ];
        }

        return $results;
    }

    public static function get_multi_copy_status_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'shortname' => new external_value(PARAM_TEXT, 'Shortname khoa hoc dich'),
                'status'    => new external_value(PARAM_TEXT, 'in_progress | completed | error'),
                'message'   => new external_value(PARAM_TEXT, 'Chi tiet trang thai'),
            ])
        );
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Ghi loi copy vao mdl_config de co the truy van sau (get_multi_copy_status).
     */
    protected static function set_last_copy_error(string $shortname, string $message, string $code = 'validation'): void {
        if ($shortname === '') { return; }
        set_config('copy_error_' . sha1($shortname), json_encode([
            'shortname' => $shortname,
            'message'   => $message,
            'code'      => $code,
            'time'      => time(),
        ], JSON_UNESCAPED_UNICODE), 'local_thcourse');
    }

    /**
     * Doc loi copy gan nhat tu mdl_config.
     */
    protected static function get_last_copy_error(string $shortname): ?array {
        if ($shortname === '') { return null; }
        $raw = get_config('local_thcourse', 'copy_error_' . sha1($shortname));
        if (!$raw) { return null; }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Xoa loi copy khi khoa da duoc tao thanh cong.
     */
    protected static function clear_last_copy_error(string $shortname): void {
        if ($shortname === '') { return; }
        unset_config('copy_error_' . sha1($shortname), 'local_thcourse');
    }

    /**
     * Tra ve array ket qua that bai chuan.
     */
    protected static function fail_result(string $shortname, string $message): array {
        return ['status' => 'Failed', 'id' => 0, 'shortname' => $shortname, 'message' => $message];
    }

    /**
     * Lay gia tri custom field theo shortname tu mang customfields cua request.
     */
    protected static function extract_customfield_value(array $customfields, string $shortname): ?string {
        foreach ($customfields as $cf) {
            if (!empty($cf['shortname']) && $cf['shortname'] === $shortname) {
                return isset($cf['value']) ? trim((string)$cf['value']) : null;
            }
        }
        return null;
    }

    /**
     * Lay shortname cua custom field dung de xac dinh course code
     * (cau hinh tai Admin > Plugins > local_thcourse > coursecode_field_shortname).
     */
    protected static function get_coursecode_field_shortname(): ?string {
        $v = get_config('local_thcourse', 'coursecode_field_shortname');
        if (!is_string($v)) { return null; }
        $v = trim($v);
        return $v !== '' ? $v : null;
    }
}