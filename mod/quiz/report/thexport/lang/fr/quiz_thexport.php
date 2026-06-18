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
 * Strings for component 'quiz_thexport', language 'en'
 *
 * @package   quiz_thexport
 * @copyright 2020 CBlue Srl
 * @copyright based on work by 2014 Johannes Burk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'QuizThexport';
$string['thexport'] = 'Thexport de quiz'; // displayed in the navigation menu

$string['thexportselected'] = 'Thexporter les tentatives sélectionnées';

$string['thexportattemptcheck'] = 'Thexporter les tentatives sélectionnées ?';

$string['thexportattempt'] = 'Thexporter cette tentative en pdf';

// thexport_form
$string['thexportsettings'] = 'Paramètres d\'thexport';
$string['pagemode'] = 'Mode de pagination (La manière dont vont être gérés les sauts de page lors de l\'thexport PDF)';
$string['thexportmodetruepage'] = 'Les pages de questions réellement posées';
$string['thexportmodequestionperpage'] = 'Une seule question par page';
$string['thexportmodesinglepage'] = 'Toutes les questions à la suite';

// Inside the pdf
$string['documenttitle'] = '{$a->coursename} <br> {$a->quizname} <br> - <br> Réponses de {$a->firstname} {$a->lastname}';

$string['newexportselected'] = 'Download files attachment';
$string['newexportattemptcheck'] = 'Thexport selected attempts?';
$string['filename'] = 'quiz_thexport';
