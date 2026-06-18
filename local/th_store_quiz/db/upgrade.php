<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_th_store_quiz_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024060800) {

        // Changing type of field instructorcustomparameters on table lti to text.
        $table = new xmldb_table('th_store_quiz');
        $field = new xmldb_field('sumgrades', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
                
        // Launch change of type for field value.
        $dbman->change_field_type($table, $field);
    }

    return true;
}
