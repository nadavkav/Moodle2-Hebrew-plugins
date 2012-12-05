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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_elluminate_activity_task
 */

/**
 * Define the complete Blackboard Collaborate structure for backup, with file and id annotations
 */
class backup_elluminate_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $elluminate = new backup_nested_element('elluminate', array('id'), array(
            'meetingid', 'meetinginit', 'course', 'creator',
            'sessiontype', 'groupingid', 'groupmode', 'groupid',
            'groupparentid', 'name', 'sessionname', 'description',
            'intro','introformat','customname','customdescription',
            'timestart','timeend','recordingmode','boundarytime',
            'boundarytimedisplay','maxtalkers','chairlist','nonchairlist','grade',
            'timemodified'));		
		
        $recordings = new backup_nested_element('recordings');
        $recording = new backup_nested_element('recording', array('id'), array(
            'meetingid', 'recordingid', 'description',
            'recordingsize','visible', 'groupvisible','created'));

        $preloads = new backup_nested_element('preloads');
        $preload = new backup_nested_element('preload', array('id'), array(
            'meetingid', 'presentationid', 'description',
            'size', 'creatorid'));        
            
        $attendance = new backup_nested_element('attendance');
        $attendee = new backup_nested_element('attendee', array('id'), array(
            'userid', 'elluminateid', 'grade','timemodified'));         
		
        // Build the tree
        $elluminate->add_child($recordings);
        $recordings->add_child($recording);

        $elluminate->add_child($preloads);
        $preloads->add_child($preload);	
        
       	$elluminate->add_child($attendance);
        $attendance->add_child($attendee);	
		
        // Define sources
        $elluminate->set_source_table('elluminate', array('id' => backup::VAR_ACTIVITYID));
        
		$recording->set_source_table('elluminate_recordings', array('meetingid' => '../../meetingid'));
		$preload->set_source_table('elluminate_preloads', array('meetingid' => '../../meetingid'));		

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
        	$attendance->set_source_table('elluminate_attendance', array('elluminateid' => backup::VAR_ACTIVITYID));    
        }

        // Define id annotations

        // Define file annotations

        // Return the root element (elluminate), wrapped into standard activity structure
        return $this->prepare_activity_structure($elluminate); 
    }
}
