<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_qaa_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // === Phiên bản upgrade mới ===
    if ($oldversion < 2025111701) {
        $table = new xmldb_table('qaa');
        $field = new xmldb_field('question_close_time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'grade');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint.
        upgrade_mod_savepoint(true, 2025111701, 'qaa');
    }

    if ($oldversion < 2026071700) {
        $table = new xmldb_table('qaapairs');
        $field_status = new xmldb_field('status', XMLDB_TYPE_INTEGER, '2');
        $field_message = new xmldb_field('message', XMLDB_TYPE_TEXT);

        if (!$dbman->field_exists($table, $field_status)) {
            $dbman->add_field($table, $field_status);
        }

        if (!$dbman->field_exists($table, $field_message)) {
            $dbman->add_field($table, $field_message);
        }

        upgrade_mod_savepoint(true, 2026071700, 'qaa');
    }
    return true;
}
