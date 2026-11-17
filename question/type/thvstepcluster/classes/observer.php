<?php

defined('MOODLE_INTERNAL') || die();

class qtype_thvstepcluster_observer {

    public static function update_child_question_cluster($event) {

        global $DB, $CFG;

        $question_entries_id = $DB->get_field('qtype_thvstepcluster', 'question_entries_id', array('id' => $event->objectid));

        $question_entries_id = json_decode($question_entries_id);

        $question_entry_items = $question_entries_id->question_entry_items;
        $questionids = (array) $question_entries_id->questionids;
        $questionids_new = [];
        $question_entry_items_new = [];

        $contextid = $DB->get_field('context', 'id', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $event->courseid));
        foreach ($questionids as $key => $question) {
            $sql = "SELECT qv.*
                FROM {question_versions} qv
                JOIN {question} q ON q.id = qv.questionid
                JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid 
                WHERE q.id = (SELECT ti.itemid 
                            FROM {tag} t
                            JOIN {tag_instance} ti ON t.id = ti.tagid
                            WHERE ti.component='core_question' AND ti.itemtype='question' 
                            AND ti.contextid = :contextid AND t.name= :name LIMIT 1)
                AND qbe.questioncategoryid
                IN (SELECT qc.id 
                    FROM {context} ctx
                    JOIN {question_categories} qc ON qc.contextid = ctx.id
                    JOIN {course} c ON c.id = ctx.instanceid
                    WHERE ctx.contextlevel = :contextlevel AND c.id = :courseid)
                ORDER BY qv.id DESC
                LIMIT 1";
            $params = array(
                'itemid' => $question->questionid, 
                'contextlevel' => CONTEXT_COURSE,
                'courseid' => $event->courseid,
                'name' => 'id_' . $question->questionid,
                'contextid' => $contextid
            );

            $record = $DB->get_record_sql($sql, $params);

            if ($record) {
                $questionids_new[$record->questionbankentryid] = array('questionid'=> $record->questionid, 'sequence' => $question->sequence);
                $question_entry_items_new[] = $record->questionbankentryid;

                // core_tag_tag::remove_item_tag('core_question', 'question', $record->questionid, 'id_'.$question->questionid, 0);

            } else {
                $questionids_new[$key] = array('questionid'=> $question->questionid, 'sequence' => $question->sequence);
                $question_entry_items_new[] = $key;
            }
        }

            $data = array(
                'question_entry_items' => $question_entry_items_new, 
                'questionids' => $questionids_new
            );

            $sql = "UPDATE {qtype_thvstepcluster}
                    SET question_entries_id = :data
                    WHERE id = :id";
            $params = array('data' => json_encode($data), 'id' => $event->objectid);

            $DB->execute($sql, $params);
            question_bank::notify_question_edited($event->objectid);

    }

    // public static function question_update_idnumber($event) {

    //     global $DB, $CFG;

    //     $question_entries_id = $DB->get_field('qtype_thvstepcluster', 'question_entries_id', array('id' => $event->objectid));

    //     $question_entries_id = json_decode($question_entries_id);

    //     $question_entry_items = $question_entries_id->question_entry_items;
    //     $questionids = (array) $question_entries_id->questionids;
    //     $questionids_new = [];
    //     $question_entry_items_new = [];

    //     foreach ($questionids as $key => $question) {
    //         $sql = "SELECT qv.*
    //             FROM {question_versions} qv
    //             JOIN {question} q ON q.id = qv.questionid
    //             JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid 
    //             WHERE qbe.idnumber = :idnumber 
    //             AND qbe.questioncategoryid 
    //             IN (SELECT qc.id 
    //                 FROM {context} ctx
    //                 JOIN {question_categories} qc ON qc.contextid = ctx.id
    //                 JOIN {course} c ON c.id = ctx.instanceid
    //                 WHERE ctx.contextlevel = :contextlevel AND c.id = :courseid)
    //             ORDER BY qv.id DESC
    //             LIMIT 1";
    //         $params = array(
    //             'idnumber' => $question->questionid, 
    //             'contextlevel' => CONTEXT_COURSE,
    //             'courseid' => $event->courseid,
    //         );

    //         $record = $DB->get_record_sql($sql, $params);

    //         // print_object($question_entry_items);
    //         // print_object($questionids);
    //         // print_object('$record');
    //         // print_object($record);
    //         // exit;
    //         if ($record) {
    //             $questionids_new[$record->questionbankentryid] = array('questionid'=> $record->questionid, 'sequence' => $question->sequence);
    //             $question_entry_items_new[] = $record->questionbankentryid;

    //             $sql = "UPDATE {question_bank_entries}
    //                     SET idnumber = :questionid1
    //                     WHERE id = (SELECT qbe.id
    //                     FROM {question_versions} qv
    //                     JOIN {question} q ON q.id = qv.questionid
    //                     JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
    //                     WHERE q.id = :questionid2)";
    //             $params = array('questionid1' => $record->questionid, 'questionid2' => $record->questionid);

    //             $DB->execute($sql, $params);
    //             // Purge this question from the cache.
    //             question_bank::notify_question_edited($record->questionid);
    //         } else {
    //             $questionids_new[$key] = array('questionid'=> $question->questionid, 'sequence' => $question->sequence);
    //             $question_entry_items_new[] = $key;

    //             $sql = "UPDATE {question_bank_entries}
    //                     SET idnumber = :questionid
    //                     WHERE id = $key";
    //             $params = array('questionid' => $question->questionid);

    //             $DB->execute($sql, $params);
    //             question_bank::notify_question_edited($question->questionid);
    //         }
    //     }

    //     // if (!empty($questionids_new) and !empty($question_entry_items_new)) {
    //         $data = array(
    //             'question_entry_items' => $question_entry_items_new, 
    //             'questionids' => $questionids_new
    //         );
    //         // print_object('$data');
    //         // print_object($data);
    //         // exit;
    //         $sql = "UPDATE {qtype_thvstepcluster}
    //                 SET question_entries_id = :data
    //                 WHERE id = :id";
    //         $params = array('data' => json_encode($data), 'id' => $event->objectid);

    //         $DB->execute($sql, $params);
    //         question_bank::notify_question_edited($event->objectid);
    //     // }


        
        
    //     // mtrace('th_cluster_question_updated_start');

    //     // $questionid = $event->objectid;
    //     // $sql = "UPDATE {question_bank_entries}
    //     //         SET idnumber = :questionid1
    //     //         WHERE id = (SELECT qbe.id
    //     //         FROM {question_versions} qv
    //     //         JOIN {question} q ON q.id = qv.questionid
    //     //         JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
    //     //         WHERE q.id = :questionid2)";
    //     // $params = array('questionid1' => $questionid, 'questionid2' => $questionid);

    //     // $DB->execute($sql, $params);

    // }

    /**
     * Listener for question_manually_graded event to persist submarks and subcomments.
     *
     * @param \core\event\base $event
     */
    public static function question_manually_graded($event) {
        global $DB;

        $questionid = $event->objectid ?? null;
        if (empty($questionid)) {
            return;
        }

        $qtype = $DB->get_field('question', 'qtype', ['id' => $questionid]);
        if ($qtype !== 'thvstepcluster') {
            return;
        }

        $slot = $event->other['slot'] ?? null;
        $attemptid = $event->other['attemptid'] ?? null;
        if (empty($slot) || empty($attemptid)) {
            return;
        }

        $is_thquiz = (strpos(get_class($event), 'mod_thquiz') !== false) || !empty($event->other['thquizid']);
        if ($is_thquiz) {
            $uniqueid = $DB->get_field('thquiz_attempts', 'uniqueid', ['id' => $attemptid]);
        } else {
            $uniqueid = $DB->get_field('quiz_attempts', 'uniqueid', ['id' => $attemptid]);
        }

        if (empty($uniqueid)) {
            return;
        }

        $qa = $DB->get_record('question_attempts', ['questionusageid' => $uniqueid, 'slot' => $slot]);
        if (empty($qa)) {
            return;
        }

        $step = $DB->get_record_sql(
            'SELECT id FROM {question_attempt_steps} WHERE questionattemptid = ? ORDER BY sequencenumber DESC, id DESC LIMIT 1',
            [$qa->id]
        );
        if (empty($step)) {
            return;
        }

        $postdata = !empty($_POST) ? $_POST : [];
        $submarks = [];
        $subcomments = [];
        $subcommentformats = [];

        foreach ($postdata as $key => $val) {
            if (preg_match('/submark_(\d+)$/', $key, $m)) {
                $idx = (int)$m[1];
                if ($val !== '' && $val !== null) {
                    $submarks[$idx] = (float)str_replace(',', '.', (string)$val);
                } else {
                    $submarks[$idx] = null;
                }
            } else if (preg_match('/subcomment_(\d+)$/', $key, $m)) {
                $idx = (int)$m[1];
                $subcomments[$idx] = (string)$val;
            } else if (preg_match('/subcommentformat_(\d+)$/', $key, $m)) {
                $idx = (int)$m[1];
                $subcommentformats[$idx] = (int)$val;
            }
        }

        // Also check if step has comment with JSON metadata
        $existingcomment = $DB->get_field('question_attempt_step_data', 'value', [
            'attemptstepid' => $step->id,
            'name' => '-comment'
        ]);
        if ($existingcomment && preg_match('/<!--TH_CLUSTER_SUBGRADES:(.*?)-->/s', $existingcomment, $m)) {
            $subdata = json_decode($m[1], true);
            if (is_array($subdata)) {
                foreach ($subdata as $idx => $item) {
                    $idx = (int)$idx;
                    if (!array_key_exists($idx, $submarks) && isset($item['mark']) && $item['mark'] !== '' && $item['mark'] !== null) {
                        $submarks[$idx] = (float)$item['mark'];
                    }
                    if (!array_key_exists($idx, $subcomments) && isset($item['comment'])) {
                        $subcomments[$idx] = (string)$item['comment'];
                    }
                }
            }
        }

        // Save into question_attempt_step_data
        foreach ($submarks as $idx => $markval) {
            self::save_step_data($step->id, "submark_$idx", ($markval !== null && $markval !== '') ? (string)$markval : '');
        }
        foreach ($subcomments as $idx => $commentval) {
            self::save_step_data($step->id, "subcomment_$idx", $commentval);
            $fmt = $subcommentformats[$idx] ?? FORMAT_HTML;
            self::save_step_data($step->id, "subcommentformat_$idx", (string)$fmt);
        }

        // Enforce cluster grading completeness:
        // If there are manual subquestions and NOT all of them are graded yet,
        // the attempt step and question attempt state must remain needsgrading,
        // and the total mark / fraction must be null (not finalized).
        try {
            global $CFG;
            require_once($CFG->libdir . '/questionlib.php');
            $question = question_bank::load_question($questionid);
            if ($question && !empty($question->question_items_instance)) {
                $has_manual = false;
                $all_manual_graded = true;
                $quba = question_engine::load_questions_usage_by_activity($uniqueid);
                $qa_obj = $quba->get_question_attempt($slot);

                foreach ($question->question_items_instance as $idx => $subq) {
                    if ($subq === null || $subq->get_type_name() === 'description') {
                        continue;
                    }
                    $type = $subq->get_type_name();
                    if ($type === 'recordrtc' || $type === 'essay') {
                        // If student did not answer this subquestion, default to 0 and do not require manual grading.
                        if (!$question->has_subquestion_response($qa_obj, $idx)) {
                            if (!isset($submarks[$idx])) {
                                $submarks[$idx] = 0.0;
                                self::save_step_data($step->id, "submark_$idx", "0");
                            }
                            continue;
                        }

                        $has_manual = true;
                        if (!isset($submarks[$idx]) || $submarks[$idx] === '' || $submarks[$idx] === null) {
                            $all_manual_graded = false;
                        }
                    }
                }

                $table = $is_thquiz ? 'thquiz_attempts' : 'quiz_attempts';

                if ($has_manual && !$all_manual_graded) {
                    // Still waiting for remaining answered subquestions to be graded.
                    $DB->set_field('question_attempt_steps', 'state', 'needsgrading', ['id' => $step->id]);
                    $DB->set_field('question_attempt_steps', 'fraction', null, ['id' => $step->id]);
                    self::save_step_data($step->id, '-mark', '');
                    $DB->set_field($table, 'sumgrades', null, ['id' => $attemptid]);

                    $attemptrec = $DB->get_record($table, ['id' => $attemptid]);
                    if ($attemptrec) {
                        global $CFG;
                        if ($is_thquiz) {
                            require_once($CFG->dirroot . '/mod/thquiz/locallib.php');
                            $quiz = $DB->get_record('thquiz', ['id' => $attemptrec->thquiz]);
                            if ($quiz) {
                                thquiz_save_best_grade($quiz, $attemptrec->userid);
                            }
                        } else {
                            require_once($CFG->dirroot . '/mod/quiz/locallib.php');
                            $quiz = $DB->get_record('quiz', ['id' => $attemptrec->quiz]);
                            if ($quiz) {
                                quiz_save_best_grade($quiz, $attemptrec->userid);
                            }
                        }
                    }
                } else if ($has_manual && $all_manual_graded) {
                    // All answered manual subquestions are graded. Ensure total fraction and sumgrades are set.
                    $total_mark = 0;
                    $max_mark = (float)$question->defaultmark;

                    foreach ($question->question_items_instance as $idx => $subq) {
                        if ($subq === null || $subq->get_type_name() === 'description') {
                            continue;
                        }
                        $type = $subq->get_type_name();
                        if ($type === 'recordrtc' || $type === 'essay') {
                            $total_mark += (float)($submarks[$idx] ?? 0);
                        } else {
                            $subfrac = $question->get_subquestion_fraction($qa_obj, $idx);
                            if ($subfrac !== null) {
                                $total_mark += $subfrac * (float)$subq->defaultmark;
                            }
                        }
                    }

                    $frac = $max_mark > 0 ? min(max(0.0, $total_mark / $max_mark), 1.0) : 0.0;
                    $state = question_state::manually_graded_state_for_fraction($frac);

                    $DB->set_field('question_attempt_steps', 'state', (string)$state, ['id' => $step->id]);
                    $DB->set_field('question_attempt_steps', 'fraction', $frac, ['id' => $step->id]);
                    self::save_step_data($step->id, '-mark', format_float($total_mark, 2));

                    $quba = question_engine::load_questions_usage_by_activity($uniqueid);
                    $tot = $quba->get_total_mark();
                    $DB->set_field($table, 'sumgrades', $tot, ['id' => $attemptid]);

                    $attemptrec = $DB->get_record($table, ['id' => $attemptid]);
                    if ($attemptrec) {
                        global $CFG;
                        if ($is_thquiz) {
                            require_once($CFG->dirroot . '/mod/thquiz/locallib.php');
                            $quiz = $DB->get_record('thquiz', ['id' => $attemptrec->thquiz]);
                            if ($quiz) {
                                thquiz_save_best_grade($quiz, $attemptrec->userid);
                            }
                        } else {
                            require_once($CFG->dirroot . '/mod/quiz/locallib.php');
                            $quiz = $DB->get_record('quiz', ['id' => $attemptrec->quiz]);
                            if ($quiz) {
                                quiz_save_best_grade($quiz, $attemptrec->userid);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            debugging('Error checking cluster manual grading status: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    protected static function save_step_data($stepid, $name, $value) {
        global $DB;
        $rec = $DB->get_record('question_attempt_step_data', ['attemptstepid' => $stepid, 'name' => $name]);
        if ($rec) {
            $rec->value = $value;
            $DB->update_record('question_attempt_step_data', $rec);
        } else {
            $newrec = new stdClass();
            $newrec->attemptstepid = $stepid;
            $newrec->name = $name;
            $newrec->value = $value;
            $DB->insert_record('question_attempt_step_data', $newrec);
        }
    }
}
