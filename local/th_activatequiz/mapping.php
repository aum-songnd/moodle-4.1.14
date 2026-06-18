<?php

require_once('../../config.php');
require_once('classes/form/mapping_form.php');

$quizid = required_param('quizid', PARAM_INT);
$quiz = $DB->get_record('quiz', ['id' => $quizid], '*', MUST_EXIST);
require_login($quiz->course);

$mform = new local_th_question_category_map_form(null, ['quizid' => $quizid]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/quiz/view.php', ['id' => $quiz->coursemodule]));
} else if ($data = $mform->get_data()) {
    $DB->delete_records('local_th_question_category_map', ['quizid' => $quizid]);

    $levels = ['DỄ', 'TRUNG BÌNH', 'KHÓ'];

    foreach ($levels as $level) {
        $record = new stdClass();
        $record->quizid = $quizid;
        $record->level = $level;
        $record->sourcecategoryid = $data->{"source_$level"};
        $record->targetcategoryid = $data->{"target_$level"};
        $record->timecreated = time();

        $DB->insert_record('local_th_question_category_map', $record);
    }

    redirect(new moodle_url('/mod/quiz/view.php', ['id' => $quiz->coursemodule]), "Đã lưu cấu hình mapping cho quiz.");
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
