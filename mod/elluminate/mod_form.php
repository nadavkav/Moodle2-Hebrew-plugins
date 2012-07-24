<?php // $Id: mod_form.php,v 1.7 2009-04-06 18:50:12 jfilip Exp $

/**
 * Standard activity module configuration form.
 *
 * @version $Id: mod_form.php,v 1.7 2009-04-06 18:50:12 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */

global $PAGE;

require_once $CFG->dirroot . '/course/moodleform_mod.php';
require_once dirname(__FILE__) . '/lib.php';
$PAGE->requires->js('/mod/elluminate/jquery-1.4.2.min.js');
$PAGE->requires->js('/mod/elluminate/mod_form.js');

class mod_elluminate_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $USER, $DB;

        $id = optional_param('update', '', PARAM_RAW);
        
        if(!empty($id)) {     
	        if (!$cm = get_coursemodule_from_id('elluminate', $id)) {
	            print_error("Course Module ID was incorrect");
	        }         
	        if (!$elluminate = $DB->get_record("elluminate", array('id'=>$cm->instance))) {
	            print_error("Course module is incorrect");
	        }
        }        
        if(!empty($id)) {
        	//In Moodle 2.2.2 the groupmodeforce option is not being enforced on objects below the 
        	//course level. So now we look to see if the force group mode is on or not. If groupmodeforce is not turned on for the
        	//course, then we can trust the cm object to have the correct groupmode value. Otherwise, we do not trust the cm 
        	//object and instead get the value from our existing course.
        	if($cm->groupmode != $elluminate->groupmode) {
        		if($COURSE->groupmodeforce == 0) {
        			$elluminate->groupmode = $cm->groupmode;
        		} else {
        			$cm->groupmode = $elluminate->groupmode;
        		}
        		elluminate_check_for_group_change($cm, $elluminate);
        		if (!$elluminate = $DB->get_record("elluminate", array('id'=>$cm->instance))) {
        			print_error("Course module is incorrect");
        		}
        		//We're doing a redirect onto itself so that the changes are reflected on the UI
        		//redirect($CFG->wwwroot . '/course/modedit.php?update=' . $cm->id. '&amp;return=0');
        	} else {
        		//print"<br></br>=============== we're in the esle";
        		//print_error(var_export($elluminate));
        		elluminate_check_for_new_groups($elluminate);
        	}
        }
		//print"<br></br>=============== we're in the esle";
		//print_error(var_export($elluminate));
		
		
        if(!empty($elluminate)) {
	        $participant = false;
	        if ($elluminate->sessiontype == 1) {
		    	//Checks to see if the user is a participant in the private meeting
		        if(elluminate_is_participant_in_meeting($elluminate, $USER->id)) {
		        	//then checks to make sure that the user role has the privilege to join a meeting
		        	$participant = true;
		        }
		    } else {
		    	$participant = true;
		    } 
	               
			if($participant == false) {
				print_error('You need to be invited to this private session in order to edit it.');
			}
        }               
               
        $elluminate_boundary_times = array (
			0 => '0',
			15 => '15',
			30 => '30',
			45 => '45',		
			60 => '60'
		);
		
		$elluminate_max_talkers = array(
			1 => '1',
			2 => '2',
			3 => '3',
			4 => '4',
			5 => '5',
			6 => '6'
		);
        
//-------------------------------------------------------------------------------

        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('title', 'elluminate'), array('size' => '64', 'maxlength' => '64'));                
        $mform->setType('name', PARAM_RAW);
        $mform->addRule('name', null, 'required', null, 'client');
		
		//These are in place to pull the localized string and use it in the javascript
		$mform->addElement('hidden', 'noGroupString', get_string('groupsnone'));
		$mform->addElement('hidden', 'seperateGroupString', get_string('groupsseparate')); 
		$mform->addElement('hidden', 'visibleGroupString', get_string('groupsvisible'));  
						
		$sessiontypes = array();
		if($COURSE->groupmodeforce == '0') {
			$sessiontypes[0] = get_string('course', 'elluminate');
			$sessiontypes[1] = get_string('private', 'elluminate');		
			$has_groups = $DB->get_records('groups', array('courseid'=>$COURSE->id), 'name ASC');
			if($has_groups != false) {
				$sessiontypes[2] = get_string('group', 'group');
			}						
			$has_groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id), 'name ASC');
			if($has_groupings != false) {
				$sessiontypes[3] = get_string('grouping', 'group');
			}			
		} else {
			if($COURSE->groupmode == 0) {
				$sessiontypes[0] = get_string('course', 'elluminate');
				$sessiontypes[1] = get_string('private', 'elluminate');
				$has_groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id), 'name ASC');
				if($has_groupings != false) {
					$sessiontypes[3] = get_string('grouping', 'group');
				}				
			} else if ($COURSE->groupmode > 0) {
				$has_groups = $DB->get_records('groups', array('courseid'=>$COURSE->id), 'name ASC');
				if($has_groups != false) {
					$sessiontypes[2] = get_string('group', 'group');
				}		
				$has_groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id), 'name ASC');
				if($has_groupings != false) {
					$sessiontypes[3] = get_string('grouping', 'group');
				}			
			}			
		}
		$mform->addElement('select', 'sessiontype', get_string('sessiontype', 'elluminate'), $sessiontypes);
		 
		$mform->addElement('select', 'customname',  get_string('appendgroupname', 'elluminate'), array(0 => 'None', 1 => 'Only Group Name', 2 => 'Append Group Name to Title'));		
		$mform->addElement('text', 'sessionname', get_string('customsessionname', 'elluminate'), array('size' => '64', 'maxlength' => '64'));
		$mform->disabledIf('sessionname', 'customname', 'eq', 1);
		$mform->disabledIf('sessionname', 'customname', 'eq', 2);		          	    
		
		$mform->addElement('text', 'sessionname_state', 'Session Name State', array('size' => '64', 'maxlength' => '64'));
									
		if(!empty($id)) {
			$mform->addElement('hidden', 'isedit', 'true'); //This is a placeholder to do disables on.
			$mform->addElement('hidden', 'edit_groupmode', $elluminate->groupmode);					
			if($elluminate->sessiontype == '0') {
				$mform->setDefault('sessiontype', 0);
			} else if($elluminate->sessiontype == '1') {
				$mform->setDefault('sessiontype', 1);
			} else if($elluminate->sessiontype == '2') {
				$mform->setDefault('sessiontype', 2);
			} else if($elluminate->sessiontype == '3') {
				$mform->setDefault('sessiontype', 3);
				$mform->setDefault('grouping', $elluminate->groupid);
			}
			
			$mform->disabledIf('sessiontype', 'isedit', 'eq', 'true');											
		}
		
        $mform->addElement('htmleditor', 'description', get_string('description', 'elluminate'));
        $mform->setType('description', PARAM_RAW);      			
				
        $mform->addElement('checkbox', 'customdescription', get_string('customdescription', 'elluminate'));
        $mform->disabledIf('customdescription', 'sessiontype', 'eq', 0);
		$mform->disabledIf('customdescription', 'sessiontype', 'eq', 1);	
        		
        $mform->addElement('date_time_selector', 'timestart', get_string('meetingbegins', 'elluminate'), array('optional'=>false, 'step'=>15));
        $mform->setDefault('timestart', time()+900);
        $mform->addElement('date_time_selector', 'timeend', get_string('meetingends', 'elluminate'), array('optional'=>false, 'step'=>15));        
        $mform->setDefault('timeend', time()+4500);        		

        $recording_options = array(
            ELLUMINATELIVE_RECORDING_NONE      => get_string('disabled', 'elluminate'),
            ELLUMINATELIVE_RECORDING_MANUAL    => get_string('manual', 'elluminate'),
            ELLUMINATELIVE_RECORDING_AUTOMATIC => get_string('automatic', 'elluminate')
        );

        $mform->addElement('select', 'recordingmode', get_string('recordingmode', 'elluminate') , $recording_options);
        $mform->setDefault('recordingmode', ELLUMINATELIVE_RECORDING_MANUAL);
        $mform->addHelpButton('recordingmode', 'recordingmode', 'elluminate');
        
        $mform -> addElement('select', 'maxtalkers', get_string('maxtalkers', 'elluminate'), $elluminate_max_talkers);
        if(!empty($elluminate -> maxtalkers)) {
        	$mform -> setDefault('maxtalkers', $elluminate -> maxtalkers);
        } else if($CFG -> elluminate_max_talkers == '-1') {
        	$mform -> setDefault('maxtalkers', ELLUMINATELIVE_MAX_TALKERS);
        } else {
        	$mform -> setDefault('maxtalkers', $CFG -> elluminate_max_talkers);
        }
        $mform -> addHelpButton('maxtalkers', 'maxtalkers', 'elluminate');

    	/// Don't allow choosing a boundary time if there is a globally defined default time.    	
        if ($CFG->elluminate_boundary_default != '-1') {
        	$attributes = array('disabled' => 'true');
        } else {
            $attributes = '';
        }

        $boundaryselect = $mform->addElement('select', 'boundarytime', get_string('boundarytime', 'elluminate') ,
                                             $elluminate_boundary_times, $attributes);
		if($CFG->elluminate_boundary_default == '-1') {
        	$mform->setDefault('boundarytime', ELLUMINATELIVE_BOUNDARY_DEFAULT);
        } else {
        	$mform->setConstant('boundarytime', $CFG->elluminate_boundary_default);
        }                                             
		      
		$mform->addHelpButton('boundarytime', 'boundarytime','elluminate');		
        $mform->addElement('checkbox', 'boundarytimedisplay', get_string('boundarytimedisplay', 'elluminate'));
        $mform->disabledIf('boundarytimedisplay', 'boundarytime', 'eq', 0); 

        $mform->addElement('modgrade', 'grade', get_string('gradeattendance', 'elluminate'));
        $mform->setDefault('grade', 0);	

		$has_groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id), 'name ASC');
		if($has_groupings) {
			if ($groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id))) {
                foreach ($groupings as $grouping) {
                    $options[$grouping->id] = format_string($grouping->name);
                }
            }
        	$mform->addElement('select', 'grouping_id', get_string('grouping', 'group'), $options);
        	$mform->addHelpButton('grouping_id', 'grouping', 'group');    
        	
        	//Set edit grouping id
        	if(!empty($id)) {
	        	$mform->setDefault('grouping_id', $elluminate->groupingid);
	        }        	
		}

        $this->standard_coursemodule_elements();

		/// Add rules for group name dependent options defined earlier.		
		$mform->disabledIf('groupname', 'groupsession', 'eq', 0);
		
        $this->add_action_buttons();

    }

}


