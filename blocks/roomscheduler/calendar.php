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
 * @package   block_roomscheduler
 * @copyright 2010 Raymond Wainman, Dustin Durand - University of Alberta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('calendar_day.php');
require_once('calendar_week.php');
require_once('apptForm.php');
require_once('apptForm_Edit.php');
require_once('apptForm_recursiveEdit.php');
require_once('reservations_lib.php');
require_once('calendar_month.php');

$PAGE->requires->js('/blocks/roomscheduler/calendar.js');

abstract class calendar {

    /* INSTANCE VARIABLES */
    
    protected $focus_time;    //focused time of calendar - UNIX timestamp
    protected $room;          //room id - from roomscheduler_rooms table
    protected $course;       //course id
    /* CONSTRUCTOR */
    
    /**
     * Initialize calendar
     *
     * @param int $room room - id from roomscheduler_rooms table
     * @param string $format - string, one of above defined constants
     * @param int $focus_time - focus time of calendar, defaults to time()
     */
    public function __construct($room, $focus_time=null) {
        $this->room = $room;

        if($focus_time==null){
            $focus_time = time();
        }
        $this->focus_time = $focus_time;
    }



    /* ABSTRACT FUNCTIONS */

    /**
     * This function must be implemented by subclasses to render themselves into
     * HTML markup.
     */
    public abstract function __toString();

    /**
     * This function must be implemented by subclasses to save itself to the $_SESSION
     * PHP global variable.
     */
    public abstract function serialize();


    /* INSTANCE FUNCTIONS */

    /**
     * Set the focus time of the calendar
     *
     * @param int $focus_time - UNIX timestamp
     */
    public function set_focusTime($focus_time){
        $this->focus_time = $focus_time;
    }

    /**
     * Get current focus time of the calendar
     *
     * @return int $focus_time
     */
    public function get_focusTime(){
        return $this->focus_time;
    }

    /**
     * Dummy function for refresh, makes php debugger happy
     */
    public function refresh(){
        
    }

    /**
     * Renders the appointment form used by all versions of the calendar in
     * a fancybox with id 'apptForm'.
     *
     * @return string $string - HTML markup for form (in hidden fancybox)
     */
    public static function render_apptForm(){
        //new apptForm
        $apptForm = new apptForm();
        echo $apptForm;

        //edit apptForm
        $apptForm = new apptForm_edit();
        echo $apptForm;

        //edit apptForm
        $apptForm = new apptForm_recursiveEdit();
        echo $apptForm;
        
    }


 

}



?>
