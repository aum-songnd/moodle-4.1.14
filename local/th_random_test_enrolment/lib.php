<?php

defined('MOODLE_INTERNAL') || die();

/**
 * This function extends the course navigation with the th_random_test_enrolment item
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */

/**
 * [local_th_random_test_enrolment_extend_navigation_course description]
 * @param  [type] $navigation [description]
 * @param  [type] $course     [description]
 * @param  [type] $context    [description]
 * @return [type]             [description]
 */
function local_th_random_test_enrolment_extend_navigation_course($navigation, $course, $context) {
	if (has_capability('local/th_random_test_enrolment:enrolusers', $context)) {
		$url = new moodle_url('/local/th_random_test_enrolment/index.php', array('id' => $course->id));
		$th_random_test_enrolmentnode = navigation_node::create(get_string('pluginname', 'local_th_random_test_enrolment'), $url,
			navigation_node::TYPE_SETTING, null, 'local_th_random_test_enrolment', new pix_icon('i/users', ''));
		$usersnode = $navigation->get('users');

		if (isset($th_random_test_enrolmentnode) && !empty($usersnode)) {
			$usersnode->add_node($th_random_test_enrolmentnode);
		}
	}
}
