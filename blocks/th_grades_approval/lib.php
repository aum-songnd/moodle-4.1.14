<?php

require_once($CFG->dirroot. '/grade/report/grader/lib.php');
require_once($CFG->dirroot. '/grade/lib.php');

class th_grade_report_grader1 extends grade_report_grader {

	public function get_grade_table1($displayaverages = false) {
        global $OUTPUT,$PAGE;
        $leftrows = $this->get_left_rows1($displayaverages);
        $rightrows = $this->get_right_rows1($displayaverages);

        $html = '';

        $fulltable = new html_table();
        $fulltable->attributes['class'] = 'gradereport-grader-table';
        
        $fulltable->attributes['border'] = '1';
        $fulltable->attributes['style'] = "width: 100%; text-align:center;";
        
        $fulltable->id = 'user-grades';
        $fulltable->caption = get_string('summarygrader', 'gradereport_grader');
        $fulltable->captionhide = true;

        // Extract rows from each side (left and right) and collate them into one row each
        foreach ($leftrows as $key => $row) {
            $row->cells = array_merge($row->cells, $rightrows[$key]->cells);
            $fulltable->data[] = $row;
        }
        $html .= html_writer::table($fulltable);
        return $OUTPUT->container($html, 'gradeparent');
    }

    public function get_left_rows1($displayaverages) {
        global $CFG, $USER, $OUTPUT;

        $rows = array();

        $showuserimage = $this->get_pref('showuserimage');
        // FIXME: MDL-52678 This get_capability_info is hacky and we should have an API for inserting grade row links instead.
        $canseeuserreport = false;
        $canseesingleview = false;
        if (get_capability_info('gradereport/'.$CFG->grade_profilereport.':view')) {
            $canseeuserreport = has_capability('gradereport/'.$CFG->grade_profilereport.':view', $this->context);
        }
        if (get_capability_info('gradereport/singleview:view')) {
            $canseesingleview = has_all_capabilities(array('gradereport/singleview:view', 'moodle/grade:viewall',
            'moodle/grade:edit'), $this->context);
        }
        $hasuserreportcell = $canseeuserreport || $canseesingleview;
        $viewfullnames = has_capability('moodle/site:viewfullnames', $this->context);

        $strfeedback  = $this->get_lang_string("feedback");
        $strgrade     = $this->get_lang_string('grade');

        $extrafields = get_extra_user_fields($this->context);

        $arrows = $this->get_sort_arrows1($extrafields);

        $colspan = 1 + $hasuserreportcell + count($extrafields);

        $levels = count($this->gtree->levels) - 1;

        $fillercell = new html_table_cell();
        $fillercell->header = true;
        $fillercell->attributes['scope'] = 'col';
        $fillercell->attributes['class'] = 'cell topleft';
        $fillercell->text = html_writer::span(get_string('participants'), 'accesshide');
        $fillercell->colspan = $colspan;
        $fillercell->rowspan = $levels;
        $row = new html_table_row(array($fillercell));
        $rows[] = $row;

        for ($i = 1; $i < $levels; $i++) {
            $row = new html_table_row();
            $rows[] = $row;
        }

        $headerrow = new html_table_row();
        $headerrow->attributes['class'] = 'heading';

        $studentheader = new html_table_cell();
        // The browser's scrollbar may partly cover (in certain operative systems) the content in the student header
        // when horizontally scrolling through the table contents (most noticeable when in RTL mode).
        // Therefore, add slight padding on the left or right when using RTL mode.
        $studentheader->attributes['class'] = "header pl-3";
        $studentheader->scope = 'col';
        $studentheader->header = true;
        $studentheader->id = 'studentheader';
        if ($hasuserreportcell) {
            $studentheader->colspan = 2;
        }
        $studentheader->text = $arrows['studentname'];

        $headerrow->cells[] = $studentheader;

        foreach ($extrafields as $field) {
            $fieldheader = new html_table_cell();
            $fieldheader->attributes['class'] = 'header userfield user' . $field;
            $fieldheader->scope = 'col';
            $fieldheader->header = true;
            $fieldheader->text = $arrows[$field];

            $headerrow->cells[] = $fieldheader;
        }

        $rows[] = $headerrow;

        $rows = $this->get_left_icons_row($rows, $colspan);

        $suspendedstring = null;

        $usercount = 0;
        foreach ($this->users as $userid => $user) {
            $userrow = new html_table_row();
            $userrow->id = 'fixed_user_'.$userid;
            $userrow->attributes['class'] = ($usercount % 2) ? 'userrow even' : 'userrow odd';

            $usercell = new html_table_cell();
            $usercell->attributes['class'] = ($usercount % 2) ? 'header user even' : 'header user odd';
            $usercount++;

            $usercell->header = true;
            $usercell->scope = 'row';

            if ($showuserimage) {
                $usercell->text = $OUTPUT->user_picture($user, ['link' => false, 'visibletoscreenreaders' => false]);
            }

            $fullname = fullname($user, $viewfullnames);
            $usercell->text = html_writer::link(
                    new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $this->course->id]),
                    $usercell->text . $fullname,
                    ['class' => 'username']
            );

            if (!empty($user->suspendedenrolment)) {
                $usercell->attributes['class'] .= ' usersuspended';

                //may be lots of suspended users so only get the string once
                if (empty($suspendedstring)) {
                    $suspendedstring = get_string('userenrolmentsuspended', 'grades');
                }
                $icon = $OUTPUT->pix_icon('i/enrolmentsuspended', $suspendedstring);
                $usercell->text .= html_writer::tag('span', $icon, array('class'=>'usersuspendedicon'));
            }
            // The browser's scrollbar may partly cover (in certain operative systems) the content in the user cells
            // when horizontally scrolling through the table contents (most noticeable when in RTL mode).
            // Therefore, add slight padding on the left or right when using RTL mode.
            $usercell->attributes['class'] .= ' pl-3';

            $userrow->cells[] = $usercell;

            $userreportcell = new html_table_cell();
            $userreportcell->attributes['class'] = 'userreport';
            $userreportcell->header = false;
            if ($canseeuserreport) {
                $a = new stdClass();
                $a->user = $fullname;
                $strgradesforuser = get_string('gradesforuser', 'grades', $a);
                $url = new moodle_url('/grade/report/'.$CFG->grade_profilereport.'/index.php',
                        ['userid' => $user->id, 'id' => $this->course->id]);
                $userreportcell->text .= $OUTPUT->action_icon($url, new pix_icon('t/grades', ''), null,
                        ['title' => $strgradesforuser, 'aria-label' => $strgradesforuser]);
            }

            if ($canseesingleview) {
                $strsingleview = get_string('singleview', 'grades', $fullname);
                $url = new moodle_url('/grade/report/singleview/index.php',
                        ['id' => $this->course->id, 'itemid' => $user->id, 'item' => 'user']);
                $singleview = $OUTPUT->action_icon($url, new pix_icon('t/editstring', ''), null,
                        ['title' => $strsingleview, 'aria-label' => $strsingleview]);
                $userreportcell->text .= $singleview;
            }

            if ($userreportcell->text) {
                $userrow->cells[] = $userreportcell;
            }

            foreach ($extrafields as $field) {
                $fieldcell = new html_table_cell();
                $fieldcell->attributes['class'] = 'userfield user' . $field;
                $fieldcell->header = false;
                $fieldcell->text = s($user->{$field});
                $userrow->cells[] = $fieldcell;
            }

            $userrow->attributes['data-uid'] = $userid;
            $rows[] = $userrow;
        }

        $rows = $this->get_left_range_row($rows, $colspan);
        if ($displayaverages) {
            $rows = $this->get_left_avg_row($rows, $colspan, true);
            $rows = $this->get_left_avg_row($rows, $colspan);
        }

        return $rows;
    }

    protected function get_course_header1($element) {
        global $OUTPUT;

        $icon = '';
        // If object is a category, display expand/contract icon.
        if ($element['type'] == 'category') {
            // Load language strings.
            $strswitchminus = $this->get_lang_string('aggregatesonly', 'grades');
            $strswitchplus  = $this->get_lang_string('gradesonly', 'grades');
            $strswitchwhole = $this->get_lang_string('fullmode', 'grades');

            $url = new moodle_url($this->gpr->get_return_url(null, array('target' => $element['eid'], 'sesskey' => sesskey())));

            if (in_array($element['object']->id, $this->collapsed['aggregatesonly'])) {
                $url->param('action', 'switch_plus');
                $icon = $OUTPUT->action_icon($url, new pix_icon('t/switch_plus', ''), null,
                        ['title' => $strswitchplus, 'aria-label' => $strswitchplus]);
                $showing = get_string('showingaggregatesonly', 'grades');
            } else if (in_array($element['object']->id, $this->collapsed['gradesonly'])) {
                $url->param('action', 'switch_whole');
                $icon = $OUTPUT->action_icon($url, new pix_icon('t/switch_whole', ''), null,
                        ['title' => $strswitchwhole, 'aria-label' => $strswitchwhole]);
                $showing = get_string('showinggradesonly', 'grades');
            } else {
                $url->param('action', 'switch_minus');
                $icon = $OUTPUT->action_icon($url, new pix_icon('t/switch_minus', ''), null,
                        ['title' => $strswitchminus, 'aria-label' => $strswitchminus]);
                $showing = get_string('showingfullmode', 'grades');
            }
        }

        $name = $element['object']->get_name();
        $describedbyid = uniqid();
        $courseheader = html_writer::tag('span', $name, [
            'title' => $name,
            'class' => 'gradeitemheader',
            'aria-describedby' => $describedbyid
        ]);
        $courseheader .= html_writer::div($showing, 'sr-only', [
            'id' => $describedbyid
        ]);
        

        return $courseheader;
    }

    public function get_right_rows1($displayaverages) {
        global $CFG, $USER, $OUTPUT, $DB, $PAGE, $COURSE;

        $rows = array();
        $this->rowcount = 0;
        $numrows = count($this->gtree->get_levels());
        $numusers = count($this->users);
        $gradetabindex = 1;
        $columnstounset = array();
        $strgrade = $this->get_lang_string('grade');
        $strfeedback  = $this->get_lang_string("feedback");
        $arrows = $this->get_sort_arrows();

        $jsarguments = array(
            'cfg'       => array('ajaxenabled'=>false),
            'items'     => array(),
            'users'     => array(),
            'feedback'  => array(),
            'grades'    => array()
        );
        $jsscales = array();

        // Get preferences once.
        $showactivityicons = $this->get_pref('showactivityicons');
        $quickgrading = $this->get_pref('quickgrading');
        $showquickfeedback = $this->get_pref('showquickfeedback');
        $enableajax = $this->get_pref('enableajax');
        $showanalysisicon = $this->get_pref('showanalysisicon');

        // Get strings which are re-used inside the loop.
        $strftimedatetimeshort = get_string('strftimedatetimeshort');
        $strexcludedgrades = get_string('excluded', 'grades');
        $strerror = get_string('error');

        $viewfullnames = has_capability('moodle/site:viewfullnames', $this->context);

        foreach ($this->gtree->get_levels() as $key => $row) {
            $headingrow = new html_table_row();
            $headingrow->attributes['class'] = 'heading_name_row';

            foreach ($row as $columnkey => $element) {
                $sortlink = clone($this->baseurl);
                if (isset($element['object']->id)) {
                    $sortlink->param('sortitemid', $element['object']->id);
                }

                $eid    = $element['eid'];
                $object = $element['object'];
                $type   = $element['type'];
                $categorystate = @$element['categorystate'];

                if (!empty($element['colspan'])) {
                    $colspan = $element['colspan'];
                } else {
                    $colspan = 1;
                }

                if (!empty($element['depth'])) {
                    $catlevel = 'catlevel'.$element['depth'];
                } else {
                    $catlevel = '';
                }

                // Element is a filler
                if ($type == 'filler' or $type == 'fillerfirst' or $type == 'fillerlast') {
                    $fillercell = new html_table_cell();
                    $fillercell->attributes['class'] = $type . ' ' . $catlevel;
                    $fillercell->colspan = $colspan;
                    $fillercell->text = '&nbsp;';

                    // This is a filler cell; don't use a <th>, it'll confuse screen readers.
                    $fillercell->header = false;
                    $headingrow->cells[] = $fillercell;
                } else if ($type == 'category') {
                    // Make sure the grade category has a grade total or at least has child grade items.
                    if (grade_tree::can_output_item($element)) {
                        // Element is a category.
                        $categorycell = new html_table_cell();
                        $categorycell->attributes['class'] = 'category ' . $catlevel;
                        $categorycell->colspan = $colspan;
                        $categorycell->text = $this->get_course_header1($element);
                        $categorycell->header = true;
                        $categorycell->scope = 'col';

                        // Print icons.
                        if ($USER->gradeediting[$this->courseid]) {
                            $categorycell->text .= $this->get_icons($element);
                        }

                        $headingrow->cells[] = $categorycell;
                    }
                } else {
                    // Element is a grade_item
                    if ($element['object']->id == $this->sortitemid) {
                        if ($this->sortorder == 'ASC') {
                            $arrow = $this->get_sort_arrow('up', $sortlink);
                        } else {
                            $arrow = $this->get_sort_arrow('down', $sortlink);
                        }
                    } else {
                        $arrow = $this->get_sort_arrow('move', $sortlink);
                    }

                    //chinh sua headerlink
                    // $headerlink = $this->gtree->get_element_header($element, true, $showactivityicons, false, false, true);

                    $grade_structure = new grade_structure();
                    $icon = $grade_structure->get_element_icon($element, false);
                    $headerlink = $icon . $element['object']->itemname;

                    $itemcell = new html_table_cell();
                    $itemcell->attributes['class'] = $type . ' ' . $catlevel . ' highlightable'. ' i'. $element['object']->id;
                    $itemcell->attributes['data-itemid'] = $element['object']->id;

                    if ($element['object']->is_hidden()) {
                        $itemcell->attributes['class'] .= ' dimmed_text';
                    }

                    $singleview = '';

                    // FIXME: MDL-52678 This is extremely hacky we should have an API for inserting grade column links.
                    if (get_capability_info('gradereport/singleview:view')) {
                        if (has_all_capabilities(array('gradereport/singleview:view', 'moodle/grade:viewall',
                            'moodle/grade:edit'), $this->context)) {

                            $strsingleview = get_string('singleview', 'grades', $element['object']->get_name());
                            $url = new moodle_url('/grade/report/singleview/index.php', array(
                                'id' => $this->course->id,
                                'item' => 'grade',
                                'itemid' => $element['object']->id));
                            $singleview = $OUTPUT->action_icon(
                                    $url,
                                    new pix_icon('t/editstring', ''),
                                    null,
                                    ['title' => $strsingleview, 'aria-label' => $strsingleview]
                            );
                        }
                    }

                    $itemcell->colspan = $colspan;

                    //xoa icon chinh sua o header
                    // $itemcell->text = $headerlink . $arrow . $singleview;
                    $itemcell->text = $headerlink;
                    $itemcell->header = true;
                    $itemcell->scope = 'col';

                    $headingrow->cells[] = $itemcell;
                }
            }
            $rows[] = $headingrow;
        }

        $rows = $this->get_right_icons_row($rows);

        // Preload scale objects for items with a scaleid and initialize tab indices
        $scaleslist = array();
        $tabindices = array();

        foreach ($this->gtree->get_items() as $itemid => $item) {
            $scale = null;
            if (!empty($item->scaleid)) {
                $scaleslist[] = $item->scaleid;
                $jsarguments['items'][$itemid] = array('id'=>$itemid, 'name'=>$item->get_name(true), 'type'=>'scale', 'scale'=>$item->scaleid, 'decimals'=>$item->get_decimals());
            } else {
                $jsarguments['items'][$itemid] = array('id'=>$itemid, 'name'=>$item->get_name(true), 'type'=>'value', 'scale'=>false, 'decimals'=>$item->get_decimals());
            }
            $tabindices[$item->id]['grade'] = $gradetabindex;
            $tabindices[$item->id]['feedback'] = $gradetabindex + $numusers;
            $gradetabindex += $numusers * 2;
        }
        $scalesarray = array();

        if (!empty($scaleslist)) {
            $scalesarray = $DB->get_records_list('scale', 'id', $scaleslist);
        }
        $jsscales = $scalesarray;

        // Get all the grade items if the user can not view hidden grade items.
        // It is possible that the user is simply viewing the 'Course total' by switching to the 'Aggregates only' view
        // and that this user does not have the ability to view hidden items. In this case we still need to pass all the
        // grade items (in case one has been hidden) as the course total shown needs to be adjusted for this particular
        // user.
        if (!$this->canviewhidden) {
            $allgradeitems = $this->get_allgradeitems();
        }

        foreach ($this->users as $userid => $user) {

            if ($this->canviewhidden) {
                $altered = array();
                $unknown = array();
            } else {
                $usergrades = $this->allgrades[$userid];
                $hidingaffected = grade_grade::get_hiding_affected($usergrades, $allgradeitems);
                $altered = $hidingaffected['altered'];
                $unknown = $hidingaffected['unknowngrades'];
                unset($hidingaffected);
            }

            $itemrow = new html_table_row();
            $itemrow->id = 'user_'.$userid;

            $fullname = fullname($user, $viewfullnames);
            $jsarguments['users'][$userid] = $fullname;

            foreach ($this->gtree->items as $itemid => $unused) {
                $item =& $this->gtree->items[$itemid];
                $grade = $this->grades[$userid][$item->id];

                $itemcell = new html_table_cell();

                $itemcell->id = 'u'.$userid.'i'.$itemid;
                $itemcell->attributes['data-itemid'] = $itemid;

                // Get the decimal points preference for this item
                $decimalpoints = $item->get_decimals();

                if (array_key_exists($itemid, $unknown)) {
                    $gradeval = null;
                } else if (array_key_exists($itemid, $altered)) {
                    $gradeval = $altered[$itemid];
                } else {
                    $gradeval = $grade->finalgrade;
                }
                if (!empty($grade->finalgrade)) {
                    $gradevalforjs = null;
                    if ($item->scaleid && !empty($scalesarray[$item->scaleid])) {
                        $gradevalforjs = (int)$gradeval;
                    } else {
                        $gradevalforjs = format_float($gradeval, $decimalpoints);
                    }
                    $jsarguments['grades'][] = array('user'=>$userid, 'item'=>$itemid, 'grade'=>$gradevalforjs);
                }

                // MDL-11274
                // Hide grades in the grader report if the current grader doesn't have 'moodle/grade:viewhidden'
                if (!$this->canviewhidden and $grade->is_hidden()) {
                    if (!empty($CFG->grade_hiddenasdate) and $grade->get_datesubmitted() and !$item->is_category_item() and !$item->is_course_item()) {
                        // the problem here is that we do not have the time when grade value was modified, 'timemodified' is general modification date for grade_grades records
                        $itemcell->text = "<span class='datesubmitted'>" .
                                userdate($grade->get_datesubmitted(), $strftimedatetimeshort) . "</span>";
                    } else {
                        $itemcell->text = '-';
                    }
                    $itemrow->cells[] = $itemcell;
                    continue;
                }

                // emulate grade element
                $eid = $this->gtree->get_grade_eid($grade);
                $element = array('eid'=>$eid, 'object'=>$grade, 'type'=>'grade');

                $itemcell->attributes['class'] .= ' grade i'.$itemid;
                if ($item->is_category_item()) {
                    $itemcell->attributes['class'] .= ' cat';
                }
                if ($item->is_course_item()) {
                    $itemcell->attributes['class'] .= ' course';
                }
                if ($grade->is_overridden()) {
                    $itemcell->attributes['class'] .= ' overridden';
                    $itemcell->attributes['aria-label'] = get_string('overriddengrade', 'gradereport_grader');
                }

                if (!empty($grade->feedback)) {
                    $feedback = wordwrap(trim(format_string($grade->feedback, $grade->feedbackformat)), 34, '<br>');
                    $itemcell->attributes['data-feedback'] = $feedback;
                    $jsarguments['feedback'][] = array('user'=>$userid, 'item'=>$itemid, 'content' => $feedback);
                }

                if ($grade->is_excluded()) {
                    // Adding white spaces before and after to prevent a screenreader from
                    // thinking that the words are attached to the next/previous <span> or text.
                    $itemcell->text .= " <span class='excludedfloater'>" . $strexcludedgrades . "</span> ";
                }

                // Do not show any icons if no grade (no record in DB to match)
                if (!$item->needsupdate and $USER->gradeediting[$this->courseid]) {
                    $itemcell->text .= $this->get_icons($element);
                }

                $hidden = '';
                if ($grade->is_hidden()) {
                    $hidden = ' dimmed_text ';
                }

                $gradepass = ' gradefail ';
                if ($grade->is_passed($item)) {
                    $gradepass = ' gradepass ';
                } else if (is_null($grade->is_passed($item))) {
                    $gradepass = '';
                }

                // if in editing mode, we need to print either a text box
                // or a drop down (for scales)
                // grades in item of type grade category or course are not directly editable
                if ($item->needsupdate) {
                    $itemcell->text .= "<span class='gradingerror{$hidden}'>" . $strerror . "</span>";

                } else if ($USER->gradeediting[$this->courseid]) {

                    if ($item->scaleid && !empty($scalesarray[$item->scaleid])) {
                        $itemcell->attributes['class'] .= ' grade_type_scale';
                    } else if ($item->gradetype == GRADE_TYPE_VALUE) {
                        $itemcell->attributes['class'] .= ' grade_type_value';
                    } else if ($item->gradetype == GRADE_TYPE_TEXT) {
                        $itemcell->attributes['class'] .= ' grade_type_text';
                    }

                    if ($item->scaleid && !empty($scalesarray[$item->scaleid])) {
                        $scale = $scalesarray[$item->scaleid];
                        $gradeval = (int)$gradeval; // scales use only integers
                        $scales = explode(",", $scale->scale);
                        // reindex because scale is off 1

                        // MDL-12104 some previous scales might have taken up part of the array
                        // so this needs to be reset
                        $scaleopt = array();
                        $i = 0;
                        foreach ($scales as $scaleoption) {
                            $i++;
                            $scaleopt[$i] = $scaleoption;
                        }

                        if ($quickgrading and $grade->is_editable()) {
                            $oldval = empty($gradeval) ? -1 : $gradeval;
                            if (empty($item->outcomeid)) {
                                $nogradestr = $this->get_lang_string('nograde');
                            } else {
                                $nogradestr = $this->get_lang_string('nooutcome', 'grades');
                            }
                            $attributes = array('tabindex' => $tabindices[$item->id]['grade'], 'id'=>'grade_'.$userid.'_'.$item->id);
                            $gradelabel = $fullname . ' ' . $item->get_name(true);
                            $itemcell->text .= html_writer::label(
                                get_string('useractivitygrade', 'gradereport_grader', $gradelabel), $attributes['id'], false,
                                    array('class' => 'accesshide'));
                            $itemcell->text .= html_writer::select($scaleopt, 'grade['.$userid.']['.$item->id.']', $gradeval, array(-1=>$nogradestr), $attributes);
                        } else if (!empty($scale)) {
                            $scales = explode(",", $scale->scale);

                            // invalid grade if gradeval < 1
                            if ($gradeval < 1) {
                                $itemcell->text .= "<span class='gradevalue{$hidden}{$gradepass}'>-</span>";
                            } else {
                                $gradeval = $grade->grade_item->bounded_grade($gradeval); //just in case somebody changes scale
                                $itemcell->text .= "<span class='gradevalue{$hidden}{$gradepass}'>{$scales[$gradeval - 1]}</span>";
                            }
                        }

                    } else if ($item->gradetype != GRADE_TYPE_TEXT) { // Value type
                        if ($quickgrading and $grade->is_editable()) {
                            $value = format_float($gradeval, $decimalpoints);
                            $gradelabel = $fullname . ' ' . $item->get_name(true);
                            $itemcell->text .= '<label class="accesshide" for="grade_'.$userid.'_'.$item->id.'">'
                                          .get_string('useractivitygrade', 'gradereport_grader', $gradelabel).'</label>';
                            $itemcell->text .= '<input size="6" tabindex="' . $tabindices[$item->id]['grade']
                                          . '" type="text" class="text" title="'. $strgrade .'" name="grade['
                                          .$userid.'][' .$item->id.']" id="grade_'.$userid.'_'.$item->id.'" value="'.$value.'" />';
                        } else {
                            $itemcell->text .= "<span class='gradevalue{$hidden}{$gradepass}'>" .
                                    format_float($gradeval, $decimalpoints) . "</span>";
                        }
                    }

                    // If quickfeedback is on, print an input element
                    if ($showquickfeedback and $grade->is_editable()) {
                        $feedbacklabel = $fullname . ' ' . $item->get_name(true);
                        $itemcell->text .= '<label class="accesshide" for="feedback_'.$userid.'_'.$item->id.'">'
                                      .get_string('useractivityfeedback', 'gradereport_grader', $feedbacklabel).'</label>';
                        $itemcell->text .= '<input class="quickfeedback" tabindex="' . $tabindices[$item->id]['feedback'].'" id="feedback_'.$userid.'_'.$item->id
                                      . '" size="6" title="' . $strfeedback . '" type="text" name="feedback['.$userid.']['.$item->id.']" value="' . s($grade->feedback) . '" />';
                    }

                } else { // Not editing
                    $gradedisplaytype = $item->get_displaytype();

                    if ($item->scaleid && !empty($scalesarray[$item->scaleid])) {
                        $itemcell->attributes['class'] .= ' grade_type_scale';
                    } else if ($item->gradetype == GRADE_TYPE_VALUE) {
                        $itemcell->attributes['class'] .= ' grade_type_value';
                    } else if ($item->gradetype == GRADE_TYPE_TEXT) {
                        $itemcell->attributes['class'] .= ' grade_type_text';
                    }

                    // Only allow edting if the grade is editable (not locked, not in a unoverridable category, etc).
                    if ($enableajax && $grade->is_editable()) {
                        // If a grade item is type text, and we don't have show quick feedback on, it can't be edited.
                        if ($item->gradetype != GRADE_TYPE_TEXT || $showquickfeedback) {
                            $itemcell->attributes['class'] .= ' clickable';
                        }
                    }

                    if ($item->needsupdate) {
                        $itemcell->text .= "<span class='gradingerror{$hidden}{$gradepass}'>" . $error . "</span>";
                    } else {
                        // The max and min for an aggregation may be different to the grade_item.
                        if (!is_null($gradeval)) {
                            $item->grademax = $grade->get_grade_max();
                            $item->grademin = $grade->get_grade_min();
                        }

                        $itemcell->text .= "<span class='gradevalue{$hidden}{$gradepass}'>" .
                                grade_format_gradevalue($gradeval, $item, true, $gradedisplaytype, null) . "</span>";

                        //xoa icon preview
                        // if ($showanalysisicon) {
                        //     $itemcell->text .= $this->gtree->get_grade_analysis_icon($grade);
                        // }
                    }
                }

                // Enable keyboard navigation if the grade is editable (not locked, not in a unoverridable category, etc).
                if ($enableajax && $grade->is_editable()) {
                    // If a grade item is type text, and we don't have show quick feedback on, it can't be edited.
                    if ($item->gradetype != GRADE_TYPE_TEXT || $showquickfeedback) {
                        $itemcell->attributes['class'] .= ' gbnavigable';
                    }
                }

                if (!empty($this->gradeserror[$item->id][$userid])) {
                    $itemcell->text .= $this->gradeserror[$item->id][$userid];
                }

                $itemrow->cells[] = $itemcell;
            }
            $rows[] = $itemrow;
        }

        if ($enableajax) {
            $jsarguments['cfg']['ajaxenabled'] = true;
            $jsarguments['cfg']['scales'] = array();
            foreach ($jsscales as $scale) {
                // Trim the scale values, as they may have a space that is ommitted from values later.
                $jsarguments['cfg']['scales'][$scale->id] = array_map('trim', explode(',', $scale->scale));
            }
            $jsarguments['cfg']['feedbacktrunclength'] =  $this->feedback_trunc_length;

            // Student grades and feedback are already at $jsarguments['feedback'] and $jsarguments['grades']
        }
        $jsarguments['cfg']['isediting'] = (bool)$USER->gradeediting[$this->courseid];
        $jsarguments['cfg']['courseid'] = $this->courseid;
        $jsarguments['cfg']['studentsperpage'] = $this->get_students_per_page();
        $jsarguments['cfg']['showquickfeedback'] = (bool) $showquickfeedback;

        $module = array(
            'name'      => 'gradereport_grader',
            'fullpath'  => '/grade/report/grader/module.js',
            'requires'  => array('base', 'dom', 'event', 'event-mouseenter', 'event-key', 'io-queue', 'json-parse', 'overlay')
        );
        $PAGE->requires->js_init_call('M.gradereport_grader.init_report', $jsarguments, false, $module);
        $PAGE->requires->strings_for_js(array('addfeedback', 'feedback', 'grade'), 'grades');
        $PAGE->requires->strings_for_js(array('ajaxchoosescale', 'ajaxclicktoclose', 'ajaxerror', 'ajaxfailedupdate', 'ajaxfieldchanged'), 'gradereport_grader');
        if (!$enableajax && $USER->gradeediting[$this->courseid]) {
            $PAGE->requires->yui_module('moodle-core-formchangechecker',
                    'M.core_formchangechecker.init',
                    array(array(
                        'formid' => 'gradereport_grader'
                    ))
            );
            $PAGE->requires->string_for_js('changesmadereallygoaway', 'moodle');
        }

        $rows = $this->get_right_range_row($rows);
        if ($displayaverages) {
            $rows = $this->get_right_avg_row($rows, true);
            $rows = $this->get_right_avg_row($rows);
        }

        return $rows;
    }

    public function get_sort_arrows1(array $extrafields = array()) {
        global $OUTPUT, $CFG;
        $arrows = array();

        $strsortasc   = $this->get_lang_string('sortasc', 'grades');
        $strsortdesc  = $this->get_lang_string('sortdesc', 'grades');
        $iconasc = $OUTPUT->pix_icon('t/sort_asc', $strsortasc, '', array('class' => 'iconsmall sorticon'));
        $icondesc = $OUTPUT->pix_icon('t/sort_desc', $strsortdesc, '', array('class' => 'iconsmall sorticon'));

        // Sourced from tablelib.php
        // Check the full name display for sortable fields.
        if (has_capability('moodle/site:viewfullnames', $this->context)) {
            $nameformat = $CFG->alternativefullnameformat;
        } else {
            $nameformat = $CFG->fullnamedisplay;
        }

        if ($nameformat == 'language') {
            $nameformat = get_string('fullnamedisplay');
        }

        $arrows['studentname'] = '';
        $requirednames = order_in_string(get_all_user_name_fields(), $nameformat);
        if (!empty($requirednames)) {
            foreach ($requirednames as $name) {
                $arrows['studentname'] .= $this->get_lang_string($name);
                if ($this->sortitemid == $name) {
                    $arrows['studentname'] .= $this->sortorder == 'ASC' ? $iconasc : $icondesc;
                }
                $arrows['studentname'] .= ' / ';
            }

            $arrows['studentname'] = substr($arrows['studentname'], 0, -3);
        }

        foreach ($extrafields as $field) {
            $fieldlink = get_user_field_name($field);
            $arrows[$field] = $fieldlink;

            if ($field == $this->sortitemid) {
                if ($this->sortorder == 'ASC') {
                    $arrows[$field] .= $iconasc;
                } else {
                    $arrows[$field] .= $icondesc;
                }
            }
        }

        return $arrows;
    }
}