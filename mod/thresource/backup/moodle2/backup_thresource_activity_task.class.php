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
 * Defines backup_thresource_activity_task class
 *
 * @package     mod_thresource
 * @category    backup
 * @copyright   2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/thresource/backup/moodle2/backup_thresource_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the thresource instance
 */
class backup_thresource_activity_task extends backup_activity_task {

    /**
     * @param bool $thresourceoldexists True if there are records in the thresource_old table.
     */
    protected static $thresourceoldexists = null;

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the thresource.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_thresource_activity_structure_step('thresource_structure', 'thresource.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content) {
        global $CFG, $DB;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of thresources.
        $search="/(".$base."\/mod\/thresource\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@THRESOURCEINDEX*$2@$', $content);

        // Link to resource view by moduleid.
        $search = "/(".$base."\/mod\/thresource\/view.php\?id\=)([0-9]+)/";
        // Link to thresource view by recordid
        $search2 = "/(".$base."\/mod\/thresource\/view.php\?r\=)([0-9]+)/";

        // Check whether there are contents in the thresource old table.
        if (static::$thresourceoldexists === null) {
            static::$thresourceoldexists = $DB->record_exists('thresource_old', array());
        }

        // If there are links to items in the thresource_old table, rewrite them to be links to the correct URL
        // for their new module.
        if (static::$thresourceoldexists) {
            // Match all of the thresources.
            $result = preg_match_all($search, $content, $matches, PREG_PATTERN_ORDER);

            // Course module ID thresource links.
            if ($result) {
                list($insql, $params) = $DB->get_in_or_equal($matches[2]);
                $oldrecs = $DB->get_records_select('thresource_old', "cmid $insql", $params, '', 'cmid, newmodule');

                for ($i = 0; $i < count($matches[0]); $i++) {
                    $cmid = $matches[2][$i];
                    if (isset($oldrecs[$cmid])) {
                        // thresource_old item, rewrite it
                        $replace = '$@' . strtoupper($oldrecs[$cmid]->newmodule) . 'VIEWBYID*' . $cmid . '@$';
                    } else {
                        // Not in the thresource old table, don't rewrite
                        $replace = '$@THRESOURCEVIEWBYID*'.$cmid.'@$';
                    }
                    $content = str_replace($matches[0][$i], $replace, $content);
                }
            }

            $matches = null;
            $result = preg_match_all($search2, $content, $matches, PREG_PATTERN_ORDER);

            // No thresource links.
            if (!$result) {
                return $content;
            }
            // thresource ID links.
            list($insql, $params) = $DB->get_in_or_equal($matches[2]);
            $oldrecs = $DB->get_records_select('thresource_old', "oldid $insql", $params, '', 'oldid, cmid, newmodule');

            for ($i = 0; $i < count($matches[0]); $i++) {
                $recordid = $matches[2][$i];
                if (isset($oldrecs[$recordid])) {
                    // thresource_old item, rewrite it
                    $replace = '$@' . strtoupper($oldrecs[$recordid]->newmodule) . 'VIEWBYID*' . $oldrecs[$recordid]->cmid . '@$';
                    $content = str_replace($matches[0][$i], $replace, $content);
                }
            }
        } else {
            $content = preg_replace($search, '$@THRESOURCEVIEWBYID*$2@$', $content);
        }
        return $content;
    }
}