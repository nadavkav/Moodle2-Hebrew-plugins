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
class apptForm {

    private static $formname= 'newAppt';

    /* INSTANCE FUNCTIONS */

    /**
     * Renders the form
     *
     * @return string markup for output
     */
    public function __toString() {
        global $room_obj;

        $formname = apptForm::$formname;

        $div = 'calendar_day_0';

        $string = '<a href="#apptForm" id="inline" class="inline"></a>';

        $string .= '<div style="display:none;">';
        $string .= '<div id="apptForm">';
        $string .= '<form name="' . $formname . '" onsubmit="return calendar_apptForm_submit(this, \'' . $div . '\')">';

        //Room id
        $string .= '<input type="hidden" name="room" value="' . $room_obj->id . '">';

        //reservation id
        $string .= '<input type="hidden" name="reservation_id" value="">';

        //Tabs for toggling
        $string .= '<table style="margin-bottom:0;"><tr>';
        $string .= '<th id="tab_details" class="calendar_apptForm_selectedTab" onclick="calendar_apptForm_toggleTab(this);">' . get_string('details', 'block_roomscheduler') . '</th>';
        $string .= '<th id="tab_recurrence" class="calendar_apptForm_tab" onclick="calendar_apptForm_toggleTab(this);">' . get_string('recurrence', 'block_roomscheduler') . '</th>';
        $string .= '</tr></table>';

        //Main Form
        $string .= '<div id="details" class="calendar_apptForm_box">';
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
        $string .= '<td><input type="text" name="' . $formname . '_startTime_date" size="8" onkeyup="calendar_apptForm_checkAvailability(\'' . $formname . '\');">';
        $string .= '&nbsp;&nbsp;';
        $string .= apptForm::apptForm_timeDropdown($formname . '_startTime', '', 'calendar_apptForm_checkAvailability(\'' . $formname . '\');');
        $string .= '&nbsp;&nbsp;&nbsp;<input type="checkbox" name="' . $formname . '_allday" onclick="calendar_apptForm_allday(this)" onchange="calendar_apptForm_checkAvailability(\'' . $formname . '\');">&nbsp;' . get_string('alldayevent', 'block_roomscheduler');
        $string .= '</td>';
        $string .= '</tr>';

        //End Time
        $string .= '<tr>';
        $string .= '<th style="text-align:right;">' . get_string('endtime', 'block_roomscheduler') . ':</th>';
        $string .= '<td><input type="text" name="' . $formname . '_endTime_date" size="8" onkeyup="calendar_apptForm_checkAvailability(\'' . $formname . '\');">';
        $string .= '&nbsp;&nbsp;';
        $string .= apptForm::apptForm_timeDropdown($formname . '_endTime', '', 'calendar_apptForm_checkAvailability(\'' . $formname . '\');');
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
        $string .= '<td><input type="submit" name="' . $formname . '_save" value="' . get_string('save', 'block_roomscheduler') . '"></td><td><input type="button" id="' . $formname . '_close" value="' . get_string('close', 'block_roomscheduler') . '" onclick="calendar_resetAppt(this);"></td>';
        $string .= '</tr>';

        $string .= '</table>';
        $string .= '</div>';    //End of main form
        //Start of recurrence form
        $string .= '<div id="recurrence" class="calendar_apptForm_box" style="display:none;height:100%;">';
        $string .= '<table style="width:100%; margin-bottom:0;">';

        //No recurrence
        $string .= '<tr>';
        $string .= '<td style="width:20%";colspan="2"><input type="radio" value="none" onclick="calendar_toggleRecurrence(this);calendar_apptForm_checkAvailability(\''.$formname.'\');" name="' . $formname . '_recurrence" CHECKED> ' . get_string('none', 'block_roomscheduler') . '</td>';
        $string .= '<td style="width:80%";></td>';
        $string .= '</tr>';

        $string .= '<tr style="border-top:1px dashed #DDDDDD;">';

        //Recurrence type selector
        $string .= '<td style="border-right:1px dashed #DDDDDD;"><input type="radio" value="daily" name="' . $formname . '_recurrence" onclick="calendar_toggleRecurrence(this);calendar_apptForm_checkAvailability(\''.$formname.'\');"> ' . get_string('daily', 'block_roomscheduler') . '</td>';

        $string .= '<td rowspan="4" valign="top" style="padding:0;" id="'.$formname.'_recurrence_details">';

        //Daily
        $string .= '<div id="' . $formname . '_daily" style="display:none;margin:0;padding:0;">';
        $string .= '<table>';
        $string .= '<tr><td><input type="radio" value="everyxdays" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" name="' . $formname . '_daily_radio" CHECKED> ' . get_string('every', 'block_roomscheduler') . ' ';
        $string .= '<input type="text" onkeyup="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="1" name="' . $formname . '_daily_everyxdays" size="1"> ' . get_string('dayss', 'block_roomscheduler') . '</td></tr>';
        $string .= '<tr><td><input type="radio" value="everyweekday" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" name="' . $formname . '_daily_radio"> ' . get_string('everyweekday', 'block_roomscheduler') . '</td></tr>';
        $string .= '</table>';
        $string .= '</div>';

        //Weekly
        $string .= '<div id="' . $formname . '_weekly" style="display:none;">';
        $string .= '<table style="width:100%;">';
        $string .= '<tr><td colspan="4">' . get_string('recurevery', 'block_roomscheduler') . ' <input type="text" onkeyup="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="1" name="' . $formname . '_weekly_recur" size="1"> ';
        $string .= get_string('weeksson', 'block_roomscheduler') . ':</td></tr>';
        $string .= '<tr style="font-size:10px;">';
        $string .= '<td><input type="checkbox" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="7" name="' . $formname . '_weekly_days"> ' . get_string('sunday', 'block_roomscheduler') . '</td>';
        $string .= '<td><input type="checkbox" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="1" name="' . $formname . '_weekly_days"> ' . get_string('monday', 'block_roomscheduler') . '</td>';
        $string .= '<td><input type="checkbox" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="2" name="' . $formname . '_weekly_days"> ' . get_string('tuesday', 'block_roomscheduler') . '</td>';
        $string .= '<td><input type="checkbox" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="3" name="' . $formname . '_weekly_days"> ' . get_string('wednesday', 'block_roomscheduler') . '</td>';
        $string .= '</tr><tr style="font-size:10px;">';
        $string .= '<td><input type="checkbox" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="4" name="' . $formname . '_weekly_days"> ' . get_string('thursday', 'block_roomscheduler') . '</td>';
        $string .= '<td><input type="checkbox" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="5" name="' . $formname . '_weekly_days"> ' . get_string('friday', 'block_roomscheduler') . '</td>';
        $string .= '<td><input type="checkbox" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="6" name="' . $formname . '_weekly_days"> ' . get_string('saturday', 'block_roomscheduler') . '</td>';
        $string .= '<td></td></tr>';
        $string .= '</table>';
        $string .= '</div>';

        //Monthly
        $string .= '<div id="' . $formname . '_monthly" style="display:none;">';
        $string .= '<table style="width:100%;">';
        $string .= '<tr><td><input type="radio" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="dayofeverymonth" name="' . $formname . '_monthly_radio" CHECKED> ' . get_string('day', 'block_roomscheduler');
        $string .= ' <input type="text" onkeyup="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="15" name="' . $formname . '_monthly_day" size="1"> ';
        $string .= get_string('ofevery', 'block_roomscheduler') . ' <input type="text" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" size="1" value="1" name="' . $formname . '_monthly_months"> ';
        $string .= get_string('monthss', 'block_roomscheduler') . '</td>';
        $string .= '</tr><tr>';
        $string .= '<td><input type="radio" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="xxofeverymonth" name="' . $formname . '_monthly_radio"> ' . get_string('the', 'block_roomscheduler') . ' ';
        $string .= '<select name="' . $formname . '_monthly_number" onchange="calendar_apptForm_checkAvailability(\''.$formname.'\');">';
        $string .= '<option value="1">' . get_string('first', 'block_roomscheduler') . '</option>';
        $string .= '<option value="2">' . get_string('second', 'block_roomscheduler') . '</option>';
        $string .= '<option value="3">' . get_string('third', 'block_roomscheduler') . '</option>';
        $string .= '<option value="4">' . get_string('fourth', 'block_roomscheduler') . '</option>';
        $string .= '</select>';
        $string .= ' ';
        $string .= '<select name="' . $formname . '_monthly_day2" onchange="calendar_apptForm_checkAvailability(\''.$formname.'\');">';
        $string .= '<option value="7">' . get_string('sunday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="1">' . get_string('monday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="2">' . get_string('tuesday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="3">' . get_string('wednesday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="4">' . get_string('thursday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="5">' . get_string('friday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="6">' . get_string('saturday', 'block_roomscheduler') . '</option>';
        $string .= '</select>';
        $string .= ' ' . get_string('ofevery', 'block_roomscheduler');
        $string .= ' <input type="text" size="1" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="1" name="' . $formname . '_monthly_ofeveryxmonth"> ';
        $string .= get_string('monthss', 'block_roomscheduler') . '</td></tr>';
        $string .= '</table>';
        $string .= '</div>';

        //Yearly
        $string .= '<div id="' . $formname . '_yearly" style="display:none;">';
        $string .= '<table style="margin-bottom:0;">';
        $string .= '<td><input type="radio" value="option1" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" name="' . $formname . '_yearly_radio" CHECKED> ' . get_string('every', 'block_roomscheduler');
        $string .= ' <select name="' . $formname . '_yearly_month" onchange="calendar_apptForm_checkAvailability(\''.$formname.'\');">';
        $string .= '<option value="1">' . get_string('january', 'block_roomscheduler') . '</option>';
        $string .= '<option value="2">' . get_string('february', 'block_roomscheduler') . '</option>';
        $string .= '<option value="3">' . get_string('march', 'block_roomscheduler') . '</option>';
        $string .= '<option value="4">' . get_string('april', 'block_roomscheduler') . '</option>';
        $string .= '<option value="5">' . get_string('may', 'block_roomscheduler') . '</option>';
        $string .= '<option value="6">' . get_string('june', 'block_roomscheduler') . '</option>';
        $string .= '<option value="7">' . get_string('july', 'block_roomscheduler') . '</option>';
        $string .= '<option value="8">' . get_string('august', 'block_roomscheduler') . '</option>';
        $string .= '<option value="9">' . get_string('september', 'block_roomscheduler') . '</option>';
        $string .= '<option value="10">' . get_string('october', 'block_roomscheduler') . '</option>';
        $string .= '<option value="11">' . get_string('november', 'block_roomscheduler') . '</option>';
        $string .= '<option value="12">' . get_string('december', 'block_roomscheduler') . '</option>';
        $string .= '</select>';
        $string .= ' <input type="text" value="15" onkeyup="calendar_apptForm_checkAvailability(\''.$formname.'\');" name="' . $formname . '_yearly_day" size="1"></td></tr>';
        $string .= '<tr>';
        $string .= '<td><input type="radio" value="option2" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" name="' . $formname . '_yearly_radio"> ' . get_string('the', 'block_roomscheduler');
        $string .= ' <select name="' . $formname . '_yearly_number" onchange="calendar_apptForm_checkAvailability(\''.$formname.'\');">';
        $string .= '<option value="1">' . get_string('first', 'block_roomscheduler') . '</option>';
        $string .= '<option value="2">' . get_string('second', 'block_roomscheduler') . '</option>';
        $string .= '<option value="3">' . get_string('third', 'block_roomscheduler') . '</option>';
        $string .= '<option value="4">' . get_string('fourth', 'block_roomscheduler') . '</option>';
        $string .= '</select>';
        $string .= ' <select name="' . $formname . '_yearly_day2" onchange="calendar_apptForm_checkAvailability(\''.$formname.'\');">';
        $string .= '<option value="7">' . get_string('sunday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="1">' . get_string('monday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="2">' . get_string('tuesday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="3">' . get_string('wednesday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="4">' . get_string('thursday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="5">' . get_string('friday', 'block_roomscheduler') . '</option>';
        $string .= '<option value="6">' . get_string('saturday', 'block_roomscheduler') . '</option>';
        $string .= '</select> ';
        $string .= get_string('of', 'block_roomscheduler') . ' ';
        $string .= '<select name="' . $formname . '_yearly_month2" onchange="calendar_apptForm_checkAvailability(\''.$formname.'\');">';
        $string .= '<option value="1">' . get_string('january', 'block_roomscheduler') . '</option>';
        $string .= '<option value="2">' . get_string('february', 'block_roomscheduler') . '</option>';
        $string .= '<option value="3">' . get_string('march', 'block_roomscheduler') . '</option>';
        $string .= '<option value="4">' . get_string('april', 'block_roomscheduler') . '</option>';
        $string .= '<option value="5">' . get_string('may', 'block_roomscheduler') . '</option>';
        $string .= '<option value="6">' . get_string('june', 'block_roomscheduler') . '</option>';
        $string .= '<option value="7">' . get_string('july', 'block_roomscheduler') . '</option>';
        $string .= '<option value="8">' . get_string('august', 'block_roomscheduler') . '</option>';
        $string .= '<option value="9">' . get_string('september', 'block_roomscheduler') . '</option>';
        $string .= '<option value="10">' . get_string('october', 'block_roomscheduler') . '</option>';
        $string .= '<option value="11">' . get_string('november', 'block_roomscheduler') . '</option>';
        $string .= '<option value="12">' . get_string('december', 'block_roomscheduler') . '</option>';
        $string .= '</select>';
        $string .= '</td></tr>';
        $string .= '</table>';
        $string .= '</div>';

        $string .= '</td></tr>';


        $string .= '<tr><td style="border-right:1px dashed #DDDDDD;"><input type="radio" value="weekly" name="' . $formname . '_recurrence" onclick="calendar_toggleRecurrence(this);calendar_apptForm_checkAvailability(\''.$formname.'\');"> ' . get_string('weekly', 'block_roomscheduler') . '</td></tr>';
        $string .= '<tr><td style="border-right:1px dashed #DDDDDD;"><input type="radio" value="monthly" name="' . $formname . '_recurrence" onclick="calendar_toggleRecurrence(this);calendar_apptForm_checkAvailability(\''.$formname.'\');"> ' . get_string('monthly', 'block_roomscheduler') . '</td></tr>';
        $string .= '<tr><td style="border-right:1px dashed #DDDDDD;"><input type="radio" value="yearly" name="' . $formname . '_recurrence" onclick="calendar_toggleRecurrence(this);calendar_apptForm_checkAvailability(\''.$formname.'\');"> ' . get_string('yearly', 'block_roomscheduler') . '</td></tr>';

        //Range
        //20% before
        //End after x occurences
        $string .= '<table style="width:100%;">';
        $string .= '<tr style="border-top:1px dashed #DDDDDD;">';
        $string .= '<td style="width:4%;"><input type="radio" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="endafter" name="' . $formname . '_range" CHECKED> ' . get_string('endafter', 'block_roomscheduler') . ':</td>';
        $string .= '<td style="width:22%;"><input type="text" onkeyup="calendar_apptForm_checkAvailability(\''.$formname.'\');" name="' . $formname . '_endafteroccurences" size="1" value="10"> ' . get_string('occurences', 'block_roomscheduler') . '</td>';
        $string .= '</tr>';

        //End by
        $string .= '<tr>';
        $string .= '<td><input type="radio" onclick="calendar_apptForm_checkAvailability(\''.$formname.'\');" value="endby" name="' . $formname . '_range" />&nbsp;' . get_string('endby', 'block_roomscheduler') . ':</td>';
        $sampledate = '8/15/2012';
        $string .= '<td><input type="text" onkeyup="calendar_apptForm_checkAvailability(\''.$formname.'\');" name="'.$formname.'_endbydate" value="'.$sampledate.'" size="8" /> </td>';
        $string .= '</tr>';

        //Status window
        $string .= '<tr><td colspan="2">';
        $string .= '<div class="calendar_apptForm_status" id="' . $formname . '_status_2">';
        $string .= '</div>';
        $string .= '</td></tr>';

        $string .= '</table>';
        $string .= '</div>';    //End of reccurence form
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
        return apptForm::$formname;
    }
}

?>
