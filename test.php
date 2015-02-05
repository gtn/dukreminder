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
 * Test
 *
 * @package    block_dukreminder
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 * @author       Florian Jungwirth <fjungwirth@gtn-solutions.com>
 * @ideaandconcept Gerhard Schwed <gerhard.schwed@donau-uni.ac.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__)."/inc.php");

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
require_login($course);

$context = context_course::instance($courseid);
require_capability('block/dukreminder:use', $context);


global $DB, $OUTPUT, $PAGE, $USER;

$entries = block_dukreminder_get_pending_reminders();


foreach ($entries as $entry) {
    echo "<h3>".$entry->subject . "</h3>";
    $mailssent = $entry->mailssent;
    $creator = $DB->get_record('user', array('id' => $entry->createdby));
    $course = $DB->get_record('course', array('id' => $entry->courseid));
    $coursecontext = context_course::instance($course->id);

    $users = block_dukreminder_filter_users($entry);
    $managers = array();

    // Go through users and send mails AND save the user managers.
    foreach ($users as $user) {
        $user->mailformat = FORMAT_HTML;

        $mailtext = block_dukreminder_replace_placeholders($entry->text, $course->fullname, fullname($user), $user->email);
        email_to_user($user, $creator, $entry->subject, strip_tags($mailtext), $mailtext);
        $mailssent++;

        if ($entry->daterelative > 0) {
            $DB->insert_record('block_dukreminder_mailssent', array('userid' => $user->id, 'reminderid' => $entry->id));
        }

        /*
        $event = \block_dukreminder\event\send_mail::create(array(
                'objectid' => $creator->id,
                'context' => $coursecontext,
                'other' => 'student was notified',
                'relateduserid' => $user->id
        ));
        $event->trigger();
        */
        echo("a reminder mail was sent to student $user->id for $entry->subject <br/>");

        // Check for user manager and save information for later notifications.
        if ($entry->to_reportsuperior) {
            $usermanager = block_dukreminder_get_manager($user);
            if ($usermanager) {
                if (!isset($managers[$usermanager->id])) {
                    $managers[$usermanager->id]->$usermanager;
                };

                $managers[$usermanager->id]->users[] = $user;
            }
        }
    }

    $mailtext = block_dukreminder_get_mail_text($course->fullname, $users);

    echo "MAIL TEXT (placeholders replaced): <br />";
    echo $mailtext . "<br/>";

    if ($entry->to_reporttrainer && $mailssent > 0) {
        // Get course teachers and send mails, and additional mails.
        $teachers = block_dukreminder_get_course_teachers($coursecontext);
        foreach ($teachers as $teacher) {
            email_to_user($teacher, $creator, get_string('pluginname', 'block_dukreminder'), $mailtext);

            /*
            $event = \block_dukreminder\event\send_mail::create(array(
                    'objectid' => $creator->id,
                    'context' => $coursecontext,
                    'other' => 'teacher was notified',
                    'relateduserid' => $teacher->id
            ));
            $event->trigger();*/
            echo("a report mail was sent to teacher $teacher->id <br/>");
        }
    }
    // Sonstige Empfänger.
    if ($entry->to_mail && $mailssent > 0) {
        $addresses = explode(';', $entry->to_mail);
        $dummyuser = $DB->get_record('user', array('id' => EMAIL_DUMMY));

        foreach ($addresses as $address) {
            $dummyuser->email = $address;
            email_to_user($dummyuser, $creator, get_string('pluginname', 'block_dukreminder'), $mailtext);

            /*
            $event = \block_dukreminder\event\send_mail::create(array(
                    'objectid' => $creator->id,
                    'context' => $coursecontext,
                    'other' => 'additional user was notified',
                    'relateduserid' => $dummyuser->id
            ));
            $event->trigger();*/
            echo("a report mail was sent to $address <br/>");
        }
    }

    // Managers.
    if ($entry->to_reportsuperior && $mailssent > 0) {
        foreach ($managers as $manager) {
            $mailtext = block_dukreminder_get_mail_text($course->fullname, $manager->users);
            email_to_user($manager, $creator, get_string('pluginname', 'block_dukreminder'), $mailtext);


            /*
            $event = \block_dukreminder\event\send_mail::create(array(
                    'objectid' => $creator->id,
                    'context' => $coursecontext,
                    'other' => 'manager was notified',
                    'relateduserid' => $manager->id
            ));
            $event->trigger();*/
            echo("a report mail was sent to manager $manager->id <br/>");
        }
    }
    // Set sentmails.
    $entry->mailssent = $mailssent;
    // Set sent.
    $entry->sent = 1;

    echo "Mails sent: " . $mailssent . "<br/><br/>";
    $DB->update_record('block_dukreminder', $entry);

}