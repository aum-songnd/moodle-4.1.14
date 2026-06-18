<?php

namespace local_th_export_quiz_batch\task;

use core\task\adhoc_task;
use context_course;
use moodle_url;
use local_th_export_quiz_batch\quiz_thexport_engine;

class export_quiz_task extends adhoc_task {

    public function execute() {
        global $CFG, $DB;

        raise_memory_limit(MEMORY_HUGE);
        set_time_limit(300);

        $data = $this->get_custom_data();
        $courseid = $data->courseid;
        $quizids  = $data->quizids; 
        $userid   = $data->userid;
        $recordid = $data->recordid;

        mtrace("Bắt đầu xuất toàn bộ quiz cho courseid={$courseid}");

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        require_once($CFG->dirroot . '/mod/quiz/report/thexport/vendor/autoload.php');
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
        require_once($CFG->dirroot . '/local/th_export_quiz_batch/classes/quiz_thexport_engine.php');

        $context = context_course::instance($courseid);

        // Thư mục tạm chứa PDF
        $tempbase = $CFG->dataroot . '/temp/quiz_export_data';
        check_dir_exists($tempbase);
        $tempdir = $tempbase . "/{$courseid}_" . time();
        check_dir_exists($tempdir);

        $engine = new quiz_thexport_engine();

        foreach ($quizids as $quizid) {
            $quiz = $DB->get_record('quiz', ['id' => $quizid, 'course' => $courseid], '*', MUST_EXIST);

            $attempts = $DB->get_records('quiz_attempts', [
                'quiz' => $quizid,
                'state' => 'finished',
                'preview' => 0
            ]);

            if (empty($attempts)) {
                mtrace("Quiz {$quizid} không có attempt nào.");
                continue;
            }

            $quizname = clean_filename($quiz->name . ' ' . $quiz->id);
            $roomdir = clean_filename($course->shortname);
            $quizdir = "$tempdir/$roomdir/$quizname";
            check_dir_exists($quizdir);

            foreach ($attempts as $attempt) {
                $user = $DB->get_record('user', ['id' => $attempt->userid], '*', MUST_EXIST);
                $username = clean_filename(fullname($user) . '_' . $user->username);
                $userdir = "$quizdir/$username";
                check_dir_exists($userdir);

                try {
                    $attemptobj = \quiz_attempt::create($attempt->id);
                    mtrace("Xuất PDF cho quiz={$quizid}, attemptid={$attempt->id}");

                    $pdfpath = $engine->a2pdf($attemptobj, quiz_thexport_engine::PAGEMODE_SINGLEPAGE, $userdir);

                    if (file_exists($pdfpath)) {
                        $size = filesize($pdfpath);
                        mtrace("PDF đã tạo: $pdfpath (Size: {$size} bytes)");
                    } else {
                        mtrace("Không tìm thấy file PDF: $pdfpath");
                    }

                } catch (\Exception $e) {
                    mtrace("Lỗi khi xử lý attempt {$attempt->id}: " . $e->getMessage());
                    continue;
                }
            }
        }

        // Nén tất cả quiz của course thành 1 ZIP
        $exportdir = $CFG->dataroot . '/quiz_export';
        check_dir_exists($exportdir);

        $filename = 'export_' . clean_filename($course->shortname) . '.zip';
        $exportpath = $exportdir . '/' . $filename;

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempdir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fullpath = $file->getPathname();
                $relativepath = substr($fullpath, strlen($tempdir) + 1);
                $relativepath = str_replace('\\', '/', $relativepath);
                $relativepath = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $relativepath);
                $relativepath = preg_replace('/[^A-Za-z0-9_\-\/\.]/', '_', $relativepath);
                $files[] = ['realpath' => $fullpath, 'zippath' => $relativepath];
            }
        }

        // Tạo ZIP
        $zip = new \ZipArchive();
        if ($zip->open($exportpath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file['realpath'], $file['zippath']);
            }
            $zip->close();
            mtrace("Tạo ZIP thành công: $exportpath");
        } else {
            mtrace("Lỗi khi tạo file ZIP.");
            $this->mark_failed($recordid);
            return;
        }

        remove_dir($tempdir);

        $fileurl = new moodle_url('/local/th_export_quiz_batch/download.php', ['file' => $filename]);

        $update = (object)[
            'id' => $recordid,
            'status' => 'done',
            'filepath' => $exportpath,
            'fileurl' => $fileurl->out(false),
        ];
        $DB->update_record('local_th_export_quiz_batch', $update);

        mtrace("Hoàn tất xuất tất cả quiz của course {$courseid}.");

    }

    private function mark_failed($recordid) {
        global $DB;

        $record = new \stdClass();
        $record->id = (int)$recordid;
        $record->status = 'failed';

        $DB->update_record('local_th_export_quiz_batch', $record);
    }
}