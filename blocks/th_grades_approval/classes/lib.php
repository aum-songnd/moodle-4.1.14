<?php

function send_mail_grades_approval(){
	global $DB;

	$send_mail_grades_approval = get_config('block_th_grades_approval', 'send_mail_grades_approval');

	$now = time();
	$one_day = 24 * 60 * 60;

	$daythreshold1 = $one_day * $send_mail_grades_approval;
	$daythreshold2 = $one_day * ($send_mail_grades_approval + 1);

	$list_course_approval = $DB->get_records_sql("SELECT {course}.id, {course}.fullname, {block_th_grades_approval}.ishidden , {block_th_grades_approval}.teachingid
	FROM {course}
	LEFT JOIN {block_th_grades_approval} ON {course}.id = {block_th_grades_approval}.course
	WHERE ($now - {course}.startdate) > $daythreshold1 AND ($now - {course}.startdate) < $daythreshold2
	GROUP BY {course}.id;");

	foreach($list_course_approval as $course_approval){
		$course_id = $course_approval->id;
	    $ishidden = $course_approval->ishidden;

	    $courses = $DB->get_record('course', array('id' => $course_id));

		$page1 = new \moodle_page();
		$page1->set_url('/course/view.php', array('id' => $courses->id));
		$page1->set_pagelayout('course');
		$page1->set_course($courses);

		$blockmanager = $page1->blocks;
		$blockmanager->load_blocks(true);
		$is_block_grades_approval = false;
		foreach ($blockmanager->get_regions() as $region) {
		    foreach ($blockmanager->get_blocks_for_region($region) as $block) {
		        $instance = $block->instance;

		        if ('th_grades_approval' == $instance->blockname){
		            $is_block_grades_approval = true;
		            break;
		        }
		    }
		}

		if($is_block_grades_approval){

			$email_subject = get_config('block_th_grades_approval', 'email_subject');
			$email_content = get_config('block_th_grades_approval', 'email_content');

			// Lấy ngày hiện tại
			$today = strtotime('today');

			// Tìm ngày sau 4 ngày
			$afterFourDays = strtotime('+4 days', $today);

			// Định dạng ngày sau 4 ngày
			$afterFourDaysFormatted = date('d-m-Y', $afterFourDays);

			$body = "Chào anh/chị,
					Anh chị cần phê duyệt bảng điểm môn học $courses->fullname trước ngày $afterFourDaysFormatted.
					$email_content";
			$supportuser = \core_user::get_support_user();
			$subject = $email_subject;

			if(isset($ishidden)){

		        $status = $DB->get_record_sql("SELECT * FROM {block_th_grades_approval} WHERE course = $course_id AND timecreated = (SELECT MAX(timecreated) FROM {block_th_grades_approval} WHERE course = $course_id)");

		        $user = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$status->teachingid");
		    	
		    	if($status->ishidden != 1){
					email_to_user($user, $supportuser, $subject, $body);
		    	}
		    } else {
		    	$roleid = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname = 'editingteacher'");
				$ds_giang_vien = $DB->get_records_sql("SELECT u.* FROM {enrol} as e, {user_enrolments} as ue, {user} as u, {context} as c, {role_assignments} as ra WHERE ue.status = 0 AND e.courseid = '$course_id' AND e.enrol = 'manual' AND e.id = ue.enrolid AND u.id = ue.userid AND c.instanceid = '$course_id' AND c.contextlevel = '50' AND c.id = ra.contextid AND ra.userid = u.id AND ra.roleid = '$roleid'");

				foreach($ds_giang_vien as $giang_vien){
					email_to_user($giang_vien, $supportuser, $subject, $body);
				}
		    }
		}
	}
}
?>