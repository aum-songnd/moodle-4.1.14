<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for th_online_exam_manager
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool
 */
function xmldb_local_th_online_exam_manager_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Example: version 2025041401
    if ($oldversion < 2025050600) {

        // Define table th_exam_regrade_requests to be created
        $table = new xmldb_table('th_exam_regrade_requests');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('reason', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
        $table->add_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timerequested', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeprocessed', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Upgrade savepoint
        upgrade_plugin_savepoint(true, 2025050600, 'local', 'th_online_exam_manager');
    }

    if ($oldversion < 2025071400) {

        $table = new xmldb_table('th_exam_regrade_requests');

        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'reason');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025071400, 'local', 'th_online_exam_manager');
    }

    if ($oldversion < 2025071401) {

        $table = new xmldb_table('th_exam_regrade_requests');

        $field = new xmldb_field('note', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'reason');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025071401, 'local', 'th_online_exam_manager');
    }

    if ($oldversion < 2025072500) {

        // Define table th_exam_regrade_requests to be created
        $table = new xmldb_table('th_exam_regrade_assign');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timerequested', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Upgrade savepoint
        upgrade_plugin_savepoint(true, 2025072500, 'local', 'th_online_exam_manager');
    }

    if ($oldversion < 2025110608) {
        global $DB, $CFG;
        $prefix = $CFG->prefix;
        $DB->execute("ALTER TABLE {$prefix}th_exam_regrade_requests
                        MODIFY grade DECIMAL(10,5) NOT NULL DEFAULT 0.00000");
    
        // local plugin
        upgrade_plugin_savepoint(true, 2025110608, 'local', 'th_online_exam_manager');
    }

    return true;
}
