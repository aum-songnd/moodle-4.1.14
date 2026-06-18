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
 *
 * @package   theme_lambda
 * @copyright 2022 redPIthemes
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/quiz/renderer.php';

class theme_th_lambda_mod_quiz_renderer extends mod_quiz_renderer {

	public function attempt_form($attemptobj, $page, $slots, $id, $nextpage) {
		$output = '';

		$output .= html_writer::start_tag('form', [
			'action' => new moodle_url($attemptobj->processattempt_url(), ['cmid' => $attemptobj->get_cmid()]),
			'method' => 'post',
			'enctype' => 'multipart/form-data',
			'accept-charset' => 'utf-8',
			'id' => 'responseform'
		]);
		$output .= html_writer::start_tag('div');

		$firstQ = '';
		$remainQ = '';
		$ques = 0;
		foreach ($slots as $slot) {
			$ques++;
			$rendered = $attemptobj->render_question($slot, false, $this, $attemptobj->attempt_url($slot, $page), $this);
			if ($ques == 1) {
				$firstQ = $rendered;
			} else {
				$remainQ .= $rendered;
			}
		}

		$DOM = new DOMDocument;
		libxml_use_internal_errors(true);
		$DOM->loadHTML($firstQ);
		libxml_use_internal_errors(false);
		$item = $DOM->getElementById('th_mycustomwidth');

		if ($item) {
			$width = (int) $item->getAttribute("value");
			$remain_width = 12 - $width;
		
			if (strpos($firstQ, 'thvstepcluster') !== false) {
				$grid = "<div class='row-fluid'>
					<div class='span12 overflow-auto'>$firstQ</div>
				</div>";
			} else {
				$grid = "<div class='row-fluid'>
					<div class='span$width overflow-auto' style='height: 90vh;'>$firstQ</div>
					<div class='span$remain_width overflow-auto' style='height: 90vh;'>$remainQ</div>
				</div>";
			}
			$output .= $grid;
		
			$output .= '<style>
				.que.thvstepcluster {
					display: grid;
					grid-template-columns: ' . $width . 'fr ' . $remain_width . 'fr;
					gap: 1rem;
					overflow: hidden;
				}
		
				.que.thvstepcluster > .description {
					grid-column: 1;
					max-height: 100%;
					margin: 0 !important;
					position: sticky;
					top: 0;
					align-self: start;
				}
		
				.que.thvstepcluster > .que:not(.description) {
					grid-column: 2;
					overflow-y: auto;
					max-height: 100%;
				}
		
				.que.thvstepcluster > .content {
					height: 70vh;
					overflow: auto;
				}
			</style>';
		}
		else {
			$output .= $firstQ . $remainQ;
		}

		$navmethod = $attemptobj->get_quiz()->navmethod;
		$output .= $this->attempt_navigation_buttons($page, $attemptobj->is_last_page($page), $navmethod);

		// Hidden inputs
		$output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'attempt', 'value' => $attemptobj->get_attemptid()]);
		$output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'thispage', 'value' => $page, 'id' => 'followingpage']);
		$output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'nextpage', 'value' => $nextpage]);
		$output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'timeup', 'value' => '0', 'id' => 'timeup']);
		$output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
		$output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'scrollpos', 'value' => '', 'id' => 'scrollpos']);
		$output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'slots', 'value' => implode(',', $attemptobj->get_active_slots($page))]);

		$output .= html_writer::end_tag('div');
		$output .= html_writer::end_tag('form');
		$output .= $this->connection_warning();

		return $output;
	}

}
?>
