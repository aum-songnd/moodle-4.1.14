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
 * Web service function and service definitions for local_th_slideshow.
 *
 * After changing this file, run:
 *   php admin/cli/upgrade.php
 * or visit Site administration → Notifications to apply the changes.
 *
 * @package   local_th_slideshow
 * @copyright 2024 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ------------------------------------------------------------------
// External functions
// ------------------------------------------------------------------
// Each entry registers one callable with Moodle's web service layer.
// ------------------------------------------------------------------

$functions = [

    'local_th_slideshow_get_slides' => [
        'classname'     => \local_th_slideshow\external\get_slides::class,
        'methodname'    => 'execute',
        'description'   => 'Returns all configured slideshow slides (image URL, title, caption, link URL) '
                         . 'together with the current display options (height, auto-advance, effects, etc.). '
                         . 'Intended for consumption by the Moodle Mobile app or any REST client.',
        'type'          => 'read',              // read-only; no DB writes.
        'ajax'          => true,               // callable from JS / AJAX.
        'loginrequired' => true,              // public endpoint – guests can call it.
        'capabilities'  => '',                 // no extra capability required.
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

];

// ------------------------------------------------------------------
// Pre-built service
// ------------------------------------------------------------------
// Moodle will create this service automatically on install/upgrade.
// Administrators can still enable/disable it at
//   Site administration → Server → Web services → External services.
// ------------------------------------------------------------------

$services = [

    'TH Slideshow Mobile API' => [
        'shortname'     => 'local_th_slideshow_mobile',
        'functions'     => ['local_th_slideshow_get_slides'],
        'restrictedusers' => 0,   // 0 = any authorised user; 1 = whitelist only.
        'enabled'       => 1,     // enabled by default after install.
        'downloadfiles' => 0,
        'uploadfiles'   => 0,
    ],

];
