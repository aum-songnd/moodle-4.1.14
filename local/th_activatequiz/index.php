<?php

require('../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once(__DIR__ . '/classes/form/select_quiz_form.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once('lib.php');
require_once($CFG->libdir . '/questionlib.php');

$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);

$context = context_course::instance($courseid);
require_capability('local/th_activatequiz:pushquestions', $context);

$PAGE->set_url('/local/th_activatequiz/index.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title('Activate Quiz');
$PAGE->set_heading('Activate Quiz');
$PAGE->set_pagelayout('course');

$mform = new \local_th_activatequiz\form\select_quiz_form(null, ['courseid' => $courseid]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} elseif ($data = $mform->get_data()) {
    $quiz = $DB->get_record('quiz', ['id' => $data->quizid], '*', MUST_EXIST);
    $map = [];
    $slots = $DB->get_records('quiz_slots', ['quizid' => $quiz->id]);

    $customfield = $DB->get_record('customfield_data', array('instanceid' => $courseid));
    $course_shortname = $customfield->charvalue;

    foreach ($slots as $slot) {
        $question = $DB->get_record('question', ['id' => $slot->questionid]);
        if ($question->qtype === 'random') {
            $cat = $DB->get_record('question_categories', ['id' => $question->category]);
            $cat_name = $cat->name; // QUIZ_ONLINE/ICT/DỄ
            
            switch ($cat_name) {
                case 'DỄ':
                    $mapkey = 'DỄ';
                    break;
                case 'TRUNG BÌNH':
                case 'TRUNGBINH':
                    $mapkey = 'TRUNG BÌNH';
                    break;
                case 'KHÓ':
                    $mapkey = 'KHÓ';
                    break;
                default:
                    $mapkey = null;
            }
            
            if ($mapkey) {
                if (!isset($map[$mapkey])) {
                    $map[$mapkey] = ['id' => $cat->id, 'count' => 1];
                } else {
                    $map[$mapkey]['count'] += 1;
                }
            }
            
        }
    }

    $context_level = CONTEXT_COURSECAT;

    $coursecatid = $DB->get_field('course_categories', 'id', ['name' => 'Thi trực tuyến']);
    $parentcat = $DB->get_record_sql("SELECT qc.*
                                        FROM {question_categories} qc
                                        JOIN {context} ctx ON ctx.id = qc.contextid
                                        WHERE ctx.instanceid = $coursecatid AND ctx.contextlevel = $context_level AND qc.name = '$course_shortname'");

    if (!$parentcat) {
        throw new moodle_exception("Không tìm thấy danh mục môn $course_shortname");
    }

    $subject = $parentcat->name;
    $results = [];
    foreach ($map as $level => $info) {
        $sourcecat = $DB->get_record('question_categories', ['name' => $level, 'parent' => $parentcat->id]);
        $targetcat = $info['id'];

        if (!$sourcecat || !$targetcat) continue;

        // $questions = $DB->get_records_select('question',
        //     'category = ? AND hidden = 0 AND parent = 0',
        //     [$sourcecat->id],
        //     'RAND()', '*', 0, $info['count']
        // );

        // $questions = $DB->get_records_select('question',
        //     'category = ? AND hidden = 0 AND parent = 0',
        //     [$sourcecat->id],
        //     'id ASC' // hoặc NULL nếu không cần sắp xếp
        // );

        // lấy tất cả câu hỏi trong danh mục con
        $categoryids = question_categorylist($sourcecat->id); // trả về mảng ID

        list($in_sql, $params) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_QM);

        // Truy vấn tất cả câu hỏi thuộc các danh mục này
        $questions = $DB->get_records_select('question',
            "category $in_sql AND hidden = 0 AND parent = 0",
            $params,
            'id ASC'
        );

        $copied = 0;
        foreach ($questions as $q) {
            copy_question_to_category($q->id, $targetcat, context_course::instance($courseid));
            $copied++;
        }

        // Tên danh mục nguồn kèm cha
        $sourceparentname = $DB->get_field('question_categories', 'name', ['id' => $sourcecat->parent]);
        $sourcecatlink = new moodle_url('/question/edit.php', [
            'courseid' => $courseid,
            'category' => $sourcecat->id . ',' . $sourcecat->contextid
        ]);

        $source_display = html_writer::link($sourcecatlink, $sourceparentname . ' / ' . $sourcecat->name);

        // Tên danh mục đích kèm cha
        $targetcatrecord = $DB->get_record('question_categories', ['id' => $targetcat]);
        $targetparentname = $DB->get_field('question_categories', 'name', ['id' => $targetcatrecord->parent]);
        $targetcatlink = new moodle_url('/question/edit.php', [
            'courseid' => $courseid,
            'category' => $targetcat . ',' . context_course::instance($courseid)->id
        ]);
        $target_display = html_writer::link($targetcatlink, $targetparentname . ' / ' . $targetcatrecord->name);

        $results[] = [
            'source' => $source_display,
            'target' => $target_display,
            'count' => $copied
        ];
    }

    // Hiển thị kết quả
    echo $OUTPUT->header();
    $mform->display();
    echo html_writer::tag('h3', 'Kết quả sao chép câu hỏi');

    $table = new html_table();
    $table->head = ['Danh mục nguồn', 'Danh mục đích', 'Số câu đã copy'];
    if($results) {
        foreach ($results as $r) {
            $table->data[] = [$r['source'], $r['target'], $r['count']];
        }
    
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->notification(
            get_string('activatecourse_failed_checkquizsetup', 'local_th_activatequiz'),
            \core\output\notification::NOTIFY_ERROR
        );
    }
    
    echo $OUTPUT->footer();
    exit;

    // redirect($PAGE->url, get_string('success', 'local_th_activatequiz'), 2);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

function get_category_id($path) {
    global $DB;
    $parts = explode('/', $path);
    $parent = 0;
    foreach ($parts as $name) {
        $cat = $DB->get_record('question_categories', ['name' => $name, 'parent' => $parent]);
        if (!$cat) return 0;
        $parent = $cat->id;
    }
    return $parent;
}
