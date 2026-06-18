<?php

defined('MOODLE_INTERNAL') || die();

function th_parse_groups($list_groups)
{
	if (empty($list_groups)) {
		return array();
	} else {
		$rawlines = explode(PHP_EOL, $list_groups);
		$result = array();
		foreach ($rawlines as $rawline) {
			$result[] = trim($rawline);
		}
		return $result;
	}
}

function th_enrol_groups_get_content($contents)
{
    $list_new = array();
    // Bắt đầu từ 1 để bỏ qua dòng tiêu đề
    for ($i = 1; $i < count($contents); ++$i) {
        if (trim($contents[$i]) !== '') { // Chỉ lấy các dòng có dữ liệu
            $list_new[] = $contents[$i];
        }
    }
    return $list_new;
}

function th_check_groups($list_groups, $identifier_option = 'email')
{
	global $DB, $CFG;

	$checkedgroups = new stdClass();
	$checkedgroups->error_messages = array();
	$checkedgroups->enrol_groups = array();
	$checkedgroups->valid_groups_found = 0;

	if (!empty($list_groups)) {

		foreach ($list_groups as $k => $list_group) {
			$line = $k + 2;
			$list_groups_arr = explode(",", $list_group);
			$shortname = trim($list_groups_arr[0]);
			$group_name = trim($list_groups_arr[1]);

			// Xóa triệt để khoảng trắng và các ký tự ẩn (BOM, Zero-width space)
            $identifier_value = trim($list_groups_arr[2]);
            $identifier_value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $identifier_value);

			$identifier_value = trim($list_groups_arr[2]); // Cột 3 có thể là email hoặc mã HV
			
            $course = $DB->get_record('course', array('shortname' => $shortname));
			
			if (empty($course)) {
				$a = new stdClass();
				$a->shortname = $shortname;
				$a->line = $line;
				$checkedgroups->error_messages[] = get_string('error_no_course_found', 'block_th_bulk_enrol_groups', $a);
				//$checkedgroups->error_messages[] = "Không tìm thấy khóa học nào có shortname (<strong>$shortname</strong>). Vui lòng kiểm tra lại hàng $line";
			} else {
				$course_id = $course->id;
				$group = $DB->get_record_sql("SELECT * FROM {groups} WHERE name = '$group_name' AND courseid = '$course_id'");
				$href = $CFG->wwwroot . "/group/index.php?id=$course_id";
				$link = "<a href='$href'>$course->fullname,$course->shortname</a>";
				
				if (empty($group)) {
                    $a = new stdClass();
					$a->groupname = $group_name;
					$a->link = $link;
					$a->line = $line;
					$checkedgroups->error_messages[] = get_string('error_no_group_found', 'block_th_bulk_enrol_groups', $a);
					//$checkedgroups->error_messages[] = "Không tìm thấy nhóm nào có tên (<strong>$group_name</strong>) trong môn học (<strong>$link</strong>). Vui lòng kiểm tra lại hàng $line";
				} else {
					// Logic tìm user phụ thuộc vào tùy chọn
                    $user = false;
					if ($identifier_option === 'email') {
                        $user = $DB->get_record('user', array('email' => $identifier_value, 'deleted' => 0));
					
                    } else {
                    $student_code_fields = get_config('local_thlib', 'studentcodeshortname');
                    
                    $shortnames_arr = explode(',', $student_code_fields);
                    $shortnames_arr = array_map('trim', $shortnames_arr);

                    // get_in_or_equal để tự động sinh câu lệnh IN (...) an toàn
                    list($in_sql, $in_params) = $DB->get_in_or_equal($shortnames_arr, SQL_PARAMS_NAMED, 'shortname');

                    $sql = "SELECT u.*
                            FROM {user} u
                            JOIN {user_info_data} uid ON uid.userid = u.id
                            JOIN {user_info_field} uif ON uif.id = uid.fieldid
                            WHERE uif.shortname $in_sql AND uid.data = :data AND u.deleted = 0";

                    $params = array_merge($in_params, array('data' => $identifier_value));

                    $user = $DB->get_record_sql($sql, $params);
                }
					
					if (empty($user)) {
                        $a = new stdClass();
                        $a->line = $line;
                        
                        if ($identifier_option === 'email') {
                            $a->email = $identifier_value;
                            $checkedgroups->error_messages[] = get_string('error_no_user_found', 'block_th_bulk_enrol_groups', $a);
                        } else {
                            $a->code = $identifier_value; // Dùng biến $a->code cho logic mã học viên
                            $checkedgroups->error_messages[] = get_string('error_no_user_found_by_code', 'block_th_bulk_enrol_groups', $a);
                        }
						//$checkedgroups->error_messages[] = "Không tìm thấy người dùng nào có email (<strong>$email</strong>).Vui lòng kiểm tra lại hàng $line";
					} else {
						$context = context_course::instance($course_id, MUST_EXIST);
						$userenroled = is_enrolled($context, $user->id);
						if(empty($userenroled)){
							$a = new stdClass();
							$a->link = $link;
							$a->line = $line;
							if ($identifier_option === 'email') {
								$a->email = $identifier_value;
								$checkedgroups->error_messages[] = get_string('error_no_user_in_course', 'block_th_bulk_enrol_groups', $a);
							} else {
								$a->code = $identifier_value; // Dùng biến $a->code cho logic mã học viên
								$checkedgroups->error_messages[] = get_string('error_no_user_in_course_code', 'block_th_bulk_enrol_groups', $a);
							}
							//$checkedgroups->error_messages[] = "Không tìm thấy người dùng nào có email (<strong>$email</strong>) trong môn học (<strong>$link</strong>). Vui lòng kiểm tra lại hàng $line";
						} else {
                            if (!groups_is_member($group->id, $user->id)) {
								$checkedgroups->valid_groups_found += 1;
								$enrol_groups = new stdClass();
								$enrol_groups->group_id = $group->id;
								$enrol_groups->group_name = $group->name;
								$enrol_groups->user_id = $user->id;
								$enrol_groups->user_name = fullname($user); // Dùng hàm fullname theo chuẩn Moodle
								$enrol_groups->course_id = $course->id;
								$enrol_groups->course_name = $course->fullname;
								$checkedgroups->enrol_groups[] = $enrol_groups;
							} else {
								$a = new stdClass();
								$a->email = $identifier_value;
								$a->group_name = $group_name;
								$a->link = $link;
								$a->line = $line;
								if ($identifier_option === 'email') {
									$a->email = $identifier_value;
									$checkedgroups->error_messages[] = get_string('error_user_already_in_group', 'block_th_bulk_enrol_groups', $a);
								} else {
									$a->code = $identifier_value; // Dùng biến $a->code cho logic mã học viên
									$checkedgroups->error_messages[] = get_string('error_user_already_in_group_code', 'block_th_bulk_enrol_groups', $a);
								}
								//$checkedgroups->error_messages[] = "Người dùng có email (<strong>$email</strong>) đã tồn tại trong nhóm (<strong>$group_name</strong>) trong môn học (<strong>$link</strong>). Vui lòng kiểm tra lại hàng $line";
							}
						}
					}
				}
			}
		}
	}
	return $checkedgroups;
};

function th_display_table_enrol_groups($data_enrol_group)
{
	global $DB;
	$table = new html_table();
	$table->head = array(
		get_string('stt', 'block_th_bulk_enrol_groups'), 
		get_string('coursename', 'block_th_bulk_enrol_groups'), 
		get_string('group', 'block_th_bulk_enrol_groups'), 
		get_string('studentname', 'block_th_bulk_enrol_groups'), 
		get_string('status', 'block_th_bulk_enrol_groups'));
	$stt = 0;
	foreach ($data_enrol_group as $k => $data) {
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;

		$link = new moodle_url('/course/view.php', ['id' => $data->course_id]);
		$link_edit = html_writer::link($link, $data->course_name);
		$cell = new html_table_cell($link_edit);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->group_name);
		$row->cells[] = $cell;
		$cell = new html_table_cell($data->user_name);
		$row->cells[] = $cell;
		$cell = new html_table_cell(get_string('will_be_added','block_th_bulk_enrol_groups'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}
	$html = html_writer::table($table);
	return $html;
}

function th_bulk_enrol_groups_display_table_error($errors)
{
	$table1 = new html_table($errors);
	$table1->head = array(get_string('stt', 'block_th_bulk_enrol_groups'), get_string('suggest', 'block_th_bulk_enrol_groups'));
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
