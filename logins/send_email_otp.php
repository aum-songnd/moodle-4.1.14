<?php
// defined('MOODLE_INTERNAL') || die();
require_once '../config.php';
require_once("$CFG->libdir/authlib.php");

global $DB, $PAGE, $CFG;
$PAGE->set_context(context_system::instance());

if (isset($_POST['type']) and $_POST['type'] != '' and isset($_POST['typesend']) and $_POST['typesend'] != '') {
	$type = trim($_POST['type']);
	$typesend = trim($_POST['typesend']);
	if ($type == 0 and ($typesend == 0 or $typesend == 1)) {
		if ($typesend == 0) {
			if (isset($_POST['username']) and $_POST['username'] != '' and isset($_POST['email']) and $_POST['email'] != '') {
				$username = trim($_POST['username']);
				$email = trim($_POST['email']);

				$username = core_text::strtolower($username);
				$email = core_text::strtolower($email);
				$sql = "SELECT COUNT(id) FROM {user} WHERE (username = ? AND email = ?) OR (username = ? AND email = ?)";


			    if ($user = get_complete_user_data('username', $username, $CFG->mnet_localhost_id)) {
			        // we have found the user

			    } else if (!empty($CFG->authloginviaemail)) {
			        if ($email = clean_param($username, PARAM_EMAIL)) {
			            $select = "mnethostid = :mnethostid AND LOWER(email) = LOWER(:email) AND deleted = 0";
			            $params = array('mnethostid' => $CFG->mnet_localhost_id, 'email' => $email);
			            $users = $DB->get_records_select('user', $select, $params, 'id', 'id', 0, 2);
			            if (count($users) === 1) {
			                // Use email for login only if unique.
			                $user = reset($users);
			                $user = get_complete_user_data('id', $user->id);
			                $username = $user->username;
			            }
			            unset($users);
			        }
			    }

				if ($DB->count_records_sql($sql, array($username, $email, $email, $email)) > 0) {
					$timeexpiry = get_config('local_th_config_login', 'timeexpiry');
					$timeexpiry = $timeexpiry ? (int)$timeexpiry : 1800;
					$time = time();
					$user = $DB->get_record('user', array('username' => $username));
					$params = array (
						'type' => $type,
						'typesend' => $typesend,
						'userid' => $user->id
					);

					$a = $time-$timeexpiry;
					if (!$DB->record_exists_select('local_th_user_login', "timerequested > ($time-$timeexpiry) AND type = :type AND typesend = :typesend AND userid = :userid", $params)) {
						$userfrom = \core_user::get_support_user();
				    	$r = new stdClass();
				    	$r->userid = $user->id;
				    	$r->timerequested = $time;
				    	$r->type = $type;
				    	$r->typesend = $typesend;
				    	$r->otp = rand(100000, 999999);
				    	$r->code = password_hash($time, PASSWORD_BCRYPT);

				    	$data = [];
				    	$data['otp'] = $r->otp;
				    	$data['timeexpiry'] = floor($timeexpiry / MINSECS);

				    	$site = get_site();
						$subject = format_string($site->fullname) . ': ' . get_string('otp_login', 'local_th_config_login');
						$message = get_string('message_send_otp', 'local_th_config_login', $data);
						$send_ok = email_to_user($user, $userfrom, $subject, $message);
				    	// $send_ok = 1;
						if ($send_ok) {
							$DB->insert_record('local_th_user_login', $r);
							echo json_encode(array('status' => 1, 'otp' => $r->otp));
						} else {
							echo json_encode(array('status' => 0));
							// exit;
						}
					} else {
						echo json_encode(array('status' => 0));
					}

				} else {
					echo json_encode(array('status' => 0));
				}

			} else {
				echo json_encode(array('status' => 0));
			}
		} else {
			if (isset($_POST['email']) and $_POST['email'] != '') {
				$email = trim($_POST['email']);
				$email = core_text::strtolower($email);
				$username = trim($_POST['username']);
				$username = core_text::strtolower($username);
				if (isset($username) AND !empty($username)) {
					if ($DB->record_exists('user', ['email' => $email, 'username' => $username])) {
						$time = time();
						$timeexpiry = get_config('local_th_config_login', 'timeexpiry');
						$timeexpiry = $timeexpiry ? (int)$timeexpiry : 1800;
						$user = $DB->get_record('user', array('email' => $email));
						$params = array (
							'type' => $type,
							'typesend' => $typesend,
							'userid' => $user->id
						);
						if (!$DB->record_exists_select('local_th_user_login', "timerequested > ($time-$timeexpiry) AND type = :type AND typesend = :typesend AND userid = :userid", $params)){

							$time = time();
							$user = $DB->get_record('user', array('email' => $email));
					    	$userfrom = \core_user::get_support_user();
					    	$r = new stdClass();
					    	$r->userid = $user->id;
					    	$r->timerequested = $time;
					    	$r->type = $type;
					    	$r->typesend = $typesend;
					    	$r->otp = rand(100000, 999999);
					    	$r->code = password_hash($time, PASSWORD_BCRYPT);

					    	$data = [];
					    	$data['otp'] = $r->otp;
					    	$data['timeexpiry'] = floor($timeexpiry / MINSECS);

					    	$site = get_site();
							$subject = format_string($site->fullname) . ': ' . get_string('otp_code', 'local_th_config_login');
							$message = get_string('message_send_otp_forgot', 'local_th_config_login', $data);
							$send_ok = email_to_user($user, $userfrom, $subject, $message);
					    	// $send_ok = 1;
							if ($send_ok) {
								$DB->insert_record('local_th_user_login', $r);
								echo json_encode(array('status' => 1, 'otp' => $r->otp));
							} else {
								echo json_encode(array('status' => 0));
							}
						} else {
							echo json_encode(array('status' => 0));
						}
					} else {
						echo json_encode(array('status' => 0));
					}
				} else {
					if ($DB->record_exists('user', ['email' => $email])) {
						$time = time();
						$timeexpiry = get_config('local_th_config_login', 'timeexpiry');
						$timeexpiry = $timeexpiry ? (int)$timeexpiry : 1800;
						$user = $DB->get_record('user', array('email' => $email));
						$params = array (
							'type' => $type,
							'typesend' => $typesend,
							'userid' => $user->id
						);
						if (!$DB->record_exists_select('local_th_user_login', "timerequested > ($time-$timeexpiry) AND type = :type AND typesend = :typesend AND userid = :userid", $params)){

							$time = time();
							$user = $DB->get_record('user', array('email' => $email));
					    	$userfrom = \core_user::get_support_user();
					    	$r = new stdClass();
					    	$r->userid = $user->id;
					    	$r->timerequested = $time;
					    	$r->type = $type;
					    	$r->typesend = $typesend;
					    	$r->otp = rand(100000, 999999);
					    	$r->code = password_hash($time, PASSWORD_BCRYPT);

					    	$data = [];
					    	$data['otp'] = $r->otp;
					    	$data['timeexpiry'] = floor($timeexpiry / MINSECS);

					    	$site = get_site();
							$subject = format_string($site->fullname) . ': ' . get_string('otp_code', 'local_th_config_login');
							$message = get_string('message_send_otp_forgot', 'local_th_config_login', $data);
							$send_ok = email_to_user($user, $userfrom, $subject, $message);
					    	// $send_ok = 1;
							if ($send_ok) {
								$DB->insert_record('local_th_user_login', $r);
								echo json_encode(array('status' => 1, 'otp' => $r->otp));
							} else {
								echo json_encode(array('status' => 0));
							}
						} else {
							echo json_encode(array('status' => 0));
						}
					} else {
						echo json_encode(array('status' => 0));
					}
				}

			}
		}
	} else {
		echo json_encode(array('status' => 0));
	}
} else {
	echo json_encode(array('status' => 0));
}