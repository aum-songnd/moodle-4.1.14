<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_block_th_bulk_enrol_student_upgrade($oldversion) {
	global $CFG, $DB;

	$dbman = $DB->get_manager();
	if ($oldversion < 2023100901) {
		
		if (!$dbman->table_exists('th_log_bulk_enrol_student')) {

            $table = new xmldb_table('th_log_bulk_enrol_student');

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null);
            $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', null);
            $table->add_field('notifications', XMLDB_TYPE_TEXT);
            $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('ma_ghi_danh', XMLDB_TYPE_CHAR, '200');
            $table->add_field('nguoi_tao', XMLDB_TYPE_INTEGER, '10');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10');

            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);
        }
	}

	return true;
}

?>