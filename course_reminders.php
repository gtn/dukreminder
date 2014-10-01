<?php
/* * *************************************************************
 *  Copyright notice
*
*  (c) 2014 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
* ************************************************************* */

require_once dirname(__FILE__)."/inc.php";
global $DB, $OUTPUT, $PAGE, $CG;
require_once $CFG->libdir . "/tablelib.php";

$courseid = required_param('courseid', PARAM_INT);
$sorting = optional_param('sorting', 'id', PARAM_TEXT);
$sorttype = optional_param('type', 'asc', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);
require_capability('block/dukreminder:use', $context);

/* DELETE */
if(($deleteid = optional_param('delete',0,PARAM_INT)) > 0) {
	$delete_record = $DB->get_record('block_dukreminder', array('id' => $deleteid));
	if($delete_record->courseid == $courseid)
		$DB->delete_records('block_dukreminder',array('id' => $deleteid));
}

$page_identifier = 'tab_course_reminders';

$PAGE->set_url('/blocks/dukreminder/course_reminders.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_dukreminder'));
$PAGE->set_title(get_string($page_identifier, 'block_dukreminder'));

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_dukreminder'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_dukreminder'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_dukreminder_build_navigation_tabs($courseid), $page_identifier);

/* CONTENT REGION */
$status = array(0 => get_string('form_to_status_all','block_dukreminder'), 1 => get_string('form_to_status_completed','block_dukreminder'), 2 => get_string('form_to_status_notcompleted','block_dukreminder'));
$table = new html_table();

$table->head = array(html_writer::link($PAGE->url . "&sorting=title", get_string('form_title','block_dukreminder')),
		html_writer::link($PAGE->url . "&sorting=subject", get_string('form_subject','block_dukreminder')),
		html_writer::link($PAGE->url . "&sorting=dateabsolute&type=desc", get_string('form_dateabsolute','block_dukreminder')),
		html_writer::link($PAGE->url . "&sorting=to_status&type=desc", get_string('form_to_status','block_dukreminder')),
		html_writer::link($PAGE->url . "&sorting=mailssent&type=desc", get_string('form_mailssent','block_dukreminder')),
		'');

$data = $DB->get_records('block_dukreminder',array('courseid' => $courseid),$sorting . ' ' . $sorttype,'id, title, subject, dateabsolute, to_status, mailssent');
foreach($data as $record) {
	$record->dateabsolute = ($record->dateabsolute > 0) ? date('d.m.Y',$record->dateabsolute) : '-';
	$record->to_status = $status[$record->to_status];
	$record->actions = 
		html_writer::link(
			new moodle_url('/blocks/dukreminder/new_reminder.php', array('courseid'=>$COURSE->id,'reminderid'=>$record->id)),
			html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/dukreminder/pix/new.png'), 'alt'=>"", 'height'=>16, 'width'=>23)))
		.html_writer::link(
			new moodle_url('/blocks/dukreminder/course_reminders.php', array('courseid'=>$COURSE->id,'sorting'=>$sorting,'delete'=>$record->id)),
			html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/dukreminder/pix/del.png'), 'alt'=>"", 'height'=>16, 'width'=>16)),array("onclick" => "return confirm('".get_string('form_delete','block_dukreminder')."')"));

	//don't display id, it is only used for the delete link
	unset($record->id);
}
$table->data = $data;
echo html_writer::table($table);

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>