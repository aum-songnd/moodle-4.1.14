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
 * Functions for file handling.
 *
 * @package   core_files
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filestorage/file_exceptions.php");
require_once("$CFG->libdir/filestorage/file_storage.php");
require_once("$CFG->libdir/filestorage/zip_packer.php");
require_once("$CFG->libdir/filebrowser/file_browser.php");
require_once("$CFG->dirroot/mod/resource/th_filetypes.php");


function &th_get_mimetypes_array() {
    // Get types from the core_filetypes function, which includes caching.
    return th_core_filetypes::get_types();
}

function th_mimeinfo($element, $filename) {
    global $CFG;
    $mimeinfo = & th_get_mimetypes_array();
    static $iconpostfixes = array(256=>'-256', 128=>'-128', 96=>'-96', 80=>'-80', 72=>'-72', 64=>'-64', 48=>'-48', 32=>'-32', 24=>'-24', 16=>'');

    $filetype = strtolower(pathinfo($filename ?? '', PATHINFO_EXTENSION));
    if (empty($filetype)) {
        $filetype = 'xxx'; // file without extension
    }
    if (preg_match('/^icon(\d*)$/', $element, $iconsizematch)) {
        $iconsize = max(array(16, (int)$iconsizematch[1]));
        $filenames = array($mimeinfo['xxx']['icon']);
        if ($filetype != 'xxx' && isset($mimeinfo[$filetype]['icon'])) {
            array_unshift($filenames, $mimeinfo[$filetype]['icon']);
        }
        // find the file with the closest size, first search for specific icon then for default icon
        foreach ($filenames as $filename) {
            foreach ($iconpostfixes as $size => $postfix) {
                $fullname = $CFG->dirroot.'/pix/f/'.$filename.$postfix;
                if ($iconsize >= $size &&
                        (file_exists($fullname.'.svg') || file_exists($fullname.'.png') || file_exists($fullname.'.gif'))) {
                    return $filename.$postfix;
                }
            }
        }
    } else if (isset($mimeinfo[$filetype][$element])) {
        return $mimeinfo[$filetype][$element];
    } else if (isset($mimeinfo['xxx'][$element])) {
        return $mimeinfo['xxx'][$element];   // By default
    } else {
        return null;
    }
}

