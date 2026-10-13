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
 * Version information for th_lambda_st_ielts
 *
 * @package    theme_th_lambda_st_ielts
 * @copyright  2026 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component    = 'theme_th_lambda_st_ielts';
$plugin->release      = '1.0';
$plugin->version      = 2026101300;
$plugin->requires     = 2022112800;
$plugin->supported    = [401, 501];
$plugin->maturity     = MATURITY_STABLE;
$plugin->dependencies = [
	'theme_th_lambda_st' => 2026050700,
	'local_modcustomfields'    => 2024070300,
    'local_th_ieltsquizaudio' => 2026071509,
];
