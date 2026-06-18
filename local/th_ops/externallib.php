    <?php
    defined('MOODLE_INTERNAL') || die();

    require_once $CFG->libdir . "/externallib.php";
    require_once "$CFG->dirroot/user/externallib.php";
    require_once $CFG->dirroot . '/course/externallib.php';
    require_once($CFG->dirroot . '/enrol/manual/externallib.php');
    require_once($CFG->dirroot . '/lib/accesslib.php');

    require_once($GLOBALS['CFG']->libdir . '/externallib.php');
    require_once($GLOBALS['CFG']->dirroot . '/user/lib.php');
    require_once($GLOBALS['CFG']->dirroot . '/user/profile/lib.php');
    require_once($GLOBALS['CFG']->dirroot . '/user/editlib.php');

    // use external_api;
    // use external_function_parameters;
    // use external_value;
    // use external_single_structure;
    // use external_multiple_structure;
    // use context_system;
    // use core_user;



    class th_ops_external extends external_api
    {

        public static function template_baoloi()
        {

            global $CFG;


            $debug_old = $CFG->debug ?? 0;
            $debugdisplay_old = $CFG->debugdisplay ?? false;

            // Bật debug tạm thời
            $CFG->debug = DEBUG_DEVELOPER;
            $CFG->debugdisplay = true;

            try {
                //Login hàm
            } catch (\Throwable $e) {
                // Khôi phục lại trạng thái debug
                $CFG->debug = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;

                // Ném ra exception mới có thông báo rõ ràng hơn
                throw new Exception($e->getMessage());
            }

            // Khôi phục lại trạng thái debug sau khi gọi xong
            $CFG->debug = $debug_old;
            $CFG->debugdisplay = $debugdisplay_old;
        }

        public static function create_users_parameters()
        {
            global $CFG;
            $userfields = [
                'createpassword' => new external_value(
                    PARAM_BOOL,
                    'True if password should be created and mailed to user.',
                    VALUE_OPTIONAL
                ),
                // General.
                'username' => new external_value(
                    core_user::get_property_type('username'),
                    'Username policy is defined in Moodle security config.'
                ),
                'auth' => new external_value(
                    core_user::get_property_type('auth'),
                    'Auth plugins include manual, ldap, etc',
                    VALUE_DEFAULT,
                    'manual',
                    core_user::get_property_null('auth')
                ),
                'password' => new external_value(
                    core_user::get_property_type('password'),
                    'Plain text password consisting of any characters',
                    VALUE_OPTIONAL
                ),
                'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user'),
                'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user'),
                'email' => new external_value(core_user::get_property_type('email'), 'A valid and unique email address'),
                'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email visibility', VALUE_OPTIONAL),
                'city' => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'country' => new external_value(
                    core_user::get_property_type('country'),
                    'Home country code of the user, such as AU or CZ',
                    VALUE_OPTIONAL
                ),
                'timezone' => new external_value(
                    core_user::get_property_type('timezone'),
                    'Timezone code such as Australia/Perth, or 99 for default',
                    VALUE_OPTIONAL
                ),
                'description' => new external_value(
                    core_user::get_property_type('description'),
                    'User profile description, no HTML',
                    VALUE_OPTIONAL
                ),
                // Additional names.
                'firstnamephonetic' => new external_value(
                    core_user::get_property_type('firstnamephonetic'),
                    'The first name(s) phonetically of the user',
                    VALUE_OPTIONAL
                ),
                'lastnamephonetic' => new external_value(
                    core_user::get_property_type('lastnamephonetic'),
                    'The family name phonetically of the user',
                    VALUE_OPTIONAL
                ),
                'middlename' => new external_value(
                    core_user::get_property_type('middlename'),
                    'The middle name of the user',
                    VALUE_OPTIONAL
                ),
                'alternatename' => new external_value(
                    core_user::get_property_type('alternatename'),
                    'The alternate name of the user',
                    VALUE_OPTIONAL
                ),
                // Interests.
                'interests' => new external_value(PARAM_TEXT, 'User interests (separated by commas)', VALUE_OPTIONAL),
                // Optional.
                'idnumber' => new external_value(
                    core_user::get_property_type('idnumber'),
                    'An arbitrary ID code number perhaps from the institution',
                    VALUE_DEFAULT,
                    ''
                ),
                'institution' => new external_value(core_user::get_property_type('institution'), 'institution', VALUE_OPTIONAL),
                'department' => new external_value(core_user::get_property_type('department'), 'department', VALUE_OPTIONAL),
                'phone1' => new external_value(core_user::get_property_type('phone1'), 'Phone 1', VALUE_OPTIONAL),
                'phone2' => new external_value(core_user::get_property_type('phone2'), 'Phone 2', VALUE_OPTIONAL),
                'address' => new external_value(core_user::get_property_type('address'), 'Postal address', VALUE_OPTIONAL),
                // Other user preferences stored in the user table.
                'lang' => new external_value(
                    core_user::get_property_type('lang'),
                    'Language code such as "en", must exist on server',
                    VALUE_DEFAULT,
                    core_user::get_property_default('lang'),
                    core_user::get_property_null('lang')
                ),
                'calendartype' => new external_value(
                    core_user::get_property_type('calendartype'),
                    'Calendar type such as "gregorian", must exist on server',
                    VALUE_DEFAULT,
                    $CFG->calendartype,
                    VALUE_OPTIONAL
                ),
                'theme' => new external_value(
                    core_user::get_property_type('theme'),
                    'Theme name such as "standard", must exist on server',
                    VALUE_OPTIONAL
                ),
                'mailformat' => new external_value(
                    core_user::get_property_type('mailformat'),
                    'Mail format code is 0 for plain text, 1 for HTML etc',
                    VALUE_OPTIONAL
                ),
                // Custom user profile fields.
                'customfields' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type'  => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                            'value' => new external_value(PARAM_RAW, 'The value of the custom field')
                        ]
                    ),
                    'User custom fields (also known as user profil fields)',
                    VALUE_OPTIONAL
                ),
                // User preferences.
                'preferences' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type'  => new external_value(PARAM_RAW, 'The name of the preference'),
                            'value' => new external_value(PARAM_RAW, 'The value of the preference')
                        ]
                    ),
                    'User preferences',
                    VALUE_OPTIONAL
                ),
            ];
            return new external_function_parameters(
                [
                    'users' => new external_multiple_structure(
                        new external_single_structure($userfields)
                    )
                ]
            );
        }

        // Đã thông báo invalid parameter ok
        public static function create_users($users)
        {
            global $CFG;

            // Đưa user đơn về mảng như core_user yêu cầu
            $params = self::validate_parameters(self::create_users_parameters(), array('users' => $users));


            // Lưu trạng thái cũ
            $debug_old = $CFG->debug ?? 0;
            $debugdisplay_old = $CFG->debugdisplay ?? false;

            // Bật debug tạm thời
            $CFG->debug = DEBUG_DEVELOPER;
            $CFG->debugdisplay = true;

            try {
                // Gọi hàm gốc
                $results = core_user_external::create_users($params['users']);
            } catch (\Throwable $e) {
                // Khôi phục lại trạng thái debug
                $CFG->debug = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;

                // Ném ra exception mới có thông báo rõ ràng hơn
                throw new Exception($e->getMessage());
            }

            // Khôi phục lại trạng thái debug sau khi gọi xong
            $CFG->debug = $debug_old;
            $CFG->debugdisplay = $debugdisplay_old;


            // Trả về phần tử đầu tiên (vì chỉ tạo 1 user)
            return $results;
        }

        /**
         * Returns description of method result value
         *
         * @return external_description
         * @since Moodle 2.2
         */
        public static function create_users_returns()
        {
            return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id'       => new external_value(core_user::get_property_type('id'), 'user id'),
                        'username' => new external_value(core_user::get_property_type('username'), 'user name'),
                    )
                )
            );
        }


        public static function get_users_by_field_parameters()
        {
            return new external_function_parameters([
                'field' => new external_value(PARAM_ALPHANUMEXT, 'Tên trường cần tìm: id, idnumber, username, email'),
                'values' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'Giá trị cần tìm'),
                    'Danh sách giá trị'
                ),
            ]);
        }

        public static function get_users_by_field($field, $values)
        {
            global $DB;

            $params = self::validate_parameters(self::get_users_by_field_parameters(), [
                'field' => $field,
                'values' => $values,
            ]);

            // Gọi API gốc
            $result = \core_user_external::get_users_by_field($params['field'], $params['values']);
            $users = $result;

            // Danh sách user thiếu email hoặc phone2
            $userids_missing_data = array_column(array_filter($users, function ($u) {
                return (!isset($u['email']) || $u['email'] === '') || (!isset($u['phone2']) || $u['phone2'] === '') && !empty($u['id']);
            }), 'id');

            if ($userids_missing_data) {
                $records = $DB->get_records_list('user', 'id', $userids_missing_data, '', 'id, email, phone2');
                foreach ($users as &$user) {
                    if (empty($user['email']) && isset($records[$user['id']])) {
                        $user['email'] = $records[$user['id']]->email;
                    }
                    if (empty($user['phone2']) && isset($records[$user['id']])) {
                        $user['phone2'] = $records[$user['id']]->phone2;
                    }
                }
            }

            return $users;
        }


        public static function get_users_by_field_returns()
        {
            return new external_multiple_structure(
                \core_user_external::user_description()
            );
        }

        public static function get_users_parameters()
        {
            return new external_function_parameters(
                array(
                    'criteria' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'key' => new external_value(PARAM_ALPHA, 'the user column to search, expected keys (value format) are:
                                    "id" (int) matching user id,
                                    "lastname" (string) user last name (Note: you can use % for searching but it may be considerably slower!),
                                    "firstname" (string) user first name (Note: you can use % for searching but it may be considerably slower!),
                                    "idnumber" (string) matching user idnumber,
                                    "username" (string) matching user username,
                                    "email" (string) user email (Note: you can use % for searching but it may be considerably slower!),
                                    "auth" (string) matching user auth plugin'),
                                'value' => new external_value(PARAM_RAW, 'the value to search')
                            )
                        ),
                        'the key/value pairs to be considered in user search. Values can not be empty.
                            Specify different keys only once (fullname => \'user1\', auth => \'manual\', ...) -
                            key occurences are forbidden.
                            The search is executed with AND operator on the criterias. Invalid criterias (keys) are ignored,
                            the search is still executed on the valid criterias.
                            You can search without criteria, but the function is not designed for it.
                            It could very slow or timeout. The function is designed to search some specific users.'
                    )
                )
            );
        }

        public static function get_users($criteria = [])
        {
            global $CFG, $DB;
            require_once($CFG->dirroot . "/user/lib.php");

            // Xác thực tham số
            $params = self::validate_parameters(self::get_users_parameters(), [
                'criteria' => $criteria,
            ]);

            // Gọi hàm core
            $result = \core_user_external::get_users($params['criteria']);
            $users = $result['users'];
            $warnings = $result['warnings'];

            // Tìm user thiếu email hoặc phone2
            $userids = array_column(array_filter($users, function ($u) {
                return (!isset($u['email']) || empty($u['email']) || !isset($u['phone2']) || empty($u['phone2'])) && !empty($u['id']);
            }), 'id');

            // Bổ sung email và phone2 nếu có
            if (!empty($userids)) {
                $fields = $DB->get_records_list('user', 'id', $userids, '', 'id, email, phone2');
                foreach ($users as &$user) {
                    if (empty($user['email']) && isset($fields[$user['id']]->email)) {
                        $user['email'] = $fields[$user['id']]->email;
                    }
                    if (empty($user['phone2']) && isset($fields[$user['id']]->phone2)) {
                        $user['phone2'] = $fields[$user['id']]->phone2;
                    }
                }
            }

            return [
                'users' => $users,
                'warnings' => $warnings,
            ];
        }


        public static function get_users_returns()
        {
            return new external_single_structure([
                'users' => new external_multiple_structure(
                    \core_user_external::user_description()
                ),
                'warnings' => new external_warnings('always set to \'key\'', 'faulty key name')
            ]);
        }



        /**
         * Khai báo tham số cho API update_enrol_manual_enrol_users
         */
        public static function update_enrol_manual_enrol_users_parameters()
        {
            return new external_function_parameters([
                'enrolments' => new external_multiple_structure(
                    new external_single_structure([
                        'rolecodes' => new external_multiple_structure(
                            new external_value(PARAM_RAW, 'Mã vai trò (shortname)'),
                            'Danh sách mã vai trò (shortname)'
                        ),
                        'username' => new external_value(PARAM_RAW, 'Tên đăng nhập người dùng'),
                        'courseshortname' => new external_value(PARAM_RAW, 'Mã khóa học (shortname)'),
                        'timestart' => new external_value(PARAM_INT, 'Thời gian bắt đầu ghi danh (timestamp)', VALUE_OPTIONAL),
                        'timeend' => new external_value(PARAM_INT, 'Thời gian kết thúc ghi danh (timestamp)', VALUE_OPTIONAL),
                        'suspend' => new external_value(PARAM_INT, '0: hoạt động, 1: tạm dừng', VALUE_OPTIONAL),
                    ])
                )
            ]);
        }

        /**
         * API cập nhật enrol nhiều vai trò cho user vào course theo mã, gọi enrol_manual_enrol_users
         */
        public static function update_enrol_manual_enrol_users($enrolments)
        {
            global $DB, $CFG;

            // Để dùng role_assign/role_unassign
            // Validate parameters
            $params = self::validate_parameters(
                self::update_enrol_manual_enrol_users_parameters(),
                ['enrolments' => $enrolments]
            );


            $enrolments_for_api = [];
            $results = [];


            $debug_old = $CFG->debug ?? 0;
            $debugdisplay_old = $CFG->debugdisplay ?? false;

            // Bật debug tạm thời
            $CFG->debug = DEBUG_DEVELOPER;
            $CFG->debugdisplay = true;

            try {
                // Gọi hàm gốc
                // Gom theo user-course để dễ xử lý role sau khi enrol
                $user_course_roles_map = [];

                foreach ($params['enrolments'] as $enrol) {
                    $username = $enrol['username'];
                    $courseshortname = $enrol['courseshortname'];
                    $rolecodes = $enrol['rolecodes'];
                    $timestart = isset($enrol['timestart']) ? $enrol['timestart'] : 0;
                    $timeend = isset($enrol['timeend']) ? $enrol['timeend'] : 0;
                    $suspend = isset($enrol['suspend']) ? $enrol['suspend'] : 0;

                    // -- Resolve userid
                    $user = $DB->get_record('user', ['username' => $username, 'deleted' => 0], '*', IGNORE_MISSING);
                    if (!$user) {
                        throw new Exception("User not found with username: $username");
                    }
                    $userid = $user->id;

                    // -- Resolve courseid
                    $course = $DB->get_record('course', ['shortname' => $courseshortname], '*', IGNORE_MISSING);
                    if (!$course) {
                        throw new Exception("Course not found with shortname: $courseshortname");
                    }
                    $courseid = $course->id;

                    // -- Resolve roleids
                    $roleids = [];
                    foreach ($rolecodes as $rolecode) {
                        $role = $DB->get_record('role', ['shortname' => $rolecode], '*', IGNORE_MISSING);
                        if (!$role) {
                            throw new Exception("Role not found with rolecode: $rolecode");
                        }
                        $roleids[] = $role->id;
                    }

                    // Gom thông tin lại để xử lý sau khi enrol
                    $key = $userid . '-' . $courseid;
                    if (!isset($user_course_roles_map[$key])) {
                        $user_course_roles_map[$key] = [
                            'userid' => $userid,
                            'courseid' => $courseid,
                            'roleids' => []
                        ];
                    }
                    $user_course_roles_map[$key]['roleids'] = array_unique(array_merge($user_course_roles_map[$key]['roleids'], $roleids));

                    // -- Gom tất cả role của user vào chung 1 lần gọi
                    foreach ($roleids as $roleid) {
                        $enrolments_for_api[] = [
                            'roleid' => $roleid,
                            'userid' => $userid,
                            'courseid' => $courseid,
                            'timestart' => $timestart,
                            'timeend' => $timeend,
                            'suspend' => $suspend,
                        ];
                    }

                    $results[] = [
                        'username' => $username,
                        'courseshortname' => $courseshortname,
                        'roleids' => $roleids,
                        'rolecodes' => $rolecodes,  // Bổ sung dòng này
                        'userid' => $userid,
                        'courseid' => $courseid,
                        'status' => 'pending'
                    ];
                }

                // Gọi duy nhất 1 lần cho toàn bộ enrolments
                if ($enrolments_for_api) {
                    \enrol_manual_external::enrol_users($enrolments_for_api);
                }


                // Sau khi enrol, kiểm tra và unassign các role thừa
                foreach ($user_course_roles_map as $ucr) {
                    $userid = $ucr['userid'];
                    $courseid = $ucr['courseid'];
                    $target_roleids = $ucr['roleids'];
                    $context = \context_course::instance($courseid);

                    // Lấy các role hiện tại của user trong context này
                    $current_roles = $DB->get_records('role_assignments', [
                        'userid' => $userid,
                        'contextid' => $context->id,
                    ]);
                    foreach ($current_roles as $roleassignment) {
                        $roleid = $roleassignment->roleid;
                        // Nếu role hiện tại KHÔNG nằm trong role cần giữ lại => unassign
                        if (!in_array($roleid, $target_roleids)) {
                            role_unassign($roleid, $userid, $context->id);
                        }
                    }
                }

                // Cập nhật status cho tất cả trả về
                foreach ($results as &$r) {
                    $r['status'] = 'success';
                }
            } catch (\Throwable $e) {
                // Khôi phục lại trạng thái debug
                $CFG->debug = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;

                // Ném ra exception mới có thông báo rõ ràng hơn
                throw new Exception($e->getMessage());
            }

            // Khôi phục lại trạng thái debug sau khi gọi xong
            $CFG->debug = $debug_old;
            $CFG->debugdisplay = $debugdisplay_old;

            return $results;
        }



        public static function update_enrol_manual_enrol_users_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'username' => new external_value(PARAM_RAW, 'Tên đăng nhập người dùng'),
                    'courseshortname' => new external_value(PARAM_RAW, 'Mã khóa học (shortname)'),
                    'roleids' => new external_multiple_structure(
                        new external_value(PARAM_INT, 'ID vai trò đã enrol'),
                        'Danh sách roleid đã enrol'
                    ),
                    'rolecodes' => new external_multiple_structure(
                        new external_value(PARAM_RAW, 'Shortname vai trò đã enrol'),
                        'Danh sách rolecode đã enrol'
                    ),
                    'userid' => new external_value(PARAM_INT, 'ID người dùng'),
                    'courseid' => new external_value(PARAM_INT, 'ID khóa học'),
                    'status' => new external_value(PARAM_RAW, 'Trạng thái: success/fail/...'),
                ])
            );
        }




        public static function get_courses_by_field_parameters()
        {
            return new external_function_parameters(
                [
                    'field' => new external_value(PARAM_TEXT, 'Trường tìm kiếm', VALUE_DEFAULT, ''),
                    'value' => new external_value(PARAM_TEXT, 'Giá trị tìm kiếm', VALUE_DEFAULT, ''),
                ]
            );
        }

        public static function get_courses_by_field($field = '', $value = '')
        {
            global $DB;

            $params = self::validate_parameters(
                self::get_courses_by_field_parameters(),
                ['field' => $field, 'value' => $value]
            );

            // === Bổ sung xử lý nếu field là "shortnames" ===
            if ($params['field'] === 'shortnames') {
                $shortnames = array_filter(array_map('trim', explode(',', $params['value'])));
                if (empty($shortnames)) {
                    return [
                        'courses' => [],
                        'warnings' => [[
                            'item' => 'shortnames',
                            'itemid' => 0,
                            'warningcode' => 'invalidvalue',
                            'message' => 'Danh sách shortname rỗng hoặc không hợp lệ.'
                        ]]
                    ];
                }

                // Truy vấn để lấy danh sách course id
                list($in, $paramslist) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
                $courses = $DB->get_records_select('course', "shortname $in", $paramslist, '', 'id');

                if (!$courses) {
                    return [
                        'courses' => [],
                        'warnings' => [[
                            'item' => 'shortnames',
                            'itemid' => 0,
                            'warningcode' => 'notfound',
                            'message' => 'Không tìm thấy khóa học nào với shortnames đã truyền.'
                        ]]
                    ];
                }

                // Gọi lại core_course_get_courses_by_field theo danh sách id
                $courseids = array_keys($courses);
                $params['field'] = 'ids';
                $params['value'] = implode(',', $courseids); // ví dụ: "4,5,6"
            }

            // === Nếu field là categorycode, xử lý như cũ ===
            if ($params['field'] === 'categorycode') {
                $cat = $DB->get_record('course_categories', ['idnumber' => $params['value']]);
                if (!$cat) {
                    return [
                        'courses' => [],
                        'warnings' => [[
                            'item' => 'categorycode',
                            'itemid' => 0,
                            'warningcode' => 'notfound',
                            'message' => 'Không tìm thấy danh mục với idnumber: ' . $params['value'],
                        ]]
                    ];
                }
                $params['field'] = 'category';
                $params['value'] = $cat->id;
            }

            // Gọi core function chính
            $core = \core_course_external::get_courses_by_field($params['field'], $params['value']);

            // FIX: Mapping lại startdate, enddate chuẩn từ DB (giữ nguyên như cũ)
            if (!empty($core['courses']) && is_array($core['courses'])) {
                $catids = [];
                $courseids = [];
                foreach ($core['courses'] as $c) {
                    if (!empty($c['categoryid'])) $catids[] = $c['categoryid'];
                    if (!empty($c['id'])) $courseids[] = $c['id'];
                }

                // Lấy mapping categoryid → idnumber
                $catid2idnumber = [];
                if ($catids) {
                    list($in, $catparams) = $DB->get_in_or_equal($catids, SQL_PARAMS_NAMED);
                    $cats = $DB->get_records_select('course_categories', "id $in", $catparams);
                    foreach ($cats as $cat) {
                        $catid2idnumber[$cat->id] = $cat->idnumber;
                    }
                }

                // Lấy lại startdate/enddate gốc
                $id2course = [];
                if ($courseids) {
                    list($in, $cparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
                    $crs = $DB->get_records_select('course', "id $in", $cparams, '', 'id,startdate,enddate');
                    foreach ($crs as $cr) {
                        $id2course[$cr->id] = $cr;
                    }
                }

                foreach ($core['courses'] as &$course) {
                    $course['categorycode'] = isset($catid2idnumber[$course['categoryid']]) ? $catid2idnumber[$course['categoryid']] : '';
                    if (!empty($course['id']) && isset($id2course[$course['id']])) {
                        $course['startdate'] = $id2course[$course['id']]->startdate;
                        $course['enddate'] = $id2course[$course['id']]->enddate;
                    }
                }
                unset($course);
            }

            return $core;
        }



        protected static function get_course_structure_with_categorycode($onlypublicdata = true)
        {
            $coursestructure = [
                'id' => new external_value(PARAM_INT, 'course id'),
                'fullname' => new external_value(PARAM_RAW, 'course full name'),
                'displayname' => new external_value(PARAM_RAW, 'course display name'),
                'shortname' => new external_value(PARAM_RAW, 'course short name'),
                'categoryid' => new external_value(PARAM_INT, 'category id'),
                'categorycode' => new external_value(PARAM_RAW, 'idnumber của danh mục khóa học', VALUE_OPTIONAL), // <-- THÊM TRƯỜNG NÀY
                'categoryname' => new external_value(PARAM_RAW, 'category name'),
                'sortorder' => new external_value(PARAM_INT, 'Sort order in the category', VALUE_OPTIONAL),
                'summary' => new external_value(PARAM_RAW, 'summary'),
                'summaryformat' => new external_format_value('summary'),
                'summaryfiles' => new external_files('summary files in the summary field', VALUE_OPTIONAL),
                'overviewfiles' => new external_files('additional overview files attached to this course'),
                'contacts' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'contact user id'),
                            'fullname' => new external_value(PARAM_NOTAGS, 'contact user fullname'),
                        ]
                    ),
                    'contact users'
                ),
                'enrollmentmethods' => new external_multiple_structure(
                    new external_value(PARAM_PLUGIN, 'enrollment method'),
                    'enrollment methods list'
                ),
                'customfields' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                            'shortname' => new external_value(PARAM_RAW, 'The shortname of the custom field'),
                            'type' => new external_value(PARAM_ALPHANUMEXT, 'The type of the custom field - text field, checkbox...'),
                            'valueraw' => new external_value(PARAM_RAW, 'The raw value of the custom field'),
                            'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                        ]
                    ),
                    'Custom fields',
                    VALUE_OPTIONAL
                ),
            ];

            if (!$onlypublicdata) {
                $extra = [
                    'idnumber' => new external_value(PARAM_RAW, 'Id number', VALUE_OPTIONAL),
                    'format' => new external_value(PARAM_PLUGIN, 'Course format: weeks, topics, social, site,..', VALUE_OPTIONAL),
                    'showgrades' => new external_value(PARAM_INT, '1 if grades are shown, otherwise 0', VALUE_OPTIONAL),
                    'newsitems' => new external_value(PARAM_INT, 'Number of recent items appearing on the course page', VALUE_OPTIONAL),
                    'startdate' => new external_value(PARAM_INT, 'Timestamp when the course start', VALUE_OPTIONAL),
                    'enddate' => new external_value(PARAM_INT, 'Timestamp when the course end', VALUE_OPTIONAL),
                    'maxbytes' => new external_value(PARAM_INT, 'Largest size of file that can be uploaded into', VALUE_OPTIONAL),
                    'showreports' => new external_value(PARAM_INT, 'Are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL),
                    'visible' => new external_value(PARAM_INT, '1: available to student, 0:not available', VALUE_OPTIONAL),
                    'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible', VALUE_OPTIONAL),
                    'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no', VALUE_OPTIONAL),
                    'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id', VALUE_OPTIONAL),
                    'enablecompletion' => new external_value(PARAM_INT, 'Completion enabled? 1: yes 0: no', VALUE_OPTIONAL),
                    'completionnotify' => new external_value(PARAM_INT, '1: yes 0: no', VALUE_OPTIONAL),
                    'lang' => new external_value(PARAM_SAFEDIR, 'Forced course language', VALUE_OPTIONAL),
                    'theme' => new external_value(PARAM_PLUGIN, 'Fame of the forced theme', VALUE_OPTIONAL),
                    'marker' => new external_value(PARAM_INT, 'Current course marker', VALUE_OPTIONAL),
                    'legacyfiles' => new external_value(PARAM_INT, 'If legacy files are enabled', VALUE_OPTIONAL),
                    'calendartype' => new external_value(PARAM_PLUGIN, 'Calendar type', VALUE_OPTIONAL),
                    'timecreated' => new external_value(PARAM_INT, 'Time when the course was created', VALUE_OPTIONAL),
                    'timemodified' => new external_value(PARAM_INT, 'Last time  the course was updated', VALUE_OPTIONAL),
                    'requested' => new external_value(PARAM_INT, 'If is a requested course', VALUE_OPTIONAL),
                    'cacherev' => new external_value(PARAM_INT, 'Cache revision number', VALUE_OPTIONAL),
                    'filters' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'filter' => new external_value(PARAM_PLUGIN, 'Filter plugin name'),
                                'localstate' => new external_value(PARAM_INT, 'Filter state: 1 for on, -1 for off, 0 if inherit'),
                                'inheritedstate' => new external_value(PARAM_INT, '1 or 0 to use when localstate is set to inherit'),
                            ]
                        ),
                        'Course filters',
                        VALUE_OPTIONAL
                    ),
                    'courseformatoptions' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'name' => new external_value(PARAM_RAW, 'Course format option name.'),
                                'value' => new external_value(PARAM_RAW, 'Course format option value.'),
                            ]
                        ),
                        'Additional options for particular course format.',
                        VALUE_OPTIONAL
                    ),
                ];
                $coursestructure = array_merge($coursestructure, $extra);
            }
            return new external_single_structure($coursestructure);
        }

        public static function get_courses_by_field_returns()
        {
            return new external_single_structure(
                [
                    'courses' => new external_multiple_structure(self::get_course_structure_with_categorycode(false), 'Course'),
                    'warnings' => new external_warnings(),
                ]
            );
        }

        public static function update_courses_parameters()
        {
            $courseconfig = get_config('moodlecourse');
            return new external_function_parameters(
                [
                    'courses' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'coursecode' => new external_value(PARAM_TEXT, 'Mã rút gọn khóa học (shortname) để định danh (bắt buộc)'),
                                'fullname' => new external_value(PARAM_TEXT, 'Tên đầy đủ khóa học', VALUE_OPTIONAL),
                                'idnumber' => new external_value(PARAM_RAW, 'Mã định danh khóa học (idnumber)', VALUE_OPTIONAL),
                                'summary' => new external_value(PARAM_RAW, 'Mô tả khóa học', VALUE_OPTIONAL),
                                'summaryformat' => new external_format_value('summary', VALUE_OPTIONAL),
                                'format' => new external_value(
                                    PARAM_PLUGIN,
                                    'Định dạng khóa học: weeks, topics, social, site,...',
                                    VALUE_OPTIONAL,
                                    $courseconfig->format
                                ),
                                'showgrades' => new external_value(
                                    PARAM_INT,
                                    'Hiển thị điểm (1: có, 0: không)',
                                    VALUE_OPTIONAL,
                                    $courseconfig->showgrades
                                ),
                                'newsitems' => new external_value(
                                    PARAM_INT,
                                    'Số tin tức trên trang khóa học',
                                    VALUE_OPTIONAL,
                                    $courseconfig->newsitems
                                ),
                                'startdate' => new external_value(
                                    PARAM_INT,
                                    'Thời gian bắt đầu (timestamp)',
                                    VALUE_OPTIONAL
                                ),
                                'enddate' => new external_value(
                                    PARAM_INT,
                                    'Thời gian kết thúc (timestamp)',
                                    VALUE_OPTIONAL
                                ),
                                'numsections' => new external_value(
                                    PARAM_INT,
                                    '(deprecated, dùng courseformatoptions) Số chủ đề/tuần',
                                    VALUE_OPTIONAL
                                ),
                                'maxbytes' => new external_value(
                                    PARAM_INT,
                                    'Kích thước tệp lớn nhất',
                                    VALUE_OPTIONAL,
                                    $courseconfig->maxbytes
                                ),
                                'showreports' => new external_value(
                                    PARAM_INT,
                                    'Báo cáo hoạt động (1: có, 0: không)',
                                    VALUE_OPTIONAL,
                                    $courseconfig->showreports
                                ),
                                'visible' => new external_value(
                                    PARAM_INT,
                                    'Hiển thị khóa học (1: có, 0: không)',
                                    VALUE_OPTIONAL
                                ),
                                'hiddensections' => new external_value(
                                    PARAM_INT,
                                    '(deprecated, dùng courseformatoptions) Cách hiển thị các phần ẩn',
                                    VALUE_OPTIONAL
                                ),
                                'groupmode' => new external_value(
                                    PARAM_INT,
                                    'Kiểu nhóm (0: không, 1: riêng biệt, 2: nhìn thấy nhau)',
                                    VALUE_OPTIONAL,
                                    $courseconfig->groupmode
                                ),
                                'groupmodeforce' => new external_value(
                                    PARAM_INT,
                                    'Bắt buộc kiểu nhóm (1: có, 0: không)',
                                    VALUE_OPTIONAL,
                                    $courseconfig->groupmodeforce
                                ),
                                'defaultgroupingid' => new external_value(
                                    PARAM_INT,
                                    'ID nhóm mặc định',
                                    VALUE_OPTIONAL,
                                    0
                                ),
                                'enablecompletion' => new external_value(
                                    PARAM_INT,
                                    'Bật hoàn thành hoạt động (1: có, 0: không)',
                                    VALUE_OPTIONAL
                                ),
                                'completionnotify' => new external_value(
                                    PARAM_INT,
                                    'Thông báo hoàn thành (1: có, 0: không)',
                                    VALUE_OPTIONAL
                                ),
                                'lang' => new external_value(
                                    PARAM_SAFEDIR,
                                    'Ngôn ngữ khóa học',
                                    VALUE_OPTIONAL
                                ),
                                'forcetheme' => new external_value(
                                    PARAM_PLUGIN,
                                    'Theme bắt buộc',
                                    VALUE_OPTIONAL
                                ),
                                // Trường dành riêng cho API wrapper này
                                'categorycode' => new external_value(
                                    PARAM_RAW,
                                    'Mã idnumber của danh mục cha (cập nhật lại parent category)',
                                    VALUE_OPTIONAL
                                ),
                                // Format options
                                'courseformatoptions' => new external_multiple_structure(
                                    new external_single_structure(
                                        [
                                            'name' => new external_value(PARAM_ALPHANUMEXT, 'Tên option định dạng khóa học'),
                                            'value' => new external_value(PARAM_RAW, 'Giá trị option'),
                                        ]
                                    ),
                                    'Tùy chọn thêm cho định dạng khóa học',
                                    VALUE_OPTIONAL
                                ),
                                'customfields' => new external_multiple_structure(
                                    new external_single_structure(
                                        [
                                            'shortname' => new external_value(PARAM_ALPHANUMEXT, 'Shortname của custom field'),
                                            'value' => new external_value(PARAM_RAW, 'Giá trị custom field'),
                                        ]
                                    ),
                                    'Custom fields cho khóa học',
                                    VALUE_OPTIONAL
                                ),
                            ]
                        ),
                        'Danh sách các khóa học cần cập nhật'
                    ),
                ]
            );
        }
        public static function update_courses($courses)
        {
            global $DB;

            $params = self::validate_parameters(self::update_courses_parameters(), ['courses' => $courses]);
            $core_courses = [];
            $results = [];

            foreach ($params['courses'] as $course) {
                // Tra cứu theo coursecode (shortname)
                $rec = $DB->get_record('course', ['shortname' => $course['coursecode']]);
                if (!$rec) {
                    $results[] = [
                        'coursecode' => $course['coursecode'],
                        'status' => 'not_found',
                        'message' => 'Không tìm thấy khóa học với coursecode (shortname): ' . $course['coursecode'],
                    ];
                    continue;
                }

                // Clone data cho core, đảm bảo id
                $core = $course;
                $core['id'] = $rec->id;
                unset($core['coursecode']);

                // Xử lý categorycode → categoryid
                if (!empty($course['categorycode'])) {
                    $cat = $DB->get_record('course_categories', ['idnumber' => $course['categorycode']]);
                    if ($cat) {
                        $core['categoryid'] = $cat->id; // CHỈ ĐƯỢC GÁN categoryid, KHÔNG PHẢI category
                    } else {
                        $results[] = [
                            'coursecode' => $course['coursecode'],
                            'status' => 'error',
                            'message' => 'Không tìm thấy danh mục với categorycode: ' . $course['categorycode'],
                        ];
                        continue;
                    }
                    unset($core['categorycode']);
                }

                $core_courses[] = $core;
                $results[] = [
                    'coursecode' => $course['coursecode'],
                    'status' => 'pending',
                    'message' => '',
                ];
            }

            // Nếu có bản ghi hợp lệ thì gọi core
            if ($core_courses) {
                try {
                    $updated = \core_course_external::update_courses($core_courses);
                    $updated = array_values($updated); // Đảm bảo thứ tự khớp
                    $pending_idx = 0;
                    foreach ($results as $k => &$res) {
                        if ($res['status'] === 'pending') {
                            $res['status'] = 'updated';
                            $res['message'] = 'Đã cập nhật thành công';
                            $pending_idx++;
                        }
                    }
                } catch (\Exception $e) {
                    foreach ($results as &$res) {
                        if ($res['status'] === 'pending') {
                            $res['status'] = 'error';
                            $res['message'] = $e->getMessage();
                        }
                    }
                }
            }
            return $results;
        }

        public static function update_courses_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'coursecode' => new external_value(PARAM_TEXT, 'Mã rút gọn khóa học (shortname)'),
                    'status' => new external_value(PARAM_TEXT, 'Trạng thái: updated | not_found | error'),
                    'message' => new external_value(PARAM_TEXT, 'Thông báo'),
                ])
            );
        }

        public static function create_courses_parameters()
        {
            $courseconfig = get_config('moodlecourse'); // needed for default values
            return new external_function_parameters(
                [
                    'courses' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'fullname' => new external_value(PARAM_TEXT, 'Tên đầy đủ khóa học'),
                                'shortname' => new external_value(PARAM_TEXT, 'Mã rút gọn khóa học'),
                                // Sử dụng categorycode thay cho categoryid (idnumber của category)
                                'categorycode' => new external_value(PARAM_RAW, 'Mã idnumber của danh mục khóa học', VALUE_REQUIRED),
                                'idnumber' => new external_value(PARAM_RAW, 'ID number của khóa học', VALUE_OPTIONAL),
                                'summary' => new external_value(PARAM_RAW, 'Mô tả khóa học', VALUE_OPTIONAL),
                                'summaryformat' => new external_format_value('summary', VALUE_DEFAULT),
                                'format' => new external_value(
                                    PARAM_PLUGIN,
                                    'Định dạng khóa học: weeks, topics, social, site,...',
                                    VALUE_DEFAULT,
                                    $courseconfig->format
                                ),
                                'showgrades' => new external_value(
                                    PARAM_INT,
                                    '1 nếu hiện điểm, 0 nếu ẩn',
                                    VALUE_DEFAULT,
                                    $courseconfig->showgrades
                                ),
                                'newsitems' => new external_value(
                                    PARAM_INT,
                                    'Số tin mới trên trang khóa học',
                                    VALUE_DEFAULT,
                                    $courseconfig->newsitems
                                ),
                                'startdate' => new external_value(
                                    PARAM_INT,
                                    'Thời gian bắt đầu (timestamp)',
                                    VALUE_OPTIONAL
                                ),
                                'enddate' => new external_value(
                                    PARAM_INT,
                                    'Thời gian kết thúc (timestamp)',
                                    VALUE_OPTIONAL
                                ),
                                'numsections' => new external_value(
                                    PARAM_INT,
                                    '(deprecated, dùng courseformatoptions) Số chủ đề/tuần',
                                    VALUE_OPTIONAL
                                ),
                                'maxbytes' => new external_value(
                                    PARAM_INT,
                                    'Kích thước file lớn nhất',
                                    VALUE_DEFAULT,
                                    $courseconfig->maxbytes
                                ),
                                'showreports' => new external_value(
                                    PARAM_INT,
                                    'Hiện báo cáo hoạt động (1: có, 0: không)',
                                    VALUE_DEFAULT,
                                    $courseconfig->showreports
                                ),
                                'visible' => new external_value(
                                    PARAM_INT,
                                    'Hiển thị (1: hiển thị, 0: ẩn)',
                                    VALUE_OPTIONAL
                                ),
                                'hiddensections' => new external_value(
                                    PARAM_INT,
                                    '(deprecated, dùng courseformatoptions) Cách hiển thị các phần bị ẩn cho sinh viên',
                                    VALUE_OPTIONAL
                                ),
                                'groupmode' => new external_value(
                                    PARAM_INT,
                                    'Kiểu nhóm (0: none, 1: riêng biệt, 2: nhìn thấy nhau)',
                                    VALUE_DEFAULT,
                                    $courseconfig->groupmode
                                ),
                                'groupmodeforce' => new external_value(
                                    PARAM_INT,
                                    'Bắt buộc kiểu nhóm (1: có, 0: không)',
                                    VALUE_DEFAULT,
                                    $courseconfig->groupmodeforce
                                ),
                                'defaultgroupingid' => new external_value(
                                    PARAM_INT,
                                    'ID nhóm mặc định',
                                    VALUE_DEFAULT,
                                    0
                                ),
                                'enablecompletion' => new external_value(
                                    PARAM_INT,
                                    'Bật hoàn thành hoạt động (1: có, 0: không)',
                                    VALUE_OPTIONAL
                                ),
                                'completionnotify' => new external_value(
                                    PARAM_INT,
                                    'Thông báo hoàn thành (1: có, 0: không)',
                                    VALUE_OPTIONAL
                                ),
                                'lang' => new external_value(
                                    PARAM_SAFEDIR,
                                    'Ngôn ngữ ép buộc cho khóa học',
                                    VALUE_OPTIONAL
                                ),
                                'forcetheme' => new external_value(
                                    PARAM_PLUGIN,
                                    'Theme bắt buộc',
                                    VALUE_OPTIONAL
                                ),
                                'courseformatoptions' => new external_multiple_structure(
                                    new external_single_structure(
                                        [
                                            'name' => new external_value(PARAM_ALPHANUMEXT, 'Tên option định dạng khóa học'),
                                            'value' => new external_value(PARAM_RAW, 'Giá trị option'),
                                        ]
                                    ),
                                    'Tùy chọn thêm cho định dạng khóa học',
                                    VALUE_OPTIONAL
                                ),
                                'customfields' => new external_multiple_structure(
                                    new external_single_structure(
                                        [
                                            'shortname' => new external_value(PARAM_ALPHANUMEXT, 'Shortname của custom field'),
                                            'value' => new external_value(PARAM_RAW, 'Giá trị custom field'),
                                        ]
                                    ),
                                    'Custom fields cho khóa học',
                                    VALUE_OPTIONAL
                                ),
                            ]
                        ),
                        'Danh sách các khóa học cần tạo'
                    ),
                ]
            );
        }

        public static function create_courses($courses)
        {
            global $DB;

            $params = self::validate_parameters(self::create_courses_parameters(), ['courses' => $courses]);
            $core_courses = [];
            $results = [];

            foreach ($params['courses'] as $course) {
                // Tra cứu category theo idnumber (categorycode)
                $category = $DB->get_record('course_categories', ['idnumber' => $course['categorycode']]);
                if (!$category) {
                    $results[] = [
                        'idnumber' => $course['idnumber'] ?? '',
                        'fullname' => $course['fullname'],
                        'shortname' => $course['shortname'],
                        'status' => 'error',
                        'message' => 'Không tìm thấy category với categorycode: ' . $course['categorycode'],
                    ];
                    continue;
                }

                // Clone toàn bộ mảng course
                $core = $course;
                $core['categoryid'] = $category->id;
                unset($core['categorycode']); // Xóa categorycode vì core không dùng field này

                $core_courses[] = $core;

                $results[] = [
                    'idnumber' => $course['idnumber'] ?? '',
                    'fullname' => $course['fullname'],
                    'shortname' => $course['shortname'],
                    'status' => 'pending',
                    'message' => '',
                ];
            }

            // Gọi core nếu có bản ghi hợp lệ
            if ($core_courses) {
                try {
                    $created = \core_course_external::create_courses($core_courses);
                    $created = array_values($created); // Đảm bảo thứ tự khớp
                    $pending_idx = 0;
                    foreach ($results as $k => &$res) {
                        if ($res['status'] === 'pending') {
                            $newcourse = $created[$pending_idx] ?? null;
                            $pending_idx++;
                            if ($newcourse && !empty($newcourse['id'])) {
                                $res['status'] = 'created';
                                $res['courseid'] = $newcourse['id'];
                                $res['message'] = 'Tạo thành công';
                            } else {
                                $res['status'] = 'error';
                                $res['message'] = 'Không rõ lỗi khi tạo';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    foreach ($results as &$res) {
                        if ($res['status'] === 'pending') {
                            $res['status'] = 'error';
                            $res['message'] = $e->getMessage();
                        }
                    }
                }
            }
            return $results;
        }

        public static function create_courses_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'idnumber' => new external_value(PARAM_RAW, 'ID number'),
                    'fullname' => new external_value(PARAM_TEXT, 'Tên đầy đủ'),
                    'shortname' => new external_value(PARAM_TEXT, 'Shortname'),
                    'status' => new external_value(PARAM_TEXT, 'created | error'),
                    'message' => new external_value(PARAM_TEXT, 'Thông báo'),
                    'courseid' => new external_value(PARAM_INT, 'Course ID LMS', VALUE_OPTIONAL),
                ])
            );
        }

        /**
         * Parameters for thc_ops_create_categories.
         */
        public static function create_categories_parameters()
        {
            return new external_function_parameters([
                'categories' => new external_multiple_structure(
                    new external_single_structure([
                        'name' => new external_value(PARAM_TEXT, 'Tên danh mục', VALUE_REQUIRED),
                        'parent_categorycode' => new external_value(PARAM_RAW, 'Mã idnumber của danh mục cha', VALUE_OPTIONAL),
                        'idnumber' => new external_value(PARAM_RAW, 'ID number (category code)', VALUE_OPTIONAL),
                        'description' => new external_value(PARAM_RAW, 'Mô tả', VALUE_OPTIONAL),
                        'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                        'theme' => new external_value(PARAM_THEME, 'Giao diện', VALUE_OPTIONAL),
                        'visible' => new external_value(PARAM_INT, 'Hiển thị', VALUE_OPTIONAL),
                    ])
                ),
            ]);
        }

        /**
         * Wrapper: Tạo mới danh mục khóa học theo parent_categorycode/idnumber, dùng core_course_create_categories.
         *
         * @param array $categories
         * @return array
         */
        public static function create_categories($categories)
        {
            global $DB;

            $params = self::validate_parameters(self::create_categories_parameters(), ['categories' => $categories]);
            $core_categories = [];
            $results = [];

            foreach ($params['categories'] as $cat) {
                $corecat = [
                    'name' => $cat['name'],
                ];

                // Xử lý parent_categorycode thành parent (id)
                if (!empty($cat['parent_categorycode'])) {
                    $parent = $DB->get_record('course_categories', ['idnumber' => $cat['parent_categorycode']]);
                    if ($parent) {
                        $corecat['parent'] = $parent->id;
                    } else {
                        $results[] = [
                            'idnumber' => $cat['idnumber'] ?? '',
                            'name' => $cat['name'],
                            'status' => 'error',
                            'message' => 'Không tìm thấy danh mục cha với parent_categorycode: ' . $cat['parent_categorycode'],
                        ];
                        continue;
                    }
                }

                // Các trường tùy chọn
                foreach (['idnumber', 'description', 'descriptionformat', 'theme', 'visible'] as $f) {
                    if (isset($cat[$f])) {
                        $corecat[$f] = $cat[$f];
                    }
                }

                $core_categories[] = $corecat;
                $results[] = [
                    'idnumber' => $cat['idnumber'] ?? '',
                    'name' => $cat['name'],
                    'status' => 'pending',
                    'message' => '',
                ];
            }

            if ($core_categories) {

                try {
                    $created = \core_course_external::create_categories($core_categories);

                    // Gán id vừa tạo về cho từng kết quả pending
                    foreach ($results as $k => &$res) {
                        if ($res['status'] === 'pending') {
                            $cat_data = $created[$k] ?? null;
                            if ($cat_data && !empty($cat_data['id'])) {
                                $res['status'] = 'created';
                                $res['message'] = 'Tạo thành công. ID: ' . $cat_data['id'];
                                $res['lmsid'] = $cat_data['id'];
                            } else {
                                $res['status'] = 'error';
                                $res['message'] = 'Không xác định được ID danh mục LMS sau khi tạo.';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    foreach ($results as &$res) {
                        if ($res['status'] === 'pending') {
                            $res['status'] = 'error';
                            $res['message'] = $e->getMessage();
                        }
                    }
                }
            }

            return $results;
        }

        /**
         * Kết quả trả về cho thc_ops_create_categories.
         */
        public static function create_categories_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'idnumber' => new external_value(PARAM_RAW, 'ID number (category code)'),
                    'name' => new external_value(PARAM_TEXT, 'Tên danh mục'),
                    'status' => new external_value(PARAM_TEXT, 'Trạng thái: created | error'),
                    'message' => new external_value(PARAM_TEXT, 'Thông báo'),
                    'lmsid' => new external_value(PARAM_INT, 'LMS Category ID', VALUE_OPTIONAL),
                ])
            );
        }

        public static function get_categories_by_ids_or_idnumbers_parameters()
        {
            return new external_function_parameters([
                'ids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'LMS category id'),
                    'Danh sách LMS id',
                    VALUE_DEFAULT, // Giá trị mặc định cho phép không truyền (optional)
                    []
                ),
                'idnumbers' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'LMS category idnumber'),
                    'Danh sách LMS idnumber',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }

        public static function get_categories_by_ids_or_idnumbers($ids = [], $idnumbers = [])
        {
            global $DB;

            $params = self::validate_parameters(self::get_categories_by_ids_or_idnumbers_parameters(), [
                'ids' => $ids,
                'idnumbers' => $idnumbers,
            ]);

            $ids = $params['ids'];
            $idnumbers = $params['idnumbers'];

            $results = [];

            // Build SQL
            $wheres = [];
            $sqlparams = [];

            if (!empty($ids)) {
                list($in_sql, $in_params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'id_');
                $wheres[] = "id $in_sql";
                $sqlparams = array_merge($sqlparams, $in_params);
            }
            if (!empty($idnumbers)) {
                list($in_sql2, $in_params2) = $DB->get_in_or_equal($idnumbers, SQL_PARAMS_NAMED, 'idnumber_');
                $wheres[] = "idnumber $in_sql2";
                $sqlparams = array_merge($sqlparams, $in_params2);
            }

            if ($wheres) {
                $sql = "SELECT * FROM {course_categories} WHERE " . implode(" OR ", $wheres);
                $categories = $DB->get_records_sql($sql, $sqlparams);

                foreach ($categories as $cat) {
                    $results[] = [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'idnumber' => $cat->idnumber,
                        'parent' => $cat->parent,
                        'visible' => $cat->visible,
                        // add more fields if needed
                    ];
                }
            }

            return $results;
        }

        public static function get_categories_by_ids_or_idnumbers_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'LMS category id'),
                    'name' => new external_value(PARAM_TEXT, 'Tên danh mục'),
                    'idnumber' => new external_value(PARAM_RAW, 'ID number'),
                    'parent' => new external_value(PARAM_INT, 'ID cha'),
                    'visible' => new external_value(PARAM_BOOL, 'Hiển thị'),
                    // thêm các trường khác nếu muốn
                ])
            );
        }

        public static function update_users_by_username_parameters()
        {
            // Giống như core_user_external::update_users_parameters, bỏ trường id, xác định user qua username.
            $userfields = [
                'username' => new external_value(
                    core_user::get_property_type('username'),
                    'Username policy is defined in Moodle security config.'
                ),
                'auth' => new external_value(
                    core_user::get_property_type('auth'),
                    'Auth plugins include manual, ldap, etc',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'suspended' => new external_value(
                    core_user::get_property_type('suspended'),
                    'Suspend user account, either false to enable user login or true to disable it',
                    VALUE_OPTIONAL
                ),
                'password' => new external_value(
                    core_user::get_property_type('password'),
                    'Plain text password consisting of any characters',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'firstname' => new external_value(
                    core_user::get_property_type('firstname'),
                    'The first name(s) of the user',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'lastname' => new external_value(
                    core_user::get_property_type('lastname'),
                    'The family name of the user',
                    VALUE_OPTIONAL
                ),
                'email' => new external_value(
                    core_user::get_property_type('email'),
                    'A valid and unique email address',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email display', VALUE_OPTIONAL),
                'city' => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'country' => new external_value(
                    core_user::get_property_type('country'),
                    'Home country code of the user, such as AU or CZ',
                    VALUE_OPTIONAL
                ),
                'timezone' => new external_value(
                    core_user::get_property_type('timezone'),
                    'Timezone code such as Australia/Perth, or 99 for default',
                    VALUE_OPTIONAL
                ),
                'description' => new external_value(
                    core_user::get_property_type('description'),
                    'User profile description, no HTML',
                    VALUE_OPTIONAL
                ),
                // User picture.
                'userpicture' => new external_value(
                    PARAM_INT,
                    'The itemid where the new user picture has been uploaded to, 0 to delete',
                    VALUE_OPTIONAL
                ),
                // Additional names.
                'firstnamephonetic' => new external_value(
                    core_user::get_property_type('firstnamephonetic'),
                    'The first name(s) phonetically of the user',
                    VALUE_OPTIONAL
                ),
                'lastnamephonetic' => new external_value(
                    core_user::get_property_type('lastnamephonetic'),
                    'The family name phonetically of the user',
                    VALUE_OPTIONAL
                ),
                'middlename' => new external_value(
                    core_user::get_property_type('middlename'),
                    'The middle name of the user',
                    VALUE_OPTIONAL
                ),
                'alternatename' => new external_value(
                    core_user::get_property_type('alternatename'),
                    'The alternate name of the user',
                    VALUE_OPTIONAL
                ),
                // Interests.
                'interests' => new external_value(PARAM_TEXT, 'User interests (separated by commas)', VALUE_OPTIONAL),
                // Optional.
                'url' => new external_value(PARAM_URL, 'User web page', VALUE_OPTIONAL),
                'idnumber' => new external_value(
                    core_user::get_property_type('idnumber'),
                    'An arbitrary ID code number perhaps from the institution',
                    VALUE_OPTIONAL
                ),
                'institution' => new external_value(core_user::get_property_type('institution'), 'Institution', VALUE_OPTIONAL),
                'department' => new external_value(core_user::get_property_type('department'), 'Department', VALUE_OPTIONAL),
                'phone1' => new external_value(core_user::get_property_type('phone1'), 'Phone', VALUE_OPTIONAL),
                'phone2' => new external_value(core_user::get_property_type('phone2'), 'Mobile phone', VALUE_OPTIONAL),
                'address' => new external_value(core_user::get_property_type('address'), 'Postal address', VALUE_OPTIONAL),
                // Other user preferences stored in the user table.
                'lang' => new external_value(
                    core_user::get_property_type('lang'),
                    'Language code such as "en", must exist on server',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'calendartype' => new external_value(
                    core_user::get_property_type('calendartype'),
                    'Calendar type such as "gregorian", must exist on server',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'theme' => new external_value(
                    core_user::get_property_type('theme'),
                    'Theme name such as "standard", must exist on server',
                    VALUE_OPTIONAL
                ),
                'mailformat' => new external_value(
                    core_user::get_property_type('mailformat'),
                    'Mail format code is 0 for plain text, 1 for HTML etc',
                    VALUE_OPTIONAL
                ),
                // Custom user profile fields.
                'customfields' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type' => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                            'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                        ]
                    ),
                    'User custom fields (also known as user profil fields)',
                    VALUE_OPTIONAL
                ),
                // User preferences.
                'preferences' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'type' => new external_value(PARAM_RAW, 'The name of the preference'),
                            'value' => new external_value(PARAM_RAW, 'The value of the preference'),
                        ]
                    ),
                    'User preferences',
                    VALUE_OPTIONAL
                ),
            ];

            return new external_function_parameters([
                'users' => new external_multiple_structure(
                    new external_single_structure($userfields)
                ),
            ]);
        }

        public static function update_users_by_username($users)
        {
            global $DB, $CFG;

            $core_input = [];
            $results = [];
            $warnings = [];

            foreach ($users as $user) {
                if (empty($user['username'])) {
                    $warnings[] = [
                        'item' => 'user',
                        'itemid' => 0,
                        'warningcode' => 'missing_username',
                        'message' => 'Thiếu trường username'
                    ];
                    continue;
                }

                $record = $DB->get_record('user', ['username' => $user['username'], 'deleted' => 0], '*', IGNORE_MISSING);
                if (!$record) {
                    $warnings[] = [
                        'item' => 'user',
                        'itemid' => 0,
                        'warningcode' => 'user_not_found',
                        'message' => "Không tìm thấy người dùng với username: {$user['username']}"
                    ];
                    continue;
                }

                $user['id'] = $record->id;
                $core_input[] = $user;
            }


            if ($core_input) {
                // Lưu trạng thái debug hiện tại
                $debug_old = $CFG->debug ?? 0;
                $debugdisplay_old = $CFG->debugdisplay ?? false;

                // Bật debug tạm thời để log lỗi chi tiết hơn
                $CFG->debug = DEBUG_DEVELOPER;
                $CFG->debugdisplay = true;

                try {
                    $result = \core_user_external::update_users($core_input);

                    // $result = \core_user_external::update_users($core_input);
                    // Nếu $result là mảng và có key 'warnings', ta merge thêm các cảnh báo cũ vào
                    if (!empty($warnings)) {
                        if (!isset($result['warnings'])) {
                            $result['warnings'] = [];
                        }

                        $result['warnings'] = array_merge($warnings, $result['warnings']);
                    }

                    return $result;
                } catch (Exception $e) {

                    foreach ($core_input as $u) {
                        $warnings[] = [
                            'item' => 'user',
                            'itemid' => $u['id'],
                            'warningcode' => $e instanceof \moodle_exception ? $e->errorcode : 'exception',
                            'message' => $e->getMessage(),
                        ];
                    }
                }
                // Khôi phục debug
                $CFG->debug = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;
            }

            $warnings = ['warnings' => $warnings];
            return $warnings;
        }


        public static function update_users_by_username_returns()
        {
            return new external_single_structure([
                'warnings' => new external_multiple_structure(
                    new external_single_structure([
                        'item' => new external_value(PARAM_TEXT, 'Loại phần tử gây lỗi (ví dụ: user)', VALUE_OPTIONAL),
                        'itemid' => new external_value(PARAM_INT, 'ID của phần tử nếu có', VALUE_OPTIONAL),
                        'warningcode' => new external_value(PARAM_ALPHANUMEXT, 'Mã lỗi có thể dùng để xử lý phía client'),
                        'message' => new external_value(PARAM_TEXT, 'Thông điệp mô tả lỗi')
                    ])
                )
            ]);
        }



        public static function update_categories_by_categorycode_parameters()
        {
            return new external_function_parameters(
                [
                    'categories' => new external_multiple_structure(
                        new external_single_structure([
                            'categorycode' => new external_value(PARAM_RAW, 'Mã danh mục (idnumber)', VALUE_REQUIRED),
                            'name' => new external_value(PARAM_TEXT, 'Tên danh mục', VALUE_OPTIONAL),
                            'parent_categorycode' => new external_value(PARAM_RAW, 'Mã danh mục cha (idnumber)', VALUE_OPTIONAL),
                            'description' => new external_value(PARAM_RAW, 'Mô tả', VALUE_OPTIONAL),
                            'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                            'theme' => new external_value(PARAM_THEME, 'Giao diện danh mục', VALUE_OPTIONAL),
                        ])
                    ),
                ]
            );
        }

        /**
         * Wrapper: Cập nhật danh mục theo categorycode (idnumber), dùng lại hàm update_categories gốc của Moodle.
         *
         * @param array $categories
         * @return array
         */
        public static function update_categories_by_categorycode($categories)
        {
            global $DB;

            $params = self::validate_parameters(self::update_categories_by_categorycode_parameters(), ['categories' => $categories]);
            $core_categories = [];
            $results = [];

            foreach ($params['categories'] as $cat) {
                // Tra cứu theo idnumber (categorycode)
                $category = $DB->get_record('course_categories', ['idnumber' => $cat['categorycode']]);
                if (!$category) {
                    $results[] = [
                        'categorycode' => $cat['categorycode'],
                        'status' => 'not_found',
                        'message' => 'Không tìm thấy danh mục với categorycode (idnumber): ' . $cat['categorycode'],
                    ];
                    continue;
                }

                // Chuẩn bị object đúng format core
                $corecat = ['id' => $category->id];
                foreach (['name', 'description', 'descriptionformat', 'theme'] as $f) {
                    if (array_key_exists($f, $cat)) {
                        $corecat[$f] = $cat[$f];
                    }
                }

                // Xử lý parent_categorycode → parent (id)
                // Xử lý parent_categorycode → parent (id)
                if (array_key_exists('parent_categorycode', $cat)) {
                    if (!empty($cat['parent_categorycode'])) {
                        $parent = $DB->get_record('course_categories', ['idnumber' => $cat['parent_categorycode']]);
                        if ($parent) {
                            $corecat['parent'] = $parent->id;
                        } else {
                            $results[] = [
                                'categorycode' => $cat['categorycode'],
                                'status' => 'error',
                                'message' => 'Không tìm thấy danh mục cha với parent_categorycode: ' . $cat['parent_categorycode'],
                            ];
                            continue;
                        }
                    } else {
                        // Nếu truyền parent_categorycode rỗng thì cũng coi như về gốc
                        $corecat['parent'] = 0;
                    }
                } else {
                    // Không truyền parent_categorycode
                    $corecat['parent'] = 0;
                }

                $core_categories[] = $corecat;
                $results[] = [
                    'categorycode' => $cat['categorycode'],
                    'status' => 'pending',
                    'message' => '', // Sẽ cập nhật lại sau khi gọi core
                ];
            }

            // Nếu có danh mục hợp lệ thì gọi hàm core
            if ($core_categories) {

                try {
                    // Gọi lại hàm core (bạn có thể require file externallib.php của core_course nếu cần)

                    // print_object($core_categories);

                    $result = core_course_external::update_categories($core_categories);

                    // Cập nhật trạng thái thành công cho các bản ghi hợp lệ
                    foreach ($results as &$res) {
                        if ($res['status'] === 'pending') {
                            $res['status'] = 'updated';
                            $res['message'] = 'Cập nhật thành công.';
                        }
                    }
                } catch (\Exception $e) {
                    // Nếu có lỗi toàn cục, báo lỗi tất cả danh mục pending
                    foreach ($results as &$res) {
                        if ($res['status'] === 'pending') {
                            $res['status'] = 'error';
                            $res['message'] = $e->getMessage();
                        }
                    }
                }
            }

            return $results;
        }

        /**
         * Định nghĩa cấu trúc trả về cho hàm update_categories_by_categorycode.
         */
        public static function update_categories_by_categorycode_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'categorycode' => new external_value(PARAM_RAW, 'Mã danh mục (idnumber)'),
                    'status' => new external_value(PARAM_TEXT, 'Trạng thái: updated | not_found | error'),
                    'message' => new external_value(PARAM_TEXT, 'Thông báo'),
                ])
            );
        }

        /**
         * Lấy danh sách tất cả vai trò trong hệ thống Moodle.
         *
         * @return external_multiple_structure Danh sách các vai trò (id, shortname, name, description)
         */
        public static function get_roles_parameters()
        {
            return new external_function_parameters([
                // Không có tham số truyền vào
            ]);
        }

        public static function get_roles()
        {
            global $DB;

            $context = context_system::instance();
            self::validate_context($context);

            // Kiểm tra capability can_view_all_roles
            if (!has_capability('local/th_ops:can_view_all_roles', $context)) {
                throw new required_capability_exception(
                    $context,
                    'local/th_ops:can_view_all_roles',
                    'nopermissions',
                    'Bạn không có quyền xem tất cả vai trò'
                );
            }

            require_once $GLOBALS['CFG']->libdir . '/accesslib.php';

            // Chỉ lấy role có contextlevel là CONTEXT_COURSE (50)
            $sql = "
            SELECT r.id, r.shortname, r.name, r.description
            FROM {role} r
            JOIN {role_context_levels} rcl ON rcl.roleid = r.id
            WHERE rcl.contextlevel = :contextcourse
            GROUP BY r.id, r.shortname, r.name, r.description
            ORDER BY r.sortorder ASC
        ";
            $params = ['contextcourse' => CONTEXT_COURSE]; // 50

            $roles = $DB->get_records_sql($sql, $params);

            $result = [];
            foreach ($roles as $role) {
                $result[] = [
                    'id' => $role->id,
                    'shortname' => $role->shortname,
                    'name' => $role->name,
                    'description' => strip_tags($role->description),
                ];
            }
            return $result;
        }

        public static function get_roles_returns()
        {
            return new external_multiple_structure(
                new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'ID vai trò'),
                        'shortname' => new external_value(PARAM_TEXT, 'Shortname'),
                        'name' => new external_value(PARAM_TEXT, 'Tên vai trò'),
                        'description' => new external_value(PARAM_TEXT, 'Mô tả'),
                    ]
                )
            );
        }

        public static function update_roles_by_rolecode_parameters()
        {
            return new external_function_parameters([
                'roles' => new external_multiple_structure(
                    new external_single_structure([
                        'shortname' => new external_value(PARAM_TEXT, 'Shortname vai trò (rolecode, bắt buộc)'),
                        'name' => new external_value(PARAM_TEXT, 'Tên vai trò mới (name, bắt buộc)'),
                    ])
                ),
            ]);
        }

        public static function update_roles_by_rolecode($roles)
        {
            global $DB;

            $context = context_system::instance();
            self::validate_context($context);

            // Chỉ cho phép user có quyền quản lý vai trò
            if (!has_capability('moodle/role:manage', $context)) {
                throw new required_capability_exception(
                    $context,
                    'moodle/role:manage',
                    'nopermissions',
                    'Bạn không có quyền cập nhật vai trò'
                );
            }

            $results = [];
            require_once $GLOBALS['CFG']->libdir . '/accesslib.php';

            foreach ($roles as $data) {
                $shortname = trim($data['shortname']);
                $name = trim($data['name']);

                // Tìm vai trò theo shortname
                $role = $DB->get_record('role', ['shortname' => $shortname], '*', IGNORE_MISSING);

                if ($role) {
                    // Cập nhật chỉ trường name
                    $DB->update_record('role', [
                        'id' => $role->id,
                        'name' => $name,
                    ]);
                    $results[] = [
                        'id' => $role->id,
                        'shortname' => $shortname,
                        'status' => 'updated',
                    ];
                } else {
                    $results[] = [
                        'shortname' => $shortname,
                        'status' => 'not_found',
                    ];
                }
            }

            return $results;
        }

        public static function update_roles_by_rolecode_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'ID vai trò', VALUE_OPTIONAL),
                    'shortname' => new external_value(PARAM_TEXT, 'Shortname vai trò'),
                    'status' => new external_value(PARAM_TEXT, 'Trạng thái cập nhật: updated | not_found'),
                ])
            );
        }

        /**
         * Định nghĩa tham số đầu vào cho API get_enrolled_users (bổ sung tham số options nếu muốn linh hoạt)
         *
         * @return external_function_parameters
         */
        public static function get_enrolled_users_parameters()
        {
            return new external_function_parameters([
                'courseid' => new external_value(PARAM_INT, 'ID của khóa học'),
                'options' => new external_multiple_structure(
                    new external_single_structure([
                        'name' => new external_value(PARAM_ALPHANUMEXT, 'Option name'),
                        'value' => new external_value(PARAM_RAW, 'Option value'),
                    ]),
                    'Các tùy chọn filter giống core_enrol_get_enrolled_users (có thể để trống)',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }

        /**
         * Lấy danh sách user đã ghi danh vào khóa học, kèm chi tiết từng lần enrolment
         *
         * @param int $courseid
         * @param array $options
         * @return array
         */
        public static function get_enrolled_users($courseid, $options = [])
        {
            global $DB, $CFG;

            // Lấy danh sách user chuẩn (tận dụng core function - giữ đúng filter/quyền gốc)

            require_once $CFG->dirroot . '/enrol/externallib.php';

            $users = \core_enrol_external::get_enrolled_users($courseid, $options);

            foreach ($users as &$user) {
                // Lấy thông tin các lần ghi danh (user_enrolments + enrol instance)

                $userenrols = $DB->get_records_sql("
                SELECT ue.*, e.enrol
                FROM {user_enrolments} ue
                JOIN {enrol} e ON ue.enrolid = e.id
                WHERE ue.userid = :userid AND e.courseid = :courseid
            ", ['userid' => $user['id'], 'courseid' => $courseid]);

                $user['enrolments'] = [];
                foreach ($userenrols as $enrol) {
                    $user['enrolments'][] = [
                        'userenrolid' => $enrol->id,
                        'enrolid' => $enrol->enrolid,
                        'enrol_method' => $enrol->enrol,
                        'status' => $enrol->status,
                        'timestart' => $enrol->timestart,
                        'timeend' => $enrol->timeend,
                        'duration' => ($enrol->timeend && $enrol->timestart) ? ($enrol->timeend - $enrol->timestart) : null,
                        'timecreated' => $enrol->timecreated,
                        'timemodified' => $enrol->timemodified,
                    ];
                }
            }

            return $users;
        }

        /**
         * Cấu trúc trả về của get_enrolled_users (có bổ sung field enrolments)
         */
        public static function get_enrolled_users_returns()
        {
            return new external_multiple_structure(
                new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'ID of the user'),
                        'username' => new external_value(PARAM_RAW, 'Username policy is defined in Moodle security config', VALUE_OPTIONAL),
                        'firstname' => new external_value(PARAM_NOTAGS, 'The first name(s) of the user', VALUE_OPTIONAL),
                        'lastname' => new external_value(PARAM_NOTAGS, 'The family name of the user', VALUE_OPTIONAL),
                        'fullname' => new external_value(PARAM_NOTAGS, 'The fullname of the user'),
                        'email' => new external_value(PARAM_TEXT, 'An email address - allow email as root@localhost', VALUE_OPTIONAL),
                        'address' => new external_value(PARAM_TEXT, 'Postal address', VALUE_OPTIONAL),
                        'phone1' => new external_value(PARAM_NOTAGS, 'Phone 1', VALUE_OPTIONAL),
                        'phone2' => new external_value(PARAM_NOTAGS, 'Phone 2', VALUE_OPTIONAL),
                        'icq' => new external_value(PARAM_NOTAGS, 'icq number', VALUE_OPTIONAL),
                        'skype' => new external_value(PARAM_NOTAGS, 'skype id', VALUE_OPTIONAL),
                        'yahoo' => new external_value(PARAM_NOTAGS, 'yahoo id', VALUE_OPTIONAL),
                        'aim' => new external_value(PARAM_NOTAGS, 'aim id', VALUE_OPTIONAL),
                        'msn' => new external_value(PARAM_NOTAGS, 'msn number', VALUE_OPTIONAL),
                        'department' => new external_value(PARAM_TEXT, 'department', VALUE_OPTIONAL),
                        'institution' => new external_value(PARAM_TEXT, 'institution', VALUE_OPTIONAL),
                        'idnumber' => new external_value(PARAM_RAW, 'An arbitrary ID code number perhaps from the institution', VALUE_OPTIONAL),
                        'interests' => new external_value(PARAM_TEXT, 'user interests (separated by commas)', VALUE_OPTIONAL),
                        'firstaccess' => new external_value(PARAM_INT, 'first access to the site (0 if never)', VALUE_OPTIONAL),
                        'lastaccess' => new external_value(PARAM_INT, 'last access to the site (0 if never)', VALUE_OPTIONAL),
                        'lastcourseaccess' => new external_value(PARAM_INT, 'last access to the course (0 if never)', VALUE_OPTIONAL),
                        'description' => new external_value(PARAM_RAW, 'User profile description', VALUE_OPTIONAL),
                        'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                        'city' => new external_value(PARAM_NOTAGS, 'Home city of the user', VALUE_OPTIONAL),
                        'url' => new external_value(PARAM_URL, 'URL of the user', VALUE_OPTIONAL),
                        'country' => new external_value(PARAM_ALPHA, 'Home country code of the user, such as AU or CZ', VALUE_OPTIONAL),
                        'profileimageurlsmall' => new external_value(PARAM_URL, 'User image profile URL - small version', VALUE_OPTIONAL),
                        'profileimageurl' => new external_value(PARAM_URL, 'User image profile URL - big version', VALUE_OPTIONAL),
                        'customfields' => new external_multiple_structure(
                            new external_single_structure(
                                [
                                    'type' => new external_value(PARAM_ALPHANUMEXT, 'The type of the custom field - text field, checkbox...'),
                                    'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                                    'name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                                    'shortname' => new external_value(PARAM_RAW, 'The shortname of the custom field - to be able to build the field class in the code'),
                                ]
                            ),
                            'User custom fields (also known as user profil fields)',
                            VALUE_OPTIONAL
                        ),
                        'groups' => new external_multiple_structure(
                            new external_single_structure(
                                [
                                    'id' => new external_value(PARAM_INT, 'group id'),
                                    'name' => new external_value(PARAM_RAW, 'group name'),
                                    'description' => new external_value(PARAM_RAW, 'group description'),
                                    'descriptionformat' => new external_format_value('description'),
                                ]
                            ),
                            'user groups',
                            VALUE_OPTIONAL
                        ),
                        'roles' => new external_multiple_structure(
                            new external_single_structure(
                                [
                                    'roleid' => new external_value(PARAM_INT, 'role id'),
                                    'name' => new external_value(PARAM_RAW, 'role name'),
                                    'shortname' => new external_value(PARAM_ALPHANUMEXT, 'role shortname'),
                                    'sortorder' => new external_value(PARAM_INT, 'role sortorder'),
                                ]
                            ),
                            'user roles',
                            VALUE_OPTIONAL
                        ),
                        'preferences' => new external_multiple_structure(
                            new external_single_structure(
                                [
                                    'name' => new external_value(PARAM_RAW, 'The name of the preferences'),
                                    'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                                ]
                            ),
                            'User preferences',
                            VALUE_OPTIONAL
                        ),
                        'enrolledcourses' => new external_multiple_structure(
                            new external_single_structure(
                                [
                                    'id' => new external_value(PARAM_INT, 'Id of the course'),
                                    'fullname' => new external_value(PARAM_RAW, 'Fullname of the course'),
                                    'shortname' => new external_value(PARAM_RAW, 'Shortname of the course'),
                                ]
                            ),
                            'Courses where the user is enrolled - limited by which courses the user is able to see',
                            VALUE_OPTIONAL
                        ),

                        // -- - Thêm trườngmới---
                        'enrolments' => new external_multiple_structure(
                            new external_single_structure([
                                'userenrolid' => new external_value(PARAM_INT, 'ID user_enrolment'),
                                'enrolid' => new external_value(PARAM_INT, 'ID instance enrol'),
                                'enrol_method' => new external_value(PARAM_TEXT, 'Phương thức ghi danh'),
                                'status' => new external_value(PARAM_INT, 'Trạng thái ghi danh, 0: active, 1: suspended'),
                                'timestart' => new external_value(PARAM_INT, 'Bắt đầu ghi danh'),
                                'timeend' => new external_value(PARAM_INT, 'Kết thúc ghi danh'),
                                'duration' => new external_value(PARAM_INT, 'Số giây ghi danh', VALUE_OPTIONAL),
                                'timecreated' => new external_value(PARAM_INT, 'Ngày tạo ghi danh'),
                                'timemodified' => new external_value(PARAM_INT, 'Ngày sửa ghi danh'),
                            ]),
                            'Danh sách các lần ghi danh vào khóa học'
                        ),

                    ]
                )
            );
        }

        public static function get_enrolled_users_by_coursecode_parameters()
        {
            return new external_function_parameters([
                'coursecode' => new external_value(PARAM_TEXT, 'Shortname (mã) của khóa học'),
                'options' => new external_multiple_structure(
                    new external_single_structure([
                        'name' => new external_value(PARAM_ALPHANUMEXT, 'Option name'),
                        'value' => new external_value(PARAM_RAW, 'Option value'),
                    ]),
                    'Các tùy chọn filter giống core_enrol_get_enrolled_users (có thể để trống)',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }

        /**
         * Lấy danh sách user đã ghi danh vào khóa học, theo course shortname
         *
         * @param string $coursecode Mã khóa học (shortname)
         * @param array $options Tùy chọn lọc
         * @return array
         */
        public static function get_enrolled_users_by_coursecode($coursecode, $options = [])
        {
            global $DB, $CFG;

            // Tìm ID khóa học theo shortname
            $course = $DB->get_record('course', ['shortname' => $coursecode], '*', MUST_EXIST);

            // Tận dụng lại hàm wrapper đã build (tương tự như get_enrolled_users)
            require_once $CFG->dirroot . '/enrol/externallib.php';
            $users = self::get_enrolled_users($course->id, $options);

            return $users;
        }

        public static function get_enrolled_users_by_coursecode_returns()
        {
            return self::get_enrolled_users_returns();
        }

        public static function get_enrolled_courses_users_by_coursecode_parameters()
        {
            return new external_function_parameters([
                'coursecodes' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Shortname (mã) của khóa học')
                ),
                'options' => new external_multiple_structure(
                    new external_single_structure([
                        'name' => new external_value(PARAM_ALPHANUMEXT, 'Option name'),
                        'value' => new external_value(PARAM_RAW, 'Option value'),
                    ]),
                    'Các tùy chọn filter giống core_enrol_get_enrolled_users (có thể để trống)',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }
        /**
         * Lấy danh sách user đã ghi danh cho nhiều khóa học theo danh sách coursecode
         *
         * @param array $coursecodes Danh sách shortname khóa học
         * @param array $options     Tùy chọn filter (giống core)
         * @return array Mỗi phần tử là ['coursecode'=>..., 'users'=>[...]]
         */
        public static function get_enrolled_courses_users_by_coursecode($coursecodes, $options = [])
        {
            global $DB, $CFG;

            $result = [];
            foreach ($coursecodes as $coursecode) {
                // Tìm course id
                $course = $DB->get_record('course', ['shortname' => $coursecode], '*', IGNORE_MISSING);
                if (!$course) {
                    $result[] = [
                        'coursecode' => $coursecode,
                        'users' => [],
                        'error' => 'Course not found',
                    ];
                    continue;
                }

                require_once $CFG->dirroot . '/enrol/externallib.php';
                $users = self::get_enrolled_users($course->id, $options);
                $result[] = [
                    'coursecode' => $coursecode,
                    'users' => $users,
                ];
            }
            return $result;
        }
        public static function get_enrolled_courses_users_by_coursecode_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'coursecode' => new external_value(PARAM_TEXT, 'Shortname của khóa học'),
                    'users' => self::get_enrolled_users_returns(),
                    'error' => new external_value(PARAM_TEXT, 'Thông báo lỗi (nếu có)', VALUE_OPTIONAL),
                ])
            );
        }


        public static function create_or_duplicate_course_parameters()
        {
            return new external_function_parameters([
                'courses' => new external_multiple_structure(
                    new external_single_structure([
                        'mode'             => new external_value(PARAM_ALPHA,      'template | new | clone', VALUE_DEFAULT, 'clone'),
                        'sourceshortname'  => new external_value(PARAM_TEXT,       'Shortname khoa nguon', VALUE_DEFAULT, null),
                        'fullname'         => new external_value(PARAM_TEXT,       'Ten day du khoa moi'),
                        'shortname'        => new external_value(PARAM_TEXT,       'Shortname khoa moi'),
                        'idnumber'         => new external_value(PARAM_RAW,        'ID number khoa moi', VALUE_OPTIONAL),
                        'categoryidnumber' => new external_value(PARAM_TEXT,       'ID number danh muc'),
                        'visible'          => new external_value(PARAM_INT,        'Visible (0/1)', VALUE_DEFAULT, 0),
                        'startdate'        => new external_value(PARAM_INT,        'Startdate UTC timestamp', VALUE_OPTIONAL),
                        'options'          => new external_multiple_structure(
                            new external_single_structure([
                                'name'  => new external_value(PARAM_ALPHAEXT, 'Ten option'),
                                'value' => new external_value(PARAM_RAW,      'Gia tri option'),
                            ]),
                            'Duplication options (users=0|1, role_X=roleid, ...)',
                            VALUE_DEFAULT,
                            []
                        ),
                        'courseformatoptions' => new external_multiple_structure(
                            new external_single_structure([
                                'name'  => new external_value(PARAM_ALPHANUMEXT, 'Ten format option'),
                                'value' => new external_value(PARAM_RAW,         'Gia tri format option'),
                            ]),
                            'Course format options (chi dung mode=new)',
                            VALUE_DEFAULT,
                            []
                        ),
                        'customfields' => new external_multiple_structure(
                            new external_single_structure([
                                'shortname' => new external_value(PARAM_ALPHANUMEXT, 'Shortname custom field'),
                                'value'     => new external_value(PARAM_RAW,         'Gia tri custom field'),
                            ]),
                            'Custom fields',
                            VALUE_DEFAULT,
                            []
                        ),
                    ])
                ),
            ]);
        }

        public static function create_or_duplicate_course($courses)
        {
            global $DB, $CFG, $USER;

            require_once($CFG->dirroot . '/course/externallib.php');

            $params = self::validate_parameters(
                self::create_or_duplicate_course_parameters(),
                ['courses' => $courses]
            );

            // validate_context chi goi MOT LAN trong external API.
            // Dung system context; cap phep cu the kiem tra bang require_capability
            // / require_all_capabilities ben trong vong lap.
            self::validate_context(\context_system::instance());

            $results = [];

            foreach ($params['courses'] as $course) {
                try {

                    // -----------------------------------------------------------------
                    // 1. Validate cac truong bat buoc
                    // -----------------------------------------------------------------
                    if (empty($course['fullname'])) {
                        self::set_last_copy_error($course['shortname'] ?? '', 'Thieu truong fullname.', 'missing_fullname');
                        $results[] = self::fail_result($course['shortname'] ?? '', 'Thieu truong fullname.');
                        continue;
                    }

                    if (empty($course['shortname'])) {
                        $results[] = self::fail_result('', 'Thieu truong shortname.');
                        continue;
                    }

                    if (empty($course['categoryidnumber'])) {
                        self::set_last_copy_error($course['shortname'], 'Thieu categoryidnumber.', 'missing_categoryidnumber');
                        $results[] = self::fail_result($course['shortname'], 'Thieu categoryidnumber.');
                        continue;
                    }

                    $mode = trim(strtolower($course['mode'] ?? 'clone'));
                    if (!in_array($mode, ['template', 'new', 'clone'], true)) {
                        self::set_last_copy_error($course['shortname'], "Gia tri mode khong hop le: {$course['mode']}.", 'invalid_mode');
                        $results[] = self::fail_result($course['shortname'], "Gia tri mode khong hop le: {$course['mode']}.");
                        continue;
                    }

                    $startdate = isset($course['startdate']) ? (int)$course['startdate'] : 0;

                    // -----------------------------------------------------------------
                    // 2. Resolve category
                    // -----------------------------------------------------------------
                    $categoryid = $DB->get_field(
                        'course_categories',
                        'id',
                        ['idnumber' => $course['categoryidnumber']],
                        IGNORE_MISSING
                    );

                    if (!$categoryid) {
                        self::set_last_copy_error(
                            $course['shortname'],
                            "Khong tim thay category idnumber='{$course['categoryidnumber']}'.",
                            'category_not_found'
                        );
                        $results[] = self::fail_result(
                            $course['shortname'],
                            "Khong tim thay category voi idnumber: {$course['categoryidnumber']}"
                        );
                        continue;
                    }

                    $catctx = \context_coursecat::instance($categoryid);
                    require_capability('moodle/course:create', $catctx);

                    // -----------------------------------------------------------------
                    // 3. Kiem tra shortname chua ton tai
                    // -----------------------------------------------------------------
                    if ($DB->record_exists('course', ['shortname' => $course['shortname']])) {
                        self::set_last_copy_error(
                            $course['shortname'],
                            "Shortname '{$course['shortname']}' da ton tai.",
                            'shortname_exists'
                        );
                        $results[] = self::fail_result(
                            $course['shortname'],
                            "Khoa hoc voi shortname '{$course['shortname']}' da ton tai."
                        );
                        continue;
                    }

                    // =================================================================
                    // MODE: new — tao khoa hoc rong, khong can backup libs
                    // =================================================================
                    if ($mode === 'new') {
                        if (empty($startdate) || $startdate <= 0) {
                            self::set_last_copy_error($course['shortname'], 'Thieu startdate cho mode=new.', 'missing_startdate');
                            $results[] = self::fail_result($course['shortname'], "Thieu 'startdate' (UNIX timestamp, UTC) cho mode=new.");
                            continue;
                        }

                        $newcourse = [
                            'fullname'            => $course['fullname'],
                            'shortname'           => $course['shortname'],
                            'categoryid'          => $categoryid,
                            'visible'             => (int)$course['visible'],
                            'startdate'           => $startdate,
                            'idnumber'            => !empty($course['idnumber']) ? $course['idnumber'] : $course['shortname'],
                            'courseformatoptions' => !empty($course['courseformatoptions'])
                                ? $course['courseformatoptions']
                                : [['name' => 'numsections', 'value' => get_config('moodlecourse', 'numsections') ?: 4]],
                        ];

                        if (!empty($course['customfields'])) {
                            $newcourse['customfields'] = $course['customfields'];
                        }

                        $created = \core_course_external::create_courses([$newcourse]);
                        self::clear_last_copy_error($course['shortname']);
                        $results[] = [
                            'status'    => 'Done',
                            'id'        => (int)$created[0]['id'],
                            'shortname' => (string)$created[0]['shortname'],
                            'message'   => 'Empty course created (mode=new).',
                        ];
                        continue;
                    }

                    // =================================================================
                    // MODE: template | clone — can backup libs, lazy-load tai day.
                    //
                    // backup_includes.php se load copy_helper.class.php (dinh nghia class copy_helper).
                    // KHONG require /backup/copy_helper.php them — se gay "Cannot redeclare class".
                    // =================================================================
                    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
                    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

                    // -----------------------------------------------------------------
                    // 4. Xac dinh khoa nguon
                    // -----------------------------------------------------------------
                    $sourcecourse = null;

                    if ($mode === 'template') {
                        $srcshort = trim($course['sourceshortname'] ?? '');
                        if ($srcshort === '') {
                            self::set_last_copy_error($course['shortname'], "mode=template yeu cau 'sourceshortname'.", 'missing_sourceshortname');
                            $results[] = self::fail_result($course['shortname'], "mode=template yeu cau 'sourceshortname'.");
                            continue;
                        }

                        $sourcecourse = $DB->get_record('course', ['shortname' => $srcshort], '*', IGNORE_MISSING);
                        if (!$sourcecourse) {
                            self::set_last_copy_error($course['shortname'], "Khong tim thay khoa nguon '{$srcshort}'.", 'source_not_found');
                            $results[] = self::fail_result($course['shortname'], "Khong tim thay khoa nguon voi sourceshortname='{$srcshort}'.");
                            continue;
                        }

                        if (empty($startdate) || $startdate <= 0) {
                            self::set_last_copy_error($course['shortname'], 'Thieu startdate cho mode=template.', 'missing_startdate');
                            $results[] = self::fail_result($course['shortname'], "Thieu 'startdate' (UNIX timestamp, UTC) cho mode=template.");
                            continue;
                        }
                    } else {
                        // mode=clone: sourceshortname tuy chon; neu bo trong thi tim qua custom field.
                        $srcshort = trim($course['sourceshortname'] ?? '');

                        if ($srcshort !== '') {
                            $sourcecourse = $DB->get_record('course', ['shortname' => $srcshort], '*', IGNORE_MISSING);
                            if (!$sourcecourse) {
                                self::set_last_copy_error($course['shortname'], "Khong tim thay khoa nguon '{$srcshort}'.", 'source_not_found');
                                $results[] = self::fail_result($course['shortname'], "Khong tim thay khoa nguon voi sourceshortname='{$srcshort}'.");
                                continue;
                            }
                        } else {
                            // Tim khoa nguon moi nhat co cung course code qua custom field.
                            $coursecodefield = self::get_coursecode_field_shortname();
                            if (empty($coursecodefield)) {
                                self::set_last_copy_error($course['shortname'], 'Chua cau hinh coursecode_field_shortname.', 'missing_coursecode_setting');
                                $results[] = self::fail_result($course['shortname'], 'Chua cau hinh truong course code (local_thcourse/coursecode_field_shortname).');
                                continue;
                            }

                            $coursecode = self::extract_customfield_value($course['customfields'] ?? [], $coursecodefield);
                            if ($coursecode === null || $coursecode === '') {
                                self::set_last_copy_error($course['shortname'], "Thieu custom field '{$coursecodefield}'.", 'missing_coursecode_value');
                                $results[] = self::fail_result($course['shortname'], "Thieu custom field '{$coursecodefield}' de xac dinh khoa nguon (mode=clone).");
                                continue;
                            }

                            $sql = "
                            SELECT c.*
                              FROM {course} c
                              JOIN {customfield_data} d       ON d.instanceid = c.id
                              JOIN {customfield_field} f      ON f.id = d.fieldid
                              JOIN {customfield_category} cat ON cat.id = f.categoryid
                             WHERE cat.component  = :component
                               AND cat.area       = :area
                               AND f.shortname    = :fieldshort
                               AND c.id          <> 1
                               AND c.shortname   <> :newshortname
                               AND (d.shortcharvalue = :code1 OR d.charvalue = :code2 OR d.value = :code3)
                          ORDER BY
                               CASE WHEN c.startdate IS NULL OR c.startdate = 0
                                    THEN c.timecreated ELSE c.startdate END DESC,
                               c.id DESC
                             LIMIT 1";

                            $sourcecourse = $DB->get_record_sql($sql, [
                                'component'    => 'core_course',
                                'area'         => 'course',
                                'fieldshort'   => $coursecodefield,
                                'newshortname' => $course['shortname'],
                                'code1'        => $coursecode,
                                'code2'        => $coursecode,
                                'code3'        => $coursecode,
                            ], IGNORE_MISSING);

                            if (!$sourcecourse) {
                                self::set_last_copy_error(
                                    $course['shortname'],
                                    "Khong tim thay khoa nguon co {$coursecodefield}='{$coursecode}'.",
                                    'source_not_found'
                                );
                                $results[] = self::fail_result(
                                    $course['shortname'],
                                    "Khong tim thay khoa nguon co {$coursecodefield}='{$coursecode}'."
                                );
                                continue;
                            }
                        }

                        if (empty($startdate) || $startdate <= 0) {
                            self::set_last_copy_error($course['shortname'], 'Thieu startdate cho mode=clone.', 'missing_startdate');
                            $results[] = self::fail_result($course['shortname'], "Thieu 'startdate' (UNIX timestamp, UTC) cho mode=clone.");
                            continue;
                        }
                    }

                    // -----------------------------------------------------------------
                    // 5. Kiem tra quyen sao chep tren khoa nguon (giong copy.php core)
                    // -----------------------------------------------------------------
                    $sourcectx = \context_course::instance($sourcecourse->id);
                    $copycaps  = \core_course\management\helper::get_course_copy_capabilities();

                    require_all_capabilities($copycaps, $sourcectx);

                    // -----------------------------------------------------------------
                    // 6. Tinh enddate theo offset (giong copy.php core)
                    // -----------------------------------------------------------------
                    $sourcestart = (int)$sourcecourse->startdate;
                    $sourceend   = (int)($sourcecourse->enddate ?? 0);
                    $enddate     = ($sourcestart > 0 && $startdate > 0 && $sourceend > 0)
                        ? $sourceend + ($startdate - $sourcestart)
                        : 0;

                    // -----------------------------------------------------------------
                    // 7. Giai ma options (users, roles)
                    // -----------------------------------------------------------------
                    $userdata  = 0;
                    $keptroles = [];
                    foreach ($course['options'] ?? [] as $opt) {
                        if (($opt['name'] ?? '') === 'users') {
                            $userdata = (int)($opt['value'] ?? 0);
                        }
                        if (substr($opt['name'] ?? '', 0, 5) === 'role_' && (int)($opt['value'] ?? 0) !== 0) {
                            $keptroles[] = (int)$opt['value'];
                        }
                    }

                    // -----------------------------------------------------------------
                    // 8. Build formdata giong het copy.php core
                    // -----------------------------------------------------------------
                    $formdata = (object)[
                        'courseid'  => (int)$sourcecourse->id,
                        'fullname'  => $course['fullname'],
                        'shortname' => $course['shortname'],
                        'category'  => $categoryid,
                        'visible'   => (int)$course['visible'],
                        'startdate' => $startdate,
                        'enddate'   => $enddate,
                        'idnumber'  => !empty($course['idnumber']) ? $course['idnumber'] : $course['shortname'],
                        'userdata'  => $userdata,
                    ];
                    foreach ($keptroles as $roleid) {
                        $formdata->{'role_' . $roleid} = $roleid;
                    }


                    // -----------------------------------------------------------------
                    // 9. Kiem tra task sao chep cho destination shortname nay
                    //    da co trong hang doi chua (tranh tao trung lap).
                    //    get_copies() tra ve cac ban ghi dang backup/restore.
                    // -----------------------------------------------------------------
                    $inProgressBefore = \copy_helper::get_copies($USER->id);
                    foreach ($inProgressBefore as $existingCopy) {
                        if (!empty($existingCopy->destination) && $existingCopy->destination === $course['shortname']) {
                            $label = ($existingCopy->operation === \backup::OPERATION_BACKUP) ? 'backup' : 'restore';
                            $results[] = [
                                'status'    => 'Done',
                                'id'        => 0,
                                'shortname' => $course['shortname'],
                                'message'   => "Task sao chep ({$label}) cho shortname '{$course['shortname']}' da ton tai trong hang doi — bo qua de tranh trung lap.",
                            ];
                            continue 2; // bo qua, chuyen sang course tiep theo trong vong lap ngoai
                        }
                    }

                    // -----------------------------------------------------------------
                    // 10. Chuan bi moi truong truoc khi chay backup controller:
                    //     - write_close : tranh session conflict voi RELEASESESSION_YES
                    //     - MEMORY_EXTRA: backup/restore can nhieu RAM
                    //     - time_limit 0: tranh timeout
                    //     - ob_start    : nuot moi output rong backup/restore co the echo
                    // -----------------------------------------------------------------
                    \core\session\manager::write_close();
                    raise_memory_limit(MEMORY_EXTRA);
                    \core_php_time_limit::raise(0);

                    ob_start();
                    try {
                        $copydata = \copy_helper::process_formdata($formdata);
                        \copy_helper::create_copy($copydata);
                        ob_end_clean();
                    } catch (\Throwable $inner) {
                        ob_end_clean();
                        throw $inner;
                    }

                    self::clear_last_copy_error($course['shortname']);
                    $results[] = [
                        'status'    => 'Done',
                        'id'        => 0,
                        'shortname' => $course['shortname'],
                        'message'   => "Copy task queued (mode={$mode}). Poll get_multi_copy_status to track completion.",
                    ];
                } catch (\required_capability_exception $e) {
                    self::set_last_copy_error($course['shortname'] ?? '', 'Khong du quyen tao/sao chep khoa hoc.', 'no_capability');
                    $results[] = self::fail_result($course['shortname'] ?? '', 'Khong du quyen tao/sao chep khoa hoc.');
                } catch (\Throwable $e) {
                    $msg = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
                    self::set_last_copy_error($course['shortname'] ?? '', $msg, 'exception');
                    $results[] = self::fail_result($course['shortname'] ?? '', $msg);
                }
            }

            return $results;
        }

        public static function create_or_duplicate_course_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'status'    => new external_value(PARAM_TEXT, 'Done or Failed'),
                    'id'        => new external_value(PARAM_INT,  'Course ID (0 neu async hoac loi)'),
                    'shortname' => new external_value(PARAM_TEXT, 'Shortname khoa hoc'),
                    'message'   => new external_value(PARAM_TEXT, 'Ket qua chi tiet'),
                ])
            );
        }

        // =========================================================================
        // get_multi_copy_status
        //
        // Su dung copy_helper::get_copies() — cung API ma copyprogress.php dung —
        // thay vi tu viet SQL. Match tac vu dang chay theo truong $copy->destination
        // (= shortname khoa dich).
        // =========================================================================

        public static function get_multi_copy_status_parameters()
        {
            return new external_function_parameters([
                'shortnames' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Shortname khoa hoc dich can kiem tra')
                ),
            ]);
        }

        public static function get_multi_copy_status($shortnames)
        {
            global $DB, $CFG, $USER;

            $params = self::validate_parameters(
                self::get_multi_copy_status_parameters(),
                ['shortnames' => $shortnames]
            );
            self::validate_context(\context_system::instance());

            // backup_includes load copy_helper.class.php can thiet cho get_copies().
            require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
            require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

            // Lay tat ca ban sao dang xu ly cua user hien tai.
            // Day chinh la ham ma copyprogress.php su dung.
            // Tra ve cac copy records voi:
            //   ->destination : shortname khoa dich
            //   ->source      : shortname khoa nguon
            //   ->operation   : 'backup' hoac 'restore'
            $inProgressCopies = \copy_helper::get_copies($USER->id);

            // Index theo destination shortname de lookup O(1).
            $copyByDest = [];
            foreach ($inProgressCopies as $copy) {
                if (!empty($copy->destination)) {
                    $copyByDest[$copy->destination] = $copy;
                }
            }

            $results = [];

            foreach ($params['shortnames'] as $shortname) {

                // 1. Khoa dich da ton tai -> completed.
                if ($DB->record_exists('course', ['shortname' => $shortname])) {
                    self::clear_last_copy_error($shortname);
                    $results[] = [
                        'shortname' => $shortname,
                        'status'    => 'completed',
                        'message'   => 'Target course already exists.',
                    ];
                    continue;
                }

                // 2. asynchronous_copy_task dang chay -> in_progress.
                if (isset($copyByDest[$shortname])) {
                    $copy  = $copyByDest[$shortname];
                    $label = $copy->operation === \backup::OPERATION_BACKUP ? 'Backing up' : 'Restoring';
                    $results[] = [
                        'shortname' => $shortname,
                        'status'    => 'in_progress',
                        'message'   => "{$label} in progress (source: {$copy->source}).",
                    ];
                    continue;
                }

                // 3. Khong co task va khoa chua ton tai -> error.
                $last = self::get_last_copy_error($shortname);
                $results[] = [
                    'shortname' => $shortname,
                    'status'    => 'error',
                    'message'   => $last
                        ? (string)($last['message'] ?? 'Unknown error')
                        : 'Khong tim thay khoa hoc dich va khong co tac vu sao chep tuong ung.',
                ];
            }

            return $results;
        }

        public static function get_multi_copy_status_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'shortname' => new external_value(PARAM_TEXT, 'Shortname khoa hoc dich'),
                    'status'    => new external_value(PARAM_TEXT, 'in_progress | completed | error'),
                    'message'   => new external_value(PARAM_TEXT, 'Chi tiet trang thai'),
                ])
            );
        }

    // =========================================================================
    // Helpers
    // =========================================================================

        /**
         * Ghi loi copy vao mdl_config de co the truy van sau (get_multi_copy_status).
         */
        protected static function set_last_copy_error(string $shortname, string $message, string $code = 'validation'): void
        {
            if ($shortname === '') {
                return;
            }
            set_config('copy_error_' . sha1($shortname), json_encode([
                'shortname' => $shortname,
                'message'   => $message,
                'code'      => $code,
                'time'      => time(),
            ], JSON_UNESCAPED_UNICODE), 'local_thcourse');
        }

        /**
         * Doc loi copy gan nhat tu mdl_config.
         */
        protected static function get_last_copy_error(string $shortname): ?array
        {
            if ($shortname === '') {
                return null;
            }
            $raw = get_config('local_thcourse', 'copy_error_' . sha1($shortname));
            if (!$raw) {
                return null;
            }
            $data = json_decode($raw, true);
            return is_array($data) ? $data : null;
        }

        /**
         * Xoa loi copy khi khoa da duoc tao thanh cong.
         */
        protected static function clear_last_copy_error(string $shortname): void
        {
            if ($shortname === '') {
                return;
            }
            unset_config('copy_error_' . sha1($shortname), 'local_thcourse');
        }

        /**
         * Tra ve array ket qua that bai chuan.
         */
        protected static function fail_result(string $shortname, string $message): array
        {
            return ['status' => 'Failed', 'id' => 0, 'shortname' => $shortname, 'message' => $message];
        }

        /**
         * Lay gia tri custom field theo shortname tu mang customfields cua request.
         */
        protected static function extract_customfield_value(array $customfields, string $shortname): ?string
        {
            foreach ($customfields as $cf) {
                if (!empty($cf['shortname']) && $cf['shortname'] === $shortname) {
                    return isset($cf['value']) ? trim((string)$cf['value']) : null;
                }
            }
            return null;
        }

        /**
         * Lay shortname cua custom field dung de xac dinh course code
         * (cau hinh tai Admin > Plugins > local_thcourse > coursecode_field_shortname).
         */
        protected static function get_coursecode_field_shortname(): ?string
        {
            $v = get_config('local_th_ops', 'coursecode_field_shortname');
            if (!is_string($v)) {
                return null;
            }
            $v = trim($v);
            return $v !== '' ? $v : null;
        }


        // -----------------------------
        // INPUT PARAMETERS
        // -----------------------------
        public static function create_user_upload_parameters()
        {
            global $CFG;

            $userfields = [
                'createpassword' => new external_value(PARAM_BOOL, 'Create password and send email (force password change on first login for internal auth)', VALUE_OPTIONAL),

                'username' => new external_value(core_user::get_property_type('username'), 'Username'),
                'auth'     => new external_value(core_user::get_property_type('auth'), 'Auth plugin', VALUE_DEFAULT, 'manual'),
                'password' => new external_value(core_user::get_property_type('password'), 'Password (required unless createpassword=1 and internal auth)', VALUE_OPTIONAL),

                'firstname' => new external_value(core_user::get_property_type('firstname'), 'First name'),
                'lastname'  => new external_value(core_user::get_property_type('lastname'),  'Last name'),
                // 'email'  => new external_value(core_user::get_property_type('email'),  'Email'),
                'email' => new external_value(PARAM_RAW, 'Email'),


                'maildisplay' => new external_value(core_user::get_property_type('maildisplay'), 'Email visibility', VALUE_OPTIONAL),
                'city'        => new external_value(core_user::get_property_type('city'),        'City', VALUE_OPTIONAL),
                'country'     => new external_value(core_user::get_property_type('country'),     'Country', VALUE_OPTIONAL),
                'timezone'    => new external_value(core_user::get_property_type('timezone'),    'Timezone', VALUE_OPTIONAL),
                'description' => new external_value(core_user::get_property_type('description'), 'Description', VALUE_OPTIONAL),

                'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'), '', VALUE_OPTIONAL),
                'lastnamephonetic'  => new external_value(core_user::get_property_type('lastnamephonetic'),  '', VALUE_OPTIONAL),
                'middlename'        => new external_value(core_user::get_property_type('middlename'),        '', VALUE_OPTIONAL),
                'alternatename'     => new external_value(core_user::get_property_type('alternatename'),     '', VALUE_OPTIONAL),

                'idnumber'    => new external_value(core_user::get_property_type('idnumber'),    'ID number', VALUE_DEFAULT, ''),
                'institution' => new external_value(core_user::get_property_type('institution'), '', VALUE_OPTIONAL),
                'department'  => new external_value(core_user::get_property_type('department'),  '', VALUE_OPTIONAL),
                'phone1'      => new external_value(core_user::get_property_type('phone1'),      '', VALUE_OPTIONAL),
                'phone2'      => new external_value(core_user::get_property_type('phone2'),      '', VALUE_OPTIONAL),
                'address'     => new external_value(core_user::get_property_type('address'),     '', VALUE_OPTIONAL),

                'interests'   => new external_value(PARAM_RAW, 'User interests (comma separated)', VALUE_OPTIONAL),

                'lang'          => new external_value(core_user::get_property_type('lang'),         '', VALUE_DEFAULT, core_user::get_property_default('lang')),
                'calendartype'  => new external_value(core_user::get_property_type('calendartype'), '', VALUE_DEFAULT, $CFG->calendartype, VALUE_OPTIONAL),
                'theme'         => new external_value(core_user::get_property_type('theme'),        '', VALUE_OPTIONAL),
                'mailformat'    => new external_value(core_user::get_property_type('mailformat'),   '', VALUE_OPTIONAL),

                // Custom profile fields: {"type":"shortname","value":"..."}
                'customfields' => new external_multiple_structure(
                    new external_single_structure([
                        'type'  => new external_value(PARAM_ALPHANUMEXT, 'Custom field shortname'),
                        'value' => new external_value(PARAM_RAW,         'Value')
                    ]),
                    'Custom profile fields',
                    VALUE_OPTIONAL
                ),

                // Preferences: {"type":"preference_name","value":"preference_value"}
                'preferences' => new external_multiple_structure(
                    new external_single_structure([
                        'type'  => new external_value(PARAM_RAW, 'Preference name'),
                        'value' => new external_value(PARAM_RAW, 'Preference value')
                    ]),
                    'User preferences',
                    VALUE_OPTIONAL
                ),
            ];

            return new external_function_parameters([
                'users' => new external_multiple_structure(
                    new external_single_structure($userfields),
                    'Array of users to create'
                )
            ]);
        }

        // -----------------------------
        // HELPERS
        // -----------------------------

        /**
         * Chuẩn hoá giá trị theo loại custom field.
         * - th_dob: nhận dd/mm/yyyy | yyyy-mm-dd => timestamp (nếu parse được) hoặc string gốc.
         * - th_gender: map về Nam/Nữ/Khác.
         * - rỗng => trả null để bỏ qua (không ghi đè).
         */
        protected static function _normalize_customfield_value(string $type, $value)
        {
            $val = is_string($value) ? trim($value) : $value;
            return $val;
        }

        /**
         * Kiểm tra đầu vào của 1 user: bắt buộc, duy nhất, định dạng...
         * Trả về mảng lỗi (không ném exception) để gom lỗi theo từng user.
         */
        protected static function validate_user_payload(array $user, array $availableauths, array $langs, array $themes, bool $enforceUniqueEmail = true): array
        {
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
            if (!empty($user['email']) && !\validate_email($user['email'])) {
                $errors[] = [
                    'field' => 'email',
                    'code'  => 'format_invalid',
                    'msg'   => "Email format is invalid / Định dạng email không hợp lệ."
                ];
            }

            // 3) Auth plugin hợp lệ (nếu có). Loại mnet/webservice
            if (!empty($user['auth'])) {
                $auth = $user['auth'];
                if (!isset($availableauths[$auth]) || in_array($auth, ['mnet', 'webservice'], true)) {
                    $errors[] = [
                        'field' => 'auth',
                        'code'  => 'auth_not_found',
                        'msg'   => "Auth plugin '{$auth}' does not exist or is not allowed / Plugin đăng nhập '{$auth}' không tồn tại hoặc không được phép."
                    ];
                }
            }

            // 4) Theme hợp lệ (nếu có)
            if (!empty($user['theme']) && !isset($themes[$user['theme']])) {
                $errors[] = [
                    'field' => 'theme',
                    'code'  => 'theme_not_found',
                    'msg'   => "Theme '{$user['theme']}' not found / Theme '{$user['theme']}' không tồn tại."
                ];
            }

            // 5) Lang hợp lệ? nếu không: cảnh báo (không chặn)
            if (!empty($user['lang']) && !isset($langs[$user['lang']])) {
                $errors[] = [
                    'field' => 'lang',
                    'code'  => 'lang_not_found',
                    'msg'   => "Language '{$user['lang']}' not found; will fallback to default / Ngôn ngữ '{$user['lang']}' không tồn tại; sẽ dùng mặc định."
                ];
            }

            // 6) Unique checks
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

            if (!empty($user['idnumber'])) {
                $exists = $DB->record_exists_select(
                    'user',
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

            if ($enforceUniqueEmail && !empty($user['email']) && \validate_email($user['email'])) {
                $exists = $DB->record_exists_select(
                    'user',
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

            // 7) Nếu internal auth (manual) thì phải có password hoặc createpassword=1
            $auth = $user['auth'] ?? 'manual';
            $createpassword = !empty($user['createpassword']); // chấp nhận 1/true/"1"
            $isinternal = true; // manual là internal
            if ($isinternal) {
                if (!$createpassword && empty($user['password'])) {
                    $errors[] = [
                        'field' => 'password',
                        'code'  => 'required_or_createpassword',
                        'msg'   => "Password is required unless createpassword=1 / Bắt buộc nhập mật khẩu trừ khi bật createpassword=1."
                    ];
                }
            }

            // 8) Kiểm cấu trúc customfields (nếu có)
            if (!empty($user['customfields']) && is_array($user['customfields'])) {
                foreach ($user['customfields'] as $idx => $cf) {
                    if (!isset($cf['type'])) {
                        $errors[] = [
                            'field' => "customfields[$idx].type",
                            'code'  => 'required',
                            'msg'   => "Custom field shortname is required / Thiếu shortname của custom field."
                        ];
                    }
                    if (!array_key_exists('value', $cf)) {
                        $errors[] = [
                            'field' => "customfields[$idx].value",
                            'code'  => 'required',
                            'msg'   => "Custom field value is required / Thiếu giá trị custom field."
                        ];
                    }
                }
            }

            return $errors;
        }

        // -----------------------------
        // EXECUTION
        // -----------------------------
        public static function create_user_upload($users)
        {
            global $CFG, $DB;


            $debug_old = $CFG->debug ?? 0;
            $debugdisplay_old = $CFG->debugdisplay ?? false;

            // Bật debug tạm thời
            $CFG->debug = DEBUG_DEVELOPER;
            $CFG->debugdisplay = true;

            $params = self::validate_parameters(self::create_user_upload_parameters(), array('users' => $users));
            try {
                //Login hàm


                $context = context_system::instance();
                self::validate_context($context);
                require_capability('moodle/user:create', $context);

                $results = [];
                $availableauths = \core_component::get_plugin_list('auth');
                unset($availableauths['mnet'], $availableauths['webservice']);

                $langs  = get_string_manager()->get_list_of_translations();
                $themes = \core_component::get_plugin_list('theme');

                $enforceUniqueEmail = true;

                $transaction = $DB->start_delegated_transaction();

                foreach ($users as $user) {
                    // Fast-path: username đã tồn tại
                    if (
                        !empty($user['username']) &&
                        $DB->record_exists('user', ['username' => $user['username'], 'mnethostid' => $CFG->mnet_localhost_id])
                    ) {

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

                    // createpassword: chấp nhận 1/true/"1"
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
                    $authplugin     = \get_auth_plugin($u->auth);
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
                        $u->id = \user_create_user($u, $updatepassword, false);

                        // Interests
                        if (!empty($u->interests)) {
                            $trimmed = array_map('trim', explode(',', $u->interests));
                            \useredit_update_interests($u, $trimmed);
                        }

                        // Custom fields: chuẩn hoá & bỏ qua rỗng
                        if (!empty($user['customfields']) && is_array($user['customfields'])) {
                            foreach ($user['customfields'] as $cf) {
                                $shortname = $cf['type'] ?? null;
                                $rawval    = $cf['value'] ?? null;
                                if (!$shortname) {
                                    continue;
                                }

                                $norm = self::_normalize_customfield_value($shortname, $rawval);
                                if ($norm === null) { // rỗng -> bỏ qua để không ghi đè
                                    continue;
                                }
                                $u->{'profile_field_' . $shortname} = $norm;
                            }
                            \profile_save_data($u);
                        }

                        // Preferences
                        if (!empty($user['preferences'])) {
                            foreach ($user['preferences'] as $pref) {
                                \set_user_preference($pref['type'], $pref['value'], $u);
                            }
                        }

                        // Buộc đổi mật khẩu lần đầu nếu createpassword với internal auth
                        if ($createpassword && $authplugin->is_internal()) {
                            \set_user_preference('auth_forcepasswordchange', 1, $u);
                            \set_user_preference('create_password', 1, $u); // flag cho job khác gửi mail reset
                        }

                        \core\event\user_created::create_from_userid($u->id)->trigger();

                        $results[] = [
                            'id'       => $u->id,
                            'username' => $u->username,
                            'status'   => 'created'
                        ];
                    } catch (\Throwable $e) {
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
            } catch (\Throwable $e) {
                // Khôi phục lại trạng thái debug
                $CFG->debug = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;

                // Ném ra exception mới có thông báo rõ ràng hơn
                throw new Exception($e->getMessage());
            }

            $CFG->debug = $debug_old;
            $CFG->debugdisplay = $debugdisplay_old;
            return $results;
        }

        // -----------------------------
        // RETURNS
        // -----------------------------
        public static function create_user_upload_returns()
        {
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
                        ]),
                        'List of errors',
                        VALUE_OPTIONAL
                    )
                ])
            );
        }



        // -----------------------------
        // PARAMETERS
        // -----------------------------
        // ─────────────────────────────────────────────────────────────────────────────
        // PARAMETERS
        // ─────────────────────────────────────────────────────────────────────────────
        // ─────────────────────────────────────────────────────────────────────────────
        // PARAMETERS
        // ─────────────────────────────────────────────────────────────────────────────
        public static function users_parameters()
        {
            // Fields dùng cho POST (tạo mới)
            $userfields_post = [
                'createpassword' => new external_value(PARAM_BOOL, 'Create password and send email', VALUE_OPTIONAL),
                'username'       => new external_value(core_user::get_property_type('username'), 'Username', VALUE_OPTIONAL),
                'auth'           => new external_value(core_user::get_property_type('auth'), 'Auth plugin', VALUE_DEFAULT, 'manual'),
                'password'       => new external_value(core_user::get_property_type('password'), 'Password', VALUE_OPTIONAL),
                'firstname'      => new external_value(core_user::get_property_type('firstname'), 'First name', VALUE_OPTIONAL),
                'lastname'       => new external_value(core_user::get_property_type('lastname'), 'Last name', VALUE_OPTIONAL),
                'email'          => new external_value(PARAM_RAW, 'Email', VALUE_OPTIONAL),
                'maildisplay'    => new external_value(core_user::get_property_type('maildisplay'), 'Email visibility', VALUE_OPTIONAL),
                'city'           => new external_value(core_user::get_property_type('city'), 'City', VALUE_OPTIONAL),
                'country'        => new external_value(core_user::get_property_type('country'), 'Country', VALUE_OPTIONAL),
                'timezone'       => new external_value(core_user::get_property_type('timezone'), 'Timezone', VALUE_OPTIONAL),
                'description'    => new external_value(core_user::get_property_type('description'), 'Description', VALUE_OPTIONAL),
                'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'), '', VALUE_OPTIONAL),
                'lastnamephonetic'  => new external_value(core_user::get_property_type('lastnamephonetic'),  '', VALUE_OPTIONAL),
                'middlename'        => new external_value(core_user::get_property_type('middlename'),        '', VALUE_OPTIONAL),
                'alternatename'     => new external_value(core_user::get_property_type('alternatename'),     '', VALUE_OPTIONAL),
                'idnumber'       => new external_value(core_user::get_property_type('idnumber'), 'ID number', VALUE_DEFAULT, ''),
                'institution'    => new external_value(core_user::get_property_type('institution'), '', VALUE_OPTIONAL),
                'department'     => new external_value(core_user::get_property_type('department'), '', VALUE_OPTIONAL),
                'phone1'         => new external_value(core_user::get_property_type('phone1'), '', VALUE_OPTIONAL),
                'phone2'         => new external_value(core_user::get_property_type('phone2'), '', VALUE_OPTIONAL),
                'address'        => new external_value(core_user::get_property_type('address'), '', VALUE_OPTIONAL),
                'interests'      => new external_value(PARAM_RAW, 'User interests (comma separated)', VALUE_OPTIONAL),
                'lang'           => new external_value(core_user::get_property_type('lang'), '', VALUE_DEFAULT, core_user::get_property_default('lang')),
                'calendartype'   => new external_value(core_user::get_property_type('calendartype'), '', VALUE_OPTIONAL),
                'theme'          => new external_value(core_user::get_property_type('theme'), '', VALUE_OPTIONAL),
                'mailformat'     => new external_value(core_user::get_property_type('mailformat'), '', VALUE_OPTIONAL),
                'customfields'   => new external_multiple_structure(
                    new external_single_structure([
                        'type'  => new external_value(PARAM_ALPHANUMEXT, 'Custom field shortname'),
                        'value' => new external_value(PARAM_RAW, 'Value'),
                    ]),
                    'Custom profile fields',
                    VALUE_OPTIONAL
                ),
                'preferences' => new external_multiple_structure(
                    new external_single_structure([
                        'type'  => new external_value(PARAM_RAW, 'Preference name'),
                        'value' => new external_value(PARAM_RAW, 'Preference value'),
                    ]),
                    'User preferences',
                    VALUE_OPTIONAL
                ),
            ];

            // Fields dùng cho PATCH (cập nhật theo username) — bổ sung userpicture, url, suspended
            $userfields_patch = [
                'username' => new external_value(
                    core_user::get_property_type('username'),
                    'Username – dùng để tra ID, bắt buộc với PATCH'
                ),
                'auth' => new external_value(
                    core_user::get_property_type('auth'),
                    'Auth plugins include manual, ldap, etc',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'suspended' => new external_value(
                    core_user::get_property_type('suspended'),
                    'Suspend user account, either false to enable or true to disable',
                    VALUE_OPTIONAL
                ),
                'password' => new external_value(
                    core_user::get_property_type('password'),
                    'Plain text password',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'firstname' => new external_value(
                    core_user::get_property_type('firstname'),
                    'The first name(s) of the user',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'lastname' => new external_value(
                    core_user::get_property_type('lastname'),
                    'The family name of the user',
                    VALUE_OPTIONAL
                ),
                'email' => new external_value(
                    core_user::get_property_type('email'),
                    'A valid and unique email address',
                    VALUE_OPTIONAL,
                    '',
                    NULL_NOT_ALLOWED
                ),
                'maildisplay'       => new external_value(core_user::get_property_type('maildisplay'), 'Email display', VALUE_OPTIONAL),
                'city'              => new external_value(core_user::get_property_type('city'), 'Home city of the user', VALUE_OPTIONAL),
                'country'           => new external_value(core_user::get_property_type('country'), 'Home country code, e.g. AU or CZ', VALUE_OPTIONAL),
                'timezone'          => new external_value(core_user::get_property_type('timezone'), 'Timezone code such as Australia/Perth, or 99', VALUE_OPTIONAL),
                'description'       => new external_value(core_user::get_property_type('description'), 'User profile description, no HTML', VALUE_OPTIONAL),
                'userpicture'       => new external_value(PARAM_INT, 'The itemid where the new user picture has been uploaded to, 0 to delete', VALUE_OPTIONAL),
                'firstnamephonetic' => new external_value(core_user::get_property_type('firstnamephonetic'), 'The first name(s) phonetically', VALUE_OPTIONAL),
                'lastnamephonetic'  => new external_value(core_user::get_property_type('lastnamephonetic'), 'The family name phonetically', VALUE_OPTIONAL),
                'middlename'        => new external_value(core_user::get_property_type('middlename'), 'The middle name of the user', VALUE_OPTIONAL),
                'alternatename'     => new external_value(core_user::get_property_type('alternatename'), 'The alternate name of the user', VALUE_OPTIONAL),
                'interests'         => new external_value(PARAM_TEXT, 'User interests (separated by commas)', VALUE_OPTIONAL),
                'url'               => new external_value(PARAM_URL, 'User web page', VALUE_OPTIONAL),
                'idnumber'          => new external_value(core_user::get_property_type('idnumber'), 'An arbitrary ID code number perhaps from the institution', VALUE_OPTIONAL),
                'institution'       => new external_value(core_user::get_property_type('institution'), 'Institution', VALUE_OPTIONAL),
                'department'        => new external_value(core_user::get_property_type('department'), 'Department', VALUE_OPTIONAL),
                'phone1'            => new external_value(core_user::get_property_type('phone1'), 'Phone', VALUE_OPTIONAL),
                'phone2'            => new external_value(core_user::get_property_type('phone2'), 'Mobile phone', VALUE_OPTIONAL),
                'address'           => new external_value(core_user::get_property_type('address'), 'Postal address', VALUE_OPTIONAL),
                'lang'              => new external_value(core_user::get_property_type('lang'), 'Language code such as "en"', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'calendartype'      => new external_value(core_user::get_property_type('calendartype'), 'Calendar type such as "gregorian"', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                'theme'             => new external_value(core_user::get_property_type('theme'), 'Theme name such as "standard"', VALUE_OPTIONAL),
                'mailformat'        => new external_value(core_user::get_property_type('mailformat'), 'Mail format code: 0 = plain text, 1 = HTML', VALUE_OPTIONAL),
                'customfields' => new external_multiple_structure(
                    new external_single_structure([
                        'type'  => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                        'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                    ]),
                    'User custom fields (also known as user profile fields)',
                    VALUE_OPTIONAL
                ),
                'preferences' => new external_multiple_structure(
                    new external_single_structure([
                        'type'  => new external_value(PARAM_RAW, 'The name of the preference'),
                        'value' => new external_value(PARAM_RAW, 'The value of the preference'),
                    ]),
                    'User preferences',
                    VALUE_OPTIONAL
                ),
            ];

            return new external_function_parameters([
                // GET: tìm theo criteria (key/value pairs)
                'criteria' => new external_multiple_structure(
                    new external_single_structure([
                        'key'   => new external_value(PARAM_ALPHA, 'Search key (id, username, email, idnumber, firstname, lastname, auth)'),
                        'value' => new external_value(PARAM_RAW,   'Search value'),
                    ]),
                    'Search criteria – dùng cho GET với core_user_external::get_users()',
                    VALUE_DEFAULT,
                    []
                ),
                // GET: tìm theo field + values (get_users_by_field)
                'field' => new external_value(
                    PARAM_ALPHANUMEXT,
                    'Field name for get_users_by_field (id, idnumber, username, email) – dùng kèm "values"',
                    VALUE_DEFAULT,
                    ''
                ),
                'values' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'Giá trị cần tìm'),
                    'Danh sách giá trị – dùng kèm "field"',
                    VALUE_DEFAULT,
                    []
                ),
                // POST: tạo user
                'users' => new external_multiple_structure(
                    new external_single_structure($userfields_post),
                    'Array of users to create (for POST)',
                    VALUE_DEFAULT,
                    []
                ),
                // PATCH: cập nhật user theo username
                'patch_users' => new external_multiple_structure(
                    new external_single_structure($userfields_patch),
                    'Array of users to update (for PATCH) – xác định qua username',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // EXECUTION
        // ─────────────────────────────────────────────────────────────────────────────
        public static function users($criteria = [], $field = '', $values = [], $users = [], $patch_users = [])
        {
            global $CFG, $DB;
            require_once($CFG->dirroot . '/user/lib.php');

            $params = self::validate_parameters(self::users_parameters(), [
                'criteria'    => $criteria,
                'field'       => $field,
                'values'      => $values,
                'users'       => $users,
                'patch_users' => $patch_users,
            ]);

            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

            // ─────────────────────────────────────────
            // GET → tìm kiếm user
            // ─────────────────────────────────────────
            if ($method === 'GET') {
                $context = context_system::instance();
                self::validate_context($context);

                // Nhánh 1: field + values  →  get_users_by_field
                if (!empty($params['field']) && !empty($params['values'])) {
                    $userlist = \core_user_external::get_users_by_field(
                        $params['field'],
                        $params['values']
                    );
                    $warnings = [];

                    // Nhánh 2: criteria  →  get_users
                } else {
                    $result   = \core_user_external::get_users($params['criteria']);
                    $userlist = $result['users'];
                    $warnings = $result['warnings'];
                }

                // Bổ sung email / phone2 nếu thiếu
                $userids = array_column(
                    array_filter(
                        $userlist,
                        fn($u) => (empty($u['email']) || empty($u['phone2'])) && !empty($u['id'])
                    ),
                    'id'
                );

                if (!empty($userids)) {
                    $fields = $DB->get_records_list('user', 'id', $userids, '', 'id, email, phone2');
                    foreach ($userlist as &$u) {
                        if (empty($u['email'])  && isset($fields[$u['id']]->email))  $u['email']  = $fields[$u['id']]->email;
                        if (empty($u['phone2']) && isset($fields[$u['id']]->phone2)) $u['phone2'] = $fields[$u['id']]->phone2;
                    }
                    unset($u);
                }

                return [
                    'get_users'       => $userlist,
                    'get_warnings'    => $warnings,
                    'upload_results'  => [],
                    'patch_warnings'  => [],
                ];
            }

            // ─────────────────────────────────────────
            // POST → tạo user
            // ─────────────────────────────────────────
            if ($method === 'POST') {
                $debug_old        = $CFG->debug ?? 0;
                $debugdisplay_old = $CFG->debugdisplay ?? false;
                $CFG->debug        = DEBUG_DEVELOPER;
                $CFG->debugdisplay = true;

                try {
                    $context = context_system::instance();
                    self::validate_context($context);
                    require_capability('moodle/user:create', $context);

                    $availableauths = \core_component::get_plugin_list('auth');
                    unset($availableauths['mnet'], $availableauths['webservice']);
                    $langs  = get_string_manager()->get_list_of_translations();
                    $themes = \core_component::get_plugin_list('theme');

                    $uploadResults = [];

                    foreach ($params['users'] as $user) {
                        $errors = self::validate_user_payload($user, $availableauths, $langs, $themes, true);
                        if (!empty($errors)) {
                            $uploadResults[] = [
                                'username' => $user['username'] ?? null,
                                'status'   => 'error',
                                'errors'   => $errors,
                            ];
                            continue;
                        }

                        $u             = (object)$user;
                        $u->confirmed  = 1;
                        $u->mnethostid = $CFG->mnet_localhost_id;
                        $u->auth       = $u->auth ?? 'manual';

                        $createpassword = !empty($u->createpassword);
                        unset($u->createpassword);

                        if (!isset($langs[$u->lang ?? ''])) {
                            $u->lang = core_user::get_property_default('lang');
                        }

                        if (!empty($u->theme) && !isset($themes[$u->theme])) {
                            unset($u->theme);
                        }

                        $authplugin     = \get_auth_plugin($u->auth);
                        $updatepassword = false;
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
                            $u->id = \user_create_user($u, $updatepassword, false);

                            if (!empty($u->interests)) {
                                \useredit_update_interests($u, array_map('trim', explode(',', $u->interests)));
                            }

                            if (!empty($user['customfields']) && is_array($user['customfields'])) {
                                foreach ($user['customfields'] as $cf) {
                                    $shortname = $cf['type'] ?? null;
                                    if (!$shortname) continue;
                                    $norm = self::_normalize_customfield_value($shortname, $cf['value'] ?? null);
                                    if ($norm === null) continue;
                                    $u->{'profile_field_' . $shortname} = $norm;
                                }
                                \profile_save_data($u);
                            }

                            if (!empty($user['preferences'])) {
                                foreach ($user['preferences'] as $pref) {
                                    \set_user_preference($pref['type'], $pref['value'], $u);
                                }
                            }

                            if ($createpassword && $authplugin->is_internal()) {
                                \set_user_preference('auth_forcepasswordchange', 1, $u);
                                \set_user_preference('create_password', 1, $u);
                            }

                            \core\event\user_created::create_from_userid($u->id)->trigger();

                            $uploadResults[] = [
                                'id'       => $u->id,
                                'username' => $u->username,
                                'status'   => 'created',
                                'errors'   => [],
                            ];
                        } catch (\Throwable $e) {
                            $uploadResults[] = [
                                'username' => $user['username'] ?? null,
                                'status'   => 'error',
                                'errors'   => [[
                                    'field' => null,
                                    'code'  => 'exception',
                                    'msg'   => 'Create failed: ' . $e->getMessage(),
                                ]],
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    $CFG->debug        = $debug_old;
                    $CFG->debugdisplay = $debugdisplay_old;
                    throw new \Exception($e->getMessage());
                }

                $CFG->debug        = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;

                return [
                    'get_users'      => [],
                    'get_warnings'   => [],
                    'upload_results' => $uploadResults,
                    'patch_warnings' => [],
                ];
            }

            // ─────────────────────────────────────────
            // PATCH → cập nhật user theo username
            // ─────────────────────────────────────────
            if ($method === 'PATCH') {
                $debug_old        = $CFG->debug ?? 0;
                $debugdisplay_old = $CFG->debugdisplay ?? false;
                $CFG->debug        = DEBUG_DEVELOPER;
                $CFG->debugdisplay = true;

                $core_input    = [];
                $patchWarnings = [];

                // Bước 1: tra username → id
                foreach ($params['patch_users'] as $user) {
                    if (empty($user['username'])) {
                        $patchWarnings[] = [
                            'item'        => 'user',
                            'itemid'      => 0,
                            'warningcode' => 'missing_username',
                            'message'     => 'Thiếu trường username',
                        ];
                        continue;
                    }

                    $record = $DB->get_record(
                        'user',
                        ['username' => $user['username'], 'deleted' => 0],
                        '*',
                        IGNORE_MISSING
                    );

                    if (!$record) {
                        $patchWarnings[] = [
                            'item'        => 'user',
                            'itemid'      => 0,
                            'warningcode' => 'user_not_found',
                            'message'     => "Không tìm thấy người dùng với username: {$user['username']}",
                        ];
                        continue;
                    }

                    $user['id'] = $record->id;
                    $core_input[] = $user;
                }

                // Bước 2: gọi core_user_external::update_users()
                if (!empty($core_input)) {
                    try {
                        $result = \core_user_external::update_users($core_input);

                        // Merge warnings từ bước tra username vào kết quả của core
                        if (!empty($patchWarnings)) {
                            $result['warnings'] = array_merge(
                                $patchWarnings,
                                $result['warnings'] ?? []
                            );
                        }

                        $patchWarnings = $result['warnings'] ?? [];
                    } catch (\Throwable $e) {
                        foreach ($core_input as $u) {
                            $patchWarnings[] = [
                                'item'        => 'user',
                                'itemid'      => $u['id'],
                                'warningcode' => $e instanceof \moodle_exception ? $e->errorcode : 'exception',
                                'message'     => $e->getMessage(),
                            ];
                        }
                    }
                }

                $CFG->debug        = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;

                return [
                    'get_users'      => [],
                    'get_warnings'   => [],
                    'upload_results' => [],
                    'patch_warnings' => $patchWarnings,
                ];
            }

            throw new \invalid_parameter_exception("Method '{$method}' not supported. Use GET, POST, or PATCH.");
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // RETURNS
        // ─────────────────────────────────────────────────────────────────────────────
        public static function users_returns()
        {
            return new external_single_structure([
                'get_users' => new external_multiple_structure(
                    new external_single_structure([
                        'id'                   => new external_value(PARAM_INT,      'ID of the user'),
                        'username'             => new external_value(PARAM_RAW,      'Username', VALUE_OPTIONAL),
                        'firstname'            => new external_value(PARAM_NOTAGS,   'First name', VALUE_OPTIONAL),
                        'lastname'             => new external_value(PARAM_NOTAGS,   'Last name', VALUE_OPTIONAL),
                        'fullname'             => new external_value(PARAM_NOTAGS,   'Full name'),
                        'email'                => new external_value(PARAM_TEXT,     'Email', VALUE_OPTIONAL),
                        'address'              => new external_value(PARAM_TEXT,     'Postal address', VALUE_OPTIONAL),
                        'phone1'               => new external_value(PARAM_NOTAGS,   'Phone 1', VALUE_OPTIONAL),
                        'phone2'               => new external_value(PARAM_NOTAGS,   'Phone 2', VALUE_OPTIONAL),
                        'department'           => new external_value(PARAM_TEXT,     'Department', VALUE_OPTIONAL),
                        'institution'          => new external_value(PARAM_TEXT,     'Institution', VALUE_OPTIONAL),
                        'idnumber'             => new external_value(PARAM_RAW,      'ID number', VALUE_OPTIONAL),
                        'interests'            => new external_value(PARAM_TEXT,     'Interests', VALUE_OPTIONAL),
                        'firstaccess'          => new external_value(PARAM_INT,      'First access (0 if never)', VALUE_OPTIONAL),
                        'lastaccess'           => new external_value(PARAM_INT,      'Last access (0 if never)', VALUE_OPTIONAL),
                        'auth'                 => new external_value(PARAM_PLUGIN,   'Auth plugin', VALUE_OPTIONAL),
                        'suspended'            => new external_value(PARAM_BOOL,     'Suspended', VALUE_OPTIONAL),
                        'confirmed'            => new external_value(PARAM_BOOL,     'Confirmed', VALUE_OPTIONAL),
                        'lang'                 => new external_value(PARAM_SAFEDIR,  'Language', VALUE_OPTIONAL),
                        'calendartype'         => new external_value(PARAM_PLUGIN,   'Calendar type', VALUE_OPTIONAL),
                        'theme'                => new external_value(PARAM_PLUGIN,   'Theme', VALUE_OPTIONAL),
                        'timezone'             => new external_value(PARAM_TIMEZONE, 'Timezone', VALUE_OPTIONAL),
                        'mailformat'           => new external_value(PARAM_INT,      'Mail format', VALUE_OPTIONAL),
                        'description'          => new external_value(PARAM_RAW,      'Description', VALUE_OPTIONAL),
                        'descriptionformat'    => new external_format_value('description', VALUE_OPTIONAL),
                        'city'                 => new external_value(PARAM_NOTAGS,   'City', VALUE_OPTIONAL),
                        'country'              => new external_value(PARAM_ALPHA,    'Country code', VALUE_OPTIONAL),
                        'profileimageurlsmall' => new external_value(PARAM_URL,     'Profile image URL (small)', VALUE_OPTIONAL),
                        'profileimageurl'      => new external_value(PARAM_URL,     'Profile image URL', VALUE_OPTIONAL),
                        'customfields' => new external_multiple_structure(
                            new external_single_structure([
                                'type'      => new external_value(PARAM_ALPHANUMEXT, 'Type'),
                                'value'     => new external_value(PARAM_RAW,         'Value'),
                                'name'      => new external_value(PARAM_RAW,         'Name'),
                                'shortname' => new external_value(PARAM_RAW,         'Shortname'),
                            ]),
                            'Custom fields',
                            VALUE_OPTIONAL
                        ),
                        'preferences' => new external_multiple_structure(
                            new external_single_structure([
                                'name'  => new external_value(PARAM_RAW, 'Name'),
                                'value' => new external_value(PARAM_RAW, 'Value'),
                            ]),
                            'Preferences',
                            VALUE_OPTIONAL
                        ),
                    ]),
                    'List of users (GET)',
                    VALUE_OPTIONAL
                ),

                'get_warnings' => new external_warnings(
                    'always set to \'key\'',
                    'faulty key name',
                    'Warnings (GET)'
                ),

                'upload_results' => new external_multiple_structure(
                    new external_single_structure([
                        'id'       => new external_value(PARAM_INT,  'User ID',  VALUE_OPTIONAL),
                        'username' => new external_value(PARAM_RAW,  'Username', VALUE_OPTIONAL),
                        'status'   => new external_value(PARAM_TEXT, 'created | error'),
                        'errors'   => new external_multiple_structure(
                            new external_single_structure([
                                'field' => new external_value(PARAM_RAW,  'Field name', VALUE_OPTIONAL),
                                'code'  => new external_value(PARAM_RAW,  'Error code'),
                                'msg'   => new external_value(PARAM_TEXT, 'Message'),
                            ]),
                            'Errors',
                            VALUE_OPTIONAL
                        ),
                    ]),
                    'Upload results (POST)',
                    VALUE_OPTIONAL
                ),

                // Kết quả PATCH
                'patch_warnings' => new external_multiple_structure(
                    new external_single_structure([
                        'item'        => new external_value(PARAM_TEXT,         'Loại phần tử gây lỗi (e.g. user)', VALUE_OPTIONAL),
                        'itemid'      => new external_value(PARAM_INT,          'ID của phần tử nếu có',            VALUE_OPTIONAL),
                        'warningcode' => new external_value(PARAM_ALPHANUMEXT,  'Mã lỗi'),
                        'message'     => new external_value(PARAM_TEXT,         'Thông điệp mô tả lỗi'),
                    ]),
                    'Warnings / errors from PATCH (update by username)',
                    VALUE_OPTIONAL
                ),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // PARAMETERS
        // ─────────────────────────────────────────────────────────────────────────────
        public static function categories_parameters()
        {
            return new external_function_parameters([
                // GET: tìm theo ids hoặc idnumbers
                'ids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'LMS category id'),
                    'Danh sách LMS id – dùng cho GET',
                    VALUE_DEFAULT,
                    []
                ),
                'idnumbers' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'LMS category idnumber'),
                    'Danh sách LMS idnumber – dùng cho GET',
                    VALUE_DEFAULT,
                    []
                ),
                // POST: tạo mới danh mục
                'categories' => new external_multiple_structure(
                    new external_single_structure([
                        'name'              => new external_value(PARAM_TEXT,  'Tên danh mục', VALUE_REQUIRED),
                        'parent_categorycode' => new external_value(PARAM_RAW, 'Mã idnumber của danh mục cha', VALUE_OPTIONAL),
                        'idnumber'          => new external_value(PARAM_RAW,   'ID number (category code)', VALUE_OPTIONAL),
                        'description'       => new external_value(PARAM_RAW,   'Mô tả', VALUE_OPTIONAL),
                        'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                        'theme'             => new external_value(PARAM_THEME, 'Giao diện', VALUE_OPTIONAL),
                        'visible'           => new external_value(PARAM_INT,   'Hiển thị', VALUE_OPTIONAL),
                    ]),
                    'Danh sách danh mục cần tạo – dùng cho POST',
                    VALUE_DEFAULT,
                    []
                ),
                // PATCH: cập nhật danh mục theo categorycode (idnumber)
                'patch_categories' => new external_multiple_structure(
                    new external_single_structure([
                        'categorycode'        => new external_value(PARAM_RAW,   'Mã danh mục (idnumber)', VALUE_REQUIRED),
                        'name'                => new external_value(PARAM_TEXT,  'Tên danh mục', VALUE_OPTIONAL),
                        'parent_categorycode' => new external_value(PARAM_RAW,   'Mã danh mục cha (idnumber)', VALUE_OPTIONAL),
                        'description'         => new external_value(PARAM_RAW,   'Mô tả', VALUE_OPTIONAL),
                        'descriptionformat'   => new external_format_value('description', VALUE_OPTIONAL),
                        'theme'               => new external_value(PARAM_THEME, 'Giao diện danh mục', VALUE_OPTIONAL),
                    ]),
                    'Danh sách danh mục cần cập nhật – dùng cho PATCH',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // EXECUTION
        // ─────────────────────────────────────────────────────────────────────────────
        public static function categories($ids = [], $idnumbers = [], $categories = [], $patch_categories = [])
        {
            global $DB;

            $params = self::validate_parameters(self::categories_parameters(), [
                'ids'              => $ids,
                'idnumbers'        => $idnumbers,
                'categories'       => $categories,
                'patch_categories' => $patch_categories,
            ]);

            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

            // ─────────────────────────────────────────
            // GET → tìm danh mục theo ids / idnumbers
            // ─────────────────────────────────────────
            if ($method === 'GET') {
                $results   = [];
                $wheres    = [];
                $sqlparams = [];

                if (!empty($params['ids'])) {
                    [$in_sql, $in_params] = $DB->get_in_or_equal($params['ids'], SQL_PARAMS_NAMED, 'id_');
                    $wheres[]  = "id $in_sql";
                    $sqlparams = array_merge($sqlparams, $in_params);
                }

                if (!empty($params['idnumbers'])) {
                    [$in_sql2, $in_params2] = $DB->get_in_or_equal($params['idnumbers'], SQL_PARAMS_NAMED, 'idnumber_');
                    $wheres[]  = "idnumber $in_sql2";
                    $sqlparams = array_merge($sqlparams, $in_params2);
                }

                if (!empty($wheres)) {
                    $sql           = "SELECT * FROM {course_categories} WHERE " . implode(" OR ", $wheres);
                    $categories_db = $DB->get_records_sql($sql, $sqlparams);

                    foreach ($categories_db as $cat) {
                        $results[] = [
                            'id'       => $cat->id,
                            'name'     => $cat->name,
                            'idnumber' => $cat->idnumber,
                            'parent'   => $cat->parent,
                            'visible'  => $cat->visible,
                        ];
                    }
                }

                return [
                    'get_categories'   => $results,
                    'create_results'   => [],
                    'patch_results'    => [],
                ];
            }

            // ─────────────────────────────────────────
            // POST → tạo mới danh mục
            // ─────────────────────────────────────────
            if ($method === 'POST') {
                $core_categories = [];
                $results         = [];
                $visible_map     = [];

                foreach ($params['categories'] as $cat) {
                    $corecat = ['name' => $cat['name']];

                    // Tra parent_categorycode → parent id
                    if (!empty($cat['parent_categorycode'])) {
                        $parent = $DB->get_record('course_categories', ['idnumber' => $cat['parent_categorycode']]);
                        if ($parent) {
                            $corecat['parent'] = $parent->id;
                        } else {
                            $results[]   = [
                                'idnumber' => $cat['idnumber'] ?? '',
                                'name'     => $cat['name'],
                                'status'   => 'error',
                                'message'  => 'Không tìm thấy danh mục cha với parent_categorycode: ' . $cat['parent_categorycode'],
                                'lmsid'    => null,
                            ];
                            continue;
                        }
                    }

                    // KHÔNG truyền visible vào core — set DB sau
                    foreach (['idnumber', 'description', 'descriptionformat', 'theme'] as $f) {
                        if (isset($cat[$f])) {
                            $corecat[$f] = $cat[$f];
                        }
                    }

                    $core_categories[] = $corecat;
                    $visible_map[]     = isset($cat['visible']) ? (int)$cat['visible'] : null;
                    $results[]         = [
                        'idnumber' => $cat['idnumber'] ?? '',
                        'name'     => $cat['name'],
                        'status'   => 'pending',
                        'message'  => '',
                        'lmsid'    => null,
                    ];
                }

                if (!empty($core_categories)) {
                    try {
                        $created     = \core_course_external::create_categories($core_categories);
                        $pending_idx = 0;

                        foreach ($results as &$res) {
                            if ($res['status'] !== 'pending') continue;

                            $cat_data    = $created[$pending_idx] ?? null;
                            $visible_val = $visible_map[$pending_idx];
                            $pending_idx++;

                            if ($cat_data && !empty($cat_data['id'])) {
                                $res['status']  = 'created';
                                $res['message'] = 'Tạo thành công. ID: ' . $cat_data['id'];
                                $res['lmsid']   = $cat_data['id'];

                                if (!is_null($visible_val)) {
                                    $DB->set_field('course_categories', 'visible', $visible_val, ['id' => $cat_data['id']]);
                                }
                            } else {
                                $res['status']  = 'error';
                                $res['message'] = 'Không xác định được ID danh mục LMS sau khi tạo.';
                            }
                        }
                        unset($res);
                    } catch (\Exception $e) {
                        foreach ($results as &$res) {
                            if ($res['status'] === 'pending') {
                                $res['status']  = 'error';
                                $res['message'] = $e->getMessage();
                            }
                        }
                        unset($res);
                    }
                }

                return [
                    'get_categories' => [],
                    'create_results' => $results,
                    'patch_results'  => [],
                ];
            }

            // ─────────────────────────────────────────
            // PATCH → cập nhật danh mục theo categorycode
            // ─────────────────────────────────────────
            if ($method === 'PATCH') {
                $core_categories = [];
                $results         = [];

                foreach ($params['patch_categories'] as $cat) {
                    // Tra cứu theo idnumber (categorycode)
                    $category = $DB->get_record('course_categories', ['idnumber' => $cat['categorycode']]);
                    if (!$category) {
                        $results[] = [
                            'categorycode' => $cat['categorycode'],
                            'status'       => 'not_found',
                            'message'      => 'Không tìm thấy danh mục với categorycode (idnumber): ' . $cat['categorycode'],
                        ];
                        continue;
                    }

                    // Chuẩn bị object đúng format core
                    $corecat = ['id' => $category->id];
                    foreach (['name', 'description', 'descriptionformat', 'theme'] as $f) {
                        if (array_key_exists($f, $cat)) {
                            $corecat[$f] = $cat[$f];
                        }
                    }

                    // Xử lý parent_categorycode → parent id (giữ nguyên logic gốc)
                    if (array_key_exists('parent_categorycode', $cat)) {
                        if (!empty($cat['parent_categorycode'])) {
                            $parent = $DB->get_record('course_categories', ['idnumber' => $cat['parent_categorycode']]);
                            if ($parent) {
                                $corecat['parent'] = $parent->id;
                            } else {
                                $results[] = [
                                    'categorycode' => $cat['categorycode'],
                                    'status'       => 'error',
                                    'message'      => 'Không tìm thấy danh mục cha với parent_categorycode: ' . $cat['parent_categorycode'],
                                ];
                                continue;
                            }
                        } else {
                            // parent_categorycode rỗng → về gốc
                            $corecat['parent'] = 0;
                        }
                    } else {
                        // Không truyền parent_categorycode → về gốc
                        $corecat['parent'] = 0;
                    }

                    $core_categories[] = $corecat;
                    $results[]         = [
                        'categorycode' => $cat['categorycode'],
                        'status'       => 'pending',
                        'message'      => '',
                    ];
                }

                if (!empty($core_categories)) {
                    try {
                        \core_course_external::update_categories($core_categories);

                        foreach ($results as &$res) {
                            if ($res['status'] === 'pending') {
                                $res['status']  = 'updated';
                                $res['message'] = 'Cập nhật thành công.';
                            }
                        }
                        unset($res);
                    } catch (\Exception $e) {
                        foreach ($results as &$res) {
                            if ($res['status'] === 'pending') {
                                $res['status']  = 'error';
                                $res['message'] = $e->getMessage();
                            }
                        }
                        unset($res);
                    }
                }

                return [
                    'get_categories' => [],
                    'create_results' => [],
                    'patch_results'  => $results,
                ];
            }

            throw new \invalid_parameter_exception("Method '{$method}' not supported. Use GET, POST, or PATCH.");
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // RETURNS
        // ─────────────────────────────────────────────────────────────────────────────
        public static function categories_returns()
        {
            return new external_single_structure([
                'get_categories' => new external_multiple_structure(
                    new external_single_structure([
                        'id'       => new external_value(PARAM_INT,  'LMS category id'),
                        'name'     => new external_value(PARAM_TEXT, 'Tên danh mục'),
                        'idnumber' => new external_value(PARAM_RAW,  'ID number'),
                        'parent'   => new external_value(PARAM_INT,  'ID cha'),
                        'visible'  => new external_value(PARAM_BOOL, 'Hiển thị'),
                    ]),
                    'Danh sách danh mục (GET)',
                    VALUE_OPTIONAL
                ),
                'create_results' => new external_multiple_structure(
                    new external_single_structure([
                        'idnumber' => new external_value(PARAM_RAW,  'ID number (category code)'),
                        'name'     => new external_value(PARAM_TEXT, 'Tên danh mục'),
                        'status'   => new external_value(PARAM_TEXT, 'created | error'),
                        'message'  => new external_value(PARAM_TEXT, 'Thông báo'),
                        'lmsid'    => new external_value(PARAM_INT,  'LMS Category ID', VALUE_OPTIONAL),
                    ]),
                    'Kết quả tạo danh mục (POST)',
                    VALUE_OPTIONAL
                ),
                'patch_results' => new external_multiple_structure(
                    new external_single_structure([
                        'categorycode' => new external_value(PARAM_RAW,  'Mã danh mục (idnumber)'),
                        'status'       => new external_value(PARAM_TEXT, 'updated | not_found | error'),
                        'message'      => new external_value(PARAM_TEXT, 'Thông báo'),
                    ]),
                    'Kết quả cập nhật danh mục (PATCH)',
                    VALUE_OPTIONAL
                ),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // PARAMETERS
        // ─────────────────────────────────────────────────────────────────────────────
        public static function courses_parameters()
        {
            $courseconfig = get_config('moodlecourse');

            return new external_function_parameters([
                // ── GET ──────────────────────────────────────────────────────────────
                'field' => new external_value(
                    PARAM_TEXT,
                    'Trường tìm kiếm (id, ids, shortname, shortnames, idnumber, category, categorycode)',
                    VALUE_DEFAULT,
                    ''
                ),
                'value' => new external_value(PARAM_TEXT, 'Giá trị tìm kiếm', VALUE_DEFAULT, ''),

                // ── POST / PATCH ─────────────────────────────────────────────────────
                'courses' => new external_multiple_structure(
                    new external_single_structure([
                        // ── POST-only fields ─────────────────────────────────────────
                        'mode'            => new external_value(PARAM_ALPHA,  'template | new | clone  (POST)', VALUE_DEFAULT, 'clone'),
                        'sourceshortname' => new external_value(PARAM_TEXT,   'Shortname khóa nguồn    (POST)', VALUE_DEFAULT, null),

                        // ── Shared fields (POST & PATCH) ─────────────────────────────
                        'shortname'       => new external_value(PARAM_TEXT,   'Shortname khóa học (POST: mới; PATCH: định danh)'),
                        'fullname'        => new external_value(PARAM_TEXT,   'Tên đầy đủ',          VALUE_OPTIONAL),
                        'idnumber'        => new external_value(PARAM_RAW,    'ID number',            VALUE_OPTIONAL),
                        'categoryidnumber' => new external_value(PARAM_TEXT,   'ID number danh mục',   VALUE_OPTIONAL),
                        'visible'         => new external_value(PARAM_INT,    'Visible (0/1)',         VALUE_OPTIONAL),
                        'startdate'       => new external_value(PARAM_INT,    'Startdate UTC',         VALUE_OPTIONAL),
                        'enddate'         => new external_value(PARAM_INT,    'Enddate UTC  (PATCH)',   VALUE_OPTIONAL),
                        'summary'         => new external_value(PARAM_RAW,    'Mô tả khóa học (PATCH)', VALUE_OPTIONAL),
                        'summaryformat'   => new external_format_value('summary', VALUE_OPTIONAL),
                        'format'          => new external_value(PARAM_PLUGIN, 'Định dạng khóa học',   VALUE_OPTIONAL, $courseconfig->format),
                        'showgrades'      => new external_value(PARAM_INT,    'Hiển thị điểm',        VALUE_OPTIONAL, $courseconfig->showgrades),
                        'newsitems'       => new external_value(PARAM_INT,    'Số tin tức',           VALUE_OPTIONAL, $courseconfig->newsitems),
                        'numsections'     => new external_value(PARAM_INT,    '(deprecated) Số chủ đề/tuần', VALUE_OPTIONAL),
                        'maxbytes'        => new external_value(PARAM_INT,    'Kích thước tệp lớn nhất', VALUE_OPTIONAL, $courseconfig->maxbytes),
                        'showreports'     => new external_value(PARAM_INT,    'Báo cáo hoạt động',    VALUE_OPTIONAL, $courseconfig->showreports),
                        'hiddensections'  => new external_value(PARAM_INT,    '(deprecated) Hiển thị phần ẩn', VALUE_OPTIONAL),
                        'groupmode'       => new external_value(PARAM_INT,    'Kiểu nhóm',            VALUE_OPTIONAL, $courseconfig->groupmode),
                        'groupmodeforce'  => new external_value(PARAM_INT,    'Bắt buộc kiểu nhóm',   VALUE_OPTIONAL, $courseconfig->groupmodeforce),
                        'defaultgroupingid' => new external_value(PARAM_INT,  'ID nhóm mặc định',     VALUE_OPTIONAL, 0),
                        'enablecompletion' => new external_value(PARAM_INT,    'Bật hoàn thành',       VALUE_OPTIONAL),
                        'completionnotify' => new external_value(PARAM_INT,    'Thông báo hoàn thành', VALUE_OPTIONAL),
                        'lang'            => new external_value(PARAM_SAFEDIR, 'Ngôn ngữ khóa học',   VALUE_OPTIONAL),
                        'forcetheme'      => new external_value(PARAM_PLUGIN,  'Theme bắt buộc',      VALUE_OPTIONAL),

                        // ── Arrays ───────────────────────────────────────────────────
                        'options' => new external_multiple_structure(
                            new external_single_structure([
                                'name'  => new external_value(PARAM_ALPHAEXT, 'Tên option'),
                                'value' => new external_value(PARAM_RAW,      'Giá trị option'),
                            ]),
                            'Duplication options – POST only (users=0|1, role_X=roleid, ...)',
                            VALUE_DEFAULT,
                            []
                        ),
                        'courseformatoptions' => new external_multiple_structure(
                            new external_single_structure([
                                'name'  => new external_value(PARAM_ALPHANUMEXT, 'Tên format option'),
                                'value' => new external_value(PARAM_RAW,         'Giá trị format option'),
                            ]),
                            'Course format options',
                            VALUE_DEFAULT,
                            []
                        ),
                        'customfields' => new external_multiple_structure(
                            new external_single_structure([
                                'shortname' => new external_value(PARAM_ALPHANUMEXT, 'Shortname custom field'),
                                'value'     => new external_value(PARAM_RAW,         'Giá trị custom field'),
                            ]),
                            'Custom fields',
                            VALUE_DEFAULT,
                            []
                        ),
                    ]),
                    'Danh sách khóa học – POST (tạo/nhân bản) hoặc PATCH (cập nhật)',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // EXECUTION
        // ─────────────────────────────────────────────────────────────────────────────
        public static function courses($field = '', $value = '', $courses = [])
        {
            global $DB, $CFG, $USER;

            require_once($CFG->dirroot . '/course/externallib.php');

            $params = self::validate_parameters(self::courses_parameters(), [
                'field'   => $field,
                'value'   => $value,
                'courses' => $courses,
            ]);

            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

            // =========================================================================
            // GET → tìm kiếm khóa học
            // =========================================================================
            if ($method === 'GET') {
                // Nhánh shortnames: tìm nhiều shortname cùng lúc
                if ($params['field'] === 'shortnames') {
                    $shortnames = array_filter(array_map('trim', explode(',', $params['value'])));
                    if (empty($shortnames)) {
                        return [
                            'get_courses'   => [],
                            'get_warnings'  => [[
                                'item' => 'shortnames',
                                'itemid' => 0,
                                'warningcode' => 'invalidvalue',
                                'message'     => 'Danh sách shortname rỗng hoặc không hợp lệ.',
                            ]],
                            'post_results'  => [],
                            'patch_results' => [],
                        ];
                    }

                    [$in, $paramslist] = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED);
                    $found = $DB->get_records_select('course', "shortname $in", $paramslist, '', 'id');

                    if (!$found) {
                        return [
                            'get_courses'   => [],
                            'get_warnings'  => [[
                                'item' => 'shortnames',
                                'itemid' => 0,
                                'warningcode' => 'notfound',
                                'message'     => 'Không tìm thấy khóa học nào với shortnames đã truyền.',
                            ]],
                            'post_results'  => [],
                            'patch_results' => [],
                        ];
                    }

                    $params['field'] = 'ids';
                    $params['value'] = implode(',', array_keys($found));
                }

                // Nhánh categorycode → category id
                if ($params['field'] === 'categorycode') {
                    $cat = $DB->get_record('course_categories', ['idnumber' => $params['value']]);
                    if (!$cat) {
                        return [
                            'get_courses'   => [],
                            'get_warnings'  => [[
                                'item' => 'categorycode',
                                'itemid' => 0,
                                'warningcode' => 'notfound',
                                'message'     => 'Không tìm thấy danh mục với idnumber: ' . $params['value'],
                            ]],
                            'post_results'  => [],
                            'patch_results' => [],
                        ];
                    }
                    $params['field'] = 'category';
                    $params['value'] = $cat->id;
                }

                // Gọi core
                $core = \core_course_external::get_courses_by_field($params['field'], $params['value']);

                // Bổ sung categorycode, fix startdate/enddate
                if (!empty($core['courses']) && is_array($core['courses'])) {
                    $catids    = [];
                    $courseids = [];
                    foreach ($core['courses'] as $c) {
                        if (!empty($c['categoryid'])) $catids[]    = $c['categoryid'];
                        if (!empty($c['id']))         $courseids[] = $c['id'];
                    }

                    $catid2idnumber = [];
                    if ($catids) {
                        [$in, $catparams] = $DB->get_in_or_equal($catids, SQL_PARAMS_NAMED);
                        $cats = $DB->get_records_select('course_categories', "id $in", $catparams);
                        foreach ($cats as $cat) {
                            $catid2idnumber[$cat->id] = $cat->idnumber;
                        }
                    }

                    $id2course = [];
                    if ($courseids) {
                        [$in, $cparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
                        $crs = $DB->get_records_select('course', "id $in", $cparams, '', 'id,startdate,enddate');
                        foreach ($crs as $cr) {
                            $id2course[$cr->id] = $cr;
                        }
                    }

                    foreach ($core['courses'] as &$course) {
                        $course['categorycode'] = $catid2idnumber[$course['categoryid']] ?? '';
                        if (!empty($course['id']) && isset($id2course[$course['id']])) {
                            $course['startdate'] = $id2course[$course['id']]->startdate;
                            $course['enddate']   = $id2course[$course['id']]->enddate;
                        }
                    }
                    unset($course);
                }

                return [
                    'get_courses'   => $core['courses']  ?? [],
                    'get_warnings'  => $core['warnings'] ?? [],
                    'post_results'  => [],
                    'patch_results' => [],
                ];
            }

            // =========================================================================
            // POST → tạo / nhân bản khóa học
            // =========================================================================
            if ($method === 'POST') {
                self::validate_context(\context_system::instance());

                $results = [];

                foreach ($params['courses'] as $course) {
                    try {
                        // 1. Validate các trường bắt buộc
                        if (empty($course['fullname'])) {
                            self::set_last_copy_error($course['shortname'] ?? '', 'Thiếu trường fullname.', 'missing_fullname');
                            $results[] = self::fail_result($course['shortname'] ?? '', 'Thiếu trường fullname.');
                            continue;
                        }

                        if (empty($course['shortname'])) {
                            $results[] = self::fail_result('', 'Thiếu trường shortname.');
                            continue;
                        }

                        if (empty($course['categoryidnumber'])) {
                            self::set_last_copy_error($course['shortname'], 'Thiếu categoryidnumber.', 'missing_categoryidnumber');
                            $results[] = self::fail_result($course['shortname'], 'Thiếu categoryidnumber.');
                            continue;
                        }

                        $mode = trim(strtolower($course['mode'] ?? 'clone'));
                        if (!in_array($mode, ['template', 'new', 'clone'], true)) {
                            self::set_last_copy_error($course['shortname'], "Giá trị mode không hợp lệ: {$course['mode']}.", 'invalid_mode');
                            $results[] = self::fail_result($course['shortname'], "Giá trị mode không hợp lệ: {$course['mode']}.");
                            continue;
                        }

                        $startdate = isset($course['startdate']) ? (int)$course['startdate'] : 0;

                        // 2. Resolve category
                        $categoryid = $DB->get_field(
                            'course_categories',
                            'id',
                            ['idnumber' => $course['categoryidnumber']],
                            IGNORE_MISSING
                        );

                        if (!$categoryid) {
                            self::set_last_copy_error($course['shortname'], "Không tìm thấy category idnumber='{$course['categoryidnumber']}'.", 'category_not_found');
                            $results[] = self::fail_result($course['shortname'], "Không tìm thấy category với idnumber: {$course['categoryidnumber']}");
                            continue;
                        }

                        $catctx = \context_coursecat::instance($categoryid);
                        require_capability('moodle/course:create', $catctx);

                        // 3. Kiểm tra shortname chưa tồn tại
                        if ($DB->record_exists('course', ['shortname' => $course['shortname']])) {
                            self::set_last_copy_error($course['shortname'], "Shortname '{$course['shortname']}' đã tồn tại.", 'shortname_exists');
                            $results[] = self::fail_result($course['shortname'], "Khóa học với shortname '{$course['shortname']}' đã tồn tại.");
                            continue;
                        }

                        // ─────────────────────────────────────────────────────────────
                        // MODE: new
                        // ─────────────────────────────────────────────────────────────
                        if ($mode === 'new') {
                            if (empty($startdate) || $startdate <= 0) {
                                self::set_last_copy_error($course['shortname'], 'Thiếu startdate cho mode=new.', 'missing_startdate');
                                $results[] = self::fail_result($course['shortname'], "Thiếu 'startdate' (UNIX timestamp, UTC) cho mode=new.");
                                continue;
                            }

                            $newcourse = [
                                'fullname'            => $course['fullname'],
                                'shortname'           => $course['shortname'],
                                'categoryid'          => $categoryid,
                                'visible'             => (int)$course['visible'],
                                'startdate'           => $startdate,
                                'idnumber'            => !empty($course['idnumber']) ? $course['idnumber'] : $course['shortname'],
                                'courseformatoptions' => !empty($course['courseformatoptions'])
                                    ? $course['courseformatoptions']
                                    : [['name' => 'numsections', 'value' => get_config('moodlecourse', 'numsections') ?: 4]],
                            ];

                            if (!empty($course['customfields'])) {
                                $newcourse['customfields'] = $course['customfields'];
                            }

                            $created   = \core_course_external::create_courses([$newcourse]);
                            self::clear_last_copy_error($course['shortname']);
                            $results[] = [
                                'status'    => 'Done',
                                'id'        => (int)$created[0]['id'],
                                'shortname' => (string)$created[0]['shortname'],
                                'message'   => 'Empty course created (mode=new).',
                            ];
                            continue;
                        }

                        // ─────────────────────────────────────────────────────────────
                        // MODE: template | clone
                        // ─────────────────────────────────────────────────────────────
                        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
                        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

                        // 4. Xác định khóa nguồn
                        $sourcecourse = null;

                        if ($mode === 'template') {
                            $srcshort = trim($course['sourceshortname'] ?? '');
                            if ($srcshort === '') {
                                self::set_last_copy_error($course['shortname'], "mode=template yêu cầu 'sourceshortname'.", 'missing_sourceshortname');
                                $results[] = self::fail_result($course['shortname'], "mode=template yêu cầu 'sourceshortname'.");
                                continue;
                            }

                            $sourcecourse = $DB->get_record('course', ['shortname' => $srcshort], '*', IGNORE_MISSING);
                            if (!$sourcecourse) {
                                self::set_last_copy_error($course['shortname'], "Không tìm thấy khóa nguồn '{$srcshort}'.", 'source_not_found');
                                $results[] = self::fail_result($course['shortname'], "Không tìm thấy khóa nguồn với sourceshortname='{$srcshort}'.");
                                continue;
                            }

                            if (empty($startdate) || $startdate <= 0) {
                                self::set_last_copy_error($course['shortname'], 'Thiếu startdate cho mode=template.', 'missing_startdate');
                                $results[] = self::fail_result($course['shortname'], "Thiếu 'startdate' (UNIX timestamp, UTC) cho mode=template.");
                                continue;
                            }
                        } else {
                            // mode = clone
                            $srcshort = trim($course['sourceshortname'] ?? '');

                            if ($srcshort !== '') {
                                $sourcecourse = $DB->get_record('course', ['shortname' => $srcshort], '*', IGNORE_MISSING);
                                if (!$sourcecourse) {
                                    self::set_last_copy_error($course['shortname'], "Không tìm thấy khóa nguồn '{$srcshort}'.", 'source_not_found');
                                    $results[] = self::fail_result($course['shortname'], "Không tìm thấy khóa nguồn với sourceshortname='{$srcshort}'.");
                                    continue;
                                }
                            } else {
                                $coursecodefield = self::get_coursecode_field_shortname();
                                if (empty($coursecodefield)) {
                                    self::set_last_copy_error($course['shortname'], 'Chưa cấu hình coursecode_field_shortname.', 'missing_coursecode_setting');
                                    $results[] = self::fail_result($course['shortname'], 'Chưa cấu hình trường course code (local_thcourse/coursecode_field_shortname).');
                                    continue;
                                }

                                $coursecode = self::extract_customfield_value($course['customfields'] ?? [], $coursecodefield);
                                if ($coursecode === null || $coursecode === '') {
                                    self::set_last_copy_error($course['shortname'], "Thiếu custom field '{$coursecodefield}'.", 'missing_coursecode_value');
                                    $results[] = self::fail_result($course['shortname'], "Thiếu custom field '{$coursecodefield}' để xác định khóa nguồn (mode=clone).");
                                    continue;
                                }

                                $sql = "
                            SELECT c.*
                              FROM {course} c
                              JOIN {customfield_data} d       ON d.instanceid = c.id
                              JOIN {customfield_field} f      ON f.id = d.fieldid
                              JOIN {customfield_category} cat ON cat.id = f.categoryid
                             WHERE cat.component  = :component
                               AND cat.area       = :area
                               AND f.shortname    = :fieldshort
                               AND c.id          <> 1
                               AND c.shortname   <> :newshortname
                               AND (d.shortcharvalue = :code1 OR d.charvalue = :code2 OR d.value = :code3)
                          ORDER BY
                               CASE WHEN c.startdate IS NULL OR c.startdate = 0
                                    THEN c.timecreated ELSE c.startdate END DESC,
                               c.id DESC
                             LIMIT 1";

                                $sourcecourse = $DB->get_record_sql($sql, [
                                    'component'    => 'core_course',
                                    'area'         => 'course',
                                    'fieldshort'   => $coursecodefield,
                                    'newshortname' => $course['shortname'],
                                    'code1'        => $coursecode,
                                    'code2'        => $coursecode,
                                    'code3'        => $coursecode,
                                ], IGNORE_MISSING);

                                if (!$sourcecourse) {
                                    self::set_last_copy_error($course['shortname'], "Không tìm thấy khóa nguồn có {$coursecodefield}='{$coursecode}'.", 'source_not_found');
                                    $results[] = self::fail_result($course['shortname'], "Không tìm thấy khóa nguồn có {$coursecodefield}='{$coursecode}'.");
                                    continue;
                                }
                            }

                            if (empty($startdate) || $startdate <= 0) {
                                self::set_last_copy_error($course['shortname'], 'Thiếu startdate cho mode=clone.', 'missing_startdate');
                                $results[] = self::fail_result($course['shortname'], "Thiếu 'startdate' (UNIX timestamp, UTC) cho mode=clone.");
                                continue;
                            }
                        }

                        // 5. Kiểm tra quyền sao chép trên khóa nguồn
                        $sourcectx = \context_course::instance($sourcecourse->id);
                        $copycaps  = \core_course\management\helper::get_course_copy_capabilities();
                        require_all_capabilities($copycaps, $sourcectx);

                        // 6. Tính enddate theo offset
                        $sourcestart = (int)$sourcecourse->startdate;
                        $sourceend   = (int)($sourcecourse->enddate ?? 0);
                        $enddate     = ($sourcestart > 0 && $startdate > 0 && $sourceend > 0)
                            ? $sourceend + ($startdate - $sourcestart)
                            : 0;

                        // 7. Giải mã options
                        $userdata  = 0;
                        $keptroles = [];
                        foreach ($course['options'] ?? [] as $opt) {
                            if (($opt['name'] ?? '') === 'users') {
                                $userdata = (int)($opt['value'] ?? 0);
                            }
                            if (substr($opt['name'] ?? '', 0, 5) === 'role_' && (int)($opt['value'] ?? 0) !== 0) {
                                $keptroles[] = (int)$opt['value'];
                            }
                        }

                        // 8. Build formdata
                        $formdata = (object)[
                            'courseid'  => (int)$sourcecourse->id,
                            'fullname'  => $course['fullname'],
                            'shortname' => $course['shortname'],
                            'category'  => $categoryid,
                            'visible'   => (int)$course['visible'],
                            'startdate' => $startdate,
                            'enddate'   => $enddate,
                            'idnumber'  => !empty($course['idnumber']) ? $course['idnumber'] : $course['shortname'],
                            'userdata'  => $userdata,
                        ];
                        foreach ($keptroles as $roleid) {
                            $formdata->{'role_' . $roleid} = $roleid;
                        }

                        // 9. Kiểm tra task đã tồn tại trong hàng đợi
                        $inProgressBefore = \copy_helper::get_copies($USER->id);
                        foreach ($inProgressBefore as $existingCopy) {
                            if (!empty($existingCopy->destination) && $existingCopy->destination === $course['shortname']) {
                                $label     = ($existingCopy->operation === \backup::OPERATION_BACKUP) ? 'backup' : 'restore';
                                $results[] = [
                                    'status'    => 'Done',
                                    'id'        => 0,
                                    'shortname' => $course['shortname'],
                                    'message'   => "Task sao chép ({$label}) cho shortname '{$course['shortname']}' đã tồn tại trong hàng đợi — bỏ qua để tránh trùng lặp.",
                                ];
                                continue 2;
                            }
                        }

                        // 10. Chạy backup/restore
                        \core\session\manager::write_close();
                        raise_memory_limit(MEMORY_EXTRA);
                        \core_php_time_limit::raise(0);

                        ob_start();
                        try {
                            $copydata = \copy_helper::process_formdata($formdata);
                            \copy_helper::create_copy($copydata);
                            ob_end_clean();
                        } catch (\Throwable $inner) {
                            ob_end_clean();
                            throw $inner;
                        }

                        self::clear_last_copy_error($course['shortname']);
                        $results[] = [
                            'status'    => 'Done',
                            'id'        => 0,
                            'shortname' => $course['shortname'],
                            'message'   => "Copy task queued (mode={$mode}). Poll get_multi_copy_status to track completion.",
                        ];
                    } catch (\required_capability_exception $e) {
                        self::set_last_copy_error($course['shortname'] ?? '', 'Không đủ quyền tạo/sao chép khóa học.', 'no_capability');
                        $results[] = self::fail_result($course['shortname'] ?? '', 'Không đủ quyền tạo/sao chép khóa học.');
                    } catch (\Throwable $e) {
                        $msg = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
                        self::set_last_copy_error($course['shortname'] ?? '', $msg, 'exception');
                        $results[] = self::fail_result($course['shortname'] ?? '', $msg);
                    }
                }

                return [
                    'get_courses'   => [],
                    'get_warnings'  => [],
                    'post_results'  => $results,
                    'patch_results' => [],
                ];
            }

            // =========================================================================
            // PATCH → cập nhật khóa học (partial update theo shortname)
            // =========================================================================
            if ($method === 'PATCH') {
                self::validate_context(\context_system::instance());

                $results     = [];
                $core_courses = [];

                // ── Pass 1: validate + resolve từng khóa học ─────────────────────────
                foreach ($params['courses'] as $course) {
                    if (empty($course['shortname'])) {
                        $results[] = [
                            'shortname' => '',
                            'status'    => 'error',
                            'message'   => 'Thiếu trường shortname (dùng để định danh khóa học khi PATCH).',
                        ];
                        continue;
                    }

                    $rec = $DB->get_record('course', ['shortname' => $course['shortname']], '*', IGNORE_MISSING);
                    if (!$rec) {
                        $results[] = [
                            'shortname' => $course['shortname'],
                            'status'    => 'not_found',
                            'message'   => "Không tìm thấy khóa học với shortname: {$course['shortname']}",
                        ];
                        continue;
                    }

                    // Kiểm tra quyền update
                    $ctx = \context_course::instance($rec->id);
                    try {
                        require_capability('moodle/course:update', $ctx);
                    } catch (\required_capability_exception $e) {
                        $results[] = [
                            'shortname' => $course['shortname'],
                            'status'    => 'error',
                            'message'   => 'Không đủ quyền cập nhật khóa học.',
                        ];
                        continue;
                    }

                    // Build payload cho core_course_external::update_courses
                    // Chỉ đưa vào những field thực sự được truyền (partial update)
                    $core = ['id' => $rec->id];

                    $scalarFields = [
                        'fullname',
                        'idnumber',
                        'summary',
                        'summaryformat',
                        'format',
                        'showgrades',
                        'newsitems',
                        'startdate',
                        'enddate',
                        'numsections',
                        'maxbytes',
                        'showreports',
                        'visible',
                        'hiddensections',
                        'groupmode',
                        'groupmodeforce',
                        'defaultgroupingid',
                        'enablecompletion',
                        'completionnotify',
                        'lang',
                        'forcetheme',
                    ];

                    foreach ($scalarFields as $f) {
                        // Chỉ đưa vào nếu key tồn tại và khác null
                        if (array_key_exists($f, $course) && $course[$f] !== null) {
                            $core[$f] = $course[$f];
                        }
                    }

                    // Xử lý categoryidnumber → categoryid
                    if (!empty($course['categoryidnumber'])) {
                        $cat = $DB->get_record('course_categories', ['idnumber' => $course['categoryidnumber']], 'id', IGNORE_MISSING);
                        if (!$cat) {
                            $results[] = [
                                'shortname' => $course['shortname'],
                                'status'    => 'error',
                                'message'   => "Không tìm thấy danh mục với categoryidnumber: {$course['categoryidnumber']}",
                            ];
                            continue;
                        }
                        $core['categoryid'] = $cat->id;
                    }

                    // Truyền thẳng mảng format options & custom fields nếu có
                    if (!empty($course['courseformatoptions'])) {
                        $core['courseformatoptions'] = $course['courseformatoptions'];
                    }
                    if (!empty($course['customfields'])) {
                        $core['customfields'] = $course['customfields'];
                    }

                    $core_courses[] = $core;
                    $results[] = [
                        'shortname' => $course['shortname'],
                        'status'    => 'pending',
                        'message'   => '',
                    ];
                }

                // ── Pass 2: gọi core nếu có record hợp lệ ───────────────────────────
                if ($core_courses) {
                    try {
                        \core_course_external::update_courses($core_courses);
                        foreach ($results as &$res) {
                            if ($res['status'] === 'pending') {
                                $res['status']  = 'updated';
                                $res['message'] = 'Đã cập nhật thành công.';
                            }
                        }
                        unset($res);
                    } catch (\Exception $e) {
                        foreach ($results as &$res) {
                            if ($res['status'] === 'pending') {
                                $res['status']  = 'error';
                                $res['message'] = $e->getMessage();
                            }
                        }
                        unset($res);
                    }
                }

                return [
                    'get_courses'   => [],
                    'get_warnings'  => [],
                    'post_results'  => [],
                    'patch_results' => $results,
                ];
            }

            // =========================================================================
            // Method không hỗ trợ
            // =========================================================================
            throw new \invalid_parameter_exception("Method '{$method}' not supported. Use GET, POST, or PATCH.");
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // RETURNS
        // ─────────────────────────────────────────────────────────────────────────────
        public static function courses_returns()
        {
            return new external_single_structure([
                'get_courses' => new external_multiple_structure(
                    self::get_course_structure_with_categorycode(false),
                    'Danh sách khóa học (GET)',
                    VALUE_OPTIONAL
                ),
                'get_warnings' => new external_warnings(),
                'post_results' => new external_multiple_structure(
                    new external_single_structure([
                        'status'    => new external_value(PARAM_TEXT, 'Done | Failed'),
                        'id'        => new external_value(PARAM_INT,  'Course ID (0 nếu async hoặc lỗi)'),
                        'shortname' => new external_value(PARAM_TEXT, 'Shortname khóa học'),
                        'message'   => new external_value(PARAM_TEXT, 'Kết quả chi tiết'),
                    ]),
                    'Kết quả tạo/nhân bản khóa học (POST)',
                    VALUE_OPTIONAL
                ),
                'patch_results' => new external_multiple_structure(
                    new external_single_structure([
                        'shortname' => new external_value(PARAM_TEXT, 'Shortname khóa học (định danh)'),
                        'status'    => new external_value(PARAM_TEXT, 'updated | not_found | error'),
                        'message'   => new external_value(PARAM_TEXT, 'Thông báo kết quả'),
                    ]),
                    'Kết quả cập nhật khóa học (PATCH)',
                    VALUE_OPTIONAL
                ),
            ]);
        }


        public static function courses_copy_status_parameters()
        {
            return new external_function_parameters([
                'shortnames' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Shortname khóa học đích cần kiểm tra'),
                    'Danh sách shortname cần kiểm tra trạng thái sao chép'
                ),
            ]);
        }

        public static function courses_copy_status($shortnames)
        {
            global $DB, $CFG, $USER;

            $params = self::validate_parameters(
                self::courses_copy_status_parameters(),
                ['shortnames' => $shortnames]
            );

            self::validate_context(\context_system::instance());

            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

            if ($method !== 'GET') {
                throw new \invalid_parameter_exception("Method '{$method}' not supported. Use GET.");
            }

            require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
            require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

            // Lấy tất cả bản sao đang xử lý của user hiện tại
            $inProgressCopies = \copy_helper::get_copies($USER->id);

            // Index theo destination shortname để lookup O(1)
            $copyByDest = [];
            foreach ($inProgressCopies as $copy) {
                if (!empty($copy->destination)) {
                    $copyByDest[$copy->destination] = $copy;
                }
            }

            $results = [];

            foreach ($params['shortnames'] as $shortname) {

                // 1. Khóa đích đã tồn tại → completed
                if ($DB->record_exists('course', ['shortname' => $shortname])) {
                    self::clear_last_copy_error($shortname);
                    $results[] = [
                        'shortname' => $shortname,
                        'status'    => 'completed',
                        'message'   => 'Target course already exists.',
                    ];
                    continue;
                }

                // 2. Task đang chạy → in_progress
                if (isset($copyByDest[$shortname])) {
                    $copy  = $copyByDest[$shortname];
                    $label = $copy->operation === \backup::OPERATION_BACKUP
                        ? 'Backing up'
                        : 'Restoring';
                    $results[] = [
                        'shortname' => $shortname,
                        'status'    => 'in_progress',
                        'message'   => "{$label} in progress (source: {$copy->source}).",
                    ];
                    continue;
                }

                // 3. Không có task, khóa chưa tồn tại → error
                $last = self::get_last_copy_error($shortname);
                $results[] = [
                    'shortname' => $shortname,
                    'status'    => 'error',
                    'message'   => $last
                        ? (string)($last['message'] ?? 'Unknown error')
                        : 'Không tìm thấy khóa học đích và không có tác vụ sao chép tương ứng.',
                ];
            }

            return $results;
        }

        public static function courses_copy_status_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'shortname' => new external_value(PARAM_TEXT, 'Shortname khóa học đích'),
                    'status'    => new external_value(PARAM_TEXT, 'in_progress | completed | error'),
                    'message'   => new external_value(PARAM_TEXT, 'Chi tiết trạng thái'),
                ])
            );
        }


        public static function enrols_parameters()
        {
            return new external_function_parameters([
                // ── GET ──────────────────────────────────────────────────────────────
                'coursecodes' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Shortname (mã) của khóa học'),
                    'Danh sách shortname khóa học cần lấy user (GET)',
                    VALUE_DEFAULT,
                    []
                ),
                'options' => new external_multiple_structure(
                    new external_single_structure([
                        'name'  => new external_value(PARAM_ALPHANUMEXT, 'Option name'),
                        'value' => new external_value(PARAM_RAW,         'Option value'),
                    ]),
                    'Các tùy chọn filter giống core_enrol_get_enrolled_users (GET)',
                    VALUE_DEFAULT,
                    []
                ),

                // ── PATCH ─────────────────────────────────────────────────────────────
                'enrolments' => new external_multiple_structure(
                    new external_single_structure([
                        'rolecodes' => new external_multiple_structure(
                            new external_value(PARAM_RAW, 'Mã vai trò (shortname)'),
                            'Danh sách mã vai trò (shortname)'
                        ),
                        'username'        => new external_value(PARAM_RAW, 'Tên đăng nhập người dùng'),
                        'courseshortname' => new external_value(PARAM_RAW, 'Mã khóa học (shortname)'),
                        'timestart'       => new external_value(PARAM_INT, 'Thời gian bắt đầu ghi danh (timestamp)', VALUE_OPTIONAL),
                        'timeend'         => new external_value(PARAM_INT, 'Thời gian kết thúc ghi danh (timestamp)', VALUE_OPTIONAL),
                        'suspend'         => new external_value(PARAM_INT, '0: hoạt động, 1: tạm dừng',              VALUE_OPTIONAL),
                    ]),
                    'Danh sách ghi danh cần cập nhật (PATCH)',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // EXECUTION
        // ─────────────────────────────────────────────────────────────────────────────
        public static function enrols($coursecodes = [], $options = [], $enrolments = [])
        {
            global $DB, $CFG, $USER;

            $params = self::validate_parameters(self::enrols_parameters(), [
                'coursecodes' => $coursecodes,
                'options'     => $options,
                'enrolments'  => $enrolments,
            ]);

            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

            // =========================================================================
            // GET → lấy danh sách user đã ghi danh theo coursecode
            // =========================================================================
            if ($method === 'GET') {
                require_once($CFG->dirroot . '/enrol/externallib.php');

                $results = [];

                foreach ($params['coursecodes'] as $coursecode) {
                    $course = $DB->get_record('course', ['shortname' => $coursecode], '*', IGNORE_MISSING);
                    if (!$course) {
                        $results[] = [
                            'coursecode'   => $coursecode,
                            'users'        => [],
                            'error'        => 'Course not found',
                            'enrol_results' => [],
                        ];
                        continue;
                    }

                    $users = self::get_enrolled_users($course->id, $params['options']);
                    $results[] = [
                        'coursecode'    => $coursecode,
                        'users'         => $users,
                        'enrol_results' => [],
                    ];
                }

                return $results;
            }

            // =========================================================================
            // PATCH → cập nhật enrol / role cho user vào course
            // =========================================================================
            if ($method === 'PATCH') {
                self::validate_context(\context_system::instance());

                $enrolments_for_api  = [];
                $results             = [];
                $user_course_roles_map = [];

                $debug_old        = $CFG->debug       ?? 0;
                $debugdisplay_old = $CFG->debugdisplay ?? false;
                $CFG->debug        = DEBUG_DEVELOPER;
                $CFG->debugdisplay = true;

                try {
                    foreach ($params['enrolments'] as $enrol) {
                        $username        = $enrol['username'];
                        $courseshortname = $enrol['courseshortname'];
                        $rolecodes       = $enrol['rolecodes'];
                        $timestart       = $enrol['timestart'] ?? 0;
                        $timeend         = $enrol['timeend']   ?? 0;
                        $suspend         = $enrol['suspend']   ?? 0;

                        // Resolve userid
                        $user = $DB->get_record('user', ['username' => $username, 'deleted' => 0], '*', IGNORE_MISSING);
                        if (!$user) {
                            throw new \Exception("User not found with username: {$username}");
                        }

                        // Resolve courseid
                        $course = $DB->get_record('course', ['shortname' => $courseshortname], '*', IGNORE_MISSING);
                        if (!$course) {
                            throw new \Exception("Course not found with shortname: {$courseshortname}");
                        }

                        // Resolve roleids
                        $roleids = [];
                        foreach ($rolecodes as $rolecode) {
                            $role = $DB->get_record('role', ['shortname' => $rolecode], '*', IGNORE_MISSING);
                            if (!$role) {
                                throw new \Exception("Role not found with rolecode: {$rolecode}");
                            }
                            $roleids[] = $role->id;
                        }

                        // Gom theo user-course để xử lý unassign role thừa sau khi enrol
                        $key = $user->id . '-' . $course->id;
                        if (!isset($user_course_roles_map[$key])) {
                            $user_course_roles_map[$key] = [
                                'userid'   => $user->id,
                                'courseid' => $course->id,
                                'roleids'  => [],
                            ];
                        }
                        $user_course_roles_map[$key]['roleids'] = array_unique(
                            array_merge($user_course_roles_map[$key]['roleids'], $roleids)
                        );

                        foreach ($roleids as $roleid) {
                            $enrolments_for_api[] = [
                                'roleid'    => $roleid,
                                'userid'    => $user->id,
                                'courseid'  => $course->id,
                                'timestart' => $timestart,
                                'timeend'   => $timeend,
                                'suspend'   => $suspend,
                            ];
                        }

                        $results[] = [
                            'username'        => $username,
                            'courseshortname' => $courseshortname,
                            'roleids'         => $roleids,
                            'rolecodes'       => $rolecodes,
                            'userid'          => $user->id,
                            'courseid'        => $course->id,
                            'status'          => 'pending',
                        ];
                    }

                    // Gọi enrol duy nhất 1 lần cho toàn bộ enrolments
                    if ($enrolments_for_api) {
                        \enrol_manual_external::enrol_users($enrolments_for_api);
                    }

                    // Unassign các role thừa (không nằm trong danh sách role cần giữ)
                    foreach ($user_course_roles_map as $ucr) {
                        $context       = \context_course::instance($ucr['courseid']);
                        $current_roles = $DB->get_records('role_assignments', [
                            'userid'    => $ucr['userid'],
                            'contextid' => $context->id,
                        ]);
                        foreach ($current_roles as $ra) {
                            if (!in_array($ra->roleid, $ucr['roleids'])) {
                                role_unassign($ra->roleid, $ucr['userid'], $context->id);
                            }
                        }
                    }

                    foreach ($results as &$r) {
                        $r['status'] = 'success';
                    }
                    unset($r);
                } catch (\Throwable $e) {
                    $CFG->debug        = $debug_old;
                    $CFG->debugdisplay = $debugdisplay_old;
                    throw new \Exception($e->getMessage());
                }

                $CFG->debug        = $debug_old;
                $CFG->debugdisplay = $debugdisplay_old;

                // Wrap kết quả PATCH vào cùng return structure
                return array_map(function ($r) {
                    return [
                        'coursecode'    => $r['courseshortname'],
                        'users'         => [],
                        'enrol_results' => [$r],
                    ];
                }, $results);
            }

            throw new \invalid_parameter_exception("Method '{$method}' not supported. Use GET or PATCH.");
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // RETURNS
        // ─────────────────────────────────────────────────────────────────────────────
        public static function enrols_returns()
        {
            return new external_multiple_structure(
                new external_single_structure([
                    'coursecode' => new external_value(PARAM_TEXT, 'Shortname của khóa học'),
                    'users'      => self::get_enrolled_users_returns(),
                    'error'      => new external_value(PARAM_TEXT, 'Thông báo lỗi nếu có (GET)', VALUE_OPTIONAL),
                    'enrol_results' => new external_multiple_structure(
                        new external_single_structure([
                            'username'        => new external_value(PARAM_RAW, 'Tên đăng nhập'),
                            'courseshortname' => new external_value(PARAM_RAW, 'Shortname khóa học'),
                            'roleids'         => new external_multiple_structure(
                                new external_value(PARAM_INT, 'ID vai trò'),
                                'Danh sách roleid đã enrol'
                            ),
                            'rolecodes'       => new external_multiple_structure(
                                new external_value(PARAM_RAW, 'Shortname vai trò'),
                                'Danh sách rolecode đã enrol'
                            ),
                            'userid'   => new external_value(PARAM_INT, 'ID người dùng'),
                            'courseid' => new external_value(PARAM_INT, 'ID khóa học'),
                            'status'   => new external_value(PARAM_RAW, 'success | pending | fail'),
                        ]),
                        'Kết quả enrol/update (PATCH)',
                        VALUE_DEFAULT,
                        []
                    ),
                ])
            );
        }

        public static function roles_parameters(): external_function_parameters
        {
            return new external_function_parameters([]);
        }

        public static function roles(): array
        {
            global $DB;

            // Không có param cần validate
            self::validate_parameters(self::roles_parameters(), []);

            $context = context_system::instance();
            self::validate_context($context);

            if (!has_capability('local/th_ops:can_view_all_roles', $context)) {
                throw new required_capability_exception(
                    $context,
                    'local/th_ops:can_view_all_roles',
                    'nopermissions',
                    ''
                );
            }

            $sql = "
        SELECT r.id, r.shortname, r.name, r.description
        FROM {role} r
        JOIN {role_context_levels} rcl ON rcl.roleid = r.id
        WHERE rcl.contextlevel = :contextcourse
        GROUP BY r.id, r.shortname, r.name, r.description
        ORDER BY r.sortorder ASC
    ";

            $records = $DB->get_records_sql($sql, ['contextcourse' => CONTEXT_COURSE]);

            $role_list = array_values(array_map(fn($r) => [
                'id'          => (int) $r->id,
                'shortname'   => $r->shortname,
                'name'        => $r->name,
                'description' => strip_tags($r->description),
            ], $records));

            return [
                'roles' => $role_list,
            ];
        }

        public static function roles_returns(): external_single_structure
        {
            return new external_single_structure([
                'roles' => new external_multiple_structure(
                    new external_single_structure([
                        'id'          => new external_value(PARAM_INT,  'ID vai trò'),
                        'shortname'   => new external_value(PARAM_TEXT, 'Shortname vai trò'),
                        'name'        => new external_value(PARAM_TEXT, 'Tên vai trò'),
                        'description' => new external_value(PARAM_TEXT, 'Mô tả vai trò'),
                    ]),
                    'Danh sách vai trò',
                    VALUE_DEFAULT,
                    []
                ),
            ]);
        }
    }
