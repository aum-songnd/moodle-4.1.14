<?php

defined('MOODLE_INTERNAL') || die();

function th_bulk_send_message($date, $sender, $receiver, $subject, $body) {
	global $DB;
	$dataobjects = new stdClass();
	$dataobjects->date = $date;
	$dataobjects->sender = $sender;
	$dataobjects->receiver = $receiver;
	$dataobjects->subject = $subject;
	$dataobjects->body = $body;
	$DB->insert_record('th_bulk_send_message', $dataobjects, false);
}

function th_send_message_check_user($users, $option) {
	global $DB;

	$checkedusers = new stdClass();
	$checkedusers->error_messages = array();
	$checkedusers->user_send_message = array();
	$checkedusers->validuserfound = 0;

	if (!empty($users)) {

		$ma_hoc_vien1 = get_config('local_th_bulk_send_message', 'ma_hoc_vien');
		$ma_hoc_vien2 = get_config('local_th_bulk_send_message', 'ma_hoc_vien2');

		foreach ($users as $k => $user) {

			$info_hv = trim($user[0][0]);
			$chu_de = trim($user[0][1]);
			$noi_dung = trim($user[0][2]);
			$row = trim($user[1]);

			if (!empty($info_hv)) {

				if($option == 1){
					$user = $DB->get_record_sql("SELECT u.* FROM {user_info_field} as i, {user_info_data} as d, {user} as u WHERE (i.shortname = '$ma_hoc_vien1' OR i.shortname = '$ma_hoc_vien2') AND i.id = d.fieldid AND d.data = '$info_hv' AND d.userid = u.id");
				} else {
					$user = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '$info_hv'");
				}		

				if ($user) {
					$checkedusers->validuserfound += 1;

					$user_send = new stdClass();
					$user_send->ma_hv = $info_hv;
					$user_send->user = $user;
					$user_send->chu_de = $chu_de;
					$user_send->noi_dung = text_to_html("<h4>$chu_de</h4>") . '<br>' . text_to_html($noi_dung);
					$user_send->row = $row;
					$checkedusers->user_send_message[] = $user_send;
				} else {
					if ($option == 1) {
						$error = get_string('user_not_found_by_id', 'local_th_bulk_send_message', ['info' => $info_hv, 'row' => $row]);
					} else {
						$error = get_string('user_not_found_by_email', 'local_th_bulk_send_message', ['info' => $info_hv, 'row' => $row]);
					}					
					$checkedusers->error_messages[] = $error;
				}
			} else {
				if ($option == 1) {
					$error = get_string('student_id_not_found', 'local_th_bulk_send_message', $row);
				} else {
					$error = get_string('email_not_found', 'local_th_bulk_send_message', $row);
				}				
				$checkedusers->error_messages[] = $error;
			}
		}
	}

	return $checkedusers;
}

function th_send_message_display_table_error($errors) {
	$table1 = new html_table($errors);
	$table1->head = array(
		get_string('stt', 'local_th_bulk_send_message'), 
		get_string('hints', 'local_yourpllocal_th_bulk_send_messageugin')
	);
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

function th_display_table_send_message($user_send_message) {
	global $DB, $USER;
	$table = new html_table();
	$table->head = array(
		get_string('stt', 'local_th_bulk_send_message'),
		get_string('student', 'local_th_bulk_send_message'),
		get_string('sender', 'local_th_bulk_send_message'),
		get_string('receiver', 'local_th_bulk_send_message'),
		get_string('subject', 'local_th_bulk_send_message'),
		get_string('content', 'local_th_bulk_send_message'),
		get_string('status', 'local_th_bulk_send_message')
	);
	$stt = 0;

	foreach ($user_send_message as $k => $user_send) {

		$user = $user_send->user;
		$stt = $stt + 1;
		$row = new html_table_row();
		$cell = new html_table_cell($stt);
		$row->cells[] = $cell;
		$cell = new html_table_cell($user_send->ma_hv);
		$row->cells[] = $cell;
		$cell = new html_table_cell($USER->firstname . ' ' . $USER->lastname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($user->firstname . ' ' . $user->lastname);
		$row->cells[] = $cell;
		$cell = new html_table_cell($user_send->chu_de);
		$row->cells[] = $cell;
		$cell = new html_table_cell(text_to_html($user_send->noi_dung));
		$row->cells[] = $cell;
		$cell = new html_table_cell(get_string('message_will_be_sent', 'local_th_bulk_send_message'));
		$row->cells[] = $cell;
		$table->data[] = $row;
	}

	$table->attributes = array('class' => 'th_send_message_table', 'border' => '1');
	$table->attributes['style'] = "width: 100%; text-align:center;";
	$html = html_writer::table($table);
	return $html;
}

function th_check_full_name_user($userid) {
	global $DB;

	$user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $userid");
	$user_name = $user->firstname . ' ' . $user->lastname;

	return $user_name;
}

function local_th_send_message_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'view';
	$view = new moodle_url('/local/th_bulk_send_message/view.php');

	if (has_capability('local/th_bulk_send_message:view', $context)) {
		$addurl = new moodle_url('/local/th_bulk_send_message/th_log_send_message.php');
		$tabs[] = new tabobject('log', $addurl, get_string('sent_messages_list', 'local_th_bulk_send_message'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'log';
		}
	}

	if (has_capability('local/th_bulk_send_message:view', $context)) {
		$addurl = new moodle_url('/local/th_bulk_send_message/view.php');
		$tabs[] = new tabobject('view', $addurl, get_string('bulk_send_message', 'local_th_bulk_send_message'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view';
		}
	}

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

function local_th_bulk_send_message_get_studentcode(int $userid): string {
    global $DB;

    $fieldshortname = get_config('local_th_bulk_send_message', 'ma_hoc_vien');
    if (empty($fieldshortname)) {
        return '';
    }

    $sql = "SELECT d.data
              FROM {user_info_field} f
              JOIN {user_info_data} d ON d.fieldid = f.id
             WHERE f.shortname = :shortname
               AND d.userid = :userid";

    $studentcode = $DB->get_field_sql($sql, [
        'shortname' => $fieldshortname,
        'userid' => $userid,
    ]);

    return $studentcode ? trim($studentcode) : '';
}

?>