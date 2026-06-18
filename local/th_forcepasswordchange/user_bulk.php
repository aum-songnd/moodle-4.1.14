<?php


require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once($CFG->dirroot . '/local/th_forcepasswordchange/user_bulk_forms.php');
require_once($CFG->dirroot . '/local/th_forcepasswordchange/lib.php');

admin_externalpage_setup('th_forcepasswordchange');

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}

$context = context_system::instance();
$userbulkform = new user_bulk_form();

$config = get_config('local_th_forcepasswordchange');
$role_str = $config->roles;

$user = [];
if (!empty($role_str)) {
    $role_list = array();
    $role_list = explode(',', $role_str);

    foreach ($role_list as $key => $roleid) {

        $roleusers = get_role_users($roleid, $context);
        foreach ($roleusers as $key => $u) {
           $user[$key] = $key;
        }
    }
}

$userbulkform->set_data(array('users' => $user));

if ($data = $userbulkform->get_data()) {

    $SESSION->bulk_users = array();
    $users = $data->users;

    if (isset($data->send_mail)){
        $SESSION->th_bulk_users_send_mail = 1;
    } else{
        unset($SESSION->th_bulk_users_send_mail);
    }

    foreach ($data->users as $userid) {
        if ($userid == -1) {
            continue;
        }
        if (!isset($SESSION->bulk_users[$userid])) {
            $SESSION->bulk_users[$userid] = $userid;
        }
    }

    $bulkactions['forcepasswordchange'] = new action_link(
                new moodle_url('/local/th_forcepasswordchange/user_bulk_forcepasswordchange.php'),
                get_string('forcepasswordchange'));
    if (array_key_exists('forcepasswordchange', $bulkactions)) {
        redirect($bulkactions['forcepasswordchange']->url);
    }
}
echo $OUTPUT->header();
$userbulkform->display();
echo $OUTPUT->footer();
