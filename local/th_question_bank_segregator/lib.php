<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Adds menu item into question bank on front page.
 *
 * @param navigation_node $coursenode navigation node object
 * @param stdClass $course frontpage course object
 * @param context $context frontpage course context object
 */
function local_th_question_bank_segregator_extend_navigation_frontpage(navigation_node $coursenode, stdClass $course,
        context $context) {
    local_th_question_bank_segregator_extend_navigation_course($coursenode, $course, $context);
}

/**
 * Adds menu item into question bank on course page.
 *
 * @param navigation_node $coursenode navigation node object
 * @param stdClass $course course object
 * @param context $context course context object
 */
function local_th_question_bank_segregator_extend_navigation_course(navigation_node $coursenode, stdClass $course,
        context $context) {
    if (!has_capability('moodle/question:editall', $context)
        && !has_capability('moodle/question:editmine', $context)
    ) {
        return;
    }
    /** @var navigation_node|null $questionbank */
    $questionbank = null;
    foreach ($coursenode->children as $node) {
        if ($node->text == get_string('questionbank', 'question')) {
            $questionbank = $node;
            break;
        }
    }
    if (!$questionbank) {
        return;
    }

    $url = new moodle_url('/local/th_question_bank_segregator/view.php', array('courseid' => $context->instanceid));
    $questionbank->add(
        get_string('th_question_bank_segregator', 'local_th_question_bank_segregator'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'th_question_bank_segregator'
    );    
}


?>