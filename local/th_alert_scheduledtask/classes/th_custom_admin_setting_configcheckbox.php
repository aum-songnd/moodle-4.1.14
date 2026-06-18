<?php

namespace local_th_alert_scheduledtask;

class th_custom_admin_setting_configcheckbox extends \admin_setting_configcheckbox {
	public function write_setting($data) {

		$name = $this->name;
		$value = $data;

		$task = null;
		$classname = null;
		switch ($name) {
		case 'alertapprovecourse':
			$task = new \local_th_alert_scheduledtask\task\alert_approve_course();
			$classname = '\local_th_alert_scheduledtask\task\alert_approve_course';
			break;
		case 'alertwritewelcomtocourseforum':
			$task = new \local_th_alert_scheduledtask\task\alert_write_welcome_to_course_forum();
			$classname = '\local_th_alert_scheduledtask\task\alert_write_welcome_to_course_forum';
			break;
		case 'alertpostscheduleonlinecourse':
			$task = new \local_th_alert_scheduledtask\task\alert_post_schedule_online_course();
			$classname = '\local_th_alert_scheduledtask\task\alert_post_schedule_online_course';
			break;
		case 'alertanswerquestion':
			$task = new \local_th_alert_scheduledtask\task\alert_answer_question();
			$classname = '\local_th_alert_scheduledtask\task\alert_answer_question';
			break;
		case 'alertnhanxetdanhgiahangtuan':
			$task = new \local_th_alert_scheduledtask\task\alert_viet_nhanxetdanhgia_hangtuan();
			$classname = '\local_th_alert_scheduledtask\task\alert_viet_nhanxetdanhgia_hangtuan';
			break;
		case 'alertchotbangdiem':
			$task = new \local_th_alert_scheduledtask\task\alert_chot_bang_diem();
			$classname = '\local_th_alert_scheduledtask\task\alert_chot_bang_diem';
			break;
		case 'alertchamdiembaituluan':
			$task = new \local_th_alert_scheduledtask\task\alert_cham_diem_bai_tu_luan();
			$classname = '\local_th_alert_scheduledtask\task\alert_cham_diem_bai_tu_luan';
			break;
		default:
			$task = null;
			$classname = null;
		}

		if ($classname != null) {
			$task = \core\task\manager::get_default_scheduled_task($classname, false);
			$task->set_disabled(!$value);
			$result = \core\task\manager::configure_scheduled_task($task);
		}

		return parent::write_setting($data);
	}
}