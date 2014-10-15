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
 * Strings for component 'block_dukreminder', language 'de'
 *
 * @package    block_dukreminder
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 * @author	   Florian Jungwirth <fjungwirth@gtn-solutions.com>
 * @ideaandconcept Gerhard Schwed <gerhard.schwed@donau-uni.ac.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['blockstring'] = 'Kursabschlusserinnerung';
$string['newblock:addinstance'] = 'Add a newblock block';
$string['newblock:myaddinstance'] = 'Add a newblock block to my moodle';
$string['pluginname'] = 'Kursabschlusserinnerung';

$string['tab_course_reminders'] = 'Kurs-Erinnerungen';
$string['tab_new_reminder'] = 'Neue Erinnerung';

$string['form_title'] = 'Kürzel';
$string['form_subject'] = 'Erinnerungs-Titel';
$string['form_text'] = 'Benachrichtigungstext Schüler';
$string['form_text_teacher'] = 'Benachrichtigungstext Trainer';
$string['form_dateabsolute'] = 'Deadline';
$string['form_daterelative'] = 'Dauer bezogen auf Kurseinschreibung';
$string['form_daterelative_completion'] = 'Dauer bezogen ab Kursabschluss';
$string['form_to_status'] = 'Kursabschlusstatus';
$string['form_to_reporttrainer'] = 'Bericht an die Kurstrainer';
$string['form_to_reportsuperior'] = 'Bericht an die Vorgesetzten';
$string['form_to_groups'] = 'Empfängergruppen';
$string['form_to_mail'] = 'Sonstige Email-Empfänger';
$string['form_mailssent'] = 'Bisher versandte Erinnerungen';
$string['form_delete'] = 'Wirklich löschen?';

$string['form_to_status_all'] = 'Alle';
$string['form_to_status_completed'] = 'Mit Abschluss';
$string['form_to_status_notcompleted'] = 'Ohne Abschluss';

$string['daterelative_error'] = 'Es ist kein negativer Wert hier erlaubt';
$string['to_mail_error'] = 'Es ist eine ungültige E-Mail-Adresse angegeben worden! Die Adressen müssen mit ; getrennt werden';

$string['email_teacher_notification'] = 'Soeben wurden folgende {$a->amount} Personen an den Kursabschluss im Kurs {$a->course} erinnert:';