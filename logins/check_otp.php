<?php
// defined('MOODLE_INTERNAL') || die();
require_once '../config.php';

global $DB, $CFG;
if (isset($_POST['type']) and ($_POST['type'] != '')) {
	$type = trim($_POST['type']);
	if ($type == 0) {
		if (isset($_POST['typesend']) and ($_POST['typesend'] != '')) {
			$typesend = trim($_POST['typesend']);
			if ($typesend == 0) {
				if (isset($_POST['username']) and ($_POST['username'] != '')) {
					if (isset($_POST['email']) and ($_POST['email'] != '')) {
						if (isset($_POST['otp']) and ($_POST['otp'] != '')) {
							$username = trim($_POST['username']);
							$email = trim($_POST['email']);
							$otp = trim($_POST['otp']);
							$username = core_text::strtolower($username);
							$email = core_text::strtolower($email);

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

							$sql = "SELECT u.*, upr.otp, upr.timerequested, upr.id as otpid, upr.code
							          FROM {user} u
							          JOIN {local_th_user_login} upr ON upr.userid = u.id
							         WHERE upr.otp = :otp and upr.type=:type and upr.typesend=:typesend
							         and upr.userid=(SELECT id FROM {user} WHERE username = :username and email = :email and deleted = 0 and suspended = 0)";
							$user = $DB->get_record_sql($sql, 
								array(
									'otp' => $otp, 
									'username' => $username, 
									'email' => $email,
									'type' => $type,
									'typesend' => $typesend
								)
							);
							$config_login = get_config('local_th_config_login');
						    $otplogintime =  $config_login->timeexpiry ? $config_login->timeexpiry : 1800;
						    if (empty($user) or ($user->timerequested < (time() - $otplogintime - DAYSECS))) {
						        echo json_encode(array('status' => false, 'message' => get_string('no_otp', 'local_th_config_login')));
						    } else if ($user->timerequested < (time() - $otplogintime)){
						        echo json_encode(array('status' => false, 'message' => get_string('expiry_otp', 'local_th_config_login')));
						    } else {
						        if (password_verify($user->timerequested, $user->code)) {
						            echo json_encode(array('status' => true));
						        } else {
						            echo json_encode(array('status' => false, 'message' => get_string('otp_incorrect', 'local_th_config_login')));
						        }
						    }
						} else {
							echo json_encode(array('status' => false, 'message' => get_string('otp_empty', 'local_th_config_login')));
						}
					} else {
							echo json_encode(array('status' => false, 'message' => get_string('email_empty', 'local_th_config_login')));
					}
				} else {
					echo json_encode(array('status' => false, 'message' => get_string('username_empty', 'local_th_config_login')));
				}
			} else if ($typesend == 1) {
				if (isset($_POST['email']) and ($_POST['email'] != '')) {
					if (isset($_POST['username']) and ($_POST['username'] != '')) {
						if (isset($_POST['otp']) and ($_POST['otp'] != '')) {
							$username = trim($_POST['username']);
							$email = trim($_POST['email']);
							$otp = trim($_POST['otp']);
							$username = core_text::strtolower($username);
							$email = core_text::strtolower($email);

							$sql = "SELECT u.*, upr.otp, upr.timerequested, upr.id as otpid, upr.code
							          FROM {user} u
							          JOIN {local_th_user_login} upr ON upr.userid = u.id
							         WHERE upr.otp = :otp and upr.type=:type and upr.typesend=:typesend
							         and upr.userid=(SELECT id FROM {user} WHERE username = :username and email = :email and deleted = 0 and suspended = 0)";
							$user = $DB->get_record_sql($sql, 
								array(
									'otp' => $otp, 
									'username' => $username, 
									'email' => $email,
									'type' => $type,
									'typesend' => $typesend
								)
							);
							$config_login = get_config('local_th_config_login');
						    $otplogintime =  $config_login->timeexpiry ? $config_login->timeexpiry : 1800;
						    if (empty($user) or ($user->timerequested < (time() - $otplogintime - DAYSECS))) {
						        echo json_encode(array('status' => false, 'message' => get_string('no_otp', 'local_th_config_login')));
						    } else if ($user->timerequested < (time() - $otplogintime)){
						        echo json_encode(array('status' => false, 'message' => get_string('expiry_otp', 'local_th_config_login')));
						    } else {
						        if (password_verify($user->timerequested, $user->code)) {
						            echo json_encode(array('status' => true));
						        } else {
						            echo json_encode(array('status' => false, 'message' => get_string('otp_incorrect', 'local_th_config_login')));
						        }
						    }
						} else {
							echo json_encode(array('status' => false, 'message' => get_string('otp_empty', 'local_th_config_login')));
						}
					} else {
						if (isset($_POST['otp']) and ($_POST['otp'] != '')) {
							// $username = trim($_POST['username']);
							$email = trim($_POST['email']);
							$otp = trim($_POST['otp']);
							// $username = core_text::strtolower($username);
							$email = core_text::strtolower($email);

							$sql = "SELECT u.*, upr.otp, upr.timerequested, upr.id as otpid, upr.code
							          FROM {user} u
							          JOIN {local_th_user_login} upr ON upr.userid = u.id
							         WHERE upr.otp=:otp and upr.type=:type and upr.typesend=:typesend
							         and upr.userid=(SELECT id FROM {user} WHERE email = :email and deleted = 0 and suspended = 0)";
							$user = $DB->get_record_sql($sql, 
								array(
									'otp' => $otp,
									'email' => $email,
									'type' => $type,
									'typesend' => $typesend
								)
							);
							$config_login = get_config('local_th_config_login');
						    $otplogintime =  $config_login->timeexpiry ? $config_login->timeexpiry : 1800;
						    if (empty($user) or ($user->timerequested < (time() - $otplogintime - DAYSECS))) {
						        echo json_encode(array('status' => false, 'message' => get_string('no_otp', 'local_th_config_login')));
						    } else if ($user->timerequested < (time() - $otplogintime)){
						        echo json_encode(array('status' => false, 'message' => get_string('expiry_otp', 'local_th_config_login')));
						    } else {
						        if (password_verify($user->timerequested, $user->code)) {
						            echo json_encode(array('status' => true));
						        } else {
						            echo json_encode(array('status' => false, 'message' => get_string('otp_incorrect', 'local_th_config_login')));
						        }
						    }
						} else {
							echo json_encode(array('status' => false, 'message' => get_string('otp_empty', 'local_th_config_login')));
						}
					}
				} else {
					echo json_encode(array('status' => false, 'message' => 'no email2'));
				}
			} else {
				echo json_encode(array('status' => false, 'message' => get_string('username_empty', 'local_th_config_login')));
			}
		} else {
			echo json_encode(array('status' => false, 'message' => get_string('error', 'local_th_config_login')));
		}
	} else {
		echo json_encode(array('status' => false, 'message' => get_string('error', 'local_th_config_login')));
	}
} else {
	echo json_encode(array('status' => false, 'message' => get_string('error', 'local_th_config_login')));
}