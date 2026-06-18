<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

class th_user_select_form extends moodleform {

    private $users;

    /**
     * $users: mảng [userid => fullname]
     * $selected: userid đang được chọn
     */
    public function __construct($users, $selected = null) {
        $this->users = $users;
        parent::__construct();
        $this->set_data(['userid' => $selected]);
    }

    public function definition() {
        $mform = $this->_form;

        // Thêm trường chọn user
        $options = [
            'multiple' => false,
            'noselectionstring' => 'Chọn',
        ];

        $mform->addElement(
            'autocomplete',
            'userid',
            get_string('chooseuser', 'local_th_framework_study'),
            $this->users,
            $options
        );

        $mform->addRule('userid', null, 'required', null, 'client');

        // Thêm nút submit
        $mform->addElement('submit', 'view', get_string('view', 'local_th_framework_study'));
    }
}
