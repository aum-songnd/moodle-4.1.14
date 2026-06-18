<?php

defined('MOODLE_INTERNAL') || die();
require_once $CFG->dirroot . '/enrol/locallib.php';

function th_check_bulk_enrol_csv(array $lines): stdClass {
    global $DB;

    $result = new stdClass();
    $result->user_enroled = []; // Dữ liệu hợp lệ
    $result->error_messages = []; // Dữ liệu lỗi
    $result->validfound = 0;

    foreach ($lines as $index => $line) {
        if ($index === 0 || trim($line) === '') continue; // Bỏ dòng tiêu đề và dòng trống

        $fields = str_getcsv($line);
        $linenum = $index + 1;

        if (count($fields) < 6) {
            $result->error_messages[] = [
                'line' => $linenum,
                'course' => '',
                'username' => '',
                'role' => '',
                'error' => 'Thiếu cột dữ liệu'
            ];
            continue;
        }

        list($courseshortname, $username, $roleshortname, $status, $timestartstr, $timeendstr) = array_map('trim', $fields);
        $errors = [];

        $course = $DB->get_record('course', ['shortname' => $courseshortname]);
        $user = $DB->get_record('user', ['username' => $username]);
        $role = $DB->get_record('role', ['shortname' => $roleshortname]);

        if (!in_array((int)$status, [0, 1, 2], true)) {
            $errors[] = get_string('invalidstatus', 'local_th_bulk_enrolment_manager', $status);
        }
        
        if ($status == 1) {
            if (!$role) {
                $errors[] = get_string('invalidrole', 'local_th_bulk_enrolment_manager', $roleshortname);
            } else {
                if (!is_course_role($role->id)) {
                    $errors[] = get_string('notcourselevelrole', 'local_th_bulk_enrolment_manager');
                }
            }
        
            $timestart = parse_custom_datetime($timestartstr);
            $timeend = empty(trim($timeendstr)) ? 0 : parse_custom_datetime($timeendstr);
        
            if ($timestart === false) {
                $errors[] = get_string('invalidtimestart', 'local_th_bulk_enrolment_manager', $timestartstr);
            }
        
            if ($timeend === false) {
                $errors[] = get_string('invalidtimeend', 'local_th_bulk_enrolment_manager', $timeendstr);
            }
        
            if ($timestart !== false && $timeend !== false && $timeend <= $timestart && $timeend > 0) {
                $errors[] = get_string('endbeforestart', 'local_th_bulk_enrolment_manager');
            }
        } else {
            if (!empty($roleshortname)) {
                $errors[] = get_string('roleshouldbeempty', 'local_th_bulk_enrolment_manager');
            }
        
            if (!empty($timestartstr)) {
                $errors[] = get_string('timestartshouldbeempty', 'local_th_bulk_enrolment_manager');
            }
        
            if (!empty($timeendstr)) {
                $errors[] = get_string('timeendshouldbeempty', 'local_th_bulk_enrolment_manager');
            }
        }        

        if (!$course) {
            $errors[] = get_string('coursenotfound', 'local_th_bulk_enrolment_manager', $courseshortname);
        }
        
        if (!$user) {
            $errors[] = get_string('usernotfound', 'local_th_bulk_enrolment_manager', $username);
        }        


        if($course && $user){
            $instance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
            $enrolled = $DB->record_exists('user_enrolments', ['userid' => $user->id, 'enrolid' => $instance->id]);

            if(!$enrolled && $status != 1) {
                $errors[] = get_string('usernotenrolled', 'local_th_bulk_enrolment_manager');
            }
        }
        
        if ($errors) {
            if($status == 1){
                $result->error_messages[] = [
                    'line' => $linenum,
                    'course' => $courseshortname,
                    'username' => $username,
                    'role' => $roleshortname,
                    'status' => $status,
                    'error' => implode('; ', $errors)
                ];
            } else {
                $result->error_messages[] = [
                    'line' => $linenum,
                    'course' => $courseshortname,
                    'username' => $username,
                    'status' => $status,
                    'error' => implode('; ', $errors)
                ];
            }
        } else {
            if($status == 1){
                $result->user_enroled[] = (object) [
                    'id' => $user->id,
                    'courseid' => $course->id,
                    'roleid' => $role->id,
                    'status' => (int)$status,
                    'timestart' => $timestart,
                    'timeend' => $timeend,
                    'username' => $username,
                    'userfullname' => $user->firstname . ' ' . $user->lastname,
                    'coursename' => $course->fullname,
                    'shortname' => $course->shortname,
                    'rolename' => $role->shortname
                ];
            } else {
                $result->user_enroled[] = (object) [
                    'id' => $user->id,
                    'courseid' => $course->id,
                    'status' => (int)$status,
                    'username' => $username,
                    'userfullname' => $user->firstname . ' ' . $user->lastname,
                    'coursename' => $course->fullname,
                    'shortname' => $course->shortname
                ];
            }
            
            $result->validfound = $result->validfound + 1;
        }
    }

    return $result;
}

function parse_date($str) {
    $str = trim($str);
    // Nếu chuỗi là dạng dd/mm/yyyy → đổi sang timestamp
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $str, $matches)) {
        return strtotime("{$matches[3]}-{$matches[2]}-{$matches[1]}"); // yyyy-mm-dd
    }

    // Thử trực tiếp
    return strtotime($str);
}

function parse_custom_datetime($str) {
    // Kiểm tra định dạng bằng regex
    if (!preg_match('/^(\d{2})h(\d{2})p(\d{2})s-(\d{2})\/(\d{2})\/(\d{4})$/', $str, $matches)) {
        return false; // Không đúng định dạng
    }

    // Lấy các phần tử thời gian
    $hour = (int)$matches[1];
    $minute = (int)$matches[2];
    $second = (int)$matches[3];
    $day = (int)$matches[4];
    $month = (int)$matches[5];
    $year = (int)$matches[6];

    // Tạo timestamp
    return mktime($hour, $minute, $second, $month, $day, $year);
}

function is_valid_ddmmyyyy($date) {
    print_object($date);
    
    // Kiểm tra theo regex định dạng dd/mm/yyyy
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
        // Dùng checkdate để xác minh ngày tháng hợp lệ
        return checkdate($month, $day, $year);
    }
    return false;
}

function th_display_table_error(array $errorrows): string {
    $table = new html_table();
    $table->head = [
        get_string('STT', 'local_th_bulk_enrolment_manager'),
        get_string('Line', 'local_th_bulk_enrolment_manager'),
        get_string('Course', 'local_th_bulk_enrolment_manager'),
        get_string('Username', 'local_th_bulk_enrolment_manager'),
        get_string('role_col', 'local_th_bulk_enrolment_manager'),
        get_string('Status', 'local_th_bulk_enrolment_manager'),
        get_string('Error', 'local_th_bulk_enrolment_manager'),
    ];
    
    $table->attributes['class'] = 'generaltable table table-bordered table-hover';
    $table->id = 'bulk_enrol_error_table';

    $stt = 1;
    foreach ($errorrows as $e) {
        // Xác định trạng thái
        switch ((int) $e['status']) {
            case 1:
                $status = get_string('enroluser', 'local_th_bulk_enrolment_manager'); break;
            case 0:
                $status = get_string('unenroluser', 'local_th_bulk_enrolment_manager'); break;
            case 2:
                $status = get_string('suspenduser', 'local_th_bulk_enrolment_manager'); break;
            default:
                $status = get_string('unknownstatus', 'local_th_bulk_enrolment_manager');
        }

        $row = new html_table_row();
        $row->cells[] = new html_table_cell($stt++);
        $row->cells[] = new html_table_cell($e['line']);
        $row->cells[] = new html_table_cell(s($e['course'] ?? '-'));
        $row->cells[] = new html_table_cell(s($e['username'] ?? '-'));
        $row->cells[] = new html_table_cell(s($e['role'] ?? '-'));
        $row->cells[] = new html_table_cell(s($status));
        $row->cells[] = new html_table_cell(nl2br(s($e['error'] ?? '-')));

        $table->data[] = $row;
    }

    return html_writer::table($table);
}

function th_display_table_enrol(array $validrows): string {
    $table = new html_table();
    
    $table->head = [
        get_string('STT', 'local_th_bulk_enrolment_manager'),
        get_string('Fullname', 'local_th_bulk_enrolment_manager'),
        get_string('Username', 'local_th_bulk_enrolment_manager'),
        get_string('Coursefullname', 'local_th_bulk_enrolment_manager'),
        get_string('Courseidnumber', 'local_th_bulk_enrolment_manager'),
        get_string('role_col', 'local_th_bulk_enrolment_manager'),
        get_string('Accesslimit', 'local_th_bulk_enrolment_manager'),
        get_string('Status', 'local_th_bulk_enrolment_manager'),
    ];

    $table->attributes['class'] = 'generaltable table table-bordered table-hover';
    $table->id = 'bulk_enrol_valid_table';

    $stt = 1;
    foreach ($validrows as $row) {
        if($row->status == 1){
            $timestart = date('d/m/Y, H:i:s', $row->timestart);
            $timeend = date('d/m/Y, H:i:s', $row->timeend);

			if($row->timeend == 0) {
				$timeinfo = get_string('timeperiod_start', 'local_th_bulk_enrolment_manager', ['timestart' => $timestart]);
			} else {
				$timeinfo = get_string('timeperiod_start', 'local_th_bulk_enrolment_manager', ['timestart' => $timestart]) . get_string('timeperiod_end', 'local_th_bulk_enrolment_manager', ['timeend' => $timeend]);
			}
        } else {
            $timeinfo = "-";
        }
        
        // Xác định trạng thái
        switch ((int) $row->status) {
            case 1:
                $status = get_string('enroluser', 'local_th_bulk_enrolment_manager'); break;
            case 0:
                $status = get_string('unenroluser', 'local_th_bulk_enrolment_manager'); break;
            case 2:
                $status = get_string('suspenduser', 'local_th_bulk_enrolment_manager'); break;
            default:
                $status = get_string('unknownstatus', 'local_th_bulk_enrolment_manager');
        }

        $table->data[] = new html_table_row([
            new html_table_cell($stt++),
            new html_table_cell(s($row->userfullname ?? '-')),
            new html_table_cell(s($row->username ?? '-')),
            new html_table_cell(s($row->coursename ?? '-')),
            new html_table_cell(s($row->shortname ?? '-')),
            new html_table_cell(s($row->rolename ?? '-')),
            new html_table_cell($timeinfo),
            new html_table_cell($status)
        ]);
    }

    return html_writer::table($table);
}

function local_th_bulk_enrolment_manager_controls(context $context, moodle_url $currenturl)
{
	$tabs       = array();
	$currenttab = 'view';
	$view       = new moodle_url('/local/th_bulk_enrolment_manager/view.php');

	if (has_capability('local/th_bulk_enrolment_manager:view', $context)) {
		$addurl = new moodle_url('/local/th_bulk_enrolment_manager/view.php');
		$tabs[] = new tabobject('view', $addurl, get_string('enrol', 'local_th_bulk_enrolment_manager'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view';
		}
	}

	if (has_capability('local/th_bulk_enrolment_manager:view', $context)) {
		$addurl = new moodle_url('/local/th_bulk_enrolment_manager/enrol_csv.php');
		$tabs[] = new tabobject('view1', $addurl, get_string('enrolbycsv', 'local_th_bulk_enrolment_manager'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view1';
		}
	}

	if (has_capability('local/th_bulk_enrolment_manager:view', $context)) {
		$addurl = new moodle_url('/local/th_bulk_enrolment_manager/bulk_enrol_roles.php');
		$tabs[] = new tabobject('view2', $addurl, get_string('title2', 'local_th_bulk_enrolment_manager'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view2';
		}
	}

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

function is_course_role($roleid) {
    global $DB;

    // 50 = CONTEXT_COURSE
    return $DB->record_exists('role_context_levels', [
        'roleid' => $roleid,
        'contextlevel' => CONTEXT_COURSE
    ]);
}

// -----------------------================GỘP THÊM CỦA BLOCKS================-------------------------

function th_bulkenrol_csv_parse_emails($emails) {
	if (empty($emails)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $emails);
		$result = array();
		foreach ($rawlines as $rawline) {
			$result[] = trim($rawline);
		}
		return $result;
	}
}

function th_bulkenrol_csv_check_user_mails($emailstextfield) {
	global $DB;

	$checkedemails = new stdClass();
	$checkedemails->error_messages = array();
	$checkedemails->moodleusers_for_email = array();
	$checkedemails->user_enroled = array();
	$checkedemails->validemailfound = 0;

	if (!empty($emailstextfield)) {

		$emailslines = th_bulkenrol_csv_parse_emails($emailstextfield);

		$context = null;

		// Process emails from textfield.
		foreach ($emailslines as $emailline) {

			$error = '';

			$line = trim($emailline);

			if (substr_count($line, ',') == 3) {
				$line_arr = explode(",", $line);
				$shortname_course = $line_arr[1];
				$sql = "SELECT id FROM {course} WHERE shortname = '$shortname_course'";
				if ($DB->record_exists_sql($sql) == 1) {
					$emailline = $line_arr[0];
					$stt = $line_arr[3];
					$courseid = $DB->get_field_sql($sql);
				} else {
					$emailline = $line_arr[0];
					$stt = $line_arr[3];

					//Show error course shortname
					$a = new stdClass();
					$a->line = $stt;
					$a->content = $shortname_course;
					$error = get_string('error_no_shortnamecourse', 'local_th_bulk_enrolment_manager', $a);
					$error_messages = $checkedemails->error_messages;
					if (!in_array($error, $error_messages)) {
						$checkedemails->error_messages[] = $error;
					}

				}
				$roleid = $line_arr[2];

			}

			// Check number of emails in current row/line.
			$emailsinlinecnt = substr_count($emailline, '@');
			// No email in row/line.
			if ($emailsinlinecnt == 0) {
				if ($DB->record_exists_sql($sql) == 1) {
					$a = new stdClass();
					$a->line = $stt;
					$a->content = $emailline;
					$error = get_string('error_no_email', 'local_th_bulk_enrolment_manager', $a);
					$checkedemails->error_messages[] = $error;
				}

				// $a = new stdClass();
				// $a->line = $stt;
				// $a->content = $emailline;
				// $error = get_string('error_no_email', 'block_th_bulkenrol_course', $a);
				// $checkedemails->error_messages[] = $error;

				// One email in row/line.
			} else if ($emailsinlinecnt == 1) {
				$email = $emailline;
				$a = new stdClass();
				$a->line = $stt;
				$a->content = $emailline;
				if ($DB->record_exists_sql($sql) == 1) {
					if ($DB->record_exists_sql("SELECT * FROM {user} WHERE email = '$email'") != 1) {
						$error = get_string('error_no_record_found_for_email', 'local_th_bulk_enrolment_manager', $a);
						$checkedemails->error_messages[] = $error;
					} else {
						$userrecord = $DB->get_record('user', array('email' => $email));
						$userisenrolled = th_bulkenrol_csv_check_email($userrecord->id, $courseid, $roleid, $stt);
						if (empty($userisenrolled)) {
							$checkedemails->validemailfound += 1;
							$userrecord->courseid = $courseid;
							$userrecord->roleid = $roleid;
							$checkedemails->user_enroled[] = $userrecord;
						} else {
							$userrecord->courseid = $courseid;
							$userrecord->roleid = $roleid;
							$checkedemails->moodleusers_for_email[] = $userrecord;
						}

					}

				}
				// else {
				// 	if ($DB->record_exists_sql("SELECT * FROM {user} WHERE email = '$email'") != 1){
				// 		$error = get_string('error_no_record_found_for_email', 'block_th_bulkenrol_course', $a);
				// 		$checkedemails->error_messages[] = $error;
				// 	}
				// }
			}
		}
	}

	return $checkedemails;
}

function th_bulkenrol_csv_check_email($userid, $courseid, $roleid, $stt) {
	global $DB;

	$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);

	$sql = "SELECT * FROM {user_enrolments} WHERE userid = $userid AND enrolid = $instance->id";
	$sql1 = "SELECT id FROM {context} WHERE instanceid = $courseid AND contextlevel = 50";
	$contextid = $DB->get_record_sql($sql1);
	$sql2 = "SELECT * FROM {role_assignments} WHERE $userid AND roleid = $roleid AND contextid = $contextid->id";

	if ($DB->record_exists_sql($sql) == 1 && $DB->record_exists_sql($sql2) == 1) {
		$userisenrolled = 1;
	} else {
		$userisenrolled = 0;
	}
	return $userisenrolled;
}

function th_get_content($contents) {
	$roleid_gvcm = get_config('local_th_bulk_enrolment_manager', 'role1');
	$roleid_gvcn = get_config('local_th_bulk_enrolment_manager', 'role2');
	$roleid_qlht = get_config('local_th_bulk_enrolment_manager', 'role3');

	$max = count($contents) - 1;
	for ($i = 1; $i < $max; ++$i) {
		$line = $contents[$i];
		$lines = explode(",", $line);
		$shortname = $lines[0];

		$gvcm = $lines[1];
		$gvcn = $lines[2];
		$qlht = $lines[3];

		$gvcm_pos = substr_count($gvcm, ';');
		if ($gvcm_pos >= 1) {
			$gvcm_lines = explode(";", $gvcm);
			foreach ($gvcm_lines as $k => $gvcm_line) {
				$arr1 = [$gvcm_line, $shortname, $roleid_gvcm, $i + 1];
				$userfile[] = implode(',', $arr1);
			}
		} else {
			$arr1 = [$gvcm, $shortname, $roleid_gvcm, $i + 1];
			$userfile[] = implode(',', $arr1);
		}

		$gvcn_pos = substr_count($gvcn, ';');
		if ($gvcn_pos >= 1) {
			$gvcn_lines = explode(";", $gvcn);
			foreach ($gvcn_lines as $k => $gvcn_line) {
				$arr2 = [$gvcn_line, $shortname, $roleid_gvcn, $i + 1];
				$userfile[] = implode(',', $arr2);
			}
		} else {
			$arr2 = [$gvcn, $shortname, $roleid_gvcn, $i + 1];
			$userfile[] = implode(',', $arr2);
		}

		$qlht_pos = substr_count($qlht, ';');
		if ($qlht_pos >= 1) {
			$qlht_lines = explode(";", $qlht);
			foreach ($qlht_lines as $k => $qlht_line) {
				$arr3 = [$qlht_line, $shortname, $roleid_qlht, $i + 1];
				$userfile[] = implode(',', $arr3);
			}
		} else {
			$arr3 = [$qlht, $shortname, $roleid_qlht, $i + 1];
			$userfile[] = implode(',', $arr3);
		}
	}

	$email = implode(PHP_EOL, $userfile);
	$emails = trim($email);
	return $emails;
}

function th_display_table_enrol2($enroleds, $no_enroleds) {
	global $DB;
	$table = new html_table();
	$table->head = array(get_string('STT2', 'local_th_bulk_enrolment_manager'), get_string('Email', 'local_th_bulk_enrolment_manager'), get_string('Firstname', 'local_th_bulk_enrolment_manager'), get_string('Lastname', 'local_th_bulk_enrolment_manager'), get_string('Course2', 'local_th_bulk_enrolment_manager'), get_string('Status2', 'local_th_bulk_enrolment_manager'));
	$stt = 0;
	foreach ($enroleds as $k => $enroled) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($enroled->email);
		$row->cells[] = $cell;
		$cell = new html_table_cell($enroled->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($enroled->lastname);
		$row->cells[] = $cell;

		$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$enroled->courseid'");
		$link = new moodle_url('/user/index.php', ['id' => $enroled->courseid]);
		$link_edit = html_writer::link($link, $course_name);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;

		$role_name = $DB->get_field_sql("SELECT shortname FROM {role} WHERE id = '$enroled->roleid'");
		$message = get_string('user_enroled_already', 'local_th_bulk_enrolment_manager', $role_name);
		$cell = new html_table_cell("<p style='color: #fff'>$message</p>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-danger");
		$table->data[] = $row;
	}

	foreach ($no_enroleds as $k => $no_enroled) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($no_enroled->email);
		$row->cells[] = $cell;
		$cell = new html_table_cell($no_enroled->firstname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($no_enroled->lastname);
		$row->cells[] = $cell;

		$course_name = $DB->get_field_sql("SELECT fullname FROM {course} WHERE id = '$no_enroled->courseid'");
		$link = new moodle_url('/user/index.php', ['id' => $no_enroled->courseid]);
		$link_edit = html_writer::link($link, $course_name);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;

		$role_name = $DB->get_field_sql("SELECT shortname FROM {role} WHERE id = '$no_enroled->roleid'");
		$message = get_string('user_enroled_yes', 'local_th_bulk_enrolment_manager') . ' ' . $role_name;
		$cell = new html_table_cell("<p style='color: #fff'>$message</p>");
		$row->cells[] = $cell;
		$cell->attributes = array('class' => "bg-success");
		$table->data[] = $row;
	}

	$table->attributes = array('class' => 'th_enrol_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);
	return $html;
}

function th_display_table_error2($errors) {
	$table1 = new html_table($errors);
	$table1->head = array(get_string('STT2', 'local_th_bulk_enrolment_manager'), get_string('Hints2', 'local_th_bulk_enrolment_manager'));
	$stt = 0;
	foreach ($errors as $k => $error) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($error);
		$row->cells[] = $cell;
		$table1->data[] = $row;
	}
	$html1 = html_writer::table($table1);
	return $html1;
}


