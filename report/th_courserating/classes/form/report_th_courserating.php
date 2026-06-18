<?php
namespace report_th_courserating\form;

require_once("$CFG->libdir/formslib.php");
class report_th_courserating extends \moodleform {

    public function definition() {
       
        $mform = $this->_form; // Don't forget the underscore!

        global $DB;
        $courses = $DB->get_records_sql(
            "SELECT id, fullname
            FROM {course} WHERE category <> 0 and visible <>0
            ORDER BY fullname ASC");    

        $courseoptions = array();
        $courseoptions['all'] = get_string('all','report_th_courserating');
        foreach ($courses as $course) {
            $courseoptions[$course->id] = $course->fullname;
        }

        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('course','report_th_courserating'), 
        );

        $mform->addElement('autocomplete', 'courseids', get_string('courses'), $courseoptions, $options);
        #add button
        // $mform->addElement('submit', 'intro', get_string('confirmbutton','report_th_courserating'));
  
        $mform->addRule('courseids', null, 'required', null, 'client');
  
$this->add_action_buttons(true, get_string('confirmbutton','report_th_courserating'));

        }

    function validation($data, $files) {
    
        return [];
    }
}