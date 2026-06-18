<?php

namespace local_th_export_quiz_batch\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class export_form extends \moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;

        // 1. Lấy idnumber từ setting
        $categoryidnumber = get_config('local_th_export_quiz_batch', 'categoryidnumber');

        if (empty($categoryidnumber)) {
            $mform->addElement(
                'static',
                'nocategory',
                '',
                get_string('nocategoryconfigured', 'local_th_export_quiz_batch')
            );
            return;
        }

        // 2. Lấy category theo idnumber
        $category = $DB->get_record(
            'course_categories',
            ['idnumber' => $categoryidnumber],
            'id, path',
            IGNORE_MISSING
        );

        if (!$category) {
            $mform->addElement(
                'static',
                'nocategory',
                '',
                get_string('categorynotfound', 'local_th_export_quiz_batch', $categoryidnumber)
            );
            return;
        }

        $categoryid = (int)$category->id;

        // 3. Lấy course trong category đó và các category con
        $sql = "SELECT c.*
                  FROM {course} c
                  JOIN {course_categories} cc ON cc.id = c.category
                 WHERE (cc.id = :catid OR cc.path LIKE :pathlike)";
        $params = [
            'catid'    => $categoryid,
            'pathlike' => $category->path . '/%',
        ];
        
        $courses = $DB->get_records_sql($sql, $params);

        $exported = $DB->get_records('local_th_export_quiz_batch', ['status' => 'done'], '', 'id, courseid');
        $exportedids = array_column($exported, 'courseid');

        $options = [];
        foreach ($courses as $course) {
            if (in_array($course->id, $exportedids)) {
                continue;
            }

            $quizcount = $DB->count_records('quiz', ['course' => $course->id]);
            if ($quizcount > 0) {
                $options[$course->id] = $course->fullname . ' (' . $course->shortname . ')';
            }
        }

        if (empty($options)) {
            $mform->addElement('static', 'noquiz', '', get_string('noquizavailable', 'local_th_export_quiz_batch'));
            return;
        }

        $mform->addElement('autocomplete', 'courseids', get_string('selectcourses', 'local_th_export_quiz_batch'), $options, ['multiple' => true]);
        $mform->addRule('courseids', get_string('selectcourses_required', 'local_th_export_quiz_batch'), 'required', null, 'client');
        $this->add_action_buttons(true, get_string('export', 'local_th_export_quiz_batch'));
    }
}
