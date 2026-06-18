<?php
function th_question_number_report_ds_course() {
    global $DB, $CFG;
    $listcourses = [];
    $sql = "SELECT c.* FROM {course} as c, {course_categories} as ca WHERE NOT c.id = 1 AND c.visible <> 0 AND c.fullname NOT LIKE '% - mẫu' AND ca.id = c.category AND ca.visible = 1";
    $courses = $DB->get_records_sql($sql);
    
    if (!empty($courses)) {
        foreach ($courses as $id => $course) {
            $listcourses[$course->id] = $course->shortname;
        }
    }
    return $listcourses;
}
?>