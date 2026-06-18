<?php

namespace mod_thpage\event;
use stdClass;
defined('MOODLE_INTERNAL') || die();

class course_thpage_created extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'thpage';
    }

    public static function get_name() {
        return get_string('eventname_create', 'thpage');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        global $DB;

        $a = new stdClass();
        $a->userid = $this->userid;
        $a->objectid = $this->objectid;

        return get_string('event_create_desc', 'thpage', $a);
    }

     public function get_url() {
        return new \moodle_url('/mod/thpage/view.php', array('id' => $this->other['cmid']));
    }
}

