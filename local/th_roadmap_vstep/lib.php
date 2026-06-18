<?php

require_once($CFG->dirroot.'/grade/export/xls/grade_export_xls.php');
use \core_grades\output\action_bar;
use \core_grades\output\general_action_bar;

function local_th_roadmap_vstep_extend_navigation_course($navigation, $course, $context) {
	if (has_capability('local/th_roadmap_vstep:export', $context)) {
		$url = new moodle_url('/local/th_roadmap_vstep/index.php', array('id' => $course->id));
		$navigation->add(get_string('name', 'local_th_roadmap_vstep'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/export', ''));
	}
}

class th_roadmap_vstep_grade_export_xls extends grade_export_xls {

    public $plugin = 'xls';

    /**
     * Constructor should set up all the private variables ready to be pulled
     * @param object $course
     * @param int $groupid id of selected group, 0 means all
     * @param stdClass $formdata The validated data from the grade export form.
     */
    public function __construct($course, $groupid, $formdata) {
        parent::__construct($course, $groupid, $formdata);

        // Overrides.
        $this->usercustomfields = true;
    }

    /**
     * To be implemented by child classes
     */
    public function print_grades() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/excellib.class.php');

        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

        // If this file was requested from a form, then mark download as complete (before sending headers).
        \core_form\util::form_download_complete();

        // Calculate file name
        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        $downloadfilename = clean_filename("$shortname $strgrades.xls");
        // Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers
        $workbook->send($downloadfilename);
        // Adding the worksheet
        $myxls = $workbook->add_worksheet($strgrades);

        // Print names of all the fields
        $profilefields = grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);
        foreach ($profilefields as $id => $field) {
            $myxls->write_string(0, $id, $field->fullname);
        }
        $pos = count($profilefields);
        if (!$this->onlyactive) {
            $myxls->write_string(0, $pos++, get_string("suspended"));
        }
        foreach ($this->columns as $grade_item) {
            foreach ($this->displaytype as $gradedisplayname => $gradedisplayconst) {
                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, false, $gradedisplayname));
            }
            // Add a column_feedback column
            if ($this->export_feedback) {
                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
            }
        }
        // // Last downloaded column header.
        $myxls->write_string(0, $pos++, get_string('roadmap', 'local_th_roadmap_vstep'));
        $myxls->write_string(0, $pos++, get_string('timeexported', 'gradeexport_xls'));

        // Print all the lines of data.
        $i = 0;
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->allow_user_custom_fields($this->usercustomfields);
        $gui->init();
        // $sql = "SELECT rm.coursecode,knlnn,listening,reading,speaking,writing,dktq,groups,c.fullname
        // FROM {local_th_roadmap_vstep} rm
        // LEFT JOIN {course} c ON rm.coursecode = c.shortname";
        $records = $DB->get_records('local_th_roadmap_vstep', null, '', 'coursecode,knlnn,listening,reading,speaking,writing,dktq,groups');
        // $records = $DB->get_records_sql($sql);
        // print_object($records);
        // exit;
        $config = get_config('local_th_roadmap_vstep');
        $idnumberlistening = $config->listening;
        $idnumberreading = $config->reading;
        $idnumberspeaking = $config->speaking;
        $idnumberwriting = $config->writing;
        $idnumbertarget = $config->target;
        // print_object($records);
        // exit;
        $roadmap = '';
        while ($userdata = $gui->next_user()) {
            $i++;
            $user = $userdata->user;

            foreach ($profilefields as $id => $field) {
                $fieldvalue = grade_helper::get_user_field_value($user, $field);
                $myxls->write_string($i, $id, $fieldvalue);
            }
            $j = count($profilefields);
            if (!$this->onlyactive) {
                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                $myxls->write_string($i, $j++, $issuspended);
            }
            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }
                if ($grade->grade_item->idnumber == $idnumberlistening) {
                	$listening = floor($grade->finalgrade);
                }
                if ($grade->grade_item->idnumber == $idnumberreading) {
                	$reading = floor($grade->finalgrade);
                }
                if ($grade->grade_item->idnumber == $idnumberspeaking) {
                	$speaking = floor($grade->finalgrade);
                }
                 if ($grade->grade_item->idnumber == $idnumberwriting) {
                	$writing = floor($grade->finalgrade);
                }
                if ($grade->grade_item->idnumber == $idnumbertarget) {
                	$target = floor($grade->finalgrade);
                }
            	// exit;
                foreach ($this->displaytype as $gradedisplayconst) {
            	// print_object($grade);
                    $gradestr = $this->format_grade($grade, $gradedisplayconst);
                    if (is_numeric($gradestr)) {
                        $myxls->write_number($i, $j++, $gradestr);
                    } else {
                        $myxls->write_string($i, $j++, $gradestr);
                    }
                }
                // writing feedback if requested
                if ($this->export_feedback) {
                    $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid], $grade));
                }
            }

            if (!empty($records)) {
            	if (isset($listening) and isset($reading) 
	            	and isset($speaking) and isset($writing)
	            	and isset($target) and !empty($target)) {
            		$roadmap = th_roadmap_vstep($records, $listening, $reading, $speaking, $writing, $target);
            	} else if (!isset($listening) or empty($listening)) {
            		$roadmap = get_string('nolistening','local_th_roadmap_vstep');
            	} else if (!isset($reading) or empty($reading)) {
            		$roadmap = get_string('noreading','local_th_roadmap_vstep');
            	} else if (!isset($speaking) or empty($speaking)) {
            		$roadmap = get_string('nospeaking','local_th_roadmap_vstep');
            	} else if (!isset($writing) or empty($writing)) {
            		$roadmap = get_string('nowriting','local_th_roadmap_vstep');
            	} else if (!isset($target) or empty($target)) {
            		$roadmap = get_string('notarget','local_th_roadmap_vstep');
            	} else {
            		$roadmap = '';
            	}

            } else {
            	$roadmap = get_string('no_table_roadmap', 'local_th_roadmap_vstep');
            }
			
			// print_object($roadmap);
			// exit;

            $myxls->write_string($i, $j++, $roadmap);
            // Time exported.
            $myxls->write_string($i, $j++, time());
        }
        $gui->close();
        $geub->close();

    	/// Close the workbook
        $workbook->close();

        exit;
    }
}

/**
 * Get fullname course by shortname
 */
function get_coursefullname($shortname) {
	global $DB;
	$fullname = $DB->get_field('course', 'fullname', array('shortname' => $shortname));
	if ($fullname) {

		return '[' . $shortname . '] ' . $fullname;
	}elseif ($shortname===''){
		return get_string('unknown','local_th_roadmap_vstep');
	}
	return '[' . $shortname . ']';
}

/**
 * @param  $records [stdClass] record table local_th_roadmap_vstep
 * @param  $listening [int]  listening level
 * @param  $reading [int]	reading level
 * @param  $speaking [int]  speaking level
 * @param  $writing [int]	writing level
 * @param  $target [int]	target level
 * @return [string]	Road Map
 */
function th_roadmap_vstep($records, $listening, $reading, $speaking, $writing, $target) {

	$config = get_config('local_th_roadmap_vstep');
    $idnumberlistening = trim($config->listening);
    $idnumberreading = trim($config->reading);
    $idnumberspeaking = trim($config->speaking);
    $idnumberwriting = trim($config->writing);
    $idnumbertarget = trim($config->target);

	$level_trung_binh = ($listening + $reading + $speaking + $writing) / 4;
	$levelA = floor($level_trung_binh);
	$startA = 'A'. $levelA . '.' . '0' . $levelA;
	if (!array_key_exists($startA, $records)) {
  		$startA = '';
  	}
	$targetA = 'A'. $target . '.' . '0' . $target;

	if ($levelA <= $target) {
		$niz = array($targetA);
		$roadmapA = roadmapA($records, $targetA, $startA, $niz);
		$roadmapA = array_reverse($roadmapA);
	}

	$a = new stdClass();
	$a->start = get_coursefullname($startA);
	$a->target = get_coursefullname($targetA);
	
	$roadmap = "A: Level trung bình: $level_trung_binh, " . get_string('roadmaplevel', 'local_th_roadmap_vstep', $a) . implode(' -> ', array_map('get_coursefullname',$roadmapA));
	$targetB = 'B'. $target;

	$bl = $br = $bw = $bs = '';
	foreach ($records as $key => $level) {
		
		if (strpos($key, "B". $listening) !== false) {
			if (1 == $level->listening) {
				$bl = $key;
			}
		}

		if (strpos($key, "B". $reading) !== false) {
			if (1 == $level->reading) {
				$br = $key;
			}
		}

		if (strpos($key, "B". $writing) !== false) {
		
			if (1 == $level->writing) {
				$bw = $key;
			}
		}

		if (strpos($key, "B". $speaking) !==  false) {
			if (1 == $level->speaking) {
				$bs = $key;
			}
		}
	}

	foreach ($records as $key => $record) {
		if (strstr($key, $targetB) ==! null and $record->listening == 1) {
			$targetBl = $key;
		}
		if (strstr($key, $targetB) ==! null and $record->reading == 1) {
			$targetBr = $key;
		}
		if (strstr($key, $targetB) ==! null and $record->speaking == 1) {
			$targetBs = $key;
		}
		if (strstr($key, $targetB) ==! null and $record->writing == 1) {
			$targetBw = $key;
		}
	}
	$Bl  = array();
	if ($listening <= $target) {
		$niz = array($targetBl);
		$Bl = roadmapB($records, $targetBl, $bl, 'listening', $niz);
		$Bl = array_reverse($Bl);
	}
	$Br = array();
	if ($reading <= $target) {
		$niz = array($targetBr);
		$Br = roadmapB($records, $targetBr, $br, 'reading', $niz);
		$Br = array_reverse($Br);
	}
	$Bs = array();
	if ($speaking <= $target) {

		$niz = array($targetBs);
		$Bs = roadmapB($records, $targetBs, $bs, 'speaking', $niz);
		$Bs = array_reverse($Bs);
	}
	$Bw = array();
	if ($writing <= $target) {

		$niz = array($targetBw);
		$Bw = roadmapB($records, $targetBw, $bw, 'writing', $niz);
		$Bw = array_reverse($Bw);
	}

	$roadmap .= "\n";
	$roadmap .= "B: ";
	$roadmap .= "\n\t";
	$a = new stdClass();
	$a->start = get_coursefullname($bl);
	$a->target = get_coursefullname($targetBl);
	// $a->coursestart = '';
	// $a->coursetarget = '';
	$roadmap .= get_string('listening', 'local_th_roadmap_vstep') . ": " . get_string('roadmaplevel', 'local_th_roadmap_vstep', $a) . implode(' -> ', array_map('get_coursefullname',$Bl));
	$roadmap .= "\n\t";
	$a = new stdClass();
	$a->start = get_coursefullname($br);
	$a->target = get_coursefullname($targetBr);
	$roadmap .= get_string('reading', 'local_th_roadmap_vstep') . ": " . get_string('roadmaplevel', 'local_th_roadmap_vstep', $a) . implode(' -> ', array_map('get_coursefullname', $Br));
	$roadmap .= "\n\t";
	$a = new stdClass();
	$a->start = get_coursefullname($bs);
	$a->target = get_coursefullname($targetBs);
	$roadmap .= get_string('speaking', 'local_th_roadmap_vstep') . ": " . get_string('roadmaplevel', 'local_th_roadmap_vstep', $a) . implode(' -> ', array_map('get_coursefullname', $Bs));
	$roadmap .= "\n\t";
	$a = new stdClass();
	$a->start = get_coursefullname($bw);
	$a->target = get_coursefullname($targetBw);
	$roadmap .= get_string('writing', 'local_th_roadmap_vstep') . ": " . get_string('roadmaplevel', 'local_th_roadmap_vstep', $a) . implode(' -> ', array_map('get_coursefullname', $Bw));
	$roadmap .= "\n";

	if ($target == 4) {

		$niz = array();
		$thithu = array();
		$c = roadmapC($records, $niz, $thithu);
		$roadmap .= 'C: ';
		$roadmap .= "\n\t" . get_string('roadmap', 'local_th_roadmap_vstep') .': ';
		$roadmap .= implode("\n\t" . get_string('roadmap', 'local_th_roadmap_vstep') .': ' , $c[0]);
		$roadmap .= "\n";
		$roadmap .= get_string('test', 'local_th_roadmap_vstep') . ': ';
		$roadmap .= get_string('roadmap', 'local_th_roadmap_vstep') .': ' . implode(' -> ', array_map('get_coursefullname',$c[1]));
	}
	return $roadmap;
}

/**
 * @param  $data [stdClass] record table local_th_roadmap_vstep
 * @param  $start [string] target level
 * @param  $end [string] Current level
 * @param  $niz [array] save roadmap
 * @return [array] road map
 */
function roadmapA($data, $start, $end, $niz) {
	
	if ($start == '' or $start == $end or $data[$start]->dktq == null) {
		return $niz;
	}
	$dktq = $data[$start]->dktq;
	// print_object('dktq ' . $dktq);
	foreach ($data as $key => $d) {
		if (strstr($dktq, $key) ==! null) {
			array_push($niz, $key);
			return roadmapA($data, $key, $end, $niz);
		}
	}
}

/**
 * @param  $data [stdClass] record table local_th_roadmap_vstep
 * @param  $start [string] target level
 * @param  $end [string] Current level
 * @param  $type [string] type (listening,speaking,reading,writing) column table local_th_roadmap_vstep
 * @param  $niz [array] save roadmap
 * @return [array] road map
 */
function roadmapB($data, $start, $end, $type, $niz) {

	if ($start == '' or $start == $end or strstr($data[$start]->dktq, "B") == null) {
		return $niz;
	}
	$dktq = $data[$start]->dktq;
	// print_object('dktq ' . $dktq);
	foreach ($data as $key => $d) {

		if (strstr($dktq, $key) ==! null and $d->$type == 1) {
			array_push($niz,  $key);
			return roadmapB($data, $key, $end, $type, $niz);
		} 
		elseif (strstr($dktq, $key) ==! null and $d->groups == "B" and $d->$type == 0 and $key == $dktq) {
			array_push($niz, $key);
			return roadmapB($data, $key, $end, $type, $niz);
		}
	}
}

/**
 * @param  $data [stdClass] record table local_th_roadmap_vstep
 * @param  $niz [array] save roadmapC
 * @param  $thithu [array] save roadmapThithu
 * @return [array] road map
 */
function roadmapC($data, $niz, $thithu) {
	if (empty($data)) {
		return array($niz,$thithu);
	}
	foreach ($data as $key => $d) {
		if (strstr($key, 'C') ==! null and strstr($d->dktq, 'C') ==! null) {
			array_push($niz, get_coursefullname($d->dktq).' -> '. get_coursefullname($key));
			
		} else {
			if ($d->dktq == '' and strstr($key, 'C') ==! null) {
				array_push($thithu, $key);
			}
		}
		unset($data["{$key}"]);
		return roadmapC($data, $niz, $thithu);
	}
}

function th_roadmap_vstep_print_grade_page_head(int $courseid, string $active_type, ?string $active_plugin = null, $heading = false,
       bool $return = false, $buttons = false, bool $shownavigation = true, ?string $headerhelpidentifier = null,
       ?string $headerhelpcomponent = null, ?stdClass $user = null, ?action_bar $actionbar = null, $showtitle = true) {
    global $CFG, $OUTPUT, $PAGE;

    // Put a warning on all gradebook pages if the course has modules currently scheduled for background deletion.
    require_once($CFG->dirroot . '/course/lib.php');
    if (course_modules_pending_deletion($courseid, true)) {
        \core\notification::add(get_string('gradesmoduledeletionpendingwarning', 'grades'),
            \core\output\notification::NOTIFY_WARNING);
    }

    if ($active_type === 'preferences') {
        // In Moodle 2.8 report preferences were moved under 'settings'. Allow backward compatibility for 3rd party grade reports.
        $active_type = 'settings';
    }

    // $plugin_info = grade_get_plugin_info($courseid, $active_type, $active_plugin);

    // // Determine the string of the active plugin
    // $stractive_plugin = ($active_plugin) ? $plugin_info['strings']['active_plugin_str'] : $heading;
    // $stractive_type = $plugin_info['strings'][$active_type];

    // if (!$showtitle) {
    //     $title = $PAGE->course->fullname;
    // } else if (empty($plugin_info[$active_type]->id) || !empty($plugin_info[$active_type]->parent)) {
    //     $title = $PAGE->course->fullname.': ' . $stractive_type . ': ' . $stractive_plugin;
    // } else {
    //     $title = $PAGE->course->fullname.': ' . $stractive_plugin;
    // }
    $title = $PAGE->course->fullname;

    if ($active_type == 'report') {
        $PAGE->set_pagelayout('report');
    } else {
        $PAGE->set_pagelayout('admin');
    }
    // $PAGE->set_title(get_string('grades') . ': ' . $stractive_type);
    $PAGE->set_title(get_string('name','local_th_roadmap_vstep'));
    $PAGE->set_heading($title);
    $PAGE->set_secondary_active_tab('grades');

    // if ($buttons instanceof single_button) {
    //     $buttons = $OUTPUT->render($buttons);
    // }
    // $PAGE->set_button($buttons);
    // if ($courseid != SITEID) {
    //     grade_extend_settings($plugin_info, $courseid);
    // }

    // // Set the current report as active in the breadcrumbs.
    // if ($active_plugin !== null && $reportnav = $PAGE->settingsnav->find($active_plugin, navigation_node::TYPE_SETTING)) {
    //     $reportnav->make_active();
    // }

    $returnval = $OUTPUT->header();

    if (!$return) {
        echo $returnval;
    }

    // Guess heading if not given explicitly
    if (!$heading) {
        $heading = $stractive_plugin;
    }

    if (!$showtitle) {
        $heading = '';
    }

    if ($shownavigation) {
        $renderer = $PAGE->get_renderer('core_grades');
        // If the navigation action bar is not explicitly defined, use the general (default) action bar.
        if (!$actionbar) {
            $actionbar = new general_action_bar($PAGE->context, $PAGE->url, $active_type, $active_plugin);
        }

        if ($return) {
            $returnval .= $renderer->render_action_bar($actionbar);
        } else {
            // echo $renderer->render_action_bar($actionbar);
        }
    }

    $output = '';
    // Add a help dialogue box if provided.
    if (isset($headerhelpidentifier)) {
        $output = $OUTPUT->heading_with_help($heading, $headerhelpidentifier, $headerhelpcomponent);
    } else {
        if (isset($user)) {
            $renderer = $PAGE->get_renderer('core_grades');
            $output = $OUTPUT->heading($renderer->user_heading($user, $courseid));
        } else {
            $output = $OUTPUT->heading($heading);
        }
    }

    if ($return) {
        $returnval .= $output;
    } else {
        echo $output;
    }

    $returnval .= print_natural_aggregation_upgrade_notice($courseid, context_course::instance($courseid), $PAGE->url,
        $return);

    if ($return) {
        return $returnval;
    }
}


defined('MOODLE_INTERNAL') || die();

function th_roadmap_vstep_csv_parse($list) {
	if (empty($list)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $list);
		array_pop($rawlines);
		$result = array();
	
		$arr_fields = explode(',', $rawlines[0]);
		$arr_fields = array_map('trim', $arr_fields);
		$arr_fields = array_map('strtolower', $arr_fields);
		$rawlines = array_slice($rawlines, 1);
		
		foreach ($rawlines as $key => $rawline) {
			$row_arr = explode(',', $rawline);
			$row_arr = array_map('trim', $row_arr);
			$data = array();
			foreach ($row_arr as $key => $row) {
				$data[$arr_fields[$key]] = $row;
			}
			$result[] = $data;
		}

		// print_object($result);
		// exit;
		return $result;
	}
}

function th_roadmap_vstep_display_table($data) {
	
	$table = new html_table();
	$table->head = array('STT', 'Course code', 'KNLNN', 'Listening', 'Speaking', 'Reading', 'Writing', 'DKTQ', 'Groups');
	$table->attributes = array('class' => 'th_roadmap_vstep', 'border' => '1');
	$stt = 0;
	foreach ($data as $k => $d) {
		$d = (object) $d;
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$cell = new html_table_cell($d->coursecode);
		$row->cells[] = $cell;

		$cell = new html_table_cell($d->knlnn);
		$row->cells[] = $cell;
		$cell = new html_table_cell($d->listening);
		$row->cells[] = $cell;

		$cell = new html_table_cell($d->speaking);
		$row->cells[] = $cell;
		$cell = new html_table_cell($d->reading);
		$row->cells[] = $cell;
		$cell = new html_table_cell($d->writing);
		$row->cells[] = $cell;
		$cell = new html_table_cell($d->dktq);
		$row->cells[] = $cell;
		$cell = new html_table_cell($d->groups);
		$row->cells[] = $cell;

		$table->data[] = $row;
	}

	$html = html_writer::table($table);
	return $html;
}

function th_roadmap_vstep_check_data($data) {

	$ok = array();
	$error = array();

	foreach ($data as $k => $d) {
		$check = 0;
		foreach ($d as $key => $value) {
			if ($key == 'coursecode' or $key == 'dktq' or $key == 'groups') {
				if (strlen($value) <= 255) {
					$check += 1;
				}
			}

			if ($key == 'knlnn') {
				if (strlen($value) <= 10 and intval($value)) {
					$check += 1;
				}
			}

			if ($key == 'listening' or $key == 'speaking' or $key == 'reading' or $key == 'writing') {
				if ((strlen($value) == 1 and intval($value)) or (strlen($value) == 0 and intval($value) == 0)) {
					$check += 1;
				}
			}		
		}

		if ($check == 8) {
			$ok[] = $d;
		}
		else {
			$error[] = $d;
		}
		// print_object($check);
		// exit;
	}

	return array('ok' => $ok, 'error' => $error);
}