<?php
require_once($CFG->libdir . '/formslib.php');

class local_th_question_category_map_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $quizid = $this->_customdata['quizid'];
        $quiz = $DB->get_record('quiz', ['id' => $quizid], '*', MUST_EXIST);
        $courseid = $quiz->course;

        $mform->addElement('hidden', 'quizid', $quizid);
        $mform->setType('quizid', PARAM_INT);

        $levels = ['DỄ', 'TRUNG BÌNH', 'KHÓ'];
        $context = context_course::instance($courseid);
        $cats = $DB->get_records_menu('question_categories', ['contextid' => $context->id], '', 'id, name');

        $context_level = CONTEXT_COURSECAT;
        $coursecatid = $DB->get_field('course_categories', 'id', ['name' => 'Thi trực tuyến']);
        $parentcats = $DB->get_records_sql_menu("SELECT qc.id, qc.name
                                            FROM {question_categories} qc
                                            JOIN {context} ctx ON ctx.id = qc.contextid
                                            WHERE ctx.instanceid = $coursecatid AND ctx.contextlevel = $context_level");

        foreach ($levels as $level) {
            $mform->addElement('header', "header_{$level}", "Cấu hình mức độ: {$level}");
            $mform->addElement('select', "source_{$level}", 'Danh mục nguồn', $cats);
            $mform->addElement('select', "target_{$level}", 'Danh mục đích (random)', $parentcats);
        }

        $mform->addElement('submit', 'submitbutton', 'Lưu cấu hình');
    }
}
