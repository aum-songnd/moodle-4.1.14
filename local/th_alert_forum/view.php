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
 * Prints an instance of mod_qaa.
 *
 * @package     mod_qaa
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require __DIR__ . '/../../config.php';
require_once $CFG->libdir . '/grade/grade_item.php';
// require_once $CFG->dirroot . '/loc/lib.php';
require_once 'lib.php';

global $USER, $DB, $COURSE;

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);
// ... module instance id.
$q = optional_param('q', 0, PARAM_INT);

$action = optional_param('action', 0, PARAM_TEXT);

$questionid = optional_param('questionid', 0, PARAM_INT);

require_login();

$lang = current_language();

$page_url = new moodle_url('/mod/qaa/view.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$courseid = $COURSE->id;
require_login($courseid);

$PAGE->set_url($page_url);
$PAGE->set_title("");
$PAGE->set_heading("");
$PAGE->set_pagelayout('standard');

// $cm = get_coursemodule_from_id('qaa', 41, 0, false, MUST_EXIST);
// $modulecontext = context_module::instance($cm->id);
// $PAGE->set_context($modulecontext);

echo $OUTPUT->header();
// $config = get_config("local_th_alert_forum");
// print_object($config);
// local_th_alert_forum\task\alert_forum::alert();
// $th_custom_admin_setting_configselect = new th_custom_admin_setting_configselect();
// $th_custom_admin_setting_configselect->get

// alert_qaa();
// alert_forum();

echo $OUTPUT->footer();
