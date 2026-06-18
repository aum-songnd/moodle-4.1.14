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
 * Question definition class for thvstepcluster.
 *
 * @package     qtype_thvstepcluster
 * @copyright   phamleminh1812@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.

/**
 * Class that represents a thvstepcluster question.
 */
class qtype_thvstepcluster_question extends question_graded_automatically {
	protected $order = null;
	
	public function make_behaviour(question_attempt $qa, $preferredbehaviour) {

		$question_items_instance = $this->question_items_instance;
		$question_total = count($question_items_instance);
		$essay_total = 0;
		$recordrtc_total = 0;
		foreach ($question_items_instance as $key => $question) {
			if ($question->get_type_name() == "description") {
				continue;
			} elseif ($question->get_type_name() == "recordrtc") {
				$recordrtc_total++;
				continue;
			} elseif ($question->get_type_name() == "essay") {
				$essay_total++;
				continue;
			}
		}

		if (($essay_total + $recordrtc_total) != $question_total) {
			return question_engine::make_archetypal_behaviour($preferredbehaviour, $qa);
		}
		return question_engine::make_behaviour('manualgraded', $qa, $preferredbehaviour);
    }
	/**
	 * Get a question_attempt_step_subquestion_adapter
	 * @param question_attempt_step $step the step to adapt.
	 * @param int $i the subquestion index.
	 * @return question_attempt_step_subquestion_adapter.
	 */
	protected function get_substep($step, $i) {
		return new question_attempt_step_subquestion_adapter($step, 'sub'. $i . '_');
	}

	public function start_attempt(question_attempt_step $step, $variant) {
		$question_items_instance = $this->question_items_instance;
		foreach ($question_items_instance as $i => $value) {
			$value->start_attempt($this->get_substep($step, $i), $variant);
		}
	}

	public function apply_attempt_state(question_attempt_step $step) {

		$question_items_instance = $this->question_items_instance;
		foreach ($question_items_instance as $i => $value) {
			$value->apply_attempt_state($this->get_substep($step, $i));
		}
	}

	public function update_attempt_state_data_for_new_version(question_attempt_step $oldstep, question_definition $oldquestion) {
		$newdata = [];

		$question_items_instance = $this->question_items_instance;
	
		foreach ($question_items_instance as $i => $newsubq) {
			$oldsubq = $oldquestion->question_items_instance[$i] ?? null;
			if (!$oldsubq) {
				continue;
			}
	
			$substep = new question_attempt_step_subquestion_adapter($oldstep, "sub{$i}_");
	
			// Xử lý multichoice
			if ($newsubq->get_type_name() === 'multichoice' && $oldsubq->get_type_name() === 'multichoice') {
				$oldanswers = $oldsubq->answers;
				$newanswers = $newsubq->answers;
			
				$mapping = [];
				foreach ($oldanswers as $oldid => $oldans) {
					foreach ($newanswers as $newid => $newans) {
						if (strip_tags($oldans->answer) === strip_tags($newans->answer)) {
							$mapping[$oldid] = $newid;
							break;
						}
					}
				}
	
				$orderkey = "_sub{$i}_order";
				if ($oldstep->has_qt_var($orderkey)) {
					$oldorderstr = $oldstep->get_qt_var($orderkey);
					$oldorder = array_map('trim', explode(',', $oldorderstr));
					$mappedorder = [];
	
					foreach ($oldorder as $oldid) {
						$mappedorder[] = isset($mapping[$oldid]) ? $mapping[$oldid] : $oldid;
					}

					$newdata[$orderkey] = implode(',', $mappedorder);
				}
				

				continue;
			}

			// Xử lý multianswer (cloze)
			if ($newsubq->get_type_name() === 'multianswer' && $oldsubq->get_type_name() === 'multianswer') {
				// Multianswer cũng có thuộc tính ->subquestions
				foreach ($newsubq->subquestions as $j => $newsubqitem) {
					$oldsubqitem = $oldsubq->subquestions[$j] ?? null;
					if (!$oldsubqitem) {
						continue;
					}

					// Giả sử cần map đáp án với dạng multichoice
					if ($newsubqitem->get_type_name() === 'multichoice' && $oldsubqitem->get_type_name() === 'multichoice') {
						$oldanswers = $oldsubqitem->answers;
						$newanswers = $newsubqitem->answers;

						$mapping = [];
						foreach ($oldanswers as $oldid => $oldans) {
							foreach ($newanswers as $newid => $newans) {
								if (strip_tags($oldans->answer) === strip_tags($newans->answer)) {
									$mapping[$oldid] = $newid;
									break;
								}
							}
						}

						$orderkey = "_sub{$i}_{$j}_order"; // hoặc dạng khác tùy Moodle lưu key như nào
						if ($oldstep->has_qt_var($orderkey)) {
							$oldorderstr = $oldstep->get_qt_var($orderkey);
							$oldorder = array_map('trim', explode(',', $oldorderstr));
							$mappedorder = [];

							foreach ($oldorder as $oldid) {
								$mappedorder[] = isset($mapping[$oldid]) ? $mapping[$oldid] : $oldid;
							}

							$newdata[$orderkey] = implode(',', $mappedorder);
						}
					}
				}

				continue;
			}
	
			// Các loại subquestion khác
			$subdata = $newsubq->update_attempt_state_data_for_new_version($substep, $oldsubq);
			foreach ($subdata as $key => $value) {
				$fullkey = "sub{$i}_{$key}";
				if (!$oldstep->has_qt_var($fullkey) || $oldstep->get_qt_var($fullkey) != $value) {
					$newdata[$fullkey] = $value;
				}
			}
		}
	
		// Xử lý _order của câu hỏi cha (nếu có)
		if ($oldstep->has_qt_var('_order')) {
			$newdata['_order'] = $oldstep->get_qt_var('_order');
		}
	
		return $newdata;
	}

	public function get_expected_data() {

		global $DB;

		foreach ($this->question_items_instance as $key => $question) {

			$name = "answer$key";

			if ($question->get_type_name() == "description") {
				continue;
			} elseif ($question->get_type_name() == "recordrtc") {
				$results[$name] = question_attempt::PARAM_FILES;
			} 
			elseif ($question->get_type_name() == "shortanswer"){
				$results[$name] = question_attempt::PARAM_MARK;
			} elseif ($question->get_type_name() == "truefalse"){
				$results[$name] = PARAM_INT;
			} else if ($question->get_type_name() == "multianswer") {
				$expected = array();

				foreach ($question->subquestions as $i => $subq) {
		            $substep = $this->get_substep(null, $i.$key);
		            foreach ($subq->get_expected_data() as $name1 => $type) {
		                if ($subq->qtype->name() == 'multichoice' &&
		                        $subq->layout == qtype_multichoice_base::LAYOUT_DROPDOWN) {
		                    // Hack or MC inline does not work.
		                    $results[$substep->add_prefix($name1)] = PARAM_RAW;
		                } else {
		                    $results[$substep->add_prefix($name1)] = $type;
		                }
		            }
		        }
			} else {
				$results[$name] = PARAM_RAW;
			}

			$question->expected_data_name = $name;
		}

		return $results;
	}

	/**
	 * Returns the data that would need to be submitted to get a correct answer.
	 *
	 * @return array|null Null if it is not possible to compute a correct response.
	 */
	public function get_correct_response() {

		global $DB;
		$question_items_instance = $this->question_items_instance;
		$results = [];

		foreach ($question_items_instance as $key => $value) {
			$k = "answer$key";
			$question = $value;

			if ($question->get_type_name() == "description") {
				continue;
			}
			if ($question->get_type_name() != "multianswer") {
				$response = $question->get_correct_response();
				if ($response == null) {
					continue;
				}

				$results[$k] = $response['answer'] ?? reset($response);
			} else {
		        foreach ($question->subquestions as $i => $subq) {
		            $substep = $this->get_substep(null, $i.$key);
		            foreach ($subq->get_correct_response() as $name => $type) {
		                $results[$substep->add_prefix($name)] = $type;
		            }
		        }
			}
		}

		return $results;
	}

	/**
	 * Checks whether the user is allowed to be served a particular file.
	 *
	 * @param question_attempt $qa The question attempt being displayed.
	 * @param question_display_options $options The options that control display of the question.
	 * @param string $component The name of the component we are serving files for.
	 * @param string $filearea The name of the file area.
	 * @param array $args the Remaining bits of the file path.
	 * @param bool $forcedownload Whether the user must be forced to download the file.
	 * @return bool True if the user can access this file.
	 */
	public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {

		return true;

		if ($component == 'question' && $filearea == 'questiontext') {
			// Question text always visible, but check it is the right question id.
			return true;

		} else if ($component == 'question' && $filearea == 'generalfeedback') {
			return true;

		} else if ($component == 'question' && strpos($filearea, "response_answer") == 0) {
			return true;
		} else {
			// Unrecognised component or filearea.
			return false;
		}
	}


	    /**
     * Check the input word count and return a message to user
     * when the number of words are outside the boundary settings.
     *
     * @param string $responsestring
     * @return string|null
     .*/
    public function check_input_word_count($responsestring,$responserequired, $maxwordlimit,$minwordlimit) {
        if (!$responserequired) {
            return null;
        }
        if (!$minwordlimit && !$maxwordlimit) {
            // This question does not care about the word count.
            return null;
        }

        // Count the number of words in the response string.
        $count = count_words($responsestring);
        if ($maxwordlimit && $count > $maxwordlimit) {
            return get_string('maxwordlimitboundary', 'qtype_essay',
                    ['limit' => $maxwordlimit, 'count' => $count]);
        } else if ($count < $minwordlimit) {
            return get_string('minwordlimitboundary', 'qtype_essay',
                    ['limit' => $minwordlimit, 'count' => $count]);
        } else {
            return null;
        }
    }


    public function get_word_count_message_for_review(array $response, $inputname, $maxwordlimit, $minwordlimit): string {
        if (!$minwordlimit && !$maxwordlimit) {
            // This question does not care about the word count.
            return '';
        }

        if (!array_key_exists($inputname, $response) || ($response[$inputname] === '')) {
            // No response.
            return '';
        }

        $count = count_words($response[$inputname]);
        if ($maxwordlimit && $count > $maxwordlimit) {
            return get_string('wordcounttoomuch', 'qtype_essay',
                    ['limit' => $maxwordlimit, 'count' => $count]);
        } else if ($count < $minwordlimit) {
            return get_string('wordcounttoofew', 'qtype_essay',
                    ['limit' => $minwordlimit, 'count' => $count]);
        } else {
            return get_string('wordcount', 'qtype_essay', $count);
        }
    }


	public function is_complete_response(array $response) {

		// return false;

		// TODO.
		/* You might want to check that the user has done something
	            before returning true, e.g. clicked a radio button or entered some
	            text
*/	
	    $expected_data = $this->get_expected_data();


	    $question_items_instance = $this->question_items_instance;
		foreach ($question_items_instance as $key => $qtype) {
			if($qtype->get_type_name() == "essay"){

				$responserequired = $qtype->responserequired;
				$minwordlimit = $qtype->minwordlimit;
				$maxwordlimit = $qtype->maxwordlimit;
				$expected_data_name = $qtype->expected_data_name;

				if (array_key_exists($expected_data_name, $response) && ($response[$expected_data_name] !== '')) {
					$responsestring = $response[$expected_data_name];
					if($this->check_input_word_count($responsestring,$responserequired,$maxwordlimit,$minwordlimit)) {
						return false;
					};
				}
			}

		}


		

	    foreach ($expected_data as $data_key => $data_type) {
	    	if($data_type == question_attempt::PARAM_FILES && array_key_exists($data_key, $response)) {
	    		$ans_saver_file = $response[$data_key];

	    		if(!$ans_saver_file) return false;

				if($ans_saver_file)
	    		{	
	    			if($ans_saver_file instanceof question_file_saver){
	    				$value = $ans_saver_file->__toString();
	    				if(!$value) return false;
	    			}
	    		}	    		
	    	}
	    	else if($data_type==PARAM_RAW && array_key_exists($data_key, $response)){
	    		$ans = $response[$data_key];

	    		if( (!$ans && $ans!='0') || $ans < 0 )
	    		{
	    			return false;
	    		}

	    	} else if ($data_type==question_attempt::PARAM_MARK && array_key_exists($data_key, $response)) {
	    		$ans = $response[$data_key];
	    		if($ans == '')
	    		{
	    			return false;
	    		}
	    	} else if ($data_type==PARAM_INT && array_key_exists($data_key, $response)){
	    		$ans = $response[$data_key];
	    		if($ans != 0 and $ans != 1)
	    		{
	    			return false;
	    		}
	    	}
	    	else if(!array_key_exists($data_key, $response))
	    	{

				return false;
	    	}
	    }
	    return true;


		// $expecteddata_key = array_keys($this->get_expected_data());
		// $response_key = array_keys($response);
		// $accept_response_key = [];
		// foreach ($response as $key => $value) {
		// 	if($value && $value >= 0)
		// 	{
		// 		$accept_response_key[] = $key;
		// 	}
		// }

		// foreach ($expecteddata_key as $expectedkey) {
		// 	if (!in_array($expectedkey, $accept_response_key)) {
		// 		return false;
		// 	}
		// }

		// return true;
	}

	/**
	 * if you are moving from viewing one question to another this will
	 * discard the processing if the answer has not changed. If you don't
	 * use this method it will constantantly generate new question steps and
	 * the question will be repeatedly set to incomplete. This is a comparison of
	 * the equality of two arrays.
	 * Comment from base class:
	 *
	 * Use by many of the behaviours to determine whether the student's
	 * response has changed. This is normally used to determine that a new set
	 * of responses can safely be discarded.
	 *
	 * @param array $prevresponse the responses previously recorded for this question,
	 *      as returned by {@link question_attempt_step::get_qt_data()}
	 * @param array $newresponse the new responses, in the same format.
	 * @return bool whether the two sets of responses are the same - that is
	 *      whether the new set of responses can safely be discarded.
	 */

	public function is_same_response(array $prevresponse, array $newresponse) {
		// TODO.
		return false;
	}

	/**
	 * @return summary
	 * A string that summarises how the user responded.
	 * It is written to responsesummary field of
	 * the question_attempts table, and used in the
	 * the quiz responses report
	 * */
	public function summarise_response(array $response) {
		// TODO.
		return null;
	}

	// public function is_gradable_response(array $response) {

	// 	$expected_data = $this->get_expected_data();
	//     foreach ($expected_data as $data_key => $data_type) {
	//     	if($data_type == question_attempt::PARAM_FILES && array_key_exists($data_key, $response)) {
	//     		$ans_saver_file = $response[$data_key];
	// 			if($ans_saver_file)
	//     		{	
	//     			if($ans_saver_file instanceof question_file_saver){
	//     				$value = $ans_saver_file->__toString();
	//     				if($value) return true;
	//     			}
	//     			else if(is_string($ans_saver_file))
	//     			{
	//     				return true;
	//     			}
	//     		}
	//     	}
	//     	else if($data_type==PARAM_RAW && array_key_exists($data_key, $response)){
	//     		$ans = $response[$data_key];

	//     		if($ans && $ans >= 0 )
	//     		{
	//     			return true;
	//     		}

	//     	} else if ($data_type==question_attempt::PARAM_MARK && array_key_exists($data_key, $response)){
	//     		$ans = $response[$data_key];
	//     		if($ans or $ans === '0')
	//     		{
	//     			return true;
	//     		}
	//     	} else if ($data_type==PARAM_INT && array_key_exists($data_key, $response)){
	//     		$ans = $response[$data_key];
	//     		if($ans == 0 or $ans == 1)
	//     		{
	//     			return true;
	//     		}
	//     	}
	//     }
	//     return false;

	// }

	public function is_gradable_response(array $response) {
		$expected_data = $this->get_expected_data();
	
		foreach ($expected_data as $data_key => $data_type) {
			if (!array_key_exists($data_key, $response)) {
				continue;
			}
	
			$ans = $response[$data_key];
	
			// 1. Nếu là file upload (recordrtc, essay có file...), check có file không
			if ($data_type === question_attempt::PARAM_FILES) {
				if ($ans instanceof question_file_saver) {
					if ((string)$ans !== '') {
						return true;
					}
				} else if (is_string($ans) && trim($ans) !== '') {
					return true;
				}
			}
	
			// 2. Nếu là text input: không được rỗng
			else if ($data_type === PARAM_RAW) {
				if ($ans !== null && $ans !== '') {
					return true;
				}
			}
	
			// 3. Nếu là điểm số hoặc số thực: chấp nhận cả 0
			else if ($data_type === question_attempt::PARAM_MARK) {
				if ($ans !== null && $ans !== '') {
					return true;
				}
			}
	
			// 4. Nếu là số nguyên (như 0, 1): chấp nhận 0 là hợp lệ
			else if ($data_type === PARAM_INT) {
				if ($ans !== null && $ans !== '') {
					return true;
				}
			}
	
			// 5. Bổ sung nếu bạn dùng kiểu khác nữa...
		}
	
		// Nếu không có dữ liệu nào hợp lệ
		return false;
	}
	

	public function get_validation_error(array $response) {
		// TODO.
		if ($this->is_complete_response($response)) {
			return '';
		}

		return get_string('pleaseananswerallparts', 'qtype_multianswer');

	}

	/**
	 * @param array $response responses, as returned by
	 *      {@link question_attempt_step::get_qt_data()}.
	 * @return array (number, integer) the fraction, and the state.
	 */
	public function grade_response(array $response) {

		// TODO.
		$max_mark = $this->defaultmark;
		$question_items_instance = $this->question_items_instance;

		$user_mark = 0;
		foreach ($question_items_instance as $key => $value) {

			if ($value->get_type_name() == "description") {
				continue;
			}
			if ($value->get_type_name() == "essay") {
				continue;
			}
			if ($value->get_type_name() == "recordrtc") {
				continue;
			}
	
			$questionitem = $value;
			$correct_response = $questionitem->get_correct_response();

			if ($questionitem->get_type_name() != "multianswer") {
				$correct_response = $correct_response["answer"];
				$keyresponse = "answer$key";

				if (array_key_exists($keyresponse, $response)) {
					$itemresponse = $response[$keyresponse];
				} else {
					$itemresponse = -1;
				}
				$item_fraction = 0;
				if ($itemresponse == $correct_response) {
					$item_fraction = 1;
				}
				$item_mark = $item_fraction * $questionitem->defaultmark;
				$user_mark += $item_mark;
			} else {

		        $fractionmax = 0;
		        foreach ($questionitem->subquestions as $i => $subq) {
		            $fractionmax += $subq->defaultmark;
		            $substep = $this->get_substep(null, $i.$key);
		            $subresp = $substep->filter_array($response);
		            if (!$subq->is_gradable_response($subresp)) {
		            } else {
		                list($subfraction, $newstate) = $subq->grade_response($subresp);
		                $user_mark += $subfraction * $subq->defaultmark;
		            }
		        }
			}
		}

		$fraction = (float) $user_mark / (float) $max_mark;

		return array($fraction, question_state::graded_state_for_fraction($fraction));
	}
	
	// public function get_response(question_attempt $qa) {
    //     return $qa->get_last_qt_var('answer', -1);
    // }


    // public function get_order(question_attempt $qa) {
    //     $this->init_order($qa);
    //     return $this->order;
    // }

    // protected function init_order(question_attempt $qa) {
    //     if (is_null($this->order)) {
    //         $this->order = explode(',', $qa->get_step(0)->get_qt_var('_order'));
    //     }
    // }

    // public function is_choice_selected($response, $value) {
    //     return (string) $response === (string) $value;
    // }
}
