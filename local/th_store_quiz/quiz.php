<?php

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/quiz/locallib.php';

global $PAGE, $COURSE, $OUTPUT, $SESSION, $DB;

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	throw new \moodle_exception('invalidcourseid');
}

$page = optional_param('page', 0, PARAM_INT);

require_login($COURSE->id);
require_capability('local/th_store_quiz:view', context_course::instance($COURSE->id));
$PAGE->set_url(new moodle_url('/local/th_store_quiz/quiz.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$title = get_string('quizdeletewarning', 'local_th_store_quiz');
$PAGE->set_heading($title);
$PAGE->set_title($title);

$editurl = new moodle_url('/local/th_store_quiz/quiz.php');
$PAGE->settingsnav->add('TH ' . $title, $editurl);

$coursestartfromtext = trim((string)get_config('local_th_store_quiz', 'course_startdate_from'));
$coursestartfrom = 0;
if (!empty($coursestartfromtext)) {
    $date = \DateTime::createFromFormat('d-m-Y', $coursestartfromtext);
    if ($date && $date->format('d-m-Y') === $coursestartfromtext) {
        $date->setTime(0, 0, 0);
        $coursestartfrom = $date->getTimestamp();
    }
}

$coursefilter = '';
if (!empty($coursestartfrom)) {
    $coursefilter = ' AND c.startdate > :course_startdate_from ';
}

$sql = "
    SELECT qa.*, c.fullname AS coursename
      FROM {quiz_attempts} qa
      JOIN {quiz} q 
        ON q.id = qa.quiz
      JOIN {course} c 
        ON c.id = q.course
     WHERE q.name LIKE :quizname
            {$coursefilter}
       AND qa.id NOT IN (
            SELECT id
              FROM (
                    SELECT main.id,
                           ROW_NUMBER() OVER (
                               PARTITION BY main.userid, main.quiz
                               ORDER BY
                                   CASE
                                       WHEN main.timefinish = (
                                           SELECT MAX(sub.timefinish)
                                             FROM {quiz_attempts} sub
                                            WHERE sub.userid = main.userid
                                              AND sub.quiz = main.quiz
                                       )
                                       THEN NULL
                                       ELSE main.sumgrades
                                   END DESC
                           ) AS ranking,
                           ROW_NUMBER() OVER (
                               PARTITION BY main.userid, main.quiz
                               ORDER BY main.attempt DESC
                           ) AS last_attempt
                      FROM {quiz_attempts} main
                   ) ranked
             WHERE ranking <= :getquiz
                OR last_attempt = 1
       )
";

$params = [
    'quizname' => '%bai luyen tap%',
    'getquiz' => 2
];

$params['course_startdate_from'] = $coursestartfrom;

$quizs = $DB->get_records_sql($sql, $params);

// Số mục trên mỗi trang
$itemsPerPage = 100;

// Lấy mục dữ liệu tương ứng với trang hiện tại
$start = $page * $itemsPerPage;
$currentPage_quizs = array_slice($quizs, $start, $itemsPerPage);

$table = new html_table();
$table->head = array(
    get_string('stt', 'local_th_store_quiz'),
    get_string('attemptid', 'local_th_store_quiz'),
    get_string('quiz', 'local_th_store_quiz'),
    get_string('course', 'local_th_store_quiz'),
    get_string('name', 'local_th_store_quiz'),
    get_string('attempt', 'local_th_store_quiz'),
    get_string('status', 'local_th_store_quiz'),
    get_string('start', 'local_th_store_quiz'),
    get_string('finish', 'local_th_store_quiz'),
    get_string('sumgrades', 'local_th_store_quiz'),
    get_string('grade', 'local_th_store_quiz')
);
$stt = 1;

foreach ($currentPage_quizs as $k => $quiz) {
    $data_quiz = $DB->get_record('quiz', array('id' => $quiz->quiz));
    $grade = quiz_rescale_grade($quiz->sumgrades, $data_quiz, false);
    $grade = quiz_format_grade($data_quiz, $grade);

    $user = $DB->get_record('user', array('id' => $quiz->userid));
    $name = $user->firstname . ' ' . $user->lastname;
    $quiz_name = $DB->get_record('quiz', array('id' => $quiz->quiz));
    $row = new html_table_row();

    $cell = new html_table_cell($stt);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->id);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz_name->name);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->coursename);
    $row->cells[] = $cell;

    $cell = new html_table_cell($name);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->attempt);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->state);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->timestart);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->timefinish);
    $row->cells[] = $cell;

    $cell = new html_table_cell($quiz->sumgrades);
    $row->cells[] = $cell;

    $cell = new html_table_cell($grade);
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
$pagingurl = new moodle_url('/local/th_store_quiz/quiz.php', array('page' => $page));
echo $OUTPUT->paging_bar(count($quizs), $page, $itemsPerPage, $pagingurl);
echo $OUTPUT->footer();
?>

