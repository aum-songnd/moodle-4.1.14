<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_th_export_quiz_batch_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025121600) {

        $table = new xmldb_table('local_th_export_quiz_batch');
        $field = new xmldb_field('quizid');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025121600, 'local', 'th_export_quiz_batch');
    }

    return true;
}
