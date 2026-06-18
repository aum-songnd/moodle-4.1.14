<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_th_bulk_send_message_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023120800) {
        $table = new xmldb_table('th_bulk_send_message');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, 1);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2023120800, 'local', 'th_bulk_send_message');
    }

    if ($oldversion < 2026041100) {
        $table = new xmldb_table('th_bulk_send_message');

        $field1 = new xmldb_field('receiverusername', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'receiver');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('receiverstudentcode', XMLDB_TYPE_CHAR, '150', null, null, null, null, 'receiverusername');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        upgrade_plugin_savepoint(true, 2026041100, 'local', 'th_bulk_send_message');
    }

    return true;
}