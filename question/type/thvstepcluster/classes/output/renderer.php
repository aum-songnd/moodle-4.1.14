<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The thvstepcluster question renderer class is defined here.
 *
 * @package     qtype_thvstepcluster
 * @copyright   phamleminh1812@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
use qtype_recordrtc\output\audio_playback;
use qtype_recordrtc\output\audio_recorder;
use qtype_recordrtc\output\screen_playback;
use qtype_recordrtc\output\screen_recorder;
use qtype_recordrtc\output\video_playback;
use qtype_recordrtc\output\video_recorder;
/**
 * Generates the output for thvstepcluster questions.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/rendererbase.php.
 */
class qtype_thvstepcluster_renderer extends qtype_renderer {

	/**
	 * Generates the display of the formulation part of the question. This is the
	 * area that contains the quetsion text, and the controls for students to
	 * input their answers. Some question types also embed bits of feedback, for
	 * example ticks and crosses, in this area.
	 *
	 * @param question_attempt $qa the question attempt to display.
	 * @param question_display_options $options controls what should and should not be displayed.
	 * @return string HTML fragment.
	 */
	public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

		global $USER, $DB,$CFG, $PAGE, $COURSE;
		
		$question_original = $qa->get_question();
		$question_items = $question_original->question_items;
		$usageid = $qa->get_usage_id();
		$slot = $qa->get_slot();

		$inputname = $qa->get_qt_field_name('answer');
		$html = '';

		$question_items_instance = $question_original->question_items_instance;
		$i = 0;
		$result = '';
		foreach ($question_items_instance as $key => $value) {
			$inputname1 = "$inputname$key";
			$variable_name = "answer$key";
			$user_response = $qa->get_last_qt_var("answer$key", '');
			$question = $value;
			$contextid = $question->contextid;
			$itemid = $question->id;
			 
			if ($question->get_type_name() == "description") {
				$data = [];
				$originalString = $question->format_questiontext($qa);
				$search = "/question/questiontext/";
				$replace = "/qtype_thvstepcluster/questiontext/from_question/";
				$newString = str_replace($search, $replace, $originalString);

				$search = "/question/answerfeedback/";
				$replace = "/qtype_thvstepcluster/answerfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);

				$search = "/question/generalfeedback/";
				$replace = "/qtype_thvstepcluster/generalfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);
				$data["questiontext"] = $newString;
				
				$htmla = $this->render_from_template('qtype_thvstepcluster/description', $data);
				$html .= $htmla;

			} elseif ($question->get_type_name() == "multichoice") {
				$data = [];

				$questiontext = $question->questiontext;
				$originalString = $question->format_questiontext($qa);
				$search = "/question/questiontext/";
				$replace = "/qtype_thvstepcluster/questiontext/from_question/";  
				$newString = str_replace($search, $replace, $originalString);
				$data["questiontext"] = $newString;
				// $data['questiontext'] = $question->format_questiontext($qa);
				$data['inputname'] = $inputname1;
				$data['answers'] = [];
				$answernumbers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$answervalues = '012345678910111213141516171819202122232425';

				$correct_response = $question->get_correct_response();
				$correct_response = isset($correct_response["answer"]) ? $correct_response["answer"] : '';
				$answers = $question->answers;
	            $data['showfeedback'] = false;
				foreach ($question->get_order($qa) as $value => $ansid) {
					$data['answers'][] = $question->answers[$ansid];
				}
				foreach ($data['answers'] as $key2 => $answer) {
					$answer->answernumber = $answernumbers[$key2];
					$answer->answervalue = $answervalues[$key2];
	
					$answer->answer = question_rewrite_question_urls($answer->answer, 'pluginfile.php', $contextid, 'question', 'answer', [$usageid, $slot], $answer->id);
					
					if ($answer->answervalue == $user_response) {
						$answer->checked = true;
						if ($options->correctness) {
							if ($user_response == $correct_response) {
								$answer->correct = true;
								$answer->iconcorrect = html_writer::span($this->feedback_image($this->is_right($answer)), 'ml-1');
							} else {
								$answer->incorrect = true;
								$answer->iconincorrect = html_writer::span($this->feedback_image($this->is_right($answer)), 'ml-1');
							}
						}
						if($answer->checked) {
							$data['checked_radio'] = true;
						}else {
							$data['checked_radio'] = false;
						}
					} else {
						$answer->checked = false;	
					}
				}
				$data['answer'] = isset($data['answers'][$correct_response]->answer) ? $data['answers'][$correct_response]->answer : '';
				foreach ($data['answers'] as $key2 => $answer) {
					$answer->answernumber = $answernumbers[$key2];
					$answer->answervalue = $answervalues[$key2];
					if ($answer->answervalue == $user_response) {
						$answer->checked = true;
						if ($options->correctness) {
							if ($user_response == $correct_response) {
								$answer->correct = true;
								if ($options->feedback) {
			                    	$specificfeedback = html_writer::tag('div', $question->correctfeedback, array('class' => 'specificfeedback'));
					            } else {
					                $specificfeedback = '';
					            }
					            if ($options->generalfeedback) {
				                    $text = question_rewrite_question_urls($question->generalfeedback, 'pluginfile.php', $contextid, 'question', 'generalfeedback', [$usageid, $slot], $itemid);
					                $generalfeedback = html_writer::tag('div', $text, array('class' => 'generalfeedback'));
					            } else {
					                $generalfeedback = '';
					            }
						        break;
							} else {
								$answer->incorrect = true;
							}
							if($answer->checked) {
								$data['checked_radio'] = true;
							}else {
								$data['checked_radio'] = false;
							}
						}
					} else {
						$answer->checked = false;
						if ($options->feedback) {
		                    $specificfeedback = html_writer::tag('div', $question->incorrectfeedback, array('class' => 'specificfeedback'));
			            } else {
			                $specificfeedback = '';
			            }
			            if ($options->generalfeedback) {
		                    $text = question_rewrite_question_urls($question->generalfeedback, 'pluginfile.php', $contextid, 'question', 'generalfeedback', [$usageid, $slot], $itemid);
			                $generalfeedback = html_writer::tag('div', $text, array('class' => 'generalfeedback'));
			            } else {
			                $generalfeedback = '';
			            }
					}
				}

				$clearchoicefieldname = $data['inputname'].'_clearchoice';
				$qid = $qa->get_outer_question_div_unique_id() .'-' . $i;
			
				$data['clearchoicefieldname'] = $clearchoicefieldname;
				$data['qid'] = $qid;
				$data['correctness'] = $options->correctness;
				$data['rightanswer'] = $options->rightanswer;
				if ($data['feedback'] = $options->feedback) {
					$data['feedbacktext'] = $specificfeedback;
				}
				if ($data['generalfeedback'] = $options->generalfeedback) {
					$data['generalfeedbacktext'] = $generalfeedback;
					$search = "/question/generalfeedback/";
					$replace = "/qtype_thvstepcluster/generalfeedback/from_question/";
					$data['generalfeedbacktext'] = str_replace($search, $replace, $data['generalfeedbacktext']);
				}
				$search = "/question/answer/";
				$replace = "/qtype_thvstepcluster/answer/from_question/";
				foreach ($data['answers'] as $key => $value) {
					$value->answer = str_replace($search, $replace, $value->answer);
				}
				
				$search = "/question/answer/";
				$replace = "/qtype_thvstepcluster/answer/from_question/"; 
				$data = $this->recursive_str_replace($search, $replace, $data);
				$htmla = $this->render_from_template('qtype_thvstepcluster/multichoice', $data);
				$html .= $htmla;
				$search = "/question/answerfeedback/";
				$replace = "/qtype_thvstepcluster/answerfeedback/from_question/"; 
				$html = str_replace($search, $replace, $html);

				$this->page->requires->js_call_amd('qtype_multichoice/clearchoice', 'init', [$qa->get_outer_question_div_unique_id() . '-' . $i, $clearchoicefieldname]);
				$this->page->requires->js_call_amd('qtype_multichoice/answers', 'init', [$qa->get_outer_question_div_unique_id() . '-' . $i]);
	            $i++;
			} elseif ($question->get_type_name() == "essay") {
				$originalString = $this->formulation_and_controls_essay($qa, $options, $question, $variable_name);
				$search = "/question/questiontext/";
				$replace = "/qtype_thvstepcluster/questiontext/from_question/";
				$newString = str_replace($search, $replace, $originalString);

				$search = "/question/answerfeedback/";
				$replace = "/qtype_thvstepcluster/answerfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);	

				$search = "/question/generalfeedback/";
				$replace = "/qtype_thvstepcluster/generalfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);
				$html .= html_writer::div($newString,"que essay manualgraded notyetanswered");
			} else if ($question->get_type_name() == "recordrtc") {
				$originalString = $this->formulation_and_controls_recordrtc($qa, $options, $question,$variable_name);
				$search = "/question/questiontext/";
				$replace = "/qtype_thvstepcluster/questiontext/from_question/";
				$newString = str_replace($search, $replace, $originalString);

				$search = "/question/answerfeedback/";
				$replace = "/qtype_thvstepcluster/answerfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);	

				$search = "/question/generalfeedback/";
				$replace = "/qtype_thvstepcluster/generalfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);
				$html .= html_writer::div($newString,"que recordrtc manualgraded notyetanswered");
			} elseif ($question->get_type_name() == "truefalse") {
				$originalString = $this->formulation_and_controls_truefalse($qa, $options, $question, $variable_name, $inputname1, $user_response);
				$search = "/question/questiontext/";
				$replace = "/qtype_thvstepcluster/questiontext/from_question/";
				$newString = str_replace($search, $replace, $originalString);

				$search = "/question/answerfeedback/";
				$replace = "/qtype_thvstepcluster/answerfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);	

				$search = "/question/generalfeedback/";
				$replace = "/qtype_thvstepcluster/generalfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);
				$html .= html_writer::div($newString,"que truefalse manualgraded notyetanswered");
			} else if ($question->get_type_name() == "shortanswer") {
				$originalString = $this->formulation_and_controls_shortanswer($qa, $options, $question, $variable_name, $inputname1, $user_response);
	
				$search = "/question/questiontext/";
				$replace = "/qtype_thvstepcluster/questiontext/from_question/";
				$newString = str_replace($search, $replace, $originalString);

				$search = "/question/answerfeedback/";
				$replace = "/qtype_thvstepcluster/answerfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);	

				$search = "/question/generalfeedback/";
				$replace = "/qtype_thvstepcluster/generalfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);
				$html .= html_writer::div($newString,"que shortanswer manualgraded notyetanswered");
			} else if ($question->get_type_name() == "multianswer") {
				$inputname1 = "$inputname$key";
				$originalString = $this->formulation_and_controls_multianswer($qa, $options, $question, $variable_name, $inputname1, $key ,$user_response);
				$search = "/question/questiontext/";
				$replace = "/qtype_thvstepcluster/questiontext/from_question/";
				$newString = str_replace($search, $replace, $originalString);

				$search = "/question/answerfeedback/";
				$replace = "/qtype_thvstepcluster/answerfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);	

				$search = "/question/generalfeedback/";
				$replace = "/qtype_thvstepcluster/generalfeedback/from_question/"; 
				$newString = str_replace($search, $replace, $newString);
				$html .= html_writer::div($newString,"que multianswer deferredfeedback notyetanswered");
			}

		}


		if ($qa->get_state() == question_state::$invalid) {

			$html .= html_writer::nonempty_tag('div',
				$question_original->get_validation_error($qa->get_last_qt_data()),
				array('class' => 'validationerror'));
		}

		return $html;
	}

	public function formulation_and_controls_multianswer(question_attempt $qa,
            question_display_options $options, $question, $variable_name, $inputname, $key, $user_response) {

        // $question = $qa->get_question();

        $output = '';
        $subquestions = array();

        $missingsubquestions = false;
        foreach ($question->textfragments as $i => $fragment) {
            if ($i > 0) {
                $index = $question->places[$i];

                $questionisvalid = !empty($question->subquestions[$index]) &&
                                 $question->subquestions[$index]->qtype->name() !== 'subquestion_replacement';

                if (!$questionisvalid) {
                    $missingsubquestions = true;
                    $questionreplacement = qtype_multianswer::deleted_subquestion_replacement();

                    // It is possible that the subquestion index does not exist. When corrupted quizzes (see MDL-54724) are
                    // restored, the sequence column of mdl_quiz_multianswer can be empty, in this case
                    // qtype_multianswer::get_question_options cannot fill in deleted questions, so we need to do it here.
                    $question->subquestions[$index] = $question->subquestions[$index] ?? $questionreplacement;
                }

                $token = 'qtypemultianswer' . $i . 'marker';
                $token = '<span class="nolink">' . $token . '</span>';
                $output .= $token;
                $subquestions[$token] = $this->subquestion($qa, $options, $index.$key,
                        $question->subquestions[$index]);
            }

            $output .= $fragment;
        }

        if ($missingsubquestions) {
            $output = $this->notification(get_string('corruptedquestion', 'qtype_multianswer'), 'error') . $output;
        }

        $output = $question->format_text($output, $question->questiontextformat,
                $qa, 'question', 'questiontext', $question->id);
        $output = str_replace(array_keys($subquestions), array_values($subquestions), $output);

        if ($qa->get_state() == question_state::$invalid) {
            $output .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($qa->get_last_qt_data()),
                    array('class' => 'validationerror'));
        }

        $feedback = '';

        if ($options->generalfeedback) {
            $feedback .= html_writer::nonempty_tag('div', $question->generalfeedback,
                    array('class' => 'generalfeedback'));
            $feedback =  $question->format_generalfeedback($qa);

	        $feedback = html_writer::nonempty_tag('div',
	                $feedback, array('class' => 'outcome clearfix'));
        }


        return $output . $feedback;
    }

    public function subquestion(question_attempt $qa,
            question_display_options $options, $index, question_automatically_gradable $subq) {

        $subtype = $subq->qtype->name();
        if ($subtype == 'numerical' || $subtype == 'shortanswer') {
            $subrenderer = 'textfield';
        } else if ($subtype == 'multichoice') {
            if ($subq instanceof qtype_multichoice_multi_question) {
                if ($subq->layout == qtype_multichoice_base::LAYOUT_VERTICAL) {
                    $subrenderer = 'multiresponse_vertical';
                } else {
                    $subrenderer = 'multiresponse_horizontal';
                }
            } else {
                if ($subq->layout == qtype_multichoice_base::LAYOUT_DROPDOWN) {
                    $subrenderer = 'multichoice_inline';
                } else if ($subq->layout == qtype_multichoice_base::LAYOUT_HORIZONTAL) {
                    $subrenderer = 'multichoice_horizontal';
                } else {
                    $subrenderer = 'multichoice_vertical';
                }
            }
        } else if ($subtype == 'subquestion_replacement') {
            return html_writer::div(
                get_string('missingsubquestion', 'qtype_multianswer'),
                'notifyproblem'
            );
        } else {
            throw new coding_exception('Unexpected subquestion type.', $subq);
        }
        /** @var qtype_multianswer_subq_renderer_base $renderer */
        $renderer = $this->page->get_renderer('qtype_multianswer', $subrenderer);
        return $renderer->subquestion($qa, $options, $index, $subq);
    }


	public function formulation_and_controls_shortanswer(question_attempt $qa,
            question_display_options $options, $question, $variable_name, $inputname, $user_response) {

        // $question = $qa->get_question();
        $currentanswer = $qa->get_last_qt_var($variable_name, '');

        $inputattributes = array(
            'type' => 'text',
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => 80,
            'class' => 'form-control d-inline',
        );

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
        }

        $feedbackimg = '';
        if ($options->correctness) {
            $answer = $question->get_matching_answer(array('answer' => $currentanswer));
            if ($answer) {
                $fraction = $answer->fraction;
            } else {
                $fraction = 0;
            }
            $inputattributes['class'] .= ' ' . $this->feedback_class($fraction);
            $feedbackimg = $this->feedback_image($fraction);
        }

        $questiontext = $question->format_questiontext($qa);
        $placeholder = false;
        if (preg_match('/_____+/', $questiontext, $matches)) {
            $placeholder = $matches[0];
            $inputattributes['size'] = round(strlen($placeholder) * 1.1);
        }
        $input = html_writer::empty_tag('input', $inputattributes) . $feedbackimg;

        if ($placeholder) {
            $inputinplace = html_writer::tag('label', $options->add_question_identifier_to_label(get_string('answer')),
                    array('for' => $inputattributes['id'], 'class' => 'sr-only'));
            $inputinplace .= $input;
            $questiontext = substr_replace($questiontext, $inputinplace,
                    strpos($questiontext, $placeholder), strlen($placeholder));
        }

        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        if (!$placeholder) {
            $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
            $answerspan = html_writer::tag('span', $input, array('class' => 'answer'));
            $label = $options->add_question_identifier_to_label(get_string('answercolon', 'qtype_numerical'), true);
            $result .= html_writer::tag('label', $label . $answerspan,
                    array('for' => $inputattributes['id']));
            $result .= html_writer::end_tag('div');
        }

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error(array('answer' => $currentanswer)),
                    array('class' => 'validationerror'));
        }

        $output = '';

        if ($options->feedback) {
        	$answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var($variable_name)));
	        if (!$answer || !$answer->feedback) {
	            $specific_feedback = '';
	        } else {
	        	$specific_feedback = $question->format_text($answer->feedback, $answer->feedbackformat,
                $qa, 'question', 'answerfeedback', $answer->id);
	        }

            $output .= html_writer::nonempty_tag('div', $specific_feedback,
                    array('class' => 'specificfeedback'));
        }

        if ($options->generalfeedback) {
            $generalfeedback = $question->format_text($question->generalfeedback,
            		$question->generalfeedbackformat,
                $qa, 'question', 'generalfeedback', $question->id);
            $output .= html_writer::nonempty_tag('div', $generalfeedback,
                    array('class' => 'generalfeedback'));
        }

        if ($options->rightanswer) {

        	$answer = $question->get_matching_answer($question->get_correct_response());
	        if (!$answer) {
	            $output .= '';
	        } else {
	        	$output .= get_string('correctansweris', 'qtype_shortanswer',
	                s($question->clean_response($answer->answer)));
	        }
        }

        $output = html_writer::nonempty_tag('div',
                $output, array('class' => 'outcome clearfix'));

        return $result . $output;
    }

	public function formulation_and_controls_truefalse(question_attempt $qa,
            question_display_options $options, $question, $variable_name, $inputname, $user_response) {

        // $question = $qa->get_question();
        $response = $qa->get_last_qt_var($variable_name, '');
        // print_object($response);
       
        $trueattributes = array(
            'type' => 'radio',
            'name' => $inputname,
            'value' => 1,
            'id' => $inputname . 'true',
        );
        $falseattributes = array(
            'type' => 'radio',
            'name' => $inputname,
            'value' => 0,
            'id' => $inputname . 'false',
        );

        if ($options->readonly) {
            $trueattributes['disabled'] = 'disabled';
            $falseattributes['disabled'] = 'disabled';
        }

        // Work out which radio button to select (if any).
        $truechecked = false;
        $falsechecked = false;
        $responsearray = array();
        if ($response) {
            $trueattributes['checked'] = 'checked';
            $truechecked = true;
            $responsearray = array('answer' => 1);
        } else if ($response !== '') {
            $falseattributes['checked'] = 'checked';
            $falsechecked = true;
            $responsearray = array('answer' => 1);
        }

        // Work out visual feedback for answer correctness.
        $trueclass = '';
        $falseclass = '';
        $truefeedbackimg = '';
        $falsefeedbackimg = '';

        if ($options->correctness) {
            if ($truechecked) {
                $trueclass = ' ' . $this->feedback_class((int) $question->rightanswer);
                $truefeedbackimg = $this->feedback_image((int) $question->rightanswer);
            } else if ($falsechecked) {
                $falseclass = ' ' . $this->feedback_class((int) (!$question->rightanswer));
                $falsefeedbackimg = $this->feedback_image((int) (!$question->rightanswer));
            }
        }

        $radiotrue = html_writer::empty_tag('input', $trueattributes) .
                html_writer::tag('label', get_string('true', 'qtype_truefalse'),
                array('for' => $trueattributes['id'], 'class' => 'ml-1'));
        $radiofalse = html_writer::empty_tag('input', $falseattributes) .
                html_writer::tag('label', get_string('false', 'qtype_truefalse'),
                array('for' => $falseattributes['id'], 'class' => 'ml-1'));

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('fieldset', array('class' => 'ablock'));
        if (!empty($question->showstandardinstruction)) {
            $legendclass = '';
            $questionnumber = $options->add_question_identifier_to_label(get_string('selectone', 'qtype_truefalse'), true, true);
        } else {
            $legendclass = 'sr-only';
            $questionnumber = $options->add_question_identifier_to_label(get_string('answer'), true, true);
        }
        $result .= html_writer::tag('legend', $questionnumber,
            array('class' => 'prompt h6 font-weight-normal ' . $legendclass));
        $result .= html_writer::start_tag('div', array('class' => 'answer'));
        $result .= html_writer::tag('div', $radiotrue . ' ' . $truefeedbackimg,
                array('class' => 'r0' . $trueclass));
        $result .= html_writer::tag('div', $radiofalse . ' ' . $falsefeedbackimg,
                array('class' => 'r1' . $falseclass));
        $result .= html_writer::end_tag('div'); // Answer.

        $result .= html_writer::end_tag('fieldset'); // Ablock.

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($responsearray),
                    array('class' => 'validationerror'));
        }

        $output = '';

        if ($options->feedback) {
            if ($response) {
            $output .= $question->format_text($question->truefeedback, $question->truefeedbackformat,
                    $qa, 'question', 'answerfeedback', $question->trueanswerid);
	        } else if ($response !== '') {
	            $output .= $question->format_text($question->falsefeedback, $question->falsefeedbackformat,
	                    $qa, 'question', 'answerfeedback', $question->falseanswerid);
	        }
        }

        if ($options->generalfeedback) {
        	$generalfeedback = $question->format_text($question->generalfeedback,
            		$question->generalfeedbackformat,
                $qa, 'question', 'generalfeedback', $question->id);
            $output .= html_writer::nonempty_tag('div', $generalfeedback,
                    array('class' => 'generalfeedback'));
        }

        if ($options->rightanswer) {

        	if ($question->rightanswer) {
	            $output .= get_string('correctanswertrue', 'qtype_truefalse');
	        } else {
	            $output .= get_string('correctanswerfalse', 'qtype_truefalse');
	        }

        }

        $output = html_writer::nonempty_tag('div',
                $output, array('class' => 'outcome clearfix'));

        return $result . $output;
    }
    
    //function for render essay

	public function formulation_and_controls_essay(question_attempt $qa,
		question_display_options $options, $question, $inputname) {

		global $CFG;


		/** @var qtype_essay_format_renderer_base $responseoutput */
		$responseoutput = $question->get_format_renderer($this->page);
		$responseoutput->set_displayoptions($options);
		// Answer field.

		$step = $qa->get_last_step_with_qt_var($inputname);
	
		$usageid = $qa->get_usage_id();
		$slot = $qa->get_slot();
		$contextid = $question->contextid;
		$itemid = $question->id;

		if (!$step->has_qt_var("$inputname") && empty($options->readonly)) {
			// Question has never been answered, fill it with response template.
			$step = new question_attempt_step(array("$inputname" => $question->responsetemplate));
		}

		if (empty($options->readonly)) {
			$answer = $responseoutput->response_area_input($inputname, $qa,
				$step, $question->responsefieldlines, $options->context);

		} else {
			$answer = $responseoutput->response_area_read_only($inputname, $qa,
				$step, $question->responsefieldlines, $options->context);

			$answer .= html_writer::nonempty_tag('p', $qa->get_question()->get_word_count_message_for_review($step->get_qt_data(), $inputname, $question->maxwordlimit, $question->minwordlimit));

			if (!empty($CFG->enableplagiarism)) {
				require_once $CFG->libdir . '/plagiarismlib.php';

				$answer .= plagiarism_get_links([
					'context' => $options->context->id,
					'component' => $qa->get_question()->qtype->plugin_name(),
					'area' => $qa->get_usage_id(),
					'itemid' => $qa->get_slot(),
					'userid' => $step->get_user_id(),
					'content' => $qa->get_response_summary()]);
			}
		}

		$files = '';
		if ($question->attachments) {
			if (empty($options->readonly)) {
				$files = $this->files_input($qa, $question->attachments, $options);
			} else {
				$files = $this->files_read_only($qa, $options);
			}
		}

		$result = '';
		$result .= html_writer::tag('div', $question->format_questiontext($qa),
			array('class' => 'qtext'));

		$result .= html_writer::start_tag('div', array('class' => 'ablock'));
		$result .= html_writer::tag('div', $answer, array('class' => 'answer'));
		// if ($options->generalfeedback && !empty($generalfeedback)) {
		// 	 $text = question_rewrite_question_urls($question->generalfeedback, 'pluginfile.php', $contextid, 'question', 'generalfeedback', [$usageid, $slot], $itemid);
        //     $generalfeedback = html_writer::tag('div', $text, array('class' => 'generalfeedback'));
		// 	$result .= html_writer::tag('div', html_writer::tag('div', $generalfeedback, array('class' => 'generalfeedback')),array('class' => 'outcome clearfix'));
		// }

		// If there is a response and min/max word limit is set in the form then check the response word count.
		if ($qa->get_state() == question_state::$invalid) {

			$validation_error =  $qa->get_question()->check_input_word_count($step->get_qt_var($inputname),$question->responserequired,
				$question->maxwordlimit,$question->minwordlimit
			);

			$result .= html_writer::nonempty_tag('div',
				$validation_error, ['class' => 'validationerror']);
		}
		$result .= html_writer::tag('div', $files, array('class' => 'attachments'));
		$result .= html_writer::end_tag('div');


        if ($options->generalfeedback) {
        	$generalfeedback = $question->format_text($question->generalfeedback,
            		$question->generalfeedbackformat,
                $qa, 'question', 'generalfeedback', $question->id);
            $result .= html_writer::nonempty_tag('div', $generalfeedback,
                    array('class' => 'generalfeedback outcome clearfix'));
        }

		return $result;
	}
	
	public function files_input(question_attempt $qa, $numallowed,
		question_display_options $options) {
		global $CFG, $COURSE;
		require_once $CFG->dirroot . '/lib/form/filemanager.php';

		$pickeroptions = new stdClass();
		$pickeroptions->mainfile = null;
		$pickeroptions->maxfiles = $numallowed;
		$pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
			'attachments', $options->context->id);
		$pickeroptions->context = $options->context;
		$pickeroptions->return_types = FILE_INTERNAL | FILE_CONTROLLED_LINK;

		$pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
			'attachments', $options->context->id);
		$pickeroptions->accepted_types = $qa->get_question()->filetypeslist;

		$fm = new form_filemanager($pickeroptions);
		$fm->options->maxbytes = get_user_max_upload_file_size(
			$this->page->context,
			$CFG->maxbytes,
			$COURSE->maxbytes,
			$qa->get_question()->maxbytes
		);
		$filesrenderer = $this->page->get_renderer('core', 'files');

		$text = '';
		if (!empty($qa->get_question()->filetypeslist)) {
			$text = html_writer::tag('p', get_string('acceptedfiletypes', 'qtype_essay'));
			$filetypesutil = new \core_form\filetypes_util();
			$filetypes = $qa->get_question()->filetypeslist;
			$filetypedescriptions = $filetypesutil->describe_file_types($filetypes);
			$text .= $this->render_from_template('core_form/filetypes-descriptions', $filetypedescriptions);
		}

		$output = html_writer::start_tag('fieldset');
		$fileslabel = $options->add_question_identifier_to_label(get_string('answerfiles', 'qtype_essay'));
		$output .= html_writer::tag('legend', $fileslabel, ['class' => 'sr-only']);
		$output .= $filesrenderer->render($fm);
		$output .= html_writer::empty_tag('input', [
			'type' => 'hidden',
			'name' => $qa->get_qt_field_name('attachments'),
			'value' => $pickeroptions->itemid,
		]);
		$output .= $text;
		$output .= html_writer::end_tag('fieldset');

		return $output;
	}

	//end function for render essay
	
	
	// function for render recordrtc
	public function formulation_and_controls_recordrtc(question_attempt $qa, question_display_options $options, $question,$variable_name): string {
		/** @var qtype_recordrtc_question $question */
		// $question = $qa->get_question();
		$candownload = has_capability('qtype/recordrtc:downloadrecordings', $this->page->context);
		$output = '';

		$usageid = $qa->get_usage_id();
		$slot = $qa->get_slot();
		$contextid = $question->contextid;
		$itemid = $question->id;

		$existingfiles = $qa->get_last_qt_files($variable_name, $options->context->id);
		if (!$options->readonly) {
			// Prepare a draft file area to store the recordings.
			$draftitemid = $qa->prepare_response_files_draft_itemid($variable_name, $options->context->id);

			// Add a hidden form field with the draft item id.
			$output .= html_writer::empty_tag('input', ['type' => 'hidden',
				'name' => $qa->get_qt_field_name($variable_name), 'value' => $draftitemid]);

			// Warning for browsers that won't work.
			$output .= $this->cannot_work_warnings();
		}

		if ($qa->get_state() == question_state::$invalid) {
			// $output .= html_writer::nonempty_tag('div',
			// 	$question->get_validation_error([]), ['class' => 'validationerror']);
		}

		// Before we prepare the question text for display, which include applying the
		// Moodle text filters, we have to protect the placeholders with
		// <span class="nolink">...</span> tags.
		$questiontext = $question->questiontext;
		foreach ($question->widgets as $widget) {
			$questiontext = str_replace($widget->placeholder, $widget->get_protected_placeholder(),
				$questiontext);
		}
		$questiontext = $question->format_text($questiontext, $question->questiontextformat,
			$qa, 'question', 'questiontext', $question->id);

		// Replace all the placeholders with the corresponding recording or player widget.
		foreach ($question->widgets as $widget) {
			$filename = qtype_recordrtc::get_media_filename($widget->name, $widget->type);

			$filename = $variable_name;

			$existingfile = $question->get_file_from_response($filename, $existingfiles);

			if ($options->readonly) {
				// Review.
				if ($existingfile) {
					$recordingurl = $qa->get_response_file_url($existingfile);
				} else {
					$recordingurl = null;
				}


				switch ($widget->type) {
				case 'audio':
					$playback = new audio_playback($filename, $recordingurl, $candownload);
					break;
				case 'screen':
					$playback = new screen_playback($filename, $recordingurl, $candownload);
					break;
				default:
					$playback = new video_playback($filename, $recordingurl, $candownload);
					break;
				}

				$thisitem = $this->render($playback);
				if ($existingfile) {
					// The next line should logically just check ->feedback, but for some reason,
					// manual graded behaviour always sets that to false, so check general feedback
					// option too.
					if (($options->feedback || $options->generalfeedback) && $widget->feedback !== '') {
						$thisitem .= html_writer::div(
							$question->format_text(
								$widget->feedback, $widget->feedbackformat,
								$qa, 'question', 'answerfeedback', $widget->answerid),
							'specificfeedback');
					}
				}

			} else {
				// Being attempted.
				if ($existingfile) {
					$recordingurl = moodle_url::make_draftfile_url($draftitemid, '/', $filename);
				} else {
					$recordingurl = null;
				}

				switch ($widget->type) {
				case 'audio':
					$recorder = new audio_recorder($filename,
						$widget->maxduration, $question->allowpausing, $recordingurl, $candownload);
					break;
				case 'screen':
					$recorder = new screen_recorder($filename,
						$widget->maxduration, $question->allowpausing, $recordingurl, $candownload);
					break;
				default:
					$recorder = new video_recorder($filename,
						$widget->maxduration, $question->allowpausing, $recordingurl, $candownload);
					break;
				}

				// Recording UI.
				$thisitem = $this->render($recorder);
			}

			$questiontext = str_replace($widget->get_protected_placeholder(), $thisitem, $questiontext);
		}

		$output .= html_writer::tag('div', $questiontext, ['class' => 'qtext']);

		if (!$options->readonly) {
			// Initialise the JavaScript.
			$repositories = repository::get_instances(
				['type' => 'upload', 'currentcontext' => $options->context]);
			if (empty($repositories)) {
				throw new moodle_exception('errornouploadrepo', 'moodle');
			}
			$uploadrepository = reset($repositories); // Get the first (and only) upload repo.
			[$videowidth, $videoheight] = explode(',', get_config('qtype_recordrtc', 'videosize'));
			[$videoscreenwidth, $videoscreenheight] = explode(',', get_config('qtype_recordrtc', 'screensize'));
			$setting = [
				'audioBitRate' => (int) get_config('qtype_recordrtc', 'audiobitrate'),
				'videoBitRate' => (int) get_config('qtype_recordrtc', 'videobitrate'),
				'screenBitRate' => (int) get_config('qtype_recordrtc', 'screenbitrate'),
				'maxUploadSize' => $question->get_upload_size_limit($options->context),
				'uploadRepositoryId' => (int) $uploadrepository->id,
				'contextId' => $options->context->id,
				'draftItemId' => $draftitemid,
				'videoWidth' => (int) $videowidth,
				'videoHeight' => (int) $videoheight,
				'screenWidth' => (int) $videoscreenwidth,
				'screenHeight' => (int) $videoscreenheight,
			];
			$this->page->requires->strings_for_js($this->strings_for_js(), 'qtype_recordrtc');
			$this->page->requires->js_call_amd('qtype_recordrtc/avrecording', 'init',
				[$qa->get_outer_question_div_unique_id(), $setting]);
		}

		if ($options->generalfeedback) {
			$generalfeedback = $question->format_text($question->generalfeedback,
            		$question->generalfeedbackformat,
                $qa, 'question', 'generalfeedback', $question->id);
            $output .= html_writer::nonempty_tag('div', $generalfeedback,
                    array('class' => 'generalfeedback outcome clearfix'));
        }

		return $output;
	}

	/**
	 * These messages are hidden unless revealed by the JavaScript.
	 *
	 * @return string HTML for the 'this can't work here' messages.
	 */
	protected function cannot_work_warnings(): string {
		return $this->render_from_template('qtype_recordrtc/cannot_work_warnings', []);
	}

	/**
	 * Strings our JS will need.
	 *
	 * @return string[] lang string names from the qtype_recordrtc lang file.
	 */
	public function strings_for_js(): array {
		return [
			'gumabort',
			'gumabort_title',
			'gumnotallowed',
			'gumnotallowed_title',
			'gumnotfound',
			'gumnotfound_title',
			'gumnotreadable',
			'gumnotreadable_title',
			'gumnotsupported',
			'gumnotsupported_title',
			'gumoverconstrained',
			'gumoverconstrained_title',
			'gumsecurity',
			'gumsecurity_title',
			'gumtype',
			'gumtype_title',
			'nearingmaxsize',
			'nearingmaxsize_title',
			'pause',
			'recordagainx',
			'recordingfailed',
			'resume',
			'startrecording',
			'stoprecording',
			'startsharescreen',
			'timedisplay',
			'uploadaborted',
			'uploadcomplete',
			'uploadfailed',
			'uploadfailed404',
			'uploadpreparing',
			'uploadpreparingpercent',
			'uploadprogress',
		];
	}

	/**
	 * Map icons for font-awesome themes.
	 *
	 * @return array of icon mappings.
	 */
	public function qtype_recordrtc_get_fontawesome_icon_map(): array {
		return [
			'atto_recordrtc:i/audiortc' => 'fa-microphone',
			'atto_recordrtc:i/videortc' => 'fa-video-camera',
		];
	}

	/**
	 * Generate the specific feedback. This is feedback that varies according to
	 * the response the student gave. This method is only called if the display options
	 * allow this to be shown.
	 *
	 * @param question_attempt $qa the question attempt to display.
	 * @return string HTML fragment.
	 */
	// protected function specific_feedback(question_attempt $qa) {
	// 	return "specific_feedback";
	// }

	/**
	 * Generates an automatic description of the correct response to this question.
	 * Not all question types can do this. If it is not possible, this method
	 * should just return an empty string.
	 *
	 * @param question_attempt $qa the question attempt to display.
	 * @return string HTML fragment.
	 */
	// protected function correct_response(question_attempt $qa) {
	// 	return "";
	// }
	protected function get_input_id(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('answer' . $value);
    }

    protected function get_input_type() {
        return 'radio';
    }
    protected function get_input_name(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('answer');
    }
    protected function get_input_value($value) {
        return $value;
    }
    
    protected function number_in_style($num, $style) {
        switch($style) {
            case 'abc':
                $number = chr(ord('a') + $num);
                break;
            case 'ABCD':
                $number = chr(ord('A') + $num);
                break;
            case '123':
                $number = $num + 1;
                break;
            case 'iii':
                $number = question_utils::int_to_roman($num + 1);
                break;
            case 'IIII':
                $number = strtoupper(question_utils::int_to_roman($num + 1));
                break;
            case 'none':
                return '';
            default:
                return 'ERR';
        }
        return $this->number_html($number);
    }

     protected function number_html($qnum) {
        return $qnum . '. ';
    }

    protected function combined_feedback(question_attempt $qa) {
        $question = $qa->get_question();

        $state = $qa->get_state();

        if (!$state->is_finished()) {
            $response = $qa->get_last_qt_data();
            if (!$qa->get_question()->is_gradable_response($response)) {
                return '';
            }
            list($notused, $state) = $qa->get_question()->grade_response($response);
        }

        $feedback = '';
        $field = $state->get_feedback_class() . 'feedback';
        $format = $state->get_feedback_class() . 'feedbackformat';
        if ($question->$field) {
            $feedback .= $question->format_text($question->$field, $question->$format,
                    $qa, 'question', $field, $question->id);
        }
        return $feedback;
    }

    protected function is_right(question_answer $ans) {
        return $ans->fraction;
    }

    public function feedback(question_attempt $qa, question_display_options $options) {
        $output = '';
        $hint = null;

        if ($options->feedback) {
            $output .= html_writer::nonempty_tag('div', $this->specific_feedback($qa),
                    array('class' => 'specificfeedback'));
            $hint = $qa->get_applicable_hint();
        }

        if ($options->numpartscorrect) {
            $output .= html_writer::nonempty_tag('div', $this->num_parts_correct($qa),
                    array('class' => 'numpartscorrect'));
        }

        if ($hint) {
            $output .= $this->hint($qa, $hint);
        }

        if ($options->generalfeedback) {
            $output .= html_writer::nonempty_tag('div', $this->general_feedback($qa),
                    array('class' => 'generalfeedback'));
        }

        if ($options->rightanswer) {
            $output .= html_writer::nonempty_tag('div', $this->correct_response($qa),
                    array('class' => 'rightanswer'));
        }

        return $output;
    }

     protected function general_feedback(question_attempt $qa) {
    	global $CFG;
    	$config = get_config('qtype_thvstepcluster');
	    $globalusedrm = $config->globalusedrm;

        if (1 == $globalusedrm) {
	    	$hostdrm = $config->urlDRM;
		    $questionid = $qa->get_question()->id;
        	$fs = get_file_storage();
		    $files = $fs->get_area_files($qa->get_question()->contextid, 'question', 'generalfeedback', $questionid, 'sortorder DESC, id ASC', false);
		    // $generalfeedback = $qa->get_question()->generalfeedback;
		    $generalfeedback = $qa->get_question()->format_generalfeedback($qa);

	        $u_agent = $_SERVER['HTTP_USER_AGENT'];
	        $operating_system = 'Unknown Operating System';

	        if (preg_match('/chrome|chromium|crios/i', $u_agent)) {
	            $bs = 'chrome';
	        } else if (preg_match('/firefox|fxios/i', $u_agent)) {
	            $bs = 'firefox';
	        } else if (preg_match('/safari/i', $u_agent)) {
	            $bs = 'safari';
	        }

	        $os = '';
	        if (preg_match('/iphone|ipad|ipod/i', $u_agent)) {
	            $os = 'iphone';
	        }

	        $type = '';
	        $subfile = '';
		    $code = "<link href='$CFG->wwwroot/question/type/thvstepcluster/video-js.css' rel='stylesheet'>
			    <script src='$CFG->wwwroot/question/type/thvstepcluster/video.js'></script>
			    <script src='$CFG->wwwroot/question/type/thvstepcluster/videojs-http-streaming.min.js'></script>
			    <script src='$CFG->wwwroot/question/type/thvstepcluster/node_modules/videojs-contrib-eme/dist/videojs-contrib-eme.min.js'></script>";
			$generalfeedback = $code . $generalfeedback;
		    $i = 0;
		    foreach ($files as $key => $f) {
		    	if ($f->get_mimetype() == 'video/mp4'){
		    		$filename = pathinfo($f->get_filename(), PATHINFO_FILENAME);
			        $filename_encode = rawurlencode($f->get_filename());
		    		$filename_encode = pathinfo($filename_encode, PATHINFO_FILENAME);
			        $contenthash = $f->get_contenthash($filename);
			        $dir1 = substr($contenthash, 0, 2);
			        $dir2 = substr($contenthash, 2, 2);
			        $contentpath = $dir1 . "/" . $dir2 . "/" . $contenthash;

		            if ($os == 'iphone') {
		                $contentsrc = $hostdrm . "/hls/," . $contentpath . $subfile . ",.urlset/master.m3u8";
		                $type = 'application/x-mpegURL';
		            } else if ($bs == 'safari') {
		                $contentsrc = $hostdrm . "/dash/," . $contentpath . $subfile . ",.urlset/manifest.mpd";
		                $type = 'application/dash+xml';
		            } else {
		                $contentsrc = $hostdrm . "/edash/," . $contentpath . $subfile . ",.urlset/manifest.mpd";
		                $type = 'application/dash+xml';
		            }

		            // print($contentsrc);

		            $width = $CFG->media_default_width . "px";
		            $height = $CFG->media_default_height . "px";
		            $videoid = 'video_' . $questionid . '_' . $i;
		            $code = "
				    <div class='resourcecontent'>
				        <div class='mediaplugin mediaplugin_videojs d-block'>
				            <div style='max-width:$width;max-height:$height;'>
				                <video-js id='$videoid' width='$width' class='vjs-default-skin' controls></video-js>
				            </div>
				        </div>
				    </div>
				    
				    <script>
				    var player = videojs('$videoid', {playbackRates: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2]});
				    player.eme();
				    player.src({
				      src: '$contentsrc',
				      type: '$type',
				      keySystems: {
				        'org.w3.clearkey': '$CFG->wwwroot/question/type/thvstepcluster/dash_clear_key.php'
				      }
				    });
				    </script>";
				    $pattern = '/<video\b[^>]*>\s*<source\s+src=["\'](?:[^"\']*\/)?'.$filename_encode.'\.mp4[^"\']*["\'][^>]*>.*?<\/video>/i';
				    $generalfeedback =  preg_replace('/\s+(?=<\/?)/', '', $generalfeedback);
				    preg_match($pattern, $generalfeedback, $matches);
				    $generalfeedback = str_replace($matches[0], $code, $generalfeedback);
				    $i++;
		    	}
	    	}
	    	return $generalfeedback;

        } else {
        	return $qa->get_question()->format_generalfeedback($qa);
        }
    }
	 protected function recursive_str_replace($search, $replace, $data) {
        if (is_string($data)) {
            return str_replace($search, $replace, $data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursive_str_replace($search, $replace, $value);
            }
            return $data;
        }
        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->{$key} = $this->recursive_str_replace($search, $replace, $value);
            }
            return $data;
        }
        return $data;
    }
}