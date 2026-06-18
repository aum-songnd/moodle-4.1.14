<?php

defined('MOODLE_INTERNAL') || die();

class quiz_thessaygrader_renderer extends mod_quiz_renderer {

    public function review_page(quiz_attempt $attemptobj, $slots, $page, $showall,
		$lastpage, mod_quiz_display_options $displayoptions,
		$summarydata) {
		global $CFG, $COURSE;
		$output = '';
		$output .= $this->header();
		$output .= $this->review_summary_table($summarydata, $page);
		$output .= $this->review_form($page, $showall, $displayoptions,
			$this->questions($attemptobj, true, $slots, $page, $showall, $displayoptions),
			$attemptobj);

		// $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);

		$action = optional_param('action', '', PARAM_TEXT);
		$status = optional_param('status', '', PARAM_TEXT);
		$cmid = $attemptobj->get_cmid();
		if($action == 'regrade'){
			$output .= "<a href='$CFG->wwwroot/local/th_online_exam_manager/regrade_assignments_list.php?status=$status'>Quay lại</a>";
		} else if ($action == 'grade') {
			$output .= "<a href='$CFG->wwwroot//mod/quiz/report.php?id=$cmid&mode=thessaygrader'>Quay lại</a>";
		}

		$output .= $this->footer();

        $output = str_replace(
            '/pluginfile.php/', 
            '/mod/quiz/report/thessaygrader/pluginfile.php/', 
            $output
        );

		return $output;
	}

	public function navigation_panel(quiz_nav_panel_base $panel) {

		$output = '';
		$userpicture = $panel->user_picture();
		if ($userpicture) {
			$fullname = fullname($userpicture->user);
			if ($userpicture->size === true) {
				$fullname = html_writer::div($fullname);
			}
			$output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
				array('id' => 'user-picture', 'class' => 'clearfix'));
		}
		$output .= $panel->render_before_button_bits($this);

		$bcc = $panel->get_button_container_class();
		$output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
		foreach ($panel->get_question_buttons() as $button) {
			$output .= $this->render($button);
		}
		$output .= html_writer::end_tag('div');

		// $output .= html_writer::tag('div', $panel->render_end_bits($this),
		// 	array('class' => 'othernav'));

		$this->page->requires->js_init_call('M.mod_quiz.nav.init', null, false,
			quiz_get_js_module());

		return $output;
	}
}