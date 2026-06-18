<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_qa
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require_once $CFG->dirroot . '/local/th_log_email/lib.php';

class mod_th_qaa {

	private static $obj;

	public static function instance() {
		if (mod_th_qaa::$obj == null) {
			mod_th_qaa::$obj = new mod_th_qaa();
		}
		return mod_th_qaa::$obj;
	}

	function email_to_user($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '',
		$usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) {

		global $CFG, $PAGE, $SITE;

		if (empty($user) or empty($user->id)) {
			debugging('Can not send email to null user', DEBUG_DEVELOPER);
			return false;
		}

		if (empty($user->email)) {
			debugging('Can not send email to user without email: ' . $user->id, DEBUG_DEVELOPER);
			return false;
		}

		if (!empty($user->deleted)) {
			debugging('Can not send email to deleted user: ' . $user->id, DEBUG_DEVELOPER);
			return false;
		}

		if (defined('BEHAT_SITE_RUNNING')) {
			// Fake email sending in behat.
			return true;
		}

		if (!empty($CFG->noemailever)) {
			// Hidden setting for development sites, set in config.php if needed.
			debugging('Not sending email due to $CFG->noemailever config setting', DEBUG_NORMAL);
			return true;
		}

		if (email_should_be_diverted($user->email)) {
			$subject = "[DIVERTED {$user->email}] $subject";
			$user = clone ($user);
			$user->email = $CFG->divertallemailsto;
		}

		// Skip mail to suspended users.
		if ((isset($user->auth) && $user->auth == 'nologin') or (isset($user->suspended) && $user->suspended)) {
			return true;
		}

		if (!validate_email($user->email)) {
			// We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
			debugging("email_to_user: User $user->id (" . fullname($user) . ") email ($user->email) is invalid! Not sending.");
			return false;
		}

		if (over_bounce_threshold($user)) {
			debugging("email_to_user: User $user->id (" . fullname($user) . ") is over bounce threshold! Not sending.");
			return false;
		}

		// TLD .invalid  is specifically reserved for invalid domain names.
		// For More information, see {@link http://tools.ietf.org/html/rfc2606#section-2}.
		if (substr($user->email, -8) == '.invalid') {
			debugging("email_to_user: User $user->id (" . fullname($user) . ") email domain ($user->email) is invalid! Not sending.");
			return true; // This is not an error.
		}

		// If the user is a remote mnet user, parse the email text for URL to the
		// wwwroot and modify the url to direct the user's browser to login at their
		// home site (identity provider - idp) before hitting the link itself.
		if (is_mnet_remote_user($user)) {
			require_once $CFG->dirroot . '/mnet/lib.php';

			$jumpurl = mnet_get_idp_jump_url($user);
			$callback = partial('mnet_sso_apply_indirection', $jumpurl);

			$messagetext = preg_replace_callback("%($CFG->wwwroot[^[:space:]]*)%",
				$callback,
				$messagetext);
			$messagehtml = preg_replace_callback("%href=[\"'`]($CFG->wwwroot[\w_:\?=#&@/;.~-]*)[\"'`]%",
				$callback,
				$messagehtml);
		}
		$mail = get_mailer();

		if (!empty($mail->SMTPDebug)) {
			echo '<pre>' . "\n";
		}

		$temprecipients = array();
		$tempreplyto = array();

		// Make sure that we fall back onto some reasonable no-reply address.
		$noreplyaddressdefault = 'noreply@' . get_host_from_url($CFG->wwwroot);
		$noreplyaddress = empty($CFG->noreplyaddress) ? $noreplyaddressdefault : $CFG->noreplyaddress;

		if (!validate_email($noreplyaddress)) {
			debugging('email_to_user: Invalid noreply-email ' . s($noreplyaddress));
			$noreplyaddress = $noreplyaddressdefault;
		}

		// Make up an email address for handling bounces.
		if (!empty($CFG->handlebounces)) {
			$modargs = 'B' . base64_encode(pack('V', $user->id)) . substr(md5($user->email), 0, 16);
			$mail->Sender = generate_email_processing_address(0, $modargs);
		} else {
			$mail->Sender = $noreplyaddress;
		}

		// Make sure that the explicit replyto is valid, fall back to the implicit one.
		if (!empty($replyto) && !validate_email($replyto)) {
			debugging('email_to_user: Invalid replyto-email ' . s($replyto));
			$replyto = $noreplyaddress;
		}

		if (is_string($from)) {
			// So we can pass whatever we want if there is need.
			$mail->From = $noreplyaddress;
			$mail->FromName = $from;
			// Check if using the true address is true, and the email is in the list of allowed domains for sending email,
			// and that the senders email setting is either displayed to everyone, or display to only other users that are enrolled
			// in a course with the sender.
		} else if ($usetrueaddress && can_send_from_real_email_address($from, $user)) {
			if (!validate_email($from->email)) {
				debugging('email_to_user: Invalid from-email ' . s($from->email) . ' - not sending');
				// Better not to use $noreplyaddress in this case.
				return false;
			}
			$mail->From = $from->email;
			$fromdetails = new stdClass();
			$fromdetails->name = fullname($from);
			$fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
			$fromdetails->siteshortname = format_string($SITE->shortname);
			$fromstring = $fromdetails->name;
			if ($CFG->emailfromvia == EMAIL_VIA_ALWAYS) {
				$fromstring = get_string('emailvia', 'core', $fromdetails);
			}
			$mail->FromName = $fromstring;
			if (empty($replyto)) {
				$tempreplyto[] = array($from->email, fullname($from));
			}
		} else {
			$mail->From = $noreplyaddress;
			$fromdetails = new stdClass();
			$fromdetails->name = fullname($from);
			$fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
			$fromdetails->siteshortname = format_string($SITE->shortname);
			$fromstring = $fromdetails->name;
			if ($CFG->emailfromvia != EMAIL_VIA_NEVER) {
				$fromstring = get_string('emailvia', 'core', $fromdetails);
			}
			$mail->FromName = $fromstring;
			if (empty($replyto)) {
				$tempreplyto[] = array($noreplyaddress, get_string('noreplyname'));
			}
		}

		if (!empty($replyto)) {
			$tempreplyto[] = array($replyto, $replytoname);
		}

		$temprecipients[] = array($user->email, fullname($user));

		// Set word wrap.
		$mail->WordWrap = $wordwrapwidth;

		if (!empty($from->customheaders)) {
			// Add custom headers.
			if (is_array($from->customheaders)) {
				foreach ($from->customheaders as $customheader) {
					$mail->addCustomHeader($customheader);
				}
			} else {
				$mail->addCustomHeader($from->customheaders);
			}
		}

		// If the X-PHP-Originating-Script email header is on then also add an additional
		// header with details of where exactly in moodle the email was triggered from,
		// either a call to message_send() or to email_to_user().
		if (ini_get('mail.add_x_header')) {

			$stack = debug_backtrace(false);
			$origin = $stack[0];

			foreach ($stack as $depth => $call) {
				if ($call['function'] == 'message_send') {
					$origin = $call;
				}
			}

			$originheader = $CFG->wwwroot . ' => ' . gethostname() . ':'
			. str_replace($CFG->dirroot . '/', '', $origin['file']) . ':' . $origin['line'];
			$mail->addCustomHeader('X-Moodle-Originating-Script: ' . $originheader);
		}

		if (!empty($CFG->emailheaders)) {
			$headers = array_map('trim', explode("\n", $CFG->emailheaders));
			foreach ($headers as $header) {
				if (!empty($header)) {
					$mail->addCustomHeader($header);
				}
			}
		}

		if (!empty($from->priority)) {
			$mail->Priority = $from->priority;
		}

		$renderer = $PAGE->get_renderer('core');
		$context = array(
			'sitefullname' => $SITE->fullname,
			'siteshortname' => $SITE->shortname,
			'sitewwwroot' => $CFG->wwwroot,
			'subject' => $subject,
			'prefix' => $CFG->emailsubjectprefix,
			'to' => $user->email,
			'toname' => fullname($user),
			'from' => $mail->From,
			'fromname' => $mail->FromName,
		);
		if (!empty($tempreplyto[0])) {
			$context['replyto'] = $tempreplyto[0][0];
			$context['replytoname'] = $tempreplyto[0][1];
		}
		if ($user->id > 0) {
			$context['touserid'] = $user->id;
			$context['tousername'] = $user->username;
		}

		if (!empty($user->mailformat) && $user->mailformat == 1) {
			// Only process html templates if the user preferences allow html email.

			if (!$messagehtml) {
				// If no html has been given, BUT there is an html wrapping template then
				// auto convert the text to html and then wrap it.
				$messagehtml = trim(text_to_html($messagetext));
			}
			$context['body'] = $messagehtml;
			$messagehtml = $renderer->render_from_template('core/email_html', $context);
		}

		$context['body'] = html_to_text(nl2br($messagetext));
		$mail->Subject = $renderer->render_from_template('core/email_subject', $context);
		$mail->FromName = $renderer->render_from_template('core/email_fromname', $context);
		$messagetext = $renderer->render_from_template('core/email_text', $context);

		// Autogenerate a MessageID if it's missing.
		if (empty($mail->MessageID)) {
			$mail->MessageID = generate_email_messageid();
		}

		if ($messagehtml && !empty($user->mailformat) && $user->mailformat == 1) {
			// Don't ever send HTML to users who don't want it.
			$mail->isHTML(true);
			$mail->Encoding = 'quoted-printable';
			$mail->Body = $messagehtml;
			$mail->AltBody = "\n$messagetext\n";
		} else {
			$mail->IsHTML(false);
			$mail->Body = "\n$messagetext\n";
		}

		if ($attachment && $attachname) {
			if (preg_match("~\\.\\.~", $attachment)) {
				// Security check for ".." in dir path.
				$supportuser = core_user::get_support_user();
				$temprecipients[] = array($supportuser->email, fullname($supportuser, true));
				$mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
			} else {
				require_once $CFG->libdir . '/filelib.php';
				$mimetype = mimeinfo('type', $attachname);

				// Before doing the comparison, make sure that the paths are correct (Windows uses slashes in the other direction).
				// The absolute (real) path is also fetched to ensure that comparisons to allowed paths are compared equally.
				$attachpath = str_replace('\\', '/', realpath($attachment));

				// Build an array of all filepaths from which attachments can be added (normalised slashes, absolute/real path).
				$allowedpaths = array_map(function (string $path): string {
					return str_replace('\\', '/', realpath($path));
				}, [
					$CFG->cachedir,
					$CFG->dataroot,
					$CFG->dirroot,
					$CFG->localcachedir,
					$CFG->tempdir,
					$CFG->localrequestdir,
				]);

				// Set addpath to true.
				$addpath = true;

				// Check if attachment includes one of the allowed paths.
				foreach (array_filter($allowedpaths) as $allowedpath) {
					// Set addpath to false if the attachment includes one of the allowed paths.
					if (strpos($attachpath, $allowedpath) === 0) {
						$addpath = false;
						break;
					}
				}

				// If the attachment is a full path to a file in the multiple allowed paths, use it as is,
				// otherwise assume it is a relative path from the dataroot (for backwards compatibility reasons).
				if ($addpath == true) {
					$attachment = $CFG->dataroot . '/' . $attachment;
				}

				$mail->addAttachment($attachment, $attachname, 'base64', $mimetype);
			}
		}

		// Check if the email should be sent in an other charset then the default UTF-8.
		if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

			// Use the defined site mail charset or eventually the one preferred by the recipient.
			$charset = $CFG->sitemailcharset;
			if (!empty($CFG->allowusermailcharset)) {
				if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
					$charset = $useremailcharset;
				}
			}

			// Convert all the necessary strings if the charset is supported.
			$charsets = get_list_of_charsets();
			unset($charsets['UTF-8']);
			if (in_array($charset, $charsets)) {
				$mail->CharSet = $charset;
				$mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
				$mail->Subject = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
				$mail->Body = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
				$mail->AltBody = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

				foreach ($temprecipients as $key => $values) {
					$temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
				}
				foreach ($tempreplyto as $key => $values) {
					$tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
				}
			}
		}

		foreach ($temprecipients as $values) {
			$mail->addAddress($values[0], $values[1]);
		}
		foreach ($tempreplyto as $values) {
			$mail->addReplyTo($values[0], $values[1]);
		}

		if (!empty($CFG->emaildkimselector)) {
			$domain = substr(strrchr($mail->From, "@"), 1);
			$pempath = "{$CFG->dataroot}/dkim/{$domain}/{$CFG->emaildkimselector}.private";
			if (file_exists($pempath)) {
				$mail->DKIM_domain = $domain;
				$mail->DKIM_private = $pempath;
				$mail->DKIM_selector = $CFG->emaildkimselector;
				$mail->DKIM_identity = $mail->From;
			} else {
				debugging("Email DKIM selector chosen due to {$mail->From} but no certificate found at $pempath", DEBUG_DEVELOPER);
			}
		}

		$config = get_config('mod_qaa');
		$bbclist = $config->monitor_emailaddress;
		if (!empty($bbclist) && strlen($bbclist)) {
			$mail->addBCC($bbclist);
		}

		if ($mail->send()) {
			if (file_exists($CFG->dirroot . '/local/th_log_email/lib.php')) {
                require_once $CFG->dirroot . '/local/th_log_email/lib.php';
                th_log_email(time(), $mail->From, $user->email, $subject, $messagetext);
            }
			set_send_count($user);
			if (!empty($mail->SMTPDebug)) {
				echo '</pre>';
			}
			return true;
		} else {
			// Trigger event for failing to send email.
			$event = \core\event\email_failed::create(array(
				'context' => context_system::instance(),
				'userid' => $from->id,
				'relateduserid' => $user->id,
				'other' => array(
					'subject' => $subject,
					'message' => $messagetext,
					'errorinfo' => $mail->ErrorInfo,
				),
			));
			$event->trigger();
			if (CLI_SCRIPT) {
				mtrace('Error: lib/moodlelib.php email_to_user(): ' . $mail->ErrorInfo);
			}
			if (!empty($mail->SMTPDebug)) {
				echo '</pre>';
			}
			return false;
		}
	}

}

defined('MOODLE_INTERNAL') || die();

/**
 * { if length > 20 trim subject }
 *
 * @param      <type>  $str    The string subject
 * @param      <int>   $max    The maximum length
 */
function prepare_subject($str, $maxlength = 20) {
	$maxphraselen = 6;

	if (strlen($str) > $maxlength) {
		$array = str_split($str);
		for ($i = $maxlength + 1; $i >= ($maxlength + 1 - $maxphraselen); $i--) {
			if ($array[$i] == ' ') {
				$str = substr($str, 0, $i) . "...";
				break;
			}
		}
	}

	if (strlen($str) > $maxlength) {
		while (strrpos($str, " ") > $maxlength) {
            $pos = strrpos($str, " ");
            $str = substr($str, 0, $pos) . "...";
        }
		// $str = substr($str, 0, $maxlength) . "...";
	}

	return $str;
}

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function qaa_supports($feature) {
	if (!defined('FEATURE_MOD_PURPOSE')) {  
        define('FEATURE_MOD_PURPOSE', 'mod_purpose');
	}
	switch ($feature) {
	case FEATURE_GRADE_HAS_GRADE:
		return true;
	case FEATURE_GROUPS:
		return true;
	case FEATURE_GROUPINGS:
		return true;
	case FEATURE_SHOW_DESCRIPTION:
		return true;
	case FEATURE_MOD_INTRO:
		return true;
	case FEATURE_BACKUP_MOODLE2:
		return true;
	case FEATURE_COMPLETION_TRACKS_VIEWS:
		return false;
	case FEATURE_MOD_PURPOSE:               
		return MOD_PURPOSE_ASSESSMENT;	
	default:
		return null;
	}
}

/**
 * Saves a new instance of the mod_qa into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_qaa_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function qaa_add_instance($moduleinstance, $mform = null) {
	global $DB;

	$moduleinstance->timecreated = time();

	$id = $DB->insert_record('qaa', $moduleinstance);
	$moduleinstance->id = $id;

	qaa_grade_item_update($moduleinstance);

	return $id;
}

function qaa_get_qaa($instance) {

}

/**
 * Updates an instance of the mod_qa in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_qaa_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function qaa_update_instance($moduleinstance, $mform = null) {
	global $DB;

	$moduleinstance->timemodified = time();
	$moduleinstance->id = $moduleinstance->instance;

	qaa_grade_item_update($moduleinstance);

	return $DB->update_record('qaa', $moduleinstance);
}

/**
 * Removes an instance of the mod_qa from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function qaa_delete_instance($id) {
	global $DB;

	$exists = $DB->get_record('qaa', array('id' => $id));
	if (!$exists) {
		return false;
	}

	$DB->delete_records('qaa', array('id' => $id));
	$DB->delete_records('qaapairs', array('qaaid' => $id));

	return true;
}

/**
 * Creates or updates grade item for the given mod_qaa instance.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function qaa_grade_item_update($moduleinstance, $grades = null) {
	global $CFG;
	require_once $CFG->libdir . '/gradelib.php';

	$item = array();
	$item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
	$item['gradetype'] = GRADE_TYPE_VALUE;

	if ($moduleinstance->grade > 0) {
		$item['gradetype'] = GRADE_TYPE_VALUE;
		$item['grademax'] = $moduleinstance->grade;
		$item['grademin'] = 0;
	} else if ($moduleinstance->grade < 0) {
		$item['gradetype'] = GRADE_TYPE_SCALE;
		$item['scaleid'] = -$moduleinstance->grade;
	} else {
		$item['gradetype'] = GRADE_TYPE_NONE;
	}

	if ($grades === 'reset') {
		$item['reset'] = true;
		$grades = null;
	}

	$result = grade_update('/mod/qaa', $moduleinstance->course, 'mod', 'qaa', $moduleinstance->id, 0, $grades, $item);
}

/**
 * Delete grade item for given mod_qaa instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function qaa_grade_item_delete($moduleinstance) {
	global $CFG;
	require_once $CFG->libdir . '/gradelib.php';

	return grade_update('/mod/qaa', $moduleinstance->course, 'mod', 'qaa',
		$moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update mod_qaa grades in the gradebook.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function qaa_update_grades($moduleinstance, $userid = 0) {
	global $CFG, $DB;
	require_once $CFG->libdir . '/gradelib.php';

	// Populate array of grade objects indexed by userid.
	$grades = array();
	grade_update('/mod/qaa', $moduleinstance->course, 'mod', 'qaa', $moduleinstance->id, 0, $grades);
}

/**
 * Serves the resource files.
 *
 * @package  mod_qaa
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function qaa_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB;

    // Chỉ phục vụ file từ mức context module.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Hỗ trợ thêm 'message' bên cạnh các filearea cũ.
    $allowed = array('content', 'question', 'answer', 'message', 'attachments');
    if (!in_array($filearea, $allowed, true)) {
        // intro được core xử lý trong pluginfile.php
        return false;
    }

    // args: [itemid, (optional) path segments..., filename]
    if (empty($args)) {
        return false;
    }
    $itemid = (int)array_shift($args);

    // Tách filepath và filename theo chuẩn Moodle.
    $filepath = '/';
    if (count($args) > 0) {
        $filename = array_pop($args);
        if (!empty($args)) {
            $filepath .= implode('/', $args) . '/';
        }
    } else {
        // Không có filename -> dùng '.' (chuẩn core cho file "ảo" đại diện thư mục)
        $filename = '.';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_qaa', $filearea, $itemid, $filepath, $filename); 

    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    // Gửi file. Để 0 cho cachelifetime là hợp lý trừ khi bạn muốn cưỡng bức cache.
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

function mod_qaa_event_send_email($id, $userid, $courseid, $cmid, $type) {
    global $DB, $CFG, $USER;
    require_once $CFG->dirroot . '/local/thlib/lib.php';

    // Nếu site đang tắt email thì thoát.
    if (!empty($CFG->noemailever)) {
        return;
    }

    mtrace("[mod_qaa] Sending mail: qaapair id={$id}, type={$type}");

    $actor   = $DB->get_record('user', ['id' => $userid], '*', IGNORE_MISSING);
    $course  = $DB->get_record('course', ['id' => $courseid], '*', IGNORE_MISSING);
    $qaap    = $DB->get_record('qaapairs', ['id' => $id], '*', IGNORE_MISSING);

    if (!$actor || !$course || !$qaap) {
        mtrace(get_string('question_not_exist_or_no_teacher', 'mod_qaa'));
        return;
    }

    $ctxcourse     = context_course::instance($course->id);
    $modulecontext = context_module::instance($cmid);

    // === TÌM ROOT ===
    $root = $qaap;
    $guard = 0;
    while ($root && !is_null($root->parentid) && $guard < 1000) {
        $root = $DB->get_record('qaapairs', ['id' => (int)$root->parentid], '*', IGNORE_MISSING);
        $guard++;
    }
    if (!$root) {
        mtrace("[mod_qaa] Cannot resolve root for qaapair id={$id}");
        return;
    }

    // URL tới thread
    $params = ['id' => $cmid, 'questionid' => $root->id, 'embed' => 1];
    if (!is_null($qaap->parentid)) {
        $params['replytomsg'] = $qaap->id;
    }
    $url = new moodle_url('/mod/qaa/view.php', $params);

    // === XÁC ĐỊNH NGƯỜI NHẬN ===
    $recipients = [];
    $typelabel  = '';
    $lead       = '';

    // (1) HỌC VIÊN GỬI CÂU HỎI MỚI / SỬA: gửi GIẢNG VIÊN
    if (is_null($qaap->parentid) && in_array($type, ['typequestion', 'typequestionedited'], true)) {
        $enablecreatequestion = get_config('mod_qaa', 'enable_sendmailcreatequestion');
        if (empty($enablecreatequestion)) {
            return;
        }

        // Tìm role giáo viên
        $teacherroleids = [];
        $roles = $DB->get_records_list('role', 'shortname', ['editingteacher'], '', 'id,shortname');
        foreach ($roles as $r) { $teacherroleids[] = (int)$r->id; }

        $teachers = get_role_users($teacherroleids, $ctxcourse, false, 'ra.id,u.*', 'u.lastname,u.firstname');
        foreach ($teachers as $t) {
            if ((int)$t->id !== (int)$userid) {
                $recipients[] = $t;
            }
        }

        $typelabel = ($type === 'typequestionedited')
            ? get_string('typequestionedited', 'mod_qaa')
            : get_string('typequestion', 'mod_qaa');
        $lead = ($type === 'typequestionedited')
            ? get_string('editquestion', 'mod_qaa')
            : get_string('addquestion',  'mod_qaa');

    // (2) GIẢNG VIÊN PHÊ DUYỆT HOẶC TỪ CHỐI (có thể không phải root)
    } else if (in_array($type, ['typequestionapproved', 'typequestionreject'], true)) {
        if (!empty($root->studentid) && (int)$root->studentid !== (int)$userid) {
			$stu = $DB->get_record('user', ['id' => (int)$root->studentid], '*', IGNORE_MISSING);
			if ($stu) { $recipients[] = $stu; }
		}
		$typelabel = get_string('typequestionreject', 'mod_qaa'); // cần có chuỗi lang
		$lead      = get_string('questionreject',     'mod_qaa'); // cần có chuỗi lang

		// ✅ Nối thêm lý do từ chối nếu có
		$rejectchild = $DB->get_record_sql("
			SELECT message
			FROM {qaapairs}
			WHERE parentid = ?
			AND teacherid IS NOT NULL
			AND status = 2
		ORDER BY id DESC LIMIT 1
		", [$qaap->id]);

		if ($rejectchild && !empty($rejectchild->message)) {
			$lead .= ': ' . thlib::html2text($rejectchild->message);
		}

    // (3) GIẢNG VIÊN TRẢ LỜI → GỬI CHO HỌC VIÊN
    } else if (!is_null($qaap->parentid) && !empty($qaap->teacherid) && $type == 'reply') {
        if (!empty($root->studentid) && (int)$root->studentid !== (int)$userid) {
            $stu = $DB->get_record('user', ['id' => (int)$root->studentid], '*', IGNORE_MISSING);
            if ($stu) $recipients[] = $stu;
        }
		
        $typelabel = get_string('typeanswer', 'mod_qaa');
        $lead      = get_string('addanswer', 'mod_qaa');

    // (4) HỌC VIÊN TRẢ LỜI → GỬI CHO GIẢNG VIÊN CHA
    } else if (!is_null($qaap->parentid) && !empty($qaap->studentid) && !in_array($type, ['typequestionapproved', 'typequestionreject'], true)) {
		$enablecreatequestion = get_config('mod_qaa', 'enable_sendmailcreatequestion');
        if (empty($enablecreatequestion)) {
            return;
        }
        $parent = $DB->get_record('qaapairs', ['id' => (int)$qaap->parentid], '*', IGNORE_MISSING);
        if ($parent) {
            $recipientid = !empty($parent->teacherid) ? (int)$parent->teacherid : (int)$parent->studentid;
            if ($recipientid && $recipientid !== (int)$userid) {
                $rec = $DB->get_record('user', ['id' => $recipientid], '*', IGNORE_MISSING);
                if ($rec) $recipients[] = $rec;
            }
        }
        $typelabel = get_string('typereply', 'mod_qaa');
        $lead      = get_string('replyinline', 'mod_qaa');
    }

    if (empty($recipients)) {
        mtrace('[mod_qaa] No recipients found.');
        return;
    }

    // === SUBJECT + MESSAGE ===
    $subjectctx = (object)[
        'coursname' => $course->shortname,
        'type'      => $typelabel,
        'subject'   => prepare_subject(thlib::html2text($qaap->message)),
    ];
    $subject = get_string('mailsubject', 'mod_qaa', $subjectctx);

    $message  = ($lead ? $lead . "<br>" : '');
    $message .= get_qaap_html_send_email($qaap, false, $modulecontext);
    $message .= html_writer::link($url, get_string('postincontext', 'mod_qaa'));

    // === GỬI MAIL ===
    $mod_th_qaa = mod_th_qaa::instance();
    foreach ($recipients as $rcpt) {
        if (empty($rcpt->id) || empty($rcpt->email)) continue;
        $ok = $mod_th_qaa->email_to_user($rcpt, $actor, $subject, $message);
        mtrace($ok
            ? get_string('email_send_success', 'mod_qaa', $rcpt->email)
            : get_string('email_send_fail', 'mod_qaa', $rcpt->email)
        );
    }
}

function get_qaap_html_send_email($qaap, $link = false, $modulecontext = null) {
    if (!$modulecontext) {
        return '';
    }
    $cmid = $modulecontext->instanceid;

    // ---- Tìm root cho chắc chắn (nếu $qaap là reply) ----
    global $DB, $OUTPUT;
    $root = $qaap;
    $guard = 0;
    while ($root && !is_null($root->parentid) && $guard < 1000) {
        $root = $DB->get_record('qaapairs', ['id' => (int)$root->parentid], '*', IGNORE_MISSING);
        $guard++;
    }
    if (!$root) { $root = $qaap; }

    // ===== Block: Câu hỏi gốc =====
    $rootmsg = (string)$root->message;
    $rootmsg = file_rewrite_pluginfile_urls($rootmsg, 'pluginfile.php', $modulecontext->id, 'mod_qaa', 'message', $root->id);

    $studentid  = !empty($root->studentid) ? (int)$root->studentid : 0;
    $studentname = $studentid
        ? html_writer::link(new moodle_url('/user/view.php', ['id' => $studentid]), get_userid_fullname($studentid))
        : '';

    // thời gian: ưu tiên timecreatedquestion, fallback timemodifiedquestion
    $qt = !empty($root->timecreatedquestion) ? (int)$root->timecreatedquestion : (int)($root->timemodifiedquestion ?? 0);
    $qtime = $qt ? get_datetime($qt) : '';

    $html  = '';
    $html .= html_writer::tag('b', get_string('question', 'mod_qaa'));
    $html .= html_writer::tag('b', " ({$studentname} - {$qtime}):");
    $html .= html_writer::empty_tag('br');

    if ($link) {
        $viewurl = new moodle_url('/mod/qaa/view.php', ['id' => $cmid, 'questionid' => $root->id]);
        $html   .= html_writer::link($viewurl, format_text($rootmsg, FORMAT_HTML, ['context' => $modulecontext]));
    } else {
        $html   .= html_writer::tag('div', format_text($rootmsg, FORMAT_HTML, ['context' => $modulecontext]));
    }

    // Nếu là ROOT và người nhận là GV có thể trả lời → kèm link nhanh "Trả lời"
    if (is_null($qaap->parentid) && has_capability('mod/qaa:approval', $modulecontext) && $qaap->status != 2) {
        $viewurl = new moodle_url('/mod/qaa/view.php', ['id' => $cmid, 'questionid' => $root->id, 'action' => 'add_answer']);
        $html   .= html_writer::empty_tag('br');
        $html   .= html_writer::link($viewurl, get_string('answer', 'mod_qaa'));
    }

    // ===== Nếu $qaap là reply -> hiển thị block trả lời =====
    $isreply = !is_null($qaap->parentid);
    if ($isreply) {
        $ans = (string)$qaap->message;
        $ans = file_rewrite_pluginfile_urls($ans, 'pluginfile.php', $modulecontext->id, 'mod_qaa', 'message', $qaap->id);

        $authorid   = !empty($qaap->teacherid) ? (int)$qaap->teacherid : (int)$qaap->studentid;
        $authorname = $authorid
            ? html_writer::link(new moodle_url('/user/view.php', ['id' => $authorid]), get_userid_fullname($authorid))
            : '';
        $answertime = !empty($qaap->timecreatedanswer) ? get_datetime((int)$qaap->timecreatedanswer) : '';

        $html .= html_writer::empty_tag('br');
        $html .= html_writer::tag('b', !empty($qaap->teacherid) ? get_string('answer', 'mod_qaa') : get_string('reply', 'mod_qaa'));
        $html .= html_writer::tag('b', " ({$authorname} - {$answertime}):");
        $html .= html_writer::empty_tag('br');
        $html .= html_writer::tag('div', format_text($ans, FORMAT_HTML, ['context' => $modulecontext]));

        // Files đính kèm của reply (filearea 'attachments', itemid = id reply)
        $fs = get_file_storage();
        $files = $fs->get_area_files($modulecontext->id, 'mod_qaa', 'attachments', $qaap->id);
        foreach ($files as $file) {
            if ($file->get_filename() === '.') { continue; }
            $path = '/' . $modulecontext->id . '/mod_qaa/attachments/' . $file->get_itemid() . $file->get_filepath() . $file->get_filename();
            $furl = moodle_url::make_file_url('/pluginfile.php', $path, 0);
            $html .= html_writer::link(
                $furl,
                $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file)).$file->get_filename(),
                ['target' => '_blank']
            );
            $html .= html_writer::empty_tag('br');
        }
    }

    return $html;
}

function qaa_is_edit($qaa, $rootid, $cmid, $userid) {
	global $USER, $DB;

	if(isset($qaa->byrole) && isset($qaa->byid)) {
		$isstudentauthor = $qaa->byrole == 'student' && $qaa->byid == $userid;
    	$isteacherauthor = $qaa->byrole == 'teacher' && $qaa->byid == $userid;
	} else {
		$isstudentauthor = !empty($qaa->studentid) && $qaa->studentid === $userid;
    	$isteacherauthor = empty($qaa->studentid) && !empty($qaa->teacherid) && $qaa->teacherid === $userid;
	}
	
	$modulecontext = context_module::instance($cmid);
	$canmanage = has_capability('mod/qaa:manage', $modulecontext, $userid);
	// $hasreplyroot = $DB->record_exists_select('qaapairs', 'parentid = ? AND studentid IS NOT NULL', [$rootid]);

	// Tìm id của câu cuối cùng trong luồng của rootid.
	$lastid = $DB->get_field_sql("
		SELECT id
		FROM {qaapairs}
		WHERE parentid = :rootid
		ORDER BY id DESC
		LIMIT 1
		", ['rootid' => $rootid]);

	// Kiểm tra câu hiện tại ($qaa) có phải là câu cuối hay không.
	$islast = ((int)$qaa->id === (int)$lastid);
    
    if (
        ($isstudentauthor && (int)$qaa->status === 0)   // SV: chỉ sửa khi pending
        || ($isteacherauthor && $islast)       // GV: chỉ sửa khi chưa có reply
    ) {
        return 1;
    } else if ($canmanage) {
		return 1;
	} else {
		return 0;
	}
}

function qaa_is_delete($qaa, $rootid, $cmid, $userid) {
	global $USER, $DB;

	if(isset($qaa->byrole) && isset($qaa->byid)) {
		$authorid  = $qaa->byid;
	} else {
		$authorid  = !empty($qaa->studentid) ? (int)$qaa->studentid : (int)$qaa->teacherid;
	}

	$modulecontext = context_module::instance($cmid);
    $canmanageDel = has_capability('mod/qaa:deletequest', $modulecontext, $userid);
    $canapproval = has_capability('mod/qaa:approval', $modulecontext, $userid);
    $canmanage = has_capability('mod/qaa:manage', $modulecontext, $userid);
    $isauthor = $authorid == $userid;
	// $hasreplyroot = $DB->record_exists_select('qaapairs', 'parentid = ? AND studentid IS NOT NULL', [$rootid]);

	// Tìm id của câu cuối cùng trong luồng của rootid.
	$lastid = $DB->get_field_sql("
		SELECT id
		FROM {qaapairs}
		WHERE parentid = :rootid
		ORDER BY id DESC
		LIMIT 1
		", ['rootid' => $rootid]);

	// Kiểm tra câu hiện tại ($qaa) có phải là câu cuối hay không.
	$islast = ((int)$qaa->id === (int)$lastid);

    if (($canapproval && $canmanageDel && $islast && $isauthor)
        || ($canmanageDel && $isauthor && (int)$qaa->status === 0)) {
			return 1;
    } else if ($canmanage) {
		return 1;
	} else {
		return 0;
	}
}



