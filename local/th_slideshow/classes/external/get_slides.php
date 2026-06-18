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
 * External function: get_slides
 *
 * @package   local_th_slideshow
 * @copyright 2024 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_th_slideshow\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/th_slideshow/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_system;

class get_slides extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'index'   => new external_value(PARAM_INT,  'Slide position'),
                'image'   => new external_value(PARAM_URL,  'Absolute URL of the slide image'),
                'title'   => new external_value(PARAM_TEXT, 'Slide title',        VALUE_DEFAULT, ''),
                'caption' => new external_value(PARAM_RAW,  'Slide caption HTML', VALUE_DEFAULT, ''),
                'url'     => new external_value(PARAM_URL,  'Click-through URL',  VALUE_DEFAULT, ''),
            ]),
            'List of configured slides',
            VALUE_DEFAULT,
            []
        );
    }

    public static function execute(): array {
        self::validate_parameters(self::execute_parameters(), []);

        $context = context_system::instance();
        self::validate_context($context);

        $data = local_th_slideshow_get_template_data();

        return $data['slides'];
    }

    // Legacy aliases.
    public static function local_th_slideshow_get_slides_parameters(): external_function_parameters {
        return self::execute_parameters();
    }
    public static function local_th_slideshow_get_slides(): array {
        return self::execute();
    }
    public static function local_th_slideshow_get_slides_returns(): external_multiple_structure {
        return self::execute_returns();
    }
}
