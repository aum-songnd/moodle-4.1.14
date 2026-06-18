<?php

defined('MOODLE_INTERNAL') || die();

define('LOCALth_random_test_enrolment_HINT', 'hint');
define('LOCALth_random_test_enrolment_ENROLUSERS', 'enrolusers');
define('LOCALth_random_test_enrolment_GROUPINFOS', 'groupinfos');

require_once $CFG->dirroot . '/enrol/locallib.php';

/**
 * Check list of submitted user mails and creates a data structure for displaying information on the confirm page and
 * for performing the th_random_test_enrolment.
 *
 * @param string $emailstextfield Text field value to be checked for emails and course groups.
 * @param int $courseid ID of the course, used to determine the context for checking whether a user is already enroled.
 *
 * @return stdClass Object containing information to be displayed on confirm page and being used for th_random_test_enrolment.
 */
function local_th_random_test_enrolment_check_user_mails($emailstextfield, $courseid)
{

    $checkedemails = new stdClass();
    $checkedemails->emails_to_ignore = array();
    $checkedemails->error_messages = array();
    $checkedemails->moodleusers_for_email = array();
    $checkedemails->course_groups = array();
    $checkedemails->user_groups = array();
    $checkedemails->user_enroled = array();
    $checkedemails->validemailfound = 0;

    $emaildelimiters = array(', ', ' ', ',');

    if (!empty($emailstextfield)) {

        $emailslines = local_th_random_test_enrolment_parse_emails($emailstextfield, $courseid);

        $linecnt = 0;

        $currentgroup = '';

        $context = null;

        if (!empty($courseid)) {
            $context = context_course::instance($courseid, MUST_EXIST);
        }

        // Process emails from textfield.
        foreach ($emailslines as $emailline) {
            $linecnt++;

            $error = '';

            $emailline = trim($emailline);

            // Check for course group.
            $grouppos = strpos($emailline, '#');
            if ($grouppos !== false) {

                $groupname = substr($emailline, $grouppos + 1);
                $currentgroup = trim($groupname);
                $checkedemails->course_groups[$currentgroup] = array();
                continue;
            }

            // Check number of emails in current row/line.
            $emailsinlinecnt = substr_count($emailline, '@');

            // No email in row/line.
            if ($emailsinlinecnt == 0) {

                $a = new stdClass();
                $a->line = $linecnt;
                $a->content = $emailline;
                $error = get_string('error_no_email', 'local_th_random_test_enrolment', $a);
                $checkedemails->error_messages[$linecnt] = $error;

                // One email in row/line.
            } else if ($emailsinlinecnt == 1) {
                $email = $emailline;
                local_th_random_test_enrolment_check_email($email, $linecnt, $courseid, $context, $currentgroup, $checkedemails);
            }
            // More than one email in row/line.
            if ($emailsinlinecnt > 1) {
                $delimiter = '';

                // Check delimiters.
                foreach ($emaildelimiters as $emaildelimiter) {
                    $pos = strpos($emailline, $emaildelimiter);
                    if ($pos) {
                        $delimiter = $emaildelimiter;
                        break;
                    }
                }
                if (!empty($delimiter)) {
                    $emailsinline = explode($delimiter, $emailline);

                    // Iterate emails in row/line.
                    foreach ($emailsinline as $emailinline) {

                        $email = trim($emailinline);
                        local_th_random_test_enrolment_check_email($email, $linecnt, $courseid, $context, $currentgroup, $checkedemails);
                    }
                }
            }
        }
    }

    return $checkedemails;
}

/**
 * Check list of submitted user_masinhvien and creates a data structure for displaying information on the confirm page and
 * for performing the th_random_test_enrolment.
 *
 * @param string $emailstextfield Text field value to be checked for user_masinhvien and course groups.
 * @param int $courseid ID of the course, used to determine the context for checking whether a user is already enroled.
 *
 * @return stdClass Object containing information to be displayed on confirm page and being used for th_random_test_enrolment.
 */
function local_th_random_test_enrolment_check_user_masinhvien($emailstextfield, $courseid)
{

    $checkedemails = new stdClass();
    $checkedemails->emails_to_ignore = array();
    $checkedemails->error_messages = array();
    $checkedemails->moodleusers_for_email = array();
    $checkedemails->course_groups = array();
    $checkedemails->user_groups = array();
    $checkedemails->user_enroled = array();
    $checkedemails->validemailfound = 0;

    $emaildelimiters = array(', ', ' ', ',');

    if (!empty($emailstextfield)) {

        $emailslines = local_th_random_test_enrolment_parse_emails($emailstextfield, $courseid);

        $linecnt = 0;

        $currentgroup = '';

        $context = null;

        if (!empty($courseid)) {
            $context = context_course::instance($courseid, MUST_EXIST);
        }

        // Process emails from textfield.
        foreach ($emailslines as $emailline) {
            $linecnt++;

            $error = '';

            $emailline = trim($emailline);

            // Check for course group.
            $grouppos = strpos($emailline, '#');
            if ($grouppos !== false) {

                $groupname = substr($emailline, $grouppos + 1);
                $currentgroup = trim($groupname);
                $checkedemails->course_groups[$currentgroup] = array();
                continue;
            }

            $email = $emailline;
            local_th_random_test_enrolment_check_masinhvien($email, $linecnt, $courseid, $context, $currentgroup, $checkedemails);
        }
    }

    return $checkedemails;
}

/**
 *
 * Check submitted email, working on the $checkedemails array
 *
 * @param string $email e-mail of the user that should be enroled
 * @param int $linecnt line counter used for error messages
 * @param int $courseid course id
 * @param context_course $context context instance of the course the user should be enroled into
 * @param string $currentgroup name of the group a user should be added to as member
 * @param object $checkedemails Object containing information to be displayed on confirm page and being used for th_random_test_enrolment.
 */
function local_th_random_test_enrolment_check_email($email, $linecnt, $courseid, $context, $currentgroup, &$checkedemails)
{
    // Check for valid email.
    $emailisvalid = validate_email($email);
    // Email is not valid.
    if (!$emailisvalid) {
        $checkedemails->emails_to_ignore[] = $email;
        $a = new stdClass();
        $a->row = $linecnt;
        $a->email = $email;
        $error = get_string('error_invalid_email', 'local_th_random_test_enrolment', $a);
        if (array_key_exists($linecnt, $checkedemails->error_messages)) {
            $errors = $checkedemails->error_messages[$linecnt];
            $errors .= "<br>" . $error;
            $checkedemails->error_messages[$linecnt] = $errors;
        } else {
            $checkedemails->error_messages[$linecnt] = $error;
        }

        // Email is valid.
    } else {
        // Check for moodle user with email.
        list($error, $userrecord) = local_th_random_test_enrolment_get_user($email);
        if (!empty($error)) {
            $checkedemails->emails_to_ignore[] = $email;
            if (array_key_exists($linecnt, $checkedemails->error_messages)) {
                $errors = $checkedemails->error_messages[$linecnt];
                $errors .= "<br>" . $error;
                $checkedemails->error_messages[$linecnt] = $errors;
            } else {
                $checkedemails->error_messages[$linecnt] = $error;
            }
        } else if (!empty($userrecord) && !empty($userrecord->id)) {
            $checkedemails->validemailfound += 1;

            $useralreadyenroled = false;
            if (!empty($context) && !empty($userrecord)) {
                $useralreadyenroled = is_enrolled($context, $userrecord->id);
            }
            $checkedemails->moodleusers_for_email[$email] = $userrecord;
            if (empty($useralreadyenroled)) {
                $checkedemails->user_enroled[$email] = $userrecord;
            }
            if (!empty($currentgroup) && array_key_exists($currentgroup, $checkedemails->course_groups)) {
                $checkedemails->course_groups[$currentgroup][] = $userrecord;
            }
            if (!array_key_exists($email, $checkedemails->user_groups)) {
                $checkedemails->user_groups[$email] = array();
            }
            if (!empty($currentgroup) && !array_key_exists($currentgroup, $checkedemails->user_groups[$email])) {
                // Check if user is already member of the group.
                $result = local_th_random_test_enrolment_is_already_member($courseid, $currentgroup, $userrecord->id);
                if (!empty($result->error)) {
                    $a = new stdClass();
                    $a->row = $linecnt;
                    $a->email = $email;
                    $a->groupname = $currentgroup;
                    $a->error = $result->error;
                    $error = get_string('error_check_is_already_member', 'local_th_random_test_enrolment', $a);
                    $checkedemails->error_messages[$linecnt] = $error;
                }
                $alreadymember = $result->already_member;
                // Compose group information.
                if (empty($alreadymember)) {
                    $groupinfo = html_writer::tag('span',
                        get_string('user_groups_yes', 'local_th_random_test_enrolment'),
                        array('class' => 'badge badge-secondary'));
                } else {
                    $groupinfo = html_writer::tag('span',
                        get_string('user_groups_already', 'local_th_random_test_enrolment'),
                        array('class' => 'badge badge-success'));
                }
                $checkedemails->user_groups[$email][] = $currentgroup . ': ' . $groupinfo;
            }
        }
    }
}

/**
 *
 * Check submitted user masinhvien, working on the $checkedemails array
 *
 * @param string $email user_masinhvien of the user that should be enroled
 * @param int $linecnt line counter used for error messages
 * @param int $courseid course id
 * @param context_course $context context instance of the course the user should be enroled into
 * @param string $currentgroup name of the group a user should be added to as member
 * @param object $checkedemails Object containing information to be displayed on confirm page and being used for th_random_test_enrolment.
 */
function local_th_random_test_enrolment_check_masinhvien($email, $linecnt, $courseid, $context, $currentgroup, &$checkedemails)
{

    // Check for moodle user with email.
    list($error, $userrecord) = local_th_random_test_enrolment_get_user_by_msv($email);
    if (!empty($error)) {
        $checkedemails->emails_to_ignore[] = $email;
        if (array_key_exists($linecnt, $checkedemails->error_messages)) {
            $errors = $checkedemails->error_messages[$linecnt];
            $errors .= "<br>" . $error;
            $checkedemails->error_messages[$linecnt] = $errors;
        } else {
            $checkedemails->error_messages[$linecnt] = $error;
        }
    } else if (!empty($userrecord) && !empty($userrecord->id)) {
        $checkedemails->validemailfound += 1;

        $useralreadyenroled = false;
        if (!empty($context) && !empty($userrecord)) {
            $useralreadyenroled = is_enrolled($context, $userrecord->id);
        }
        $checkedemails->moodleusers_for_email[$email] = $userrecord;
        if (empty($useralreadyenroled)) {
            $checkedemails->user_enroled[$email] = $userrecord;
        }
        if (!empty($currentgroup) && array_key_exists($currentgroup, $checkedemails->course_groups)) {
            $checkedemails->course_groups[$currentgroup][] = $userrecord;
        }
        if (!array_key_exists($email, $checkedemails->user_groups)) {
            $checkedemails->user_groups[$email] = array();
        }
        if (!empty($currentgroup) && !array_key_exists($currentgroup, $checkedemails->user_groups[$email])) {
            // Check if user is already member of the group.
            $result = local_th_random_test_enrolment_is_already_member($courseid, $currentgroup, $userrecord->id);
            if (!empty($result->error)) {
                $a = new stdClass();
                $a->row = $linecnt;
                $a->email = $email;
                $a->groupname = $currentgroup;
                $a->error = $result->error;
                $error = get_string('error_check_is_already_member', 'local_th_random_test_enrolment', $a);
                $checkedemails->error_messages[$linecnt] = $error;
            }
            $alreadymember = $result->already_member;
            // Compose group information.
            if (empty($alreadymember)) {
                $groupinfo = html_writer::tag('span',
                    get_string('user_groups_yes', 'local_th_random_test_enrolment'),
                    array('class' => 'badge badge-secondary'));
            } else {
                $groupinfo = html_writer::tag('span',
                    get_string('user_groups_already', 'local_th_random_test_enrolment'),
                    array('class' => 'badge badge-success'));
            }
            $checkedemails->user_groups[$email][] = $currentgroup . ': ' . $groupinfo;
        }
    }

}

/**
 * Takes input from text area containing a list of e-mail adresses (optionally group names starting with '#').
 * Returns an array representation of the input.
 *
 * @param mixed $emails input value of the text area.
 * @return string[] of e-emails and optional group names
 */
function local_th_random_test_enrolment_parse_emails($emails, $courseid)
{
    if (empty($emails)) {
        return array();
    } else {
        $rawlines = explode(PHP_EOL, $emails);
        $result = array();

        $groups = groups_get_all_groups($courseid);
        // print_object(!empty($groups));
        // print_object($groups);
        // exit;
        if (!empty($groups)){
            $main_groups = get_config('local_th_random_test_enrolment','groups');
            if (!empty($main_groups)){
                $main_groups = array_map('trim', explode(",", $main_groups));
                // print_object($main_groups);
                $g = array();
                foreach ($main_groups as $k => $main_group) {
                    $t = array();
                    foreach ($groups as $key => $group) {
                        if(preg_match("/{$main_group}/i", $group->name)) {
                           $t[] = $group->name;
                        }
                    }
                    if (!empty($t)) {
                        $g[$k] = $t;
                    }
                }

                $tmp = [];
                if (!empty($g)) {
                    foreach ($rawlines as $rawline) {
                        foreach ($g as $key => $v) {
                            $groupname = '#'.$v[rand(1,1000) % count($v)];
                            $tmp[$groupname][] = trim($rawline);
                        } 
                    }
                } 
            }
        }
    }
    
    if (!empty($tmp)){
        foreach ($tmp as $groupname => $e) {
            $result[] = trim($groupname);
            foreach ($e as $key => $e) {
                $result[] = $e;
            }
        }
    }
    return $result;
}

/**
 * Takes an e-mail and returns a moodle user record and error string (if occured).
 *
 * @param string $email E-mail used to search for a user
 * @return string,object[]
 */
function local_th_random_test_enrolment_get_user($email)
{
    global $DB;

    $error = null;
    $userrecord = null;

    if (empty($email)) {
        return array($error, $userrecord);
    } else {
        // Get user records for email.
        try {

            $userrecords = $DB->get_records('user', array('email' => $email));
            $count = count($userrecords);
            if (!empty($count)) {
                // More than one user with email -> ignore email and don't enrol users later!
                if ($count > 1) {
                    $error = get_string('error_more_than_one_record_for_email', 'local_th_random_test_enrolment', $email);
                } else {
                    $userrecord = current($userrecords);
                }
            } else {
                $error = get_string('error_no_record_found_for_email', 'local_th_random_test_enrolment', $email);
            }
        } catch (Exception $e) {
            $error = get_string('error_getting_user_for_email', 'local_th_random_test_enrolment', $email) . local_th_random_test_enrolment_get_exception_info($e);
        }

        return array($error, $userrecord);
    }
}

/**
 * Takes an e-mail and returns a moodle user record and error string (if occured).
 *
 * @param string $email user masinhvien used to search for a user
 * @return string,object[]
 */
function local_th_random_test_enrolment_get_user_by_msv($email)
{
    global $DB;

    $error = null;
    $userrecord = null;

    $config = get_config('local_thlib');
    $studentcodeshortname = trim($config->studentcodeshortname);

    $studentcodeshortname = explode(",", $studentcodeshortname);
    if (count($studentcodeshortname) > 0) {
        list($insql, $inparams) = $DB->get_in_or_equal($studentcodeshortname);
    } else {
        list($insql, $inparams) = $DB->get_in_or_equal(['']);
    }

    if (empty($email)) {
        return array($error, $userrecord);
    } else {
        // Get user records for email.
        try {

            $sql = "SELECT {user}.*
            from {user_info_field}, {user_info_data}, {user}
            where shortname $insql and {user_info_data}.fieldid = {user_info_field}.id and data = '$email' and
            {user}.id = {user_info_data}.userid";

            $userrecords = $DB->get_records_sql($sql, $inparams);

            $count = count($userrecords);
            if (!empty($count)) {
                // More than one user with email -> ignore email and don't enrol users later!
                if ($count > 1) {
                    $error = get_string('error_more_than_one_record_for_studentcode', 'local_th_random_test_enrolment', $email);
                } else {
                    $userrecord = current($userrecords);
                }
            } else {
                $error = get_string('error_no_record_found_for_studentcode', 'local_th_random_test_enrolment', $email);
            }
        } catch (Exception $e) {
            $error = get_string('error_getting_user_for_email', 'local_th_random_test_enrolment', $email) . local_th_random_test_enrolment_get_exception_info($e);
        }

        return array($error, $userrecord);
    }
}

/**
 * Get an understandable reason from an exception which happened during th_random_test_enrolment.
 *
 * @param object $e should be of instanceof Exception
 * @return string readable form of an exception
 */
function local_th_random_test_enrolment_get_exception_info($e)
{
    if (empty($e) || !($e instanceof Exception)) {
        return '';
    }

    return " " . get_string('error_exception_info', 'local_th_random_test_enrolment') . ": " . $e->getMessage() . " -> " . $e->getTraceAsString();
}

/**
 * Perform user enrolment into the course and optionally add users as member into course groups. Groups are created if necessary.
 *
 * @param string $localth_random_test_enrolmentkey
 * @return object
 */
function local_th_random_test_enrolment_users($localth_random_test_enrolmentkey, $localth_random_test_enrolmentrole)
{
    global $CFG, $DB, $SESSION, $PAGE, $COURSE;

    $course = $DB->get_record('course', ['id' => $COURSE->id]);
    $manager = new course_enrolment_manager($PAGE, $course);

    $instance = null;
    foreach ($manager->get_enrolment_instances() as $tempinstance) {
        if ($tempinstance->enrol == 'manual') {
            if ($instance === null) {
                $instance = $tempinstance;
                break;
            }
        }
    }

    $duration = $instance->enrolperiod;
    $timestart = time();
    $timeend = 0;
    if ($duration > 0) {
        $timeend = $timestart + $duration;
    }

    $error = '';
    $exceptionsmsg = array();

    if (!empty($localth_random_test_enrolmentkey)) {
        if (!empty($localth_random_test_enrolmentkey) && !empty($SESSION->local_th_random_test_enrolment) &&
            array_key_exists($localth_random_test_enrolmentkey, $SESSION->local_th_random_test_enrolment)) {
            $localth_random_test_enrolmentdata = $SESSION->local_th_random_test_enrolment[$localth_random_test_enrolmentkey];
             $groups = $localth_random_test_enrolmentdata->course_groups;

            // $campaignid = $SESSION->local_th_random_test_enrolment_campaignid[$localth_random_test_enrolmentkey];

            if (!empty($localth_random_test_enrolmentdata)) {
                $error = '';

                $courseid = 0;

                $tmpdata = explode('_', $localth_random_test_enrolmentkey);
                if (!empty($tmpdata)) {
                    $courseid = $tmpdata[0];
                }

                $userstoenrol = $localth_random_test_enrolmentdata->moodleusers_for_email;

                if (!empty($courseid) && !empty($userstoenrol)) {
                    try {
                        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

                        $enrolinstances = enrol_get_instances($course->id, false);

                        // Get enrolment for th_random_test_enrolment.
                        $th_random_test_enrolmentplugin = get_config('local_th_random_test_enrolment', 'enrolplugin');

                        // Check if string contains "enrol_".
                        if (strpos($th_random_test_enrolmentplugin, 'enrol_') === 0) {
                            // This is needed because enrol_get_plugin needs the string without the "enrol_".
                            $th_random_test_enrolmentplugin = substr($th_random_test_enrolmentplugin, 6);
                        }

                        $plugin = enrol_get_plugin($th_random_test_enrolmentplugin);

                        $enrolinstance = null;

                        foreach ($enrolinstances as $instance) {
                            // Check enrolment.
                            if ($th_random_test_enrolmentplugin == $instance->enrol) {
                                if ($instance->status != ENROL_INSTANCE_ENABLED) {
                                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                                }
                                $enrolinstance = $instance;
                                break;
                            }
                        }

                        if (empty($enrolinstance)) {
                            $fields = $plugin->get_instance_defaults();
                            $id = $plugin->add_instance($course, $fields);

                            $enrolinstance = $DB->get_record('enrol', array('id' => $id));
                            $enrolinstance->expirynotify = $plugin->get_config('expirynotify');
                            $enrolinstance->expirythreshold = $plugin->get_config('expirythreshold');
                            $enrolinstance->roleid = $plugin->get_config('roleid');
                            $enrolinstance->timemodified = time();
                            $DB->update_record('enrol', $enrolinstance);
                        }

                        if (!empty($enrolinstance)) {
                            // Enrol users in course.
                            $roleid = $localth_random_test_enrolmentrole;

                            // Get the course context.
                            $coursecontext = context_course::instance($courseid);

                            foreach ($userstoenrol as $user) {
                                try {
                                    // Check if user is already enrolled with another enrolment method.
                                    $userisenrolled = is_enrolled($coursecontext, $user->id, '', false);

                                    // If the user is already enrolled, continue to avoid a second enrolment for the user.
                                    if ($userisenrolled) {
                                        continue;

                                        // Otherwise.
                                    } else {
                                        // here
                                        $plugin->enrol_user($enrolinstance, $user->id, $roleid, $timestart, $timeend);
                                        // if ($campaignid) {
                                        //     $user_campaign_course = new stdClass();
                                        //     $user_campaign_course->userid = $user->id;
                                        //     $user_campaign_course->courseid = $courseid;
                                        //     $user_campaign_course->campaignid = $campaignid;

                                        //     $DB->insert_record('user_campaign_course', $user_campaign_course, $returnid = true);
                                        // }
                                    }
                                } catch (Exception $e) {
                                    $a = new stdClass();
                                    $a->email = $user->email;

                                    $msg = get_string('error_enrol_user', 'local_th_random_test_enrolment', $a) .
                                    local_th_random_test_enrolment_get_exception_info($e);
                                    $exceptionsmsg[] = $msg;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $msg = get_string('error_enrol_users', 'local_th_random_test_enrolment') . local_th_random_test_enrolment_get_exception_info($e);
                        $exceptionsmsg[] = $msg;
                    }

                    // Check for course groups to create.
                    $groups = $localth_random_test_enrolmentdata->course_groups;

                    if (!empty($groups)) {

                        try {
                            require_once $CFG->dirroot . '/group/lib.php';

                            $existingcoursegroups = groups_get_all_groups($courseid);

                            foreach ($groups as $name => $members) {
                                $groupname = trim($name);

                                // Check if group already exists.
                                $groupid = null;
                                foreach ($existingcoursegroups as $key => $existingcoursegroup) {
                                    if ($groupname == $existingcoursegroup->name) {
                                        $groupid = $existingcoursegroup->id;
                                        break;
                                    }
                                }
                                // Group not found in course -> create new course group.
                                if (empty($groupid)) {
                                    $groupdata = new stdClass();
                                    $groupdata->courseid = $courseid;
                                    $groupdata->name = $groupname;
                                    $groupid = groups_create_group($groupdata, false, false);
                                }
                                if (!empty($groupid) && !empty($members)) {
                                    foreach ($members as $key => $member) {
                                        try {
                                            $useradded = groups_add_member($groupid, $member->id);

                                            if (empty($useradded)) {
                                                $a = new stdClass();
                                                $a->email = $member->email;
                                                $a->group = $groupname;
                                                $msg = get_string('error_group_add_member', 'local_th_random_test_enrolment', $a);
                                                $exceptionsmsg[] = $msg;
                                            }
                                        } catch (Exception $e) {
                                            $a = new stdClass();
                                            $a->email = $member->email;
                                            $a->group = $groupname;
                                            $msg = get_string('error_group_add_member', 'local_th_random_test_enrolment', $a) .
                                            local_th_random_test_enrolment_get_exception_info($e);
                                            $exceptionsmsg[] = $msg;
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $msg = get_string('error_group_add_members', 'local_th_random_test_enrolment') . local_th_random_test_enrolment_get_exception_info($e);
                            $exceptionsmsg[] = $msg;
                        }
                    }
                }
            }
        }
    }

    $retval = new stdClass();
    $retval->status = '';
    $retval->text = '';

    if (!empty($error) || !empty($exceptionsmsg)) {
        $retval->status = 'error';

        if (!empty($error)) {
            $msg = get_string($error, 'local_th_random_test_enrolment');
            $retval->text = $msg;
        }

        if (!empty($exceptionsmsg)) {
            if (!empty($error)) {
                $retval->text .= '<br>';
            }
            $retval->text .= implode('<br>', $exceptionsmsg);
        }
    } else {
        $retval->status = 'success';
        $msg = get_string('enrol_users_successful', 'local_th_random_test_enrolment');
        $retval->text = $msg;
    }

    return $retval;
}

/**
 * Perform user unenrol into the course
 *
 * @param string $localth_random_test_enrolmentkey
 * @return object
 */
function local_th_bulkunenrol_users($localth_random_test_enrolmentkey)
{
    global $CFG, $DB, $SESSION, $PAGE, $COURSE;

    $course = $DB->get_record('course', ['id' => $COURSE->id]);
    $manager = new course_enrolment_manager($PAGE, $course);

    $instance = null;
    foreach ($manager->get_enrolment_instances() as $tempinstance) {
        if ($tempinstance->enrol == 'manual') {
            if ($instance === null) {
                $instance = $tempinstance;
                break;
            }
        }
    }

    $duration = $instance->enrolperiod;
    $timestart = time();
    $timeend = 0;
    if ($duration > 0) {
        $timeend = $timestart + $duration;
    }

    $error = '';
    $exceptionsmsg = array();

    if (!empty($localth_random_test_enrolmentkey)) {
        if (!empty($localth_random_test_enrolmentkey) && !empty($SESSION->local_th_random_test_enrolment) &&
            array_key_exists($localth_random_test_enrolmentkey, $SESSION->local_th_random_test_enrolment)) {
            $localth_random_test_enrolmentdata = $SESSION->local_th_random_test_enrolment[$localth_random_test_enrolmentkey];

            if (!empty($localth_random_test_enrolmentdata)) {
                $error = '';

                $courseid = 0;

                $tmpdata = explode('_', $localth_random_test_enrolmentkey);
                if (!empty($tmpdata)) {
                    $courseid = $tmpdata[0];
                }

                $userstoenrol = $localth_random_test_enrolmentdata->moodleusers_for_email;

                if (!empty($courseid) && !empty($userstoenrol)) {
                    try {
                        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

                        $enrolinstances = enrol_get_instances($course->id, false);

                        // Get enrolment for th_random_test_enrolment.
                        $th_random_test_enrolmentplugin = get_config('local_th_random_test_enrolment', 'enrolplugin');

                        // Check if string contains "enrol_".
                        if (strpos($th_random_test_enrolmentplugin, 'enrol_') === 0) {
                            // This is needed because enrol_get_plugin needs the string without the "enrol_".
                            $th_random_test_enrolmentplugin = substr($th_random_test_enrolmentplugin, 6);
                        }

                        $plugin = enrol_get_plugin($th_random_test_enrolmentplugin);

                        $enrolinstance = null;

                        foreach ($enrolinstances as $instance) {
                            // Check enrolment.
                            if ($th_random_test_enrolmentplugin == $instance->enrol) {
                                if ($instance->status != ENROL_INSTANCE_ENABLED) {
                                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                                }
                                $enrolinstance = $instance;
                                break;
                            }
                        }

                        if (empty($enrolinstance)) {
                            $fields = $plugin->get_instance_defaults();
                            $id = $plugin->add_instance($course, $fields);

                            $enrolinstance = $DB->get_record('enrol', array('id' => $id));
                            $enrolinstance->expirynotify = $plugin->get_config('expirynotify');
                            $enrolinstance->expirythreshold = $plugin->get_config('expirythreshold');
                            $enrolinstance->roleid = $plugin->get_config('roleid');
                            $enrolinstance->timemodified = time();
                            $DB->update_record('enrol', $enrolinstance);
                        }

                        if (!empty($enrolinstance)) {
                            // Enrol users in course.
                            $roleid = get_config('local_th_random_test_enrolment', 'role');

                            // Get the course context.
                            $coursecontext = context_course::instance($courseid);

                            foreach ($userstoenrol as $user) {
                                try {
                                    // Check if user is already enrolled with another enrolment method.
                                    $userisenrolled = is_enrolled($coursecontext, $user->id, '', false);

                                    // If the user is already enrolled, continue to avoid a second enrolment for the user.
                                    if ($userisenrolled) {

                                        $enrolinstances = enrol_get_instances($course->id, false);
                                        foreach ($enrolinstances as $instance) {
                                            {
                                                if ($instance->status == ENROL_INSTANCE_ENABLED) {

                                                    $myplugin = $plugin = enrol_get_plugin($instance->enrol);
                                                    $plugin->unenrol_user($instance, $user->id);
                                                }
                                            }
                                        }

                                    } else {
                                        // $plugin->enrol_user($enrolinstance, $user->id, $roleid, $timestart, $timeend);
                                        continue;
                                    }
                                } catch (Exception $e) {
                                    $a = new stdClass();
                                    $a->email = $user->email;

                                    $msg = get_string('error_enrol_user', 'local_th_random_test_enrolment', $a) .
                                    local_th_random_test_enrolment_get_exception_info($e);
                                    $exceptionsmsg[] = $msg;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $msg = get_string('error_enrol_users', 'local_th_random_test_enrolment') . local_th_random_test_enrolment_get_exception_info($e);
                        $exceptionsmsg[] = $msg;
                    }

                }
            }
        }
    }

    $retval = new stdClass();
    $retval->status = '';
    $retval->text = '';

    if (!empty($error) || !empty($exceptionsmsg)) {
        $retval->status = 'error';

        if (!empty($error)) {
            $msg = get_string($error, 'local_th_random_test_enrolment');
            $retval->text = $msg;
        }

        if (!empty($exceptionsmsg)) {
            if (!empty($error)) {
                $retval->text .= '<br>';
            }
            $retval->text .= implode('<br>', $exceptionsmsg);
        }
    } else {
        $retval->status = 'success';
        $msg = get_string('unenrol_users_successful', 'local_th_random_test_enrolment');
        $retval->text = $msg;
    }

    return $retval;
}

/**
 * Perform user suspend into the course
 *
 * @param string $localth_random_test_enrolmentkey
 * @return object
 */
function local_th_bulksuspend_users($localth_random_test_enrolmentkey)
{
    global $CFG, $DB, $SESSION, $PAGE, $COURSE;

    $course = $DB->get_record('course', ['id' => $COURSE->id]);
    $manager = new course_enrolment_manager($PAGE, $course);

    $instance = null;
    foreach ($manager->get_enrolment_instances() as $tempinstance) {
        if ($tempinstance->enrol == 'manual') {
            if ($instance === null) {
                $instance = $tempinstance;
                break;
            }
        }
    }

    $duration = $instance->enrolperiod;
    $timestart = time();
    $timeend = 0;
    if ($duration > 0) {
        $timeend = $timestart + $duration;
    }

    $error = '';
    $exceptionsmsg = array();

    if (!empty($localth_random_test_enrolmentkey)) {
        if (!empty($localth_random_test_enrolmentkey) && !empty($SESSION->local_th_random_test_enrolment) &&
            array_key_exists($localth_random_test_enrolmentkey, $SESSION->local_th_random_test_enrolment)) {
            $localth_random_test_enrolmentdata = $SESSION->local_th_random_test_enrolment[$localth_random_test_enrolmentkey];
            if (!empty($localth_random_test_enrolmentdata)) {
                $error = '';

                $courseid = 0;

                $tmpdata = explode('_', $localth_random_test_enrolmentkey);
                if (!empty($tmpdata)) {
                    $courseid = $tmpdata[0];
                }

                $userstoenrol = $localth_random_test_enrolmentdata->moodleusers_for_email;

                if (!empty($courseid) && !empty($userstoenrol)) {
                    try {
                        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

                        $enrolinstances = enrol_get_instances($course->id, false);

                        // Get enrolment for th_random_test_enrolment.
                        $th_random_test_enrolmentplugin = get_config('local_th_random_test_enrolment', 'enrolplugin');

                        // Check if string contains "enrol_".
                        if (strpos($th_random_test_enrolmentplugin, 'enrol_') === 0) {
                            // This is needed because enrol_get_plugin needs the string without the "enrol_".
                            $th_random_test_enrolmentplugin = substr($th_random_test_enrolmentplugin, 6);
                        }

                        $plugin = enrol_get_plugin($th_random_test_enrolmentplugin);

                        $enrolinstance = null;

                        foreach ($enrolinstances as $instance) {
                            // Check enrolment.
                            if ($th_random_test_enrolmentplugin == $instance->enrol) {

                                if ($instance->status != ENROL_INSTANCE_ENABLED) {
                                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                                }
                                $enrolinstance = $instance;
                                break;
                            }
                        }

                        if (empty($enrolinstance)) {
                            $fields = $plugin->get_instance_defaults();
                            $id = $plugin->add_instance($course, $fields);

                            $enrolinstance = $DB->get_record('enrol', array('id' => $id));
                            $enrolinstance->expirynotify = $plugin->get_config('expirynotify');
                            $enrolinstance->expirythreshold = $plugin->get_config('expirythreshold');
                            $enrolinstance->roleid = $plugin->get_config('roleid');
                            $enrolinstance->timemodified = time();
                            $DB->update_record('enrol', $enrolinstance);
                        }

                        if (!empty($enrolinstance)) {
                            // Enrol users in course.
                            $roleid = get_config('local_th_random_test_enrolment', 'role');

                            // Get the course context.
                            $coursecontext = context_course::instance($courseid);

                            foreach ($userstoenrol as $user) {
                                try {
                                    // Check if user is already enrolled with another enrolment method.
                                    $userisenrolled = is_enrolled($coursecontext, $user->id, '', false);

                                    // If the user is already enrolled, continue to avoid a second enrolment for the user.
                                    if ($userisenrolled) {

                                        $enrolinstances = enrol_get_instances($course->id, false);
                                        foreach ($enrolinstances as $instance) {
                                            {
                                                if ($instance->status == ENROL_INSTANCE_ENABLED) {

                                                    $myplugin = $plugin = enrol_get_plugin($instance->enrol);
                                                    $plugin->update_user_enrol($instance, $user->id, ENROL_USER_SUSPENDED);
                                                }
                                            }
                                        }

                                    } else {
                                        // $plugin->enrol_user($enrolinstance, $user->id, $roleid, $timestart, $timeend);
                                        continue;
                                    }
                                } catch (Exception $e) {
                                    $a = new stdClass();
                                    $a->email = $user->email;

                                    $msg = get_string('error_enrol_user', 'local_th_random_test_enrolment', $a) .
                                    local_th_random_test_enrolment_get_exception_info($e);
                                    $exceptionsmsg[] = $msg;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $msg = get_string('error_enrol_users', 'local_th_random_test_enrolment') . local_th_random_test_enrolment_get_exception_info($e);
                        $exceptionsmsg[] = $msg;
                    }

                }
            }
        }
    }

    $retval = new stdClass();
    $retval->status = '';
    $retval->text = '';

    if (!empty($error) || !empty($exceptionsmsg)) {
        $retval->status = 'error';

        if (!empty($error)) {
            $msg = get_string($error, 'local_th_random_test_enrolment');
            $retval->text = $msg;
        }

        if (!empty($exceptionsmsg)) {
            if (!empty($error)) {
                $retval->text .= '<br>';
            }
            $retval->text .= implode('<br>', $exceptionsmsg);
        }
    } else {
        $retval->status = 'success';
        $msg = get_string('suspend_users_successful', 'local_th_random_test_enrolment');
        $retval->text = $msg;
    }

    return $retval;
}
/**
 * According to the parameter, either a table with hints is displayed or a table with users to be written is displayed.
 *
 * @param object $localth_random_test_enrolmentdata
 * @param string $key
 */
function local_th_random_test_enrolment_display_table($localth_random_test_enrolmentdata, $key)
{
    global $OUTPUT;

    if (!empty($localth_random_test_enrolmentdata) && !empty($key)) {

        switch ($key) {
            case LOCALth_random_test_enrolment_HINT:

                $data = array();

                if (!empty($localth_random_test_enrolmentdata->error_messages)) {
                    foreach ($localth_random_test_enrolmentdata->error_messages as $line => $errormessages) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $line;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $errormessages;
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_hints";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('hints', 'local_th_random_test_enrolment');
                $table->size = array('10%', '90%');
                $table->head = array();
                $table->head[] = get_string('row', 'local_th_random_test_enrolment');
                $table->head[] = get_string('hints', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('hints', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }

                break;

            case LOCALth_random_test_enrolment_ENROLUSERS:
                $data = array();

                if (!empty($localth_random_test_enrolmentdata->moodleusers_for_email)) {
                    foreach ($localth_random_test_enrolmentdata->moodleusers_for_email as $email => $user) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $user->email;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->firstname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->lastname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_enroled[$email])) {
                            $cell->text = html_writer::tag('span',
                                get_string('user_enroled_yes', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        } else {
                            $cell->text = html_writer::tag('span',
                                get_string('user_enroled_already', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        }
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_groups[$email])) {
                            $cell->text = implode(',<br />', $localth_random_test_enrolmentdata->user_groups[$email]);
                        }
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_enrolusers";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment');
                $table->size = array('20%', '17%', '17%', '20%', '26%');
                $table->head = array();
                $table->head[] = get_string('email');
                $table->head[] = get_string('firstname');
                $table->head[] = get_string('lastname');
                $table->head[] = get_string('user_enroled', 'local_th_random_test_enrolment');
                $table->head[] = get_string('user_groups', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            case LOCALth_random_test_enrolment_GROUPINFOS:
                $data = array();

                if (!empty($localth_random_test_enrolmentdata->course_groups)) {
                    $courseid = required_param('id', PARAM_INT);
                    $existingcoursegroups = groups_get_all_groups($courseid, 0, 0, 'id, name');

                    foreach ($localth_random_test_enrolmentdata->course_groups as $name => $members) {
                        $groupname = trim($name);

                        // Check if group already exists.
                        $groupexists = false;
                        foreach ($existingcoursegroups as $key => $existingcoursegroup) {
                            if ($groupname == $existingcoursegroup->name) {
                                $groupexists = true;
                                break;
                            }
                        }

                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $groupname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        if (empty($groupexists)) {
                            $cell->text = html_writer::tag('span',
                                get_string('group_status_create', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        } else {
                            $cell->text = html_writer::tag('span',
                                get_string('group_status_exists', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-success'));
                        }

                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_groupinfos";
                $table->attributes['class'] = 'generaltable';
                $table->size = array('50%', '50%');
                $table->head = array();
                $table->head[] = get_string('group_name_headline', 'local_th_random_test_enrolment');
                $table->head[] = get_string('group_status_headline', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('groupinfos_headline', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            default:
                break;
        }
    }
}

/**
 * According to the parameter, either a table with hints is displayed or a table with users to be written is displayed.
 *
 * @param object $localth_random_test_enrolmentdata
 * @param string $key
 */
function local_th_random_test_enrolment_display_table_mahocvien($localth_random_test_enrolmentdata, $key)
{
    global $OUTPUT;

    if (!empty($localth_random_test_enrolmentdata) && !empty($key)) {

        switch ($key) {
            case LOCALth_random_test_enrolment_HINT:

                $data = array();

                if (!empty($localth_random_test_enrolmentdata->error_messages)) {
                    foreach ($localth_random_test_enrolmentdata->error_messages as $line => $errormessages) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $line;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $errormessages;
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_hints";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('hints', 'local_th_random_test_enrolment');
                $table->size = array('10%', '90%');
                $table->head = array();
                $table->head[] = get_string('row', 'local_th_random_test_enrolment');
                $table->head[] = get_string('hints', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('hints', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }

                break;

            case LOCALth_random_test_enrolment_ENROLUSERS:
                $data = array();

                if (!empty($localth_random_test_enrolmentdata->moodleusers_for_email)) {
                    foreach ($localth_random_test_enrolmentdata->moodleusers_for_email as $email => $user) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $user->email;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->firstname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->lastname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_enroled[$email])) {
                            $cell->text = html_writer::tag('span',
                                get_string('user_enroled_yes', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        } else {
                            $cell->text = html_writer::tag('span',
                                get_string('user_enroled_already', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        }
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_groups[$email])) {
                            $cell->text = implode(',<br />', $localth_random_test_enrolmentdata->user_groups[$email]);
                        }
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_enrolusers";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment');
                $table->size = array('20%', '17%', '17%', '20%', '26%');
                $table->head = array();
                $table->head[] = get_string('studentcode', 'local_th_random_test_enrolment');
                $table->head[] = get_string('firstname');
                $table->head[] = get_string('lastname');
                $table->head[] = get_string('user_enroled', 'local_th_random_test_enrolment');
                $table->head[] = get_string('user_groups', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            case LOCALth_random_test_enrolment_GROUPINFOS:
                $data = array();

                if (!empty($localth_random_test_enrolmentdata->course_groups)) {
                    $courseid = required_param('id', PARAM_INT);
                    $existingcoursegroups = groups_get_all_groups($courseid, 0, 0, 'id, name');

                    foreach ($localth_random_test_enrolmentdata->course_groups as $name => $members) {
                        $groupname = trim($name);

                        // Check if group already exists.
                        $groupexists = false;
                        foreach ($existingcoursegroups as $key => $existingcoursegroup) {
                            if ($groupname == $existingcoursegroup->name) {
                                $groupexists = true;
                                break;
                            }
                        }

                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $groupname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        if (empty($groupexists)) {
                            $cell->text = html_writer::tag('span',
                                get_string('group_status_create', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        } else {
                            $cell->text = html_writer::tag('span',
                                get_string('group_status_exists', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-success'));
                        }

                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_groupinfos";
                $table->attributes['class'] = 'generaltable';
                $table->size = array('50%', '50%');
                $table->head = array();
                $table->head[] = get_string('group_name_headline', 'local_th_random_test_enrolment');
                $table->head[] = get_string('group_status_headline', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('groupinfos_headline', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            default:
                break;
        }
    }
}

/**
 * table unenrol
 *
 * @param object $localth_random_test_enrolmentdata
 * @param string $key
 */
function local_th_bulkunenrol_display_table($localth_random_test_enrolmentdata, $key)
{

    global $OUTPUT;

    if (!empty($localth_random_test_enrolmentdata) && !empty($key)) {

        switch ($key) {
            case LOCALth_random_test_enrolment_HINT:

                $data = array();

                if (!empty($localth_random_test_enrolmentdata->error_messages)) {
                    foreach ($localth_random_test_enrolmentdata->error_messages as $line => $errormessages) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $line;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $errormessages;
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_hints";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('hints', 'local_th_random_test_enrolment');
                $table->size = array('10%', '90%');
                $table->head = array();
                $table->head[] = get_string('row', 'local_th_random_test_enrolment');
                $table->head[] = get_string('hints', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('hints', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }

                break;

            case LOCALth_random_test_enrolment_ENROLUSERS:
                $data = array();

                if (!empty($localth_random_test_enrolmentdata->moodleusers_for_email)) {
                    foreach ($localth_random_test_enrolmentdata->moodleusers_for_email as $email => $user) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $user->email;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->firstname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->lastname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_enroled[$email])) {
                            $cell->text = html_writer::tag('span',
                                get_string('user_notincourse_already', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        } else {
                            $cell->text = html_writer::tag('span',
                                get_string('user_will_be_unenrolled', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        }
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_groups[$email])) {
                            $cell->text = implode(',<br />', $localth_random_test_enrolmentdata->user_groups[$email]);
                        }
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_enrolusers";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment');
                $table->size = array('20%', '17%', '17%', '20%', '26%');
                $table->head = array();
                $table->head[] = get_string('email');
                $table->head[] = get_string('firstname');
                $table->head[] = get_string('lastname');
                $table->head[] = get_string('user_enroled', 'local_th_random_test_enrolment');
                $table->head[] = get_string('user_groups', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            default:
                break;
        }
    }
}

/**
 * table suspend
 *
 * @param object $localth_random_test_enrolmentdata
 * @param string $key
 */
function local_th_bulksuspend_display_table($localth_random_test_enrolmentdata, $key)
{

    global $OUTPUT, $COURSE;

    $coursecontext = context_course::instance($COURSE->id);

    if (!empty($localth_random_test_enrolmentdata) && !empty($key)) {

        switch ($key) {
            case LOCALth_random_test_enrolment_HINT:

                $data = array();

                if (!empty($localth_random_test_enrolmentdata->error_messages)) {
                    foreach ($localth_random_test_enrolmentdata->error_messages as $line => $errormessages) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $line;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $errormessages;
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_hints";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('hints', 'local_th_random_test_enrolment');
                $table->size = array('10%', '90%');
                $table->head = array();
                $table->head[] = get_string('row', 'local_th_random_test_enrolment');
                $table->head[] = get_string('hints', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('hints', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }

                break;

            case LOCALth_random_test_enrolment_ENROLUSERS:
                $data = array();

                if (!empty($localth_random_test_enrolmentdata->moodleusers_for_email)) {
                    foreach ($localth_random_test_enrolmentdata->moodleusers_for_email as $email => $user) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $user->email;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->firstname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $user->lastname;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_enroled[$email])) {
                            $cell->text = html_writer::tag('span',
                                get_string('user_notincourse_already', 'local_th_random_test_enrolment'),
                                array('class' => 'badge badge-secondary'));
                        } else {

                            $userid = $user->id;

                            if (is_enrolled($coursecontext, $userid, '', true)) {
                                $cell->text = html_writer::tag('span',
                                    get_string('user_will_be_suspend', 'local_th_random_test_enrolment'),
                                    array('class' => 'badge badge-secondary'));
                                $localth_random_test_enrolmentdata->moodleusers_for_email[$email]->is_activeincourse = true;
                            } else {
                                $cell->text = html_writer::tag('span',
                                    get_string('user_suspended_already', 'local_th_random_test_enrolment'),
                                    array('class' => 'badge badge-secondary'));
                            }

                        }
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = '';
                        if (!empty($localth_random_test_enrolmentdata->user_groups[$email])) {
                            $cell->text = implode(',<br />', $localth_random_test_enrolmentdata->user_groups[$email]);
                        }
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localth_random_test_enrolment_enrolusers";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment');
                $table->size = array('20%', '17%', '17%', '20%', '26%');
                $table->head = array();
                $table->head[] = get_string('email');
                $table->head[] = get_string('firstname');
                $table->head[] = get_string('lastname');
                $table->head[] = get_string('user_enroled', 'local_th_random_test_enrolment');
                $table->head[] = get_string('user_groups', 'local_th_random_test_enrolment');
                $table->data = $data;

                if (!empty($data)) {
                    echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'local_th_random_test_enrolment'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            default:
                break;
        }
    }
}

/**
 * Checks whether a user with id $userid can be found in members list of the course group with name $groupname.
 *
 * @param int $courseid id of the course
 * @param string $groupname
 * @param int $userid id of the user
 * @return mixed Data structure containing flag 'already_member' and 'error'.
 */
function local_th_random_test_enrolment_is_already_member($courseid, $groupname, $userid)
{
    global $CFG;

    $result = new stdClass();
    $result->already_member = null;
    $result->error = '';

    if (empty($courseid) || empty($groupname) || empty($userid)) {
        $result->error = get_string('parameter_empty', 'local_th_random_test_enrolment');
        return $result;
    }

    // Get group by groupname.
    try {
        require_once $CFG->dirroot . '/group/lib.php';

        $existingcoursegroups = groups_get_all_groups($courseid);

        $groupname = trim($groupname);

        // Check if group already exists.
        $groupid = null;
        foreach ($existingcoursegroups as $key => $existingcoursegroup) {
            if ($groupname == $existingcoursegroup->name) {
                $groupid = $existingcoursegroup->id;
                break;
            }
        }
        // Group not found in course -> group has to be created, user is not a member.
        if (empty($groupid)) {
            return $result;
        }

        if (!empty($groupid)) {
            $ismember = groups_is_member($groupid, $userid);
            if ($ismember) {
                $result->already_member = $ismember;
            }
        }
    } catch (Exception $e) {
        $msg = get_string('error_group_add_members', 'local_th_random_test_enrolment') . local_th_random_test_enrolment_get_exception_info($e);
        $exceptionsmsg[] = $msg;
    }
    return $result;
}

/**
 * Helper function to show the enrolment details about the upcoming enrolments.
 */
function local_th_random_test_enrolment_display_enroldetails($localth_random_test_enrolmentrole)
{
    global $DB, $OUTPUT;

    // Get enrolment configuration.
    $enrolpluginshortname = get_config('local_th_random_test_enrolment', 'enrolplugin');
    $enrolpluginname = get_string('pluginname', 'enrol_' . $enrolpluginshortname);

    // Get role configuration.
    $roleid = $localth_random_test_enrolmentrole;
    $role = $DB->get_record('role', array('id' => $roleid));
    $systemcontext = context_system::instance();
    $roles = role_fix_names(array($roleid => $role), $systemcontext, ROLENAME_ORIGINAL);
    $rolename = $roles[$roleid]->localname;

    // Build enrolment details table.
    $data = array();
    $row = array();
    $cell = new html_table_cell();
    $cell->text = $enrolpluginname;
    $row[] = $cell;

    $cell = new html_table_cell();
    $cell->text = $rolename;
    $row[] = $cell;

    $data[] = $row;

    $table = new html_table();
    $table->id = "localth_random_test_enrolment_enrolinfo";
    $table->attributes['class'] = 'generaltable';
    $table->size = array('50%', '50%');
    $table->head = array();
    $table->head[] = get_string('type_enrol', 'local_th_random_test_enrolment');
    $table->head[] = get_string('role_assigned', 'local_th_random_test_enrolment');
    $table->data = $data;

    echo $OUTPUT->heading(get_string('enrolinfo_headline', 'local_th_random_test_enrolment'), 3);
    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
}
