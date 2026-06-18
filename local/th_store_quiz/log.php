<?php

require_once '../../config.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION, $DB;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	throw new \moodle_exception('invalidcourseid');
}

$page = optional_param('page', 0, PARAM_INT);

require_login($COURSE->id);
require_capability('local/th_store_quiz:view', context_course::instance($COURSE->id));
$PAGE->set_url(new moodle_url('/local/th_store_quiz/log.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$title = get_string('deleted', 'local_th_store_quiz');
$PAGE->set_heading($title);
$PAGE->set_title($title);

$editurl = new moodle_url('/local/th_store_quiz/log.php');
$PAGE->settingsnav->add('TH ' . $title, $editurl);

$quizs = $DB->get_records_sql("SELECT * FROM {th_store_quiz};");

// Số mục trên mỗi trang
$itemsPerPage = 100;

// Lấy mục dữ liệu tương ứng với trang hiện tại
$start = $page * $itemsPerPage;
$currentPage_quizs = array_slice($quizs, $start, $itemsPerPage);

$table = new html_table();
$table->head = array(
    get_string('stt', 'local_th_store_quiz'), 
    get_string('course', 'local_th_store_quiz'),
    get_string('quiz', 'local_th_store_quiz'), 
    get_string('name', 'local_th_store_quiz'), 
    get_string('attemptid', 'local_th_store_quiz'),
    get_string('grade', 'local_th_store_quiz')
);
$stt = 1;

foreach ($currentPage_quizs as $k => $quiz) {

    $quiz_id = $quiz->quiz;
    $user_id = $quiz->userid;

    // Kiểm tra quiz tồn tại
    $quiz_name = $DB->get_record('quiz', array('id' => $quiz_id));
    if (!$quiz_name) {
        continue;
    }

    // Kiểm tra course tồn tại
    $course = $DB->get_record('course', array('id' => $quiz_name->course));
    if (!$course) {
        continue;
    }

    // Kiểm tra user tồn tại
    $user = $DB->get_record('user', array('id' => $user_id));
    if (!$user) {
        continue;
    }

    $name = fullname($user);

    $row = new html_table_row();

    $cell = new html_table_cell($stt);
    $row->cells[] = $cell;

    $cell = new html_table_cell($course->fullname);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz_name->name);
    $row->cells[] = $cell;

    $cell = new html_table_cell($name);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->attempt);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->sumgrades);
    $row->cells[] = $cell;

    $table->data[] = $row;

    $stt++;
}

$table->attributes = array('class' => 'table_quiz', 'border' => '1');
$table->attributes['style'] = "width: 100%; text-align:center;";

$html = html_writer::table($table);

echo $OUTPUT->header();
echo $OUTPUT->heading("<center>$title</center>");
echo $html;

//panigation
$pagingurl = new moodle_url('/local/th_store_quiz/log.php', array('page' => $page));
echo $OUTPUT->paging_bar(count($quizs), $page, $itemsPerPage, $pagingurl);
echo $OUTPUT->footer();
?>

