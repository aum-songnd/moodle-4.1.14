<?php

defined('MOODLE_INTERNAL') || die();

function local_th_list_user_in_course_extend_navigation_course($navigation, $course, $context) {
	if (has_capability('local/th_list_user_in_course:view', $context)) {
		$url = new moodle_url('/local/th_list_user_in_course/index.php', array('id' => $course->id));
		$th_list_user_in_coursenode = navigation_node::create(get_string('pluginname', 'local_th_list_user_in_course'), $url,
			navigation_node::TYPE_SETTING, null, 'local_th_list_user_in_course', new pix_icon('i/users', ''));
		$usersnode = $navigation->get('users');

		if (isset($th_list_user_in_coursenode) && !empty($usersnode)) {
			$usersnode->add_node($th_list_user_in_coursenode);
		}
	}
}

function get_role_name($role_id) {
    global $DB;
    $sql = "SELECT name FROM {role} WHERE id = $role_id";
    $rolename = $DB->get_field_sql($sql);
    return $rolename;
}

function local_th_list_user_in_course_controls(context $context, moodle_url $currenturl, $course_id) {
    $tabs = array();
    $currenttab = 'view';
    $view = new moodle_url('/local/th_list_user_in_course/index.php');

    if (has_capability('local/th_list_user_in_course:view', $context)) {
        $addurl = new moodle_url('/local/th_list_user_in_course/index.php', array('id' => $course_id));
        $tabs[] = new tabobject('index', $addurl, "Danh sách thành viên");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'index';
        }
    }

    if (has_capability('local/th_list_user_in_course:view', $context)) {
        $addurl = new moodle_url('/local/th_list_user_in_course/th_list_group.php', array('id' => $course_id));
        $tabs[] = new tabobject('groups', $addurl, "Danh sách nhóm");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'groups';
        }
    }

    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

function get_list_groups($course_id) {
    global $DB;
    $groups = [];
    $list_groups_sql = "SELECT * FROM {th_list_user_in_course_group} WHERE course_id = $course_id";
    $list_groups = $DB->get_records_sql($list_groups_sql);
    if (count($list_groups)) {
        foreach ($list_groups as $id => $group) {
            $groups[$id] = $group->ten_nhom;
        }
    }

    return $groups;
}

?>