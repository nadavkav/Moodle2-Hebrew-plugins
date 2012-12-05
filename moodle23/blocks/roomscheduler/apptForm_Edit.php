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
 * @copyright 2011 Dustin Durand - University of Alberta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class apptForm_edit {

    private static $formname = 'EditAppt';

    /* INSTANCE FUNCTIONS */

    /**
     * Renders the form
     *
     * @return string markup for output
     */
    public function __toString() {
        global $room_obj;

        $formname = apptForm_edit::$formname;

        $div = 'calendar_day_0';

        $string = '<a href="#apptFormEdit" id="inline2" class="inline"></a>';

        $string .= '<div style="display:none;">';
        $string .= '<div id="apptFormEdit">';
        $string .= '<form name="' . $formname . '" onsubmit="return calendar_apptForm_submit(this, \'' . $div . '\')">';

        //Room id
        $string .= '<input type="hidden" name="room" value="' . $room_obj->id . '">';

        //reservation id
        $string .= '<input type="hidden" name="'.$formname.'_reservationid" value="">';
    $string .= '<input type="hidden" name="'.$formname.'_editrecursive" value="0">';


        //Tabs for toggling
        $string .= '<table style="margin-bottom:0;"><tr>';
        $string .= '<th id="tab_details_edit" class="calendar_apptForm_selectedTab">' . get_string('details', 'block_roomscheduler') . '</th>';
        $string .= '</tr></table>';

        //Main Form
        $string .= '<div id="details_edit" class="calendar_apptForm_box">';
        $string .= '<table>';

        //Subject
        $string .= '<tr>';
        $string .= '<th style="text-align:right;">' . get_string('subject', 'block_roomscheduler') . ':</th>';
        $string .= '<td><input type="text" name="' . $formname . '_subject" size="50"></td>';
        $string .= '</tr>';

        //Location
        $string .= '<tr>';
        $string .= '<th>' . get_string('location', 'block_roomscheduler') . ':</th>';
        $string .= '<td>' . $room_obj->name . '</td>';
        $string .= '</tr>';

        //Category
        //Location
        $string .= '<tr>';
        $string .= '<th style="text-align:right;">' . get_string('category', 'block_roomscheduler') . ':</th>';
        $string .= '<td><select name="' . $formname . '_category">';
        $string .= '<option value="" class="calendar_default_dropdown"></option>';
        $string .= '<option value="class" class="calendar_class_dropdown">' . get_string('class', 'block_roomscheduler') . '</option>';
        $string .= '<option value="meeting" class="calendar_meeting_dropdown">' . get_string('meeting', 'block_roomscheduler') . '</option>';
        $string .= '</select></td>';
        $string .= '</tr>';

        //Start Time
        $string .= '<tr style="border-top:1px dashed #DDDDDD;">';
        $string .= '<th style="text-align:right;">' . get_string('starttime', 'block_roomscheduler') . ':</th>';
        $string .= '<td><input type="text" name="' . $formname . '_startTime_date" size="8" onkeyup="calendar_apptForm_edit_checkAvailability(\'' . $formname . '\');">';
        $string .= '&nbsp;&nbsp;';
        $string .= apptForm::apptForm_timeDropdown($formname . '_startTime', '', 'calendar_apptForm_edit_checkAvailability(\'' . $formname . '\');');
        $string .= '&nbsp;&nbsp;&nbsp;<input type="checkbox" name="' . $formname . '_allday" onclick="calendar_apptForm_allday(this)" onchange="calendar_apptForm_edit_checkAvailability(\'' . $formname . '\');">&nbsp;' . get_string('alldayevent', 'block_roomscheduler');
        $string .= '</td>';
        $string .= '</tr>';

        //End Time
        $string .= '<tr>';
        $string .= '<th style="text-align:right;">' . get_string('endtime', 'block_roomscheduler') . ':</th>';
        $string .= '<td><input type="text" name="' . $formname . '_endTime_date" size="8" onkeyup="calendar_apptForm_edit_checkAvailability(\'' . $formname . '\');">';
        $string .= '&nbsp;&nbsp;';
        $string .= apptForm::apptForm_timeDropdown($formname . '_endTime', '', 'calendar_apptForm_edit_checkAvailability(\'' . $formname . '\');');
        $string .= '</td></tr>';

        //Status window
        $string .= '<tr><td colspan="2">';
        $string .= '<div class="calendar_apptForm_status" id="' . $formname . '_status">';
        $string .= '</div>';
        $string .= '</td></tr>';

        //Description
        $string .= '<tr>';
        $string .= '<td colspan="2">';
        $string .= '<textarea style="width:100%;" rows="10" name="' . $formname . '_description"></textarea>';
        $string .= '</td>';
        $string .= '</tr>';

        //Save button
        $string .= '<tr>';
        $string .= '<td><input type="submit" name="' . $formname . '_save" value="' . get_string('save', 'block_roomscheduler') . '"></td><td><input type="button" value="' . get_string('close', 'block_roomscheduler') . '" onclick="calendar_resetAppt(this);"></td>';
        $string .= '</tr>';

        $string .= '</table>';

        $string .= '</div>';    //End of main form
        //Start of recurrence form


         $string .= '<div id="recurrence_edit" class="calendar_apptForm_box" style="display:none;height:100%;">';
        $string .= '<input type="hidden" value="none" name="' . $formname . '_recurrence"></td>';
$string .= '<input type="hidden" value="none" id="' . $formname .'_recurrence_details"></td>';

        $string .='</div>';


 $string .= '<div class="calendar_apptForm_status" id="' . $formname . '_status_2" style="visibility:hidden">';

        $string .= '</form>';
        $string .= '</div>';
        $string .= '</div>';


          //Start of recurrence form





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
        return apptForm_edit::$formname;
    }
}

?>
