<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_th_qaa_manager_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026072100) {
        // Define field reviewerid to be added to local_th_qaa_ticket.
        $table = new xmldb_table('local_th_qaa_ticket');
        $field = new xmldb_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'rejectreason');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Upgrade savepoint.
        upgrade_plugin_savepoint(true, 2026072100, 'local', 'th_qaa_manager');
    }

    if ($oldversion < 2026072200) {
        // Define table local_th_qaa_assign_history to be created.
        $table = new xmldb_table('local_th_qaa_assign_history');

        // Adding fields to table local_th_qaa_assign_history.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ticketid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assignedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeassigned', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timerecalled', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('recalltype', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);

        // Adding keys to table local_th_qaa_assign_history.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_th_qaa_assign_history.
        $table->add_index('ticketid_ix', XMLDB_INDEX_NOTUNIQUE, array('ticketid'));
        $table->add_index('userid_ix', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch create table.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        // Migrate existing ticket assignees to the history table.
        $sql = "SELECT id, assigneduserid, timemodified, status 
                  FROM {local_th_qaa_ticket} 
                 WHERE assigneduserid IS NOT NULL AND assigneduserid > 0";
        $existingtickets = $DB->get_records_sql($sql);
        foreach ($existingtickets as $t) {
            $timerecalled = null;
            $recalltype = 0;
            $DB->insert_record('local_th_qaa_assign_history', (object)[
                'ticketid' => $t->id,
                'userid' => $t->assigneduserid,
                'assignedby' => 0,
                'timeassigned' => $t->timemodified,
                'timerecalled' => $timerecalled,
                'recalltype' => $recalltype,
            ]);
        }

        // Upgrade savepoint.
        upgrade_plugin_savepoint(true, 2026072200, 'local', 'th_qaa_manager');
    }

    if ($oldversion < 2026072201) {
        $table = new xmldb_table('local_th_qaa_ticket');
        
        $index_old = new xmldb_index('qaapairid_uix', XMLDB_INDEX_UNIQUE, array('qaapairid'));
        if ($dbman->index_exists($table, $index_old)) {
            $dbman->drop_index($table, $index_old);
        }

        $index_new = new xmldb_index('qaapairid_ix', XMLDB_INDEX_NOTUNIQUE, array('qaapairid'));
        if (!$dbman->index_exists($table, $index_new)) {
            $dbman->add_index($table, $index_new);
        }

        upgrade_plugin_savepoint(true, 2026072201, 'local', 'th_qaa_manager');
    }

    return true;
}
