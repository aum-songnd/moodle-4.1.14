<?php
defined('MOODLE_INTERNAL') || die();

function block_th_exam_schedule_tabs(context $context, moodle_url $currenturl) {
    global $OUTPUT;

    $tabs = [];
    $currenttab = '';

    if (has_capability('block/th_exam_schedule:manageimports', $context)) {
        $addurl = new moodle_url('/blocks/th_exam_schedule/manage.php');
        $tabs[] = new tabobject('manage', $addurl, get_string('manageexam', 'block_th_exam_schedule'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'manage';
        }
    }

    if (has_capability('block/th_exam_schedule:manageimports', $context)) {
        $addurl = new moodle_url('/blocks/th_exam_schedule/manage_courses.php');
        $tabs[] = new tabobject('managecourses', $addurl, get_string('managecourses', 'block_th_exam_schedule'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'managecourses';
        }
    }

    if (has_capability('block/th_exam_schedule:manageimports', $context)) {
        $allurl = new moodle_url('/blocks/th_exam_schedule/import_courses.php');
        $tabs[] = new tabobject('course', $allurl, get_string('addcourse', 'block_th_exam_schedule'));
        if ($currenturl->get_path() === $allurl->get_path()) {
            $currenttab = 'course';
        }
    }

    if (has_capability('block/th_exam_schedule:manageimports', $context)) {
        $addurl = new moodle_url('/blocks/th_exam_schedule/import_exams.php');
        $tabs[] = new tabobject('exam', $addurl, get_string('addexam', 'block_th_exam_schedule'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'exam';
        }
    }

    if (has_capability('block/th_exam_schedule:manageimports', $context)) {
        $addurl = new moodle_url('/blocks/th_exam_schedule/delete_courses.php');
        $tabs[] = new tabobject('deletecourses', $addurl, get_string('deletecourses', 'block_th_exam_schedule'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'deletecourses';
        }
    }

    if (has_capability('block/th_exam_schedule:manageimports', $context)) {
        $addurl = new moodle_url('/blocks/th_exam_schedule/delete_exams.php');
        $tabs[] = new tabobject('deleteexams', $addurl, get_string('deleteexams', 'block_th_exam_schedule'));
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'deleteexams';
        }
    }

    // Hiển thị thanh tab.
    if (!empty($tabs)) {
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    return null;
}

