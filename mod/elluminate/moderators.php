<?php // $Id: moderators.php,v 1.6 2009-06-05 20:12:38 jfilip Exp $

/**
 * Used to update the moderators for a given meeting.
 *
 * @version $Id: moderators.php,v 1.6 2009-06-05 20:12:38 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */

	global $DB,$PAGE;
    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';
    $PAGE->requires->js('/mod/elluminate/jquery-1.4.2.min.js');
    $PAGE->requires->js('/mod/elluminate/add_remove_submit.js');
   	

    $id           = required_param('id', PARAM_INT);
    $PAGE->set_url('/mod/elluminate/participants.php', array('id'=>$id));
    
    $firstinitial = optional_param('firstinitial', '', PARAM_ALPHA);
    $lastinitial  = optional_param('lastinitial', '', PARAM_ALPHA);
    $sort         = optional_param('sort', '', PARAM_ALPHA);
    $dir          = optional_param('dir', '', PARAM_ALPHA);

	if (!$elluminate = $DB->get_record('elluminate', array('id'=>$id))) {
        print_error('Could not get meeting (' . $id . ')');
    }
    if (!$course = $DB->get_record('course', array('id'=>$elluminate->course))) {
        print_error('Invalid course.');
    }    
    if($elluminate->groupmode == 0 && $elluminate->groupparentid == 0) {
	    if (! $cm = get_coursemodule_from_instance('elluminate', $elluminate->id, $course->id)) {
	        print_error('Course Module ID was incorrect');
	    }
	} else if ($elluminate->groupmode != 0 && $elluminate->groupparentid != 0){
		if (! $cm = get_coursemodule_from_instance('elluminate', $elluminate->groupparentid, $course->id)) {
	        print_error('Course Module ID was incorrect');
	    }
	} else if ($elluminate->groupmode != 0 && $elluminate->groupparentid == 0){
	    if (! $cm = get_coursemodule_from_instance('elluminate', $elluminate->id, $course->id)) {
	        print_error('Course Module ID was incorrect');
	    }
	} else {
		print_error('Blackboard Collaborate Group Error');
	}
	
	$groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

/// Some capability checks.
    require_course_login($course, true, $cm);
    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
    $crscontext = get_context_instance(CONTEXT_COURSE, $course->id);
    require_capability('mod/elluminate:view', $modcontext);
    require_capability('mod/elluminate:managemoderators', $modcontext);

    $elluminate->name = stripslashes($elluminate->name);
    $notice        = '';

/// Check to see if groups are being used here
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);
	
	if($elluminate->groupmode != 0) {
	    $currentgroup = $elluminate->groupid;
	} else {
		if (empty($currentgroup)) {
	        $currentgroup = 0;
	    }
	}

/// Process data submission.
    if (($data = data_submitted($CFG->wwwroot . '/mod/elluminate/moderators.php')) && confirm_sesskey()) {
    /// Delete records for selected moderators chosen to be removed.
    	if($data->submitvalue == "remove") {
	        if (!empty($data->modscur)) {
	            if (!elluminate_del_users($elluminate, $data->modscur, $currentgroup, true)) {
	                $notice = get_string('couldnotremoveusersfromsession', 'elluminate');
	            }
	        }
    	}

    /// Add records for selected moderators chosen to be added.
    	if($data->submitvalue == "add") {
	        if (!empty($data->modsavail)) {
	            if (!elluminate_add_users($elluminate, $data->modsavail, $currentgroup, true)) {
	                $notice = get_string('couldnotadduserstosession', 'elluminate');
	            }
	        }
    	}
    }

/// Get a list of existing moderators for this meeting (if any) and assosciated
/// information.                          
    $curmods = elluminate_get_meeting_participants($elluminate->id, true);  
    
    $modsexist = array();
    if (!empty($curmods)) {
        foreach ($curmods as $curmod) {        	
            $modsexist[] = $curmod->id;
        }        
        reset($curmods);
    }
    
    /// Get a list of existing participants for this meeting (if any) and assosciated
	/// information.
	$curusers = elluminate_get_meeting_participants($elluminate->id);
    if (!empty ($curusers)) {
		foreach ($curusers as $curuser) {
			$modsexist[] = $curuser->id;
		}
	}

	/// Available moderators are teachers in this course who have an account on the
	/// Blackboard Collaborate server.
    $allmods = get_users_by_capability($modcontext, 'mod/elluminate:moderatemeeting',
                                        'u.id, u.firstname, u.lastname, u.username', 'u.lastname, u.firstname',
                                        '', '', '', '', false);

    $ausers = array_keys($allmods);
    // if groupmembersonly used, only include members of the appropriate groups.
    if ($allmods and !empty($CFG->enablegroupings) and $cm->groupmembersonly) {
        if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
            $ausers = array_intersect($ausers, array_keys($groupingusers));
        }
    }
    
    $ausers = array_diff($ausers, $modsexist);    
    $availmods = array();
    foreach ($ausers as $uid) {
        $availmods[$uid]  = $allmods[$uid];
    }       
    unset($allmods);

    $cavailmods = empty($availmods) ? 0 : count($availmods);
    $ccurmods   = empty($curmods) ? 0 : count($curmods) - 1; //Subtract one as the creator will be displayed above.    
    $sesskey         = !empty($USER->sesskey) ? $USER->sesskey : '';
    $strmeeting      = get_string('modulename', 'elluminate');
    $strmeetings     = get_string('modulenameplural', 'elluminate');
    $strmoderators   = get_string('editingmoderators', 'elluminate');
    $strmodscur      = ($ccurmods == 1) ? get_string('existingmoderator', 'elluminate') :
                                          get_string('existingmoderators', 'elluminate', $ccurmods);                                          
    $strmodsavail    = ($cavailmods == 1) ? get_string('availablemoderator', 'elluminate') :
                                            get_string('availablemoderators', 'elluminate', $cavailmods);
	$strcreator		 = $DB->get_record('user', array('id'=>$elluminate->creator));                                            
    $strfilterdesc   = get_string('participantfilterdesc', 'elluminate');
    $strall          = get_string('all');
	
/// Print header.
    $navigation = build_navigation($strmoderators, $cm);
    print_header_simple(format_string($elluminate->name), "",
                        $navigation, "", "", true, '');

	echo $OUTPUT->box_start('generalbox', 'notice');
	
    if (!empty($notice)) {
        notify($notice);
    }

    include($CFG->dirroot . '/mod/elluminate/moderators-edit.html');

	echo $OUTPUT->box_end();
    echo $OUTPUT->footer();


