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
 * @copyright 2010 Raymond Wainman - University of Alberta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
class rooms_avaliable_form {

    private static $formname= 'AvaliableRooms';
    private $course = "";

    public function __toString() {
        global $CFG;

        $formname = rooms_avaliable_form::$formname;

      $string = '<a href="#apptForm_2" id="avaliable_rooms_link" class="inline"></a>';

        $string .= '<div style="display:none;">';
        $string .= '<div id="apptForm_2">';
        $string .= '<form name="' . $formname . '" onsubmit="">';

        //reservation id
        $string .= '<input type="hidden" name="reservation_id" value="">';
        $string .= '<input type="hidden" name="form_name" value="'.$formname.'">';
        $string .= '<input type="hidden" name="base_url" value="'.$CFG->wwwroot.'">';
        $string .= '<input type="hidden" name="courseid" value="'.$this->course.'">';

        //Main Form
        $string .= '<div id="details" class="calendar_apptForm_box">';
        $string .= '<table>';


        //Start Time
        $string .= '<tr>';
        $string .= '<th style="text-align:right;">' . get_string('starttime', 'block_roomscheduler') . ':</th>';
        $string .= '<td><input type="text" name="' . $formname . '_startTime_date" size="8" onkeyup="get_avaliable_rooms(\''.$formname.'\');">';
        $string .= '&nbsp;&nbsp;';
        $string .= rooms_avaliable_form::apptForm_timeDropdown($formname . '_startTime', '', 'get_avaliable_rooms(\''.$formname.'\');');
 
        $string .= '</td>';
        $string .= '</tr>';

        //End Time
        $string .= '<tr>';
        $string .= '<th style="text-align:right;">' . get_string('endtime', 'block_roomscheduler') . ':</th>';
        $string .= '<td><input type="text" name="' . $formname . '_endTime_date" size="8" onkeyup="get_avaliable_rooms(\''.$formname.'\');">';
        $string .= '&nbsp;&nbsp;';
        $string .= rooms_avaliable_form::apptForm_timeDropdown($formname . '_endTime', '','get_avaliable_rooms(\''.$formname.'\');');
        $string .= '</td></tr>';

      
        //Save button
        $string .= '<tr>';
        $string .= '<td><input type="button" id="' . $formname . '_close" value="' . get_string('close', 'block_roomscheduler') . '" onclick="$.fancybox.close()"></td>';
        $string .= '</tr>';


        
        $string .= '</table>';
        $string.= '<div id="rooms_available"></div>';
        $string .= '</div>';    //End of main form
   
        $string .= '</form>';

        


        $string .= '</div>';
        $string .= '</div>';

        return $string;
    }

    /* STATIC FUNCTIONS */

     /**
     * Generates a dropdown for selecting time
     *
     * @param string $name form element name
     * @param int $selectedvalue selected value (eg.1830 == 18h30)
     * @return string markup for output
     */
    public static function apptForm_timeDropdown($name, $selectedvalue='', $onchange='') {
        $string = '<select name="' . $name . '" onchange="' . $onchange . '">';
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute+=10) {
                if ($minute == 0) {
                    $minute = '00';
                }
                $string .= '<option value="' . $hour . $minute . '" ';
                if ($selectedvalue == ($hour . $minute)) {
                    $string .= 'SELECTED';
                }
                $string .= '>';
                $string .= $hour . 'h' . $minute;
                $string .= '</option>';
            }
        }
        $string .= '</select>';
        return $string;
    }


    public static function apptForm_formName(){
        return rooms_avaliable_form::$formname;
    }

public function setCourseID($course){
 $this->course = $course;   
}

}
?>
