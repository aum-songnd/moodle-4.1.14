<?php

class block_th_my_curriculum extends block_base {
    public function init() {
        $this->title = get_string('title', 'block_th_my_curriculum');
    }

    public function get_content() {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/lib/completionlib.php'); 

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        // Lấy tất cả bản ghi từ cơ sở dữ liệu với DISTINCT để tránh trùng lặp
        $records = $DB->get_records_sql("SELECT DISTINCT all_remote_course FROM {th_bundleassign_api} WHERE userid = $USER->id AND status = 1;");

        if ($records) {
            // Gộp tất cả dữ liệu JSON từ các bản ghi vào một mảng duy nhất
            $mergedData = [];
            foreach ($records as $record) {
                if (isset($record->all_remote_course)) {
                    $data = json_decode($record->all_remote_course, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Chỉ gộp dữ liệu khi course_combo_shortname bị trùng
                        foreach ($data as $entry) {
                            $key = $entry['course_combo_shortname'];
                            if (!isset($mergedData[$key])) {
                                $mergedData[$key] = [];
                            }
                            // Giữ nguyên các bản ghi có cùng course_combo_shortname nhưng khác các trường còn lại
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

                // Thêm CSS vào bảng
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

                // Xử lý và hiển thị dữ liệu trong bảng
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

                $this->content->text = $tableHTML;
                return $this->content;
            }
        }

        return null;
    }
}
