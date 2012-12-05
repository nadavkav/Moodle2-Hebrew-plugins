<?php 
/*
//Simplehtml block
//Created By: Swati Sra
//Created on: 19 Dec. 2011
*/

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');


class block_activity_track extends block_base 
{
	//DISPLAY BLOCK HEADING OR TITLE
	public function init()
	{
		$this->title= get_string('pluginname','block_activity_track');
	}
	
	
	
	
	function print_course($course, $highlightterms = '') {
    global $CFG, $USER, $DB, $OUTPUT;
	
    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    // Rewrite file URLs so that they are correct
    $course->summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', NULL);

    echo html_writer::start_tag('div', array('class'=>'coursebox clearfix'));
    echo html_writer::start_tag('div', array('class'=>'info'));
    echo html_writer::start_tag('h3', array('class'=>'name'));

    $linkhref = new moodle_url('/course/view.php', array('id'=>$course->id));
	
    $linktext = highlight($highlightterms, format_string($course->fullname));
    $linkparams = array('title'=>get_string('entercourse'));
    if (empty($course->visible)) {
        $linkparams['class'] = 'dimmed';
    }
    echo html_writer::link($linkhref, $linktext, $linkparams);
    echo html_writer::end_tag('h3');

    /// first find all roles that are supposed to be displayed
    if (!empty($CFG->coursecontact)) {
        $managerroles = explode(',', $CFG->coursecontact);
        $namesarray = array();
        if (isset($course->managers)) {
            if (count($course->managers)) {
                $rusers = $course->managers;
                $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);

                 /// Rename some of the role names if needed
                if (isset($context)) {
                    $aliasnames = $DB->get_records('role_names', array('contextid'=>$context->id), '', 'roleid,contextid,name');
                }

                // keep a note of users displayed to eliminate duplicates
                $usersshown = array();
                foreach ($rusers as $ra) {

                    // if we've already displayed user don't again
                    if (in_array($ra->user->id,$usersshown)) {
                        continue;
                    }
                    $usersshown[] = $ra->user->id;

                    $fullname = fullname($ra->user, $canviewfullnames);

                    if (isset($aliasnames[$ra->roleid])) {
                        $ra->rolename = $aliasnames[$ra->roleid]->name;
                    }

                    $namesarray[] = format_string($ra->rolename).': '.
                                    html_writer::link(new moodle_url('/user/view.php', array('id'=>$ra->user->id, 'course'=>SITEID)), $fullname);
                }
            }
        } else {
            $rusers = get_role_users($managerroles, $context,
                                     true, '', 'r.sortorder ASC, u.lastname ASC');
            if (is_array($rusers) && count($rusers)) {
                $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);

                /// Rename some of the role names if needed
                if (isset($context)) {
                    $aliasnames = $DB->get_records('role_names', array('contextid'=>$context->id), '', 'roleid,contextid,name');
                }

                foreach ($rusers as $teacher) {
                    $fullname = fullname($teacher, $canviewfullnames);

                    /// Apply role names
                    if (isset($aliasnames[$teacher->roleid])) {
                        $teacher->rolename = $aliasnames[$teacher->roleid]->name;
                    }

                    $namesarray[] = format_string($teacher->rolename).': '.
                                    html_writer::link(new moodle_url('/user/view.php', array('id'=>$teacher->id, 'course'=>SITEID)), $fullname);
                }
            }
        }

        if (!empty($namesarray)) {
         //   echo html_writer::start_tag('ul', array('class'=>'teachers'));
            foreach ($namesarray as $name) {
            //    echo html_writer::tag('li', $name);
            }
         //   echo html_writer::end_tag('ul');
        }
    }
    echo html_writer::end_tag('div'); // End of info div

    echo html_writer::start_tag('div', array('class'=>'summary'));
    $options = NULL;
    $options->noclean = true;
    $options->para = false;
    $options->overflowdiv = true;
    if (!isset($course->summaryformat)) {
        $course->summaryformat = FORMAT_MOODLE;
    }
    echo highlight($highlightterms, format_text($course->summary, $course->summaryformat, $options,  $course->id));
    if ($icons = enrol_get_course_info_icons($course)) {
      //  echo html_writer::start_tag('div', array('class'=>'enrolmenticons'));
        foreach ($icons as $icon) {
       //     echo $OUTPUT->render($icon);
        }
     //   echo html_writer::end_tag('div'); // End of enrolmenticons div
    }
   // echo html_writer::end_tag('div'); // End of summary div
  //  echo html_writer::end_tag('div'); // End of coursebox div
  
}

	
	//DISPLAY 
	public function get_content()
	{
		 global $USER, $CFG, $DB, $COURSE;
			
		if($this->content !== null)
		{
			return $this->content;
			
		}
		
		$this->content = new stdClass;
		
				
		 $courses1  = enrol_get_my_courses('', 'visible DESC,sortorder ASC'); //defined in lib/enrollib.php file	
		
        // Display completion status       
      	$this->content->text .=  '<table cellpadding="0" cellspacing="0" width="178" border="0">';
				$this->content->text .= '<tr>'; 
				$this->content->text .= '<td width="85" align="right"><b>Total</b>';
				$this->content->text .= '</td><td align="left"><b>Over</b>';
				$this->content->text .= '</td><td align="left"><b>%</b>';
				$this->content->text .= '</td></tr></table>';
		$this->content->text .=  '<table cellpadding="0" cellspacing="0" width="178" border="0">';
		
		foreach ($courses1 as $course1) 
		{

				if ($course1->id == SITEID) {continue; };
				
				$userid = optional_param('user', 0, PARAM_INT);
				// Load course
				$course = $DB->get_record('course', array('id' =>$course1->id));
				
				// Load user
				if ($userid){ $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST); }else {$user = $USER; }
				
				
				
				
			
			
			
			
				$coursecontext   = get_context_instance(CONTEXT_COURSE, $course->id);
				$personalcontext = get_context_instance(CONTEXT_USER, $user->id);
	
				$can_view = false;
	
				// Can view own report
				if ($USER->id == $user->id) {
					$can_view = true;
				} else if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
					$can_view = true;
				} else if (has_capability('coursereport/completion:view', $coursecontext)) {
					$can_view = true;
				} else if (has_capability('coursereport/completion:view', $personalcontext)) {
					$can_view = true;
				}

				if (!$can_view) 
				{
					$this->content->text .=print_error('cannotviewreport');
				}
				
				
				
				
				
				
				
				
				
				
				// Load completion data
				$info = new completion_info($course);
				
				// Load criteria to display
				$completions = $info->get_completions($user->id);
				
				// Check this user is enroled
				if (!$info->is_tracked_user($user->id)) 
				{
					if ($USER->id == $user->id) {
						$this->content->text .=print_error('notenroled', 'completion', $returnurl);
					} else {
						$this->content->text .=print_error('usernotenroled', 'completion', $returnurl);
					}
				}
				
				// Is course complete?
				$coursecomplete = $info->is_course_complete($user->id);
		
				// Has this user completed any criteria?
				$criteriacomplete = $info->count_course_user_data($user->id);
				
				if ($coursecomplete) {
					$status= get_string('complete');
				} else if (!$criteriacomplete) {
					$status= '<i>'.get_string('notyetstarted', 'completion').'</i>';
				} else {
					$status= '<i>'.get_string('inprogress','completion').'</i>';
				}
				
				
				
				//GET TOTAL NUMBER OF TASKS ASSIGNED TO CURRENT USER
				$conditions = array("course" => $course->id);
				$tot_rec=$DB->count_records('course_completion_criteria',$conditions);
				
				
				 //GET TOTAL NUMBER OF TASKS ASSIGNED TO CURRENT USER
				$result = $DB->get_records_sql('SELECT mc.coursemoduleid,mc.userid,mc.completionstate,
				m.id,m.course FROM `mdl_course_modules_completion` mc 
				LEFT JOIN 
				mdl_course_modules m
				ON
				mc.coursemoduleid=m.id WHERE mc.userid = ? AND m.course = ?', array( $user->id ,$course->id ));
				$tot_comp=count($result);
				
				
				$count1 = $tot_comp / $tot_rec;
				$count2 = $count1 * 100;
				$count = number_format($count2, 0);
				$linkhref = new moodle_url('/course/view.php', array('id'=>$course->id));
				
                
				
				
				$this->content->text .='<tr><td colspan="4"><b title="Course" style="color:#000066;">';
				$this->content->text .='<a href="'.$linkhref.'">'.$course1->fullname.'</a>';
				$this->content->text .='</b></td></tr>';
				
				
				$this->content->text .= '<tr><td align="left" width="70" title="Activity completion status">'; 
				//$this->content->text .=$course1->fullname;
				$this->content->text .=$status;
				$this->content->text .= '</td><td align="left" width="27" title="Total activities">'. $tot_rec;
				$this->content->text .= '</td><td width="27" align="left" title="Total Activities completed">'.$tot_comp;
				$this->content->text .= '</td><td width="26" align="right" title="Activities completed(%)">'.$count.'%';
				$this->content->text .= '</td></tr>';
				
				
		
		
		}
		$this->content->text .= '</table>';
	
		//$this->content->footer .= 'Footer here...';
	
  return $this->content;
        
}
	
}//main class close	
?>