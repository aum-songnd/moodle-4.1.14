<?php

defined('MOODLE_INTERNAL') || die();

class local_th_shuffle_answers_observer {

    public static function updated($event) {

        global $DB, $CFG;

        $use_config = get_config('local_th_shuffle_answers', 'shuffleanswers');
        if ($use_config) {
            $questionid = $event->objectid;

            // $question_type = array('ddwtos', 'gapselect', 'match', 'multichoice');
            $qtype = $DB->get_field('question', 'qtype', array('id' => $questionid));
            if ($qtype == 'multichoice') {
                $shuffleanswers = $DB->get_field('qtype_multichoice_options', 'shuffleanswers', array('questionid' => $questionid));
                if ($shuffleanswers == 0) {
                    $DB->set_field('qtype_multichoice_options', 'shuffleanswers', 1, array('questionid' => $questionid));
                }

            } else if ($qtype == 'match') {
                $shuffleanswers = $DB->get_field('qtype_match_options', 'shuffleanswers', array('questionid' => $questionid));
                if ($shuffleanswers == 0) {
                    $DB->set_field('qtype_match_options', 'shuffleanswers', 1, array('questionid' => $questionid));
                }

            } else if ($qtype == 'gapselect') {
                $shuffleanswers = $DB->get_field('question_gapselect', 'shuffleanswers', array('questionid' => $questionid));
                if ($shuffleanswers == 0) {
                    $DB->set_field('question_gapselect', 'shuffleanswers', 1, array('questionid' => $questionid));
                }

            } else if ($qtype == 'ddwtos') {
                
                $shuffleanswers = $DB->get_field('question_ddwtos', 'shuffleanswers', array('questionid' => $questionid));
                if ($shuffleanswers == 0) {
                    $DB->set_field('question_ddwtos', 'shuffleanswers', 1, array('questionid' => $questionid));
                }
            } else if ($qtype == 'thddwtos') {
                
                $shuffleanswers = $DB->get_field('question_thddwtos', 'shuffleanswers', array('questionid' => $questionid));
                if ($shuffleanswers == 0) {
                    $DB->set_field('question_thddwtos', 'shuffleanswers', 1, array('questionid' => $questionid));
                }
            }
        }
    }
}
