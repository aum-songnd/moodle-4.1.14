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
 * Standalone settings page for local_th_slideshow.
 *
 * @package   local_th_slideshow
 * @copyright 2024 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/form/slides_form.php');

// -------------------------------------------------------------------------
// Access control
// -------------------------------------------------------------------------
require_login();
$context = context_system::instance();
require_capability('local/th_slideshow:manage', $context);

// -------------------------------------------------------------------------
// Page setup
// -------------------------------------------------------------------------
$pageurl = new moodle_url('/local/th_slideshow/index.php');

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_th_slideshow'));
$PAGE->set_heading(get_string('pluginname', 'local_th_slideshow'));

// -------------------------------------------------------------------------
// Build form
// -------------------------------------------------------------------------
$mform = new \local_th_slideshow\form\slides_form($pageurl);

// -------------------------------------------------------------------------
// Handle form submission
// -------------------------------------------------------------------------
if ($mform->is_cancelled()) {
    redirect($pageurl);
} else if ($data = $mform->get_data()) {

    // Save scalar config values (skip internal/file fields).
    $ignore = ['submitbutton', 'sesskey', 'tab', '_qf__local_th_slideshow_form_slides_form', 'slide1image'];
    foreach ((array) $data as $key => $value) {
        if (in_array($key, $ignore)) {
            continue;
        }
        set_config($key, $value, 'local_th_slideshow');
    }

    // Save slide 1 image.
    $draftid = $data->slide1image ?? 0;
    if ($draftid) {
        file_save_draft_area_files(
            $draftid,
            $context->id,
            'local_th_slideshow',
            'slide1image',
            0,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['web_image']]
        );
    }

    theme_reset_all_caches();
    redirect($pageurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// -------------------------------------------------------------------------
// Pre-populate form with current config
// -------------------------------------------------------------------------
$current = (array) get_config('local_th_slideshow');

$draftid = file_get_submitted_draft_itemid('slide1image');
file_prepare_draft_area(
    $draftid,
    $context->id,
    'local_th_slideshow',
    'slide1image',
    0,
    ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['web_image']]
);
$current['slide1image'] = $draftid;

$mform->set_data($current);

// -------------------------------------------------------------------------
// Output
// -------------------------------------------------------------------------
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();