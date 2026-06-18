<?php

namespace local_th_activatequiz\form;

require_once($CFG->libdir . '/formslib.php');

use moodleform;
use moodle_url;
use html_writer;

class select_quiz_form extends moodleform {
    public function definition() {
        global $DB;

        $mform    = $this->_form;
        $courseid = (int)$this->_customdata['courseid'];

        // Header + mô tả ngắn.
        $mform->addElement('header', 'h', get_string('activatequiz', 'local_th_activatequiz'));
        $mform->addElement('static', 'desc', '',
            html_writer::tag('div',
                get_string('activatequiz_desc', 'local_th_activatequiz'),
                ['class' => 'text-muted mb-2']
            )
        );

        // Course id ẩn.
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        // Danh sách quiz theo khóa học (sắp xếp theo tên).
        $quizzes = $DB->get_records_menu('quiz', ['course' => $courseid], 'name ASC', 'id, name');

        if (empty($quizzes)) {
            // Không có bài thi nào.
            $mform->addElement('static', 'noquiz', '',
                get_string('noquizzesincourse', 'local_th_activatequiz'));
            $submitattrs = ['class' => 'btn btn-success btn-lg px-4', 'disabled' => 'disabled'];
        } else {
            // Dùng autocomplete để gõ tìm nhanh (đẹp & tiện).
            $mform->addElement(
                'autocomplete',
                'quizid',
                get_string('selectquiz', 'local_th_activatequiz'),
                $quizzes,
                [
                    'multiple' => false,
                    'placeholder'       => get_string('selectquiz_placeholder', 'local_th_activatequiz'),
                    'noselectionstring' => get_string('pleaseselect', 'local_th_activatequiz'),
                ]
            );
            $mform->setType('quizid', PARAM_INT);
            $mform->addRule('quizid', null, 'required', null, 'client');
            $mform->addHelpButton('quizid', 'selectquiz', 'local_th_activatequiz');
        }

        // Nút bấm (to, đẹp).
        $btns = [];
        $btns[] = $mform->createElement('cancel');
        $btns[] = $mform->createElement('submit', 'submitbtn',
            get_string('activenow', 'local_th_activatequiz'));
        $mform->addGroup($btns, 'btns', '', [' '], false);
    }
}

