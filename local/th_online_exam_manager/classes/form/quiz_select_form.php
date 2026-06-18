<?php

require_once("$CFG->libdir/formslib.php");

class local_th_online_exam_manager_quiz_select_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $categoryidnumber = get_config('local_th_online_exam_manager', 'categoryidnumber');
        $setting_categoryid = null;

        if (!empty($categoryidnumber)) {
            $category = $DB->get_record(
                'course_categories',
                ['idnumber' => $categoryidnumber],
                'id',
                IGNORE_MISSING
            );

            if ($category) {
                $setting_categoryid = $category->id;
            }
        }
        // 1. Lấy danh sách khóa học (course) từ DB
        // $courses = $DB->get_records_menu('course', ['visible' => 1, 'category' => $setting_categoryid], 'fullname ASC', 'id, fullname');
        $courses = [];

        if ($setting_categoryid) {

            $cat = \core_course_category::get($setting_categoryid);

            // Lấy tất cả course trong category + subcategory
            $courseobjects = $cat->get_courses([
                'recursive' => true
            ]);

            foreach ($courseobjects as $course) {
                if ($course->id == SITEID) {
                    continue; // bỏ front page
                }
                $courses[$course->id] = $course->fullname;
            }

            asort($courses); // sắp xếp theo tên
        }

        unset($courses[1]); // Loại bỏ khóa học mặc định (id = 1: front page)

        $mform->addElement('autocomplete', 'courseid', get_string('selectcourse', 'local_th_online_exam_manager'), $courses, [
            'noselectionstring' => '',
            'multiple' => false,
        ]);
        $mform->addRule('courseid', null, 'required', null, 'client');

        // 2. Quiz autocomplete (dựa trên course đã chọn)
        $mform->addElement('autocomplete', 'quizid', get_string('selectquiz', 'local_th_online_exam_manager'), [],
        [
            'ajax' => 'local_th_online_exam_manager/load_quizzes',
            'noselectionstring' => '',
            'multiple' => false,
            'valuehtmlcallback' => function($value) {
                global $DB;
                $quiz = $DB->get_record('quiz', ['id' => $value]);
                return $quiz ? $quiz->name : get_string('quiznotfound', 'local_th_online_exam_manager');
            }
        ]);
        $mform->addRule('quizid', null, 'required', null, 'client');

        $optionsattempt = [
            'multiple' => true,
            'ajax' => 'local_th_online_exam_manager/load_attempts',
            'noselectionstring' => '',
            'valuehtmlcallback' => function($value) {
                global $DB;
                $attempt = $DB->get_record('quiz_attempts', ['id' => $value], '*', IGNORE_MISSING);
                if (!$attempt) return get_string('attemptnotfound', 'local_th_online_exam_manager');
                $user = $DB->get_record('user', ['id' => $attempt->userid], '*', IGNORE_MISSING);
                $customfields = profile_user_record($user->id);
                $mhv = '';
                $setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');
                if (!empty($setting_ma_hoc_vien)) {
                    $arr_mahv = explode(',', $setting_ma_hoc_vien);
                    $ma_hoc_vien1 = trim($arr_mahv[0]); // đảm bảo không dính khoảng trắng

                    if (!empty($ma_hoc_vien1) && isset($customfields->$ma_hoc_vien1)) {
                        $mhv = $customfields->$ma_hoc_vien1;
                    }
                }
                
                $label = fullname($user) . ", $user->username, $mhv" .
                    " – " . get_string('attemptnumber', 'local_th_online_exam_manager', $attempt->id) .
                    " – " . userdate($attempt->timefinish);
                return $label;
            }
        ];
        $mform->addElement('autocomplete', 'attemptid', get_string('selectattempt', 'local_th_online_exam_manager'), [], $optionsattempt);
        $mform->addRule('attemptid', null, 'required', null, 'client');

        $this->add_action_buttons(true, get_string('submit', 'local_th_online_exam_manager'));
    }
}
