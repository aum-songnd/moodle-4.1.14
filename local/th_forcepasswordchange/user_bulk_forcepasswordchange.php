<?php
/**
* script for bulk user force password change
*/

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir .'/adminlib.php');
require_once($CFG->dirroot .'/report/usersessions/locallib.php');
require_once($CFG->dirroot .'/webservice/lib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('th_forcepasswordchange');
require_capability('moodle/user:update', context_system::instance());

$return = $CFG->wwwroot . '/local/th_forcepasswordchange/user_bulk.php';

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

// print_object($SESSION->bulk_users);

echo $OUTPUT->header();
if ($confirm and confirm_sesskey()) {
    // only force password change if user may actually change the password
    $authsavailable = get_enabled_auth_plugins();
    $changeable = array();

    foreach($authsavailable as $authplugin) {
        if (!$auth = get_auth_plugin($authplugin)) {
            continue;
        }
        if ($auth->is_internal() and $auth->can_change_password()) {
            $changeable[$authplugin] = true;
        }
    }

    $parts = array_chunk($SESSION->bulk_users, 300);
    foreach ($parts as $users) {
        list($in, $params) = $DB->get_in_or_equal($users);
        $rs = $DB->get_recordset_select('user', "id $in", $params);
        foreach ($rs as $user) {
            if (!empty($changeable[$user->auth])) {

                if (isset($SESSION->th_bulk_users_send_mail)) {
                    
                    $newpassword = generate_password(20);
                    update_internal_user_password($user, $newpassword);
                   
                    \core\session\manager::kill_user_sessions($user->id);
                    webservice::delete_user_ws_tokens($user->id);
                    local_th_forcepasswordchange_send_mail($user);

                } else {
                    set_user_preference('auth_forcepasswordchange', 1, $user->id);
                }
                unset($SESSION->bulk_users[$user->id]);
            } else {
                echo $OUTPUT->notification(get_string('forcepasswordchangenot', '', fullname($user, true)));
            }
        }
        unset($SESSION->th_bulk_users_send_mail);
        $rs->close();
    }
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);

} else {
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $userlist = $DB->get_records_select_menu('user', "id $in", $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname', 0, MAX_BULK_USERS);
    $usernames = implode(', ', $userlist);
    if (count($SESSION->bulk_users) > MAX_BULK_USERS) {
        $usernames .= ', ...';
    }
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcontinue = new single_button(new moodle_url('/local/th_forcepasswordchange/user_bulk_forcepasswordchange.php', array('confirm' => 1)), get_string('yes'));
    $formcancel = new single_button(new moodle_url('/local/th_forcepasswordchange/user_bulk.php'), get_string('no'), 'get');
    echo $OUTPUT->confirm(get_string('forcepasswordchangecheckfull', '', '<b>' . $usernames . '</b>'), $formcontinue, $formcancel);
}

echo $OUTPUT->footer();
