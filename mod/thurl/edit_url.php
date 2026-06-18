<?php

require_once '../../config.php';
require_once 'edit_url_form.php';
require_once $CFG->dirroot . '/course/modlib.php';
require_once $CFG->dirroot . '/lib/filelib.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

$url_id = optional_param('id', 0, PARAM_INT);
$cm_id = optional_param('cm', 0, PARAM_INT);
$courseid = $COURSE->id;
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'mod_url', $courseid);
}

require_login($courseid);
require_capability('mod/thurl:view', context_course::instance($COURSE->id));

$title = get_string('editurl', 'thurl');
$PAGE->set_url('/mod/thurl/edit_url.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);

$title = get_string('editurl', 'thurl');
$PAGE->set_url('/mod/thurl/edit_url.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($SITE->fullname . ': ' . $title);

if ($url_id && $cm_id) {
	$url_edit = $DB->get_record_sql("SELECT * FROM {thurl} WHERE id = '$url_id'");
	$cm = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '$cm_id'");

	if ($url_edit) {
		$cm_id = $cm->id;
		$url_edit1 = new stdClass();
		$url_edit1->id = $url_edit->id;
		$url_edit1->name = $url_edit->name;
		$url_edit1->url = $url_edit->externalurl;
		$desc = array(
			'text' => $url_edit->intro,
			'format' => 1,
		);

		$url_edit1->introeditor = $desc;
		$url_edit1->display = $cm->showdescription;
		$url_edit1->time_open = '';
		$url_edit1->time_close = '';

		$availability_object = json_decode($cm->availability);

		if ($availability_object) {
			$list_time = $availability_object->c;

			foreach ($list_time as $time) {
				if ($time->type == 'date') {
					if ($time->d == '>=') {
						$url_edit1->time_open = $time->t;
					} else {
						$url_edit1->time_close = $time->t;
					}
				}
			}
		}
	} else {
		redirect($CFG->wwwroot . "/mod/thurl/view.php?id=$cm_id", get_string('url_not_found', 'thurl'), null, \core\output\notification::NOTIFY_WARNING);
	}

} else {
	$cm_id = '';
	$url_edit1 = new stdClass();
	$url_edit1->id = '';
	$url_edit1->name = '';
	$url_edit1->url = '';
	$url_edit1->introeditor = '';
	$url_edit1->display = '';
	$url_edit1->time_open = '';
	$url_edit1->time_close = '';
}

$url_form = new edit_url_form();
$url_form->set_data(array('cm_id' => $cm_id, 'name' => $url_edit1->name, 'url' => $url_edit1->url, 'desc' => $url_edit1->introeditor, 'display' => $url_edit1->display, 'id' => $url_edit1->id, 'time_open' => $url_edit1->time_open, 'time_close' => $url_edit1->time_close));

if ($url_form->is_cancelled()) {

	$cm_id = $_POST['cm_id'];
	$courseurl = new moodle_url('view.php', array('id' => $cm_id));
	redirect($courseurl);

} else if ($fromform = $url_form->get_data()) {

	$cm_id = $fromform->cm_id;
	$url_id = $fromform->id;
	$time_open = $fromform->time_open;
	$time_close = $fromform->time_close;

	$cm = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '$cm_id'");
	$availability_object = json_decode($cm->availability);

	if (!empty($availability_object)) {
		$ds_han_che = $availability_object->c;

		foreach ($ds_han_che as $k => $han_che) {
			if ($han_che->type == 'date') {
				if ($han_che->d == '>=') {
					$check_time_open = true;
					break;
				} else {
					$check_time_open = false;
				}
			}
		}

		foreach ($ds_han_che as $k => $han_che) {
			if ($han_che->type == 'date') {
				if ($han_che->d == '<') {
					$check_time_close = true;
					break;
				} else {
					$check_time_close = false;
				}
			}
		}

		foreach ($ds_han_che as $k => $han_che) {

			if (!empty($han_che)) {
				if ($han_che->type == 'date') {

					if ($check_time_open == false) {

						$start_date_object = new stdClass();
						$start_date_object->type = 'date';
						$start_date_object->d = '>=';
						$start_date_object->t = $time_open;

						$availability_object->c[] = $start_date_object;
						$availability_object->showc[] = true;

					} else {
						if ($han_che->d == '>=') {
							$availability_object->c[$k]->t = $time_open;
						}
					}

					if ($check_time_close == false) {

						$close_date_object = new stdClass();
						$close_date_object->type = 'date';
						$close_date_object->d = '<';
						$close_date_object->t = $time_close;

						$availability_object->c[] = $close_date_object;
						$availability_object->showc[] = true;

					} else {
						if ($han_che->d == '<') {
							$availability_object->c[$k]->t = $time_close;
						}
					}
				}
			}
		}

	} else {
		$start_date_object = new stdClass();
		$start_date_object->type = 'date';
		$start_date_object->d = '>=';
		$start_date_object->t = $time_open;

		$close_date_object = new stdClass();
		$close_date_object->type = 'date';
		$close_date_object->d = '<';
		$close_date_object->t = $time_close;

		$availability_object = new stdClass();
		$availability_object->op = '&';
		$availability_object->c = array(
			'0' => $start_date_object,
			'1' => $close_date_object,
		);
		$availability_object->showc = array(
			'0' => true,
			'1' => true,
		);
	}

	$availabilityconditionsjson = json_encode($availability_object);

	if ($cm_id && $url_id) {
		$cm = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '$cm_id'");
		$cm->modname = 'thurl';
		$url_edit = $DB->get_record_sql("SELECT * FROM {thurl} WHERE id = '$url_id'");
		$course = $DB->get_record('course', array('id' => $cm->course));

		$url_edit->name = $fromform->name;
		$url_edit->externalurl = $fromform->url;
		$url_edit->intro = $fromform->desc['text'];

		$fromform1 = new stdClass();

		$fromform1->name = $fromform->name;
		$fromform1->externalurl = $fromform->url;

		$fromform1->introeditor = $fromform->desc;

		if (isset($fromform->display)) {
			$fromform1->showdescription = $fromform->display;
		} else {
			$fromform1->showdescription = 0;
		}

		$fromform1->display = $url_edit->display;
		$fromform1->visible = $cm->visible;
		$fromform1->visibleoncoursepage = $cm->visibleoncoursepage;
		$fromform1->completion = $cm->completion;
		$fromform1->completionexpected = $cm->completionexpected;
		$fromform1->course = $course->id;
		$fromform1->coursemodule = $cm->id;
		$fromform1->module = $cm->module;
		$fromform1->modulename = 'thurl';
		$fromform1->instance = $cm->instance;
		$fromform1->update = $cm->id;
		$fromform1->availabilityconditionsjson = $availabilityconditionsjson;

		list($cm, $fromform1) = update_moduleinfo($cm, $fromform1, $course);
		redirect($CFG->wwwroot . "/course/view.php?id=$cm->course", get_string('url_edit_success', 'thurl'), null, \core\output\notification::NOTIFY_SUCCESS);
	} else {
		redirect($CFG->wwwroot . "/mod/thurl/edit_url.php", get_string('url_edit_failed_missing_param', 'thurl'), null, \core\output\notification::NOTIFY_ERROR);
	}

} else {
	// form didn't validate or this is the first display
	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);
	echo "</br>";
	$url_form->display();
	echo $OUTPUT->footer();
}