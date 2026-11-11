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
                    $submarks[$idx] = (float)$val;
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
                    if (!isset($submarks[$idx]) && isset($item['mark']) && $item['mark'] !== '' && $item['mark'] !== null) {
                        $submarks[$idx] = (float)$item['mark'];
                    }
                    if (!isset($subcomments[$idx]) && isset($item['comment'])) {
                        $subcomments[$idx] = (string)$item['comment'];
                    }
                }
            }
        }

        // Save into question_attempt_step_data
        foreach ($submarks as $idx => $markval) {
            self::save_step_data($step->id, "submark_$idx", (string)$markval);
        }
        foreach ($subcomments as $idx => $commentval) {
            self::save_step_data($step->id, "subcomment_$idx", $commentval);
            $fmt = $subcommentformats[$idx] ?? FORMAT_HTML;
            self::save_step_data($step->id, "subcommentformat_$idx", (string)$fmt);
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
