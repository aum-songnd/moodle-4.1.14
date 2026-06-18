<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for mod_qaa.
 *
 * @package    mod_qaa
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
    
    return true;
}
