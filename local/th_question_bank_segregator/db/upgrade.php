<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_th_question_bank_segregator_upgrade($oldversion) {
	global $CFG, $DB;
    
	$dbman = $DB->get_manager();
	if ($oldversion < 2023110901) {
		
		// if (!$dbman->table_exists('th_course_category_tree')) {

        //     $table = new xmldb_table('th_course_category_tree');

        //     $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        //     $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', null);
        //     $table->add_field('tegory_tree_json', XMLDB_TYPE_TEXT);
        //     $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20');

        //     $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        //     $dbman->create_table($table);
        // }

        if ($dbman->table_exists('th_course_category_tree')) {

            $table = new xmldb_table('th_course_category_tree');
            $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null);
            $dbman->rename_field($table, $field, 'course_id');
        }

	}

	return true;
}

?>