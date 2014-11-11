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
 * Form to record new reminder
 *
 * @package    block_dukreminder
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 * @author	   Florian Jungwirth <fjungwirth@gtn-solutions.com>
 * @ideaandconcept Gerhard Schwed <gerhard.schwed@donau-uni.ac.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class reminder_form extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG,$COURSE,$DB;

		$mform = $this->_form; // Don't forget the underscore!

		// DISABLE
		$mform->addElement('hidden', 'disable');
		$mform->setType('disable',PARAM_INT);
		$mform->setDefault('disable', 0);
		
		if($this->_customdata['disable'] == 1) {
			$mform->addElement('text', 'mailssent', get_string('form_mailssent','block_dukreminder'), array("disabled"));	
			$mform->setType('mailssent',PARAM_INT);
		}
		
		// ID
		$mform->addElement('hidden', 'id');
		$mform->setType('id',PARAM_INT);
		$mform->setDefault('id', 0);
		
		$mform->addElement('header', 'nameforyourheaderelement', "Allgemeines");
		
		// TITLE
		$mform->addElement('text', 'title', get_string('form_title','block_dukreminder'), array('size'=>'50')); // Add elements to your form
		$mform->setType('title', PARAM_NOTAGS);                   //Set type of element
		
		// SUBJECT
		$mform->addElement('text', 'subject', get_string('form_subject','block_dukreminder')); // Add elements to your form
		$mform->setType('subject', PARAM_NOTAGS);                   //Set type of element
		$mform->addRule('subject', null, 'required', null, 'client');

		$placeholder = '<a href="#" onclick="insertTextAtCursor(\'###username###\');return false;">Username</a> ';
		$placeholder .= '<a href="#" onclick="insertTextAtCursor(\'###usermail###\');return false;">Usermail</a> ';
		$placeholder .= '<a href="#" onclick="insertTextAtCursor(\'###coursename###\');return false;">Kursname</a>';
		
		$placeholder = '###username### ###usermail### ###coursename###';
		
		$mform->addElement('html', html_writer::div(
				html_writer::div(html_writer::tag('label', 'Platzhalter'),'fitemtitle') . html_writer::div($placeholder,'felement ftext'),'fitem'));
		
		// TEXT
		$mform->addElement('editor', 'text', get_string('form_text','block_dukreminder'),array(
		    'subdirs'=>0,
		    'maxbytes'=>0,
		    'maxfiles'=>0,
		    'changeformat'=>0,
		    'context'=>null,
		    'noclean'=>0)); // Add elements to your form
		$mform->addRule('text', null, 'required', null, 'client');
		
		$mform->addElement('header', 'nameforyourheaderelement', "Auswahl Zeit");
		
		// DATEABSOLUT
		$mform->addElement('date_selector', 'dateabsolute', get_string('form_dateabsolute','block_dukreminder')); // Add elements to your form
		$mform->disabledIf('dateabsolute', 'daterelative[number]', 'neq', 0);
		
		
		// DATERELATIVE
		$mform->addElement('duration', 'daterelative', get_string('form_daterelative','block_dukreminder')); // Add elements to your form
		$mform->setDefault('daterelative', 0);
		$mform->disabledIf('daterelative', 'daterelative_completion[number]', 'neq', 0);
		
		// DATERELATIVE_COMPLETION
		/*
		$mform->addElement('duration', 'daterelative_completion', get_string('form_daterelative_completion','block_dukreminder')); // Add elements to your form
		$mform->setDefault('daterelative_completion', 0);
		$mform->disabledIf('daterelative_completion', 'daterelative[number]', 'neq', 0);
		*/
		
		// TO_STATUS
		//$data = array(0 => get_string('form_to_status_all','block_dukreminder'), 1 => get_string('form_to_status_completed','block_dukreminder'), 2 => get_string('form_to_status_notcompleted','block_dukreminder'));
		//$mform->addElement('hidden', 'to_status', get_string('form_to_status','block_dukreminder'),$data); // Add elements to your form
		
		$mform->addElement('header', 'nameforyourheaderelement', "Auswahl Kriterium");
		
		// Get criteria for course
		$courseid = required_param('courseid', PARAM_INT);
		$course = $DB->get_record('course',array('id' => $courseid));
		$completion = new completion_info($course);
		
		$criteria = array();
		$criteria[CRITERIA_ALL] = get_string('criteria_all','block_dukreminder');
		$criteria[CRITERIA_COMPLETION] = get_string('criteria_completion','block_dukreminder');
		$criteria[CRITERIA_ENROLMENT] = get_string('criteria_enrolment','block_dukreminder');
		
		if ($completion->has_criteria()) {
		
			// Get criteria and put in correct order
				
			foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY) as $id => $criterion) {
				$criteria[$id] = $criterion->get_title_detailed();
		
			}
			foreach ($completion->get_criteria() as $id => $criterion) {
				if (!in_array($criterion->criteriatype, array(
						COMPLETION_CRITERIA_TYPE_COURSE, COMPLETION_CRITERIA_TYPE_ACTIVITY))) {
					$criteria[$id] = $criterion->get_title_detailed();
				}
			}
				
			$mform->addElement('select','criteria',get_string('form_criteria','block_dukreminder'),$criteria);
		
		}
		
		$mform->addElement('header', 'nameforyourheaderelement', "Gruppenfilter");
		
		// TO_GROUPS
		$groups = array();
		foreach(groups_get_course_data($COURSE->id)->groups as $group) {
			$groups[$group->id] = $group->name;
		}
		$select = $mform->addElement('select','to_groups', get_string('form_to_groups', 'block_dukreminder'),$groups);
		$select->setMultiple(true);

		$mform->addElement('header', 'nameforyourheaderelement', "Berichtsoptionen");
		
		$placeholder = '<a href="#" onclick="insertTextAtCursor(\'###coursename###\');return false;">Kursname</a> ';
		$placeholder .= '<a href="#" onclick="insertTextAtCursor(\'###users###\');return false;">Liste der benachrichtigten User</a> ';
		$placeholder .= '<a href="#" onclick="insertTextAtCursor(\'###usercount###\');return false;">Anzahl der benachrichtigten User</a>';
		$placeholder = '###coursename### ###users### ###usercount###';
		
		$mform->addElement('html', html_writer::div(
				html_writer::div(html_writer::tag('label', 'Platzhalter'),'fitemtitle') . html_writer::div($placeholder,'felement ftext'),'fitem'));
		
		// TEXT
		$mform->addElement('editor', 'text_teacher', get_string('form_text_teacher','block_dukreminder'),array(
				'subdirs'=>0,
				'maxbytes'=>0,
				'maxfiles'=>0,
				'changeformat'=>0,
				'context'=>null,
				'noclean'=>0)); // Add elements to your form
		$mform->addRule('text_teacher', null, 'required', null, 'client');
		
		// TO_REPORTTRAINER
		$mform->addElement('checkbox', 'to_reporttrainer', get_string('form_to_reporttrainer', 'block_dukreminder'));
		
		// TO_REPORTSUPERIOR
		$mform->addElement('checkbox', 'to_reportsuperior', get_string('form_to_reportsuperior', 'block_dukreminder'));
		
		// TO_MAIL
		$mform->addElement('text', 'to_mail', get_string('form_to_mail','block_dukreminder'));
		$mform->setType('to_mail',PARAM_RAW);
		
		// only display buttons if form is enabled
		if($this->_customdata['disable'] == 0)
			$this->add_action_buttons();
		
		$mform->disabledIf('title', 'disable', 'neq', 0);
		$mform->disabledIf('subject', 'disable', 'neq', 0);
		$mform->disabledIf('text', 'disable', 'neq', 0);
		$mform->disabledIf('dateabsolute', 'disable', 'neq', 0);
		$mform->disabledIf('daterelative', 'disable', 'neq', 0);
		$mform->disabledIf('to_status', 'disable', 'neq', 0);
		$mform->disabledIf('to_reporttrainer', 'disable', 'neq', 0);
		$mform->disabledIf('to_mail', 'disable', 'neq', 0);
		$mform->disabledIf('to_groups', 'disable', 'neq', 0);
	}

	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		
        $errors= array();
 
        if (($data['daterelative'] < 0)){
               $errors['daterelative'] = get_string('daterelative_error','block_dukreminder');
        }
        
        if (!empty($data['to_mail'])){
        	$mails = explode(';',$data['to_mail']);
        	foreach($mails as $mail) {
        		if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        			$errors['to_mail'] = get_string('to_mail_error','block_dukreminder');
        		}
        	}
        }
        
        if ($data['daterelative'] == 0 && $data['criteria'] == CRITERIA_ENROLMENT)
        	$errors['criteria'] = get_string('criteria_error','block_dukreminder');

        if ($data['daterelative'] > 0 && $data['criteria'] == CRITERIA_ALL)
        	$errors['criteria'] = get_string('criteria_error2','block_dukreminder');
        
        return $errors;
    }
}