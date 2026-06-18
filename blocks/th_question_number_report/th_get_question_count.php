<?php
function th_get_question_count($courseid) {
    global $DB, $CFG;
    $version = substr($CFG->release, 0, 1);
	if ($version == 3) {
        $count = $DB->get_field_sql("
            SELECT COUNT(q.id)
            FROM {question_categories} as qc, {question} as q, {context} as c 
            WHERE c.instanceid = :courseid
                AND c.id = qc.contextid
                AND qc.id = q.category
                AND q.parent = '0'
                AND q.hidden = '0'", ['courseid' => $courseid]);
    } elseif ($version == 4) {
        $count = $DB->get_field_sql("
            SELECT COUNT(1)
            FROM {question} q
            JOIN {question_versions} qv ON qv.questionid = q.id
            JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
            JOIN {question_categories} as qc ON qbe.questioncategoryid = qc.id
            JOIN {context} as c ON qc.contextid = c.id
            WHERE q.parent = '0' AND qv.version = 1 AND (qv.status = 'ready' OR qv.status = 'draft') AND c.instanceid = $courseid
            ");
    } else {
        $count = 0; 
    }
    
    return $count;
}
?>




