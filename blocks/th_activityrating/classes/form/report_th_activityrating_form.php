<?php
namespace block_th_activityrating\form;

require_once("$CFG->libdir/formslib.php");
class report_th_activityrating_form extends \moodleform {

    public function definition() {
       
        $mform = $this->_form; // Don't forget the underscore!

        global $DB;
        $courses = $DB->get_records_sql(
            "SELECT id, fullname, shortname
            FROM {course} WHERE category <> 0 and visible = 1
            ORDER BY fullname ASC");    

        $courseoptions = [];
        $courseoptions['all'] = get_string('all','block_th_activityrating');
        foreach ($courses as $course) {
            $courseoptions[$course->id] = $course->fullname . ',' . $course->shortname;
        }

        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('course','block_th_activityrating'), 
        ];

        $mform->addElement('autocomplete', 'courseids', get_string('courses'), $courseoptions, $options);
        #add button
        // $mform->addElement('submit', 'intro', get_string('confirmbutton','report_th_courserating'));
  
        $mform->addRule('courseids', null, 'required', null, 'client');
  
        $this->add_action_buttons(true, get_string('show','block_th_activityrating'));

        }

    function validation($data, $files) {
    
        return [];
    }
}