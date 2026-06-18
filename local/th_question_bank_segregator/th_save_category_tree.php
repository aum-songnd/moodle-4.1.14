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

    $categories = get_categories_for_contexts($context_course->id, 'parent, sortorder, name ASC', false);

    if($top_cate){
        $categories = get_categories_for_contexts($context_course->id, 'parent, sortorder, name ASC', false);

        function buildCategoryTree($categories, $parentId) {

            $tree = [];

            foreach ($categories as $category) {
                if ($category->parent == $parentId) {
                    $node = new stdClass();
                    $node->id = $category->id;
                    $node->name = $category->name;
                    $all_question = get_questions_category($category, true, false);
                    $list_question = array();
                    foreach($all_question as $question){
                        $list_question[] = $question->id;
                    }
                    $node->list_question = $list_question;
                    $node->sub_menu = buildCategoryTree($categories, $category->id);
                    $tree[] = $node;
                }
            }

            return $tree;
        }

        $categoriesTree = buildCategoryTree($categories, $top_cate);
        $categoriesTree_str = json_encode($categoriesTree);

        if ($data_id = $DB->get_field_sql("SELECT id FROM {th_course_category_tree} WHERE course_id = $courseid")){
            $dataobject = new stdClass();
            $dataobject->id = $data_id;
            $dataobject->course_id = $courseid;
            $dataobject->category_tree_json = $categoriesTree_str;
            $dataobject->category_json = json_encode($categories);
            $dataobject->timecreated = time();

            $DB->update_record('th_course_category_tree', $dataobject);
        } else {
            $dataobject = new stdClass();
            $dataobject->course_id = $courseid;
            $dataobject->category_tree_json = $categoriesTree_str;
            $dataobject->category_json = json_encode($categories);
            $dataobject->timecreated = time();

            $DB->insert_record('th_course_category_tree', $dataobject);
        }
        
        redirect(
            $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
            get_string('question_bank_backup_success', 'local_th_question_bank_segregator'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );        
    } else {
        redirect(
            $CFG->wwwroot . "/local/th_question_bank_segregator/view.php?courseid=$courseid",
            get_string('category_top_not_found', 'local_th_question_bank_segregator'),
            null,
            \core\output\notification::NOTIFY_WARNING
        );        
    }
?>