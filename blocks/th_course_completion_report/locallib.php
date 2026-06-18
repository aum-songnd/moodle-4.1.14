<?php

function th_course_completion_report_get_courseid_category_form($mform) {
	global $DB;
	$courses = th_course_completion_report_get_courseid_category();

	$choice = array();
	foreach ($courses as $key => $course) {

		$choice[$key] = $course->fullname . ',' . $course->shortname;
	}

	$options = array(
		'multiple' => true,
		'noselectionstring' => get_string('no_selection', 'block_th_course_completion_report'),
	);
	$mform->addElement('autocomplete', 'courseidarr', get_string('course', 'block_th_course_completion_report'), $choice, $options);
	return $courses;
}

function th_course_completion_report_get_userid_form($mform, $sortorder = null, $required = false) {
	global $DB, $COURSE, $CFG;

	$extra = array_filter(explode(',', $CFG->showuseridentity));
	$userfields = array_values($extra);

	$usernamefield = get_all_user_name_fields();
	$usernamefield = implode(",", $usernamefield);
	$alluserfields = "id," . $usernamefield;

	if (count($userfields) > 0) {
		$alluserfields .= "," . implode(',', $userfields);
	}

	$alluserfields .= "," . "email";

	$users = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0), $sortorder, $alluserfields);
	$choice = array();
	$choice[''] = '';

	foreach ($users as $key => $value) {
		$fullname = fullname($value);
		$users[$key]->fullname = $fullname;
		$fullname = html_writer::tag("span", $fullname);

		$extraf = array();
		foreach ($userfields as $key => $uf) {
			$v = $value->$uf;
			if (!is_null($v) && $v !== '') {
				$extraf[] = $v;
			}
		}
		$extraf = implode(", ", $extraf);
		$extraf = html_writer::tag("small", $extraf);
		$extraf = html_writer::tag("span", $extraf);

		$val = html_writer::tag("span", $fullname . ", " . $extraf);

		$choice[$value->id] = $val;
	}
	$options = array(
		'multiple' => true,
		'noselectionstring' => get_string('no_selection', 'block_th_course_completion_report'),
	);

	$element = $mform->addElement('autocomplete', 'userid', get_string('user', 'block_th_course_completion_report'), $choice, $options);
	if ($required) {
		$attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
		$element->setAttributes($attributes);
	}
	return $users;
}

/**
 * [th_course_completion_report_get_courseid_category description] Lấy các khóa học trong các danh mục trong setting
 * @return [array] [list course]
 */
function th_course_completion_report_get_courseid_category() {
	global $DB;
	$config = get_config('block_th_course_completion_report');
	$idnumbercategory = trim($config->idnumbercategory);
	// print_object($idnumbercategory);
	if (strpos($idnumbercategory, ',')) {
		$idnumber_arr = array_map('trim', explode(",", $idnumbercategory));
		$idnumber_arr = array_filter($idnumber_arr);
		$idnumber_arr = array_unique($idnumber_arr);

		[$insql, $inparams] = $DB->get_in_or_equal($idnumber_arr);
		$sql = "SELECT c.*
			     FROM {course} c
			     JOIN {course_categories} cc ON c.category = cc.id
			     WHERE cc.idnumber {$insql}
			     AND c.visible = 1 AND cc.visible = 1 AND c.id <> 1
			     ORDER BY c.startdate DESC;";
		$courses = $DB->get_records_sql($sql, $inparams);

	} else {

		if (!empty($idnumbercategory)) {
			// print_object('b');
			$sql = "SELECT c.*
			     from {course} c
			     join {course_categories} cc on c.category = cc.id
			     WHERE cc.parent = (SELECT id FROM {course_categories} WHERE idnumber = :idnumber2)
			     AND c.visible = :cvisible and cc.visible = :ccvisible and c.id <> 1
			     ORDER BY c.startdate DESC;";

		} else {
			$sql = "SELECT c.*
			     from {course} c
			     join {course_categories} cc on c.category = cc.id
			     WHERE c.visible = :cvisible and cc.visible = :ccvisible and c.id <> 1
			     ORDER BY c.startdate DESC;";
		}

		$params = array(
			'idnumber1' => $idnumbercategory,
			'idnumber2' => $idnumbercategory,
			'cvisible' => 1,
			'ccvisible' => 1,
		);
		$courses = $DB->get_records_sql($sql, $params);
	}

	return $courses;
}

/**
 * [th_course_completion_report_get_userid lấy tất cả các Tài khoản trên hệ thống]
 * @return [array] [list user]
 */
function th_course_completion_report_get_userid() {
	global $DB;

	$users = $DB->get_records('user', ['suspended' => 0, 'deleted' => 0], '', 'id');
	$choice = array();

	foreach ($users as $key => $value) {

		$choice[$value->id] = $value->id;
	}
	return $choice;
}
