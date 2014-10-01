<?php
namespace block_dukreminder\event;
defined('MOODLE_INTERNAL') || die();
class send_mail extends \core\event\base {
	protected function init() {
		$this->data['crud'] = 'c';
		$this->data['edulevel'] = self::LEVEL_OTHER;
		$this->data['objecttable'] = 'block_dukreminder';
	}

	/**
	 * Return localised event name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return get_string('eventsendmail', 'block_dukreminder');
	}

	public function get_description() {
		return "User {$this->relateduserid} was notified";
	}

	
	/**
	 * Get URL related to the action
	 *
	 * @return \moodle_url
	 */
	public function get_url() {
		return new \moodle_url('/blocks/dukreminder/course_reminders.php', array('courseid' => $this->contextinstanceid));
	}

	/**
	 * Return legacy log data.
	 *
	 * @return array
	 */
	public function get_legacy_logdata() {
		// Override if you are migrating an add_to_log() call.
		return array($this->courseid, 'block_dukreminder', 'send mail',
				'user was notified',
				$this->objectid, $this->relateduserid);
	}

	public static function get_legacy_eventname() {
		// Override ONLY if you are migrating events_trigger() call.
		return 'block_dukreminder_send_mail';
	}

}