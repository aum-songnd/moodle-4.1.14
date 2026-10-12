<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Report theo dõi tiến trình xem video cho mod_resource.
 *
 * @package   mod_resource
 * @copyright 2024
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->libdir . "/tablelib.php");

$id     = optional_param('id', 0, PARAM_INT);
$userid = optional_param('u', false, PARAM_INT);

$cm       = get_coursemodule_from_id('resource', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$resource = $DB->get_record('resource', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/resource:viewreport', $context);

if (!has_capability('moodle/course:manageactivities', $context)) {
    $userid = $USER->id;
}

$table = new \mod_resource\report\resource_view('resource_video_report', $cm->id, $userid, $resource);

if (!$table->is_downloading()) {
    $PAGE->set_url('/mod/resource/report.php', ['id' => $cm->id]);
    $PAGE->set_title("{$course->shortname}: {$resource->name}");
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();

    if ($userid) {
        $user  = $DB->get_record('user', ['id' => $userid]);
        $title = get_string('report_title_user', 'mod_resource', fullname($user));
    } else {
        $title = get_string('report_title', 'mod_resource', $resource->name);
    }

    echo $OUTPUT->heading("{$title}", 2);
}

$table->define_baseurl("{$CFG->wwwroot}/mod/resource/report.php?id={$cm->id}&u={$userid}");
$table->out(40, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
