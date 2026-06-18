<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_th_exam_monitor_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025071100) {

        $table = new xmldb_table('local_th_exam_monitor');

        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025071100, 'local', 'th_exam_monitor');
    }
    if ($oldversion < 2025071101) {

        $table = new xmldb_table('local_th_exam_monitor');
    
        $key = new xmldb_key('attempt_fk', XMLDB_KEY_FOREIGN, ['attemptid'], 'quiz_attempts', ['id']);
        $dbman->drop_key($table, $key);
    
        $field = new xmldb_field('attemptid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    
        upgrade_plugin_savepoint(true, 2025071101, 'local', 'th_exam_monitor');
    }
    if ($oldversion < 2025081100) {
        $table = new xmldb_table('local_th_exam_monitor');

        $key = new xmldb_key('quiz_fk', XMLDB_KEY_FOREIGN, ['quizid'], 'quiz', ['id']);
        $dbman->drop_key($table, $key);
        

        $field = new xmldb_field('quizid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    
        upgrade_plugin_savepoint(true, 2025081100, 'local', 'th_exam_monitor');
    }
    return true;
}
