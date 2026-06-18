<?php

namespace local_th_store_quiz\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class auto_delete_quiz extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('taskname', 'local_th_store_quiz');
    }

    public function execute() {
        global $DB;

        // $days = (int)get_config('local_th_store_quiz', 'deletion_date');
		$days = 0;
        $isactive = (int)get_config('local_th_store_quiz', 'active');
        $getquiz = (int)get_config('local_th_store_quiz', 'get_quiz');
		$coursestartfromtext = trim((string)get_config('local_th_store_quiz', 'course_startdate_from'));
        if ($coursestartfromtext === '') {
            $coursestartfromtext = date('d-m-Y');
        }
		$coursestartfrom = 0;
		
		if (!empty($coursestartfromtext)) {
			$date = \DateTime::createFromFormat('d-m-Y', $coursestartfromtext);
			if ($date && $date->format('d-m-Y') === $coursestartfromtext) {
				$date->setTime(0, 0, 0);
				$coursestartfrom = $date->getTimestamp();
			} else {
				mtrace('local_th_store_quiz: invalid course_startdate_from format. Expected dd-mm-yyyy, got: ' . $coursestartfromtext);
                return;
			}
		}

        if (!$isactive) {
            mtrace('local_th_store_quiz: task disabled.');
            return;
        }

        if ($days < 0) {
            $days = 7;
        }

        if ($getquiz <= 0) {
            $getquiz = 2;
        }

        mtrace('local_th_store_quiz: task started.');

        $params = [
            'quizname' => '%bai luyen tap%',
            'getquiz' => $getquiz,
        ];

        $coursefilter = '';
        if (!empty($coursestartfrom)) {
            $coursefilter = ' AND c.startdate > :course_startdate_from ';
            $params['course_startdate_from'] = $coursestartfrom;
        }

        $sql = "
            SELECT qa.*
              FROM {quiz_attempts} qa
              JOIN {quiz} q ON q.id = qa.quiz
              JOIN {course} c ON c.id = q.course
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

        $attempts = $DB->get_records_sql($sql, $params);

        $deletedcount = 0;
        $skipcount = 0;
        $errorcount = 0;
        $now = time();

        foreach ($attempts as $attempt) {
            if (empty($attempt->timefinish)) {
                $skipcount++;
                continue;
            }

            $age = $now - (int)$attempt->timefinish;
            if ($age <= $days * DAYSECS) {
                $skipcount++;
                continue;
            }

            $quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);

            if ($DB->record_exists('th_store_quiz', ['attempt' => $attempt->id])) {
                $skipcount++;
                continue;
            }

            $record = new \stdClass();
            $record->quiz = $attempt->quiz;
            $record->userid = $attempt->userid;
            $record->attempt = $attempt->id;

            $grade = quiz_rescale_grade($attempt->sumgrades, $quiz, false);
            $record->sumgrades = (string)quiz_format_grade($quiz, $grade);

            $transaction = $DB->start_delegated_transaction();
            try {
                $DB->insert_record('th_store_quiz', $record);
                quiz_delete_attempt($attempt->id, $quiz);
                $transaction->allow_commit();
                $deletedcount++;
            } catch (\Throwable $e) {
                $transaction->rollback($e);
                $errorcount++;
                mtrace('local_th_store_quiz error attempt ' . $attempt->id . ': ' . $e->getMessage());
            }
        }

        mtrace("local_th_store_quiz: deleted={$deletedcount}, skipped={$skipcount}, errors={$errorcount}");
    }
}