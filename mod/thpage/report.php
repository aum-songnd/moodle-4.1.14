<?php

require_once '../../config.php';
require_once $CFG->dirroot .'/mod/thpage/classes/report_form.php';
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
    print_error('invalidcourse');
}

$context = context_system::instance();
require_login($COURSE->id);
require_capability('mod/thpage:viewall', $context);

$pageurl = '/mod/thpage/report.php';
$title = get_string('pluginname', 'mod_thpage');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'mod_thpage'));
$PAGE->set_title(get_string('pluginname', 'mod_thpage'));
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.thpage', 'LIST GVCN/QLHT', current_language()));

$editurl = new moodle_url($pageurl);
$settingsnode = $PAGE->navbar->add(get_string('pluginname', 'mod_thpage'), $editurl);
$settingsnode->make_active();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('list_pdf', 'mod_thpage'));

$mform = new report_form();
$mform->display();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
   
} else if ($fromform = $mform->get_data()) {

    $date_from = $fromform->form_date_from + strtotime("+23 hours 59 minutes 59 seconds", 0);
    $date_to = $fromform->form_date_to + strtotime("+23 hours 59 minutes 59 seconds", 0);

    $params = array('date_from' => $date_from, 'date_to' => $date_to);
    $sql = "SELECT id,fullname,shortname
        FROM {course}
        WHERE startdate > :date_from and startdate <= :date_to";
    $courses = $DB->get_records_sql($sql, $params);

    $table = new html_table();
    $table->head = array('STT',
                        get_string('course'),
                        get_string('shortname'),
                        get_string('module', 'mod_thpage'),
                        get_string('status'),
                        get_string('create', 'mod_thpage'),
                        get_string('timecreated', 'mod_thpage'),
                        );
    $table->attributes          = array('class' => 'thpage', 'border' => '1');
    $table->attributes['style'] = "width: 100%; text-align:center;";
    $stt = 0;
    foreach ($courses as $key => $c) {
    
        $courseid = $c->id;

        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            continue;
        }

        $sql = "SELECT cm.id,cm.instance,ctx.id as contextid,p.name
            from {course_modules} cm
            join {thpage} p on p.id=cm.instance
            join {context} ctx on ctx.instanceid=cm.id
            where cm.course=:course and cm.module=(select id from {modules} where name=:name) order by cm.course DESC";
        $params = array('course' => $courseid, 'name' => 'thpage');
        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $key => $record) {
            if (!$thpage = $DB->get_record('thpage', array('id' => $record->instance))) {
                continue;
            }
            if (!$cm = $DB->get_record('course_modules', array('id' => $record->id))) {
               continue;
            }
            $url_course = new moodle_url('/course/view.php', array('id' => $c->id));
            $url_coursemodule = new moodle_url('/mod/thpage/view.php', array('id' => $cm->id));

            $filename = get_string('dcct', 'mod_thpage') . '.pdf';
            $fs = get_file_storage();
            $fileinfo = [
                'contextid' => $record->contextid,
                'component' => 'mod_thpage',
                'filearea'  => 'pdf',
                'itemid'    => 0,
                'filepath'  => '/',
                'filename'  => $filename,
            ];

            $file_exists = $fs->file_exists(
                $fileinfo['contextid'], 
                $fileinfo['component'], 
                $fileinfo['filearea'],
                $fileinfo['itemid'], 
                $fileinfo['filepath'], 
                $fileinfo['filename']
            );

            if ($file_exists) {
                $file = $fs->get_file(
                    $fileinfo['contextid'],
                    $fileinfo['component'],
                    $fileinfo['filearea'],
                    $fileinfo['itemid'],
                    $fileinfo['filepath'],
                    $fileinfo['filename']
                );
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false                
                );
                $status = html_writer::link($url, get_string('view'), ['target' => '_blank']);
                $time = date('d/m/Y H:i:s', $file->get_timecreated());
            } else {
                $status = get_string('no_file', 'mod_thpage');
                $time = '';
            }

            $output = html_writer::start_tag('form',
                    array('action' => new moodle_url('/mod/thpage/confirm.php',
                        array('cmid' => $cm->id,
                            'thpageid' => $thpage->id, 
                            'courseid' => $c->id, 
                            'contextid' => $record->contextid,
                            'coursename' => $c->fullname
                        )
                    ), 'method' => 'post')
                );
            $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'th_submit',
                'value' => get_string('create_pdf', 'mod_thpage'), 'class' => 'btn btn-primary'));
            $output .= html_writer::end_tag('form');

            $stt = $stt + 1;
            $row          = new html_table_row();
            $cell         = new html_table_cell($stt);
            $row->cells[] = $cell;
            $cell         = new html_table_cell(html_writer::link($url_course, $c->fullname, ['target' => '_blank']));
            $row->cells[] = $cell;
            $cell         = new html_table_cell(html_writer::link($url_course, $c->shortname, ['target' => '_blank']));
            $row->cells[] = $cell;
            $cell         = new html_table_cell(html_writer::link($url_coursemodule, $record->name, ['target' => '_blank']));
            $row->cells[] = $cell;
            $cell         = new html_table_cell($status);
            $row->cells[] = $cell;
            $cell         = new html_table_cell($output);
            $row->cells[] = $cell;
            $cell         = new html_table_cell($time);
            $row->cells[] = $cell;
            $table->data[] = $row;
        }
    }
    $html = html_writer::table($table);
    echo $html;
}

echo $OUTPUT->footer();