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
 * Library functions for local_th_slideshow
 *
 * @package   local_th_slideshow
 * @copyright 2024 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the URL of a stored file for a given filearea.
 *
 * @param string $filearea  The filearea name (e.g. 'slide1image').
 * @return moodle_url|false  URL of the file, or false if not set.
 */
function local_th_slideshow_get_image_url(string $filearea) {
    $context = context_system::instance();
    $fs      = get_file_storage();
    $files   = $fs->get_area_files($context->id, 'local_th_slideshow', $filearea, false, 'itemid', false);

    if (empty($files)) {
        return false;
    }

    $file = reset($files);
    return moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename()
    );
}

/**
 * Collects slide data and options into a single array suitable for a Mustache template.
 *
 * @return array  Template context data.
 */
function local_th_slideshow_get_template_data(): array {
    $config   = get_config('local_th_slideshow');
    $imageurl = local_th_slideshow_get_image_url('slide1image');

    $slides = [];
    if ($imageurl) {
        $slides[] = [
            'index'   => 1,
            'image'   => $imageurl->out(),
            'title'   => format_string($config->slide1 ?? ''),
            'caption' => format_text($config->slide1caption ?? '', FORMAT_HTML),
            'url'     => clean_param($config->slide1_url ?? '', PARAM_URL),
        ];
    }

    return [
        'slides'         => $slides,
        'has_slides'     => !empty($slides),
        'height'         => $config->slideshow_height     ?? '475px',
        'hide_captions'  => !empty($config->slideshow_hide_captions),
        'pattern'        => $config->slideshowpattern     ?? '0',
        'auto_advance'   => !empty($config->slideshow_advance),
        'show_nav'       => !empty($config->slideshow_nav),
        'loader'         => $config->slideshow_loader     ?? '0',
        'imgfx'          => $config->slideshow_imgfx      ?? 'random',
        'txtfx'          => $config->slideshow_txtfx      ?? 'moveFromLeft',
    ];
}

/**
 * Serves plugin files (slide images).
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context  $context
 * @param string   $filearea
 * @param array    $args
 * @param bool     $forcedownload
 * @param array    $options
 * @return bool
 */
function local_th_slideshow_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel !== CONTEXT_SYSTEM) {
        return false;
    }

    if ($filearea !== 'slide1image') {
        return false;
    }

    $itemid   = array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs   = get_file_storage();
    $file = $fs->get_file($context->id, 'local_th_slideshow', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false;
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}