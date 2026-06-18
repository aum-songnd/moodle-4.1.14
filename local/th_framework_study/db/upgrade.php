<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for local_th_framework_study
 *
 * @param int $oldversion
 */
function xmldb_local_th_framework_study_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025122201) {
        // Cập nhật cột training_type_code

        // Thêm cột training_type_code vào bảng th_framework_study
        $table = new xmldb_table('th_framework_study');
        $field = new xmldb_field('training_type_code', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'target_code');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $table = new xmldb_table('th_subject_code_mapping');

        // Nếu bảng chưa tồn tại thì tạo
        if (!$dbman->table_exists($table)) {

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('current_code', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL);
            $table->add_field('old_code', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('uq_subject_code', XMLDB_KEY_UNIQUE, ['current_code', 'old_code']);

            $dbman->create_table($table);
        }

        // Savepoint upgrade
        upgrade_plugin_savepoint(true, 2025122201, 'local', 'th_framework_study');
    }

    // Allow credit to be NULL (for non-credit subjects)
    if ($oldversion < 2026012600) {

        $table = new xmldb_table('th_framework_study');
        $field = new xmldb_field('credit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'subject_code');

        if ($dbman->field_exists($table, $field)) {
            // Remove NOT NULL constraint
            $dbman->change_field_notnull($table, $field);
        }

        // Savepoint
        upgrade_plugin_savepoint(true, 2026012600, 'local', 'th_framework_study');
    }
    
    return true;
}
