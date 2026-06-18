<?php
require_once $CFG->dirroot . '/config.php';
require_once $CFG->dirroot . '/lib/moodlelib.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';

defined('MOODLE_INTERNAL') || die();

class th_alert_forum {
    const MAIL = "datdt720@wru.vn";
}

/**
 * alert_qaa function Lấy ra các khóa học có cầu hỏi hoặc câu trả lời trong Hỏi/đáp trong khoảng thời gian
 *
 * @return void
 */
function alert_qaa() {

    global $DB, $CFG;

    $time = $DB->get_record('task_scheduled', array('component' => 'local_th_alert_forum'));
    $from = $time->lastruntime;
    $to = $time->nextruntime;
    if ($from == 0) {
        $from = $to - 3600 * 24 * 7;
    }
    // $from = 1630170000;
    mtrace(date('d/m/Y H:i:s', $from) . ' - ' . date('d/m/Y H:i:s', $to));
    $sql = "SELECT DISTINCT q.course
			FROM {qaa} q
            JOIN {qaapairs} qp ON q.id = qp.qaaid
            JOIN {course} c ON q.course = c.id AND c.visible = 1
			WHERE
			(timemodifiedquestion > ? AND timemodifiedquestion <= ?)
			OR (timemodifiedanswer > ? AND timemodifiedanswer <= ?)";
    $params = array($from, $to, $from, $to);
    $courses = $DB->get_records_sql($sql, $params);
    // print_object($courses);
    $messages = "";

    if (empty($courses)) {
        return;
    }
    foreach ($courses as $courseid => $course) {
        $course = $DB->get_record('course', array('id' => $courseid));
        $shortname = $course->shortname;
        $fullname = $course->fullname;
        $link = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid, $shortname . ': ' . $fullname);

        $sql = "SELECT cm.id
				FROM {modules} m JOIN {course_modules} cm ON m.id=cm.module
				WHERE m.name LIKE 'qaa' AND cm.course=?
				AND cm.deletioninprogress=0";
        $params = array($courseid);
        $cm = $DB->get_record_sql($sql, $params);
        // print_object($cm);

        $messages = $messages . $link . "<br>";
        $sql = "SELECT qp.*,q.course
				FROM {qaa} q JOIN {qaapairs} qp ON q.id = qp.qaaid
				WHERE course = ? AND
			((timemodifiedquestion > ? AND timemodifiedquestion <= ?)
			OR (timemodifiedanswer > ? AND timemodifiedanswer <= ?))";
        $params = array($courseid, $from, $to, $from, $to);
        $qaapairs = $DB->get_records_sql($sql, $params);
        // print_object($qaapairs);
        foreach ($qaapairs as $qaapairs_id => $qaapair) {
            $id = $qaapair->id;
            $qaaid = $qaapair->qaaid;
            $question = $qaapair->question;
            $studentid = $qaapair->studentid;
            $answer = $qaapair->answer;
            $teacherid = $qaapair->teacherid;
            $teacheridmodified = $qaapair->teacheridmodified;
            $timemodifiedquestion = $qaapair->timemodifiedquestion;
            $timemodifiedanswer = $qaapair->timemodifiedanswer;

            $qaap = new stdClass();
            $qaap->question = $question;
            $qaap->qaaid = $qaaid;
            $qaap->studentid = $studentid;
            $qaap->timemodifiedquestion = $timemodifiedquestion;
            $qaap->timemodifiedanswer = $timemodifiedanswer;
            $qaap->answer = $answer;
            $qaap->teacherid = $teacherid;
            $see_this_post_in_context = html_writer::link($CFG->wwwroot . '/mod/qaa/view.php?id=' . $cm->id . '&questionid=' . $id, get_string('postincontext', 'local_th_alert_forum'));

            $messages = $messages . get_qaap_html1($qaap) . $see_this_post_in_context . "<hr>";
        }
        $messages .= "<br>";
    }
    // echo $messages;

    $user = $DB->get_record('user', array('email' => th_alert_forum::MAIL));
    // print_object($user);
    $userfrom = \core_user::get_support_user();
    $send_ok = email_to_user1($user, $userfrom, get_string('mailsubject_hoi_dap', 'local_th_alert_forum'), $messages);
    if ($send_ok == true) {
        echo "send_ok";
    } else {
        echo "send_fail";
    }
}

function get_qaap_html1($qaap, $link = false) {
    $cmid = optional_param('id', 0, PARAM_INT);
    $question = $qaap->question;
    $answer = $qaap->answer;
    $timemodifiedquestion = $qaap->timemodifiedquestion;

    $studentid = $qaap->studentid;
    $studentname = get_userid_fullname($studentid);
    $studentname = html_writer::link(new moodle_url('/user/view.php', array('id' => $studentid)),
        $studentname);

    $content_answered = "";
    $content_answered .= html_writer::tag('b', get_string('question', 'mod_qaa'));
    $bold = html_writer::tag('b', " (" . $studentname . ' - ' . get_datetime($timemodifiedquestion) . "):");
    $content_answered .= $bold;

    $content_answered .= "<br>";

    if ($link) {
        $viewurl = new moodle_url('/mod/qaa/view.php', array('id' => $cmid, 'questionid' => $qaap->id));
        $question = html_writer::link($viewurl, $qaap->question);
    }

    $content_answered .= html_writer::tag('div', $question);
    // $content_answered .= "<br>";
    if ($answer) {
        $teacherid = $qaap->teacherid;
        $teachername = get_userid_fullname($teacherid);
        $teachername = html_writer::link(new moodle_url('/user/view.php', array('id' => $teacherid)),
            $teachername);

        $content_answered .= html_writer::tag('b', get_string('answer', 'mod_qaa'));
        $timeanswered = $qaap->timemodifiedanswer;
        $bold = html_writer::tag('b', " (" . $teachername . ' - ' . get_datetime($timeanswered) . "):");
        $content_answered .= $bold;

        $content_answered .= "<br>";
        $content_answered .= html_writer::tag('div', $answer);
    }
    return $content_answered;
}

/**
 * alert_forum function Lấy ra các khóa học có cầu hỏi hoặc câu trả lời trong Forum trong khoảng thời gian
 *
 * @return void
 */
function alert_forum() {
    global $DB, $CFG;

    $time = $DB->get_record('task_scheduled', array('component' => 'local_th_alert_forum'));
    $from = $time->lastruntime;
    $to = $time->nextruntime;
    if ($from == 0) {
        $from = $to - 3600 * 24 * 7;
    }
    // $from = 1630170000;

    mtrace(date('d/m/Y H:i:s', $from) . ' - ' . date('d/m/Y H:i:s', $to));

    //lay chu de
    $sql = "SELECT fp.id,fp.discussion,fp.modified,fp.message,fp.parent,fp.subject,fd.course,fd.userid,fd.forum
	   FROM {forum_discussions} fd
        INNER JOIN {forum_posts} fp ON fp.discussion=fd.id
        INNER JOIN {course} c ON c.id=fd.course
	   WHERE fp.parent = 0 AND fd.timemodified > ? AND fd.timemodified <= ? AND c.visible = 1";
    $discussions = $DB->get_records_sql($sql, [$from, $to]);

    // print_object('$discussions');
    // print_object($discussions);
    $messages = "";

    if (empty($discussions)) {
        return;
    }

    // lấy các câu trả lời
    foreach ($discussions as $key => $discussion) {

        $courseid = $discussion->course;
        $course = $DB->get_record('course', array('id' => $courseid));
        $shortname = $course->shortname;
        $objectid = $discussion->discussion;
        $subject = $discussion->subject;
        $userid = $discussion->userid;
        $userfrom = $DB->get_record('user', array('id' => $userid));
        $time = $discussion->modified;
        $message = $discussion->message;
        $parent = $discussion->parent;
        $forum_postid = $discussion->id;

        $link = html_writer::link($CFG->wwwroot . '/course/view.php?id=' . $courseid, $shortname);
        $link .= " » ";
        $link .= html_writer::link($CFG->wwwroot . '/mod/forum/index.php?id=' . $courseid, "Các diễn đàn");
        $link .= " » ";
        $link .= html_writer::link($CFG->wwwroot . '/mod/forum/view.php?f=' . $discussion->forum, "Diễn đàn tin tức");
        $link .= " » ";
        $link .= html_writer::link($CFG->wwwroot . '/mod/forum/discuss.php?d=' . $objectid, $subject);

        $topic = "<table border='0' cellpadding='3' cellspacing='0'>
		    <tbody>
		    <tr>
		        <td width='35' valign='top'>
		            <a href='" . $CFG->wwwroot . "/user/view.php?id=" . $userid . "&amp;course=" . $courseid . "' target='_blank'><img src='https://secure.gravatar.com/avatar/99200137f6b23954ea217439151277fa?s=100&d=mm' width='35' height='35' alt='Picture of " . fullname($userfrom) . "' title='Picture of " . fullname($userfrom) . "' class='CToWUd'></a>
		        </td>
		        <td>
		            <div>
		                " . $subject . "
		            </div>
		            <div>
		                by <a href='" . $CFG->wwwroot . "/user/view.php?id=" . $userid . "&amp;course=" . $courseid . "' target='_blank'>" . fullname($userfrom) . "</a> - " . date('l, d F Y, g:i A', $time) . "
		            </div>
		        </td>
		    </tr>
		    <tr>
		        <td valign='top'>
		                &nbsp;
		        </td>
		        <td>
		            <p dir='ltr' style='text-align:left'></p><p>" . $message . "</p>
		            <div>
		                    <a href='" . $CFG->wwwroot . "/mod/forum/post.php?reply=" . $forum_postid . "' target='_blank'>
		                        Reply
		                    </a>
		            </div>
		            <div>
		                <a href='" . $CFG->wwwroot . "/mod/forum/discuss.php?d=" . $objectid . "#p" . $forum_postid . "' target='_blank'>
		                    See this post in context
		                </a>
		            </div>
		        </td>
		    </tr>
		</tbody></table><hr>";

        $content = $link . $topic;
        $messages = $messages . $content;
        $replys = "";
        $forum_post = $DB->get_records_sql("SELECT * FROM {forum_posts} WHERE parent != 0 AND discussion = ? AND modified > ? AND modified <= ?", [$discussion->discussion, $from, $to]);
        //print_object("$forum_post");
        // print_object($forum_post);
        foreach ($forum_post as $key => $reply) {
            $userid = $reply->userid;
            $time = $reply->modified;
            $userfrom = $DB->get_record('user', array('id' => $userid));
            $subject = $reply->subject;
            $discussionid = $reply->discussion;
            $message = $reply->message;
            $parent = $reply->parent;
            $forum_postid = $reply->id;
            $reply = "<table border='0' cellpadding='3' cellspacing='0'>
		    <tbody><tr>
		        <td width='35' valign='top'>
		            <a href='" . $CFG->wwwroot . "/user/view.php?id=" . $userid . "&amp;course=" . $courseid . "' target='_blank'><img src='https://secure.gravatar.com/avatar/99200137f6b23954ea217439151277fa?s=100&d=mm' width='35' height='35' alt='Picture of " . fullname($userfrom) . "' title='Picture of " . fullname($userfrom) . "' class='CToWUd'></a>
		        </td>
		        <td>
		            <div>
		                " . $subject . "
		            </div>
		            <div>
		                by <a href='" . $CFG->wwwroot . "/user/view.php?id=" . $userid . "&amp;course=" . $courseid . "' target='_blank'>" . fullname($userfrom) . "</a> - " . date('l, d F Y, g:i A', $time) . "
		            </div>
		        </td>
		    </tr>
		    <tr>
		        <td valign='top'>
		                &nbsp;
		        </td>
		        <td>
		            <p dir='ltr' style='text-align:left'></p><p>" . $message . "</p>
		            <div>
		                    <a href='" . $CFG->wwwroot . "/mod/forum/post.php?reply=" . $forum_postid . "' target='_blank'>
		                        Reply
		                    </a>
		            </div>
		            <div>
		                <a href='" . $CFG->wwwroot . "/mod/forum/discuss.php?d=" . $discussionid . "#p" . $forum_postid . "' target='_blank'>
		                    See this post in context
		                </a>
		            </div>
		        </td>
		    </tr>
		</tbody>
		</table>
		<hr>";
            $replys = $replys . $reply;
        }
        $messages = $messages . $replys;
    }
    // echo $messages;
    //$config = get_config("local_th_alert_forum");
    $user = $DB->get_record('user', array('email' => th_alert_forum::MAIL));
    $userfrom = \core_user::get_support_user();
    $send_ok = email_to_user1($user, $userfrom, get_string('taskname', 'local_th_alert_forum'), $messages);
    if ($send_ok == true) {
        echo "send_ok";
    } else {
        echo "send_fail";
    }
}

//class local_th_alert_forum {
/**
 * Send an email to a specified user
 *
 * @param stdClass $user  A {@link $USER} object
 * @param stdClass $from A {@link $USER} object
 * @param string $subject plain text subject line of the email
 * @param string $messagetext plain text version of the message
 * @param string $messagehtml complete html version of the message (optional)
 * @param string $attachment a file on the filesystem, either relative to $CFG->dataroot or a full path to a file in one of
 *          the following directories: $CFG->cachedir, $CFG->dataroot, $CFG->dirroot, $CFG->localcachedir, $CFG->tempdir
 * @param string $attachname the name of the file (extension indicates MIME)
 * @param bool $usetrueaddress determines whether $from email address should
 *          be sent out. Will be overruled by user profile setting for maildisplay
 * @param string $replyto Email address to reply to
 * @param string $replytoname Name of reply to recipient
 * @param int $wordwrapwidth custom word wrap width, default 79
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function email_to_user1($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) {

    global $CFG, $PAGE, $SITE;
    $config = get_config("local_th_alert_forum");

    if (empty($user) or empty($user->id)) {
        debugging(get_string('cannot_send_email_null_user', 'local_th_alert_forum'), DEBUG_DEVELOPER);
        return false;
    }

    if (empty($user->email)) {
        debugging(get_string('cannot_send_email_no_email', 'local_th_alert_forum', $user->id), DEBUG_DEVELOPER);
        return false;
    }

    if (!empty($user->deleted)) {
        debugging(get_string('cannot_send_email_deleted_user', 'local_th_alert_forum', $user->id), DEBUG_DEVELOPER);
        return false;
    }

    if (defined('BEHAT_SITE_RUNNING')) {
        // Fake email sending in behat.
        return true;
    }

    if (!empty($CFG->noemailever)) {
        // Hidden setting for development sites, set in config.php if needed.
        debugging(get_string('not_sending_email_noemailever', 'local_th_alert_forum'), DEBUG_NORMAL);
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
        debugging(get_string('email_to_user_invalid', 'local_th_alert_forum', [
            'id' => $user->id,
            'fullname' => fullname($user),
            'email' => $user->email
        ]), DEBUG_DEVELOPER);        
        return false;
    }

    if (over_bounce_threshold($user)) {
        debugging(get_string('email_to_user_bounce', 'local_th_alert_forum', [
            'id' => $user->id,
            'fullname' => fullname($user)
        ]), DEBUG_DEVELOPER);        
        return false;
    }

    // TLD .invalid  is specifically reserved for invalid domain names.
    // For More information, see {@link http://tools.ietf.org/html/rfc2606#section-2}.
    if (substr($user->email, -8) == '.invalid') {
        debugging(get_string('email_to_user_invalid_domain', 'local_th_alert_forum', [
            'id' => $user->id,
            'fullname' => fullname($user),
            'email' => $user->email
        ]), DEBUG_DEVELOPER);        
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
        debugging(get_string('invalid_noreply_email', 'local_th_alert_forum', [
            'noreply' => s($noreplyaddress)
        ]), DEBUG_DEVELOPER);        
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
        debugging(get_string('invalid_replyto_email', 'local_th_alert_forum', [
            'replyto' => s($replyto)
        ]), DEBUG_DEVELOPER);        
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
            debugging(get_string('invalid_from_email', 'local_th_alert_forum', [
                'email' => s($from->email)
            ]), DEBUG_DEVELOPER);            
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

            // Add allowed paths to an array (also check if it's not empty).
            $allowedpaths = array_filter([
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
            foreach ($allowedpaths as $allowedpath) {
                // Make sure both variables are normalised before comparing.
                $allowedpath = str_replace('\\', '/', realpath($allowedpath));
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

    // foreach ($temprecipients as $values) {
    //     $mail->addAddress($values[0], $values[1]);
    // }

    $emails = $config->email;
    $emails_arr = explode(',', $emails);
    foreach ($emails_arr as $emailaddr) {
        $ea = trim($emailaddr);
        if ($ea != "") {
            $mail->addAddress($ea);
        }
        print_object($ea);
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
            debugging(get_string('email_dkim_no_cert', 'local_th_alert_forum', [
                'from' => $mail->From,
                'pempath' => $pempath
            ]), DEBUG_DEVELOPER);            
        }
    }

    if ($mail->send()) {
        if (file_exists($CFG->dirroot . '/local/th_log_email/lib.php')) {
            require_once $CFG->dirroot . '/local/th_log_email/lib.php';
            th_log_email(time(), $from->email, $user->email, $subject, $messagetext);
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
            mtrace(get_string('email_to_user_error', 'local_th_alert_forum', $mail->ErrorInfo));
        }        
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return false;
    }
}
