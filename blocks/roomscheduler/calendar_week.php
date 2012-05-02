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
 * @copyright 2011 Raymond Wainman, Dustin Durand - University of Alberta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calendar_week extends calendar {
    /* INSTANCE VARIABLES */

    protected $start_time;
    protected $end_time;
    protected $instance;

    /* STATIC VARIABLES */
    protected $count;

    /* CONSTRUCTOR */

    /**
     * Initializes day calendar
     *
     * @param int $room - id from roomscheduler_rooms table
     * @param int $start_time - 0 to 23 representing start time of displayed content
     * @param int $end_time - 0 to 23 representing end time of displayed content
     * @param int $focus_time - focus time of calendar, defaults to time()
     */
    public function __construct($room,$course, $start_time=8, $end_time=5, $focus_time=null) {
        parent::__construct($room, $focus_time);

        $this->course = $course;

        if ($start_time > $end_time) {
            $this->start_time = $end_time;
            $this->end_time = $start_time;
        } else {
            $this->start_time = $start_time;
            $this->end_time = $end_time;
        }

        //Set instance of the calendar
        if ($this->count == null) {
            $this->count = 0;
        } else {
            $this->count++;
        }
        $this->instance = 'calendar_day_' . $this->count;
    }

    /* IMPLEMENTED FUNCTIONS */

    /**
     * Renders the table into HTML markup.
     *
     * @return string $string HTML markup
     */
    public function __toString() {
        global $OUTPUT, $DB, $COURSE, $CFG,$USER,$PAGE;




         $context = get_context_instance(CONTEXT_COURSE, $this->course);
        $PAGE->set_context($context);
        $room_obj = $DB->get_record('roomscheduler_rooms', array('id' => $this->room));

        $string = '<div id="' . $this->get_instance() . '">';

        //Mode picker
        $string .= '<div class="calendar_day" style="margin-right:10px;padding:0px;border:0;width:20%;">';
        $string .= '<table style="width:100%;margin:0;padding:0;"><tr>';
        $string .= '<td class="calendar_side_picker" style="border-bottom:0;" onclick="window.location.href=\'' . $CFG->wwwroot . '/blocks/roomscheduler/room.php?course=' . $this->course . '&room=' . $this->room . '&time=' . $this->focus_time . '\';">' . get_string('day', 'block_roomscheduler') . '</td>';
        $string .= '<td class="calendar_side_picker" style="background-color:#DDDDDD;border-bottom:0;">' . get_string('week', 'block_roomscheduler') . '</td>';
        $string .= '<td class="calendar_side_picker" style="border-bottom:0;" onclick="window.location.href=\'' . $CFG->wwwroot . '/blocks/roomscheduler/room_calendar.php?course=' . $this->course . '&room=' . $this->room . '&time=' . $this->focus_time . '\';">' . get_string('month', 'block_roomscheduler') . '</td>';
        $string .= '</tr></table>';
        $string .= '</div>';


        $string .= '<table class="calendar_day" style="width:100%;">';

        //Date
        $string .= '<tr><th colspan="9">';
        $string .= $OUTPUT->pix_icon('t/left', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'previousWeek\',[]);'));
        $string .= '&nbsp;&nbsp;';
        //Get Sunday of week
        $focus_dayofweek = strftime('%w', $this->get_focusTime());

        $sunday = $this->get_focusTime() - ($focus_dayofweek * 86400);
        $difference_to_sat = 6 - $focus_dayofweek;
        $saturday = $this->get_focusTime() + ($difference_to_sat * 86400);

        $string .= strftime('%B %d, %Y', $sunday);
        $string .= ' - ';
        $string .= strftime('%B %d, %Y', $saturday);
        $string .= '&nbsp;&nbsp;';
        $string .= $OUTPUT->pix_icon('t/right', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'nextWeek\',[]);'));
        $string .= '</th></tr>';

        //Time picker
        $string .= '<tr><td colspan="9" style="font-size:10px;text-align:center;">';
        $string .= calendar_day::calendar_hourdropdown($this->get_startTime(), 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'set_startTime\',[this.value]);');
        $string .= '&nbsp;&nbsp; - &nbsp;&nbsp;';
        $string .= calendar_day::calendar_hourdropdown($this->get_endTime(), 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'set_endTime\',[this.value]);');
        $string .= '</td></tr>';

         if(has_capability('block/roomscheduler:reserve', $context) && isset($room_obj) && !$room_obj->reservable && !has_capability('block/roomscheduler:manage', $context)){
        $string .= '<tr><th colspan="9">';
        $string .= '<font size="1" color="red">To book this book this room please contant:</font>' . $this->email_icon($room_obj);
        $string .= '</th></tr>';

        }

        //Days of week
        $string .= '<tr><th style="width:9%;"></th>';
        for ($i = 0; $i < 7; $i++) {
            $string .= '<th style="width:13%">' . strftime('%A', $sunday + ($i * 86400)) . '</th>';
        }
        $string .= '<td style="width:2%;"></td>';
        $string .= '</tr>';


        $day = date('j', $sunday);
        $month = date('n', $sunday);
        $year = date('Y', $sunday);

        for ($hour = $this->get_startTime(); $hour < $this->get_endTime(); $hour++) {

            for ($minute = 0; $minute < 60; $minute+=10) {
                if ($minute == 0) {
                    $minute = '00';
                    $string .= '<tr class="calendar_tophour">';
                    $string .= '<td rowspan="6" style="width:7%;" valign="top" id="calendar_time_' . $hour . '"></td>';
                } else {
                    $string .= '<tr>';
                }

                for ($dayofweek = 0; $dayofweek < 7; $dayofweek++) {

                    $timestamp = mktime($hour, $minute, 0, $month, $day + $dayofweek, $year);

                    if ($reservation = get_reservation_byTime($this->room, $timestamp)) {
                        $user = $DB->get_record('user', array('id' => $reservation->meetingorganizer));

                        //Set style of calendar event
                        if ($reservation->categories == '') {
                            $category = 'default';
                        } else {
                            $category = $reservation->categories;
                        }

                        //Top portion
                        if ($timestamp == $reservation->startdate) {
                            $string .= '<td class="calendar_' . $category . '_top">';
                            $starttimedisplay = strftime('%H:%M', $reservation->startdate);
                            $endtimedisplay = strftime('%H:%M', $reservation->enddate);
                            if ($reservation->alldayevent == 1) {
                                $string .= '&nbsp;' . get_string('alldayevent', 'block_roomscheduler');
                                $string .= '-' . strftime('%h %d', $reservation->startdate);
                                $string .= '&nbsp;' . get_string('to', 'block_roomscheduler') . '&nbsp;';
                                $string .= strftime('%h %d', $reservation->enddate - 600);
                            } else {
                                $string .= '&nbsp;' . $starttimedisplay . '-' . $endtimedisplay;
                            }
                            if(has_capability('block/roomscheduler:manage', $context) || $reservation->meetingorganizer == $USER->id){
                            $string .= '&nbsp;<span style="font-weight:normal;"><a href="" onclick="calendar_removeAppt(\'' . $reservation->id . '\',\'' . get_string('reservationdelete', 'block_roomscheduler') . '\');">[' . get_string('remove', 'block_roomscheduler') . ']</a></span>';
                            }
                            $string .= '</td>';
                        }
                        else if($timestamp == $reservation->startdate + 600){
                            $string .= '<td class="calendar_' . $category . '_top">';
                            $string .= '&nbsp;'.$reservation->subject;
                            $string .= '</td>';
                        }
                        else if($timestamp == $reservation->startdate + 1200){
                            $string .= '<td class="calendar_' . $category . '_bottom">';
                            $string .= '&nbsp;' . $reservation->description;
                            $string .= '</td>';
                        }
                        //Bottom portion
                        else if ($timestamp == $reservation->enddate - 600) {
                            $string .= '<td class="calendar_' . $category . '_bottom">';
                            $string .= '</td>';
                        }
                        //Middle portion
                        else {
                            $string .= '<td class="calendar_' . $category . '_middle">';
                            $string .= '</td>';
                        }
                    }
                    else{
                        $string .= '<td style="font-size:10px;"';
                        

                       if(has_capability('block/roomscheduler:reserve', $context) && isset($room_obj) && $room_obj->reservable){
                        $string .='onmousedown="calendar_startAppt(this)" onmouseover="hoverAppt(this)" onmouseup="calendar_endAppt(this)"';
                       } elseif(has_capability('block/roomscheduler:manage', $context)) {
                        $string .='onmousedown="calendar_startAppt(this)" onmouseover="hoverAppt(this)" onmouseup="calendar_endAppt(this)"';
                       }

                        $string .=' id="' . $timestamp . '";></td>';
                    }

                }
                $string .= '<td style="width:2%;"></td>';
                $string .= '</tr>';
            }
        }

        $string .= '</table>';
        $string .= '</div>';
        $string .= '</div>';
        $this->serialize();

        return $string;
    }

    /* INSTANCE FUNCTIONS */

    /**
     * Seralizes itself and puts itself into the $_SESSION global variable
     * as $_SESSION['calendar_day_$instance']
     */
    public function serialize() {
        $_SESSION[$this->get_instance()] = serialize($this);
    }

    /**
     * Gets the calendar's instance
     *
     * @return int $instance
     */
    public function get_instance() {
        return $this->instance;
    }

    /**
     * Advances calendar to the next day
     */
    public function nextWeek() {
        $this->focus_time = $this->focus_time + (86400 * 7);
    }

    /**
     * Backs up calendar to previous day
     */
    public function previousWeek() {
        $this->focus_time = $this->focus_time - (86400 * 7);
    }

    /**
     * Set the start time of the calendar
     *
     * @param int $start_time - value from 0 to 23
     */
    public function set_startTime($start_time) {
        if ($start_time > $this->end_time) {
            $this->end_time = $start_time;
        } else {
            $this->start_time = $start_time;
        }
    }

    /**
     * Get current start time of the calendar
     *
     * @return int $start_time
     */
    public function get_startTime() {
        return $this->start_time;
    }

    /**
     * Set the end time of the calendar
     *
     * @param int $end_time - value from 0 to 23
     */
    public function set_endTime($end_time) {
        if ($end_time < $this->start_time) {
            $this->start_time = $end_time;
        } else {
            $this->end_time = $end_time;
        }
    }

    /**
     * Get current end time of the calendar
     *
     * @return int $end_time
     */
    public function get_endTime() {
        return $this->end_time;
    }

    /**
     * Sets the start and end time of the calendar
     *
     * @param int $start_time - value from 0 to 23
     * @param int $end_time - value form 0 to 23
     */
    public function set_time($start_time, $end_time) {
        if ($start_time > $end_time) {
            $this->start_time = $end_time;
            $this->end_time = $start_time;
        } else {
            $this->start_time = $start_time;
            $this->end_time = $end_time;
        }
    }

    /**
     * Sets the calendar to a standard work day - from 8 AM to 5 PM
     */
    public function standardWorkDay() {
        $this->set_time(8, 17);
    }

    /* STATIC FUNCTIONS */

    /**
     * Returns the markup needed for an hour selection dropdown
     *
     * @param int $current hour from 0 to 23
     * @param string $onchange javascript to be called when dropdown is changed
     *
     * @return string HTML markup for dropdown menu
     */
    public static function calendar_hourdropdown($current, $onchange) {
        $string = '<select onChange="' . $onchange . '">';

        for ($hour = 0; $hour <= 24; $hour++) {
            $string .= '<option value="' . $hour . '" ';
            if ($current == $hour) {
                $string .= 'SELECTED';
            }
            $string .= '>' . $hour . 'h</option>';
        }

        $string .= '</select>';

        return $string;
    }

     private function email_icon($room){
        global $USER, $CFG;

if(!isset($room)){
    return "FAIL";
}


$email = $CFG->roomscheduler_manage_email;
$subject = get_string('book_room_email_subject','block_roomscheduler');

$information = get_string('email_room','block_roomscheduler')." ".$room->name . "%0A";
$information .= get_string('email_start','block_roomscheduler'). " _______________________________ %0A";
$information .= get_string('email_end','block_roomscheduler').   " _______________________________ %0A";
$information .= get_string('email_reason','block_roomscheduler')." _______________________________%0A%0A";

$body = get_string('book_room_email_body','block_roomscheduler') . $information;
$body .= get_string('book_room_email_close','block_roomscheduler') . $USER->firstname ." ".$USER->lastname ;


return '<a href="mailto:'.$email.'?subject='.$subject.'&body='.$body.'" border="0"><img src="img/email.png"></a>';

    }

}

?>
