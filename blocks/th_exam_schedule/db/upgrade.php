<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_block_th_exam_schedule_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026011501) { // <-- tăng version plugin lên 2026010801 trong version.php
        $table = new xmldb_table('block_thexamschedule_exam');

        // Drop index idx_userid if exists
        $index = new xmldb_index('idx_userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Drop field userid if exists
        $field = new xmldb_field('userid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Drop unique index (studentcode, coursecode) if exists
        $uniq = new xmldb_index('uniq_student_course', XMLDB_INDEX_UNIQUE, ['studentcode', 'coursecode']);
        if ($dbman->index_exists($table, $uniq)) {
            $dbman->drop_index($table, $uniq);
        }

        // Add composite non-unique index for (studentcode, coursecode, examdate, examslot)
        $idx = new xmldb_index('idx_sc_cd_date_slot', XMLDB_INDEX_NOTUNIQUE, ['studentcode','coursecode','examdate','examslot']);
        if (!$dbman->index_exists($table, $idx)) {
            $dbman->add_index($table, $idx);
        }

        upgrade_block_savepoint(true, 2026011501, 'th_exam_schedule');
    }

    return true;
}
