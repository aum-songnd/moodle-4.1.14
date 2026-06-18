<?php

require_once "{$CFG->libdir}/formslib.php";
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/local/thlib/th_form.php';

class th_user_activity_report_form extends th_form {

    function definition() {
        global $DB, $COURSE;

        $mform = $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('textfields', 'block_th_user_activity_report'));

        $this->add_show_option_radio();
        $this->add_makhoa_malop_user_filter();

        $this->add_user_status_option_radio();

        $config = get_config('block_th_user_activity_report');
        $roles_field = $config->roles_field;

        $records = $DB->get_record("user_info_field", array('shortname' => $roles_field), 'id,param1,defaultdata');
        if($records) {
            $param1 = $records->param1;
            $arr_roles = ['0' => get_string('all', 'block_th_user_activity_report')];
            $arr_roles = array_merge($arr_roles, explode("\n", $param1));
    
            $this->defaultdata_role = $records->defaultdata;
            $this->arr_roles = $arr_roles;
            $this->user_info_field_id = $records->id;
        } else {
            $this->defaultdata_role = '';
            $arr_roles = ['0' => get_string('all', 'block_th_user_activity_report')];
            $this->arr_roles = $arr_roles;
            $this->user_info_field_id = 0;
        }

        $mform->addElement('select', "custom_role", get_string('roles', 'block_th_user_activity_report'), $arr_roles);
        $mform->disabledIf('custom_role', 'show_option', 'neq', '2');
        $mform->hideif('custom_role', 'show_option', 'neq', '2');

        $this->_name = "numlogin";
        $this->_label = get_string('numlogin', 'block_th_user_activity_report');
        $objs = array();
        $operators = [
            get_string('lessthan', 'block_th_user_activity_report'),
            get_string('greaterthan', 'block_th_user_activity_report'),
            get_string('equalto', 'block_th_user_activity_report'),
        ];

        $objs['op'] = $mform->createElement('select', $this->_name . "_op", null, $operators);
        $mform->setDefault($this->_name . "_op", 0);
        $objs['value'] = $mform->createElement('text', $this->_name, null);
        $mform->setDefault($this->_name, 1);

        $grp = &$mform->addElement('group', $this->_name . '_grp', $this->_label, $objs, '', false);
        $mform->setType($this->_name, PARAM_RAW);

        // $mform->setAdvanced($this->_name . '_grp');
        $this->add_from_to_datetime();
        $this->add_action_buttons(true, get_string("submmit", 'block_th_user_activity_report'));
    }

    function get_userid_form($mform, $sortorder = null, $required = false) {
        global $DB, $COURSE, $CFG;
        $context = context_course::instance($COURSE->id);
        // $userfields = get_extra_user_fields($context);

        $version = substr($CFG->release, 0, 1);
        if ($version == 4) {
            $extra = array_filter(explode(',', $CFG->showuseridentity));
            $userfields = array_values($extra);

            //v4
            $usernamefield = core_user\fields::get_name_fields(true);
            $usernamefield = implode(",", $usernamefield);
            $alluserfields = "id," . $usernamefield;

            $corefields = [];
            $customfields = [];

            foreach ($userfields as $field) {
                if (strpos($field, 'profile_field_') === 0) {
                    $customfields[] = substr($field, strlen('profile_field_')); // bỏ tiền tố
                } else {
                    $corefields[] = $field;
                }
            }

            if (!empty($corefields)) {
                $alluserfields .= "," . implode(',', $corefields);
            }
            $alluserfields .= ",email";

            $users = $DB->get_records('user', ['deleted' => 0], $sortorder, $alluserfields);
            $choice = array();
            $choice[''] = '';

            // Nếu có customfields thì lấy toàn bộ dữ liệu user_info_data của các user
            $userids = array_keys($users);
            $customdata = [];
            if (!empty($customfields)) {
                list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
                $fields = $DB->get_records('user_info_field', null, '', 'id, shortname');
                $fieldmap = [];
                foreach ($fields as $f) {
                    if (in_array($f->shortname, $customfields)) {
                        $fieldmap[$f->id] = $f->shortname;
                    }
                }

                if (!empty($fieldmap)) {
                    $records = $DB->get_records_select('user_info_data', "userid $insql", $inparams);
                    foreach ($records as $rec) {
                        if (!isset($fieldmap[$rec->fieldid])) {
                            continue;
                        }
                        $shortname = $fieldmap[$rec->fieldid];
                        $customdata[$rec->userid][$shortname] = $rec->data;
                    }
                }
            }

            foreach ($users as $key => $value) {
                $fullname = fullname($value);
                $users[$key]->fullname = $fullname;
                $fullnamehtml = html_writer::tag("span", $fullname);

                $extraf = [];
                foreach ($corefields as $uf) {
                    if (!empty($value->$uf)) {
                        $extraf[] = $value->$uf;
                    }
                }

                // Gắn thêm customfield nếu có
                if (!empty($customfields) && isset($customdata[$value->id])) {
                    foreach ($customfields as $cf) {
                        if (!empty($customdata[$value->id][$cf])) {
                            $extraf[] = $customdata[$value->id][$cf];
                        }
                    }
                }

                $extraftext = implode(", ", $extraf);
                $extrafhtml = html_writer::tag("small", $extraftext);
                $val = html_writer::tag("span", $fullnamehtml . ", " . $extrafhtml);

                $choice[$value->id] = $val;
            }

            $options = array(
                'multiple' => true,
                'noselectionstring' => get_string('no_selection', 'local_thlib'),
                'ajax' => 'local_thlib/loadUser',
                'perpage' => $CFG->maxusersperpage,
                'userfields' => implode(',', \core_user\fields::get_identity_fields($context)),
                'valuehtmlcallback' => function($value) {
                    global $DB, $OUTPUT;
                    $user = $DB->get_record('user', ['id' => (int)$value], '*', IGNORE_MISSING);
                    $user->fullname = fullname($user);
                    return $OUTPUT->render_from_template(
                            'local_thlib/form-user-selector', $user);
                }
            );
        
            $element = $mform->addElement('autocomplete', 'userid', get_string('search_user', 'local_thlib'), [], $options);
            if ($required) {
                $attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
                $element->setAttributes($attributes);
            }
            return $users;
            
        } else {
            $extra = array_filter(explode(',', $CFG->showuseridentity));
            $userfields = array_values($extra);

            $usernamefield = get_all_user_name_fields();
            $usernamefield = implode(",", $usernamefield);
            $alluserfields = "id," . $usernamefield;

            if (count($userfields) > 0) {
                $alluserfields .= "," . implode(',', $userfields);
            }

            $alluserfields .= "," . "email";

            $users = $DB->get_records('user', array('deleted' => 0), $sortorder, $alluserfields);
            $choice = array();
            $choice[''] = '';

            foreach ($users as $key => $value) {
                $fullname = fullname($value);
                $users[$key]->fullname = $fullname;
                $fullname = html_writer::tag("span", $fullname);

                $extraf = array();
                foreach ($userfields as $key => $uf) {
                    $v = $value->$uf;
                    if (!is_null($v) && $v !== '') {
                        $extraf[] = $v;
                    }
                }
                $extraf = implode(", ", $extraf);
                $extraf = html_writer::tag("small", $extraf);
                $extraf = html_writer::tag("span", $extraf);

                $val = html_writer::tag("span", $fullname . ", " . $extraf);

                $choice[$value->id] = $val;
            }

            $options = array(
                'multiple' => true,
                'noselectionstring' => get_string('no_selection', 'local_thlib'),
                'ajax' => 'local_thlib/loadUser',
                'perpage' => $CFG->maxusersperpage,
                'userfields' => implode(',', get_extra_user_fields(context_course::instance(1))),
                'valuehtmlcallback' => function($value) {
                    global $DB, $OUTPUT;
                    $user = $DB->get_record('user', ['id' => (int)$value], '*', IGNORE_MISSING);
                    $user->fullname = fullname($user);
                    return $OUTPUT->render_from_template(
                            'local_thlib/form-user-selector', $user);
                }
            );
        
            $element = $mform->addElement('autocomplete', 'userid', get_string('search_user', 'local_thlib'), [], $options);
            if ($required) {
                $attributes = $element->getAttributes() + ['required' => 'true', 'class' => 'custom_required'];
                $element->setAttributes($attributes);
            }
            return $users;
        }
    }

    function add_from_to_datetime() {
        $mform = $this->_form;
        $mform->addElement('date_selector', 'time_from', get_string('fromdate', 'block_th_user_activity_report'));
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('-31 day');
        $mform->setDefault('time_from', $date->getTimestamp());
        $mform->addElement('date_selector', 'time_to', get_string('todate', 'block_th_user_activity_report'));
    }

    function add_user_status_option_radio() {
        $mform = $this->_form;
        $radioarray = array();
        $radioarray[] = &$mform->createElement('radio', 'user_status', '', get_string('radio_all', 'local_thlib'), 0);
        $radioarray[] = &$mform->createElement('radio', 'user_status', '', get_string('radio_active', 'local_thlib'), 1);
        $radioarray[] = &$mform->createElement('radio', 'user_status', '', get_string('radio_suppend', 'local_thlib'), 2);

        $element = $mform->addGroup($radioarray, 'user_status', get_string('selectoption', 'block_th_user_activity_report'), array(''), false);
        $attributes = $element->_attributes = ['class' => 'custom_required'];
        $element->setAttributes($attributes);
    }

    function add_show_option_radio() {
        $mform = $this->_form;
        $radioarray = array();
        $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomakhoa', 'local_thlib'), 0);
        $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiomalop', 'local_thlib'), 1);
        $radioarray[] = &$mform->createElement('radio', 'show_option', '', get_string('radiouser', 'local_thlib'), 2);

        $mform->addGroup($radioarray, 'show_option', get_string('selectoption', 'block_th_user_activity_report'), array(''), false);
        $mform->setDefault('show_option', 0);
    }

    function add_makhoa_malop_user_filter() {
        $mform = $this->_form;

        $config = get_config('local_thlib');
        $sortorder = "lastname,firstname";
        if ($config->sortorder == 1) {
            $sortorder = "firstname,lastname";
        }

        $enrollmentcourseshortname = trim($config->enrollmentcourseshortname);
        $classcodeshortname = trim($config->classcodeshortname);

        $this->makhoaarr = get_profile_data($enrollmentcourseshortname);
        $this->maloparr = get_profile_data($classcodeshortname);

        // Ma Khoa Filter
        $choice = array();
        $choice[''] = '';
        foreach ($this->makhoaarr as $key => $value) {
            $choice[$value->id] = $value->data;
        }

        $options = array(
            'multiple' => false,
            'noselectionstring' => get_string('no_selection', 'block_th_user_activity_report'),
        );

        $element = $mform->addElement('autocomplete', 'makhoaid', get_string('makhoa', 'local_thlib'), $choice, $options, array('classs' => 'cohort-cohort'));
        $attributes = $element->getAttributes() + ['id' => 'myid', 'class' => 'custom_required'];

        $element->setAttributes($attributes);
        // Ma lop filter
        $choice = array();
        $choice[''] = '';
        foreach ($this->maloparr as $key => $value) {
            $choice[$value->id] = $value->data;
        }

        $options = array(
            'multiple' => false,
            'noselectionstring' => get_string('no_selection', 'block_th_user_activity_report'),
        );

        $element = $mform->addElement('autocomplete', 'malopid', get_string('malop', 'local_thlib'), $choice, $options, array('classs' => 'cohort-cohort'));
        $attributes = $element->getAttributes() + ['class' => 'custom_required'];
        $element->setAttributes($attributes);
        // User filter
        $this->user_arr = $this->get_userid_form($mform, $sortorder);

        $mform->disabledIf('makhoaid', 'show_option', 'neq', '0');
        $mform->disabledIf('malopid', 'show_option', 'neq', '1');
        $mform->disabledIf('userid', 'show_option', 'neq', '2');

        $mform->hideif('makhoaid', 'show_option', 'neq', '0');
        $mform->hideif('malopid', 'show_option', 'neq', '1');
        $mform->hideif('userid', 'show_option', 'neq', '2');
    }

    function validation($data, $files) {
        if ($data['show_option'] == 0 && empty($data['makhoaid'])) {
            return array('makhoaid' => get_string('err_required', 'form'));
        }

        if ($data['show_option'] == 1 && empty($data['malopid'])) {
            return array('malopid' => get_string('err_required', 'form'));
        }

        $day_count = ($data['time_to'] - $data['time_from']) / (24 * 60 * 60);

        if ($day_count > 31) {
            return array('time_from' => get_string('limit_31_days', 'block_th_user_activity_report'));
        }

        return array();
    }

}
