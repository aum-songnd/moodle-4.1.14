<?php

namespace mod_thpage\task;
defined('MOODLE_INTERNAL') || die();


require_once $CFG->libdir . '/datalib.php';
require_once($CFG->dirroot . '/mod/thpage/lib.php');
require_once($CFG->dirroot . '/mod/thpage/thpagepdf.php');


class create_pdf_adhoc_task extends \core\task\adhoc_task {

    public function execute() {
        global $CFG, $DB;

        mtrace("My ad-hoc task started");

        $data = $this->get_custom_data();

        mtrace('data');
        mtrace(json_encode($data));
        // if (!file_exists($path . '/thpage_create_pdf')) {
        //     mkdir($path . '/thpage_create_pdf', 0777);
        // }
        // if ($data->type == 1) {
        $thpageid = $data->thpageid;
        $courseid = $data->course;
        $cmid = $data->cmid;
        $contextid = $data->contextid;
        $path = $CFG->tempdir;
        $tempdir = make_temp_directory('thpage_create_pdf');
        check_dir_exists($tempdir);

        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            mtrace("No course id: " . json_encode($courseid));
            mtrace("My ad-hoc task ended");
            exit;
        }

        if (!$thpage = $DB->get_record('thpage', array('id' => $thpageid))) {
            mtrace("No thpage id: " . json_encode($thpageid));
            mtrace("My ad-hoc task ended");
            exit;
        }
        if (!$cm = $DB->get_record('course_modules', array('id' => $cmid))) {
            mtrace("No thpage id: " . json_encode($cmid));
            mtrace("My ad-hoc task ended");
            exit;
        }

        $thpage->content = thpage_create_content($course, $thpage, $cm);
    
        try {
            thpage_create_pdf($course, $thpage->content, 'F', $tempdir, $thpage->font);
            $filename = get_string('dcct', 'mod_thpage') . '.pdf';
            $fs = get_file_storage();
            $fileinfo = [
                'contextid' => $contextid,
                'component' => 'mod_thpage',
                'filearea'  => 'pdf',
                'itemid'    => 0,
                'filepath'  => '/',
                'filename'  => $filename,
            ];
            mtrace('fileinfo');
            mtrace(json_encode($fileinfo));

            $files = $fs->get_area_files(
                $fileinfo['contextid'],
                $fileinfo['component'],
                $fileinfo['filearea'],
                $fileinfo['itemid']
            );
            foreach ($files as $file) {
                $file->delete();
                mtrace("Delete old file");
            }

            $fs->create_file_from_pathname($fileinfo, $tempdir . '/' . $filename);
            @unlink($tempdir . '/' . $filename);
            mtrace("Create pdf success");
        } catch (Exception $e) {
            mtrace(json_encode($e));
            mtrace("Create pdf error");
        }

        mtrace("My ad-hoc task ended");
        // } 
        // else {

        //     $courseid = $data->course;

        //     if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        //         mtrace("No course id: " . json_encode($courseid));
        //         mtrace("My ad-hoc task ended");
        //         exit;
        //     }

        //     $sql = "SELECT cm.id,cm.instance,ctx.id as contextid
        //         from {course_modules} cm
        //         join {thpage} p on p.id=cm.instance
        //         join {context} ctx on ctx.instanceid=cm.id
        //         where cm.course=:course and cm.module=(select id from {modules} where name=:name)";
        //     $params = array('course' => $courseid, 'name' => 'thpage');
        //     $records = $DB->get_records_sql($sql, $params);

        //     foreach ($records as $key => $record) {
        //         if (!$thpage = $DB->get_record('thpage', array('id' => $record->instance))) {
        //             mtrace("No thpage id: " . json_encode($record->instance));
        //             mtrace("My ad-hoc task ended");
        //             exit;
        //         }
        //         if (!$cm = $DB->get_record('course_modules', array('id' => $record->id))) {
        //             mtrace("No course_modules id: " . json_encode($record->id));
        //             mtrace("My ad-hoc task ended");
        //             exit;
        //         }

        //         $thpage->content = thpage_create_content($course, $thpage, $cm);
        
        //         try {
        //             thpage_create_pdf($course, $thpage->content, 'F', $tempdir);
        //             $filename = get_string('dcct', 'mod_thpage') . '.pdf';
        //             $fs = get_file_storage();
        //             $fileinfo = [
        //                 'contextid' => $record->contextid,
        //                 'component' => 'mod_thpage',
        //                 'filearea'  => 'pdf',
        //                 'itemid'    => 0,
        //                 'filepath'  => '/',
        //                 'filename'  => $filename,
        //             ];
        //             mtrace('fileinfo');
        //             mtrace(json_encode($fileinfo));

        //             $files = $fs->get_area_files(
        //                 $fileinfo['contextid'],
        //                 $fileinfo['component'],
        //                 $fileinfo['filearea'],
        //                 $fileinfo['itemid']
        //             );
        //             foreach ($files as $file) {
        //                 $file->delete();
        //                 mtrace("Delete old file");
        //             }

        //             $fs->create_file_from_pathname($fileinfo, $tempdir . '/' . $filename);
        //             @unlink($tempdir . '/' . $filename);
        //             mtrace("Create pdf success");
        //         } catch (Exception $e) {
        //             mtrace(json_encode($e));
        //             mtrace("Create pdf error");
        //         }

        //         mtrace("My ad-hoc task ended");
        //     }
        // }
    }
}