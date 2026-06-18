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
 * Provides support for the conversion of moodle1 backup to the moodle2 format
 *
 * @package mod_thpage
 * @copyright  2011 Andrew Davis <andrew@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * thpage conversion handler. This resource handler is called by moodle1_mod_resource_handler
 */
class moodle1_mod_thpage_handler extends moodle1_resource_successor_handler {

    /** @var moodle1_file_manager instance */
    protected $fileman = null;

    /**
     * Converts /MOODLE_BACKUP/COURSE/MODULES/MOD/RESOURCE data
     * Called by moodle1_mod_resource_handler::process_resource()
     */
    public function process_legacy_resource(array $data, array $raw = null) {

        // get the course module id and context id
        $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($instanceid, 'resource');
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

        // convert the legacy data onto the new thpage record
        $thpage                       = array();
        $thpage['id']                 = $data['id'];
        $thpage['name']               = $data['name'];
        $thpage['intro']              = $data['intro'];
        $thpage['introformat']        = $data['introformat'];
        $thpage['content']            = $data['alltext'];

        if ($data['type'] === 'html') {
            // legacy Resource of the type Web thpage
            $thpage['contentformat'] = FORMAT_HTML;

        } else {
            // legacy Resource of the type Plain text thpage
            $thpage['contentformat'] = (int)$data['reference'];

            if ($thpage['contentformat'] < 0 or $thpage['contentformat'] > 4) {
                $thpage['contentformat'] = FORMAT_MOODLE;
            }
        }

        $thpage['legacyfiles']        = RESOURCELIB_LEGACYFILES_ACTIVE;
        $thpage['legacyfileslast']    = null;
        $thpage['revision']           = 1;
        $thpage['timemodified']       = $data['timemodified'];

        // populate display and displayoptions fields
        $options = array('printheading' => 1, 'printintro' => 0);
        if ($data['popup']) {
            $thpage['display'] = RESOURCELIB_DISPLAY_POPUP;
            $rawoptions = explode(',', $data['popup']);
            foreach ($rawoptions as $rawoption) {
                list($name, $value) = explode('=', trim($rawoption), 2);
                if ($value > 0 and ($name == 'width' or $name == 'height')) {
                    $options['popup'.$name] = $value;
                    continue;
                }
            }
        } else {
            $thpage['display'] = RESOURCELIB_DISPLAY_OPEN;
        }
        $thpage['displayoptions'] = serialize($options);

        // get a fresh new file manager for this instance
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_thpage');

        // convert course files embedded into the intro
        $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $thpage['intro'] = moodle1_converter::migrate_referenced_files($thpage['intro'], $this->fileman);

        // convert course files embedded into the content
        $this->fileman->filearea = 'content';
        $this->fileman->itemid   = 0;
        $thpage['content'] = moodle1_converter::migrate_referenced_files($thpage['content'], $this->fileman);

        // write thpage.xml
        $this->open_xml_writer("activities/thpage_{$moduleid}/thpage.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'thpage', 'contextid' => $contextid));
        $this->write_xml('thpage', $thpage, array('/thpage/id'));
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

        // write inforef.xml for migrated resource file.
        $this->open_xml_writer("activities/thpage_{$moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }
}
