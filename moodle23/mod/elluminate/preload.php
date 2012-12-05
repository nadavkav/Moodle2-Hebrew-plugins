<?php // $Id: preload.php,v 1.2 2009-06-05 20:12:38 jfilip Exp $

/**
 * Manage load a whiteboard preload file onto the ELM server.
 *
 * @version $Id: preload.php,v 1.2 2009-06-05 20:12:38 jfilip Exp $
 * @author Remote Learner - http://www.remote-learner.net/
 * @author Justin Filip <jfilip@remote-learner.net>
 */
	global $DB;
    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';
    $PAGE->requires->js('/mod/elluminate/jquery-1.4.2.min.js');
	$PAGE->requires->js('/mod/elluminate/preload.js');

    $id     = optional_param('id', '', PARAM_INT);
    $PAGE->set_url('/mod/elluminate/preload.php', array('id'=>$id));
    $delete = optional_param('delete', 0, PARAM_ALPHANUM);
    
    $a = new stdClass;
    $a->uploadmaxfilesize = ini_get('upload_max_filesize');
    $a->postmaxsize = ini_get('post_max_size');

	if(empty($id)) {
		print_error('Preload file was too large for upload!');
	}
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
		
	require_course_login($course, true, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminate:managepreloads', $context);

/// Check to see if groups are being used here
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

    if (empty($currentgroup)) {
        $currentgroup = 0;
    }
    
    if(empty($elluminate->meetingid)) {
		elluminate_group_instance_check($elluminate, $cm->id);
	}


    $baseurl = $CFG->wwwroot . '/mod/elluminate/preload.php?id=' . $elluminate->id;
	
	/// Print the page header
    $strelluminates = get_string('modulenameplural', 'elluminate');
    $strelluminate  = get_string('modulename', 'elluminate');
    $straddpreload  = get_string('addpreload', 'elluminate');
    $strdelpreload  = get_string('deletewhiteboardpreload', 'elluminate');

    $buttontext = update_module_button($cm->id, $course->id, $strelluminate);
    $navigation = build_navigation(empty($delete) ? $straddpreload : $strdelpreload, $cm);

    print_header_simple(format_string($elluminate->name), '', $navigation, '', '', true,
                        $buttontext, navmenu($course, $cm));


	/// Delete a preload file for this meeting.
    if (!empty($delete)) {
        if (!empty($elluminate->meetingid)) {
            if ($preload = elluminate_list_meeting_preloads($elluminate->meetingid)) {            	
                if ($preload->presentationid == $delete) {
                /// Delete the preload from the meeting.
				if (!elluminate_delete_preload($preload->presentationid, $elluminate->meetingid)) {
                        print_error(get_string('preloaddeleteerror', 'elluminate'));
                    }
                    redirect($CFG->wwwroot . '/mod/elluminate/view.php?id=' . $cm->id,
                             get_string('preloaddeletesuccess', 'elluminate'), 5);
                }
            }
        }
    }


    if (($data = data_submitted($CFG->wwwroot . '/mod/elluminate/preload.php')) && confirm_sesskey()) {
        if (!empty($_FILES['whiteboard'])) {
        	
        	$actual_filename = $_FILES['whiteboard']['name'];     
            $filepath = $_FILES['whiteboard']['tmp_name'];            
            $filesize = $_FILES['whiteboard']['size'];
            
            $search = array("<",">","&","#","%","\"","\\","|", "'");	
			$replace = '';	
			$filename = str_replace($search, $replace, $actual_filename);
			            
             /// Make sure the file uses a valid whiteboard preload file extension.
            if (!eregi('\.([a-zA-Z0-9]+)$', $filename, $match)) {
                print_error(get_string('preloadnofileextension', 'elluminate'));
            }

            if (!isset($match[1])) {
                print_error(get_string('preloadnofileextension', 'elluminate'));
            }           

        	/// Ensure that the document actually contains XML.  Only required for wbd and wbp files
        	if(strtolower($match[1]) == 'wbd' || strtolower($match[1]) == 'wbp') {
	            if (!simplexml_load_file($filepath)) {
	                print_error(get_string('preloadinvalidfilecontents', 'elluminate', $a));
	            }
        	}
        	
        	if (!(strtolower($match[1]) == 'wbd' ||
            	strtolower($match[1]) == 'wbp' ||
            	strtolower($match[1]) == 'elp' ||
            	strtolower($match[1]) == 'elpx')) {
                print_error(get_string('preloadinvalidfileextension', 'elluminate'));
            }    
                                   	
            if (empty($filesize)) {
                //print_error('preloademptyfile', 'elluminate', $baseurl);
                print_error(get_string('preloadinvalidfilecontents', 'elluminate', $a));
            } 

       		 /// The file is valid, let's proceed with creating the preload.
       		/// Read the file contents into memory.
            if (!$filedata = file_get_contents($filepath)) {
                print_error(get_string('preloadcouldnotreadfilecontents', 'elluminate'));
            }

            /// Create the preload object on the server.
            $preload = elluminate_upload_presentation_content($filename, '', $filedata, $USER->id, $actual_filename);
            if (empty($preload->presentationid)) {
                print_error(get_string('preloadcouldnotcreatepreload', 'elluminate'));
            }

        /// Associate the preload object with the meeting.
        
            if (!elluminate_set_session_presentation($preload->presentationid, $elluminate->meetingid)) {
                print_error(get_string('preloadcouldnotaddpreloadtomeeting', 'elluminate'));
            }
		
            redirect($CFG->wwwroot . '/mod/elluminate/view.php?id=' . $cm->id,
                     get_string('preloaduploadsuccess', 'elluminate'), 5);
        }
    }


    //groups_print_activity_menu($cm, 'preload.php?id=' . $elluminate->id, false, true);

    $sesskey = !empty($USER->sesskey) ? $USER->sesskey : '';

/// Print the main part of the page
    echo $OUTPUT->box_start('generalbox', 'notice');

    echo '<p>'. get_string('preloadchoosewhiteboardfile', 'elluminate', $a) . '</p>';
    echo '<form action="preload.php" method="post" enctype="multipart/form-data">';
    echo '<input type="hidden" name="sesskey" value="' . $sesskey . '" />';
    echo '<input type="hidden" name="id" value="' . $elluminate->id . '" />';
    echo '<input type="hidden" name="userfilename" id="userfilename">'; 
    echo '<input type="file" name="whiteboard" alt="whiteboard" id="userfile" size="50" /><br />';
    echo '<input type="submit" value="' . get_string('uploadthisfile') . '" /><br />';
    echo '<input type="button" value="' . get_string('cancel') . '" onclick="document.location = \'' .
         $CFG->wwwroot . '/mod/elluminate/view.php?id=' . $cm->id . '\'" />';
    echo '</form>';
        
	echo $OUTPUT->box_end();
    echo $OUTPUT->footer($course);


