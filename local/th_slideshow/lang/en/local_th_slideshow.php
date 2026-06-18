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
 * Language strings for local_th_slideshow
 *
 * @package   local_th_slideshow
 * @copyright 2024 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin name.
$string['pluginname'] = 'TH Slideshow';

// Settings page.
$string['slideshowheadingsub'] = 'Slideshow Settings';
$string['slideshowdesc']       = 'Only upload photos with a 3x4 aspect ratio.';

// Slide 1 heading.
$string['slideshow_slide1'] = 'Slide 1';

// Slide fields.
$string['slideimage']   = 'Banner image';
$string['slidetitle']   = 'Slide title';
$string['slidecaption'] = 'Slide caption';
$string['slideurl']     = 'Slide link URL';

// Options.
$string['slideshow_options']          = 'Slideshow Options';
$string['slideshow_height']           = 'Slideshow height';
$string['slideshow_height_desc']      = 'Select the height of the slideshow. Choose "Responsive" to let it adapt to the image ratio.';
$string['responsive']                 = 'Responsive';
$string['slideshow_hide_captions']    = 'Hide captions on mobile';
$string['slideshow_hide_captions_desc'] = 'Check to hide slide titles and captions on small screens.';
$string['slideshowpattern']           = 'Overlay pattern';
$string['slideshowpatterndesc']       = 'Choose a decorative pattern overlay for the slideshow area.';
$string['slideshow_advance']          = 'Auto-advance slides';
$string['slideshow_advance_desc']     = 'Check to automatically advance to the next slide.';
$string['slideshow_nav']              = 'Show navigation';
$string['slideshow_nav_desc']         = 'Check to display previous/next navigation arrows and dot indicators.';
$string['slideshow_loader']           = 'Loader style';
$string['slideshow_loader_desc']      = 'Choose the style of the progress loader shown between slide transitions.';
$string['slideshow_imgfx']            = 'Image transition effect';
$string['slideshow_imgfx_desc']       = 'Enter the transition effect name for images (e.g. random, fade, slideLeft).';
$string['slideshow_txtfx']            = 'Text transition effect';
$string['slideshow_txtfx_desc']       = 'Enter the transition effect name for slide text (e.g. moveFromLeft, fadeIn).';

// Privacy.
$string['privacy:metadata']        = 'The TH Slideshow plugin stores only site-wide configuration (slide images, titles, captions, URLs). It does not store any personal user data.';
$string['th_slideshow:manage']     = 'Manage slideshow';
