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
 * Return token
 * @package    moodlecore
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/externallib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

if (!$CFG->enablewebservices) {
    throw new moodle_exception('enablewsdescription', 'webservice');
}

// This script is used by the mobile app to check that the site is available and web services
// are allowed. In this mode, no further action is needed.
if (optional_param('appsitecheck', 0, PARAM_INT)) {
    echo json_encode((object)['appsitecheck' => 'ok']);
    exit;
}

$username = required_param('username', PARAM_USERNAME);
$password = required_param('password', PARAM_RAW);
$serviceshortname  = required_param('service',  PARAM_ALPHANUMEXT);
$iprestrict = optional_param('iprestrict', null, PARAM_RAW);
$valid_duration = optional_param('validuntil', 0, PARAM_INT); // Tính bằng giây

echo $OUTPUT->header();

$username = trim(core_text::strtolower($username));
if (is_restored_user($username)) {
    throw new moodle_exception('restoredaccountresetpassword', 'webservice');
}

$systemcontext = context_system::instance();

$reason = null;
$user = authenticate_user_login($username, $password, false, $reason, false);


/**
 * Generate or return an existing token for the current authenticated user.
 * This function is used for creating a valid token for users authenticathing via login/token.php or admin/tool/mobile/launch.php.
 *
 * @param stdClass $service external service object
 * @return stdClass token object
 * @since Moodle 3.2
 * @throws moodle_exception
 */
function external_generate_thc_ops_token_for_current_user($service, $iprestrict = null, $validuntil = 0)
{
    global $DB, $USER, $CFG;

    core_user::require_active_user($USER, true, true);

    // Check if there is any required system capability.
    if ($service->requiredcapability and !has_capability($service->requiredcapability, context_system::instance())) {
        throw new moodle_exception('missingrequiredcapability', 'webservice', '', $service->requiredcapability);
    }

    // Specific checks related to user restricted service.
    if ($service->restrictedusers) {
        $authoriseduser = $DB->get_record(
            'external_services_users',
            array('externalserviceid' => $service->id, 'userid' => $USER->id)
        );

        if (empty($authoriseduser)) {
            throw new moodle_exception('usernotallowed', 'webservice', '', $service->shortname);
        }

        if (!empty($authoriseduser->validuntil) and $authoriseduser->validuntil < time()) {
            throw new moodle_exception('invalidtimedtoken', 'webservice');
        }

        if (!empty($authoriseduser->iprestriction) and !address_in_subnet(getremoteaddr(), $authoriseduser->iprestriction)) {
            throw new moodle_exception('invalidiptoken', 'webservice');
        }
    }

    // Check if a token has already been created for this user and this service.
    $conditions = array(
        'userid' => $USER->id,
        'externalserviceid' => $service->id,
        'tokentype' => EXTERNAL_TOKEN_PERMANENT
    );
    $tokens = $DB->get_records('external_tokens', $conditions, 'timecreated ASC');

    // A bit of sanity checks.
    foreach ($tokens as $key => $token) {

        // Checks related to a specific token. (script execution continue).
        $unsettoken = false;
        // If sid is set then there must be a valid associated session no matter the token type.
        if (!empty($token->sid)) {
            if (!\core\session\manager::session_exists($token->sid)) {
                // This token will never be valid anymore, delete it.
                $DB->delete_records('external_tokens', array('sid' => $token->sid));
                $unsettoken = true;
            }
        }

        // Remove token is not valid anymore.
        if (!empty($token->validuntil) and $token->validuntil < time()) {
            $DB->delete_records('external_tokens', array('token' => $token->token, 'tokentype' => EXTERNAL_TOKEN_PERMANENT));
            $unsettoken = true;
        }

        // Remove token if its IP is restricted.
        if (isset($token->iprestriction) and !address_in_subnet(getremoteaddr(), $token->iprestriction)) {
            $unsettoken = true;
        }

        if ($unsettoken) {
            unset($tokens[$key]);
        }
    }

    // If some valid tokens exist then use the most recent.
    if (count($tokens) > 0) {
        $token = array_pop($tokens);
    } else {
        $context = context_system::instance();
        $isofficialservice = $service->shortname == MOODLE_OFFICIAL_MOBILE_SERVICE;
        $is_thc_ops_service = $service->shortname == "th_ops_service";

        if (($is_thc_ops_service and has_capability('local/th_ops:create_thc_ops_service_token', $context)) or
            (is_siteadmin($USER) && $is_thc_ops_service)
        ) {

            // Create a new token.
            $token = new stdClass;
            $token->token = md5(uniqid(rand(), 1));
            $token->userid = $USER->id;
            $token->tokentype = EXTERNAL_TOKEN_PERMANENT;
            $token->contextid = context_system::instance()->id;
            $token->creatorid = $USER->id;
            $token->timecreated = time();
            $token->externalserviceid = $service->id;
            // By default tokens are valid for 12 weeks.
            $token->validuntil = $validuntil > 0 ? $validuntil : ($token->timecreated + $CFG->tokenduration);
            $token->iprestriction = $iprestrict ?: null;

            $token->sid = null;
            $token->lastaccess = null;
            // Generate the private token, it must be transmitted only via https.
            $token->privatetoken = random_string(64);
            $token->id = $DB->insert_record('external_tokens', $token);

            $eventtoken = clone $token;
            $eventtoken->privatetoken = null;
            $params = array(
                'objectid' => $eventtoken->id,
                'relateduserid' => $USER->id,
                'other' => array(
                    'auto' => true
                )
            );
            $event = \core\event\webservice_token_created::create($params);
            $event->add_record_snapshot('external_tokens', $eventtoken);
            $event->trigger();
        } else {
            throw new moodle_exception('cannotcreatetoken', 'webservice', '', $service->shortname);
        }
    }
    return $token;
}




if (!empty($user)) {

    // Cannot authenticate unless maintenance access is granted.
    $hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', $systemcontext, $user);
    if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
        throw new moodle_exception('sitemaintenance', 'admin');
    }

    if (isguestuser($user)) {
        throw new moodle_exception('noguest');
    }
    if (empty($user->confirmed)) {
        throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
    }
    // check credential expiry
    $userauth = get_auth_plugin($user->auth);
    if (!empty($userauth->config->expiration) and $userauth->config->expiration == 1) {
        $days2expire = $userauth->password_expire($user->username);
        if (intval($days2expire) < 0) {
            throw new moodle_exception('passwordisexpired', 'webservice');
        }
    }

    // let enrol plugins deal with new enrolments if necessary
    enrol_check_plugins($user);

    // setup user session to check capability
    \core\session\manager::set_user($user);

    //check if the service exists and is enabled
    $service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
    if (empty($service)) {
        // will throw exception if no token found
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    // Get an existing token or create a new one.
    $validuntil = 0;
    if (!empty($valid_duration)) {
        $validuntil = time() + $valid_duration;
    }

    $token = external_generate_thc_ops_token_for_current_user($service, $iprestrict, $validuntil);

    $privatetoken = $token->privatetoken;
    external_log_token_request($token);

    $siteadmin = has_capability('moodle/site:config', $systemcontext, $USER->id);

    $usertoken = new stdClass;
    $usertoken->token = $token->token;
    // Private token, only transmitted to https sites and non-admin users.
    if (is_https() and !$siteadmin) {
        $usertoken->privatetoken = $privatetoken;
    } else {
        $usertoken->privatetoken = null;
    }
    echo json_encode($usertoken);
} else {
    throw new moodle_exception('invalidlogin');
}
