<?php
// This file is part of the Thcontact Form plugin for Moodle - http://moodle.org/
//
// Thcontact Form is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Thcontact Form is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Thcontact Form.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This plugin for Moodle is used to send emails through a web form.
 *
 * @package    local_th_contact
 * @copyright  2016-2021 TNG Consulting Inc. - www.tngconsulting.ca
 * @author     Michael Milette
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the Thcontact Form local plugin.
 *
 * @param int $oldversion - the version we are upgrading from.
 * @return bool result
 */
function xmldb_local_th_contact_upgrade($oldversion) {

    // Moodle v3.1.0 release upgrade line.
    // Upgrade steps below.

    return true;
}