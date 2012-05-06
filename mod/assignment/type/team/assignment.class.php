<?php // $Id: assignment.class.php,v 1.32.2.15 2008/10/09 11:22:14 poltawski Exp $
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/assignment/type/upload/assignment.class.php');
define('TEAM_TABLE', 'assignment_team');
define('TEAM_STUDENT_TABLE', 'assignment_team_student');

/**
 * Extend the base assignment class for assignments where you upload a single file
 * Moodle Team Assignment 1.0.2
 */
class assignment_team extends assignment_upload {

    function assignment_team($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {
        parent::assignment_upload($cmid, $assignment, $cm, $course);
        $this->type = 'typeteam';
    }

    function view() {
    	global $USER, $OUTPUT, $DB, $CFG;
    	
        $joinaction = optional_param('act_jointeam', NULL, PARAM_ALPHA);
        $removeaction = optional_param('act_removemember', NULL, PARAM_ALPHA);
        $deleteteamaction = optional_param('act_deleteteam',NULL,  PARAM_ALPHA);
        $opencloseaction = optional_param('act_opencloseteam',NULL, PARAM_ALPHA);
        $createaction = optional_param('act_createteam', NULL,  PARAM_ALPHA);
        
        //common parameters
        $teamid = optional_param('teamid', NULL, PARAM_INT);

        require_capability('mod/assignment:view', $this->context);


        add_to_log($this->course->id, 'assignment', 'view', "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

        $this->view_header();

        if ($this->assignment->timeavailable > time()
        and !has_capability('mod/assignment:grade', $this->context)      // grading user can see it anytime
        and $this->assignment->var3) {                                   // force hiding before available date
            echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
            print_string('notavailableyet', 'assignment');
            echo $OUTPUT->box_end(); 
        } else {
            $this->view_intro();
        }
        
        $this->view_dates();
        
        if (has_capability('mod/assignment:submit', $this->context)) {
        	//1.check if user can join team
            //possible values from join team action
            $groups = optional_param('groups', NULL, PARAM_INT);
            $jointime = optional_param('jointeamtime', NULL, PARAM_INT);
            
            if (isset($joinaction)
            && isset($jointime)
            && isset($groups)
            && (!isset($_SESSION['jointeamtime']) || $_SESSION['jointeamtime']!= $jointime) 
            && confirm_sesskey()) {
                error_log('start join team');
                $this->join_team($USER->id, $groups);
                $_SESSION['jointeamtime'] = $jointime;
            }

            //2.check if user can remove a member from a team
            if (isset($removeaction)&& confirm_sesskey()) {
                //possible values
                $members = optional_param('members', NULL, PARAM_INT);
                $removetime = optional_param('removetime', NULL, PARAM_INT);
                $confirm = optional_param('confirm', NULL, PARAM_INT);
                if (isset($members)
                && isset($removetime)
                && $this->is_member($teamid)
                && (!isset($_SESSION['removetime']) || $_SESSION['removetime']!= $removetime))
                //use session control to avoid users processing this action by refresh browser.
                {
                    error_log('start remove members');
                    $_SESSION['removetime'] = $removetime;
                    //$members maynot be array if the POST back from delete members UI
                    //refer remove_user_from_team().
                    $removeright = $this->is_member($teamid);
                    if (!is_array($members)) {
                        if( is_numeric($members)
                        && $members >= 0
                        && isset($confirm)
                        && $confirm == 1) {
                            $memberids = array();
                            for($i = 0 ; $i<=$members ; $i++){
                                $memberkey = 'member'.$i;
                                $memberids[] = optional_param ($memberkey, NULL, PARAM_INT);
                            }
                            $this ->remove_users_from_team($memberids, $teamid, $removeright );
                        }
                    } else if(count($members)>0) {
                        $this->remove_users_from_team($members, $teamid, $removeright);
                    }
                }
            }

            //3.check if user delete a team
            $deleteteamtime = optional_param('deleteteamtime', NULL, PARAM_INT);
            if (isset($deleteteamaction)
            && isset($deleteteamtime)
            && isset($teamid)
            && (!isset($_SESSION['deleteteamtime']) || $_SESSION['deleteteamtime']!= $deleteteamtime )
            && confirm_sesskey()) {
                //use session control to avoid users processing this action by refresh browser.
                error_log('start to delete team ');
                $this->delete_team($teamid);
                $_SESSION['deleteteamtime'] = $deleteteamtime;
            }

            //4. check if user can open or close team
            $openclosetime = optional_param('openclosetime', NULL, PARAM_INT);
            if (isset($opencloseaction)
            && isset($openclosetime )
            && isset($teamid)
            && (!isset($_SESSION['openclosetime']) || $_SESSION['openclosetime']!= $openclosetime)
            && confirm_sesskey()) {
                error_log('start to open or close a team');
                //use session control to avoid users processing this action by refresh browser.
                $this->open_close_team($teamid);
                $_SESSION['openclosetime'] = $openclosetime;

            }

            //5. check if user can create a team
            $createteamtime = optional_param('createteamtime', NULL, PARAM_INT);
            $teamname = optional_param('teamname', NULL, PARAM_TEXT);
            if (isset($createaction)
            && isset($createteamtime)
            && (!isset($_SESSION['createteamtime']) || $_SESSION['createteamtime']!= $createteamtime)
            && confirm_sesskey()) {
                error_log('create team start');
                //error_log('creation parameter: '.$createaction);
                $this->create_team($teamname);
                $_SESSION['createteamtime'] = $createteamtime;
            }

            //6. check if user belongs to a team for this assignment.
            //We already done capability check. 
            $team = $this->get_user_team($USER->id);
            if ($team) {
            	if ($submission = $this->get_submission($USER->id)) {
            		$filecount = $this->count_team_files($team->id);
            	} else {
            		$filecount = 0;
            		$submission = null;
            	}
            	$this->print_team_admin($team, $filecount, $submission);

            	$this->view_final_submission($team->id);

            } else {
            	// Allow the user to join an existing team or create and join a new team
            	$this->print_team_list();
            }
        }
        $this->view_footer();
    }
    
    //One change, but have to be here to use this count_team_files for is_finalized
	function can_finalize($submission) {
        global $USER;

        if(is_bool($submission)) {
            return false;
        }

        if (!$this->drafts_tracked()) {
            return false;
        }

        if ($this->is_finalized($submission)) {
            return false;
        }
		
        if($teamid = $this->get_user_team($USER->id)) $teamid = $teamid->id;
        
        if (has_capability('mod/assignment:grade', $this->context)) {
            return true;

        } else if (is_enrolled($this->context, $USER, 'mod/assignment:submit')
          and $this->isopen()                                                 // assignment not closed yet
          and !empty($submission)                                             // submission must exist
          and ($this->count_team_files($teamid)
            or ($this->notes_allowed() and !empty($submission->data1)))) {    // something must be submitted

            return true;
        } else {
            return false;
        }
    }
    
	function count_team_files($teamid) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'mod_assignment', 'team_submission', $teamid, 'timemodified', false);
        return count($files);
    }
    
	function print_responsefiles($userid, $return=false) {
        global $CFG, $USER, $OUTPUT, $PAGE;

        $mode    = optional_param('mode', '', PARAM_ALPHA);
        $offset  = optional_param('offset', 0, PARAM_INT);

        $output = $OUTPUT->box_start('responsefiles');

        $candelete = $this->can_manage_responsefiles();
        $strdelete = get_string('delete');

        $fs = get_file_storage();
        $browser = get_file_browser();
        $teamid = $this->get_user_team($userid)->id;

        if ($submission = $this->get_submission($userid)) {
            $renderer = $PAGE->get_renderer('mod_assignment');
            $output .= $renderer->assignment_files($this->context, $teamid, 'response');
        }
        $output .= $OUTPUT->box_end();

        if ($return) {
            return $output;
        }
        echo $output;
    }

    function print_team_admin($team, $filecount, $submission) {
        global $CFG, $USER, $OUTPUT;
        // display the team and the file submission box
        echo '<table class="generaltable generalbox groupmanagementtable boxaligncenter">';

        echo '<tr>';
        	echo '<td>';
        		$teamheading = $team->name." ".$this->get_team_status_name($team->membershipopen);
        		echo $OUTPUT->heading($teamheading, 2);
        	echo '</td>';
        echo '</tr>';
        echo '<tr>';
        	echo '<td>';
        		$this->view_feedback();
        	echo '</td>';
        echo '</tr>';
        echo '<tr>';
        	echo '<td>';
		        if (!$this->drafts_tracked() or !$this->isopen() or $this->is_finalized($submission)) {
		            echo $OUTPUT->heading(get_string('submission', 'assignment'), 6);
		        } else {
		            echo $OUTPUT->heading(get_string('submissiondraft', 'assignment'), 6);
		        }
		
        		if ($filecount and $submission) {
        		    echo $OUTPUT->heading($this->print_team_files($USER->id, true), 6, 'generalbox boxaligncenter');
        		} else {
        		    if (!$this->isopen() or $this->is_finalized($submission)) {
        		        echo $OUTPUT->box(get_string('nofiles', 'assignment'));
        		    } else {
        		        echo $OUTPUT->box(get_string('nofilesyet', 'assignment'));
        		    }
        		}
        			$this->view_upload_form();

            	if ($this->notes_allowed()) {
            		echo $OUTPUT->heading(get_string('notes', 'assignment'), 3);
            		$this->view_notes();
            	}
        	echo '</td>';
        echo '</tr>';
        echo '<tr>';
        	//print team members
        	//echo '<td>';
        	$members =$this -> get_members_from_team ($team ->id);
        	$action = $_SERVER['REQUEST_URI'];
        	$datas = array('members'=>$members, 'team'=>$team, 'openstatus'=>$team->membershipopen);
        	$removememberform = new team_management_form($action, $datas);
        	$removememberform->display();
        	//echo '</td>';
        echo '</tr>';
        //team row end
        echo '</table>';


    }

    /**
     * to support team feedback
     * Creating  a uploading response file input box.
     * @param $submission
     * @param $return
     */
    function custom_team_feedbackform($id, $teamid, $userrep, $mode) {
        global $CFG, $OUTPUT;
        echo $output = get_string('responsefiles', 'assignment').': ';

        $output .= '<form enctype="multipart/form-data" method="post" '.
             "action=\"$CFG->wwwroot/mod/assignment/upload.php\">";
        $output .= '<div>';
        $output .= '<input type="hidden" name="id" value="'.$id.'" />';
        $output .= '<input type="hidden" name="action" value="uploadteamresponse" />';
        $output .= '<input type="hidden" name="mode" value="'.$mode.'" />';
        $output .= '<input type="hidden" name="teamid" value="'.$teamid.'" />';
        $output .= '<input type="hidden" name="userrep" value="'.$userrep.'" />';
        $output .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        require_once($CFG->libdir.'/uploadlib.php');
        $output .= upload_print_form_fragment(1,array('newfile'),null,false,null,0,0,true);
        $output .= '<input type="submit" name="save" value="'.get_string('uploadthisfile').'" />';
        $output .= '</div>';
        $output .= '</form>';

        $responsefiles = $this->print_team_responsefiles($id, $teamid, $userrep, $mode);
        if (!empty($responsefiles)) {
            $output .= $responsefiles;
        }
        return $output;
    }
    
	function view_upload_form() {
        global $CFG, $USER, $OUTPUT;

        $submission = $this->get_submission($USER->id);
        $team = $this->get_user_team($USER->id);

        if ($this->is_finalized($submission)) {
            // no uploading
            return;
        }

        $uploadfiles = 1;
        if ($this->can_upload_file($submission,$team->id)) {
            $fs = get_file_storage();
            // edit files in another page
            if ($submission) {
                if ($files = $fs->get_area_files($this->context->id, 'mod_assignment', 'team_submission', $team->id, "timemodified", false)) {
                    $uploadfiles = 0;
                	$str = get_string('editthesefiles', 'assignment');
                } else {
                    $str = get_string('uploadfiles', 'assignment');
                }
            } else {
                $str = get_string('uploadfiles', 'assignment');
            }
            $html = $OUTPUT->single_button(new moodle_url('/mod/assignment/type/team/upload.php', array('contextid'=>$this->context->id, 'userid'=>$USER->id)), $str, 'post');
        	if($uploadfiles) {
            	$html = str_replace('</div></form>',$OUTPUT->help_icon('uploadfiles','assignment_team').'</div></form>',$html);
            } else {
            	$html = str_replace('</div></form>',$OUTPUT->help_icon('editthesefiles','assignment_team').'</div></form>',$html);
            }
            echo $html;
        }
    }


    function view_final_submission($teamid) {
        global $CFG, $USER, $OUTPUT;

        $submission = $this->get_submission($USER->id);
        if ($this->isopen() and $this->can_finalize($submission)) {
            //print final submit button
            echo $OUTPUT->heading(get_string('submitformarking','assignment'), 3);
            echo '<div style="text-align:center">';
            echo '<form method="post" action="upload.php">';
            echo '<fieldset class="invisiblefieldset">';
            echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
            echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
            echo '<input type="hidden" name="action" value="finalize" />';
            echo '<input type="hidden" name="teamid" value="'.$teamid.'" />';
            echo '<input type="submit" name="formarking" value="'.get_string('sendformarking', 'assignment').'" />';
            echo $OUTPUT->help_icon('sendformarking','assignment_team');
            echo '</fieldset>';
            echo '</form>';
            echo '</div>';
        } else if (!$this->isopen()) {
            echo $OUTPUT->print_heading(get_string('nomoresubmissions','assignment'), 3);

        } else if ($this->drafts_tracked() and $state = $this->is_finalized($submission)) {
            if ($state == ASSIGNMENT_STATUS_SUBMITTED) {
                echo $OUTPUT->heading(get_string('submitedformarking','assignment'), 3);
            } else {
                echo $OUTPUT->heading(get_string('nomoresubmissions','assignment'), 3);
            }
        } else {
            //no submission yet
        }
    }
    
    /**
     * @param $teamid
     */
    function print_team_responsefiles($id, $teamid, $userrep, $mode, $delete = true) {
        global $CFG, $USER, $OUTPUT;
        $output = '';
        $filearea = $this->team_file_area_name($teamid).'/responses';
        $basedir = $CFG->dataroot.'/'.$filearea;
        if (!is_dir($basedir)) {
            return $output;
        }

        $candelete = $this->can_manage_responsefiles();
        $strdelete   = get_string('delete');

        if ($files = get_directory_list($basedir)) {
            require_once($CFG->libdir.'/filelib.php');
            foreach ($files as $key => $file) {

                $icon = mimeinfo('icon', $file);

                $ffurl = get_file_url("$filearea/$file");

                $output .= '<a href="'.$ffurl.'" ><img src="'.$CFG->pixpath.'/f/'.$icon.'" alt="'.$icon.'" />'.$file.'</a>';

                if ($candelete && $delete) {
                    $delurl  = "$CFG->wwwroot/mod/assignment/delete.php?id=$id&amp;file=$file&amp;teamid=$teamid&amp;userrep=$userrep&amp;mode=$mode&amp;action=teamresponse";

                    $output .= '<a href="'.$delurl.'">&nbsp;'
                    .'<img title="'.$strdelete.'" src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt=""/></a> ';
                }

                $output .= '&nbsp;';
            }
        }
        $output = '<div class="responsefiles">'.$output.'</div>';

        return $output;

    }

    /**
     * Although all team members have their own assignment submission record, the work that is
     * submitted belongs to the team and is kept in the team_file_area
     * @param teamid
     */
    private function print_team_answer($teamid){
        global $CFG, $OUTPUT, $PAGE;
		
        $userid = $this->get_first_teammember($teamid)->student;
        $submission = $this->get_submission($userid);

        $output = '';
        if ($this->drafts_tracked() and $this->isopen() and !$this->is_finalized($submission)) {
            $output .= '<strong>'.get_string('draft', 'assignment').':</strong> ';
        }

        if ($this->notes_allowed() and !empty($submission->data1)) {
            $link = new moodle_url("/mod/assignment/type/team/notes.php", array('id'=>$this->cm->id, 'userid'=>$userid));
            $action = new popup_action('click', $link, 'notes', array('height' => 500, 'width' => 780));
            $output .= $OUTPUT->action_link($link, get_string('notes', 'assignment'), $action, array('title'=>get_string('notes', 'assignment')));

            $output .= '&nbsp;';
        }
        		
        $renderer = $PAGE->get_renderer('mod_assignment');
        $output = $OUTPUT->box_start('files').$output;
        $output .= $renderer->assignment_files($this->context, $teamid, 'team_submission');
        $output .= $OUTPUT->box_end();

        return $output;
    }

    /**
     * Produces a list of links to the files uploaded by a user
     *
     * @param $userid int optional id of the user. If 0 then $USER->id is used.
     * @param $return boolean optional defaults to false. If true the list is returned rather than printed
     * @return string optional
     */
    
    function upload($mform=null, $filemanager_options=null) {
        $action = required_param('action', PARAM_ALPHA);
        switch ($action) {
            case 'finalize':
                $this->finalize();
                break;
            case 'finalizeclose':
                $this->finalizeclose();
                break;
            case 'unfinalize':
                $this->unfinalize();
                break;
            case 'uploadresponse':
                $this->upload_responsefile();
                break;
            case 'uploadteamresponse':
                $this->upload_team_responsefile();
                break;
            case 'uploadfile':
                $this->upload_file($mform, $filemanager_options);
            case 'savenotes':
            case 'editnotes':
                $this->upload_notes();
            default:
                print_error('Error: Unknow upload action ('.$action.').');
        }
    }
    
	function finalize($forcemode=null) {
        global $USER, $DB, $OUTPUT;
        $userid = optional_param('userid', $USER->id, PARAM_INT);
        $offset = optional_param('offset', 0, PARAM_INT);
        $confirm    = optional_param('confirm', 0, PARAM_BOOL);
        $returnurl  = new moodle_url('/mod/assignment/view.php', array('id'=>$this->cm->id));

        $teamid = $this->get_user_team($userid)->id;
        $this->close_team($teamid);
        $members = $this->get_members_from_team($teamid);
        foreach($members as $member) {
        	$submission = $this->get_submission($member->student);
        	
        	if ($forcemode!=null) {
        		$returnurl  = new moodle_url('/mod/assignment/submissions.php',
        		array('id'=>$this->cm->id,
                    'userid'=>$userid,
                    'mode'=>$forcemode,
                    'offset'=>$offset
        		));
        	}

        	if (!$this->can_finalize($submission)) {
        		redirect($returnurl->out(false)); // probably already graded, redirect to assignment page, the reason should be obvious
        	}

        	if ($forcemode==null) {
        		if (!data_submitted() or !$confirm or !confirm_sesskey()) {
        			$optionsno = array('id'=>$this->cm->id);
        			$optionsyes = array ('id'=>$this->cm->id, 'confirm'=>1, 'action'=>'finalize', 'sesskey'=>sesskey());
        			$this->view_header(get_string('submitformarking', 'assignment'));
        			echo $OUTPUT->heading(get_string('submitformarking', 'assignment'));
        			echo $OUTPUT->confirm(get_string('onceassignmentsent', 'assignment'), new moodle_url('upload.php', $optionsyes),new moodle_url( 'view.php', $optionsno));
        			$this->view_footer();
        			die;
        		}
        	}
        	$updated = new stdClass();
        	$updated->id           = $submission->id;
        	$updated->data2        = ASSIGNMENT_STATUS_SUBMITTED;
        	$updated->timemodified = time();

        	$DB->update_record('assignment_submissions', $updated);
        	add_to_log($this->course->id, 'assignment', 'upload', //TODO: add finalize action to log
                'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
        	$submission = $this->get_submission($member->student);
        	$this->update_grade($submission);
        	$this->email_teachers($submission);

        	// Trigger assessable_files_done event to show files are complete
        	$eventdata = new stdClass();
        	$eventdata->modulename   = 'assignment';
        	$eventdata->cmid         = $this->cm->id;
        	$eventdata->itemid       = $submission->id;
        	$eventdata->courseid     = $this->course->id;
        	$eventdata->userid       = $member->student;
        	events_trigger('assessable_files_done', $eventdata);
        }

        if ($forcemode==null) {
            redirect($returnurl->out(false));
        }
    }
    
	function unfinalize($forcemode=null) {
        global $DB;

        $userid = required_param('userid', PARAM_INT);
        $mode   = required_param('mode', PARAM_ALPHA);
        $offset = required_param('offset', PARAM_INT);
        
        $returnurl = new moodle_url('/mod/assignment/submissions.php', array('id'=>$this->cm->id, 'userid'=>$userid, 'mode'=>$mode, 'offset'=>$offset, 'forcerefresh'=>1) );
        
        if ($forcemode!=null) {
            $mode=$forcemode;
        }
        if($teamid = $this->get_user_team($userid)->id) $members = $this->get_members_from_team($teamid);
        foreach($members as $member) {
        	if (data_submitted()
        	and $member->student
        	and $submission = $this->get_submission($member->student)
        	and $this->can_unfinalize($submission)
        	and confirm_sesskey()) {

        		$updated = new stdClass();
        		$updated->id = $submission->id;
        		$updated->data2 = '';
        		$DB->update_record('assignment_submissions', $updated);
        		//TODO: add unfinalize action to log
        		add_to_log($this->course->id, 'assignment', 'view submission', 'submissions.php?id='.$this->cm->id.'&userid='.$member->student.'&mode='.$mode.'&offset='.$offset, $this->assignment->id, $this->cm->id);
        		$submission = $this->get_submission($member->id);
        		if($submission) $this->update_grade($submission);
        	}
        }

        if ($forcemode==null) {
            redirect($returnurl);
        }
    }

    //override from upload_assignment
    //Teachers can change the data2 field to 'closed'.
    function finalizeclose() {
        $userrep   = required_param('userrep', PARAM_INT);
        $teamid    = required_param('teamid', PARAM_INT);
        $mode      = required_param('mode', PARAM_ALPHA);
        $offset    = required_param('offset', PARAM_INT);
        $returnurl = "submissions.php?id={$this->cm->id}&amp;teamid=$teamid&amp;userrep=$userrep&amp;mode=$mode&amp;offset=$offset&amp;forcerefresh=1";

        // create but do not add student submission date
        $submission = $this->get_submission($userrep);

        if (!data_submitted()) {
        	error_log('post dat null');
            redirect($returnurl); 
        }

        $members = $this->get_members_from_team($teamid);
        if (is_array($members) && data_submitted('nomatch')) {
        	foreach ($members as $member) {
        		if (($submission = $this->get_submission($member->student)) 
        		    && $this->can_finalize($submission) ) {
        		    $updated = new object();
                    $updated->id    = $submission->id;
                    $updated->data2 = ASSIGNMENT_STATUS_CLOSED;

                    if (update_record('assignment_submissions', $updated)) {
                    	$submission = $this->get_submission($member->student, false, true);
                        $this->update_grade($submission);
                    }
        		}
        	}
        }
        
        redirect($returnurl);
    }
    
    /**
     * Team members should have same files in their  folder.
     * This method will handle the team submission files.
     */
    
	function upload_file($mform, $options) {
        global $CFG, $USER, $DB, $OUTPUT;

        $returnurl  = new moodle_url('/mod/assignment/view.php', array('id'=>$this->cm->id));
        $submission = $this->get_submission($USER->id);
		$teamid = $this->get_user_team($USER->id)->id;
        
        if (!$this->can_upload_file($submission, $teamid)) {
            $this->view_header(get_string('upload'));
            echo $OUTPUT->notification(get_string('uploaderror', 'assignment'));
            echo $OUTPUT->continue_button($returnurl);
            $this->view_footer();
            die;
        }
		
        $members = $this->get_members_from_team($teamid);
        if($members && is_array($members))
        {
        	if ($formdata = $mform->get_data()) {
        		$fs = get_file_storage();
        		$fs->delete_area_files($this->context->id, 'mod_assignment', 'team_submission', $teamid);
        		foreach($members as $member)
        		{
        			$submission = $this->get_submission($member->student, true); //create new submission if needed
        			$formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $this->context, 'mod_assignment', 'team_submission', $teamid);
        			$updates = new stdClass();
        			$updates->id = $submission->id;
        			$updates->timemodified = time();
        			$DB->update_record('assignment_submissions', $updates);
        			add_to_log($this->course->id, 'assignment', 'upload',
                    'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
        			$this->update_grade($submission);
        			if (!$this->drafts_tracked()) {
        				$this->email_teachers($submission);
        			}

        			// send files to event system
        			$files = $fs->get_area_files($this->context->id, 'mod_assignment', 'team_submission', $teamid);
        			// Let Moodle know that assessable files were  uploaded (eg for plagiarism detection)
        			$eventdata = new stdClass();
        			$eventdata->modulename   = 'assignment';
        			$eventdata->cmid         = $this->cm->id;
        			$eventdata->itemid       = $submission->id;
        			$eventdata->courseid     = $this->course->id;
        			$eventdata->userid       = $USER->id;
        			if ($files) {
        				$eventdata->files        = $files;
        			}
        		}
        		events_trigger('assessable_file_uploaded', $eventdata);
        		$returnurl  = new moodle_url('/mod/assignment/view.php', array('id'=>$this->cm->id));
        		redirect($returnurl);
        	}
        }

        $this->view_header(get_string('upload'));
        echo $OUTPUT->notification(get_string('uploaderror', 'assignment'));
        echo $OUTPUT->continue_button($returnurl);
        $this->view_footer();
        die;
    }

    function can_upload_file($submission, $teamid) {
        global $USER;

        if (has_capability('mod/assignment:submit', $this->context)           // can submit
        and $this->isopen()                                                 // assignment not closed yet
        and (empty($submission) or $submission->userid == $USER->id)        // his/her own submission
        and $this->count_team_files($teamid) < $this->assignment->var1    // file limit not reached
        and !$this->is_finalized($submission)                              // no uploading after final submission
        and $this->is_member($teamid)) {
            return true;
        } else {
            return false;
        }
    }

    function can_delete_files($submission, $teamid) {
        global $USER;

        if (has_capability('mod/assignment:grade', $this->context)) {
            return true;
        }
        if (has_capability('mod/assignment:submit', $this->context)
        and $this->isopen()                                      // assignment not closed yet
        and $this->assignment->resubmit                          // deleting allowed
        and $USER->id == $submission->userid                     // his/her own submission
        and !$this->is_finalized($submission)                    // no deleting after final submission
        and $this->is_member($teamid)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the team that the user belongs to. A student can only belong to one
     * team per assignment
     * @param $userid
     * @return team object
     */
    function get_user_team($userid ){
        global $CFG, $DB;
        $teams = $DB->get_records_sql("SELECT id, assignment, name, membershipopen".
                                 " FROM {$CFG->prefix}".TEAM_TABLE.
                                 " WHERE assignment = ".$this->assignment->id);
        if ($teams) {
            foreach($teams as $team) {
                $teamid = $team->id;
                if ($DB->get_record(TEAM_STUDENT_TABLE, array('student'=>$userid, 'team'=>$teamid))) {
                    return $team;
                }
            }
        }
        return null;
    }

    /**
     *return the first member whose id is not same as login user id
     */
    function get_another_user_copy($userid, $teamid) {
        global $CFG;
        $members = $this -> get_members_from_team($teamid);
        if (is_array($members)) {
            foreach($members as $member) {
                if($member->student != $userid) {
                    return $member;
                }
            }
        }
        return null;
    }
    
    /**
     * returns all valid members from this team
     * @param $teamid
     * @return array of users or boolean if not found
     */
    function get_members_from_team ($teamid) {
        global $CFG, $DB;
        $validmembers = array();
        $allmembers = $DB->get_records_sql("SELECT id, student, timemodified".
                                 " FROM {$CFG->prefix}". TEAM_STUDENT_TABLE.
                                 " WHERE team = ".$teamid);
        if ($allmembers) {
            foreach ($allmembers as $member) {
                if ($this->is_user_course_participant($member->student)) {
                    $validmembers[]=$member;
                }
            }
            if (!empty($validmembers)) {
                return $validmembers;
            }
        }
        return false;
    }

    private function get_selection_member_id_string ($members) {
    	
    	$ids = array();
    	foreach($members as $member) {
    		$ids[] = $member->student; 
    	}
    	$selection = implode(",", $ids);
    	return '( '.$selection .' )';
    }
    
    function remove_users_from_team($userids, $teamid, $fullright = false) {
        global $CFG, $DB, $OUTPUT;
        $confirm  = optional_param('confirm', 0, PARAM_BOOL);
        
        if ($confirm == 0) {
            $i = 0;
            $optionsyes = array('confirm'=>1,'teamid'=>$teamid,'members'=>$i, 'removetime'=> time(), 'act_removemember'=>get_string('removeteammember','assignment_team'),'sesskey' =>sesskey());
 
			$team = $DB->get_record(TEAM_TABLE, array('id'=>$teamid, 'assignment'=>$this->assignment->id));
            
            if (!$team) {
                return ;
            }
            $deletemembers = '';
            $count = count($userids);
            foreach ($userids as $userid) {
                $memberkey = 'member'.$i;
                $optionsyes[$memberkey] = $userid ;
                $optionsyes['members'] =$i;
                $user = $DB->get_record('user', array('id'=>$userid));
                if ($i < $count-2)
                	$deletemembers = $deletemembers. '\''.fullname($user).'\', ';
                else if ($i == $count-2)
                	$deletemembers = $deletemembers. '\''.fullname($user).'\' and ';
                else 
                	$deletemembers = $deletemembers. '\''.fullname($user).'\'';
                $i++;
            }
            $message = '';
            if ($team->membershipopen) {
                $message = get_string('removememberwhenmembershipopen', 'assignment_team',$deletemembers);
            } else {
                $message = get_string('removememberwhenmembershipclosed', 'assignment_team',$deletemembers);
            }

            $optionsyes['id'] = $optionsno['id'] = $this->cm->id;
            echo $OUTPUT->heading(get_string('delete'));
            $yes = new single_button(new moodle_url('view.php', $optionsyes), get_string('yes'), 'post');
            $no = new single_button(new moodle_url('view.php',$optionsno), get_string('no'), 'get');
            echo $OUTPUT->confirm($message,$yes,$no);
            echo $OUTPUT->footer('none');
            die;
        }else{
            foreach($userids as $userid) {
            	//error_log('userid: '.$userid);
                $this->remove_user_from_team($userid, $teamid, $fullright);
                //retrieve the id
                echo '	<script type="text/javascript">
						<!--
						window.location = "'.new moodle_url('view.php',array('id'=>$this->cm->id)).'"
						//-->
						</script>';
                //redirect(new moodle_url($_SERVER['REQUEST_URI'], array('id'=>$this->assignment->id)));
            }
        }
    }



    function is_member($teamid) {
        global $USER;
        $team = $this->get_user_team($USER->id);
        return isset($team) && ($team->id == $teamid);
         
    }

    /**
     *
     * @param $name
     *
     */
    function create_team ($name) {
        global $USER, $DB;
        if(!isset($name) || trim($name)== '' ) {
            notify(get_string('teamnameerror', 'assignment_team'));
        } else {
            //if (get_record('team', 'assignment',$this->assignment->id, 'name',$name)) {
			if ($DB->get_record(TEAM_TABLE, array('assignment'=>$this->assignment->id, 'name'=>$name))) {
                notify(get_string('teamnameexist','assignment_team'));
            } else {
                $userteam = $this->get_user_team($USER ->id);
                if (!isset($userteam)) {
                    $team = new object();
                    $team ->assignment = $this->assignment->id;
                    $team ->name = $name;
                    $team ->membershipopen = 1; //1 for team membership open, 0 is for team membership close
                    $team ->timemodified = time();
                    //start create a team and join this team
                    $createTeam = $DB->insert_record(TEAM_TABLE, $team, true) ;
                    //Insert a record into a table and return the "id" field or boolean value
                    if (!$createTeam) {
                        notify(get_string('createteamerror', 'assignment_team'));
                    } else {
                        $this ->join_team($USER->id, $createTeam);
                    }
                }
            }
        }
    }

    
    /**
     *
     * @param $teamid
     *
     */
    function delete_team ($teamid) {
        global $USER, $OUTPUT;
        $confirm = optional_param('confirm_deleteteam',0,PARAM_BOOL);
        $members = $this->get_members_from_team($teamid);
        //only can remove this team if only if there is only this log in student in this team
        if (isset($members)&& is_array($members)&& count($members)== 1) {
        	if(!$confirm) {
        		$yes = new single_button(	new moodle_url(	'view.php',
        									array(	'id'=>$this->cm->id,
        											'confirm_deleteteam'=>1,
        											'act_deleteteam'=>get_string('deleteteam','assignment_team'),
        											'deleteteamtime'=>time(),
        											'teamid'=>$teamid)),
        									        get_string('yes'),
        											'post');
        		$no = new single_button(new moodle_url(	'view.php',
        												array(	'id'=>$this->cm->id)),
        								                        get_string('no'),
        														'get');
        		echo $OUTPUT->heading(get_string('delete'));
        		echo $OUTPUT->confirm(get_string('deleteteamconfirmation','assignment_team'),$yes,$no);
        		$this->view_footer();
        		die;
        	}
        	else {
        		foreach ($members as $member) {
        			if ($member->student == $USER->id) {
        				$this -> remove_user_from_team($USER->id, $teamid);
        			}
            	}
            	//retrieve id in url
            	echo '	<script type="text/javascript">
						<!--
						window.location = "'.new moodle_url('view.php',array('id'=>$this->cm->id)).'"
						//-->
						</script>';
        	}
        }
        else
        {
        	notify(get_string('deleteteamwarning','assignment_team'));
        }
    }

    /**
     * Print a select box with the list of teams
     * @return unknown_type
     */
    function print_team_list(){
        global $CFG, $DB;
        $viewmemberact = optional_param('act_viewmember', NULL, PARAM_ALPHA);
        $groups = optional_param('groups', NULL, PARAM_INT);
        $teams = $DB->get_records_sql("SELECT id, assignment, name, membershipopen".
                                 " FROM {$CFG->prefix}".TEAM_TABLE.
                                 " WHERE assignment = ".$this->assignment->id);
        
        
        
        //datas
        $datas = array('viewmemberact'=>$viewmemberact,
                       'groups'=>$groups, 
                       'teams' =>$teams ,
                        'assignment'=>$this);
        $action = $_SERVER['REQUEST_URI'];
        
   
        echo '<table cellpadding="6"  class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">';
        
        $joinandviewform = new team_list_form($action, $datas);
        $joinandviewform ->display();
        echo '<tr>';
        echo '<td colspan="2" >';
        $action = $_SERVER['REQUEST_URI'];
        $createteamform = new create_team_form($action);
        $createteamform->display();
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        
        
    }


    function join_team ($studentid, $teamid) {
        global $CFG, $DB;
        //if user already in team update otherwise insert
        $team = $this->get_user_team($studentid);
        //insert team_student table
        if (!isset($team)) {
            if ($teamid == null || $teamid == 0) {
                return;
            }
            $insertteam = $DB->get_record(TEAM_TABLE, array('id'=>$teamid, 'assignment'=>$this->assignment->id));
            if (!$insertteam) {
                return;
            }
            if ($insertteam->membershipopen) {

                //if the team already has members and they have submitted, create a mdl_assignment_submissions
                //record for the new member
                $existmember = $this->get_another_user_copy($studentid, $teamid);
                if (isset($existmember)) {
                    //if this existing member already has been graded, other student cannot join this team.
                    $copy = $DB->get_record('assignment_submissions', array('assignment'=>$this->assignment->id, 'userid'=>$existmember->student));
                    if ($copy) {
                        //check the existing member's grade.
                        //If this team already have a grade, a new student cannot join the team.
                        if ($copy->grade >= 0) {
                            notify(get_string('teammarkedwarning', 'assignment_team'));
                            return;
                        }
                        $this -> add_new_team_member($studentid, $teamid);
                        $submission = $this -> prepare_update_submission($studentid, $copy);
                        $DB->update_record('assignment_submissions', $submission);
                    }
                } else {
                    $this -> add_new_team_member($studentid, $teamid);
                    //create a dummy record and update assignment_submission record.
                    $submission = $this->get_submission($studentid, true, true);       
                }
            } else {
                notify(get_string('teamclosedwarning', 'assignment_team'));
            }
        }

    }

    private function add_new_team_member($studentid, $teamid) {
    	global $DB;
        
    	//update team timemodified
        $team = $DB->get_record(TEAM_TABLE, array('id'=>$teamid, 'assignment'=>$this->assignment->id)); 
        if ($team) {
        	$updated = new object();
            $updated->id = $team ->id;
            $updated-> timemodified = time();
            $DB->update_record(TEAM_TABLE, $updated);
        } else {
            error_log('team not exist teamId: '.$teamid);
        }   
        
        $teamstudent = new object();
        $teamstudent ->student = $studentid;
        $teamstudent ->team = $teamid;
        $teamstudent ->timemodified = time();
        $DB->insert_record(TEAM_STUDENT_TABLE, $teamstudent, false);   
    }
    
    function prepare_update_submission($studentid, $copy) {
        $submission = $this->get_submission($studentid, true);
        $submission->assignment   = $copy->assignment;
        $submission->userid       = $studentid;
        $submission->timecreated  = $copy->timecreated;
        $submission->timemodified = time();
        $submission->numfiles     = $copy->numfiles;
        $submission->data1        = $copy->data1;
        $submission->data2        = $copy->data2;
        $submission->grade        = $copy->grade;
        $submission->submissioncomment      = $copy->submissioncomment;
        $submission->format       = $copy->format;
        $submission->teacher      = $copy->teacher;
        $submission->timemarked   = $copy->timemarked;
        $submission->mailed       = $copy->mailed;
        return $submission;
    }

    /**
     * delete this user all files and dir.
     */
    function delete_all_files($dir) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');
        $filepath = $CFG->dataroot.'/'.$dir;
        fulldelete($filepath);
    }

    function delete_submission_file($dir, $file) {
        global $CFG;
        $filepath = $CFG->dataroot.'/'.$dir.'/'.$file;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    function print_error() {
        $returnurl = 'view.php?id='.$this->cm->id;
        print_error('unkownerror', 'assignment_team', $returnurl);       
    }

    /**
     *
     * @param $teamid
     */
    function open_close_team($teamid) {
    	global $DB;
        $team = $DB->get_record(TEAM_TABLE, array('id'=>$teamid, 'assignment'=>$this->assignment->id));
        if ($team && $this ->is_member($teamid)) {
            $status = $team -> membershipopen;
            if ($status) {
                $team -> membershipopen = 0;
            } else {
                $team -> membershipopen = 1;
            }
            $team ->timemodified = time();
            $DB->update_record(TEAM_TABLE, $team);
        }
    }
    
	function close_team($teamid) {
		global $DB;
        $team = $DB->get_record(TEAM_TABLE, array('id'=>$teamid, 'assignment'=>$this->assignment->id));
        if ($team) {
            $team -> membershipopen = 0;
            $team ->timemodified = time();
            $DB->update_record(TEAM_TABLE, $team);
        }
    }

    /**
     * display two links one for individual team members submissions
     * another for team submissions
     * Override the method form base class
     * @param $allgroups
     */
    function submittedlink($allgroups=false) {
        global $USER, $CFG, $DB;
        $linkmessage = '';
        $context = get_context_instance(CONTEXT_MODULE,$this->cm->id);
        if (has_capability('mod/assignment:grade', $context)) {
            $teams = $this->get_teams();
            $teamsubmitted ='';
            $membersubmitted ='';

            if ($teams) {
                if ($teamcount = $this->get_all_team_submissions_number($teams)) {
                	if ($teamcount == 1){
                		$teamsubmitted = '<a href="submissions.php?id='.$this->cm->id.'&amp;mode=team">'.
                        get_string('viewoneteamsubmissionbyteam', 'assignment_team').'</a>';
                        $membersubmitted = '<a href="submissions.php?id='.$this->cm->id.'">'.
                        get_string('viewoneteamsubmissionbyteammembers', 'assignment_team').'</a>';
                	} else {
                        $teamsubmitted = '<a href="submissions.php?id='.$this->cm->id.'&amp;mode=team">'.
                        get_string('viewteamsubmissions', 'assignment_team', $teamcount).'</a>';
                        $membersubmitted = '<a href="submissions.php?id='.$this->cm->id.'">'.
                        get_string('viewmembersubmissions', 'assignment_team', $teamcount).'</a>';
                	}
                    $linkmessage = $teamsubmitted.'<br/>'.$membersubmitted.'<br/>';
                } else {
                    $linkmessage = '<a href="submissions.php?id='.$this->cm->id.'">'.
                    get_string('noattempts', 'assignment').'</a>';
                }
            }

        } else {
            if (!empty($USER->id)) {
                if ($submission = $this->get_submission($USER->id)) {
                    if ($submission->timemodified) {
                        if ($submission->timemodified <= $this->assignment->timedue || empty($this->assignment->timedue)) {
                            $linkmessage = '<span class="early">'.userdate($submission->timemodified).'</span>';
                        } else {
                            $linkmessage = '<span class="late">'.userdate($submission->timemodified).'</span>';
                        }
                    }
                }
            }
        }

        return $linkmessage;
    }

    /**
     * overrid base class method.
     * @param $mode
     */
	function submissions($mode) {
		//Finilize/Unfinalize before anything refer to submission status
		$unfinalize = optional_param('unfinalize', FALSE, PARAM_TEXT);
        $finalize = optional_param('finalize', FALSE, PARAM_TEXT);
        $mode_data = optional_param('mode_data', null, PARAM_TEXT);
		
        if($mode_data == 'team') {
        	if ($unfinalize) {
        		$this->unfinalize('teamsingle');
        	} else if ($finalize) {
        		$this->finalize('teamsingle');
        	}
        }
        else {
        	if ($unfinalize) {
        		$this->unfinalize('single');
        	} else if ($finalize) {
        		$this->finalize('single');
        	}
        }
        if ($unfinalize || $finalize) {
        	$mode = 'singlenosave';
        }

		///The main switch is changed to facilitate
        ///1) Batch fast grading
        ///2) Skip to the next one on the popup
        ///3) Save and Skip to the next one on the popup

        //make user global so we can use the id
        global $USER, $OUTPUT, $DB, $PAGE;

        $mailinfo = optional_param('mailinfo', null, PARAM_BOOL);
        if (optional_param('next', null, PARAM_BOOL)) {
            $mode='next';
        }
        if (optional_param('saveandnext', null, PARAM_BOOL)) {
            $mode='saveandnext';
        }

        if (is_null($mailinfo)) {
            if (optional_param('sesskey', null, PARAM_BOOL)) {
                set_user_preference('assignment_mailinfo', 0);
            } else {
                $mailinfo = get_user_preferences('assignment_mailinfo', 0);
            }
        } else {
            set_user_preference('assignment_mailinfo', 1);
        }

        switch ($mode) {
            case 'grade':                         // We are in a main window grading
                if($mode_data == 'team') {
                	if ($submission = $this->process_team_feedback()) {
            			$this->display_team_submissions(get_string('changessaved'));
            		} else {
            			$this->display_team_submission();
            		}
                }
                else {
            		if ($submission = $this->process_feedback()) {
                	    $this->display_submissions(get_string('changessaved'));
                	} else {
                	    $this->display_submissions();
                	}
                }
                break;

            case 'single':                        // We are in a main window displaying one submission
                if ($submission = $this->process_feedback()) {
                    $this->display_submissions(get_string('changessaved'));
                } else {
                    $this->display_submission();
                }
                break;
                
            case 'team':						//Main for teams
            	$this->display_team_submissions();
            	break;

            case 'teamsingle':					//For grading a single team
            	if ($submission = $this->process_team_feedback()) {
            		$this->display_team_submissions(get_string('changessaved'));
            	} else {
            		$this->display_team_submission();
            	}
            	break;
            	
            case 'all':                          // Main window, display everything
                $this->display_submissions();
                break;

            case 'fastgrade':
                ///do the fast grading stuff  - this process should work for all 3 subclasses
            	if($mode_data == 'team') {
            		$grading    = false;
            		$commenting = false;
            		$col        = false;
            		if (isset($_POST['submissioncomment'])) {
            			$col = 'submissioncomment';
            			$commenting = true;
            		}
            		if (isset($_POST['menu'])) {
            			$col = 'menu';
            			$grading = true;
            		}
            		if (!$col) {
            			//both submissioncomment and grade columns collapsed..
            			$this->display_team_submissions();
            			break;
            		}

            		foreach ($_POST[$col] as $id => $unusedvalue){

            			$id = (int)$id; //clean parameter name

            			$this->process_outcomes($id);
            			$members = $this->get_members_from_team($this->get_user_team($id)->id);

            			foreach($members as $member) {
            				if (!$submission = $this->get_submission($member->student)) {
            					$submission = $this->prepare_new_submission($member->student);
            					$newsubmission = true;
            				} else {
            					$newsubmission = false;
            				}
            				unset($submission->data1);  // Don't need to update this.
            				unset($submission->data2);  // Don't need to update this.

            				//for fast grade, we need to check if any changes take place
            				$updatedb = false;

            				if ($grading) {
            					$grade = $_POST['menu'][$id];
            					$updatedb = $updatedb || ($submission->grade != $grade);
            					$submission->grade = $grade;
            				} else {
            					if (!$newsubmission) {
            						unset($submission->grade);  // Don't need to update this.
            					}
            				}
            				if ($commenting) {
            					$commentvalue = trim($_POST['submissioncomment'][$id]);
            					$updatedb = $updatedb || ($submission->submissioncomment != $commentvalue);
            					$submission->submissioncomment = $commentvalue;
            				} else {
            					unset($submission->submissioncomment);  // Don't need to update this.
            				}

            				$submission->teacher    = $USER->id;
            				if ($updatedb) {
            					$submission->mailed = (int)(!$mailinfo);
            				}

            				$submission->timemarked = time();

            				//if it is not an update, we don't change the last modified time etc.
            				//this will also not write into database if no submissioncomment and grade is entered.

            				if ($updatedb){
            					if ($newsubmission) {
            						if (!isset($submission->submissioncomment)) {
            							$submission->submissioncomment = '';
            						}
            						$sid = $DB->insert_record('assignment_submissions', $submission);
            						$submission->id = $sid;
            					} else {
            						$DB->update_record('assignment_submissions', $submission);
            					}

            					// trigger grade event
            					$this->update_grade($submission);

            					//add to log only if updating
            					add_to_log($this->course->id, 'assignment', 'update grades',
                                   'submissions.php?id='.$this->cm->id.'&user='.$submission->userid,
            					$submission->userid, $this->cm->id);
            				}
            			}

            		}

            		$message = $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');

            		$this->display_team_submissions($message);
            	}
            	else {
            		$grading    = false;
            		$commenting = false;
            		$col        = false;
            		if (isset($_POST['submissioncomment'])) {
            			$col = 'submissioncomment';
            			$commenting = true;
            		}
            		if (isset($_POST['menu'])) {
            			$col = 'menu';
            			$grading = true;
            		}
            		if (!$col) {
            			//both submissioncomment and grade columns collapsed..
            			$this->display_submissions();
            			break;
            		}

            		foreach ($_POST[$col] as $id => $unusedvalue){

            			$id = (int)$id; //clean parameter name

            			$this->process_outcomes($id);

            			if (!$submission = $this->get_submission($id)) {
            				$submission = $this->prepare_new_submission($id);
            				$newsubmission = true;
            			} else {
            				$newsubmission = false;
            			}
            			unset($submission->data1);  // Don't need to update this.
            			unset($submission->data2);  // Don't need to update this.

            			//for fast grade, we need to check if any changes take place
            			$updatedb = false;

            			if ($grading) {
            				$grade = $_POST['menu'][$id];
            				$updatedb = $updatedb || ($submission->grade != $grade);
            				$submission->grade = $grade;
            			} else {
            				if (!$newsubmission) {
            					unset($submission->grade);  // Don't need to update this.
            				}
            			}
            			if ($commenting) {
            				$commentvalue = trim($_POST['submissioncomment'][$id]);
            				$updatedb = $updatedb || ($submission->submissioncomment != $commentvalue);
            				$submission->submissioncomment = $commentvalue;
            			} else {
            				unset($submission->submissioncomment);  // Don't need to update this.
            			}

            			$submission->teacher    = $USER->id;
            			if ($updatedb) {
            				$submission->mailed = (int)(!$mailinfo);
            			}

            			$submission->timemarked = time();

            			//if it is not an update, we don't change the last modified time etc.
            			//this will also not write into database if no submissioncomment and grade is entered.

            			if ($updatedb){
            				if ($newsubmission) {
            					if (!isset($submission->submissioncomment)) {
            						$submission->submissioncomment = '';
            					}
            					$sid = $DB->insert_record('assignment_submissions', $submission);
            					$submission->id = $sid;
            				} else {
            					$DB->update_record('assignment_submissions', $submission);
            				}

            				// trigger grade event
            				$this->update_grade($submission);

            				//add to log only if updating
            				add_to_log($this->course->id, 'assignment', 'update grades',
                                   'submissions.php?id='.$this->cm->id.'&user='.$submission->userid,
            				$submission->userid, $this->cm->id);
            			}

            		}

            		$message = $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');

            		$this->display_submissions($message);
            	}
                break;
			case 'showteam':
                $this->show_team_members();
                break;

            case 'saveandnext':
                ///We are in pop up. save the current one and go to the next one.
                //first we save the current changes
            	if($mode_data == 'team') {
            		if ($submission = $this->process_team_feedback()) {
            		}
            	}
            	else {
            		if ($submission = $this->process_feedback()) {
            		}
                    //print_heading(get_string('changessaved'));
                    //$extra_javascript = $this->update_main_listing($submission);
                }

            case 'next':
                /// We are currently in pop up, but we want to skip to next one without saving.
                ///    This turns out to be similar to a single case
                /// The URL used is for the next submission.
        		if($mode_data == 'team') {
        			$offset = required_param('offset', PARAM_INT);
                	$nextid = required_param('nextid', PARAM_INT);
                	$id = required_param('id', PARAM_INT);
                	$filter = optional_param('filter', self::FILTER_ALL, PARAM_INT);

                	if ($mode == 'next' || $filter !== self::FILTER_REQUIRE_GRADING) {
                		$offset = (int)$offset+1;
                	}
                	$redirect = new moodle_url('submissions.php',
                	array('id' => $id, 'offset' => $offset, 'userid' => $nextid,
                        'mode' => 'teamsingle', 'filter' => $filter));

                	redirect($redirect);
                }
                else {
                	$offset = required_param('offset', PARAM_INT);
                	$nextid = required_param('nextid', PARAM_INT);
                	$id = required_param('id', PARAM_INT);
                	$filter = optional_param('filter', self::FILTER_ALL, PARAM_INT);

                	if ($mode == 'next' || $filter !== self::FILTER_REQUIRE_GRADING) {
                		$offset = (int)$offset+1;
                	}
                	$redirect = new moodle_url('submissions.php',
                	array('id' => $id, 'offset' => $offset, 'userid' => $nextid,
                        'mode' => 'single', 'filter' => $filter));

                	redirect($redirect);
                }
                break;

            case 'singlenosave':
            	if($mode_data == 'team') {
            		$this->display_team_submission();
            	}
            	else {
            		$this->display_submission();	
            	}
                break;

            default:
                echo "something seriously is wrong!!";
                break;
        }
    }
    
	function display_submission($offset=-1,$userid =-1, $display=true) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->libdir.'/tablelib.php');
        require_once("$CFG->dirroot/repository/lib.php");
        if ($userid==-1) {
            $userid = required_param('userid', PARAM_INT);
        }
        if ($offset==-1) {
            $offset = required_param('offset', PARAM_INT);//offset for where to start looking for student.
        }
        $filter = optional_param('filter', 0, PARAM_INT);

        if (!$user = $DB->get_record('user', array('id'=>$userid))) {
            print_error('nousers');
        }

        if (!$submission = $this->get_submission($user->id)) {
            $submission = $this->prepare_new_submission($userid);
        }
        if ($submission->timemodified > $submission->timemarked) {
            $subtype = 'assignmentnew';
        } else {
            $subtype = 'assignmentold';
        }

        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, array($user->id));
        $gradingdisabled = $grading_info->items[0]->grades[$userid]->locked || $grading_info->items[0]->grades[$userid]->overridden;

    /// construct SQL, using current offset to find the data of the next student
        $course     = $this->course;
        $assignment = $this->assignment;
        $cm         = $this->cm;
        $context    = get_context_instance(CONTEXT_MODULE, $cm->id);

        //reset filter to all for offline assignment
        if ($assignment->assignmenttype == 'offline' && $filter == self::FILTER_SUBMITTED) {
            $filter = self::FILTER_ALL;
        }
        /// Get all ppl that can submit assignments

        $currentgroup = groups_get_activity_group($cm);
        $users = get_enrolled_users($context, 'mod/assignment:submit', $currentgroup, 'u.id');
        if ($users) {
            $users = array_keys($users);
            // if groupmembersonly used, remove users who are not in any group
            if (!empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
                if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
                    $users = array_intersect($users, array_keys($groupingusers));
                }
            }
        }

        $nextid = 0;
        $where = '';
        if($filter == self::FILTER_SUBMITTED) {
            $where .= 's.timemodified > 0 AND ';
        } else if($filter == self::FILTER_REQUIRE_GRADING) {
            $where .= 's.timemarked < s.timemodified AND ';
        }

        if ($users) {
            $userfields = user_picture::fields('u', array('lastaccess'));
            $select = "SELECT $userfields,
                              s.id AS submissionid, s.grade, s.submissioncomment,
                              s.timemodified, s.timemarked,
                              CASE WHEN s.timemarked > 0 AND s.timemarked >= s.timemodified THEN 1
                                   ELSE 0 END AS status, t.name AS teamname ";

            $sql = 'FROM ({user} u LEFT JOIN {assignment_submissions} s ON u.id=s.userid AND s.assignment='.$this->assignment->id.') '.
                   'LEFT JOIN ('.$CFG->prefix.TEAM_STUDENT_TABLE.' st LEFT JOIN '.$CFG->prefix.TEAM_TABLE.' t ON st.team = t.id) '.
            	   'ON u.id=st.student AND t.assignment=s.assignment '.
                   'WHERE '.$where.' u.id IN ('.implode(',',$users).') ';

            if ($sort = flexible_table::get_sort_for_table('mod-assignment-submissions')) {
                $sort = 'ORDER BY '.$sort.' ';
            }
            $auser = $DB->get_records_sql($select.$sql.$sort, null, $offset, 2);

            if (is_array($auser) && count($auser)>1) {
                $nextuser = next($auser);
                $nextid = $nextuser->id;
            }
        }

        if ($submission->teacher) {
            $teacher = $DB->get_record('user', array('id'=>$submission->teacher));
        } else {
            global $USER;
            $teacher = $USER;
        }

        $this->preprocess_submission($submission);
        $team = $this->get_user_team($user->id);
        $mode = 'single';

        $mformdata = new stdClass();
        $mformdata->context = $this->context;
        $mformdata->maxbytes = $this->course->maxbytes;
        $mformdata->courseid = $this->course->id;
        $mformdata->teacher = $teacher;
        $mformdata->assignment = $assignment;
        $mformdata->submission = $submission;
        $mformdata->lateness = $this->display_lateness($submission->timemodified);
        $mformdata->auser = $auser;
        $mformdata->user = $user;
        $mformdata->teamid = $team->id;
        $mformdata->offset = $offset;
        $mformdata->userid = $userid;
        $mformdata->cm = $this->cm;
        $mformdata->mode = $mode;
        $mformdata->grading_info = $grading_info;
        $mformdata->enableoutcomes = $CFG->enableoutcomes;
        $mformdata->grade = $this->assignment->grade;
        $mformdata->gradingdisabled = $gradingdisabled;
        $mformdata->nextid = $nextid;
        $mformdata->submissioncomment= $submission->submissioncomment;
        $mformdata->submissioncommentformat= FORMAT_HTML;
        $mformdata->submission_content= $this->print_team_files($user->id,true);
        $mformdata->filter = $filter;
        $mformdata->mailinfo = get_user_preferences('assignment_mailinfo', 0);
         if ($assignment->assignmenttype == 'upload') {
            $mformdata->fileui_options = array('subdirs'=>1, 'maxbytes'=>$assignment->maxbytes, 'maxfiles'=>$assignment->var1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);
        } elseif ($assignment->assignmenttype == 'uploadsingle') {
            $mformdata->fileui_options = array('subdirs'=>0, 'maxbytes'=>$CFG->userquota, 'maxfiles'=>1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);
        } elseif ($assignment->assignmenttype == 'team') {
        	$mformdata->fileui_options = array('subdirs'=>1, 'maxbytes'=>$assignment->maxbytes, 'maxfiles'=>$assignment->var1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);
        }

        $submitform = new mod_assignment_team_grading_form( null, $mformdata );

         if (!$display) {
            $ret_data = new stdClass();
            $ret_data->mform = $submitform;
            $ret_data->fileui_options = $mformdata->fileui_options;
            return $ret_data;
        }

        if ($submitform->is_cancelled()) {
            redirect('submissions.php?id='.$this->cm->id);
        }

        $submitform->set_data($mformdata);

        $PAGE->set_title($this->course->fullname . ': ' .get_string('feedback', 'assignment').' - '.fullname($user, true));
        $PAGE->set_heading($this->course->fullname);
        $PAGE->navbar->add(get_string('submissions', 'assignment'), new moodle_url('/mod/assignment/submissions.php', array('id'=>$cm->id)));
        $PAGE->navbar->add(fullname($user, true));

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('feedback', 'assignment').': '.fullname($user, true));

        // display mform here...
        $submitform->display();

        $customfeedback = $this->custom_feedbackform($submission, true);
        if (!empty($customfeedback)) {
            echo $customfeedback;
        }

        echo $OUTPUT->footer();
    }
    
	function display_team_submission($offset=-1,$userid =-1, $display=true) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->libdir.'/tablelib.php');
        require_once("$CFG->dirroot/repository/lib.php");
        if ($userid==-1) {
            $userid = required_param('userid', PARAM_INT);
        }
        if ($offset==-1) {
            $offset = required_param('offset', PARAM_INT);//offset for where to start looking for student.
        }
        $filter = optional_param('filter', 0, PARAM_INT);

        if (!$user = $DB->get_record('user', array('id'=>$userid))) {
            print_error('nousers');
        }

        if (!$submission = $this->get_submission($user->id)) {
            $submission = $this->prepare_new_submission($userid);
        }
        if ($submission->timemodified > $submission->timemarked) {
            $subtype = 'assignmentnew';
        } else {
            $subtype = 'assignmentold';
        }

        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, array($user->id));
        $gradingdisabled = $grading_info->items[0]->grades[$userid]->locked || $grading_info->items[0]->grades[$userid]->overridden;

    /// construct SQL, using current offset to find the data of the next student
        $course     = $this->course;
        $assignment = $this->assignment;
        $cm         = $this->cm;
        $context    = get_context_instance(CONTEXT_MODULE, $cm->id);

        //reset filter to all for offline assignment
        if ($assignment->assignmenttype == 'offline' && $filter == self::FILTER_SUBMITTED) {
            $filter = self::FILTER_ALL;
        }
        /// Get all ppl that can submit assignments

        $currentgroup = groups_get_activity_group($cm);
        $users = get_enrolled_users($context, 'mod/assignment:submit', $currentgroup, 'u.id');
        if ($users) {
            $users = array_keys($users);
            // if groupmembersonly used, remove users who are not in any group
            if (!empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
                if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
                    $users = array_intersect($users, array_keys($groupingusers));
                }
            }
        }

        $nextid = 0;
        $where = '';
        if($filter == self::FILTER_SUBMITTED) {
            $where .= 's.timemodified > 0 AND ';
        } else if($filter == self::FILTER_REQUIRE_GRADING) {
            $where .= 's.timemarked < s.timemodified AND ';
        }
        if ($users) {
            $userfields = user_picture::fields('u', array('lastaccess'));
           	$select = "SELECT $userfields,
                              s.id AS submissionid, s.grade, s.submissioncomment,
                              s.timemodified, s.timemarked,
                              CASE WHEN s.timemarked > 0 AND s.timemarked >= s.timemodified THEN 1
                                   ELSE 0 END AS status, t.name AS teamname ";

            $sql = 'FROM '.$CFG->prefix.TEAM_TABLE.' t,'.$CFG->prefix.TEAM_STUDENT_TABLE.' st,'.
                   '{user} u,{assignment_submissions} s '.
                   'WHERE '.$where.' u.id IN ('.implode(',',$users).') '.
                   'AND s.assignment='.$this->assignment->id.' AND u.id=s.userid '. 
                   'AND st.team=t.id AND st.student = u.id AND t.assignment = s.assignment ';
            
            if ($sort = flexible_table::get_sort_for_table('mod-assignment-submissions')) {
                $sort = 'ORDER BY '.$sort.' ';
            }

            $auser = $DB->get_records_sql($select.$sql.$sort, null, $offset, 2);
            
            if (is_array($auser) && count($auser)>1) {
                $nextuser = next($auser);
                $nextid = $nextuser->id;
            }
        }

        if ($submission->teacher) {
            $teacher = $DB->get_record('user', array('id'=>$submission->teacher));
        } else {
            global $USER;
            $teacher = $USER;
        }

        $this->preprocess_submission($submission);
        
        $team = $this->get_user_team($userid);
        $teamdummyuser = $user;
        $teamdummyuser->firstname = $team->name;
        $teamdummyuser->lastname = null;
        $mode = 'teamsingle';
		
        $mformdata = new stdClass();
        $mformdata->context = $this->context;
        $mformdata->maxbytes = $this->course->maxbytes;
        $mformdata->courseid = $this->course->id;
        $mformdata->teacher = $teacher;
        $mformdata->assignment = $assignment;
        $mformdata->submission = $submission;
        $mformdata->lateness = $this->display_lateness($submission->timemodified);
        $mformdata->auser = $auser;
        $mformdata->user = $user;
        $mformdata->teamid = $team->id;
        $mformdata->offset = $offset;
        $mformdata->userid = $userid;
        $mformdata->cm = $this->cm;
        $mformdata->mode = $mode;
        $mformdata->grading_info = $grading_info;
        $mformdata->enableoutcomes = $CFG->enableoutcomes;
        $mformdata->grade = $this->assignment->grade;
        $mformdata->gradingdisabled = $gradingdisabled;
        $mformdata->nextid = $nextid;
        $mformdata->submissioncomment= $submission->submissioncomment;
        $mformdata->submissioncommentformat= FORMAT_HTML;
        $mformdata->submission_content= $this->print_team_files($user->id,true);
        $mformdata->filter = $filter;
        $mformdata->mailinfo = get_user_preferences('assignment_mailinfo', 0);
         if ($assignment->assignmenttype == 'upload') {
            $mformdata->fileui_options = array('subdirs'=>1, 'maxbytes'=>$assignment->maxbytes, 'maxfiles'=>$assignment->var1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);
        } elseif ($assignment->assignmenttype == 'uploadsingle') {
            $mformdata->fileui_options = array('subdirs'=>0, 'maxbytes'=>$CFG->userquota, 'maxfiles'=>1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);
        } elseif ($assignment->assignmenttype == 'team') {
        	$mformdata->fileui_options = array('subdirs'=>1, 'maxbytes'=>$assignment->maxbytes, 'maxfiles'=>$assignment->var1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);
        }

        $submitform = new mod_assignment_team_grading_form( null, $mformdata );

         if (!$display) {
            $ret_data = new stdClass();
            $ret_data->mform = $submitform;
            $ret_data->fileui_options = $mformdata->fileui_options;
            return $ret_data;
        }

        if ($submitform->is_cancelled()) {
            redirect('submissions.php?id='.$this->cm->id.'&mode=team');
        }

        $submitform->set_data($mformdata);

        $PAGE->set_title($this->course->fullname . ': ' .get_string('feedback', 'assignment').' - '.fullname($user, true));
        $PAGE->set_heading($this->course->fullname);
        $PAGE->navbar->add(get_string('submissions', 'assignment'), new moodle_url('/mod/assignment/submissions.php', array('id'=>$cm->id)));
        $PAGE->navbar->add(fullname($user, true));

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('feedback', 'assignment').': '.fullname($user, true));

        // display mform here...
        $submitform->display();

        $customfeedback = $this->custom_feedbackform($submission, true);
        if (!empty($customfeedback)) {
            echo $customfeedback;
        }

        echo $OUTPUT->footer();
    }
    
	function process_team_feedback($formdata=null) {
        global $CFG, $USER, $DB;
        require_once($CFG->libdir.'/gradelib.php');
        
        if (!$feedback = data_submitted() or !confirm_sesskey()) {      // No incoming data?
            return false;
        }
        $userid = required_param('userid', PARAM_INT);
        $offset = required_param('offset', PARAM_INT);
        $formdata = $this->display_team_submission($offset, $userid, false);

        ///For save and next, we need to know the userid to save, and the userid to go
        ///We use a new hidden field in the form, and set it to -1. If it's set, we use this
        ///as the userid to store
        
        //Save for all user in the team
        $team = $this->get_user_team($feedback->userid);
        $members = $this->get_members_from_team($team->id);
        
        if ((int)$feedback->saveuserid !== -1){
        	$feedback->userid = $feedback->saveuserid;
        }

        if (!empty($feedback->cancel)) {          // User hit cancel button
        	return false;
        }
        
        foreach($members as $member)
        {
        	$feedback->userid = $member->student;
        	$feedback->saveuserid = $member->student;

        	$grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, $feedback->userid);

        	// store outcomes if needed
        	$this->process_outcomes($feedback->userid);

        	$submission = $this->get_submission($feedback->userid, true);  // Get or make one

        	if (!($grading_info->items[0]->grades[$feedback->userid]->locked ||
        	$grading_info->items[0]->grades[$feedback->userid]->overridden) ) {

        		$submission->grade      = $feedback->xgrade;
        		$submission->submissioncomment    = $feedback->submissioncomment_editor['text'];
        		$submission->teacher    = $USER->id;
        		$mailinfo = get_user_preferences('assignment_mailinfo', 0);
        		if (!$mailinfo) {
        			$submission->mailed = 1;       // treat as already mailed
        		} else {
        			$submission->mailed = 0;       // Make sure mail goes out (again, even)
        		}
        		$submission->timemarked = time();

        		unset($submission->data1);  // Don't need to update this.
        		unset($submission->data2);  // Don't need to update this.

        		if (empty($submission->timemodified)) {   // eg for offline assignments
        			// $submission->timemodified = time();
        		}

        		$DB->update_record('assignment_submissions', $submission);

        		// triger grade event
        		$this->update_grade($submission);

        		add_to_log($this->course->id, 'assignment', 'update grades',
                       'submissions.php?id='.$this->cm->id.'&user='.$feedback->userid, $feedback->userid, $this->cm->id);
        		if (!is_null($formdata)) { 
        			$mformdata = $formdata->mform->get_data();
        			$mformdata = file_postupdate_standard_filemanager($mformdata, 'files', $formdata->fileui_options, $this->context, 'mod_assignment', 'response', $team->id);
        		}
        	}
        }

        return $submission;

    }
   
	function print_team_files($userid=0, $return=false) {
        global $CFG, $USER, $OUTPUT, $PAGE;

        $mode    = optional_param('mode', '', PARAM_ALPHA);
        $offset  = optional_param('offset', 0, PARAM_INT);

        if (!$userid) {
            if (!isloggedin()) {
                return '';
            }
            $userid = $USER->id;
        }
        
        if($teamid = $this->get_user_team($userid)) $teamid = $teamid->id;

        $output = $OUTPUT->box_start('files');

        $submission = $this->get_submission($userid);

        // only during grading
        if ($this->drafts_tracked() and $this->isopen() and !$this->is_finalized($submission) and !empty($mode)) {
            $output .= '<strong>'.get_string('draft', 'assignment').':</strong><br />';
        }

        if ($this->notes_allowed() and !empty($submission->data1) and !empty($mode)) { // only during grading

            $npurl = $CFG->wwwroot."/mod/assignment/type/team/notes.php?id={$this->cm->id}&amp;userid=$userid&amp;offset=$offset&amp;mode=single";
            $output .= '<a href="'.$npurl.'">'.get_string('notes', 'assignment').'</a><br />';

        }

        if ($this->drafts_tracked() and $this->isopen() and has_capability('mod/assignment:grade', $this->context) and $mode != '') { // we do not want it on view.php page
            if ($this->can_unfinalize($submission)) {
                //$options = array ('id'=>$this->cm->id, 'userid'=>$userid, 'action'=>'unfinalize', 'mode'=>$mode, 'offset'=>$offset);
                $output .= '<br /><input type="submit" name="unfinalize" value="'.get_string('unfinalize', 'assignment').'" />';
                $output .=  $OUTPUT->help_icon('unfinalize', 'assignment');

            } else if ($this->can_finalize($submission)) {
                //$options = array ('id'=>$this->cm->id, 'userid'=>$userid, 'action'=>'finalizeclose', 'mode'=>$mode, 'offset'=>$offset);
                $output .= '<br /><input type="submit" name="finalize" value="'.get_string('finalize', 'assignment').'" />';
            }
        }

        if ($submission) {
            $renderer = $PAGE->get_renderer('mod_assignment');
            $output .= $renderer->assignment_files($this->context, $teamid, 'team_submission');
        }
        $output .= $OUTPUT->box_end();

        if ($return) {
            return $output;
        }
        echo $output;
    }
    
	function send_file($filearea, $args) {
        global $CFG, $DB, $USER;
        require_once($CFG->libdir.'/filelib.php');

        require_login($this->course, false, $this->cm);

        if ($filearea === 'team_submission') {
            $teamid = (int)array_shift($args);
            $fmemb = $this->get_first_teammember($teamid);
            $submissionid = $this->get_submission($fmemb->student)->id;

            if (!$submission = $DB->get_record('assignment_submissions', array('assignment'=>$this->assignment->id, 'id'=>$submissionid))) {
                return false;
            }

            if ($USER->id != $submission->userid and !has_capability('mod/assignment:grade', $this->context)) {
                return false;
            }
            
            $relativepath = implode('/', $args);
            $fullpath = "/{$this->context->id}/mod_assignment/team_submission/$teamid/$relativepath";

            $fs = get_file_storage();
            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                return false;
            }
            send_stored_file($file, 0, 0, true); // download MUST be forced - security!

        } else if ($filearea === 'response') {
            $teamid = (int)array_shift($args);
            $fmemb = $this->get_first_teammember($teamid);
            $submissionid = $this->get_submission($fmemb->student)->id;

            if (!$submission = $DB->get_record('assignment_submissions', array('assignment'=>$this->assignment->id, 'id'=>$submissionid))) {
                return false;
            }

            if ($USER->id != $submission->userid and !has_capability('mod/assignment:grade', $this->context)) {
                return false;
            }

            $relativepath = implode('/', $args);
            $fullpath = "/{$this->context->id}/mod_assignment/response/$teamid/$relativepath";

            $fs = get_file_storage();
            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                return false;
            }
            send_stored_file($file, 0, 0, true);
        }

        return false;
    }
    
	function display_submissions($message='') {
        global $CFG, $DB, $USER, $DB, $OUTPUT, $PAGE;
        require_once($CFG->libdir.'/gradelib.php');

        /* first we check to see if the form has just been submitted
         * to request user_preference updates
         */

       $filters = array(self::FILTER_ALL             => get_string('all'),
                        self::FILTER_REQUIRE_GRADING => get_string('requiregrading', 'assignment'));

        $updatepref = optional_param('updatepref', 0, PARAM_BOOL);
        if ($updatepref) {
            $perpage = optional_param('perpage', 10, PARAM_INT);
            $perpage = ($perpage <= 0) ? 10 : $perpage ;
            $filter = optional_param('filter', 0, PARAM_INT);
            set_user_preference('assignment_perpage', $perpage);
            set_user_preference('assignment_quickgrade', optional_param('quickgrade', 0, PARAM_BOOL));
            set_user_preference('assignment_filter', $filter);
        }

        /* next we get perpage and quickgrade (allow quick grade) params
         * from database
         */
        $perpage    = get_user_preferences('assignment_perpage', 10);
        $quickgrade = get_user_preferences('assignment_quickgrade', 0);
        $filter = get_user_preferences('assignment_filter', 0);
        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id);

        if (!empty($CFG->enableoutcomes) and !empty($grading_info->outcomes)) {
            $uses_outcomes = true;
        } else {
            $uses_outcomes = false;
        }

        $page    = optional_param('page', 0, PARAM_INT);
        $strsaveallfeedback = get_string('saveallfeedback', 'assignment');

    /// Some shortcuts to make the code read better

        $course     = $this->course;
        $assignment = $this->assignment;
        $cm         = $this->cm;
        $hassubmission = false;

        // reset filter to all for offline assignment only.
        if ($assignment->assignmenttype == 'offline') {
            if ($filter == self::FILTER_SUBMITTED) {
                $filter = self::FILTER_ALL;
            }
        } else {
            $filters[self::FILTER_SUBMITTED] = get_string('submitted', 'assignment');
        }

        $tabindex = 1; //tabindex for quick grading tabbing; Not working for dropdowns yet
        add_to_log($course->id, 'assignment', 'view submission', 'submissions.php?id='.$this->cm->id, $this->assignment->id, $this->cm->id);

        $PAGE->set_title(format_string($this->assignment->name,true));
        $PAGE->set_heading($this->course->fullname);
        echo $OUTPUT->header();

        echo '<div class="usersubmissions">';

        //hook to allow plagiarism plugins to update status/print links.
        plagiarism_update_status($this->course, $this->cm);

        $course_context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (has_capability('gradereport/grader:view', $course_context) && has_capability('moodle/grade:viewall', $course_context)) {
            echo '<div class="allcoursegrades"><a href="' . $CFG->wwwroot . '/grade/report/grader/index.php?id=' . $course->id . '">'
                . get_string('seeallcoursegrades', 'grades') . '</a></div>';
        }

        if (!empty($message)) {
            echo $message;   // display messages here if any
        }

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    /// Check to see if groups are being used in this assignment

        /// find out current groups mode
        $groupmode = groups_get_activity_groupmode($cm);
        $currentgroup = groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/assignment/submissions.php?id=' . $this->cm->id);

        /// Print quickgrade form around the table
        if ($quickgrade) {
            $formattrs = array();
            $formattrs['action'] = new moodle_url('/mod/assignment/submissions.php');
            $formattrs['id'] = 'fastg';
            $formattrs['method'] = 'post';

            echo html_writer::start_tag('form', $formattrs);
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id',      'value'=> $this->cm->id));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'mode',    'value'=> 'fastgrade'));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'page',    'value'=> $page));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=> sesskey()));
        }

        /// Get all ppl that are allowed to submit assignments
        list($esql, $params) = get_enrolled_sql($context, 'mod/assignment:submit', $currentgroup);

        if ($filter == self::FILTER_ALL) {
            $sql = "SELECT u.id FROM {user} u ".
                   "LEFT JOIN ($esql) eu ON eu.id=u.id ".
                   "WHERE u.deleted = 0 AND eu.id=u.id ";
        } else {
            $wherefilter = ' AND s.assignment = '. $this->assignment->id;
            $assignmentsubmission = "LEFT JOIN {assignment_submissions} s ON (u.id = s.userid) ";
            if($filter == self::FILTER_SUBMITTED) {
                $wherefilter .= ' AND s.timemodified > 0 ';
            } else if($filter == self::FILTER_REQUIRE_GRADING && $assignment->assignmenttype != 'offline') {
                $wherefilter .= ' AND s.timemarked < s.timemodified ';
            } else { // require grading for offline assignment
                $assignmentsubmission = "";
                $wherefilter = "";
            }

            $sql = "SELECT u.id FROM {user} u ".
                   "LEFT JOIN ($esql) eu ON eu.id=u.id ".
                   $assignmentsubmission.
                   "WHERE u.deleted = 0 AND eu.id=u.id ".
                   $wherefilter;
        }

        $users = $DB->get_records_sql($sql, $params);
        if (!empty($users)) {
            if($assignment->assignmenttype == 'offline' && $filter == self::FILTER_REQUIRE_GRADING) {
                //remove users who has submitted their assignment
                foreach ($this->get_submissions() as $submission) {
                    if (array_key_exists($submission->userid, $users)) {
                        unset($users[$submission->userid]);
                    }
                }
            }
            $users = array_keys($users);
        }

        // if groupmembersonly used, remove users who are not in any group
        if ($users and !empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
            if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
                $users = array_intersect($users, array_keys($groupingusers));
            }
        }

        $tablecolumns = array('picture', 'fullname', 'grade', 'teamname', 'submissioncomment', 'timemodified', 'timemarked', 'status', 'finalgrade');
        if ($uses_outcomes) {
            $tablecolumns[] = 'outcome'; // no sorting based on outcomes column
        }

        $tableheaders = array('',
                              get_string('fullnameuser'),
                              get_string('grade'),
                              get_string('team','assignment_team'),
                              get_string('comment', 'assignment'),
                              get_string('lastmodified').' ('.get_string('submission', 'assignment').')',
                              get_string('lastmodified').' ('.get_string('grade').')',
                              get_string('status'),
                              get_string('finalgrade', 'grades'));
        if ($uses_outcomes) {
            $tableheaders[] = get_string('outcome', 'grades');
        }

        require_once($CFG->libdir.'/tablelib.php');
        $table = new flexible_table('mod-assignment-submissions');

        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($CFG->wwwroot.'/mod/assignment/submissions.php?id='.$this->cm->id.'&amp;currentgroup='.$currentgroup);

        $table->sortable(true, 'lastname');//sorted by lastname by default
        $table->collapsible(true);
        $table->initialbars(true);

        $table->column_suppress('picture');
        $table->column_suppress('fullname');

        $table->column_class('picture', 'picture');
        $table->column_class('fullname', 'fullname');
        $table->column_class('grade', 'grade');
        $table->column_class('teamname', 'teamname');
        $table->column_class('submissioncomment', 'comment');
        $table->column_class('timemodified', 'timemodified');
        $table->column_class('timemarked', 'timemarked');
        $table->column_class('status', 'status');
        $table->column_class('finalgrade', 'finalgrade');
        if ($uses_outcomes) {
            $table->column_class('outcome', 'outcome');
        }

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'submissions');
        $table->set_attribute('width', '100%');

        $table->no_sorting('finalgrade');
        $table->no_sorting('outcome');

        // Start working -- this is necessary as soon as the niceties are over
        $table->setup();

        /// Construct the SQL
        list($where, $params) = $table->get_sql_where();
        if ($where) {
            $where .= ' AND ';
        }

        if ($filter == self::FILTER_SUBMITTED) {
           $where .= 's.timemodified > 0 AND ';
        } else if($filter == self::FILTER_REQUIRE_GRADING) {
            $where = '';
            if ($assignment->assignmenttype != 'offline') {
               $where .= 's.timemarked < s.timemodified AND ';
            }
        }

        if ($sort = $table->get_sql_sort()) {
            $sort = ' ORDER BY '.$sort;
        }
        
        $ufields = user_picture::fields('u');
        if (!empty($users)) {
            $select = "SELECT $ufields,
                              s.id AS submissionid, s.grade, s.submissioncomment,
                              s.timemodified, s.timemarked,
                              CASE WHEN s.timemarked > 0 AND s.timemarked >= s.timemodified THEN 1
                                   ELSE 0 END AS status, t.name AS teamname ";

            $sql = 'FROM ({user} u LEFT JOIN {assignment_submissions} s ON u.id=s.userid AND s.assignment='.$this->assignment->id.') '.
                   'LEFT JOIN ('.$CFG->prefix.TEAM_STUDENT_TABLE.' st LEFT JOIN '.$CFG->prefix.TEAM_TABLE.' t ON st.team = t.id) '.
            	   'ON u.id=st.student AND t.assignment=s.assignment '.
                   'WHERE '.$where.' u.id IN ('.implode(',',$users).') ';

            $ausers = $DB->get_records_sql($select.$sql.$sort, $params, $table->get_page_start(), $table->get_page_size());

            $table->pagesize($perpage, count($users));

            ///offset used to calculate index of student in that particular query, needed for the pop up to know who's next
            $offset = $page * $perpage;
            $strupdate = get_string('update');
            $strgrade  = get_string('grade');
            $grademenu = make_grades_menu($this->assignment->grade);

            if ($ausers !== false) {
                $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, array_keys($ausers));
                $endposition = $offset + $perpage;
                $currentposition = 0;
                foreach ($ausers as $auser) {
                    if ($currentposition == $offset && $offset < $endposition) {
                        $final_grade = $grading_info->items[0]->grades[$auser->id];
                        $grademax = $grading_info->items[0]->grademax;
                        $final_grade->formatted_grade = round($final_grade->grade,2) .' / ' . round($grademax,2);
                        $locked_overridden = 'locked';
                        $team = $this->get_user_team($auser->id);
                        if ($final_grade->overridden) {
                            $locked_overridden = 'overridden';
                        }

                        $picture = $OUTPUT->user_picture($auser);

                        if (empty($auser->submissionid)) {
                            $auser->grade = -1; //no submission yet
                        }

                        if (!empty($auser->submissionid)) {
                            $hassubmission = true;
                        ///Prints student answer and student modified date
                        ///attach file or print link to student answer, depending on the type of the assignment.
                        ///Refer to print_student_answer in inherited classes.
                            if ($auser->timemodified > 0 && $team) {
                                $studentmodified = '<div id="ts'.$auser->id.'">'.$this->print_team_answer($team->id)
                                                 . userdate($auser->timemodified).'</div>';
                            } else {
                                $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
                            }
                        ///Print grade, dropdown or text
                            if ($auser->timemarked > 0) {
                                $teachermodified = '<div id="tt'.$auser->id.'">'.userdate($auser->timemarked).'</div>';

                                if ($final_grade->locked or $final_grade->overridden) {
                                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                                } else if ($quickgrade) {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $menu = html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                    $grade = '<div id="g'.$auser->id.'">'. $menu .'</div>';
                                } else {
                                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                                }

                            } else {
                                $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                                if ($final_grade->locked or $final_grade->overridden) {
                                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                                } else if ($quickgrade) {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $menu = html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                    $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
                                } else {
                                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                                }
                            }
                        ///Print Comment
                            if ($final_grade->locked or $final_grade->overridden) {
                                $comment = '<div id="com'.$auser->id.'">'.shorten_text(strip_tags($final_grade->str_feedback),15).'</div>';

                            } else if ($quickgrade) {
                                $comment = '<div id="com'.$auser->id.'">'
                                         . '<textarea tabindex="'.$tabindex++.'" name="submissioncomment['.$auser->id.']" id="submissioncomment'
                                         . $auser->id.'" rows="2" cols="20">'.(shorten_text(strip_tags($auser->submissioncomment))).'</textarea></div>';
                            } else {
                                $comment = '<div id="com'.$auser->id.'">'.shorten_text(strip_tags($auser->submissioncomment),15).'</div>';
                            }
                        } else {
                            $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
                            $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                            $status          = '<div id="st'.$auser->id.'">&nbsp;</div>';

                            if ($final_grade->locked or $final_grade->overridden) {
                                $grade = '<div id="g'.$auser->id.'">'.$final_grade->formatted_grade . '</div>';
                                $hassubmission = true;
                            } else if ($quickgrade) {   // allow editing
                                $attributes = array();
                                $attributes['tabindex'] = $tabindex++;
                                $menu = html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
                                $hassubmission = true;
                            } else {
                                $grade = '<div id="g'.$auser->id.'">-</div>';
                            }

                            if ($final_grade->locked or $final_grade->overridden) {
                                $comment = '<div id="com'.$auser->id.'">'.$final_grade->str_feedback.'</div>';
                            } else if ($quickgrade) {
                                $comment = '<div id="com'.$auser->id.'">'
                                         . '<textarea tabindex="'.$tabindex++.'" name="submissioncomment['.$auser->id.']" id="submissioncomment'
                                         . $auser->id.'" rows="2" cols="20">'.($auser->submissioncomment).'</textarea></div>';
                            } else {
                                $comment = '<div id="com'.$auser->id.'">&nbsp;</div>';
                            }
                        }

                        if (empty($auser->status)) { /// Confirm we have exclusively 0 or 1
                            $auser->status = 0;
                        } else {
                            $auser->status = 1;
                        }

                        $buttontext = ($auser->status == 1) ? $strupdate : $strgrade;

                        ///No more buttons, we use popups ;-).
                        $popup_url = '/mod/assignment/submissions.php?id='.$this->cm->id
                                   . '&amp;userid='.$auser->id.'&amp;mode=single'.'&amp;filter='.$filter.'&amp;offset='.$offset++;

                        $button = $OUTPUT->action_link($popup_url, $buttontext);

                        $status  = '<div id="up'.$auser->id.'" class="s'.$auser->status.'">'.$button.'</div>';

                        $finalgrade = '<span id="finalgrade_'.$auser->id.'">'.$final_grade->str_grade.'</span>';

                        $outcomes = '';

                        if ($uses_outcomes) {

                            foreach($grading_info->outcomes as $n=>$outcome) {
                                $outcomes .= '<div class="outcome"><label>'.$outcome->name.'</label>';
                                $options = make_grades_menu(-$outcome->scaleid);

                                if ($outcome->grades[$auser->id]->locked or !$quickgrade) {
                                    $options[0] = get_string('nooutcome', 'grades');
                                    $outcomes .= ': <span id="outcome_'.$n.'_'.$auser->id.'">'.$options[$outcome->grades[$auser->id]->grade].'</span>';
                                } else {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $attributes['id'] = 'outcome_'.$n.'_'.$auser->id;
                                    $outcomes .= ' '.html_writer::select($options, 'outcome_'.$n.'['.$auser->id.']', $outcome->grades[$auser->id]->grade, array(0=>get_string('nooutcome', 'grades')), $attributes);
                                }
                                $outcomes .= '</div>';
                            }
                        }

                        //team link
                        if($team) $teamlink = $this->get_team_link($team);
                        else $teamlink = null;
                		$row = array($teamlink, $grade,  $comment, $studentmodified, $teachermodified, $status, $finalgrade);

                        $userlink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $auser->id . '&amp;course=' . $course->id . '">' . fullname($auser, has_capability('moodle/site:viewfullnames', $this->context)) . '</a>';
                        $row = array($picture, $userlink, $grade, $teamlink, $comment, $studentmodified, $teachermodified, $status, $finalgrade);
                        if ($uses_outcomes) {
                            $row[] = $outcomes;
                        }
                        $table->add_data($row);
                    }
                    $currentposition++;
                }
                if ($hassubmission && ($this->assignment->assignmenttype=='upload' || $this->assignment->assignmenttype=='online' || $this->assignment->assignmenttype=='uploadsingle')) { //TODO: this is an ugly hack, where is the plugin spirit? (skodak)
                    echo html_writer::start_tag('div', array('class' => 'mod-assignment-download-link'));
                    echo html_writer::link(new moodle_url('/mod/assignment/submissions.php', array('id' => $this->cm->id, 'download' => 'zip')), get_string('downloadall', 'assignment'));
                    echo html_writer::end_tag('div');
                }
                $table->print_html();  /// Print the whole table
            } else {
                if ($filter == self::FILTER_SUBMITTED) {
                    echo html_writer::tag('div', get_string('nosubmisson', 'assignment'), array('class'=>'nosubmisson'));
                } else if ($filter == self::FILTER_REQUIRE_GRADING) {
                    echo html_writer::tag('div', get_string('norequiregrading', 'assignment'), array('class'=>'norequiregrading'));
                }
            }
        }
       
        /// Print quickgrade form around the table
        if ($quickgrade && $table->started_output && !empty($users)){
            $mailinfopref = false;
            if (get_user_preferences('assignment_mailinfo', 1)) {
                $mailinfopref = true;
            }
            $emailnotification =  html_writer::checkbox('mailinfo', 1, $mailinfopref, get_string('enablenotification','assignment'));

            $emailnotification .= $OUTPUT->help_icon('enablenotification', 'assignment');
            echo html_writer::tag('div', $emailnotification, array('class'=>'emailnotification'));

            $savefeedback = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'fastg', 'value'=>get_string('saveallfeedback', 'assignment')));
            echo html_writer::tag('div', $savefeedback, array('class'=>'fastgbutton'));

            echo html_writer::end_tag('form');
        } else if ($quickgrade) {
            echo html_writer::end_tag('form');
        }

        echo '</div>';
        /// End of fast grading form

        /// Mini form for setting user preference

        $formaction = new moodle_url('/mod/assignment/submissions.php', array('id'=>$this->cm->id));
        $mform = new MoodleQuickForm('optionspref', 'post', $formaction, '', array('class'=>'optionspref'));

        $mform->addElement('hidden', 'updatepref');
        $mform->setDefault('updatepref', 1);
        $mform->addElement('header', 'qgprefs', get_string('optionalsettings', 'assignment'));
        $mform->addElement('select', 'filter', get_string('show'),  $filters);

        $mform->setDefault('filter', $filter);

        $mform->addElement('text', 'perpage', get_string('pagesize', 'assignment'), array('size'=>1));
        $mform->setDefault('perpage', $perpage);

        $mform->addElement('checkbox', 'quickgrade', get_string('quickgrade','assignment'));
        $mform->setDefault('quickgrade', $quickgrade);
        $mform->addHelpButton('quickgrade', 'quickgrade', 'assignment');

        $mform->addElement('submit', 'savepreferences', get_string('savepreferences'));

        $mform->display();

        echo $OUTPUT->footer();
    }

	function display_team_submissions($message='') {
        global $CFG, $DB, $USER, $DB, $OUTPUT, $PAGE;
        require_once($CFG->libdir.'/gradelib.php');

        /* first we check to see if the form has just been submitted
         * to request user_preference updates
         */
        
		$mode = 'team';
        
       	$filters = array(self::FILTER_ALL             => get_string('all'),
                        self::FILTER_REQUIRE_GRADING => get_string('requiregrading', 'assignment'));

        $updatepref = optional_param('updatepref', 0, PARAM_BOOL);
        if ($updatepref) {
            $perpage = optional_param('perpage', 10, PARAM_INT);
            $perpage = ($perpage <= 0) ? 10 : $perpage ;
            $filter = optional_param('filter', 0, PARAM_INT);
            set_user_preference('assignment_perpage', $perpage);
            set_user_preference('assignment_quickgrade', optional_param('quickgrade', 0, PARAM_BOOL));
            set_user_preference('assignment_filter', $filter);
        }

        /* next we get perpage and quickgrade (allow quick grade) params
         * from database
         */
        $perpage    = get_user_preferences('assignment_perpage', 10);
        $quickgrade = get_user_preferences('assignment_quickgrade', 0);
        $filter = get_user_preferences('assignment_filter', 0);
        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id);

        if (!empty($CFG->enableoutcomes) and !empty($grading_info->outcomes)) {
            $uses_outcomes = true;
        } else {
            $uses_outcomes = false;
        }

        $page    = optional_param('page', 0, PARAM_INT);
        $strsaveallfeedback = get_string('saveallfeedback', 'assignment');

    /// Some shortcuts to make the code read better

        $course     = $this->course;
        $assignment = $this->assignment;
        $cm         = $this->cm;
        $hassubmission = false;

        // reset filter to all for offline assignment only.
        if ($assignment->assignmenttype == 'offline') {
            if ($filter == self::FILTER_SUBMITTED) {
                $filter = self::FILTER_ALL;
            }
        } else {
            $filters[self::FILTER_SUBMITTED] = get_string('submitted', 'assignment');
        }

        $tabindex = 1; //tabindex for quick grading tabbing; Not working for dropdowns yet
        add_to_log($course->id, 'assignment', 'view submission', 'submissions.php?id='.$this->cm->id, $this->assignment->id, $this->cm->id);

        $PAGE->set_title(format_string($this->assignment->name,true));
        $PAGE->set_heading($this->course->fullname);
        echo $OUTPUT->header();

        echo '<div class="usersubmissions">';

        //hook to allow plagiarism plugins to update status/print links.
        plagiarism_update_status($this->course, $this->cm);

        $course_context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (has_capability('gradereport/grader:view', $course_context) && has_capability('moodle/grade:viewall', $course_context)) {
            echo '<div class="allcoursegrades"><a href="' . $CFG->wwwroot . '/grade/report/grader/index.php?id=' . $course->id . '">'
                . get_string('seeallcoursegrades', 'grades') . '</a></div>';
        }

        if (!empty($message)) {
            echo $message;   // display messages here if any
        }

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    /// Check to see if groups are being used in this assignment

        /// find out current groups mode
        $groupmode = groups_get_activity_groupmode($cm);
        $currentgroup = groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/assignment/submissions.php?id=' . $this->cm->id);

        /// Print quickgrade form around the table
        if ($quickgrade) {
            $formattrs = array();
            $formattrs['action'] = new moodle_url('/mod/assignment/submissions.php');
            $formattrs['id'] = 'fastg';
            $formattrs['method'] = 'post';

            echo html_writer::start_tag('form', $formattrs);
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id',      'value'=> $this->cm->id));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'mode',    'value'=> 'fastgrade'));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'page',    'value'=> $page));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=> sesskey()));
        	echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'mode_data', 'value'=>$mode));
        }

        /// Get all ppl that are allowed to submit assignments
        list($esql, $params) = get_enrolled_sql($context, 'mod/assignment:submit', $currentgroup);

        if ($filter == self::FILTER_ALL) {
            $sql = "SELECT u.id FROM {user} u ".
                   "LEFT JOIN ($esql) eu ON eu.id=u.id ".
                   "WHERE u.deleted = 0 AND eu.id=u.id ";
        } else {
            $wherefilter = ' AND s.assignment = '. $this->assignment->id;
            $assignmentsubmission = "LEFT JOIN {assignment_submissions} s ON (u.id = s.userid) ";
            if($filter == self::FILTER_SUBMITTED) {
                $wherefilter .= ' AND s.timemodified > 0 ';
            } else if($filter == self::FILTER_REQUIRE_GRADING && $assignment->assignmenttype != 'offline') {
                $wherefilter .= ' AND s.timemarked < s.timemodified ';
            } else { // require grading for offline assignment
                $assignmentsubmission = "";
                $wherefilter = "";
            }

            $sql = "SELECT u.id FROM {user} u ".
                   "LEFT JOIN ($esql) eu ON eu.id=u.id ".
                   $assignmentsubmission.
                   "WHERE u.deleted = 0 AND eu.id=u.id ".
                   $wherefilter;
        }

        $users = $DB->get_records_sql($sql, $params);
        if (!empty($users)) {
            if($assignment->assignmenttype == 'offline' && $filter == self::FILTER_REQUIRE_GRADING) {
                //remove users who has submitted their assignment
                foreach ($this->get_submissions() as $submission) {
                    if (array_key_exists($submission->userid, $users)) {
                        unset($users[$submission->userid]);
                    }
                }
            }
            $users = array_keys($users);
        }

        // if groupmembersonly used, remove users who are not in any group
        if ($users and !empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
            if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
                $users = array_intersect($users, array_keys($groupingusers));
            }
        }

        $tablecolumns = array(/*'picture', */'teamname', 'grade', 'submissioncomment', 'timemodified', 'timemarked', 'status', 'finalgrade');
        if ($uses_outcomes) {
            $tablecolumns[] = 'outcome'; // no sorting based on outcomes column
        }

        $tableheaders = array(get_string('team', 'assignment_team'),
                              get_string('grade'),
                              get_string('comment', 'assignment'),
                              get_string('lastmodified').' ('.get_string('submission', 'assignment').')',
                              get_string('lastmodified').' ('.get_string('grade').')',
                              get_string('status'),
                              get_string('finalgrade', 'grades'));
        if ($uses_outcomes) {
            $tableheaders[] = get_string('outcome', 'grades');
        }

        require_once($CFG->libdir.'/tablelib.php');
        $table = new flexible_table('mod-assignment-submissions');

        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($CFG->wwwroot.'/mod/assignment/submissions.php?id='.$this->cm->id.'&amp;currentgroup='.$currentgroup.'&mode=team');

        $table->sortable(true, 'teamname');//sorted by lastname by default
        $table->collapsible(true);
        $table->initialbars(true);

        //$table->column_suppress('picture');
        $table->column_suppress('teamname');

        //$table->column_class('picture', 'picture');
        $table->column_class('teamname', 'teamname');
        $table->column_class('grade', 'grade');
        $table->column_class('submissioncomment', 'comment');
        $table->column_class('timemodified', 'timemodified');
        $table->column_class('timemarked', 'timemarked');
        $table->column_class('status', 'status');
        $table->column_class('finalgrade', 'finalgrade');
        if ($uses_outcomes) {
            $table->column_class('outcome', 'outcome');
        }

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'submissions');
        $table->set_attribute('width', '100%');

        $table->no_sorting('finalgrade');
        $table->no_sorting('outcome');

        // Start working -- this is necessary as soon as the niceties are over
        $table->setup();

        /// Construct the SQL
        list($where, $params) = $table->get_sql_where();
        if ($where) {
            $where .= ' AND ';
        }

        if ($filter == self::FILTER_SUBMITTED) {
           $where .= 's.timemodified > 0 AND ';
        } else if($filter == self::FILTER_REQUIRE_GRADING) {
            $where = '';
            if ($assignment->assignmenttype != 'offline') {
               $where .= 's.timemarked < s.timemodified AND ';
            }
        }

        if ($sort = $table->get_sql_sort()) {
            $sort = ' ORDER BY '.$sort;
        }
        
		$teamusers = $this->get_team_users();
        if (count($teamusers)==0) {
            print_heading(get_string('nosubmitusers','assignment'));
            return true;
        } else {
            $users = array();
            foreach ($teamusers as $key =>$value) {
                $users[] = $value;
            }
        }

        $ufields = user_picture::fields('u');
        if (!empty($users)) {
            $select = "SELECT $ufields,
                              s.id AS submissionid, s.grade, s.submissioncomment,
                              s.timemodified, s.timemarked,
                              CASE WHEN s.timemarked > 0 AND s.timemarked >= s.timemodified THEN 1
                                   ELSE 0 END AS status, t.name AS teamname ";

            $sql = 'FROM '.$CFG->prefix.TEAM_TABLE.' t,'.$CFG->prefix.TEAM_STUDENT_TABLE.' st,'.
                   '{user} u,{assignment_submissions} s '.
                   'WHERE '.$where.' u.id IN ('.implode(',',$users).') '.
                   'AND s.assignment='.$this->assignment->id.' AND u.id=s.userid '. 
                   'AND st.team=t.id AND st.student = u.id AND t.assignment = s.assignment ';

            $ausers = $DB->get_records_sql($select.$sql.$sort, $params, $table->get_page_start(), $table->get_page_size());

            $table->pagesize($perpage, count($users));

            ///offset used to calculate index of student in that particular query, needed for the pop up to know who's next
            $offset = $page * $perpage;
            $strupdate = get_string('update');
            $strgrade  = get_string('grade');
            $grademenu = make_grades_menu($this->assignment->grade);

            if ($ausers !== false) {
                $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, array_keys($ausers));
                $endposition = $offset + $perpage;
                $currentposition = 0;
                foreach ($ausers as $auser) {
					if ($currentposition == $offset && $offset < $endposition) {
                        $final_grade = $grading_info->items[0]->grades[$auser->id];
                        $grademax = $grading_info->items[0]->grademax;
                        $final_grade->formatted_grade = round($final_grade->grade,2) .' / ' . round($grademax,2);
                        $locked_overridden = 'locked';
                        $team = $this->get_user_team($auser->id);
                        if ($final_grade->overridden) {
                            $locked_overridden = 'overridden';
                        }

                        $picture = $OUTPUT->user_picture($auser);

                        if (empty($auser->submissionid)) {
                            $auser->grade = -1; //no submission yet
                        }

                        if (!empty($auser->submissionid)) {
                            $hassubmission = true;
                        ///Prints student answer and student modified date
                        ///attach file or print link to student answer, depending on the type of the assignment.
                        ///Refer to print_student_answer in inherited classes.
                            if ($auser->timemodified > 0) {
                                $studentmodified = '<div id="ts'.$auser->id.'">'.$this->print_team_answer($team->id)
                                                 . userdate($auser->timemodified).'</div>';
                            } else {
                                $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
                            }
                        ///Print grade, dropdown or text
                            if ($auser->timemarked > 0) {
                                $teachermodified = '<div id="tt'.$auser->id.'">'.userdate($auser->timemarked).'</div>';

                                if ($final_grade->locked or $final_grade->overridden) {
                                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                                } else if ($quickgrade) {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $menu = html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                    $grade = '<div id="g'.$auser->id.'">'. $menu .'</div>';
                                } else {
                                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                                }

                            } else {
                                $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                                if ($final_grade->locked or $final_grade->overridden) {
                                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                                } else if ($quickgrade) {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $menu = html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                    $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
                                } else {
                                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                                }
                            }
                        ///Print Comment
                            if ($final_grade->locked or $final_grade->overridden) {
                                $comment = '<div id="com'.$auser->id.'">'.shorten_text(strip_tags($final_grade->str_feedback),15).'</div>';

                            } else if ($quickgrade) {
                                $comment = '<div id="com'.$auser->id.'">'
                                         . '<textarea tabindex="'.$tabindex++.'" name="submissioncomment['.$auser->id.']" id="submissioncomment'
                                         . $auser->id.'" rows="2" cols="20">'.shorten_text(strip_tags($auser->submissioncomment),15).'</textarea></div>';
                            } else {
                                $comment = '<div id="com'.$auser->id.'">'.shorten_text(strip_tags($auser->submissioncomment),15).'</div>';
                            }
                        } else {
                            $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
                            $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                            $status          = '<div id="st'.$auser->id.'">&nbsp;</div>';

                            if ($final_grade->locked or $final_grade->overridden) {
                                $grade = '<div id="g'.$auser->id.'">'.$final_grade->formatted_grade . '</div>';
                                $hassubmission = true;
                            } else if ($quickgrade) {   // allow editing
                                $attributes = array();
                                $attributes['tabindex'] = $tabindex++;
                                $menu = html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
                                $hassubmission = true;
                            } else {
                                $grade = '<div id="g'.$auser->id.'">-</div>';
                            }

                            if ($final_grade->locked or $final_grade->overridden) {
                                $comment = '<div id="com'.$auser->id.'">'.$final_grade->str_feedback.'</div>';
                            } else if ($quickgrade) {
                                $comment = '<div id="com'.$auser->id.'">'
                                         . '<textarea tabindex="'.$tabindex++.'" name="submissioncomment['.$auser->id.']" id="submissioncomment'
                                         . $auser->id.'" rows="2" cols="20">'.($auser->submissioncomment).'</textarea></div>';
                            } else {
                                $comment = '<div id="com'.$auser->id.'">&nbsp;</div>';
                            }
                        }

                        if (empty($auser->status)) { /// Confirm we have exclusively 0 or 1
                            $auser->status = 0;
                        } else {
                            $auser->status = 1;
                        }

                        $buttontext = ($auser->status == 1) ? $strupdate : $strgrade;

                        ///No more buttons, we use popups ;-).
                        $popup_url = '/mod/assignment/submissions.php?id='.$this->cm->id
                                   . '&amp;userid='.$auser->id.'&amp;mode=teamsingle'.'&amp;filter='.$filter.'&amp;offset='.$offset++;

                        $button = $OUTPUT->action_link($popup_url, $buttontext);

                        $status  = '<div id="up'.$auser->id.'" class="s'.$auser->status.'">'.$button.'</div>';

                        $finalgrade = '<span id="finalgrade_'.$auser->id.'">'.$final_grade->str_grade.'</span>';

                        $outcomes = '';

                        if ($uses_outcomes) {

                            foreach($grading_info->outcomes as $n=>$outcome) {
                                $outcomes .= '<div class="outcome"><label>'.$outcome->name.'</label>';
                                $options = make_grades_menu(-$outcome->scaleid);

                                if ($outcome->grades[$auser->id]->locked or !$quickgrade) {
                                    $options[0] = get_string('nooutcome', 'grades');
                                    $outcomes .= ': <span id="outcome_'.$n.'_'.$auser->id.'">'.$options[$outcome->grades[$auser->id]->grade].'</span>';
                                } else {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $attributes['id'] = 'outcome_'.$n.'_'.$auser->id;
                                    $outcomes .= ' '.html_writer::select($options, 'outcome_'.$n.'['.$auser->id.']', $outcome->grades[$auser->id]->grade, array(0=>get_string('nooutcome', 'grades')), $attributes);
                                }
                                $outcomes .= '</div>';
                            }
                        }
						//team link
                		$teamlink = $this->get_team_link($team);
                		$row = array($teamlink, $grade,  $comment, $studentmodified, $teachermodified, $status, $finalgrade);

                        //$userlink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $auser->id . '&amp;course=' . $course->id . '">' . fullname($auser, has_capability('moodle/site:viewfullnames', $this->context)) . '</a>';
                        //$row = array($picture, $userlink, $grade, $comment, $studentmodified, $teachermodified, $status, $finalgrade);
                        if ($uses_outcomes) {
                            $row[] = $outcomes;
                        }
                        $table->add_data($row);
                    }
                    $currentposition++;
                }
                if ($hassubmission && ($this->assignment->assignmenttype=='upload' || $this->assignment->assignmenttype=='online' || $this->assignment->assignmenttype=='uploadsingle')) { //TODO: this is an ugly hack, where is the plugin spirit? (skodak)
                    echo html_writer::start_tag('div', array('class' => 'mod-assignment-download-link'));
                    echo html_writer::link(new moodle_url('/mod/assignment/submissions.php', array('id' => $this->cm->id, 'download' => 'zip')), get_string('downloadall', 'assignment'));
                    echo html_writer::end_tag('div');
                }
                $table->print_html();  /// Print the whole table
            } else {
                if ($filter == self::FILTER_SUBMITTED) {
                    echo html_writer::tag('div', get_string('nosubmisson', 'assignment'), array('class'=>'nosubmisson'));
                } else if ($filter == self::FILTER_REQUIRE_GRADING) {
                    echo html_writer::tag('div', get_string('norequiregrading', 'assignment'), array('class'=>'norequiregrading'));
                }
            }
        }
		
        /// Print quickgrade form around the table
        if ($quickgrade && $table->started_output && !empty($users)){
            $mailinfopref = false;
            if (get_user_preferences('assignment_mailinfo', 1)) {
                $mailinfopref = true;
            }
            $emailnotification =  html_writer::checkbox('mailinfo', 1, $mailinfopref, get_string('enablenotification','assignment'));

            $emailnotification .= $OUTPUT->help_icon('enablenotification', 'assignment');
            echo html_writer::tag('div', $emailnotification, array('class'=>'emailnotification'));

            $savefeedback = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'fastg', 'value'=>get_string('saveallfeedback', 'assignment')));
            echo html_writer::tag('div', $savefeedback, array('class'=>'fastgbutton'));
            
            echo html_writer::end_tag('form');
        } else if ($quickgrade) {
            echo html_writer::end_tag('form');
        }

        echo '</div>';
        /// End of fast grading form

        /// Mini form for setting user preference

        $formaction = new moodle_url('/mod/assignment/submissions.php', array('id'=>$this->cm->id));
        $mform = new MoodleQuickForm('optionspref', 'post', $formaction, '', array('class'=>'optionspref'));

        $mform->addElement('hidden', 'updatepref');
        $mform->setDefault('updatepref', 1);
        $mform->addElement('header', 'qgprefs', get_string('optionalsettings', 'assignment'));
        $mform->addElement('select', 'filter', get_string('show'),  $filters);
        $mform->addElement('hidden','mode',$mode);

        $mform->setDefault('filter', $filter);

        $mform->addElement('text', 'perpage', get_string('pagesize', 'assignment'), array('size'=>1));
        $mform->setDefault('perpage', $perpage);

        $mform->addElement('checkbox', 'quickgrade', get_string('quickgrade','assignment'));
        $mform->setDefault('quickgrade', $quickgrade);
        $mform->addHelpButton('quickgrade', 'quickgrade', 'assignment');

        $mform->addElement('submit', 'savepreferences', get_string('savepreferences'));

        $mform->display();

        echo $OUTPUT->footer();
    }

    function show_team_members() {
    	global $DB, $OUTPUT;
    	
        $teamid = required_param('teamid', PARAM_INT);
        $team = $DB->get_record(TEAM_TABLE, array('id'=>$teamid,'assignment'=>$this->assignment->id));
        $members = $this->get_members_from_team($teamid);
        print_header ($team->name);
        if ($team && $members) {
            echo '<table cellspacing="0"  >';
            ///Start of teacher info row
            echo '<tr>';
            echo '<th>&nbsp;</th>';
            echo '<th>'.$team->name.'</th>';
            echo '</tr>';
            foreach ($members as $member) {
                if ($user = $DB->get_record('user', array('id'=>$member->student))) {
                    echo '<tr>';
                    echo '<td class="topic">';
                    echo $OUTPUT->user_picture($user, array('courseid'=>$this->course->id, 'picture'=>$user->picture));
                    echo '</td>';
                    echo '<td class="topic">';
                    echo fullname($user);
                    echo '</td></tr>';
                }
            }
            echo '</table>';
        } else {
            echo get_string('teamchangedwarning', 'assignment_team');
        }
        $OUTPUT->footer('none');
    }

    /**
     *
     * @param $userid
     * @param $teamid
     * @return removed object
     */
    private function remove_user_from_team($userid , $teamid, $fullright = false) {
        global $USER, $DB;

        $submission = $this->get_submission($userid, false);
        //error_log('user id: '.$userid);
        //capability check only if team member can remove a user from a team
        if ($this->is_member($teamid) || $fullright) {
        	if ($submission && $submission->grade >= 0) {
                notify(get_string('teammarkedwarning', 'assignment_team'));
                return ;
            }
            
        	$select = ' student = '.$userid.' and '.' team = '.$teamid;
            if (!$DB->delete_records_select(TEAM_STUDENT_TABLE, $select)){
            	//error_log('print error 1');
                $this->print_error();

            }
            
            //if team members in this team  are empty, delete this team
            $members = $this->get_members_from_team($teamid);
            $team = $DB->get_record(TEAM_TABLE, array('id'=>$teamid, 'assignment'=>$this->assignment->id));
            if ($team && !$members) {
                $updated = new object();
                $updated->id              = $team->id;
                $updated->assignment      = 0;
                $updated->timemodified    = time();
                if ($DB->update_record(TEAM_TABLE, $updated)) {
                    $fs = get_file_storage();
        			$fs->delete_area_files($this->context->id, 'mod_assignment', 'team_submission', $team->id);
                }              
            }
            if ($submission) {
                $dummysubmission = $this->prepare_dummy_submission($submission);
                if (!$DB->update_record('assignment_submissions', $dummysubmission)) {
                	//error_log('print error 3');
                    $this -> print_error();
                }
            }
            //remove this student's assignment files
         	$submission->timemodified = time();
         	$DB->delete_records_select('assignment_submissions', 'id ='.$submission->id.' AND userid = '.$userid.' AND assignment = '.$this->assignment->id);
         	add_to_log($this->course->id, 'assignment', 'upload', //TODO: add delete action to log
            	        'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
         	$this->update_grade($submission);
        	//}

            //double check whether or not this team existing, update team record if this team exist
            $team = $DB->get_record(TEAM_TABLE , array('id'=>$teamid,'assignment'=>$this->assignment->id));
            if ($team) {
                $updated = new object();
                $updated->id            = $team->id;
                $updated ->timemodified = time();
                $DB->update_record(TEAM_TABLE, $updated);
            }
        } else {
        	//error_log('print error 4');
            $this->print_error();
        }
    }
    
    private function prepare_dummy_submission($submission) {
        //Students leave the team, we update the this time. 
        $submission->timemodified = time();
        $submission->numfiles     = 0;
        $submission->data1        = '';
        $submission->data2        = '';
        $submission->grade        = -1;
        $submission->submissioncomment      = '';
        $submission->format       = 0;
        $submission->teacher      = 0;
        $submission->timemarked   = 0;
        $submission->mailed       = 0;
        return $submission;
    } 
    

    private function get_teams() {
        global $CFG, $DB;
        $validteams = array();    
        $allteams = $DB->get_records_sql("SELECT id, assignment, name, membershipopen".
                                 " FROM {$CFG->prefix}".TEAM_TABLE.
                                 " WHERE assignment = ".$this->assignment->id);
        if ($allteams && is_array($allteams)) {
            foreach ($allteams as $team) {
                if ($this->has_members($team->id)) {
                    $validteams[] = $team;
                }
            }
            if (!empty($validteams)) {
                return $validteams;
            }
        }
        return false;
    }

    public function get_team_status_name($status) {
        if ($status) {
            return  get_string('teamopen', 'assignment_team');
        } else {
            return  get_string('teamclosed', 'assignment_team');
        }
    }

    private function get_all_team_submissions_number($teams) {
        global $CFG;
        $count = 0;
        foreach ($teams as $team) {
            //$member = get_record(TEAM_STUDENT_TABLE, 'team', $team->id);
            $members = $this ->get_members_from_team($team->id);
            if($members && count($members)>0) {
                if ($this->is_team_submitted($members)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    private function is_team_submitted($members) {
        foreach ($members as $member) {
            $membersubmission = $this ->get_submission($member->student);
            if ($membersubmission) {
                if ($membersubmission->data2 == 'submitted' || $membersubmission->data2 == 'closed') {
                    return true;
                }
            }
        }
        return false;
    }

    private function get_team_users() {
        //create teamusers to represent a team.
        //When a maker mark this a teamuser ,all the members in this team are updated.
        $teamuser = array();
        $teams = $this-> get_teams();
        if($teams) {
            foreach($teams as $team) {
                $teamstudent = $this->get_first_teammember($team->id);
                if (!isset($teamuser[$team->id])) {
                    $teamuser[$team->id] = $teamstudent->student;
                }
            }
        }     
        return $teamuser;
    }

    /**
     * get first match team member
     * @param unknown_type $teamid
     * return first team member or false
     */
    private function get_first_teammember($teamid) {
        global $CFG;
        $members = $this -> get_members_from_team ($teamid);
        if ($members && count($members)) {
            foreach ($members as $member) {
                return $member;
            }
        }
        return false;
    }

    private function is_grades_diff($teamid) {
        $teammembers = $this->get_members_from_team($teamid);
        $i = 0;
        $flag = true;
        $prev = null;
        foreach ($teammembers as $member) {
            if ($submission = $this->get_submission($member->student)) {
                if ($i == 0) {
                    $prev = $submission;
                }
                if ($prev->grade != $submission->grade) {
                    return true;
                }
                $i++;
                $prev = $submission;
            }else {
                // TODO add logic
            }
        }
        return false;
    }

    private function process_team_grades() {
        global $CFG, $USER, $DB;
        require_once($CFG->libdir.'/gradelib.php');

        if (!$feedback = data_submitted()) {      // No incoming data?
            return false;
        }

        if (!empty($feedback->cancel)) {          // User hit cancel button
            return false;
        }

        $teamid = $feedback->teamid;
        $members = $this->get_members_from_team($teamid);
        foreach ($members as $member) {
            $userid = $member->student;
            if($DB->get_record('user', array('id'=>$userid))) {
                $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, $userid);

                // store outcomes if needed
                $this->process_outcomes($userid);

                $submission = $this->get_submission($userid, true);  // Get or make one

                if (!$grading_info->items[0]->grades[$userid]->locked and
                !$grading_info->items[0]->grades[$userid]->overridden) {

                    $submission->grade      = $feedback->grade;
                    $submission->submissioncomment    = $feedback->submissioncomment;
                    $submission->format     = $feedback->format;
                    $submission->teacher    = $USER->id;
                    $mailinfo = get_user_preferences('assignment_mailinfo', 0);
                    if (!$mailinfo) {
                        $submission->mailed = 1;       // treat as already mailed
                    } else {
                        $submission->mailed = 0;       // Make sure mail goes out (again, even)
                    }
                    $submission->timemarked = time();

                    unset($submission->data1);  // Don't need to update this.
                    unset($submission->data2);  // Don't need to update this.

                    if (empty($submission->timemodified)) {   // eg for offline assignments
                        // $submission->timemodified = time();
                    }

                    if (! $DB->update_record('assignment_submissions', $submission)) {
                        return false;
                    }

                    // triger grade event
                    $this->update_grade($submission);

                    add_to_log($this->course->id, 'assignment', 'update grades',
                       'submissions.php?id='.$this->assignment->id.'&user='.$userid, $userid, $this->cm->id);
                }
            }
        }
        return true;
    }

    /**
     * helper class to update parent page view after updating the team marking
     *
     * @param $submission
     */
    private function update_team_main_listing($submission) {
        global $SESSION, $CFG;

        $output = '';

        $perpage = 10;

        /// Run some Javascript to try and update the parent page
        $output .= '<script type="text/javascript">'."\n<!--\n";
        if (empty($SESSION->flextable['mod-assignment-submissions']->collapse['submissioncomment'])) {
            $output.= 'opener.document.getElementById("com'.$submission->userid.
                '").innerHTML="'.shorten_text(trim(strip_tags($submission->submissioncomment)), 15)."\";\n";

        }

        if (empty($SESSION->flextable['mod-assignment-submissions']->collapse['grade'])) {
            $output.= 'opener.document.getElementById("g'.$submission->userid.'").innerHTML="'.
            $this->display_grade($submission->grade)."\";\n";

        }
        //need to add student's assignments in there too.
        if (empty($SESSION->flextable['mod-assignment-submissions']->collapse['timemodified']) &&
        $submission->timemodified) {
            $output.= 'opener.document.getElementById("ts'.$submission->userid.
                 '").innerHTML="'.addslashes_js($this->print_student_answer($submission->userid)).userdate($submission->timemodified)."\";\n";
        }

        if (empty($SESSION->flextable['mod-assignment-submissions']->collapse['timemarked']) &&
        $submission->timemarked) {
            $output.= 'opener.document.getElementById("tt'.$submission->userid.
                 '").innerHTML="'.userdate($submission->timemarked)."\";\n";
        }

        //modified the popup_url link parameters.
        if (empty($SESSION->flextable['mod-assignment-submissions']->collapse['status'])) {
            $output.= 'opener.document.getElementById("up'.$submission->userid.'").className="s1";';
            $buttontext = get_string('update');
            $team = $this -> get_user_team($submission->userid);
            $popup_url = '/mod/assignment/submissions.php?id='.$this->cm->id
            . '&amp;teamid='.$team->id.'&amp;userrep='.$submission->userid.'&amp;mode=single';
            $button = link_to_popup_window ($popup_url, '', $buttontext, 600, 780,
            $buttontext, 'none', true, 'button'.$submission->userid);
            $output.= 'opener.document.getElementById("up'.$submission->userid.'").innerHTML="'.addslashes_js($button).'";';
        }

        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, $submission->userid);

        if (!empty($CFG->enableoutcomes) and empty($SESSION->flextable['mod-assignment-submissions']->collapse['outcome'])) {

            if (!empty($grading_info->outcomes)) {
                foreach($grading_info->outcomes as $n=>$outcome) {
                    if ($outcome->grades[$submission->userid]->locked) {
                        continue;
                    }
                    $options = make_grades_menu(-$outcome->scaleid);
                    $options[0] = get_string('nooutcome', 'grades');
                    $output.= 'opener.document.getElementById("outcome_'.$n.'_'.$submission->userid.'").innerHTML="'.$options[$outcome->grades[$submission->userid]->grade]."\";\n";
                     
                }
            }
        }

        $output .= "\n-->\n</script>";
        return $output;
    }

    private function get_team_link($team) {
    	global $OUTPUT;
    	
        $teambuttontext = $team->name;
        $teampopup_url = '/mod/assignment/submissions.php?id='.$this->cm->id
        . '&amp;teamid='.$team->id.'&amp;mode=showteam';
        return $OUTPUT->action_link($teampopup_url, 
        							$teambuttontext, 
        							new component_action($teambuttontext, 
        												 array(600, 780, 
        												 	   $teambuttontext, 
        												 	   'none', true)));
        //link_to_popup_window ($teampopup_url, $teambuttontext, $teambuttontext, 600, 780,
        //$teambuttontext, 'none', true);
    }
    
    private function is_user_course_participant($userid) {
        global $CFG, $DB;
        $studentrole = 5;
        $context = get_context_instance(CONTEXT_COURSE, $this->assignment->course);
        $contextid = $context->id;
        $sql =  "SELECT u.id FROM {user} u INNER JOIN ".
               "{role_assignments} ra on u.id=ra.userid ".
               "WHERE u.id = {$userid} ".
               "AND ra.contextid = {$contextid} ".
               "AND ra.roleid = {$studentrole}";
        if ($DB->get_records_sql($sql)) {
            return true;
        }
        return false;
    
   }
   
   public function has_members($teamid) {
   		global $DB;
       if ($DB->get_record(TEAM_TABLE, array('id'=>$teamid, 'assignment'=>$this->assignment->id))) {
           $members = $this->get_members_from_team ($teamid);
           if($members && is_array($members)) {
               foreach ($members as $member) {
                   if ($this->is_user_course_participant($member->student)) {
                       return true;
                   }
               }
           }        
       }
       return false;
   }
   
   /*
    * find the tiifile object, which has the same name as the given filename
    * return null or tiifile object
    */
   private function get_tii_file($filename, $tiifiles) {
       if (!empty($tiifiles)) {
	       foreach ($tiifiles as $tiifile) {
		       if ($tiifile->filename == $filename) {
				   return $tiifile;
			   }
		   }
	   }
	   return null;
	   
   }
   
   /*
    * get all tiifiles, which belong to the given team
    * return null or array of tiifiles.
    */
   private function get_team_tiifiles($teamid) {
   	   global $CFG, $DB;
       $members = $this -> get_members_from_team ($teamid);
       if ($members) {
           $memberselection = $this -> get_selection_member_id_string($members);
           $tiifiles = $DB->get_records_sql("SELECT id, course, module, instance, userid,
                                   filename, tii, tiicode, tiiscore".
                                   " FROM {tii_files}".
                                   " WHERE instance = ".$this->assignment->id.
                                   " and userid in ".$memberselection );
           return $tiifiles;
       } else {
           return null;
       }
  }
}

class team_list_form extends moodleform {
	
	function definition() {
		global $USER, $CFG, $DB, $OUTPUT;
        $mform =& $this->_form;
        $datas = $this->_customdata;
        $viewmemberact = $datas['viewmemberact'];
        $groups = $datas['groups'];
        $teams = $datas['teams'];
        //$strteams = get_string('teams');
        $assignment = $datas['assignment'];
        
        //add elements
        $mform ->addElement('html','<td>');
        $select = &$mform ->addElement('select', 'existingteams', get_string('existingteams', 'assignment_team'));
        $mform->addHelpButton('existingteams', 'existingteams', 'assignment_team');
        $select->setName('groups');
        $select->setMultiple(false);
        $select->setSize(15);
        if ($teams) {
        	foreach($teams as $team) {
        		if (!$assignment->has_members($team->id)) {
        			continue;
        		}
        		
                //$usercount = (int)$DB->count_records(TEAM_STUDENT_TABLE, 'team', $team->id);
                $usercount = (int)$DB->count_records(TEAM_STUDENT_TABLE, array('team'=>$team->id));
                $teamname = format_string($team->name).' ('.$usercount.')'.' '.$assignment ->get_team_status_name($team->membershipopen);
                $value = $team->id;
        	
                //after any post action from act_viewmember button still can selected previous select
                if (isset($viewmemberact)
                && isset($groups)
                && $groups == $team->id ) {
                    $select ->addOption($teamname, $value );
                    $select ->setSelected($value);
                } else {
                	$select ->addOption($teamname, $value);
                }
               
            }
        }  
       
        $mform ->addElement('html', '<input type="hidden" name="jointeamtime" value="'.time().'" />');
        $mform ->addElement('submit', 'act_jointeam', get_string('jointeam', 'assignment_team'));
        $mform ->addElement('html',$OUTPUT->help_icon('jointeam','assignment_team'));
        $mform ->addElement('html','</td>');
        
        //add team member view list box
        $mform ->addElement('html','<td>');
        $selection = &$mform ->addElement('select', 'teammember', get_string('teammember', 'assignment_team'));
		$mform->addHelpButton('teammember', 'teammember', 'assignment_team');
        $selection->setMultiple(false);
        $selection->setSize(15);
	    if (isset($viewmemberact) && isset($groups)) {
            $teamid = $groups;
            $members = $assignment->get_members_from_team($teamid);
            if (is_array($members) && count($members)>0) {
                foreach ($members as $member) {
                    $userid = $member->student;
                    $user = $DB->get_record ('user', array('id'=>$userid));
                    $selection->addOption(fullname($user),fullname($user));
                }
            }
             
        } 
        $mform ->addElement('submit', 'act_viewmember',get_string('viewmember', 'assignment_team'));
        $mform ->addElement('html',$OUTPUT->help_icon('viewmember','assignment_team'));
        $mform ->addElement('html','</td>');  
    }
    
}

class team_management_form extends moodleform {
	
	function definition() {
		global $USER, $CFG, $DB, $OUTPUT;
        $mform =& $this->_form;
        $datas = $this->_customdata;
        $members = $datas['members'];
        $team = $datas['team'];
        $open = $datas['openstatus'];
        
        $mform ->addElement('html','<td>');
        $mform ->addElement('html','<div style="float:right;position:relative;right:50%;">');
        $mform ->addElement('html','<div style="float:right;position:relative;right:-50%;>');
        $select = &$mform ->addElement('select','teammember', get_string('teammember', 'assignment_team'));
		$mform->addHelpButton('teammember', 'teammember', 'assignment_team');
        $select -> setName('members');
        $select->setMultiple(true);
        $select->setSize(15);
        if (is_array($members) && count($members)>0) {
            foreach ($members as $member) {
                $userid = $member->student;
                //error_log('member id'.$userid);
                $user = $DB->get_record ('user', array('id'=>$userid));
                $name = fullname($user);
                $value = $user->id;
                //error_log('userid: '.$value);
                $select ->addOption($name, $value);
            }
        }
        $mform->addElement('html','</div>'); 
        $mform->addElement('html','</div>'); 
        $mform->addElement('html','</td>');
        $mform->addElement('html','</tr>');
        
        $mform->addElement('html','<tr>');
        $mform->addElement('html','<td>');
        $mform ->addElement('html','<div style="float:right;position:relative;right:50%;">');
        $mform ->addElement('html','<div style="float:right;position:relative;right:-50%;">');
        $mform ->addElement('hidden', 'teamid', $team->id);
        //Beware that moodle disables hidden elements, therefore following is undesired
        //$mform ->addElement('hidden', 'removetime', time());
        //$mform ->addElement('hidden', 'openclosetime', time());
        //$mform ->addElement('hidden', 'deleteteamtime', time());
        $mform ->addElement('html', '<input type="hidden" name="removetime" value="'.time().'" />');
        $mform ->addElement('html', '<input type="hidden" name="openclosetime" value="'.time().'" />');
        $mform ->addElement('html', '<input type="hidden" name="deleteteamtime" value="'.time().'" />');
        $buttonarray = array(); 
        if($open)
        {
        	$buttonarray[] = &$mform->createElement('submit', 'act_opencloseteam', get_string('closemembership','assignment_team'));
        }
        else
        {
        	$buttonarray[] = &$mform->createElement('submit', 'act_opencloseteam', get_string('openmembership','assignment_team'));
        }
        $buttonarray[] = &$mform->createElement('submit', 'act_removemember', get_string('removeteammember','assignment_team'));
        $buttonarray[] = &$mform->createElement('submit', 'act_deleteteam', get_string('deleteteam','assignment_team'));
        $mform->addGroup($buttonarray, '', array(' '), false);
        //$mform->addHelpButton('teammanager', 'teammanager', 'assignment_team');
        $mform->addElement('html',$OUTPUT->help_icon('teammanager','assignment_team')); 
        $mform->addElement('html','</div>'); 
        $mform->addElement('html','</div>'); 
        $mform->addElement('html','</td>');
	}
}

class create_team_form extends moodleform {
	
	function definition() {
		global $USER, $CFG;
        $mform =& $this->_form;
        
        $mform ->addElement('html', '<p>'.get_string('createteamlabel','assignment_team').'</p>');
        $mform ->addElement('html', '<input type="hidden" name="createteamtime" value="'.time().'" />');
        $mform->addElement('text', 'teamname', get_string('teamname','assignment_team'));
        $mform->setType('teamname', PARAM_TEXT);  
        $mform->addHelpButton('teamname', 'teamname_create', 'assignment_team');
        $mform ->addElement('submit', 'act_createteam', get_string('createteam','assignment_team'));       
	}
}

/* The only difference between the original function mod_assignment_grade_form is I have added
 * a hidden filed called mode data, so that it does not redirect to single or grade.
 */
class mod_assignment_team_grading_form extends mod_assignment_grading_form {
	 
	function definition() {
		global $OUTPUT;
		$mform =& $this->_form;

		$formattr = $mform->getAttributes();
		$formattr['id'] = 'submitform';
		$mform->setAttributes($formattr);
		// hidden params
		$mform->addElement('hidden', 'offset', ($this->_customdata->offset+1));
		$mform->setType('offset', PARAM_INT);
		$mform->addElement('hidden', 'userid', $this->_customdata->userid);
		$mform->setType('userid', PARAM_INT);
		$mform->addElement('hidden', 'nextid', $this->_customdata->nextid);
		$mform->setType('nextid', PARAM_INT);
		$mform->addElement('hidden', 'id', $this->_customdata->cm->id);
		$mform->setType('id', PARAM_INT);
		$mform->addElement('hidden', 'sesskey', sesskey());
		$mform->setType('sesskey', PARAM_ALPHANUM);
		$mform->addElement('hidden', 'mode', 'grade');
		$mform->setType('mode', PARAM_TEXT);
		if($this->_customdata->mode == 'teamsingle')
		{
			$mform->addElement('hidden', 'mode_data', 'team');//Modified here
			$mform->setType('mode_data', PARAM_TEXT);
		}
		$mform->addElement('hidden', 'menuindex', "0");
		$mform->setType('menuindex', PARAM_INT);
		$mform->addElement('hidden', 'saveuserid', "-1");
		$mform->setType('saveuserid', PARAM_INT);
		$mform->addElement('hidden', 'filter', "0");
		$mform->setType('filter', PARAM_INT);

		$mform->addElement('static', 'picture', $OUTPUT->user_picture($this->_customdata->user),
		fullname($this->_customdata->user, true) . '<br/>' .
		userdate($this->_customdata->submission->timemodified) .
		$this->_customdata->lateness );

		$this->add_submission_content();
		$this->add_grades_section();

		$this->add_feedback_section();

		if ($this->_customdata->submission->timemarked) {
			$datestring = userdate($this->_customdata->submission->timemarked)."&nbsp; (".format_time(time() - $this->_customdata->submission->timemarked).")";
			$mform->addElement('header', 'Last Grade', get_string('lastgrade', 'assignment'));
			$mform->addElement('static', 'picture', $OUTPUT->user_picture($this->_customdata->teacher) ,
			fullname($this->_customdata->teacher,true).
                                                    '<br/>'.$datestring);
		}
		// buttons
		$this->add_action_buttons();
	}
	 
	function add_feedback_section() {
		global $OUTPUT;
		$mform =& $this->_form;
		$mform->addElement('header', 'Feed Back', get_string('feedback', 'grades'));

		if ($this->_customdata->gradingdisabled) {
			$mform->addElement('static', 'disabledfeedback', $this->_customdata->grading_info->items[0]->grades[$this->_customdata->userid]->str_feedback );
		} else {
			// visible elements

			$mform->addElement('editor', 'submissioncomment_editor', get_string('feedback', 'assignment').':', null, $this->get_editor_options() );
			$mform->setType('submissioncomment_editor', PARAM_RAW); // to be cleaned before display
			$mform->setDefault('submissioncomment_editor', $this->_customdata->submission->submissioncomment);
			//$mform->addRule('submissioncomment', get_string('required'), 'required', null, 'client');
			switch ($this->_customdata->assignment->assignmenttype) {
				case 'team':
				case 'upload' :
				case 'uploadsingle' :
					$mform->addElement('filemanager', 'files_filemanager', get_string('responsefiles', 'assignment'). ':', null, $this->_customdata->fileui_options);
					break;
				default :
					break;
			}
			$mform->addElement('hidden', 'mailinfo_h', "0");
			$mform->setType('mailinfo_h', PARAM_INT);
			$mform->addElement('checkbox', 'mailinfo',get_string('enablenotification','assignment').
			$OUTPUT->help_icon('enablenotification', 'assignment') .':' );
			$mform->setType('mailinfo', PARAM_INT);
		}
	}

	public function set_data($data) {
		$editoroptions = $this->get_editor_options();
		if (!isset($data->text)) {
			$data->text = '';
		}
		if (!isset($data->format)) {
			$data->textformat = FORMAT_HTML;
		} else {
			$data->textformat = $data->format;
		}

		switch ($this->_customdata->assignment->assignmenttype) {
			case 'team' :
			case 'upload' :
			case 'uploadsingle' :
				$data = file_prepare_standard_filemanager($data, 'files', $editoroptions, $this->_customdata->context, 'mod_assignment', 'response', $this->_customdata->teamid);
				break;
			default :
				break;
		}

		$data = file_prepare_standard_editor($data, 'submissioncomment', $editoroptions, $this->_customdata->context, $editoroptions['component'], $editoroptions['filearea'], $this->_customdata->teamid);
		return parent::set_data($data);
	}

	public function get_data() {
		$data = parent::get_data();

		if ($data) {
			$editoroptions = $this->get_editor_options();
			switch ($this->_customdata->assignment->assignmenttype) {
				case 'team' :
				case 'upload' :
				case 'uploadsingle' :
					$data = file_postupdate_standard_filemanager($data, 'files', $editoroptions, $this->_customdata->context, 'mod_assignment', 'response', $this->_customdata->teamid);
					break;
				default :
					break;
			}
			$data = file_postupdate_standard_editor($data, 'submissioncomment', $editoroptions, $this->_customdata->context, $editoroptions['component'], $editoroptions['filearea'], $this->_customdata->teamid);
		}
		return $data;
	}
}

?>
