<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

   $ADMIN->add('security', new admin_externalpage('th_forcepasswordchange',
        get_string('pluginname', 'local_th_forcepasswordchange'), "$CFG->wwwroot/local/th_forcepasswordchange/user_bulk.php"));

   $settings = new admin_settingpage('local_th_forcepasswordchange', get_string('pluginname', 'local_th_forcepasswordchange'));

   $settings->add(new admin_setting_heading('local_th_forcepasswordchange/heading', get_string('heading_sendmail', 'local_th_forcepasswordchange'), ''));

   $settings->add(new admin_setting_configcheckbox('local_th_forcepasswordchange/send_mail',
            get_string('cron_send_mail', 'local_th_forcepasswordchange'),
            get_string('cron_send_mail_desc', 'local_th_forcepasswordchange'), 1));

   global $DB;

   $sql = "SELECT r.id,shortname FROM {role_context_levels} rcl
            JOIN {role} r ON rcl.roleid = r.id
            WHERE rcl.contextlevel = 10";
   $data = $DB->get_records_sql($sql);
   $role = array();
   foreach ($data as $key => $item) {
      $role[$key] = $item->shortname;
   }
   $settings->add(new admin_setting_heading('local_th_forcepasswordchange/heading2', get_string('heading_selectrole', 'local_th_forcepasswordchange'), ''));
   $settings->add(new admin_setting_configmulticheckbox('local_th_forcepasswordchange/roles',
            get_string('selectrole', 'local_th_forcepasswordchange'),
            get_string('selectrole_desc', 'local_th_forcepasswordchange'), array('1' => 1), 
             $role));
   $settings->add(new admin_setting_heading('local_th_forcepasswordchange/heading3', get_string('heading_tokenduration', 'local_th_forcepasswordchange'), ''));
   $settings->add(new admin_setting_configduration('local_th_forcepasswordchange/pwresettime',
        new lang_string('tokenduration', 'local_th_forcepasswordchange'),
        new lang_string('tokenduration_desc', 'local_th_forcepasswordchange'), 1 * WEEKSECS, WEEKSECS));
    // Add the category to the admin tree.
   $ADMIN->add('localplugins', $settings);
}
?>