<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of functions used by the quiz module.
 *
 * This contains functions that are called from within the quiz module only
 * Functions that are also called by core Moodle are in {@link lib.php}
 * This script also loads the code in {@link questionlib.php} which holds
 * the module-indpendent code for handling questions and which in turn
 * initialises all the questiontype classes.
 *
 * @package    mod_quiz
 * @copyright  1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


//require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/attemptlib.php');
require_once('./attemptlib.php');


require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager_form.php');
require_once($CFG->dirroot . '/mod/quiz/renderer.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/mod/quiz/report/thessaygrader/lib.php');


/**
 * Work out what state this quiz attempt is in - in the sense used by
 * quiz_get_review_options, not in the sense of $attempt->state.
 * @param object $quiz the quiz settings
 * @param object $attempt the quiz_attempt database row.
 * @return int one of the mod_quiz_display_options::DURING,
 *      IMMEDIATELY_AFTER, LATER_WHILE_OPEN or AFTER_CLOSE constants.
 */
function quiz_attempt_state1($quiz, $attempt) {
    if ($attempt->state == quiz_attempt::IN_PROGRESS) {
        return mod_quiz_display_options::DURING;
    } else if ($quiz->timeclose && time() >= $quiz->timeclose) {
        return mod_quiz_display_options::AFTER_CLOSE;
    } else if (time() < $attempt->timefinish + 120) {
        return mod_quiz_display_options::IMMEDIATELY_AFTER;
    } else {
        return mod_quiz_display_options::LATER_WHILE_OPEN;
    }
}



// function quiz_essay_response_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload, array $options = array()) {
//     global $DB, $CFG;

//     // Kiểm tra ngữ cảnh và quyền truy cập
//     if ($context->contextlevel != CONTEXT_MODULE) {
//         return false;
//     }

//     require_login($course, false, $cm);

//     // Lấy thông tin về bài thi và câu hỏi essay
//     $attemptid = (int)array_shift($args);  // attemptid là id của bài làm
//     $record = $DB->get_record('quiz_attempts', array('attempt' => $attemptid), 'quiz, userid, questionusageid', MUST_EXIST);
//     $quizid = $record->quiz;

//     // Kiểm tra quyền truy cập
//     require_once($CFG->dirroot . '/mod/quiz/locallib.php');
//     $quiz = new quiz($context, $cm, $course);
    
//     // Kiểm tra nếu bài thi không phải của mô-đun hiện tại
//     // if ($quiz->get_instance()->id != $quizid) {
//     //     return false;
//     // }

//     // Lấy thông tin câu trả lời từ bảng question_attempts
//     $questionattempt = $DB->get_record('question_attempts', array('questionusageid' => $record->questionusageid, 'attemptid' => $attemptid), 'responsesummary, questionid', MUST_EXIST);
//     $responsesummary = $questionattempt->responsesummary;

//     // Kiểm tra xem có tệp đính kèm trong responsesummary không (ví dụ hình ảnh hoặc tệp đính kèm)
//     if (strpos($responsesummary, 'file:') !== false) {
//         // Trích xuất thông tin file từ responsesummary (bạn cần sử dụng biểu thức chính quy hoặc phương thức khác để lấy đường dẫn tệp)
//         preg_match('/file:([0-9]+:[0-9]+)/', $responsesummary, $matches);
//         if (!empty($matches)) {
//             $filearea = 'question_attempts';
//             $itemid = (int)$matches[1]; // Lấy itemid từ chuỗi đường dẫn tệp
//             $relativepath = implode('/', $args);
//             $fullpath = "/{$context->id}/$filearea/$itemid/$relativepath";
            
//             $fs = get_file_storage();
//             if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
//                 return false;
//             }

//             // Gửi tệp về cho trình duyệt, ép buộc tải xuống
//             send_stored_file($file, 0, 0, true, $options);
//         }
//     }

//     return false;
// }


// Functions related to attempts ///////////////////////////////////////////////

/**
 * Creates an object to represent a new attempt at a quiz
 *
 * Creates an attempt object to represent an attempt at the quiz by the current
 * user starting at the current time. The ->id field is not set. The object is
 * NOT written to the database.
 *
 * @param object $quizobj the quiz object to create an attempt for.
 * @param int $attemptnumber the sequence number for the attempt.
 * @param stdClass|null $lastattempt the previous attempt by this user, if any. Only needed
 *         if $attemptnumber > 1 and $quiz->attemptonlast is true.
 * @param int $timenow the time the attempt was started at.
 * @param bool $ispreview whether this new attempt is a preview.
 * @param int $userid  the id of the user attempting this quiz.
 *
 * @return object the newly created attempt object.
 */

function quiz_get_review_options1($quiz, $attempt, $context) {
    global $USER, $COURSE;
    $options = mod_quiz_display_options1::make_from_quiz($quiz, quiz_attempt_state($quiz, $attempt));
    $options->readonly = true;
    $options->flags = quiz_get_flag_option($attempt, $context);
    if (!empty($attempt->id)) {
        $options->questionreviewlink = new moodle_url('/mod/quiz/reviewquestion.php',
                array('attempt' => $attempt->id));
    }

    // Show a link to the comment box only for closed attempts.
    if (!empty($attempt->id) && $attempt->state == quiz_attempt1::FINISHED && !$attempt->preview &&
            !is_null($context) && has_capability('mod/quiz:grade', $context)) {
        $options->manualcomment = question_display_options::VISIBLE;
        // $options->manualcommentlink = new moodle_url('/blocks/th_ccoring_collaborators/comment.php',
        //         array('attempt' => $attempt->id));
        $role_id = get_config('quiz_thessaygrader', 'role_hidden');
        if (!empty($role_id)) {
        $context = context_course::instance($COURSE->id);
        $isgrader = user_has_role_assignment($USER->id, $role_id, $context->id);
        }
        if($isgrader){
            $token = encrypt_attempt_id($attempt->id);
            $options->manualcommentlink = new moodle_url('/mod/quiz/report/thessaygrader/comment.php',
            array('attempt_token' => $token));
            // $options->manualcommentlink = new moodle_url('/mod/quiz/report/thessaygrader/comment.php',
            // array('attempt' => $attempt->id));
        } else {
            $options->manualcommentlink = new moodle_url('/mod/quiz/comment.php',
            array('attempt' => $attempt->id));
        }

    }

    if (!is_null($context) && !$attempt->preview &&
            has_capability('mod/quiz:viewreports', $context) &&
            has_capability('moodle/grade:viewhidden', $context)) {
        // People who can see reports and hidden grades should be shown everything,
        // except during preview when teachers want to see what students see.
        $options->attempt = question_display_options::VISIBLE;
        $options->correctness = question_display_options::VISIBLE;
        $options->marks = question_display_options::MARK_AND_MAX;
        $options->feedback = question_display_options::VISIBLE;
        $options->numpartscorrect = question_display_options::VISIBLE;
        $options->manualcomment = question_display_options::VISIBLE;
        $options->generalfeedback = question_display_options::VISIBLE;
        $options->rightanswer = question_display_options::VISIBLE;
        $options->overallfeedback = question_display_options::VISIBLE;
        $options->history = question_display_options::VISIBLE;
        $options->userinfoinhistory = $attempt->userid;

    }

    return $options;
}

/**
 * An extension of question_display_options that includes the extra options used
 * by the quiz.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quiz_display_options1 extends mod_quiz_display_options {
    /**#@+
     * @var integer bits used to indicate various times in relation to a
     * quiz attempt.
     */
    const DURING =            0x10000;
    const IMMEDIATELY_AFTER = 0x01000;
    const LATER_WHILE_OPEN =  0x00100;
    const AFTER_CLOSE =       0x00010;
    /**#@-*/

    /**
     * @var boolean if this is false, then the student is not allowed to review
     * anything about the attempt.
     */
    public $attempt = true;

    /**
     * @var boolean if this is false, then the student is not allowed to review
     * anything about the attempt.
     */
    public $overallfeedback = self::VISIBLE;

    /**
     * Set up the various options from the quiz settings, and a time constant.
     * @param object $quiz the quiz settings.
     * @param int $one of the {@link DURING}, {@link IMMEDIATELY_AFTER},
     * {@link LATER_WHILE_OPEN} or {@link AFTER_CLOSE} constants.
     * @return mod_quiz_display_options set up appropriately.
     */
    public static function make_from_quiz($quiz, $when) {
        $options = new self();

        $options->attempt = self::extract($quiz->reviewattempt, $when, true, false);
        $options->correctness = self::extract($quiz->reviewcorrectness, $when);
        $options->marks = self::extract($quiz->reviewmarks, $when,
                self::MARK_AND_MAX, self::MAX_ONLY);
        $options->feedback = self::extract($quiz->reviewspecificfeedback, $when);
        $options->generalfeedback = self::extract($quiz->reviewgeneralfeedback, $when);
        $options->rightanswer = self::extract($quiz->reviewrightanswer, $when);
        $options->overallfeedback = self::extract($quiz->reviewoverallfeedback, $when);

        $options->numpartscorrect = $options->feedback;
        $options->manualcomment = $options->feedback;

        if ($quiz->questiondecimalpoints != -1) {
            $options->markdp = $quiz->questiondecimalpoints;
        } else {
            $options->markdp = $quiz->decimalpoints;
        }

        return $options;
    }

    protected static function extract($bitmask, $bit,
            $whenset = self::VISIBLE, $whennotset = self::HIDDEN) {
        if ($bitmask & $bit) {
            return $whenset;
        } else {
            return $whennotset;
        }
    }
}


/**
 * Get quiz attempt and handling error.
 *
 * @param int $attemptid the id of the current attempt.
 * @param int|null $cmid the course_module id for this quiz.
 * @return quiz_attempt1 $attemptobj all the data about the quiz attempt.
 * @throws moodle_exception
 */
function quiz_create_attempt_handling_errors1($attemptid, $cmid = null) {
    try {
        $attempobj = quiz_attempt1::create($attemptid);
    } catch (moodle_exception $e) {
        if (!empty($cmid)) {
            list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');
            $continuelink = new moodle_url('/mod/quiz/view.php', array('id' => $cmid));
            $context = context_module::instance($cm->id);
            if (has_capability('mod/quiz:preview', $context)) {
                throw new moodle_exception('attempterrorcontentchange', 'quiz', $continuelink);
            } else {
                throw new moodle_exception('attempterrorcontentchangeforuser', 'quiz', $continuelink);
            }
        } else {
            throw new moodle_exception('attempterrorinvalid', 'quiz');
        }
    }
    if (!empty($cmid) && $attempobj->get_cmid() != $cmid) {
        throw new moodle_exception('invalidcoursemodule');
    } else {
        return $attempobj;
    }
}