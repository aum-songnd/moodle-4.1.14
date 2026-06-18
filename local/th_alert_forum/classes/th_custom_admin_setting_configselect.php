<?php

namespace local_th_alert_forum;

class th_custom_admin_setting_configselect extends \admin_setting_configselect {
    public function write_setting($data) {

        $name = $this->name;
        $value = $data;

        $task = new \local_th_alert_forum\task\alert_forum();
        $classname = '\local_th_alert_forum\task\alert_forum';

        // print_object($data);
        // print_object("Vào đâu");
        if ($data == 'scheduled') {
            $task = \core\task\manager::get_default_scheduled_task($classname, false);
            $task->set_disabled(!$value);
            $result = \core\task\manager::configure_scheduled_task($task);
            //print_object("Lập lịch");
        }
        if ($data == 'event') {
            $task = \core\task\manager::get_default_scheduled_task($classname, false);
            $task->set_disabled($value);
            $result = \core\task\manager::configure_scheduled_task($task);
            //print_object("Sự kiện");
        }
        // exit();
        return parent::write_setting($data);
    }
}