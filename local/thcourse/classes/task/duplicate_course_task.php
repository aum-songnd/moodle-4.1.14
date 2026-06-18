<?php
namespace local_thcourse\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Adhoc task: wrapper để gọi copy_helper::create_copy() trong cron.
 *
 * Flow:
 *  API -> queue duplicate_course_task
 *  cron -> duplicate_course_task
 *  duplicate_course_task -> copy_helper::create_copy()
 *  copy_helper -> queue core asynchronous_copy_task
 *  cron -> asynchronous_copy_task
 *  restore -> course created
 */
class duplicate_course_task extends \core\task\adhoc_task {

    public function get_name(): string {
        return 'Duplicate course via copy_helper (local_thcourse)';
    }

    public function execute(): void {
        global $DB, $CFG;

        require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot.'/backup/util/includes/restore_includes.php');
        require_once($CFG->dirroot.'/backup/copy_helper.php');

        $data = (object)$this->get_custom_data();

        $sourceshortname = $data->sourceshortname ?? '';
        $fullname        = $data->fullname ?? '';
        $shortname       = $data->shortname ?? '';
        $categoryid      = (int)($data->categoryid ?? 0);
        $visible         = (int)($data->visible ?? 0);
        $startdate       = (int)($data->startdate ?? 0);
        $idnumber        = !empty($data->idnumber) ? (string)$data->idnumber : $shortname;

        if (!$sourceshortname || !$fullname || !$shortname || !$categoryid) {
            mtrace('[local_thcourse] duplicate_course_task: missing required data');
            return;
        }

        // tránh task chạy 2 lần
        if ($DB->record_exists('course', ['shortname'=>$shortname])) {
            mtrace("[local_thcourse] course '{$shortname}' already exists, skipping.");
            \local_thcourse_external::clear_last_copy_error_static($shortname);
            return;
        }

        // source course
        $sourcecourse = $DB->get_record('course', ['shortname'=>$sourceshortname], '*', IGNORE_MISSING);

        if (!$sourcecourse) {
            mtrace("[local_thcourse] source course '{$sourceshortname}' not found");

            \local_thcourse_external::set_last_copy_error_static(
                $shortname,
                "Không tìm thấy khóa nguồn '{$sourceshortname}'",
                'source_not_found'
            );

            return;
        }

        // category tồn tại
        if (!$DB->record_exists('course_categories',['id'=>$categoryid])) {

            \local_thcourse_external::set_last_copy_error_static(
                $shortname,
                "Category {$categoryid} không tồn tại",
                'category_not_found'
            );

            return;
        }

        // decode options
        $userdata = 0;
        $keptroles = [];

        foreach ((array)($data->options ?? []) as $opt) {

            $opt = (array)$opt;

            $name  = $opt['name'] ?? '';
            $value = $opt['value'] ?? '';

            if ($name === 'users') {
                $userdata = (int)$value;
            }

            if (strpos($name,'role_') === 0 && (int)$value !== 0) {
                $keptroles[] = (int)$value;
            }

        }

        // calculate enddate
        $enddate = 0;

        $sourcestart = (int)$sourcecourse->startdate;
        $sourceend   = (int)($sourcecourse->enddate ?? 0);

        if ($sourcestart && $startdate && $sourceend) {
            $enddate = $sourceend + ($startdate - $sourcestart);
        }

        // build copydata
        $copydata = (object)[

            'courseid'  => (int)$sourcecourse->id,
            'fullname'  => $fullname,
            'shortname' => $shortname,
            'category'  => $categoryid,
            'visible'   => $visible,
            'startdate' => $startdate,
            'enddate'   => $enddate,
            'idnumber'  => $idnumber,
            'userdata'  => $userdata,
            'keptroles' => $keptroles

        ];

        // setup user context
        $taskuserid = $this->get_userid();

        if ($taskuserid) {

            $user = $DB->get_record('user',['id'=>$taskuserid],'*',IGNORE_MISSING);

            \cron_setup_user($user ?: null);

        } else {

            \cron_setup_user();

        }

        // run copy
        try {

            $copyids = \copy_helper::create_copy($copydata);

            mtrace(sprintf(
                "[local_thcourse] async copy queued '%s' (src='%s') backupid=%s restoreid=%s",
                $shortname,
                $sourceshortname,
                $copyids['backupid'] ?? 'n/a',
                $copyids['restoreid'] ?? 'n/a'
            ));

            // save ids
            set_config(
                'copy_backupids_'.sha1($shortname),
                json_encode([
                    'backupid'=>$copyids['backupid'] ?? '',
                    'restoreid'=>$copyids['restoreid'] ?? ''
                ]),
                'local_thcourse'
            );

        } catch (\Throwable $e) {

            mtrace("[local_thcourse] copy failed '{$shortname}': ".$e->getMessage());

            \local_thcourse_external::set_last_copy_error_static(
                $shortname,
                'copy_helper error: '.$e->getMessage(),
                'copy_exception'
            );

        }

    }

}