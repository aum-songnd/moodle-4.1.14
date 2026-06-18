<?php

namespace block_plugin02\form;

require_once("$CFG->libdir/formslib.php");

class myform extends \moodleform {
   
    public function definition() {
        $mform = $this->_form; 
        global $DB;

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'yesno', '', get_string('ghidanh','block_plugin02'), 1);
        $radioarray[] = $mform->createElement('radio', 'yesno', '', get_string('xoaghidanh','block_plugin02'), 0);
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
        $mform->setDefault('yesno', 1);
        

        $users = $DB->get_records_select('user','id>20',null,'id DESC','id , username');                                                     
        $usernames = array();                                                                                                       
        foreach ($users as $userid => $user) {                                                                          
            $usernames[$user->id] = $user->username;                                                                  
        }                                                                                                                           
        $options = array(                                                                                                           
            'multiple' => true,                                                  
            'noselectionstring' => get_string('user1','block_plugin02'),                                                                 
        );         
        $mform->addElement('autocomplete', 'userids', get_string('user'), $usernames, $options);


        $courses = $DB->get_records_select('course','id<>1',null,'id DESC','id , fullname');                                                     
        $coursenames = array();                                                                                                       
        foreach ($courses as $courseid => $course) {                                                                          
            $coursenames[$course->id] = $course->fullname;                                                                  
        }                                                                                                                           
        $options = array(                                                                                                           
            'multiple' => true,                                                  
            'noselectionstring' => get_string('course1','block_plugin02'),                                                                 
        );         
        $mform->addElement('autocomplete', 'courseids', get_string('course'), $coursenames, $options);
        
        $context = \context_course::instance(1, MUST_EXIST);   
        $roles = array();
        $roles = get_assignable_roles($context);
        $options = array(
            'multiple'=>true,
            'noselectionstring' => get_string('role1','block_plugin02'),
        );
        $mform->addElement('autocomplete', 'roleids', get_string('roles'), $roles, $options);
        $mform->addElement('selectyesno', 'maxbytes', 'YES NO');
        
        // echo userdate(time(), '%d/%m/%Y %H:%M:%S');

        $mform->addElement('date_selector', 'assesstimefinish', get_string('to'));

        $mform->addElement('date_time_selector', 'assesstimestart', 'Date');

        // $mform->addElement('advcheckbox', 'ratingtime', 'TEST', 'Label displayed after checkbox', array('group' => 1), array(0, 1));
        $options = array(
            0 => 'Lựa chọn 0',
            1 => 'Lựa chọn 1',
            2 => 'Lựa chọn 2',
            3 => 'Lựa chọn 3',
            4 => 'Lựa chọn 4'
        );
        $mform->addElement('select', 'ratingtime', 'TEST', $options);

        $mform->addElement('checkbox', 'text', 'Tôi không phải robot');
        $mform->addElement('text', 'abc', 'Text');
        $mform->setType('abc', PARAM_TEXT);
        $mform->hideIf('abc', 'text', 'notchecked');
        // $mform->addElement('checkbox', 'robot', 'KHong phai robot');
        // $mform->addElement('submit', 'intro', 'click');
        // An nut submit
        // $mform->hideIf('intro', 'robot', 'notchecked');
        $mform->addElement('checkbox', 'robot', 'Tôi không phải robot');
        $mform->addElement('submit', 'submitbtn', 'Gửi');
        // $mform->disable_form_change_checker();
        // $mform->addRule('abc',get_string('maximumchars', '', 20), 'maxlength', 20, 'client');
        $mform->addRule('abc',get_string('email'), 'email', null, 'client');  
        // Vô hiệu hóa nút nếu chưa tick checkbox
        $mform->disabledIf('submitbtn', 'robot', 'notchecked'); // submit bi an neu khong tick robot
       
        // $timestamp = time();
        // echo '<div id="clock">Đang tải thời gian...</div>';

        // echo "<script>
        //     let timestamp = $timestamp * 1000;

        //     function updateClock() {
        //         let date = new Date(timestamp);

        //         let day = date.getDate().toString().padStart(2, '0');
        //         let month = (date.getMonth() + 1).toString().padStart(2, '0');
        //         let year = date.getFullYear();
        //         let hours = date.getHours().toString().padStart(2, '0');
        //         let minutes = date.getMinutes().toString().padStart(2, '0');
        //         let seconds = date.getSeconds().toString().padStart(2, '0');

        //         document.getElementById('clock').innerHTML =
        //             'Bây giờ là: ' + day + '/' + month + '/' + year + ' ' + hours + ':' + minutes + ':' + seconds;

        //         timestamp += 1000;
        //     }

        //     updateClock();
        //     setInterval(updateClock, 1000);
        // </script>";
    }

    function validation($data, $files) {
        return [];
    }
}
?>