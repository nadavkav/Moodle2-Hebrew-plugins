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
 * Block viewizer
 * Alternate view for MYMoodle
 *
 * @package    block
 * @subpackage viewizer
 * @copyright  2012 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/lib/weblib.php');
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/blocks/viewizer/lib.php');

class block_viewizer extends block_base {

    public function init() {
        $this->title   = get_string('pluginname', 'block_viewizer');
    }
    
    // only one instance of this block is required
    function instance_allow_multiple() {
        return false;
    }

    // label and button values can be set in admin
    function has_config() {
        return false;
    }
    
    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        
        global $USER, $CFG, $PAGE;
        
        //Kick ass viewizer with AJAX & Javascript! These are needed..
        $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/blocks/viewizer/javascript/jquery-1.7.1.min.js'));
        $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/blocks/viewizer/javascript/viewizer.min.js'));
        
        if($this->content !== NULL) {
            return $this->content;
        }
        
        //Page param for paging 
        $currpage = optional_param('page', 0, PARAM_INT);

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();

        // limits the number of courses showing up - default
        $courses_limit = 10;

        // FIXME: this should be a block setting, rather than a global setting
        if (isset($CFG->viewizercoursesperpage)) {
            $courses_limit = $CFG->viewizercoursesperpage;
        }
        
        //Get all user enrolled courses        
        $all_courses = enrol_get_users_courses($USER->id, false, array('id, shortname'), 'visible DESC, fullname ASC, sortorder ASC');

        //Slice em up
        if (isset($currpage)) {  
            $courses = viewizer_get_my_courses('id, shortname, modinfo', 'visible DESC, fullname ASC, sortorder ASC', ($courses_limit*$currpage), $courses_limit);
        } else {
            $courses = viewizer_get_my_courses('id, shortname, modinfo', 'visible DESC, fullname ASC, sortorder ASC', 0, $courses_limit);                
        }

        $total_courses = count($all_courses);
        
        $site = get_site();
        $course = $site; //just in case we need the old global $course hack

        if (is_enabled_auth('mnet')) {
            $remote_courses = get_my_remotecourses();
        }
        if (empty($remote_courses)) {
            $remote_courses = array();
        }

        if (($courses_limit > 0) && (count($courses)+count($remote_courses) >= $courses_limit)) {
            // get rid of any remote courses that are above the limit
            $remote_courses = array_slice($remote_courses, 0, $courses_limit - count($courses), true);
        }

        if (array_key_exists($site->id,$courses)) {
            unset($courses[$site->id]);
        }

        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }

        if (empty($courses) && empty($remote_courses)) {
            $content[] = get_string('nocourses','my');
        } else {            
            ob_start();
            //Profile info
            viewizer_print_profile();
            //Course list/ Selected courses
            echo html_writer::start_tag('div', array('id'=>'viewizer_courses'));
            echo viewizer_print_overview($courses, $remote_courses, $total_courses, $courses_limit, $currpage);
            echo html_writer::end_tag('div');  

            $content[] = ob_get_contents();
            ob_end_clean();
        }

        $this->content->text = implode($content);

        return $this->content;
    }
    
    //specialisation
    function specialisation() {
      //empty!
    } 

    //Can only be added to MyMoodle page
    public function applicable_formats() {
        return array('my-index'=>true);
    }
}
?>
