<?php

namespace local_th_vmc_sms;

class th_custom_admin_setting_configcheckbox extends \admin_setting_configcheckbox {
	public function write_setting($data) {

		$name = $this->name;
		$value = $data;
		$task = null;
		$classname = null;
		switch ($name) {
		case 'tinnhan2':
			$task = new \local_th_vmc_sms\task\tin_nhan2();
			$classname = '\local_th_vmc_sms\task\tin_nhan2';
			break;
		case 'tinnhan3':
			$task = new \local_th_vmc_sms\task\tin_nhan3();
			$classname = '\local_th_vmc_sms\task\tin_nhan3';
			break;
		case 'tinnhan4':
			$task = new \local_th_vmc_sms\task\tin_nhan4();
			$classname = '\local_th_vmc_sms\task\tin_nhan4';
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