<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_th_feedback_report_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024091600) {

        // Define field os_name to be added to local_th_feedback_report.
        $table = new xmldb_table('local_th_feedback_report');
        $field = new xmldb_field('os_name', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'timemodified');

        // Conditionally launch add field os_name.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field browser_name to be added to local_th_feedback_report.
        $field = new xmldb_field('browser_name', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'os_name');

        // Conditionally launch add field browser_name.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // th_feedback_report savepoint reached.
        upgrade_plugin_savepoint(true, 2024091600, 'local', 'th_feedback_report');
    }

    if ($oldversion < 2024112800) {
        // Define field type to be added to local_th_feedback_report.
        $table = new xmldb_table('local_th_feedback_report');
        $field = new xmldb_field('type', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'browser_name');

        // Conditionally launch add field type.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // th_feedback_report savepoint reached.
        upgrade_plugin_savepoint(true, 2024112800, 'local', 'th_feedback_report');
    }

    if ($oldversion < 2024112801) {

        // Define table th_error_categories to be created.
        $table = new xmldb_table('th_error_categories');

        // Add fields to the table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Add keys to the table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for th_error_categories.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2024112801, 'local', 'th_feedback_report');
    }

    if ($oldversion < 2024112801) {

        // Define table th_error_categories to be created.
        $table = new xmldb_table('th_error_categories');

        // Add fields to the table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Add keys to the table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for th_error_categories.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2024112801, 'local', 'th_feedback_report');
    }

    if ($oldversion < 2024122600) {
        // Define field type to be added to local_th_feedback_report.
        $table = new xmldb_table('th_error_categories');
        $field = new xmldb_field('code', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'description');

        // Conditionally launch add field type.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // th_feedback_report savepoint reached.
        upgrade_plugin_savepoint(true, 2024122600, 'local', 'th_feedback_report');
    }

    if ($oldversion < 2026050600) {
        $table = new xmldb_table('local_th_feedback_report');
        $field = new xmldb_field(
            'response',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'type'
        );
    
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        upgrade_plugin_savepoint(true, 2026050600, 'local', 'th_feedback_report');
    }

    if ($oldversion < 2026051900) {
        $table = new xmldb_table('th_feedback_responses');
    
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('response', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    
        upgrade_plugin_savepoint(true, 2026051900, 'local', 'th_feedback_report');
    }

    if ($oldversion < 2026052700) {
        $table = new xmldb_table('th_feedback_responses');
    
        $field = new xmldb_field(
            'status',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            null,
            null,
            null,
            'userid'
        );
    
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        upgrade_plugin_savepoint(true, 2026052700, 'local', 'th_feedback_report');
    }

    return true;
}