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
 * Moodle form — Slides tab (images, titles, captions, URLs).
 *
 * @package   local_th_slideshow
 * @copyright 2024 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_th_slideshow\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
class slides_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'tab', 'slides');
        $mform->setType('tab', PARAM_ALPHA);

        $fileoptions = [
            'subdirs'        => 0,
            'maxfiles'       => 1,
            'accepted_types' => ['web_image'],
        ];

        $mform->addElement('header', 'slide1_header',
            get_string('slideshow_slide1', 'local_th_slideshow'));
        $mform->setExpanded('slide1_header', true);
        $mform->addElement('html', '<div class="alert alert-info mt-2">' .
            format_text(get_string('slideshowdesc', 'local_th_slideshow'), FORMAT_MARKDOWN) .
            '</div>');
        $mform->addElement('filemanager', 'slide1image',
            get_string('slideimage', 'local_th_slideshow'), null, $fileoptions);



        $this->add_action_buttons();
    }
    public function validation($data, $files) {
        return parent::validation($data, $files);
    }
}
