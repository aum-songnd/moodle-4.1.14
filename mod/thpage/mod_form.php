<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/thpage/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_thpage_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $config = get_config('thpage');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'thpage'));
        // $mform->addElement('editor', 'thpage', get_string('content', 'thpage'), '', thpage_get_editor_options($this->context));
        // $mform->addRule('thpage', get_string('required'), 'required', null, 'client');

        MoodleQuickForm::registerElementType('thpage_editor', "$CFG->dirroot/mod/thpage/locallib.php", 'mod_thpage_editor_form_element');
        $mform->addElement('thpage_editor', 'thpage', get_string('content', 'thpage'), '', thpage_get_editor_options($this->context));
        $mform->addRule('thpage', get_string('required'), 'required', null, 'client');

         // Initialize HTML output.
        $html = '';

        // Fetch context.
        $context = \context_system::instance();

        // Get filearea.
        $fs = get_file_storage();
        // print_object($fs->is_area_empty($context->id, 'mod_thpage', 'documents'));

        // If no file is found, quit with notification.
        if ($fs->is_area_empty($context->id, 'mod_thpage', 'documents')) {
           
        } else {

            // Fetch all files.
            $pages = $fs->get_area_files($context->id, 'mod_thpage', 'documents', false, 'filename', false);

            // Get plugin config.
            $localstaticpageconfig = get_config('mod_thpage');

            // Output each page as a page list entry.
            $i = 1;
            foreach ($pages as $page) {
                $filename = $page->get_filename();
                $pageextension = pathinfo($filename, PATHINFO_EXTENSION);
                $pagepagename = pathinfo($filename, PATHINFO_FILENAME);

                // If this is not .html file, skip it.
                if ($pageextension != 'html') {
                    continue;
                }

                // Get filearea.
                $fs = get_file_storage();

                // Get document from filearea.
                $file = $fs->get_file($context->id, 'mod_thpage', 'documents', 0, '/', $filename);
                // stored_file
                $filecontents = $file->get_content();
                $aid = 'th_template'.$i;
                $mform->addElement('static', "th_template$i", get_string('template', 'thpage', ['i' => $i]), "
                    <a style='cursor: pointer;' title='copy' onclick='myFunction($aid)' id='$aid' data-custom-attribute='$filecontents'>$pagepagename</a>");
                $i++;
            }
        }
        $PAGE->requires->js('/mod/thpage/amd/src/custom.js', true);

        $mform->addElement('header', 'thheader', get_string('thheader', 'thpage'));

        $mform->addElement('text', 'exam1', get_string('week_exam1', 'thpage'), array('size'=>'10'));
        $mform->setType('exam1', PARAM_INT);
        $mform->setDefault('exam1', 4);
        $mform->addElement('text', 'exam2', get_string('week_exam2', 'thpage'), array('size'=>'10'));
        $mform->setType('exam2', PARAM_INT);
        $mform->setDefault('exam2', 8);


        $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        $dh = new RecursiveDirectoryIterator(K_PATH_FONTS, $flags);
        foreach ($dh as $fi) {
            if ($fi->getExtension() !== 'php') {
                continue;
            }
            $fontname = $fi->getBasename('.' . $fi->getExtension());
            include($fi->getPathname());
            $fontinfo = [
                'name' => $name,
                'family' => '',
            ];

            $family = strtoupper($fontname);
            if (substr($family, -1) == 'I') {
                $family = substr($family, 0, -1);
            }
            if (substr($family, -1) == 'B') {
                $family = substr($family, 0, -1);
            }
            $fontinfo['family'] = strtolower($family); // Normalize family name.
            $info[$fontname] = $fontinfo;
        }
        array_multisort(array_column($info, 'name'), SORT_NATURAL, $info);


        $fontslist = $info;

        $fonts = [];
        foreach ($fontslist as $font) {
            if (!isset($fonts[$font['family']])) {
                $fonts[$font['family']] = $font['family'];
            }
        } 
        unset($font); // Always unset references!
        ksort($fonts);

        // $options = array(
        //     'ff0000' => 'Red',
        //     '00ff00' => 'Green',
        //     '0000ff' => 'Blue'
        // );
        $select = $mform->addElement('select', 'font', get_string('font', 'thpage'), $fonts);
        // This will select the colour blue.
        $select->setSelected('freeserif');

         // $template = trim($config->template);
        $study_time = trim($config->study_time);
        $teacher = trim($config->teacher);
        $week1 = trim($config->week1);
        $week2 = trim($config->week2);
        $week3 = trim($config->week3);
        $week4 = trim($config->week4);
        $week5 = trim($config->week5);
        $week6 = trim($config->week6);
        $week7 = trim($config->week7);
        $week8 = trim($config->week8);
        $week9 = trim($config->week9);
        $week10 = trim($config->week10);
        $kt1 = trim($config->kt1);
        $kt2 = trim($config->kt2);
        $thi = trim($config->thi);
        $params = get_string('study_time', 'thpage') . ": [[$study_time]]</br>"
                . get_string('teacher', 'thpage') .": [[$teacher]]</br>"
                . get_string('week1', 'thpage') .": [[$week1]]</br>"
                . get_string('week2', 'thpage') .": [[$week2]]</br>"
                . get_string('week3', 'thpage') .": [[$week3]]</br>"
                . get_string('week4', 'thpage') .": [[$week4]]</br>"
                . get_string('week5', 'thpage') .": [[$week5]]</br>"
                . get_string('week6', 'thpage') .": [[$week6]]</br>"
                . get_string('week7', 'thpage') .": [[$week7]]</br>"
                . get_string('week8', 'thpage') .": [[$week8]]</br>"
                . get_string('week9', 'thpage') .": [[$week9]]</br>"
                . get_string('week10', 'thpage') .": [[$week10]]</br>"
                . get_string('kt1', 'thpage') .": [[$kt1]]</br>"
                . get_string('kt2', 'thpage') .": [[$kt2]]</br>"
                . get_string('thi', 'thpage') .": [[$thi]]</br>";
        $mform->addElement('static', 'params1', get_string('params', 'mod_thpage'), $params);

        //-------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'thpage'), $options);
            $mform->setDefault('display', $config->display);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'thpage'), array('size'=>3));
            if (count($options) > 1) {
                $mform->hideIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'thpage'), array('size'=>3));
            if (count($options) > 1) {
                $mform->hideIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
        }

        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'thpage'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'thpage'));
        $mform->setDefault('printintro', $config->printintro);
        $mform->addElement('advcheckbox', 'printlastmodified', get_string('printlastmodified', 'thpage'));
        $mform->setDefault('printlastmodified', $config->printlastmodified);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'thpage'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'thpage'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'thpage'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('thpage');
            $defaultvalues['thpage']['format'] = $defaultvalues['contentformat'];
            $defaultvalues['thpage']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_thpage',
                    'content', 0, thpage_get_editor_options($this->context), $defaultvalues['content']);
            $defaultvalues['thpage']['itemid'] = $draftitemid;
        }
        if (!empty($defaultvalues['displayoptions'])) {
            $displayoptions = unserialize($defaultvalues['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $defaultvalues['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $defaultvalues['printheading'] = $displayoptions['printheading'];
            }
            if (isset($displayoptions['printlastmodified'])) {
                $defaultvalues['printlastmodified'] = $displayoptions['printlastmodified'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $defaultvalues['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $defaultvalues['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }
}