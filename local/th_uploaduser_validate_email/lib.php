<?php
defined('MOODLE_INTERNAL') || die();


function local_th_uploaduser_validate_email_before_http_headers() {
    global $SCRIPT, $USER, $DB, $CFG;
    if (get_config('local_th_uploaduser_validate_email', 'enable')) {

        if (strpos($SCRIPT, '/admin/tool/uploaduser/index.php') !== false) {
            require_once($CFG->libdir.'/adminlib.php');
            require_once($CFG->libdir.'/csvlib.class.php');
            require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/uploaduser/locallib.php');
            require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/uploaduser/user_form.php');
            $iid         = optional_param('iid', '', PARAM_INT);
            $previewrows = optional_param('previewrows', 10, PARAM_INT);

            if (empty($iid)) {
                $mform1 = new admin_uploaduser_form1();
                $formdata = (object) $_POST;
                if (!empty((array)$formdata) and isset($formdata) and isset($formdata->userfile)) {

                    $iid = csv_import_reader::get_new_iid('uploaduser');
                    $cir = new csv_import_reader($iid, 'uploaduser');
                    $content = $mform1->get_file_content('userfile');
                    $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
                    unset($content);
                }
            }
            $formdata = (object) $_POST;

            if (!empty((array)$formdata) and isset($formdata) and isset($cir)) {
                $process = new \tool_uploaduser\process($cir);
                $filecolumns = $process->get_file_columns();
                $table = new \local_th_uploaduser_validate_email\preview($cir, $filecolumns, $previewrows);

                // Print the form if valid values are available.
                if (!$table->noerror_email) {
                    ob_start('local_th_uploaduser_validate_email_strip_mform2');

                    if (file_exists($CFG->dirroot . '/local/thlib/lib.php')) {
                        require_once($CFG->dirroot . '/local/thlib/lib.php');
                        if (function_exists('generate_download_excel')){
                            $filecolumns[]= get_string('status');
                            array_unshift($filecolumns, get_string('uucsvline', 'tool_uploaduser'));
                            $filecolumns = array($filecolumns);
                            foreach ($table->data_error as $key1 => $value) {
                                $filecolumns[] = array_values($value);
                            }
                            
                            generate_download_excel('email_error.xlsx', $filecolumns, 'th');
                            exit();
                        }
                    }
                }
            }
        }
    }
}


function local_th_uploaduser_validate_email_strip_mform2($html, $table) {
    global $OUTPUT;
    $pattern = '/<form\b(?=[^>]*\bid\s*=\s*(["\']?)mform2_[^"\'> ]+\1)[^>]*>.*?<\/form>/is';
    $html = preg_replace($pattern, '', $html);
    return $html;
}