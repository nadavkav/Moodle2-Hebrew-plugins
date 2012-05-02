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
class apptForm_recursiveEdit {

    private static $formname = 'RecurEditAppt';

    /* INSTANCE FUNCTIONS */

    /**
     * Renders the form
     *
     * @return string markup for output
     */
    public function __toString() {
        global $room_obj;

        $formname = apptForm_recursiveEdit::$formname;

        $div = 'calendar_day_0';

        $string = '<a href="#apptFormRecurEdit" id="inline3" class="inline"></a>';

        $string .= '<div style="display:none;">';
        $string .= '<div id="apptFormRecurEdit">';
        $string .= '<form name="' . $formname . '" onsubmit="return calendar_apptForm_submit(this, \'' . $div . '\')">';

        //Room id
        $string .= '<input type="hidden" name="room" value="' . $room_obj->id . '">';

        //reservation id
        $string .= '<input type="hidden" name="'.$formname.'_reservationid" value="">';
        $string .= '<input type="hidden" name="'.$formname.'_editrecursive" value="">';


        //Tabs for toggling
        $string .= '<table style="margin-bottom:0;"><tr>';
        $string .= '<th id="tab_details_recursiveEdit" class="calendar_apptForm_selectedTab">' . get_string('details', 'block_roomscheduler') . '</th>';
        $string .= '</tr></table>';

        //Main Form
        $string .= '<div id="details_recursiveEdit" class="calendar_apptForm_box">';
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


         $string .= '<div id="recurrence_recursiveEdit" class="calendar_apptForm_box" style="display:none;height:100%;">';
        $string .= '<input type="hidden" value="none" name="' . $formname . '_recurrence"></td>';
$string .='</div>';


 $string .= '<div class="calendar_apptForm_status" id="' . $formname . '_status_2" style="visibility:hidden">';

        $string .= '</form>';
        $string .= '</div>';
        $string .= '</div>';


          //----------NOT USED-------------------------------------------------
          //Included so we can piggy-back on existing code to process form submit
          //
//Start Time
        $string .= '<input type="hidden" name="' . $formname . '_startTime_date" value="01/01/2011">';
        $string .= '<input type="hidden" name="'.$formname . '_startTime"  value="0000">';
        $string .= '<input type="hidden" name="' . $formname . '_allday" >';

        //End Time

         $string .= '<input type="hidden" name="' . $formname . '_endTime_date" value="01/01/2011">';
        $string .= '<input type="hidden" name="'.$formname . '_endTime"  value="0000">';
 $string .= '<input type="hidden" value="none" id="' . $formname .'_recurrence_details"></td>';

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

    public static function apptForm_formName(){
        return apptForm_recursiveEdit::$formname;
    }
}

?>
