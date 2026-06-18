<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for local_th_daily_grade_update.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_th_daily_grade_update_upgrade($oldversion) {
    global $DB, $CFG;

    // Đổi oldgrade -> DECIMAL(10,5)
    if ($oldversion < 2025112401) {

        $prefix = $CFG->prefix;
        $DB->execute("
            ALTER TABLE {$prefix}local_th_daily_grade_log
            MODIFY oldgrade DECIMAL(10,5) NULL DEFAULT 0.00000
        ");

        $DB->execute("
            ALTER TABLE {$prefix}local_th_daily_grade_log
            MODIFY newgrade DECIMAL(10,5) NULL DEFAULT 0.00000
        ");

        upgrade_plugin_savepoint(true, 2025112401, 'local', 'th_daily_grade_update');
    }

    return true;
}

