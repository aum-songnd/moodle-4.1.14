<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify

namespace local_th_create_upload\external;
defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->dirroot . '/user/lib.php');
require_once($GLOBALS['CFG']->dirroot . '/user/profile/lib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use core_user;

class local_th_create_user_upload_external extends external_api {

    public static function create_user_upload_parameters() {
        global $CFG;

        $userfields = [
            'createpassword' => new external_value(PARAM_BOOL, 'Create password and send email', VALUE_OPTIONAL),

            'username' => new external_value(core_user::get_property_type('username'), 'Username'),
            'auth'     => new external_value(core_user::get_property_type('auth'), 'Auth plugin', VALUE_DEFAULT, 'manual'),
            'password' => new external_value(core_user::get_property_type('password'), 'Password', VALUE_OPTIONAL),

            'firstname' => new external_value(core_user::get_property_type('firstname'), 'First name'),
            'lastname'  => new external_value(core_user::get_property_type('lastname'), 'Last name'),
            'email'     => new external_value(core_user::get_property_type('email'), 'Email'),

            'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email visibility', VALUE_OPTIONAL),
            'city'        => new external_value(core_user::get_property_type('city'), 'City', VALUE_OPTIONAL),
            'country'     => new external_value(core_user::get_property_type('country'), 'Country', VALUE_OPTIONAL),
            'timezone'    => new external_value(core_user::get_property_type('timezone'), 'Timezone', VALUE_OPTIONAL),
            'description' => new external_value(core_user::get_property_type('description'), 'Description', VALUE_OPTIONAL),

            'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'), '', VALUE_OPTIONAL),
            'lastnamephonetic'  => new external_value(core_user::get_property_type('lastnamephonetic'),  '', VALUE_OPTIONAL),
            'middlename'        => new external_value(core_user::get_property_type('middlename'),        '', VALUE_OPTIONAL),
            'alternatename'     => new external_value(core_user::get_property_type('alternatename'),     '', VALUE_OPTIONAL),

            'idnumber'    => new external_value(core_user::get_property_type('idnumber'), 'ID number', VALUE_DEFAULT, ''),
            'institution' => new external_value(core_user::get_property_type('institution'), '', VALUE_OPTIONAL),
            'department'  => new external_value(core_user::get_property_type('department'),  '', VALUE_OPTIONAL),
            'phone1'      => new external_value(core_user::get_property_type('phone1'),      '', VALUE_OPTIONAL),
            'phone2'      => new external_value(core_user::get_property_type('phone2'),      '', VALUE_OPTIONAL),
            'address'     => new external_value(core_user::get_property_type('address'),     '', VALUE_OPTIONAL),
            'interests'   => new external_value(PARAM_RAW, 'User interests (comma separated)', VALUE_OPTIONAL),

            'lang'          => new external_value(core_user::get_property_type('lang'), '', VALUE_DEFAULT, core_user::get_property_default('lang')),
            'calendartype'  => new external_value(core_user::get_property_type('calendartype'), '', VALUE_DEFAULT, $CFG->calendartype, VALUE_OPTIONAL),
            'theme'         => new external_value(core_user::get_property_type('theme'), '', VALUE_OPTIONAL),
            'mailformat'    => new external_value(core_user::get_property_type('mailformat'), '', VALUE_OPTIONAL),

            'customfields' => new external_multiple_structure(
                new external_single_structure([
                    'type'  => new external_value(PARAM_ALPHANUMEXT, 'Custom field shortname'),
                    'value' => new external_value(PARAM_RAW,        'Value')
                ])
            , 'Custom profile fields', VALUE_OPTIONAL),

            'preferences' => new external_multiple_structure(
                new external_single_structure([
                    'type'  => new external_value(PARAM_RAW, 'Preference name'),
                    'value' => new external_value(PARAM_RAW, 'Preference value')
                ])
            , 'User preferences', VALUE_OPTIONAL),
        ];

        return new external_function_parameters([
            'users' => new external_multiple_structure(
                new external_single_structure($userfields)
            )
        ]);
    }

    /**
     * Kiểm tra đầu vào của 1 user: bắt buộc, duy nhất, định dạng...
     * Trả về mảng lỗi (không ném exception) để gom lỗi theo từng user.
     *
     * @param array $user
     * @param array $availableauths
     * @param array $langs
     * @param array $themes
     * @param bool  $enforceUniqueEmail
     * @return array errors
     */
    protected static function validate_user_payload(array $user, array $availableauths, array $langs, array $themes, bool $enforceUniqueEmail = true): array {
        global $DB, $CFG;

        $errors = [];

        // 1) Required fields
        $required = ['username', 'firstname', 'lastname', 'email'];
        foreach ($required as $f) {
            if (!array_key_exists($f, $user) || trim((string)$user[$f]) === '') {
                $errors[] = [
                    'field' => $f,
                    'code'  => 'required',
                    'msg'   => "Field '{$f}' is required / Trường '{$f}' là bắt buộc."
                ];
            }
        }

        // 2) Email format
        if (!empty($user['email'])) {
            if (!validate_email($user['email'])) {
                $errors[] = [
                    'field' => 'email',
                    'code'  => 'format_invalid',
                    'msg'   => "Email format is invalid / Định dạng email không hợp lệ."
                ];
            }
        }

        // 3) Auth plugin hợp lệ (nếu có). Loại mnet/webservice
        if (!empty($user['auth'])) {
            $auth = $user['auth'];
            if (!isset($availableauths[$auth]) || in_array($auth, ['mnet','webservice'], true)) {
                $errors[] = [
                    'field' => 'auth',
                    'code'  => 'auth_not_found',
                    'msg'   => "Auth plugin '{$auth}' does not exist or is not allowed / Plugin đăng nhập '{$auth}' không tồn tại hoặc không được phép."
                ];
            }
        }

        // 4) Theme hợp lệ (nếu có)
        if (!empty($user['theme'])) {
            if (!isset($themes[$user['theme']])) {
                $errors[] = [
                    'field' => 'theme',
                    'code'  => 'theme_not_found',
                    'msg'   => "Theme '{$user['theme']}' not found / Theme '{$user['theme']}' không tồn tại."
                ];
            }
        }

        // 5) Lang hợp lệ? nếu không: cảnh báo (không chặn)
        if (!empty($user['lang'])) {
            if (!isset($langs[$user['lang']])) {
                $errors[] = [
                    'field' => 'lang',
                    'code'  => 'lang_not_found',
                    'msg'   => "Language '{$user['lang']}' not found; will fallback to default / Ngôn ngữ '{$user['lang']}' không tồn tại; sẽ dùng mặc định."
                ];
            }
        }

        // 6) Unique checks
        // username duy nhất theo mnethostid
        if (!empty($user['username'])) {
            $exists = $DB->record_exists('user', [
                'username'   => $user['username'],
                'mnethostid' => $CFG->mnet_localhost_id
            ]);
            if ($exists) {
                $errors[] = [
                    'field' => 'username',
                    'code'  => 'duplicate',
                    'msg'   => "Username already exists / Tên đăng nhập đã tồn tại."
                ];
            }
        }

        // idnumber (nếu có) duy nhất theo mnethostid
        if (!empty($user['idnumber'])) {
            $exists = $DB->record_exists_select('user',
                'idnumber = :idn AND mnethostid = :m',
                ['idn' => $user['idnumber'], 'm' => $CFG->mnet_localhost_id]
            );
            if ($exists) {
                $errors[] = [
                    'field' => 'idnumber',
                    'code'  => 'duplicate',
                    'msg'   => "ID number already exists / Mã định danh (idnumber) đã tồn tại."
                ];
            }
        }

        // email duy nhất (tuỳ chính sách)
        if ($enforceUniqueEmail && !empty($user['email']) && validate_email($user['email'])) {
            $exists = $DB->record_exists_select('user',
                'LOWER(email) = LOWER(:e) AND mnethostid = :m',
                ['e' => $user['email'], 'm' => $CFG->mnet_localhost_id]
            );
            if ($exists) {
                $errors[] = [
                    'field' => 'email',
                    'code'  => 'duplicate',
                    'msg'   => "Email already exists / Email đã tồn tại."
                ];
            }
        }

        return $errors;
    }

    public static function create_user_upload($users) {
        global $CFG, $DB;

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:create', $context);

        $results = [];
        $availableauths = \core_component::get_plugin_list('auth');
        unset($availableauths['mnet'], $availableauths['webservice']);

        $langs  = get_string_manager()->get_list_of_translations();
        $themes = \core_component::get_plugin_list('theme');

        // Chính sách: có bắt buộc email duy nhất không?
        $enforceUniqueEmail = true;

        $transaction = $DB->start_delegated_transaction();

        foreach ($users as $user) {

            // --- Cách 1: Fast-path nhưng TRẢ VỀ LỖI khi username đã tồn tại ---
            if (!empty($user['username']) &&
                $DB->record_exists('user', ['username' => $user['username'], 'mnethostid' => $CFG->mnet_localhost_id])) {

                $results[] = [
                    'username' => $user['username'],
                    'status'   => 'error',
                    'errors'   => [[
                        'field' => 'username',
                        'code'  => 'duplicate',
                        'msg'   => "Username already exists / Tên đăng nhập đã tồn tại."
                    ]]
                ];
                continue;
            }

            // Validate chi tiết
            $errors = self::validate_user_payload($user, $availableauths, $langs, $themes, $enforceUniqueEmail);
            if (!empty($errors)) {
                $results[] = [
                    'username' => $user['username'] ?? null,
                    'status'   => 'error',
                    'errors'   => $errors
                ];
                continue;
            }

            // Chuẩn bị object để tạo
            $u = (object)$user;
            $u->confirmed  = 1;
            $u->mnethostid = $CFG->mnet_localhost_id;
            $u->auth       = $u->auth ?? 'manual';
            $createpassword = !empty($u->createpassword);
            unset($u->createpassword);

            // Fallback lang nếu không hợp lệ
            if (!isset($langs[$u->lang ?? ''])) {
                $u->lang = core_user::get_property_default('lang');
            }

            // Bỏ theme nếu không hợp lệ
            if (!empty($u->theme) && !isset($themes[$u->theme])) {
                unset($u->theme);
            }

            // Xử lý password/auth
            $updatepassword = false;
            $authplugin     = get_auth_plugin($u->auth);
            if ($authplugin->is_internal()) {
                if ($createpassword) {
                    $u->password = '';
                } else {
                    $updatepassword = true;
                }
            } else {
                $u->password = AUTH_PASSWORD_NOT_CACHED;
            }

            try {
                // Tạo user
                $u->id = user_create_user($u, $updatepassword, false);

                // Interests
                if (!empty($u->interests)) {
                    $trimmed = array_map('trim', explode(',', $u->interests));
                    \useredit_update_interests($u, $trimmed);
                }

                // Custom fields
                if (!empty($user['customfields'])) {
                    foreach ($user['customfields'] as $cf) {
                        $u->{'profile_field_' . $cf['type']} = $cf['value'];
                    }
                    \profile_save_data($u);
                }

                // Preferences
                if (!empty($user['preferences'])) {
                    foreach ($user['preferences'] as $pref) {
                        \set_user_preference($pref['type'], $pref['value'], $u);
                    }
                }

                // Buộc đổi mật khẩu lần đầu nếu createpassword
                if ($createpassword && $authplugin->is_internal()) {
                    set_user_preference('auth_forcepasswordchange', 1, $u);
                    set_user_preference('create_password', 1, $u); // flag để job khác gửi mail reset
                }

                \core\event\user_created::create_from_userid($u->id)->trigger();

                $results[] = [
                    'id'       => $u->id,
                    'username' => $u->username,
                    'status'   => 'created'
                ];
            } catch (\Throwable $e) {
                // Phòng khi lỗi DB/validation sâu của Moodle
                $results[] = [
                    'username' => $user['username'] ?? null,
                    'status'   => 'error',
                    'errors'   => [[
                        'field' => null,
                        'code'  => 'exception',
                        'msg'   => "Create failed: " . $e->getMessage()
                    ]]
                ];
            }
        }

        $transaction->allow_commit();
        return $results;
    }

    public static function create_user_upload_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id'       => new external_value(PARAM_INT,  'User ID', VALUE_OPTIONAL),
                'username' => new external_value(PARAM_RAW,  'Username', VALUE_OPTIONAL),
                'status'   => new external_value(PARAM_TEXT, 'Result status'),
                'errors'   => new external_multiple_structure(
                    new external_single_structure([
                        'field' => new external_value(PARAM_RAW,  'Field name (nullable)', VALUE_OPTIONAL),
                        'code'  => new external_value(PARAM_RAW,  'Error code'),
                        'msg'   => new external_value(PARAM_TEXT, 'Human-readable message (bilingual)')
                    ])
                , 'List of errors', VALUE_OPTIONAL)
            ])
        );
    }
}