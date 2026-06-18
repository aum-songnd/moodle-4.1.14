<?php

function local_th_feedback_report_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            $category = $DB->get_record('course_categories', array('id' => $parentcontext->instanceid), '*', MUST_EXIST);
            if (!$category->visible) {
                require_capability('moodle/category:viewhiddencategories', $parentcontext);
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if ($filearea !== 'screenshot') {
        send_file_not_found();
    }

    $itemid = (int)$args[0];

    $report = $DB->get_record(
        'local_th_feedback_report',
        ['id' => $itemid],
        '*',
        MUST_EXIST
    );

    $canviewall = has_capability(
        'local/th_feedback_report:view',
        context_system::instance()
    );

    if (!$canviewall && $report->userid != $USER->id) {
        send_file_not_found();
    }

    $fs = get_file_storage();

    if (count($args) != 2) {
        send_file_not_found();
    }

    if (!$file = $fs->get_file($context->id, 'local_th_feedback_report', $filearea, $args[0], '/', $args[1]) or $file->is_directory()) {
        send_file_not_found();
    }

    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

function local_th_feedback_report_controls(context $context, moodle_url $currenturl) {
	$tabs = array();
	$currenttab = 'view';
	$view = new moodle_url('/local/th_register/doi_tuong/index.php');

	if (has_capability('local/th_feedback_report:view', $context)) {
		$addurl = new moodle_url('/local/th_feedback_report/index.php');
		$tabs[] = new tabobject('view', $addurl, get_string('title_log_feedback_report', 'local_th_feedback_report'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'view';
		}
	}
	if (has_capability('local/th_feedback_report:view', $context)) {
		$addurl = new moodle_url('/local/th_feedback_report/manage_feedback_categories.php');
		$tabs[] = new tabobject('edit', $addurl, get_string('manageerrorcategories', 'local_th_feedback_report'));
		if ($currenturl->get_path() === $addurl->get_path()) {
			$currenttab = 'edit';
		}
	}

	if (count($tabs) > 1) {
		return new tabtree($tabs, $currenttab);
	}
	return null;
}

?>