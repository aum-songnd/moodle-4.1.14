<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');
require_once($CFG->dirroot . '/local/th_framework_study/classes/form/upload_framework_study_form.php');

require_login();

$context = context_system::instance();
require_capability('local/th_framework_study:manageframeworkstudy', $context);

global $PAGE, $OUTPUT, $DB, $SESSION;

$PAGE->set_url(new moodle_url('/local/th_framework_study/manageframeworkstudy/upload.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('batchupload', 'local_th_framework_study'));
$PAGE->set_heading(get_string('batchupload', 'local_th_framework_study'));
$PAGE->navbar->add(get_string('manageframeworkstudy', 'local_th_framework_study'),
    new moodle_url('/local/th_framework_study/manageframeworkstudy/upload.php'));

echo $OUTPUT->header();
echo $OUTPUT->heading('<center>' . get_string('manageframeworkstudy', 'local_th_framework_study') . '</center>');

local_th_framework_study_tabs($context, $PAGE->url);
$enable_train_code = get_config('local_th_framework_study', 'enable_train_code');
$skip = (bool)get_config('local_th_framework_study', 'skip_slot_target');

$slotdefault = 0;

$mform = new framework_upload_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/th_framework_study/manageframeworkstudy/manage.php'));

} else if ($data = $mform->get_data()) {

    $SESSION->framework_action = $data->actionmode; 

    $draftitemid = file_get_submitted_draft_itemid('frameworkfile');
    $fs = get_file_storage();

    file_save_draft_area_files(
        $draftitemid, $context->id,
        'local_th_framework_study', 'uploadfile',
        0, ['subdirs' => 0]
    );

    $files = $fs->get_area_files($context->id, 'local_th_framework_study', 'uploadfile', 0, 'id', false);

    if ($files) {
        $file = reset($files);
        $filepath = $file->copy_content_to_temp('local_th_framework_study', '.xlsx');

        require_once($CFG->dirroot . '/lib/phpspreadsheet/vendor/autoload.php');

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filepath);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $header = array_map('trim', $rows[0]);

        $header = array_filter($header, function($col) {
            return $col !== '';
        });

        $lowerheader = array_map('strtolower', $header);

        $expected = ['school_id', 'subject_id', 'subject_name', 'major_id', 'target_id', 'credit', 'slot_order','training_type_code'];

        if (count($lowerheader) !== count($expected) || $lowerheader !== $expected) {
            $message = get_string('invalid_excel_format', 'local_th_framework_study');
            echo $OUTPUT->notification($message, 'error');

            unlink($filepath);

            $mform->display();
            echo $OUTPUT->footer();
            exit;
        }
        $records = [];
        $action = $data->actionmode;
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $subject_code = trim((string)($row[1] ?? ''));
            $subject_name = trim((string)($row[2] ?? ''));

            if ($subject_code === '' || $subject_name === '') continue;
        
            $subject_code = trim($row[1]);
            $subject_name = trim($row[2]);
            $major_code   = trim($row[3]);
            $target_code  = trim($row[4]);
            $credit_raw   = trim($row[5]);
            $slot_raw     = trim($row[6]);
            $training_type_code = $enable_train_code ? trim((string)($row[7] ?? '')) : null;
            $credit = null;

            if (!$skip) {
                if (!filter_var($credit_raw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
                    $status = get_string('skip', 'local_th_framework_study');
                    $records[] = [
                        'school_id'    => trim($row[0]),
                        'subject_code' => $subject_code,
                        'subject_name' => $subject_name,
                        'major_code'   => $major_code,
                        'target_code'  => $target_code,
                        'credit'       => $credit_raw,
                        'slot'         => $slot_raw,
                        'training_type_code' => $enable_train_code ? $training_type_code : null,
                        'status'       => $status
                    ];
                    continue;
                }
                $credit = (int)$credit_raw;

            } else {
                if ($credit_raw === '') {
                    $credit = null;
                } else if (filter_var($credit_raw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
                    $credit = (int)$credit_raw;
                } else {
                    $status = get_string('skip', 'local_th_framework_study');
                    $records[] = [
                        'school_id'    => trim($row[0]),
                        'subject_code' => $subject_code,
                        'subject_name' => $subject_name,
                        'major_code'   => $major_code,
                        'target_code'  => $target_code,
                        'credit'       => $credit_raw,
                        'slot'         => $slot_raw,
                        'training_type_code' => $enable_train_code ? $training_type_code : null,
                        'status'       => $status
                    ];
                    continue;
                }
            }
            if (!$skip) {
                if (!filter_var($slot_raw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
                    $status = get_string('skip', 'local_th_framework_study');
                    $records[] = [
                        'school_id'    => trim($row[0]),
                        'subject_code' => $subject_code,
                        'subject_name' => $subject_name,
                        'major_code'   => $major_code,
                        'target_code'  => $target_code,
                        'credit'       => $credit,
                        'slot'         => $slot_raw,
                        'training_type_code' => $enable_train_code ? $training_type_code : null,
                        'status'       => $status
                    ];
                    continue;
                }
                $slot = (int)$slot_raw;

            } else {
                if ($slot_raw === '') {
                    $slot = $slotdefault; // 0
                } else if (filter_var($slot_raw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                    $slot = (int)$slot_raw;
                } else {
                    $status = get_string('skip', 'local_th_framework_study');
                    $records[] = [
                        'school_id'    => trim($row[0]),
                        'subject_code' => $subject_code,
                        'subject_name' => $subject_name,
                        'major_code'   => $major_code,
                        'target_code'  => $target_code,
                        'credit'       => $credit,
                        'slot'         => $slot_raw,
                        'training_type_code' => $enable_train_code ? $training_type_code : null,
                        'status'       => $status
                    ];
                    continue;
                }
            }

            if ($skip && $target_code === '') {
                $target_code = '';
            }
            $isspecial1 = $skip && $subject_code === '-' && is_null($credit);
            $isspecial2 = $skip && $subject_code === '-';

            if ($isspecial1 || $isspecial2) {
                $conditions = [
                    'major_code'   => $major_code,
                    'subject_name' => $subject_name,
                ];
            } else {
                $conditions = [
                    'subject_code' => $subject_code,
                    'major_code'   => $major_code,
                    'target_code'  => $target_code,
                ];
            }

            if ($enable_train_code) {
                $conditions['training_type_code'] = $training_type_code;
            }

            $existing = $DB->get_record(
                'th_framework_study',
                $conditions,
                '*',
                IGNORE_MULTIPLE
            );
            $isspecial = $skip && $subject_code === '-' && is_null($credit);

            if ($action === 'import') {
                if (!$existing) {
                    $status = get_string('new', 'local_th_framework_study');
            
                } else {
                    $credit_changed =
                        ($existing->credit === null && $credit !== null) ||
                        ($existing->credit !== null && $credit === null) ||
                        ($existing->credit !== null && $credit !== null &&
                            (int)$existing->credit !== (int)$credit);
            
                    $changed =
                        trim($existing->subject_name) !== $subject_name ||
                        $credit_changed ||
                        (int)$existing->slot !== (int)$slot;
            
                    if ($changed) {
                        $status = get_string('update', 'local_th_framework_study');
                    } else {
                        $status = get_string('skip', 'local_th_framework_study');
                    }
                }
            }
            else if ($action === 'delete') {

                if (!$existing) {
                    $status = get_string('notfound', 'local_th_framework_study');
            
                } else {
                    $isspecial = $skip
                        && $subject_code === '-'
                        && (is_null($credit) || $credit === '');
            
                    if ($isspecial) {
                        $nomatch =
                            trim($existing->subject_name) !== $subject_name ||
                            trim($existing->major_code)   !== $major_code ||
                            ($enable_train_code &&
                             trim($existing->training_type_code) !== $training_type_code);
            
                    } else {
                        $credit_match =
                            (is_null($existing->credit) && is_null($credit)) ||
                            (!is_null($existing->credit) && !is_null($credit) &&
                                (int)$existing->credit === (int)$credit);
            
                        $nomatch =
                            trim($existing->subject_name) !== $subject_name ||
                            trim($existing->major_code)   !== $major_code ||
                            (!$skip && trim($existing->target_code) !== $target_code) ||
                            !$credit_match ||
                            (!$skip && (int)$existing->slot !== (int)$slot) ||
                            ($enable_train_code &&
                             trim($existing->training_type_code) !== $training_type_code);
                    }
            
                    if ($nomatch) {
                        $status = get_string('nomatch', 'local_th_framework_study');
                    } else {
                        $status = get_string('deletable', 'local_th_framework_study');
                    }
                }
            }
            
            $school_id = trim((string)($row[0] ?? ''));
            $records[] = [
                'school_id'    => $school_id,
                'subject_code' => $subject_code,
                'subject_name' => $subject_name,
                'major_code'   => $major_code,
                'target_code'  => $target_code,
                'credit'       => $credit,
                'slot'         => $slot,
                'training_type_code' => $enable_train_code ? $training_type_code : null,
                'status'       => $status
            ];
        }

        $SESSION->framework_batchdata = $records;

        echo html_writer::tag('h4', get_string('previewdata', 'local_th_framework_study'));
        $table = new html_table();
        $table->head = [
            get_string('schoolid', 'local_th_framework_study'),
            get_string('subjectcode', 'local_th_framework_study'),
            get_string('subjectname', 'local_th_framework_study'),
            get_string('majorcode', 'local_th_framework_study'),
            get_string('targetcode', 'local_th_framework_study'),
            get_string('credit', 'local_th_framework_study'),
            get_string('slot', 'local_th_framework_study'),
        ];
        
        if ($enable_train_code) {
            $table->head[] = get_string('training_type_code', 'local_th_framework_study');
        }
        $table->head[] = get_string('status', 'local_th_framework_study');

        foreach ($records as $r) {
            $data_row = [
                $r['school_id'] ?: '-',
                $r['subject_code'],
                $r['subject_name'],
                $r['major_code'],
                $r['target_code'],
                $r['credit'],
                $r['slot'],
            ];
        
            if ($enable_train_code) {
                $data_row[] = $r['training_type_code'] ?: '-';
            }
        
            $data_row[] = $r['status'];
            $table->data[] = $data_row;
        }
        
        echo html_writer::table($table);

        $confirmurl = new moodle_url('/local/th_framework_study/manageframeworkstudy/upload.php', ['confirm' => 1]);
        echo $OUTPUT->confirm(
            get_string('confirmimport', 'local_th_framework_study'),
            $confirmurl,
            new moodle_url('/local/th_framework_study/manageframeworkstudy/upload.php')
        );
        
        unlink($filepath);
        

    } else {
        echo $OUTPUT->notification(get_string('nofilefound', 'local_th_framework_study'), 'error');
    }

} else if (optional_param('confirm', 0, PARAM_BOOL)) {

    if (!empty($SESSION->framework_batchdata)) {

        $records = $SESSION->framework_batchdata;
        $action  = $SESSION->framework_action ?? 'import';

    if ($action === 'delete') {

        $deleted  = 0;
        $notfound = 0;
        $notmatch = 0;
        $invalid  = 0;

        foreach ($records as $r) {
            $isspecial = $skip
            && $r['subject_code'] === '-'
            && (is_null($r['credit']) || $r['credit'] === '');
    
        if (
            !isset($r['subject_name']) ||
            !isset($r['major_code'])   ||
            (!$isspecial && !isset($r['target_code'])) ||
            (!$isspecial && !array_key_exists('credit', $r)) ||
            (!$isspecial && !isset($r['slot'])) ||
            ($enable_train_code && !array_key_exists('training_type_code', $r))
        ) {
            $invalid++;
            continue;
        }
        $isspecial1 = $skip && $r['subject_code'] === '-' && (is_null($r['credit']));
        $isspecial2 = $skip && $r['subject_code'] === '-';
        if ($isspecial1 || $isspecial2) {
            $conditions = [
                'major_code'   => $r['major_code'],
                'subject_name' => $r['subject_name'],
            ];
        } else {
            $conditions = [
                'subject_code' => $r['subject_code'],
                'major_code'   => $r['major_code'],
                'target_code'  => $r['target_code'],
            ];
        }

        if ($enable_train_code) {
            $conditions['training_type_code'] = $r['training_type_code'];
        }
        
        $existing = $DB->get_record(
            'th_framework_study',
            $conditions,
            '*',
            IGNORE_MULTIPLE
        );

        if (!$existing) {
            $notfound++;
            continue;
        }

        if ($isspecial1 || $isspecial2) {
            $match =
                trim($existing->subject_name) === trim($r['subject_name']) &&
                trim($existing->major_code)   === trim($r['major_code']) &&
                (
                    !$enable_train_code ||
                    trim($existing->training_type_code) == trim($r['training_type_code'])
                );
        } else {
            $match =
                trim($existing->subject_name) === trim($r['subject_name']) &&
                trim($existing->major_code)   === trim($r['major_code']) &&
                trim($existing->target_code)  === trim($r['target_code']) &&
                (int)$existing->credit        === (int)$r['credit'] &&
                (int)$existing->slot          === (int)$r['slot'] &&
                (
                    !$enable_train_code ||
                    trim($existing->training_type_code) == trim($r['training_type_code'])
                );
        }
        
        if (!$match) {
            $notmatch++;
            continue;
        }

        $DB->delete_records('th_framework_study', ['id' => $existing->id]);
        $deleted++;
        }

        unset($SESSION->framework_batchdata, $SESSION->framework_action);
        $mform->display();
        echo $OUTPUT->notification(
            get_string('deletesummary', 'local_th_framework_study', [
                'deleted' => $deleted,
                'notfound' => $notfound,
                'nomatch' => $notmatch,
                'invalid' => $invalid
            ]),
            'success'
        );
        echo $OUTPUT->footer();
        exit;
    }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        foreach ($records as $r) {
            if ($r['status'] === get_string('skip', 'local_th_framework_study')) {
                $skipped++;
                continue;
            }
            if (!$skip) {
                if (!is_int($r['credit']) || $r['credit'] < 1) {
                    $skipped++;
                    continue;
                }
            } else {
                if ($r['credit'] !== null && (!is_int($r['credit']) || $r['credit'] < 1)) {
                    $skipped++;
                    continue;
                }
            }

            if (!$skip) {
                if (!is_int($r['slot']) || $r['slot'] < 1) {
                    $skipped++;
                    continue;
                }
            } else {
                if (!is_int($r['slot']) || $r['slot'] < 0) {
                    $skipped++;
                    continue;
                }
            }

            $conditions = [
                'subject_code' => $r['subject_code'],
                'major_code'   => $r['major_code'],
                'target_code'  => $r['target_code'],
            ];
            
            if ($skip && $r['subject_code'] === '-') {
                $conditions['subject_name'] = $r['subject_name'];
            }
            
            if ($enable_train_code) {
                $conditions['training_type_code'] = $r['training_type_code'];
            }
            
            $existing = $DB->get_record(
                'th_framework_study',
                $conditions,
                '*',
                IGNORE_MULTIPLE
            );

            if (!$existing) {
                $record = new stdClass();
                $record->school_id    = $r['school_id'];
                $record->subject_code = $r['subject_code'];
                $record->subject_name = $r['subject_name'];
                $record->major_code   = $r['major_code'];
                $record->target_code  = $r['target_code'];
                $record->credit       = $r['credit'];
                $record->slot         = $r['slot'];

                if ($enable_train_code) {
                    $record->training_type_code = $r['training_type_code'];
                }
                $record->timecreated  = time();
                $record->timemodified = 0;
                $record->created_by   = $USER->id;

                $DB->insert_record('th_framework_study', $record);
                $inserted++;
                continue;
            }
            $credit_changed =
                ($existing->credit === null && $r['credit'] !== null) ||
                ($existing->credit !== null && $r['credit'] === null) ||
                ($existing->credit !== null && $r['credit'] !== null && (int)$existing->credit !== (int)$r['credit']);

            $changed =
                trim($existing->subject_name) !== $r['subject_name'] ||
                $credit_changed ||
                (int)$existing->slot !== (int)$r['slot'];


            if (!$changed) {
                $skipped++;
                continue;
            }

            $existing->subject_name = $r['subject_name'];
            $existing->credit       = $r['credit'];
            $existing->slot         = $r['slot'];
            $existing->timemodified = time();
            $existing->updated_by   = $USER->id;

            $DB->update_record('th_framework_study', $existing);
            $updated++;
        }

        unset($SESSION->framework_batchdata, $SESSION->framework_action);
        $mform->display();
        echo $OUTPUT->notification(
            get_string('importsummary', 'local_th_framework_study', [
                'inserted' => $inserted,
                'updated'  => $updated,
                'skipped'  => $skipped
            ]),
            'success'
        );
    }
}

if (empty($data) && !optional_param('confirm', 0, PARAM_BOOL)) {
    $mform->display();
}

echo $OUTPUT->footer();