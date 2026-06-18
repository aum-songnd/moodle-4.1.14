<?php

defined('MOODLE_INTERNAL') || die();

function local_th_online_exam_manager_extend_navigation_course($navigation, $course, $context) {

    if (has_capability('local/th_online_exam_manager:exam_results', $context) || has_capability('local/th_online_exam_manager:exam_attendance', $context) ) {
        // Tạo URL và node cha
        $parenturl = '#';
        $parentnode = $navigation->add(
            get_string('reporttitle', 'local_th_online_exam_manager'),
            $parenturl,
            navigation_node::TYPE_SETTING,
            null,
            'th_online_exam_manager',
            new pix_icon('i/report', '')
        );

		if (has_capability('local/th_online_exam_manager:exam_attendance', $context)) {
			// Link con 1: danh sách kết quả thi
			$childurl1 = new moodle_url('/local/th_online_exam_manager/exam_attendance.php', ['courseid' => $course->id]);
			$parentnode->add(
				get_string('contestreport', 'local_th_online_exam_manager'),
				$childurl1,
				navigation_node::TYPE_SETTING,
				null,
				'th_exam_results'
			);
		}
        
		if (has_capability('local/th_online_exam_manager:exam_results', $context)) {
			// Link con 2: danh sách phúc khảo
			$childurl2 = new moodle_url('/local/th_online_exam_manager/exam_results.php', ['courseid' => $course->id]);
			$parentnode->add(
				get_string('resultreport', 'local_th_online_exam_manager'),
				$childurl2,
				navigation_node::TYPE_SETTING,
				null,
				'th_regrade_requests'
			);
		}
    }
}

function get_list_role() {
    global $DB;
    $sql = "SELECT * FROM {role} WHERE NOT id = 1";
    $role = $DB->get_records_sql($sql);
    if (!empty($role)) {
        $sql1 = "SELECT DISTINCT roleid FROM {role_context_levels} ORDER BY roleid";
        $role_id = $DB->get_records_sql($sql1);
        foreach ($role_id as $k => $role2) {
            $list[] = $role2->roleid;
        }
        $listroles = [];
        foreach ($role as $id => $role1) {
            $context = context_system::instance();
            $rolename = role_get_name($role1, $context);
            if (in_array($id, $list) == true) {
                $listroles[$id] = $rolename . ' , ' .$role1->shortname;
            }
        }
    }
    return $listroles;
}

function th_regrade_assignments_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'pending';
    $pendingurl = new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', array('status' => 'pending'));

	if (has_capability('local/th_online_exam_manager:regrade', $context)) {
		$pendingurl = new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', array('status' => 'pending'));
		$tabs[] = new tabobject('pending', $pendingurl, get_string('pending', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $pendingurl->get_param('status')) {
			$currenttab = 'pending';
		}
	}
	
	if (has_capability('local/th_online_exam_manager:regrade', $context)) {
		$regradedurl = new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', array('status' => 'regraded'));
		$tabs[] = new tabobject('regraded', $regradedurl, get_string('regraded', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $regradedurl->get_param('status')) {
			$currenttab = 'regraded';
		}
	}
    
    if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
		$waitingreviewurl = new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', array('status' => 'waitingreview'));
		$tabs[] = new tabobject('waitingreview', $waitingreviewurl, get_string('waitingreview', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $waitingreviewurl->get_param('status')) {
			$currenttab = 'waitingreview';
		}
	}	

    if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
		$reviewedurl = new moodle_url('/local/th_online_exam_manager/regrade_assignments_list.php', array('status' => 'reviewed'));
		$tabs[] = new tabobject('reviewed', $reviewedurl, get_string('reviewed', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $reviewedurl->get_param('status')) {
			$currenttab = 'reviewed';
		}
    }

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

function th_regrade_quiz_select_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'waiting';
    $pendingurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => 'waiting'));

	if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
		$pendingurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => 'waiting'));
		$tabs[] = new tabobject('waiting', $pendingurl, get_string('waiting', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $pendingurl->get_param('status')) {
			$currenttab = 'waiting';
		}
	}

	if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
		$pendingurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => 'pending'));
		$tabs[] = new tabobject('pending', $pendingurl, get_string('pending', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $pendingurl->get_param('status')) {
			$currenttab = 'pending';
		}
	}
	
	if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
		$regradedurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => 'regraded'));
		$tabs[] = new tabobject('regraded', $regradedurl, get_string('regraded', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $regradedurl->get_param('status')) {
			$currenttab = 'regraded';
		}
	}
    
    if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
		$waitingreviewurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => 'waitingreview'));
		$tabs[] = new tabobject('waitingreview', $waitingreviewurl, get_string('waitingreview', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $waitingreviewurl->get_param('status')) {
			$currenttab = 'waitingreview';
		}
	}	

    if (has_capability('local/th_online_exam_manager:manager_regrade', $context)) {
		$reviewedurl = new moodle_url('/local/th_online_exam_manager/regrade_quiz_select.php', array('status' => 'reviewed'));
		$tabs[] = new tabobject('reviewed', $reviewedurl, get_string('reviewed', 'local_th_online_exam_manager'));
		if ($currenturl->get_param('status') == $reviewedurl->get_param('status')) {
			$currenttab = 'reviewed';
		}
    }

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

function load_quizzes_in_courses($courseids) {
    global $DB;
    if (empty($courseids)) {
        return [];
    }
    list($insql, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
    $quizzes = $DB->get_records_select('quiz', "course $insql", $params);
    return array_keys($quizzes);
}

function load_users_enrolled_in_courses($courseids) {
    global $DB;
    if (empty($courseids)) {
        return [];
    }

    list($insql, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

    $sql = "
        SELECT ue.id, u.id as userid, e.courseid
        FROM {user_enrolments} ue
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {user} u ON u.id = ue.userid
        JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = 50
        JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
        WHERE e.courseid $insql
        AND u.deleted = 0
        AND ra.roleid = 5
        AND u.suspended = 0
    ";

    $records = $DB->get_records_sql($sql, $params);

    $result = [];
    foreach ($records as $record) {
        $result[$record->userid][$record->courseid] = true;
    }

    return $result;
}

function load_users_in_groups($groupids) {
    global $DB;

    if (empty($groupids)) {
        return [];
    }

    list($insql, $params) = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED);

    $sql = "
        SELECT gm.id, u.id AS userid, g.courseid
        FROM {groups_members} gm
        JOIN {user} u ON u.id = gm.userid
        JOIN {groups} g ON g.id = gm.groupid
        JOIN {context} ctx ON ctx.contextlevel = 50 AND ctx.instanceid = g.courseid
        JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
        WHERE gm.groupid $insql
        AND u.deleted = 0
        AND u.suspended = 0
        AND ra.roleid = 5
    ";

    $records = $DB->get_records_sql($sql, $params);

    $result = [];
    foreach ($records as $record) {
        $result[$record->userid][$record->courseid] = true;
    }

    return $result;
}

function load_user_fullnames(array $userids): array {
    global $DB;
    if (empty($userids)) {
        return [];
    }

    list($insql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

    $sql = "
        SELECT 
            u.id, 
            u.firstname, 
            u.lastname, 
            u.firstnamephonetic, 
            u.lastnamephonetic, 
            u.middlename, 
            u.alternatename
        FROM {user} u
        WHERE u.id $insql
    ";

    $users = $DB->get_records_sql($sql, $params);

    $result = [];
    foreach ($users as $user) {
        $result[$user->id] = fullname($user);
    }

    return $result;
}