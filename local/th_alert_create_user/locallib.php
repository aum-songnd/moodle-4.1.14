<?php
require_once $CFG->dirroot . '/login/lib.php';

/**
 * Send the link create password to the user via email.
 *
 * @param stdClass $user A {@link $USER} object
 * @return bool|string Returns "true" if mail was sent OK and "false" if there was an error
 */
function local_th_alert_create_user($user) {
	global $CFG, $DB;
	$resetrecord = th_core_login_generate_password_reset($user);
	$site = get_site();
	$supportuser = core_user::get_support_user();
	$lang = empty($user->lang) ? get_newuser_language() : $user->lang;

	$value = 0;
	$name = 'auth_forcepasswordchange';
	$preference = $DB->get_record('user_preferences', array('userid' => $user->id, 'name' => $name));
	if ($preference) {
		if ($preference->value == 1) {
			$DB->set_field('user_preferences', 'value', $value, array('id' => $preference->id));
		}
	}

	$data = new stdClass();
	$data->firstname = $user->firstname;
	$data->lastname = $user->lastname;
	$data->username = $user->username;
	$data->sitename = format_string($site->fullname);
	$url = $CFG->wwwroot . '/local/th_alert_create_user/set_password.php?token=' . $resetrecord->token;
	$data->link = html_writer::link($url, get_string('here', 'local_th_alert_create_user'));
	$data->url = $url;
	$data->login = $CFG->wwwroot;

	$message = get_string('newusernewpasswordtext', 'local_th_alert_create_user', $data);
	$subject = format_string($site->fullname) . ': ' . get_string('newusernewpasswordsubj', '', format_string($site->fullname));

	// Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
	return email_to_user($user, $supportuser, $subject, $message);
}

/** Create a new record in the database to track a new password set request for user.
 * @param object $user the user record, the requester would like a new password set for.
 * @return record created.
 */
function th_core_login_generate_password_reset($user) {
	global $DB;
	$resetrecord = new stdClass();
	$resetrecord->timerequested = time();
	$resetrecord->userid = $user->id;
	$resetrecord->token = random_string(32);
	$resetrecord->id = $DB->insert_record('local_th_alert_create_user', $resetrecord);
	return $resetrecord;
}

/**
 * This function processes a user's submitted token to validate the request to set a new password.
 * If the user's token is validated, they are prompted to set a new password.
 * @param string $token the one-use identifier which should verify the password reset request as being valid.
 * @return void
 */
function th_core_login_process_password_set($token) {
	global $DB, $CFG, $OUTPUT, $PAGE, $SESSION;
	require_once $CFG->dirroot . '/user/lib.php';

	// $pwresettime = isset($CFG->pwresettime) ? $CFG->pwresettime : 1800;
	$sql = "SELECT u.*, upr.token, upr.timerequested, upr.id as tokenid
              FROM {user} u
              JOIN {local_th_alert_create_user} upr ON upr.userid = u.id
             WHERE upr.token = ? AND u.deleted = 0";
	$user = $DB->get_record_sql($sql, array($token));

	$forgotpasswordurl = "{$CFG->wwwroot}/local/th_alert_create_user/set_password.php";
	// if (empty($user) or ($user->timerequested < (time() - $pwresettime - DAYSECS))) {
	// 	// There is no valid reset request record - not even a recently expired one.
	// 	// (suspicious)
	// 	// Direct the user to the forgot password page to request a password reset.
	// 	echo $OUTPUT->header();
	// 	notice(get_string('noresetrecord'), $forgotpasswordurl);
	// 	die; // Never reached.
	// }
	// if ($user->timerequested < (time() - $pwresettime)) {
	// 	// There is a reset record, but it's expired.
	// 	// Direct the user to the forgot password page to request a password reset.
	// 	$pwresetmins = floor($pwresettime / MINSECS);
	// 	echo $OUTPUT->header();
	// 	notice(get_string('resetrecordexpired', '', $pwresetmins), $forgotpasswordurl);
	// 	die; // Never reached.
	// }
	if (empty($user)) {
		// There is no valid reset request record - not even a recently expired one.
		// (suspicious)
		// Direct the user to the forgot password page to request a password reset.
		// echo $OUTPUT->header();
		// notice(get_string('noresetrecord'), $forgotpasswordurl);
		// die; // Never reached.
		redirect($CFG->wwwroot . '/index.php');
	}
	// if ($user->timerequested < (time() - $pwresettime)) {
	// 	// There is a reset record, but it's expired.
	// 	// Direct the user to the forgot password page to request a password reset.
	// 	$pwresetmins = floor($pwresettime / MINSECS);
	// 	echo $OUTPUT->header();
	// 	notice(get_string('resetrecordexpired', '', $pwresetmins), $forgotpasswordurl);
	// 	die; // Never reached.
	// }

	if ($user->auth === 'nologin' or !is_enabled_auth($user->auth)) {
		// Bad luck - user is not able to login, do not let them set password.
		echo $OUTPUT->header();
		print_error(get_string('forgotteninvalidurl', 'local_th_alert_create_user'));
		die; // Never reached.
	}

	// Check this isn't guest user.
	if (isguestuser($user)) {
		print_error(get_string('cannotresetguestpwd', 'local_th_alert_create_user'));
	}

	// Token is correct, and unexpired.
	$mform = new th_login_set_password_form(null, $user);
	$data = $mform->get_data();
	if (empty($data)) {
		// User hasn't submitted form, they got here directly from email link.
		// Next, display the form.
		$setdata = new stdClass();
		$setdata->username = $user->username;
		$setdata->username2 = $user->username;
		$setdata->token = $user->token;
		$mform->set_data($setdata);
		echo $OUTPUT->header();
		echo $OUTPUT->box(get_string('setpasswordinstructions', 'local_th_alert_create_user'), 'generalbox boxwidthnormal boxaligncenter');
		$mform->display();
		echo $OUTPUT->footer();
		return;
	} else {
		// User has submitted form.
		// Delete this token so it can't be used again.
		$DB->delete_records('local_th_alert_create_user', array('id' => $user->tokenid));
		$userauth = get_auth_plugin($user->auth);
		if (!$userauth->user_update_password($user, $data->password)) {
			print_error(get_string('errorpasswordupdate', 'local_th_alert_create_user'), 'auth');
		}
		// user_add_password_history($user->id, $data->password);
		// if (!empty($CFG->passwordchangelogout)) {
		// 	\core\session\manager::kill_user_sessions($user->id, session_id());
		// }
		// Reset login lockout (if present) before a new password is set.
		login_unlock_account($user);
		// Clear any requirement to change passwords.
		unset_user_preference(get_string('auth_forcepasswordchange', 'local_th_alert_create_user'), $user);
		unset_user_preference(get_string('create_password', 'local_th_alert_create_user'), $user);

		if (!empty($user->lang)) {
			// Unset previous session language - use user preference instead.
			unset($SESSION->lang);
		}
		complete_user_login($user); // Triggers the login event.

		\core\session\manager::apply_concurrent_login_limit($user->id, session_id());

		$urltogo = core_login_get_return_url();
		unset($SESSION->wantsurl);

		// Plugins can perform post set password actions once data has been validated.
		core_login_post_set_password_requests($data, $user);

		redirect($urltogo, get_string('passwordset'), 1);
	}
}