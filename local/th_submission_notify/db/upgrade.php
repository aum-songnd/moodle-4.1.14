<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_th_submission_notify_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    
    if ($oldversion < 2025051500) {

        $table = new xmldb_table('local_th_sub_result_logs');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ess', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null);
        $table->add_field('rec', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null);
        $table->add_field('reading', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null);
        $table->add_field('listening', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null);
        $table->add_field('average', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'pending');
        $table->add_field('timesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        
        upgrade_plugin_savepoint(true, 2025051500, 'local', 'th_submission_notify');
    } else if ($oldversion < 2025052200) {
        $table = new xmldb_table('local_th_sub_result_logs');

        // Thêm trường summaryhash
        $field = new xmldb_field('summaryhash', XMLDB_TYPE_CHAR, '32', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $index = new xmldb_index('summaryhash_idx', XMLDB_INDEX_UNIQUE, ['summaryhash']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2025052200, 'local', 'th_submission_notify');
    }

    return true;
}
