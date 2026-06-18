<?php

namespace local_th_vmc_alert;

class th_custom_admin_setting_configcheckbox extends \admin_setting_configcheckbox {
	public function write_setting($data) {

		$name = $this->name;
		$value = $data;

		$task = null;
		$classname = null;
		switch ($name) {
		case 'alertlogin':
			$task = new \local_th_vmc_alert\task\alert_learning();
			$classname = '\local_th_vmc_alert\task\alert_learning';
			break;
		case 'alertlearning':
			$task = new \local_th_vmc_alert\task\alert_login();
			$classname = '\local_th_vmc_alert\task\alert_login';
			break;
		case 'alertaccountduedate':
			$task = new \local_th_vmc_alert\task\alert_accountduedate();
			$classname = '\local_th_vmc_alert\task\alert_accountduedate';
			break;
		case 'alertketthuckhoahoc':
			$task = new \local_th_vmc_alert\task\alert_ketthuckhoahoc();
			$classname = '\local_th_vmc_alert\task\alert_ketthuckhoahoc';
			break;
		case 'alertaccountenrolcourse':
			$task = new \local_th_vmc_alert\task\alert_account_enrol_course();
			$classname = '\local_th_vmc_alert\task\alert_account_enrol_course';
			break;
		case 'alertactivecourse':
			$task = new \local_th_vmc_alert\task\alert_active_course();
			$classname = '\local_th_vmc_alert\task\alert_active_course';
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