<?php
require('../../config.php');

require_login();
require_capability('block/th_curriculum_completion_report:view', context_system::instance());

$baseurl = new moodle_url('/blocks/th_curriculum_completion_report/manage_courses.php');
$pluginname = get_string('pluginname', 'block_th_curriculum_completion_report');

$managename = get_string('managecourses', 'block_th_curriculum_completion_report');

$PAGE->set_url($baseurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title($managename);
$PAGE->set_heading($managename);
$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($managename, $baseurl);

// Handle add course.
if ($courseid = optional_param('addcourseid', 0, PARAM_INT)) {
    if ($DB->record_exists('course', ['id' => $courseid]) &&
        !$DB->record_exists('th_curriculum_courses', ['courseid' => $courseid])) {
        $DB->insert_record('th_curriculum_courses', [
            'courseid' => $courseid,
            'timemodified' => time(),
        ]);
        redirect($PAGE->url);
    }
}

// Handle delete.
if ($delid = optional_param('del', 0, PARAM_INT)) {
    $DB->delete_records('th_curriculum_courses', ['id' => $delid]);
    redirect($PAGE->url);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($managename);



// Show add course form.
$allcourses = $DB->get_records_menu('course', ['visible' => 1], 'shortname', 'id, shortname');
$selectedids = $DB->get_records_menu('th_curriculum_courses', null, '', 'courseid, id');

$select = new single_select($PAGE->url, 'addcourseid', $allcourses);
$select->label = get_string('selectcourse', 'block_th_curriculum_completion_report');
echo $OUTPUT->render($select);

// Show course list.
$courses = $DB->get_records_sql("
    SELECT b.id, c.shortname, c.fullname
    FROM {th_curriculum_courses} b
    JOIN {course} c ON c.id = b.courseid
    ORDER BY c.shortname
");

if ($courses) {
    $table = new html_table();
    $table->head = [get_string('shortname'), get_string('fullname'), get_string('actions')];
    foreach ($courses as $c) {
        $deletebtn = html_writer::tag('button', get_string('delete'), [
            'type' => 'button',
            'class' => 'btn btn-danger btn-sm confirm-delete-course',
            'data-courseid' => $c->id
        ]);
        $table->data[] = [$c->shortname, $c->fullname, $deletebtn];
    }
    echo html_writer::table($table);
}

$PAGE->requires->js_amd_inline("
    require(['core/notification'], function(Notification) {
        document.querySelectorAll('.confirm-delete-course').forEach(function(button) {
            button.addEventListener('click', function() {
                const courseid = button.dataset.courseid;
                Notification.confirm(
                    '" . get_string('confirmdelete', 'block_th_curriculum_completion_report') . "',
                    '" . get_string('confirmdeletecontent', 'block_th_curriculum_completion_report') . "',
                    '" . get_string('yes') . "',
                    '" . get_string('no') . "',
                    function() {
                        window.location.href = '" . $PAGE->url->out(false) . "?del=' + courseid;
                    }
                );
            });
        });
    });
");

echo $OUTPUT->footer();
