<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_th_register_upgrade($oldversion) {

  global $CFG, $DB;

  if ($oldversion < 2023070600) {

      $dbman = $DB->get_manager();
      $table = new xmldb_table('th_nganh_hoc');
      $field = new xmldb_field('hoc_phi_mot_tin_chi', XMLDB_TYPE_INTEGER, '10');
      if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
      }

      $table = new xmldb_table('th_dot_mo_mon');
      $field = new xmldb_field('hoc_phi_mot_tin_chi', XMLDB_TYPE_INTEGER, '10', null, true);
      if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
      }
  }

  return true;
}
