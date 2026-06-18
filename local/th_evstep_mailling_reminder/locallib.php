<?php

/**
 * th_send_mail_attemps_to_grading function gửi thông báo bài tập học viên cần chấm
 *
 * @return void
 */
function th_send_mail_attemps_need_to_grading() {

	global $DB, $CFG;

	$hoursend = get_config('local_th_evstep_mailling_reminder', 'config_attemps_need_to_grading') ?? 9;

	$new_created_count = $DB->get_field_sql("SELECT COUNT(*) 
    		FROM {block_th_scoring_request} AS sr
            JOIN {quiz_attempts} as qa ON qa.id = sr.atempt_review
            JOIN {quiz} as q ON qa.quiz = q.id 
            JOIN {course_modules} as cm ON q.id = cm.instance
        	WHERE atempt_review NOT IN (SELECT attemptid FROM {block_th_assign_grading})
            AND (cm.idnumber LIKE '%rec%' OR cm.idnumber LIKE '%ess%')
    		AND (sr.time BETWEEN UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY + INTERVAL :hoursend_start HOUR) 
        	AND UNIX_TIMESTAMP(CURDATE() + INTERVAL :hoursend_end HOUR) - 1)", ['hoursend_start' => $hoursend,'hoursend_end' => $hoursend,]);

	$backlog = $DB->get_field_sql("SELECT COUNT(*) 
			FROM {block_th_scoring_request} AS sr
            JOIN {quiz_attempts} as qa ON qa.id = sr.atempt_review
            JOIN {quiz} as q ON qa.quiz = q.id 
            JOIN {course_modules} as cm ON q.id = cm.instance
        	WHERE atempt_review NOT IN (SELECT attemptid FROM {block_th_assign_grading})
            AND (cm.idnumber LIKE '%rec%' OR cm.idnumber LIKE '%ess%')
            AND sr.time < UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY + INTERVAL :hoursend HOUR)", ['hoursend' => $hoursend]);

	if ((isset($new_created_count) && $new_created_count > 0) || (isset($backlog) && $backlog > 0)) {
		$collaborator_manager = get_config('local_th_evstep_mailling_reminder', 'config_collaborator_manager') ?? 'collaborator_manager';

		$qtvs = $DB->get_records_sql("SELECT u.id
				FROM {user} as u 
				JOIN {role_assignments} as ra ON ra.userid = u.id 
				JOIN {role} as r ON r.shortname = :shortname AND r.id = ra.roleid;", ['shortname' => $collaborator_manager]);
		try {

			$from = core_user::get_support_user();
			$subject = '';
			if (isset($CFG->supportname)) {
				$subject = "[" . $CFG->supportname . "] ";
			}
			$subject .= get_string('subject_email_attemps_need_to_grading', 'local_th_evstep_mailling_reminder');

			foreach ($qtvs as $qtv) {

				$a = new stdClass();
				$a->date = date('d/m/Y');
				$a->newcreate = $new_created_count;
				$a->backlog = $backlog;
				$a->url = $CFG->wwwroot .'/blocks/th_assign_grading/edit.php';

				$message = get_string('message_email_attemps_need_to_grading', 'local_th_evstep_mailling_reminder', $a);

				$user = $DB->get_record('user', array('id' => $qtv->id, 'deleted' => 0));
				if ($user) {
					
					$result = email_to_user($user, $from, $subject, $message, '');
					if ($result) {
						mtrace('send: ' . $user->email . ' ok ' . $user->firstname . ' ' . $user->lastname);
					} else {
						mtrace('send: ' . $user->email . ' no ok ' . $user->firstname . ' ' .$user->lastname);
					}
				} else {
					mtrace('No email: ' . $user->firstname .' ' . $user->lastname);
				}
			}

		} catch (Exception $e) {
			mtrace($e);
			die;
		}
	} else {
		mtrace('Nothing to be send');
	}
}

/**
 * th_send_mail_attemps_to_grading function gửi thông báo bài tập cần chấm
 *
 * @return void
 */
function th_send_mail_attemps_to_grading() {

	global $DB, $CFG;

	$hoursend = get_config('local_th_evstep_mailling_reminder', 'config_attemps_to_grading') ?? 18;

	$sql = "SELECT * FROM {block_th_assign_grading}
        	WHERE assignment_time BETWEEN UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY + INTERVAL :hoursend_start HOUR) 
            AND UNIX_TIMESTAMP(CURDATE() + INTERVAL :hoursend_end HOUR) - 1";

	$new_assign_for_ctvs = $DB->get_records_sql($sql, 
		['hoursend_start' => $hoursend,
		'hoursend_end' => $hoursend,]);

	if($new_assign_for_ctvs) {
		try {

			$from = core_user::get_support_user();
			$subject = '';
			if (isset($CFG->supportname)) {
				$subject = "[" . $CFG->supportname . "] ";
			}
			$subject .= get_string('subject_email_attemps_to_grading', 'local_th_evstep_mailling_reminder');
	
			// Tạo một mảng để lưu danh sách URL theo user.
			$user_urls = array();
	
			foreach ($new_assign_for_ctvs as $new_assign_for_ctv) {
				$user = $DB->get_record('user', array('id' => $new_assign_for_ctv->ctvid, 'deleted' => 0));
				if ($user) {
					// Tạo URL cho user này.
					$url = $CFG->wwwroot . '/blocks/th_scoring_collaborators/review.php?attempt=' . $new_assign_for_ctv->attemptid . '&id=' . $new_assign_for_ctv->id . '&confirm=1';
					
					// Lưu URL vào danh sách của user.
					if (!isset($user_urls[$user->id])) {
						$user_urls[$user->id] = array(
							'user' => $user,
							'urls' => array()
						);
					}
					$user_urls[$user->id]['urls'][] = $url;
				}
			}
	
			// Gửi email tổng hợp cho từng user.
			foreach ($user_urls as $userid => $data) {
				$user = $data['user'];
				$urls = $data['urls'];
	
				// Tạo nội dung email với danh sách URL.
				$a = new stdClass();
				$a->url = implode("\n", array_map(function ($url) {
					return '- ' . $url;
				}, $urls));

				$message = get_string('message_email_attemps_to_grading', 'local_th_evstep_mailling_reminder', $a);
	
				// Gửi email.
				$result = email_to_user($user, $from, $subject, $message, '');
				if ($result) {
					mtrace('send: ' . $user->email . ' ok ' . $user->firstname . ' ' . $user->lastname);
				} else {
					mtrace('send: ' . $user->email . ' no ok ' . $user->firstname . ' ' . $user->lastname);
				}
			}
	
		} catch (Exception $e) {
			mtrace($e);
			die;
		}
	} else {
		mtrace('Nothing to be send');
	}
	
}

/**
 * th_send_mail_attemps_to_grading function gửi thông báo CTV hoàn thành chấm bài
 *
 * @return void
 */
function th_send_mail_ctv_finished_grading() {

	global $DB, $CFG;

	$hoursend = get_config('local_th_evstep_mailling_reminder', 'config_ctv_finished_grading') ?? 15;

	$sql = "SELECT * FROM {block_th_assign_grading}
        	WHERE grading_date BETWEEN UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY + INTERVAL :hoursend_start HOUR) 
            AND UNIX_TIMESTAMP(CURDATE() + INTERVAL :hoursend_end HOUR) - 1";

	$ctv_finish_gradings = $DB->get_records_sql($sql, ['hoursend_start' => $hoursend,'hoursend_end' => $hoursend,]);

	if($ctv_finish_gradings) {
		try {

			$from = core_user::get_support_user();
			$subject = '';
			if (isset($CFG->supportname)) {
				$subject = "[" . $CFG->supportname . "] ";
			}
			$subject .= get_string('subject_email_ctv_finished_grading', 'local_th_evstep_mailling_reminder');
			
			$qtvs = $DB->get_records_sql("SELECT u.id
				FROM {user} as u 
				JOIN {role_assignments} as ra ON ra.userid = u.id 
				JOIN {role} as r ON r.shortname = 'collaborator_manager' AND r.id = ra.roleid;");
	
			foreach ($qtvs as $qtv) {
				$user = $DB->get_record('user', array('id' => $qtv->id, 'deleted' => 0));
				
				if ($user) {
					$a = new stdClass();
					$a->url = $CFG->wwwroot . '/blocks/th_assign_grading/view.php';

					$message = get_string('message_email_ctv_finished_grading', 'local_th_evstep_mailling_reminder', $a);
					$result = email_to_user($user, $from, $subject, $message, '');
					
					if ($result) {
						mtrace('send: ' . $user->email . ' ok ' . $user->firstname . ' ' . $user->lastname);
					} else {
						mtrace('send: ' . $user->email . ' no ok ' . $user->firstname . ' ' . $user->lastname);
					}
						
				} else {
					mtrace('No email: ' . $qtv->id);
				}
			}
	
		} catch (Exception $e) {
			mtrace($e);
			die;
		}
	} else {
		mtrace('Nothing to be send');
	}
}

/**
 * th_send_mail_attemps_to_grading function gửi thông báo phê duyệt bài chấm
 *
 * @return void
 */
function th_send_mail_grading_approved() {

	global $DB, $CFG;

	$hoursend = get_config('local_th_evstep_mailling_reminder', 'config_grading_approved') ?? 15;

	$sql = "SELECT * FROM {block_th_assign_grading}
        	WHERE confirm_date BETWEEN UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY + INTERVAL :hoursend_start HOUR) 
            AND UNIX_TIMESTAMP(CURDATE() + INTERVAL :hoursend_end HOUR) - 1";

	$grading_approveds = $DB->get_records_sql($sql, ['hoursend_start' => $hoursend,'hoursend_end' => $hoursend,]);

	if($grading_approveds) {
		try {

			$from = core_user::get_support_user();
			$subject = '';
			if (isset($CFG->supportname)) {
				$subject = "[" . $CFG->supportname . "] ";
			}
			$subject .= get_string('subject_email_grading_approved', 'local_th_evstep_mailling_reminder');
			
			// Tạo một mảng để lưu danh sách URL theo user.
			$user_grading_approved = array();
	
			foreach ($grading_approveds as $grading_approved) {
				$user = $DB->get_record('user', array('id' => $grading_approved->ctvid, 'deleted' => 0));
				if ($user) {
					// Tạo URL liên quan đến bài grading được phê duyệt.
					$url = $CFG->wwwroot . '/blocks/th_scoring_collaborators/review.php?attempt=' . $grading_approved->attemptid . '&id=' . $grading_approved->id . '&confirm=1';
	
					// Lưu URL vào danh sách của user.
					if (!isset($user_grading_approved[$user->id])) {
						$user_grading_approved[$user->id] = array(
							'user' => $user,
							'urls' => array()
						);
					}
					$user_grading_approved[$user->id]['urls'][] = $url;
				}
			}
	
			// Gửi email tổng hợp cho từng user.
			foreach ($user_grading_approved as $userid => $data) {
				$user = $data['user'];
				$urls = $data['urls'];
	
				// Tạo nội dung email với danh sách URL.
				$a = new stdClass();
				$a->url = implode("\n", array_map(function ($url) {
					return '- ' . $url;
				}, $urls));
	
				$message = get_string('message_email_grading_approved', 'local_th_evstep_mailling_reminder', $a);
	
				// Gửi email.
				$result = email_to_user($user, $from, $subject, $message, '');
				if ($result) {
					mtrace('send: ' . $user->email . ' ok ' . $user->firstname . ' ' . $user->lastname);
				} else {
					mtrace('send: ' . $user->email . ' no ok ' . $user->firstname . ' ' . $user->lastname);
				}
			}
		} catch (Exception $e) {
			mtrace($e);
			die;
		}
	} else {
		mtrace('Nothing to be send');
	}
}

/**
 * th_send_mail_attemps_to_grading function gửi thông báo gói gia sư của học viên
 *
 * @return void
 */
function th_send_mail_assign_turns() {

	global $DB, $CFG;

	$hoursend = get_config('local_th_evstep_mailling_reminder', 'config_assign_turns') ?? 22;

	$sql = "SELECT * FROM {th_assign_turns}
        	WHERE assign_date BETWEEN UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY + INTERVAL :hoursend_start HOUR) 
            AND UNIX_TIMESTAMP(CURDATE() + INTERVAL :hoursend_end HOUR) - 1";

	$assign_turns = $DB->get_records_sql($sql, ['hoursend_start' => $hoursend,'hoursend_end' => $hoursend,]);

	if($assign_turns) {
		try {

			$from = core_user::get_support_user();
			$subject = '';
			if (isset($CFG->supportname)) {
				$subject = "[" . $CFG->supportname . "] ";
			}
			$subject .= get_string('subject_email_assign_turns', 'local_th_evstep_mailling_reminder');
			
			foreach ($assign_turns as $assign_turn) {
	
				$user = $DB->get_record('user', array('id' => $assign_turn->userid, 'deleted' => 0));
	
				$a = new stdClass();
				$a->fullname = $user->firstname . ' ' . $user->lastname;
				$a->turn = $assign_turn->unused;
				
				$message = get_string('message_email_assign_turns', 'local_th_evstep_mailling_reminder', $a);
	
				if ($user) {
					$result = email_to_user($user, $from, $subject, $message, '');
					if ($result) {
						mtrace('send: ' . $user->email . ' ok ' . $user->firstname . ' ' . $user->lastname);
					} else {
						mtrace('send: ' . $user->email . ' no ok' . $user->firstname . ' ' .$user->lastname);
					}
				} else {
					mtrace('No email: ' . $user->firstname .' ' . $user->lastname);
				}
			}
		} catch (Exception $e) {
			mtrace($e);
			die;
		}
	} else {
		mtrace('Nothing to be send');
	}
}

/**
 * th_send_mail_attemps_to_grading function gửi thông báo bài tập đã chấm
 * @data a row from block_th_assign
 * @user recieved student
 * @return void
 */
function send_mail_graded_attemp($approved_ids) {
	global $DB;
	foreach ($approved_ids as $approved_id) {
		$data = $DB->get_record('block_th_assign_grading', array('id' => $approved_id), '*', MUST_EXIST);
		th_send_mail_graded_attemp($data);
	}
}


function th_send_mail_graded_attemp($data) {

	global $DB, $CFG;

	$userid = $DB->get_record_sql("SELECT u.id FROM {quiz_attempts} as qa JOIN {user} as u ON qa.userid = u.id
		    WHERE qa.id = $data->attemptid AND qa.preview = 0");

	try {

		$from = core_user::get_support_user();
		$subject = '';
		if (isset($CFG->supportname)) {
			$subject = "[" . $CFG->supportname . "] ";
		}
		$subject .= get_string('subject_email_graded_attemp', 'local_th_evstep_mailling_reminder');
        //Get user
		$user = $DB->get_record('user', array('id' => $userid->id, 'deleted' => 0));

        $a = new stdClass();
        $a->url = $CFG->wwwroot . '/mod/quiz/review.php?attempt=' . $data->attemptid;
        $a->fullname = $user->firstname . ' ' . $user->lastname;

        $message = get_string('message_email_graded_attemp', 'local_th_evstep_mailling_reminder', $a);

        if ($user) {
            $result = email_to_user($user, $from, $subject, $message, '');
            if ($result) {
                mtrace('send: ' . $user->email . ' ok ' . $user->firstname . ' ' . $user->lastname);
            } else {
                mtrace('send: ' . $user->email . ' no ok ' . $user->firstname . ' ' .$user->lastname);
            }
        } else {
            mtrace('No email: ' . $user->firstname .' ' . $user->lastname);
		}
	} catch (Exception $e) {
		print_object($e);
		mtrace($e);
		die;
	}
}

/**
 * th_send_mail_attemps_to_grading function gửi thông báo ghi danh
 *
 * @return void
 */
function th_send_mail_user_enrolled() {

	global $DB, $CFG;

	$hoursend = get_config('local_th_evstep_mailling_reminder', 'config_user_enrolled') ?? 22;

	$sql = "SELECT ra.userid, c.id AS courseid, c.fullname AS coursename
			FROM {role_assignments} ra
			JOIN {context} ctx ON ra.contextid = ctx.id
			JOIN {course} c ON ctx.instanceid = c.id
			WHERE ctx.contextlevel = :contextlevel
			AND ra.timemodified BETWEEN UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY + INTERVAL :hoursend_start HOUR) 
            AND UNIX_TIMESTAMP(CURDATE() + INTERVAL :hoursend_end HOUR) - 1";
	$params = [
		'contextlevel' => CONTEXT_COURSE,
		'hoursend_start' => $hoursend,
    	'hoursend_end' => $hoursend,
	];

	$user_enrolleds = $DB->get_records_sql($sql, $params);

	if($user_enrolleds) {
		try {

			$from = core_user::get_support_user();
			$subject = '';
			if (isset($CFG->supportname)) {
				$subject = "[" . $CFG->supportname . "] ";
			}
			$subject .= get_string('subject_email_user_enrolled', 'local_th_evstep_mailling_reminder');
			
			foreach ($user_enrolleds as $user_enrolled) {
	
				$user = $DB->get_record('user', array('id' => $user_enrolled->userid, 'deleted' => 0));
	
				$a = new stdClass();
				$a->fullname = $user->firstname . ' ' . $user->lastname;
				$a->url = $CFG->wwwroot . '/course/view.php?id=' . $user_enrolled->courseid;
				$a->coursename = $user_enrolled->coursename;
				
				$message = get_string('message_email_user_enrolled', 'local_th_evstep_mailling_reminder', $a);
	
				if ($user) {
					$result = email_to_user($user, $from, $subject, $message, '');
					if ($result) {
						mtrace('send: ' . $user->email . ' ok ' . $user->firstname . ' ' . $user->lastname);
					} else {
						mtrace('send: ' . $user->email . ' no ok ' . $user->firstname . ' ' .$user->lastname);
					}
				} else {
					mtrace('No email: ' . $user->firstname .' ' . $user->lastname);
				}
			}
		} catch (Exception $e) {
			mtrace($e);
			die;
		}
	} else {
		mtrace('Nothing to be send');
	}
}