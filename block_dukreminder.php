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
 * dukreminder block caps.
 *
 * @package    block_dukreminder
 * @copyright  Florian Jungwirth <fjungwirth@gtn-solutions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_dukreminder extends block_list {

	function init() {
		$this->title = get_string('pluginname', 'block_dukreminder');
	}

	function get_content() {
		global $CFG, $OUTPUT, $COURSE;

		if ($this->content !== null) {
			return $this->content;
		}

		if (empty($this->instance)) {
			$this->content = '';
			return $this->content;
		}

		$this->content = new stdClass();
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';

		$this->content->items[] = html_writer::link(new moodle_url('/blocks/dukreminder/course_reminders.php', array('courseid'=>$COURSE->id)), get_string('tab_course_reminders', 'block_dukreminder'), array('title'=>get_string('tab_course_reminders', 'block_dukreminder')));
		$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/dukreminder/pix/reminders.png'), 'alt'=>"", 'height'=>16, 'width'=>23));

		$this->content->items[] = html_writer::link(new moodle_url('/blocks/dukreminder/new_reminder.php', array('courseid'=>$COURSE->id)), get_string('tab_new_reminder', 'block_dukreminder'), array('title'=>get_string('tab_new_reminder', 'block_dukreminder')));
		$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/dukreminder/pix/new.png'), 'alt'=>"", 'height'=>16, 'width'=>23));

		return $this->content;
	}


	public function instance_allow_multiple() {
		return true;
	}

	public function cron() {
		require_once dirname(__FILE__)."/inc.php";

		global $DB, $OUTPUT, $PAGE, $USER;

		$entries = block_dukreminder_get_pending_reminders();
		
		foreach($entries as $entry) {
			//$mailssent = $entry->mailssent;
			$mailssent = 0;
			$creator = $DB->get_record('user', array('id' => $entry->createdby));
			$course = $DB->get_record('course', array('id' => $entry->courseid));
			$coursecontext = context_course::instance($course->id);

			$users = block_dukreminder_filter_users($entry);
			$managers = array();

			//go through users and send mails AND save the user managers
			foreach($users as $user) {
				$user->mailformat = FORMAT_HTML;

				$mailText = block_dukreminder_replace_placeholders($entry->text, $course->fullname, fullname($user), $user->email);
				email_to_user($user, $creator, $entry->subject, strip_tags($mailText), $mailText);
				$mailssent++;
				
				if($entry->daterelative > 0)
					$DB->insert_record('block_dukreminder_mailssent', array('userid' => $user->id, 'reminderid' => $entry->id));
				/*
				$event = \block_dukreminder\event\send_mail::create(array(
						'objectid' => $creator->id,
						'context' => $coursecontext,
						'other' => 'student was notified',
						'relateduserid' => $user->id
				));
				$event->trigger();*/
				mtrace("a reminder mail was sent to student $user->id for $entry->subject");

				//check for user manager and save information for later notifications
				if($entry->to_reportsuperior) {
					$usermanager = block_dukreminder_get_manager($user);
					if($usermanager) {
						if(!isset($managers[$usermanager->id]))
							$managers[$usermanager->id]->$usermanager;

						$managers[$usermanager->id]->users[] = $user;
					}
				}
			}

			$mailText = block_dukreminder_get_mail_text($course->fullname, $users);

			if($entry->to_reporttrainer && $mailssent > 0) {
				//get course teachers and send mails, and additional mails
				$teachers = block_dukreminder_get_course_teachers($coursecontext);
				foreach($teachers as $teacher) {
					email_to_user($teacher, $creator, get_string('pluginname','block_dukreminder'), $mailText);

					/*
					$event = \block_dukreminder\event\send_mail::create(array(
							'objectid' => $creator->id,
							'context' => $coursecontext,
							'other' => 'teacher was notified',
							'relateduserid' => $teacher->id
					));
					$event->trigger();*/
					mtrace("a report mail was sent to teacher $teacher->id");
				}
			}
			// "Sonstige Empfänger"
			if($entry->to_mail && $mailssent > 0) {
				$addresses = explode(';',$entry->to_mail);
				$dummyuser = $DB->get_record('user',array('id' => EMAIL_DUMMY));

				foreach($addresses as $address) {
					$dummyuser->email = $address;
					email_to_user($dummyuser, $creator, get_string('pluginname','block_dukreminder'), $mailText);

					/*
					$event = \block_dukreminder\event\send_mail::create(array(
							'objectid' => $creator->id,
							'context' => $coursecontext,
							'other' => 'additional user was notified',
							'relateduserid' => $dummyuser->id
					));
					$event->trigger();*/
					mtrace("a report mail was sent to $address");
				}
			}

			// Managers
			if($entry->to_reportsuperior && $mailssent > 0) {
				foreach($managers as $manager) {
					$mailText = block_dukreminder_get_mail_text($course->fullname, $manager->users);
					email_to_user($manager, $creator, get_string('pluginname','block_dukreminder'), $mailText);

					/*
					$event = \block_dukreminder\event\send_mail::create(array(
							'objectid' => $creator->id,
							'context' => $coursecontext,
							'other' => 'manager was notified',
							'relateduserid' => $manager->id
					));
					$event->trigger();*/
					mtrace("a report mail was sent to manager $manager->id");
				}
			}
			//set sentmails
			$entry->mailssent += $mailssent;
			//set sent
			$entry->sent = 1;

			$DB->update_record('block_dukreminder', $entry);

		}
		return true;
	}
}