<?php

    require_once '../../config.php';
    require_once $CFG->libdir . '/adminlib.php';
    require_once $CFG->dirroot . '/local/th_question_bank_segregator/lib.php';
    require_once $CFG->dirroot . '/lib/questionlib.php';
    require_once $CFG->dirroot . '/question/editlib.php';
    require_once($CFG->dirroot."/question/category_class.php");

    $courseid = optional_param('courseid', 0, PARAM_INT);
    $ratio = optional_param('ratio', '', PARAM_TEXT);

    list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) = question_edit_setup('categories', '/question/category.php');

    $qcobject = new question_category_object($pagevars['cpage'], $thispageurl,
            $contexts->having_one_edit_tab_cap('categories'), 0,
            $pagevars['cat'], 0, $contexts->having_cap('moodle/question:add'));

    $context_course = context_course::instance($courseid);
    $top_cate = $DB->get_field('question_categories', 'id', array('parent' => 0, 'contextid' => $context_course->id));

    // $categories = get_categories_for_contexts($context_course->id, 'parent, sortorder, name ASC', false);
    $categoriesTree_obj = $DB->get_record('th_course_category_tree', array('course_id' => $courseid));
    $categories = json_decode($categoriesTree_obj->category_json);

    $cate_backup = $DB->get_record('question_categories', array('name' => 'Backup', 'contextid' => $context_course->id, 'parent' => $top_cate));
    if(!$cate_backup){
        $cate_backup_data = new stdClass();
        $cate_backup_data->parent = $top_cate . ',' . $context_course->id;
        $cate_backup_data->name = 'Backup';
        $cate_backup_data->info = '';
        $cate_backup_data->idnumber =  '';
        $cate_backup_data->infoformat = 1;

        $cate_backup = $qcobject->add_category($cate_backup_data->parent, $cate_backup_data->name,
            $cate_backup_data->info, true, $cate_backup_data->infoformat, $cate_backup_data->idnumber);
    } else {
        $all_question_backup = get_questions_category($cate_backup, true, true);
        if(count($all_question_backup) == 0){
            $cate_backup = $cate_backup->id;
        } else {
            redirect(
                $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
                get_string('backup_category_exists', 'local_th_question_bank_segregator'),
                null,
                \core\output\notification::NOTIFY_WARNING
            );            
        }
    }

    $cate_setup = $DB->get_record('question_categories', array('name' => 'Setup', 'contextid' => $context_course->id, 'parent' => $top_cate));
    if(!$cate_setup){
        $cate_setup_data = new stdClass();
        $cate_setup_data->parent = $top_cate . ',' . $context_course->id;
        $cate_setup_data->name = 'Setup';
        $cate_setup_data->info = '';
        $cate_setup_data->idnumber =  '';
        $cate_setup_data->infoformat = 1;

        $cate_setup = $qcobject->add_category($cate_setup_data->parent, $cate_setup_data->name,
            $cate_setup_data->info, true, $cate_setup_data->infoformat, $cate_setup_data->idnumber);
    } else {
        $all_question_setup = get_questions_category($cate_setup, true, true);
        if(count($all_question_backup) == 0){
            $cate_setup = $cate_setup->id;
        } else {
            redirect(
                $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
                get_string('setup_category_exists', 'local_th_question_bank_segregator'),
                null,
                \core\output\notification::NOTIFY_WARNING
            );            
        }
    }

    function splitArrayByRatio($array, $ratio) {
        $totalElements = count($array);
        $splitIndex = ceil($totalElements * $ratio);

        $firstPart = array_slice($array, 0, $splitIndex);
        $secondPart = array_slice($array, $splitIndex);

        return array($firstPart, $secondPart);
    }
    
    function buildCategoryTree($categories, $parentId, $cate_backup, $cate_setup, $context_course, $qcobject, $ratio) {
        global $DB;
    
        foreach ($categories as $category) {
            if ($category->parent == $parentId) {

                $new_sub_cate_backup = $DB->get_field('question_categories', 'id', array('name' => $category->name, 'contextid' => $context_course->id, 
                    'parent' => $cate_backup));
                if(!$new_sub_cate_backup){
                    $catedata_backup = new stdClass();
                    $catedata_backup->parent = $cate_backup . ',' . $context_course->id;
                    $catedata_backup->name = $category->name;
                    $catedata_backup->info = '';
                    $catedata_backup->idnumber =  '';
                    $catedata_backup->infoformat = 1;

                    $new_sub_cate_backup = $qcobject->add_category($catedata_backup->parent, $catedata_backup->name,
                    $catedata_backup->info, true, $catedata_backup->infoformat, $catedata_backup->idnumber);
                }
                
                $new_sub_cate_setup = $DB->get_field('question_categories', 'id', array('name' => $category->name, 'contextid' => $context_course->id, 
                    'parent' => $cate_setup));
                if(!$new_sub_cate_setup){
                    $catedata_setup = new stdClass();
                    $catedata_setup->parent = $cate_setup . ',' . $context_course->id;
                    $catedata_setup->name = $category->name;
                    $catedata_setup->info = '';
                    $catedata_setup->idnumber =  '';
                    $catedata_setup->infoformat = 1;

                    $new_sub_cate_setup = $qcobject->add_category($catedata_setup->parent, $catedata_setup->name,
                    $catedata_setup->info, true, $catedata_setup->infoformat, $catedata_setup->idnumber);
                }
                
                $all_question_old = get_questions_category($category, true, false);

                if($all_question_old){
                    $questionids_old = array();
                    foreach($all_question_old as $obj_question_old){
                        $questionids_old[] = $obj_question_old->id;
                    }
    
                    $questions_slice = splitArrayByRatio($questionids_old, $ratio);

    
                    if(count($questions_slice[0]) > 0){
                        question_move_questions_to_category(
                            $questions_slice[0],
                            $new_sub_cate_backup
                        );
                    }
                    
                    if(count($questions_slice[1]) > 0){
                        question_move_questions_to_category(
                            $questions_slice[1],
                            $new_sub_cate_setup
                        );
                    }
                }

                buildCategoryTree($categories, $category->id, $new_sub_cate_backup, $new_sub_cate_setup, $context_course, $qcobject, $ratio);
            }
        }
    
        return true;
    }
    
    $categoriesTree = buildCategoryTree($categories, $top_cate, $cate_backup, $cate_setup, $context_course, $qcobject, $ratio);

    redirect(
        $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
        get_string('question_bank_segregation_success', 'local_th_question_bank_segregator'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );    

?>