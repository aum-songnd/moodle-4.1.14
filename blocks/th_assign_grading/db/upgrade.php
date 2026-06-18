<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_block_th_assign_grading_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2024082700) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('block_th_assign_grading');

        // Thêm trường assignment_time
        $field = new xmldb_field('assignment_time', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Thêm trường grading_date
        $field = new xmldb_field('grading_date', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        
    } else if ($oldversion < 2025010200) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('block_th_assign_grading');

        // Thêm trường confirm_date
        $field = new xmldb_field('confirm_date', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table2 = new xmldb_table('th_assign_turns');

        // Thêm trường assign_date
        $field = new xmldb_field('assign_date', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table2, $field)) {
            $dbman->add_field($table2, $field);
        }
    }else if ($oldversion < 2025081800) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('th_assign_turns');

        // Thêm cột used (NOT NULL, default 0), đặt sau 'unused' cho rõ ràng
        $field = new xmldb_field('used', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'unused');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Backfill dữ liệu: used = max(numberturns - unused, 0)
        // (GREATEST có trên MySQL và Postgres)
        $DB->execute("UPDATE {th_assign_turns} SET used = GREATEST(numberturns - unused, 0)");

        upgrade_block_savepoint(true, 2025081500, 'th_assign_grading');
    }
    return true;
}