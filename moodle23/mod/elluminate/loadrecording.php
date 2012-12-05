<?php // $Id: loadrecording.php,v 1.1.2.2 2009/03/18 16:45:54 mchurch Exp $

/**
 * Blackboard Collaborate recording load script.
 * 
 * @version $Id: loadrecording.php,v 1.1.2.2 2009/03/18 16:45:54 mchurch Exp $
 * @author Justin Filip <jfilip@oktech.ca>
 * @author Remote Learner - http://www.remote-learner.net/
 */

	global $DB;
    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';



    $id = required_param('id', PARAM_INT);


    if (!$recording = $DB->get_record('elluminate_recordings', array('id'=>$id))) {
        print_error('Could not get recording (' . $id . ')');
    }

	/*
    if (!$meeting = $DB->get_record('elluminate_session', 'meetingid', $recording->meetingid)) {
        print_error('Could not get meeting (' . $recording->meetingid . ')');
    }
    */

    if (!$elluminate = $DB->get_record('elluminate', array('meetingid'=>$recording->meetingid))) {
        print_error('Could not load activity record.');
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

    require_course_login($course, true, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminate:viewrecordings', $context);

    if (!empty($cm)) {
        $cmid = $cm->id;
    } else {
        $cmid = 0;
    }

    add_to_log($elluminate->course, 'elluminate', 'view recording', 'loadrecording.php?id=' .
               $recording->id, $elluminate->id, $cmid, $USER->id);

/// Load the recording.
    if (!elluminate_build_recording_jnlp($recording->recordingid, $USER->id)) {
        print_error('Could not load Blackboard Collaborate recording');
    }


