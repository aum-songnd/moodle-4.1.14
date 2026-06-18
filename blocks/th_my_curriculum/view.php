<?php

require_once '../../config.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_th_my_curriculum', $courseid);
}

require_login($courseid);

$PAGE->set_url('/blocks/th_my_curriculum/view.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('th_my_curriculum', 'block_th_my_curriculum'));
$PAGE->set_heading(get_string('th_my_curriculum', 'block_th_my_curriculum'));
$editurl = new moodle_url('/blocks/th_my_curriculum/view.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_my_curriculum'), $editurl);
$settingsnode->make_active();


echo $OUTPUT->header(); // Hiển thị tiêu đề trang

$content = new stdClass;
$content->text = '';

$sql = "SELECT DISTINCT all_remote_course FROM {th_bundleassign_api} WHERE userid = ? AND status = 1";
$records = $DB->get_records_sql($sql, [$USER->id]);

if ($records) {
    $content->text = "<h2>" . get_string('th_curriculum_title', 'block_th_my_curriculum') . "</h2>"; 
    $mergedData = [];
    foreach ($records as $record) {
        if (!empty($record->all_remote_course)) {
            $data = json_decode($record->all_remote_course, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($data as $entry) {
                    $key = $entry['course_combo_shortname'];
                    if (!isset($mergedData[$key])) {
                        $mergedData[$key] = [];
                    }
                    $mergedData[$key][] = $entry;
                }
            } else {
                debugging('Error decoding JSON: ' . json_last_error_msg(), DEBUG_DEVELOPER);
            }
        }
    }

    if (!empty($mergedData)) {
        $organizedData = [];

        // Tổ chức lại dữ liệu theo combo, stage và slot
        foreach ($mergedData as $combo => $entries) {
            foreach ($entries as $entry) {
                $stage = $entry['stage'];
                $slotName = $entry['slotname'];
                $organizedData[$combo][$stage][$slotName][] = $entry;
            }
        }

        // Sắp xếp các stage theo sequence_stage (sắp xếp theo thứ tự)
        ksort($organizedData);

        foreach ($organizedData as $combo => &$stages) {

            uksort($stages, function ($stageA, $stageB) use ($stages) {
        
                $seqA = PHP_INT_MAX;
                $seqB = PHP_INT_MAX;
        
                foreach ($stages[$stageA] as $slots) {
                    $seqA = min($seqA, $slots[0]['sequence_stage']);
                }
        
                foreach ($stages[$stageB] as $slots) {
                    $seqB = min($seqB, $slots[0]['sequence_stage']);
                }
        
                return $seqA <=> $seqB;
            });
            foreach ($stages as &$slots) {

                uksort($slots, function ($slotA, $slotB) use ($slots) {
        
                    $seqA = $slots[$slotA][0]['sequence_slot'];
                    $seqB = $slots[$slotB][0]['sequence_slot'];
        
                    return $seqA <=> $seqB;
                });
        
                // (optional) sort course trong cùng slot
                foreach ($slots as &$entries) {
                    usort($entries, fn($a, $b) => $a['course'] <=> $b['course']);
                }
            }
        }

        unset($stages, $slots, $entries);

        // Bảng HTML
        $tableHTML = "<style>
            table.curriculum-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 14px;
                text-align: left;
            }
            table.curriculum-table th, table.curriculum-table td {
                border: 1px solid #dddddd;
                padding: 8px 12px;
            }
            table.curriculum-table th {
                background-color: #f4f4f4;
                color: #333;
                font-weight: bold;
            }
            table.curriculum-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            table.curriculum-table tr:hover {
                background-color: #f1f1f1;
            }
        </style>";

        $tableHTML .= "<table class='curriculum-table'>";
        $tableHTML .= "<tr>
            <th>" . get_string('combo', 'block_th_my_curriculum') . "</th>
            <th>" . get_string('stage', 'block_th_my_curriculum') . "</th>
            <th>" . get_string('slotname', 'block_th_my_curriculum') . "</th>
            <th>" . get_string('course', 'block_th_my_curriculum') . "</th>
            <th>" . get_string('studytime', 'block_th_my_curriculum') . "</th>
            <th>" . get_string('dateassigned', 'block_th_my_curriculum') . "</th>
            <th>" . get_string('completionpercent', 'block_th_my_curriculum') . "</th>
        </tr>";

        foreach ($organizedData as $combo => $stages) {
            $comboRowSpan = array_sum(array_map(fn($stages) => array_sum(array_map('count', $stages)), $stages));
            $firstComboRow = true;

            foreach ($stages as $stage => $slots) {
                $stageRowSpan = array_sum(array_map('count', $slots));
                $firstStageRow = true;

                foreach ($slots as $slotName => $entries) {
                    $slotRowSpan = count($entries);
                    $firstSlotRow = true;

                    foreach ($entries as $entry) {
                        $courseShortname = $entry['course'];
                        $studyTime = $entry['study_time'];
                        $dateAssigned = date('d-m-Y', $entry['date_assign']);

                        $course = $DB->get_record('course', ['shortname' => $courseShortname], 'id');
                        $completionPercent = '-';
                        if ($course) {
                            $completion = new completion_info($course);
                            $completions = $completion->get_completions($USER->id);
                            $total = count($completions);
                            $completed = count(array_filter($completions, fn($c) => $c->is_complete()));
                            $completionPercent = $total > 0 ? round(($completed / $total) * 100) . '%' : '0%';
                        }

                        $tableHTML .= "<tr>";
                        if ($firstComboRow) {
                            $tableHTML .= "<td rowspan='{$comboRowSpan}'>{$combo}</td>";
                            $firstComboRow = false;
                        }
                        if ($firstStageRow) {
                            $tableHTML .= "<td rowspan='{$stageRowSpan}'>{$stage}</td>";
                            $firstStageRow = false;
                        }
                        if ($firstSlotRow) {
                            $tableHTML .= "<td rowspan='{$slotRowSpan}'>{$slotName}</td>";
                            $firstSlotRow = false;
                        }
                        $tableHTML .= "<td>{$courseShortname}</td>";
                        $tableHTML .= "<td>{$studyTime} tháng</td>";
                        $tableHTML .= "<td>{$dateAssigned}</td>";
                        $tableHTML .= "<td>{$completionPercent}</td>";
                        $tableHTML .= "</tr>";
                    }
                }
            }
        }

        $tableHTML .= "</table>";
        $content->text .= $tableHTML;
    } else {
        $content->text .= "<h4>" . get_string('no_curriculum', 'block_th_my_curriculum') . ' !' ."</h4>";
    }
} else {
    $content->text .= "<center><h3>" . get_string('no_curriculum', 'block_th_my_curriculum') . ' !' . "</h3></center>";
}

echo $content->text;
echo $OUTPUT->footer();
