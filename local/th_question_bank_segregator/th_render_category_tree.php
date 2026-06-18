<?php
    require_once '../../config.php';
    require_once $CFG->libdir . '/adminlib.php';
    require_once $CFG->dirroot . '/local/th_question_bank_segregator/lib.php';
    require_once $CFG->dirroot . '/lib/questionlib.php';
    require_once $CFG->dirroot . '/question/editlib.php';
    require_once($CFG->dirroot."/question/category_class.php");

    $courseid = optional_param('courseid', 0, PARAM_INT);

    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('invalidcourse', 'local_th_question_bank_segregator', $courseid);
    }
    require_login($courseid);
    require_capability('local/th_question_bank_segregator:view', context_course::instance($courseid));

    list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) = question_edit_setup('categories', '/question/category.php');

    $qcobject = new question_category_object($pagevars['cpage'], $thispageurl,
            $contexts->having_one_edit_tab_cap('categories'), 0,
            $pagevars['cat'], 0, $contexts->having_cap('moodle/question:add'));

    $context_course = context_course::instance($courseid);
    $top_cate = $DB->get_field('question_categories', 'id', array('parent' => 0, 'contextid' => $context_course->id));

    $categoriesTree_obj = $DB->get_record('th_course_category_tree', array('course_id' => $courseid));

    if($categoriesTree_obj){
        $categoriesTree = json_decode($categoriesTree_obj->category_tree_json);

        function renderMenu($data, $qcobject, $parentid, $context_course) {
            global $DB;

            foreach ($data as $item) {
                $oldcat = $DB->get_record('question_categories', array('id' => $item->id));

                if(!$oldcat){
                    $catedata = new stdClass();
                    $catedata->id = $item->id;
                    $catedata->parent = $parentid;
                    $catedata->contextid = $context_course->id;
                    $catedata->name = $item->name;
                    $catedata->info = '';
                    $catedata->infoformat = 1;
                    $catedata->sortorder = 999;
                    $catedata->stamp = make_unique_id_code();

                    $DB->import_record("question_categories", $catedata);
                    if(!empty($item->list_question)){
                        question_move_questions_to_category(
                            $item->list_question,
                            $item->id
                        );
                    }

                    if (!empty($item->sub_menu)) {
                        renderMenu($item->sub_menu, $qcobject, $item->id, $context_course);
                    }
                } else {
                    if(!empty($item->list_question)){
                        question_move_questions_to_category(
                            $item->list_question,
                            $item->id
                        );
                    }

                    if (!empty($item->sub_menu)) {
                        renderMenu($item->sub_menu, $qcobject, $item->id, $context_course);
                    }
                }
            }
            
            return true;
        }

        renderMenu($categoriesTree, $qcobject, $top_cate, $context_course);

        $categories = get_categories_for_contexts($context_course->id, 'parent, sortorder, name ASC', false);
        $categories_old = (array) json_decode($categoriesTree_obj->category_json);

        function objectDiff($obj1, $obj2) {
            return $obj1->id - $obj2->id;
        }

        $diff_categories = array_udiff_assoc($categories, $categories_old, 'objectDiff');
        
        foreach($diff_categories as $catedata){

            if($catedata->questioncount == 0){
                $qcobject->delete_category($catedata->id);
            }
        }
        
        redirect(
            $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
            get_string('question_bank_restore_success', 'local_th_question_bank_segregator'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );        
    } else {
        redirect(
            $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
            get_string('question_bank_backup_not_found', 'local_th_question_bank_segregator'),
            null,
            \core\output\notification::NOTIFY_WARNING
        );        
    } 

?>