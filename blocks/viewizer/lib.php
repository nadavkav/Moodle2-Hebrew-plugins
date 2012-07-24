<?php
// This file is part of Viewizer block for Moodle - http://moodle.org/
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
 * Viewizer library functions
 *
 * @package   block
 * @subpackage Viewizer
 * @copyright 2012 Tõnis Tartes <t6nis20@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Overview */
function viewizer_print_overview($courses, array $remote_courses=array(), $total_courses, $courses_limit, $currpage) {
    
    global $CFG, $USER, $DB, $OUTPUT;

    $viewizer = 0;
    
    if ($total_courses > $courses_limit) {
        $viewizer = 1;
    }
    
    //If there are any selected courses, print only that information
    if ($viewizer == 1 || !empty($USER->profile['viewizerimportantcourses'])) {
        viewizer_print_important();
    } 
    
    //If there are any selected courses or the user course limit hasnt been reached, show all information foreach course
    if ($viewizer == 0 || $viewizer == 1 && empty($USER->profile['viewizerimportantcourses']) || isset($USER->editing) && $USER->editing == 1) {
       
        $course_list = '';
        
        if ($viewizer == 1) {
            $course_list = viewizer_print_courses_list($courses, $remote_courses, $total_courses, $courses_limit, $currpage, $viewizer);            
        } else {
            $course_list = viewizer_print_courses_details($courses, $remote_courses, $total_courses, $courses_limit, $currpage, $viewizer);
        }
        
        //everything in a nut shell
        echo html_writer::tag('div', $course_list, array('class' => 'viewizercourses'));
    }
    
}

function viewizer_print_courses_list($courses, $remote_courses, $total_courses, $courses_limit, $currpage, $viewizer) {
        
    global $CFG, $USER, $DB, $OUTPUT;

    $course_list = '';

    /* Pagination */
    $pager = ceil($total_courses / $courses_limit);

    if ($pager > 1) {
        echo '<div id="coursesbypage">';
        if ($currpage > 0) {
            echo '<a href="javascript:void(0);" name="page" value="'.($currpage-1).'" class="page"><</a> ';
        }
        for ($o = 0; $o < $pager; $o++) {
            if ($currpage == $o) {
                echo '<a href="javascript:void(0);" name="page" value="'.$o.'" class="currpage">'.($o+1).'</a> ';
            } else {
                echo '<a href="javascript:void(0);" name="page" value="'.$o.'" class="page">'.($o+1).'</a> ';
            }
        }
        if ($currpage != ($pager-1)) {
            echo '<a href="javascript:void(0);" name="page" value="'.($currpage+1).'" class="page">></a> ';
        }
        echo '</div>';
    }

    foreach ($courses as $course) {

        $fullname = format_string($course->fullname, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));
        $course_list .=  $OUTPUT->box_start('coursebox');
        $attributes = array('title' => s($course->fullname));
        $add_link = '<a href="javascript:void(0);" value="'.$course->id.'" class="viewizer_important" name="add"><img src="'.$OUTPUT->pix_url('accept', 'block_viewizer').'" /></a> ';
        if (empty($course->visible)) {
            $attributes['class'] = 'dimmed';
        }

        //Possible to add course as important when editing
        if (isset($USER->editing) && $USER->editing == 1) {            
            $course_list .= $OUTPUT->heading(($viewizer == 1 ? $add_link : '').html_writer::link(
                new moodle_url('/course/view.php', array('id' => $course->id)), $fullname, $attributes), 3);
        } else {            
            $course_list .= $OUTPUT->heading(html_writer::link(
                new moodle_url('/course/view.php', array('id' => $course->id)), $fullname, $attributes), 3);
        }
        $course_list .= $OUTPUT->box_end();
        
    }

    //Havent tested with remote courses... sorry..
    if (!empty($remote_courses)) {
        $course_list .= $OUTPUT->heading(get_string('remotecourses', 'mnet'));
    }

    foreach ($remote_courses as $course) {
        $course_list .= $OUTPUT->box_start('coursebox');
        $attributes = array('title' => s($course->fullname));
        $course_list .= $OUTPUT->heading(html_writer::link(
            new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
            format_string($course->shortname),
            $attributes) . ' (' . format_string($course->hostname) . ')', 3);
        $course_list .= $OUTPUT->box_end();
    }
    
    return $course_list;
    
}

function viewizer_print_courses_details($courses, $remote_courses, $total_courses, $courses_limit, $currpage, $viewizer) {
   
    global $CFG, $USER, $DB, $OUTPUT;

    $course_list = '';

    //Mod details
    $htmlarray = array();
    if ($modules = $DB->get_records('modules')) {
        foreach ($modules as $mod) {
            if (file_exists($CFG->dirroot.'/mod/'.$mod->name.'/lib.php')) {
                include_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
                $fname = $mod->name.'_print_overview';
                if (function_exists($fname)) {
                    $fname($courses,$htmlarray);
                }
            }
        }
    }

    foreach ($courses as $course) {

        //mod details
        $mod_details = '';
        if (array_key_exists($course->id,$htmlarray)) {
            foreach ($htmlarray[$course->id] as $modname => $html) {
                $mod_details .= $html;
            }
        }

        $fullname = format_string($course->fullname, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));
        $course_list .=  $OUTPUT->box_start('coursebox');
        $attributes = array('title' => s($course->fullname));
        $add_link = '<a href="javascript:void(0);" value="'.$course->id.'" class="viewizer_important" name="add"><img src="'.$OUTPUT->pix_url('accept', 'block_viewizer').'" /></a> ';
        if (empty($course->visible)) {
            $attributes['class'] = 'dimmed';
        }

        //Possible to add course as important when editing
        if (isset($USER->editing) && $USER->editing == 1) {            
            $course_list .= $OUTPUT->heading(($viewizer == 1 ? $add_link : '').html_writer::link(
                new moodle_url('/course/view.php', array('id' => $course->id)), $fullname, $attributes), 3);
        } else {            
            $course_list .= $OUTPUT->heading(($viewizer == 0 && !empty($mod_details) ? '<a href="javascript:void(0);" class="shdetails" value="'.$course->id.'"><img class="shimage_'.$course->id.'" src="'.$OUTPUT->pix_url('myplus', 'block_viewizer').'" /></a> ' : '').html_writer::link(
                new moodle_url('/course/view.php', array('id' => $course->id)), $fullname, $attributes), 3);
        }

        $course_list .= '<div class="course_details" id="course_details_'.$course->id.'">';

        //When courses dont meet the coursesperpage limit, show all details from course
        if ($viewizer == 0) {
            $course_list .= '<div id="course_'.$course->id.'" '.($viewizer != 1 ? 'style="display:none;"' : '').' class="mod_details">'.$mod_details.'</div>';
        }

        $course_list .= '</div>';
        $course_list .= $OUTPUT->box_end();
    }

    //Havent tested with remote courses... sorry..
    if (!empty($remote_courses)) {
        $course_list .= $OUTPUT->heading(get_string('remotecourses', 'mnet'));
    }

    foreach ($remote_courses as $course) {
        $course_list .= $OUTPUT->box_start('coursebox');
        $attributes = array('title' => s($course->fullname));
        $course_list .= $OUTPUT->heading(html_writer::link(
            new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
            format_string($course->shortname),
            $attributes) . ' (' . format_string($course->hostname) . ')', 3);
        $course_list .= $OUTPUT->box_end();
    }
    
    return $course_list;
    
}

/* Selected courses box */
function viewizer_print_important() {
    
    global $CFG, $USER, $DB, $OUTPUT;
    
    $cs = '';
    
    if (!empty($USER->profile['viewizerimportantcourses'])) {
        $cs = explode(',', $USER->profile['viewizerimportantcourses']);
    }

    $course_list = '';
    
    if (!empty($cs)) {
        
        $courses = array();
        foreach ($cs as $key => $value) {
            $course = $DB->get_record('course', array('id' => $value));
            $courses[$value] = $course;
        }

        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }
        
        $htmlarray = array();
        if ($modules = $DB->get_records('modules')) {
            foreach ($modules as $mod) {
                if (file_exists($CFG->dirroot.'/mod/'.$mod->name.'/lib.php')) {
                    include_once($CFG->dirroot.'/mod/'.$mod->name.'/lib.php');
                    $fname = $mod->name.'_print_overview';
                    if (function_exists($fname)) {
                        $fname($courses,$htmlarray);
                    }
                }
            }
        }
        
        foreach ($cs as $key => $value) {
            $course = $DB->get_record('course', array('id' => $value));

            $mod_details = '';
            if (array_key_exists($course->id,$htmlarray)) {
                foreach ($htmlarray[$course->id] as $modname => $html) {
                    $mod_details .= $html;
                }
            }
            
            $fullname = format_string($course->fullname, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));
            $attributes = array('title' => s($course->fullname));
            
            if (empty($course->visible)) {
                $attributes['class'] = 'dimmed';
            }

            //Possible to remove course from important list when editing
            if (isset($USER->editing) && $USER->editing == 1) {       
                $course_list .= $OUTPUT->heading('<a href="javascript:void(0);" value="'.$value.'" class="viewizer_important" name="remove"><img src="'.$OUTPUT->pix_url('cross', 'block_viewizer').'" /></a> '.html_writer::link(
                    new moodle_url('/course/view.php', array('id' => $value)), $fullname, $attributes), 3);
            } else {            
                $course_list .= $OUTPUT->heading((!empty($mod_details) ? '<a href="javascript:void(0);" class="shdetails" value="'.$course->id.'"><img class="shimage_'.$course->id.'" src="'.$OUTPUT->pix_url('myplus', 'block_viewizer').'" /></a> ' : '').html_writer::link(
                    new moodle_url('/course/view.php', array('id' => $course->id)), $fullname, $attributes), 3);
            }

            $course_list .= '<div class="course_details" id="course_details_'.$course->id.'">';

            $course_list .= '<div id="course_'.$course->id.'" style="display:none;" class="mod_details">'.$mod_details.'</div>';
            $course_list .= '</div>';
            
        }

        echo $OUTPUT->box_start('coursebox viewizerimportant');
        echo $course_list;
        echo $OUTPUT->box_end();
        
    }

}

/* Profile details */
function viewizer_print_profile() {
    
    global $CFG, $USER, $PAGE, $DB, $OUTPUT;
    
    //Messages
    if (!empty($CFG->messaging) and has_capability('moodle/site:sendmessage', get_context_instance(CONTEXT_SYSTEM))) {
        if (!empty($USER->id)) {
            if ($countmessages = $DB->count_records('message', array('useridto' => $USER->id))) {
                $msg = get_string('you_have_messages','block_viewizer',$countmessages);
            } else {
                $msg = get_string('you_have_no_messages','block_viewizer');
            }
            $msg .= '<a href="'.$CFG->wwwroot.'/message/index.php" onclick="openpopup(\'/message/index.php\', \'message\', \'menubar=0,location=0,scrollbars,status,resizable,width=400,height=500\', 0);return false;">';
            if ($countmessages == 1) {
                $msg .= get_string('message','block_viewizer');
            } else {
                $msg .= get_string('messages','block_viewizer');
            }
            $msg .= '</a>';
        }
    }
    
    $udetails = '<p>'.get_string('viewizerwelcome', 'block_viewizer').$USER->firstname.' '.$USER->lastname.'!</p>';
    $udetails .= '<p>'.$msg.'</p>';
    $udetails .= '<p>'.print_course_search('', true).'</p>';
    $udetails .= '<p class="viewizer_lastlogin">'.
                    get_string('viewizerlastlogin', 'block_viewizer').
                    date('j F Y H:i:s', usertime($USER->lastlogin, $USER->timezone)).
                '</p>';    
    
    echo $OUTPUT->box_start('coursebox viewizerprofile');
    echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>120, 'class' => 'viewizer_userpicture')), array('class' => 'viewizer_uimage'));
    echo html_writer::tag('div', $udetails, array('class' => 'viewizer_udetails'));
    echo html_writer::tag('div', '', array('class' => 'viewizer_clear'));
    echo $OUTPUT->box_end();
}

//Add/Remove course ajax handler
function viewizer_important($id, $action) {
    
    global $CFG, $USER, $PAGE, $DB;
    
    
    require_once($CFG->dirroot.'/user/profile/lib.php');

    $cs = '';
  
    if (!empty($USER->profile['viewizerimportantcourses'])) {
        $cs = explode(',', $USER->profile['viewizerimportantcourses']);
    }

    //add course
    if ($action == 'add') {
        
        if (!in_array($id, $cs)) {
            
            if ($fields = $DB->get_records_select('user_info_field', "shortname = 'viewizerimportantcourses'")) {
                
                foreach ($fields as $field) {
                    $data = new object();
                    $data->userid = $USER->id;
                    $data->fieldid = $field->id;
                    $data->data = (empty($USER->profile['viewizerimportantcourses']) ? $id : $USER->profile['viewizerimportantcourses'].','.$id);
                    if ($dataid = $DB->get_field('user_info_data', 'id', array('userid' => $data->userid, 'fieldid' => $data->fieldid))) {
                        // row already exists
                        $data->id = $dataid;
                        if (!$DB->update_record('user_info_data', $data)) {
                           echo error(get_string('update_order_fail', 'block_viewizer'));
                        }
                    } else {
                        // new row
                        $DB->insert_record('user_info_data', $data);
                    }
                }
                
            } else {
                echo error(get_string('need_field', 'block_viewizer'));
            }
        } else {
            echo error(get_string('existing_course', 'block_viewizer'));
        }

    } else {
        //remove course
        if (in_array($id, $cs)) { //Check if key exists in array

            $akey = array_search($id, $cs); //find the key position

            unset($cs[$akey]); //remove selected key from array

            if ($fields = $DB->get_records_select('user_info_field', "shortname = 'viewizerimportantcourses'")) {

                foreach ($fields as $field) {
                    
                    $data = new object();
                    $data->userid = $USER->id;
                    $data->fieldid = $field->id;
                    //Make string of courses to be removed
                    if (count($cs) >= 1) {
                        $i = count($cs);
                        $cdata = '';
                        foreach ($cs as $c) {                            
                            if ($i <= 1) {
                                $cdata .= $c;
                            } else {
                                $cdata .= $c.',';
                            }
                            $i--;
                        }
                    } else {
                        $cdata = $cs;
                    }

                    //dont add any array or something
                    if (is_array($cdata)) {
                        $cdata = '';
                    }                    

                    $data->data = $cdata;
                    if ($dataid = $DB->get_field('user_info_data', 'id', array('userid' => $data->userid, 'fieldid' => $data->fieldid))) {
                        // row already exists
                        $data->id = $dataid;
                        if (!$DB->update_record('user_info_data', $data)) {
                            echo error(get_string('update_order_fail', 'block_viewizer'));
                        }
                    } else {
                        // new row
                        $DB->insert_record('user_info_data', $data);
                    }
                }
            } else {
                echo error(get_string('need_field', 'block_viewizer'));
            }
        } else {
            echo error(get_string('removed_course', 'block_viewizer'));
        }

    }
    
    //Update myimportant USER global value
    $USER->profile['viewizerimportantcourses'] = $DB->get_field('user_info_data', 'data', array('userid'=> $data->userid, 'fieldid' => $data->fieldid));

}

/**
 * Returns list of courses current $USER is enrolled in and can access
 *
 * - $fields is an array of field names to ADD
 *   so name the fields you really need, which will
 *   be added and uniq'd
 *
 * @param string|array $fields
 * @param string $sort
 * @param int $limit max number of courses
 * @return array
 */
function viewizer_get_my_courses($fields = NULL, $sort = 'visible DESC,fullname ASC', $limit_start = 0, $limit_end = NULL) {
    global $DB, $USER;

    // Guest account does not have any courses
    if (isguestuser() or !isloggedin()) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
                        'shortname', 'fullname', 'idnumber',
                        'startdate', 'visible',
                        'groupmode', 'groupmodeforce');

    if (empty($fields)) {
        $fields = $basefields;
    } else if (is_string($fields)) {
        // turn the fields from a string to an array
        $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_unique(array_merge($basefields, $fields));
    } else if (is_array($fields)) {
        $fields = array_unique(array_merge($basefields, $fields));
    } else {
        throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
    }
    if (in_array('*', $fields)) {
        $fields = array('*');
    }

    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $rawsorts = explode(',', $sort);
        $sorts = array();
        foreach ($rawsorts as $rawsort) {
            $rawsort = trim($rawsort);
            if (strpos($rawsort, 'c.') === 0) {
                $rawsort = substr($rawsort, 2);
            }
            $sorts[] = trim($rawsort);
        }
        $sort = 'c.'.implode(',c.', $sorts);
        $orderby = "ORDER BY $sort";
    }

    $wheres = array("c.id <> :siteid");
    $params = array('siteid'=>SITEID);

    if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
        // list _only_ this course - anything else is asking for trouble...
        $wheres[] = "courseid = :loginas";
        $params['loginas'] = $USER->loginascontext->instanceid;
    }

    $coursefields = 'c.' .join(',c.', $fields);
    list($ccselect, $ccjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
    $wheres = implode(" AND ", $wheres);

    //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
    $sql = "SELECT $coursefields $ccselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                     WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                   ) en ON (en.courseid = c.id)
           $ccjoin
             WHERE $wheres
          $orderby";
    $params['userid']  = $USER->id;
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['now1']    = round(time(), -2); // improves db caching
    $params['now2']    = $params['now1'];

    $courses = $DB->get_records_sql($sql, $params, $limit_start, $limit_end);

    // preload contexts and check visibility
    foreach ($courses as $id=>$course) {
        context_instance_preload($course);
        if (!$course->visible) {
            if (!$context = get_context_instance(CONTEXT_COURSE, $id)) {
                unset($courses[$id]);
                continue;
            }
            if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                unset($courses[$id]);
                continue;
            }
        }
        $courses[$id] = $course;
    }

    //wow! Is that really all? :-D
    return $courses;
}
?>