<?php
namespace report_th_gradereport\form;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir . '/xmldb/xmldb_table.php');
use xmldb_table;
class report_th_gradereport extends \moodleform {

    public function definition(){
          $mform = $this->_form; // Don't forget the underscore!
        global $DB;

        $format_date = get_config('local_thlib','format_date');
        
        if(!$format_date){
            $format_date = 'd/m/Y';
        }

        $dbman = $DB->get_manager();
        $table = new xmldb_table('report_th_courseopening');
        $exist = $dbman->table_exists($table);

        if($exist){
            $dot_mon_mons = $DB->get_records_sql(
                "SELECT id, dot_mo_mon
                FROM {report_th_courseopening} 
                ORDER BY id ASC");    
        } else {
            $dot_mon_mons = array();
        }  

        $dotoptions = array();
       
        foreach ($dot_mon_mons as $dot_mon_mon) {
            $dotoptions[$dot_mon_mon->dot_mo_mon] = date($format_date,$dot_mon_mon->dot_mo_mon);
        }

        $options = array(
            'multiple' => false, // hoặc bỏ hẳn key này
            'noselectionstring' => get_string('heading2','report_th_gradereport'), 
        );

        $mform->addElement(
            'autocomplete',
            'dot_mon_monids',
            get_string('heading2','report_th_gradereport'),
            $dotoptions,
            $options
        );
        
        $mform->addRule('dot_mon_monids', null, 'required', null, 'client');

        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'reporttype', '', get_string('detail', 'report_th_gradereport'), 'detail');
        $radioarray[] = $mform->createElement('radio', 'reporttype', '', get_string('status', 'report_th_gradereport'), 'status');
        $mform->addGroup($radioarray, 'reporttype_group', get_string('reporttype', 'report_th_gradereport'), [' '], false);

        // Đặt giá trị mặc định cho radio
        $mform->setDefault('reporttype', 'detail');
  
        $this->add_action_buttons(true, get_string('confirmbutton','report_th_gradereport'));

        }

    function validation($data, $files) {
    
        return [];
    }
    
}