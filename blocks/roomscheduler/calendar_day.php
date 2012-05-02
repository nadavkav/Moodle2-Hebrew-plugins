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
require_once('lib.php');
require_once('apptForm.php');
require_once('apptForm_Edit.php');

class calendar_day extends calendar {
    /* INSTANCE VARIABLES */

    protected $day;
    protected $start_time;
    protected $end_time;
    protected $show_toolbar;
    protected $miniCal;
    protected $instance;

    /* STATIC VARIABLES */
    protected $count;

    /* CONSTRUCTOR */

    /**
     * Initializes day calendar
     *
     * @param int $room - id from roomscheduler_rooms table
     * @param int $day - UNIX time stamp representing the day wanted
     * @param int $start_time - 0 to 23 representing start time of displayed content
     * @param int $end_time - 0 to 23 representing end time of displayed content
     * @param int $focus_time - focus time of calendar, defaults to time()
     */
    public function __construct($room, $day, $course, $start_time=8, $end_time=5, $show_toolbar=true, $focus_time=null) {
        parent::__construct($room, $focus_time);
        $this->course = $course;
        $this->day = $day;
        $this->miniCal = $day;
        if ($start_time > $end_time) {
            $this->start_time = $end_time;
            $this->end_time = $start_time;
        } else {
            $this->start_time = $start_time;
            $this->end_time = $end_time;
        }

        $this->show_toolbar = $show_toolbar;

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
        global $OUTPUT, $DB, $CFG, $USER,$PAGE;  


        $context = get_context_instance(CONTEXT_COURSE, $this->course);

        $PAGE->set_context($context);

        $this->focus_time = $this->day;

        $string = '<div id="' . $this->get_instance() . '">';


        if ($this->show_toolbar) {
            $cal_style = 'width:70%;float:left;';
            $string .= '<table cellspacing="0" cellpadding="0" style="width:20%;float:left;">';

            //Mode picker
            $string .= '<tr><td style="margin:0;padding:0;">';
            $string .= '<div class="calendar_day" style="margin-right:10px;padding:0px;border:0;">';
            $string .= '<table style="width:100%;margin:0;padding:0;"><tr>';
            $string .= '<td class="calendar_side_picker" style="background-color:#DDDDDD;">' . get_string('day', 'block_roomscheduler') . '</td>';
            $string .= '<td class="calendar_side_picker" onclick="window.location.href=\'' . $CFG->wwwroot . '/blocks/roomscheduler/room_week.php?course=' . $this->course . '&room=' . $this->room . '&time=' . $this->focus_time . '\';">' . get_string('week', 'block_roomscheduler') . '</td>';
            $string .= '<td class="calendar_side_picker" onclick="window.location.href=\'' . $CFG->wwwroot . '/blocks/roomscheduler/room_calendar.php?course=' . $this->course . '&room=' . $this->room . '&time=' . $this->focus_time . '\';">' . get_string('month', 'block_roomscheduler') . '</td>';
            $string .= '</tr></table>';
            $string .= '</div></td></tr>';

            //Mini calendar picker
            $string .= '<tr><td style="margin:0;padding:0;">';
            $string .= '<div class="calendar_day" style="margin-right:10px;margin-top:10px;padding:5px;">';

            $string .= '<table style="font-size:10px;width:100%;">';

            //Month
            $string .= '<tr>';
            $string .= '<th style="font-align:left;">';
            $string .= $OUTPUT->pix_icon('t/left', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'miniCal_previousMonth\',[]);'));
            $string .= '</th><th colspan="5">';
            $string .= strftime('%B %Y', $this->get_miniCal());
            $string .= '</th><th style="font-align:right;">';
            $string .= $OUTPUT->pix_icon('t/right', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'miniCal_nextMonth\',[]);'));
            $string .= '</th>';
            $string .= '</tr>';

            //Days of week
            $sunday = strtotime('8/8/2010');    //Use this as a reference point for sunday
            $string .= '<tr class="calendar_minical_daysofweek">';
            for ($day = 0; $day < 7; $day++) {
                $string .= '<th>' . strftime('%a', $sunday + (86400 * $day)) . '</th>';
            }
            $string .= '</tr>';

            //Determine which day of the week is the first day of the month
            $month = date('n', $this->get_miniCal());
            $year = date('Y', $this->get_miniCal());
            $dayofweek_first = strftime_dayofweek_compatible(mktime(0, 0, 0, $month, 1, $year));
            $totaldays = date('t', mktime(0, 0, 0, $month, 1, $year));

            //Calculate offset, offset is dayofweek_first (except in the case of sunday)
            if ($dayofweek_first == 7) {
                $dayofweek_first = 0;
            }

            //Days
            $count = 1;
            for ($week = 1; $week <= 6; $week++) {
                $string .= '<tr>';
                for ($dayofweek = 0; $dayofweek < 7; $dayofweek++) {
                    //Beginning offset period
                    if ($dayofweek_first > 0) {
                        $dayofweek_first--;
                        $string .= '<td></td>';
                    }
                    //Ending offset period
                    else if ($count > $totaldays) {
                        $string .= '<td></td>';
                    }
                    //Actual month, print out day
                    else {
                        $daystamp = mktime(0, 0, 0, $month, $count, $year);
                        $string .= '<td style="text-align:right;" class="calendar_miniCal_day" onclick="calendar_phpFunction(\'' . $this->get_instance() . '\',\'set_day\',[\'' . $daystamp . '\']);">';
                        $string .= '<div ';
                        if ($month == date('n') && $count == date('j') && $year == date('Y')) {
                            if ($month == date('n', $this->get_day()) && $count == date('j', $this->get_day()) && $year == date('Y', $this->get_day()))
                                $string .= 'class="calendar_miniCal_todaySelected"';
                            else
                                $string .= 'class="calendar_miniCal_today"';
                        }
                        else if ($month == date('n', $this->get_day()) && $count == date('j', $this->get_day()) && $year == date('Y', $this->get_day())) {
                            $string .= 'class="calendar_miniCal_selected"';
                        }

                        $string .= '>';
                        $string .= $count;
                        $string .= '</div></td>';
                        $count++;
                    }
                }
                $string .= '</tr>';
            }

            $string .= '</table>';

            $string .= '</div>';
            $string .= '</td></tr>';

            //Resources
            $room_obj = $DB->get_record('roomscheduler_rooms', array('id' => $this->room));
            if ($room_obj->resources != '') {
                $string .= '<tr><td style="margin:0;padding:0;">';
                $string .= '<div class="calendar_day" style="margin-right:10px;margin-top:10px;padding:5px;">';
                $resources = explode(',', $room_obj->resources);
                $string .= '<ul style="margin-top:0;margin-bottom:0;">';
                foreach ($resources as $resource) {
                    $resource = trim($resource);
                    $string .= '<li>' . $resource . '</li>';
                }
                $string .= '</ul>';
                $string .= '</div>';
                $string .= '</td></tr>';
            }

            //Other rooms picker
            $string .= '<tr><td style="margin:0;padding:0;">';
            $string .= '<div class="calendar_day" style="margin-right:10px;margin-top:10px;padding:5px;">';
            $rooms = $DB->get_records('roomscheduler_rooms', array('active' => 1), 'name ASC');
            $string .= '<select onchange="window.location.href=(this.options[this.selectedIndex].value);">';
            foreach ($rooms as $room) {
                $string .= '<option value="' . $CFG->wwwroot . '/blocks/roomscheduler/room.php?time='.$this->focus_time.'&course=' . $this->course . '&room=' . $room->id . '"';
                if ($this->room == $room->id) {
                    $string .= 'SELECTED';
                }
                $string .= '>' . $room->name . '</option>';
            }
            $string .= '</select>';
            $string .= '</div>';
            $string .= '</td></tr>';

            $string .= '</table>';
        } else {
            $cal_style = 'width:100%;';
        }

        $string .= '<table class="calendar_day" style="' . $cal_style . '">';


     
        //Date

        $string .= '<tr><th colspan="3">';
        $string .= $OUTPUT->pix_icon('t/left', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'previousDay\',[]);'));
        $string .= '&nbsp;&nbsp;';
        $string .= strftime('%B %d, %Y', $this->get_day());
        $string .= '&nbsp;&nbsp;';
        $string .= $OUTPUT->pix_icon('t/right', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'nextDay\',[]);'));
        $string .= '</th></tr>';       
          

        //Time picker
        $string .= '<tr><td colspan="3" style="font-size:10px;text-align:center;">';
        $string .= calendar_day::calendar_hourdropdown($this->get_startTime(), 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'set_startTime\',[this.value]);');
        $string .= '&nbsp;&nbsp; - &nbsp;&nbsp;';
        $string .= calendar_day::calendar_hourdropdown($this->get_endTime(), 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'set_endTime\',[this.value]);');
        $string .= '</td></tr>';

         if(has_capability('block/roomscheduler:reserve', $context) && isset($room_obj) && !$room_obj->reservable && !has_capability('block/roomscheduler:manage', $context)){
        $string .= '<tr><th colspan="3">';
        $string .= '<font size="1" color="red">To book this book this room please contant:</font>' . $this->email_icon($room_obj);
        $string .= '</th></tr>';

        }

        //Day of week + day
        $string .= '<tr><th style="text-align:left;">';
        $string .= strftime('%A', $this->get_day());
        $string .= '</th><th></th><th style="text-align:right;">';
        $string .= strftime('%d', $this->get_day());
        $string .= '</th></tr>';

        $day = date('j', $this->get_day());
        $month = date('n', $this->get_day());
        $year = date('Y', $this->get_day());

        $block_counter = 1;   //Count how many blocks are printed out

        for ($hour = $this->get_startTime(); $hour < $this->get_endTime(); $hour++) {
            $count = 1;
            for ($minute = 0; $minute < 60; $minute+=10) {
                if ($minute == 0) {
                    $minute = '00';
                    $string .= '<tr class="calendar_tophour">';
                    $string .= '<td rowspan="6" style="width:20%;" valign="top" id="calendar_time_' . $hour . '"></td>';
                } else {
                    $string .= '<tr>';
                }

                $timestamp = mktime($hour, $minute, 0, $month, $day, $year);
                if ($reservation = get_reservation_byTime($this->room, $timestamp)) {
                    $user = $DB->get_record('user', array('id' => $reservation->meetingorganizer));

                    //Set style of calendar event
                    if ($reservation->categories == '') {
                        $category = 'default';
                    } else {
                        $category = $reservation->categories;
                    }

                    //Top portion
                    if ($timestamp == $reservation->startdate || $block_counter == 1) {
                        $string .= '<td class="calendar_' . $category . '_top">';
                        $starttimedisplay = strftime('%H:%M', $reservation->startdate);
                        $endtimedisplay = strftime('%H:%M', $reservation->enddate);
                        if ($reservation->alldayevent == 1) {
                            $string .= '&nbsp;' . get_string('alldayevent', 'block_roomscheduler');
                            $string .= ' - ' . strftime('%h %d', $reservation->startdate);
                            $string .= '&nbsp;' . get_string('to', 'block_roomscheduler') . '&nbsp;';
                            $string .= strftime('%h %d', $reservation->enddate - 600);
                        } else {
                            $string .= '&nbsp;' . $starttimedisplay . ' - ' . $endtimedisplay;
                        }
                        $string .= ' : ' . $reservation->subject;

                        if (has_capability('block/roomscheduler:manage', $context) || $reservation->meetingorganizer == $USER->id) {
                            $string .= '&nbsp;&nbsp;<span style="font-weight:normal;"><a href="" onclick="calendar_removeAppt(\'' . $reservation->id . '\',\'' . get_string('reservationdelete', 'block_roomscheduler') . '\');">[' . get_string('remove', 'block_roomscheduler') . ']</a></span>';
                            $string .= '&nbsp;&nbsp;<span style="font-weight:normal;"><a href="#" onclick="calendar_editAppt(\'' . $reservation->id . '\',\'' . apptForm_edit::apptForm_formName() . '\', \'single\');">[' . get_string('edit', 'block_roomscheduler') . ']</a></span>';
                            if ($reservation->recurrence_id > 0) {
                               $string .= '&nbsp;&nbsp;<span style="font-weight:normal;"><a href="#" onclick="calendar_editAppt(\'' . $reservation->id . '\',\'' . apptForm_recursiveEdit::apptForm_formName() . '\', \'recursive\');">[' . get_string('editall', 'block_roomscheduler') . ']</a></span>';
                                $string .= '&nbsp;&nbsp;<span style="font-weight:normal;"><a href="" onclick="calendar_removeAppts(\'' . $reservation->id . '\',\'' . get_string('reservationdeletes', 'block_roomscheduler') . '\');">[' . get_string('removeall', 'block_roomscheduler') . ']</a></span>';
                            }
                        }

                        if($reservation->confirm==0){
                            $string .= '&nbsp;&nbsp;&nbsp;<span style="font-weight:normal;">';
                            if(has_capability('block/roomscheduler:manage', $context)){
                              //  $string .= '<a href="" onclick="calendar_confirmAppt(\'' . $reservation->id . '\',\'' . get_string('confirmconfirmation', 'block_roomscheduler') . '\');">';
                            }
                           // $string .= '['.get_string('unconfirmed','block_roomscheduler').']';
                            if(has_capability('block/roomscheduler:manage', $context)){
                           //     $string .= '</a>';
                            }
                            $string .= '</span>';
                        }

                        if($reservation->recurrence_id!=0 && $reservation->confirm==0 && has_capability('block/roomscheduler:manage',$context)){
                          //  $string .= '&nbsp;&nbsp;&nbsp;<span style="font-weight:normal;"><a href="" onclick="calendar_confirmAppts(\'' . $reservation->id . '\',\'' . get_string('confirmconfirmations', 'block_roomscheduler') . '\');">['.get_string('confirmall','block_roomscheduler').']</a></span>';
                        }

                        $string .= '</td>';
                        $block_counter++;
                    }
                    //Bottom portion
                    else if ($timestamp == $reservation->enddate - 600) {
                        $string .= '<td class="calendar_' . $category . '_bottom">';
                        if ($block_counter == 2) {
                            $string .= '&nbsp;' . $reservation->description;
                        }
                        /*
                          else if($block_counter==3){
                          $string .= '&nbsp;'.$user->firstname.' '.$user->lastname;
                          }
                         *
                         */
                        $string .= '</td>';
                        $block_counter = 1;
                    }
                    //Middle portion
                    else {
                        $string .= '<td class="calendar_' . $category . '_middle">';
                        if ($block_counter == 2) {
                            $string .= '&nbsp;' . $reservation->description;
                        }
                        /*
                          else if($block_counter==3){
                          $string .= '&nbsp;'.$user->firstname.' '.$user->lastname;
                          }
                         *
                         */
                        $string .= '</td>';
                        $block_counter++;
                    }
                } else { //blank row
                    $string .= '<td style="font-size:10px;"';


                    if(has_capability('block/roomscheduler:reserve', $context) && isset($room_obj) && $room_obj->reservable){
                        $string .=' onmousedown="calendar_startAppt(this);" onmouseover="hoverAppt(this);" onmouseup="calendar_endAppt(this);"';
                       } elseif(has_capability('block/roomscheduler:manage', $context)) {
                        $string .=' onmousedown="calendar_startAppt(this);" onmouseover="hoverAppt(this);" onmouseup="calendar_endAppt(this);"';
                       }

                    $string .= ' id="' . $timestamp . '"></td>';
                }

                $string .= '<td style="width:10%;"></td></tr>';

                $count++;
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
     * Set mini calendar time
     *
     * @param int $miniCal mini calendar time UNIX stamp
     */
    public function set_miniCal($miniCal) {
        $this->miniCal = $miniCal;
    }

    /**
     * Returns time of mini calendar focus
     *
     * @return int $miniCal UNIX timestamp
     */
    public function get_miniCal() {
        return $this->miniCal;
    }

    /**
     * Set the day of the calendar
     *
     * @param int $day - UNIX timestamp
     */
    public function set_day($day) {
        $this->day = $day;
    }

    /**
     * Get current day of the calendar
     *
     * @return int $focus_time
     */
    public function get_day() {
        return $this->day;
    }

    /**
     * Advances calendar to the next day
     */
    public function nextDay() {
        $this->day = $this->day + 86400;
    }

    /**
     * Backs up calendar to previous day
     */
    public function previousDay() {
        $this->day = $this->day - 86400;
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

    /**
     * Advances the mini calendar by one month
     */
    public function miniCal_nextMonth() {
        $this->set_miniCal($this->get_miniCal() + (30 * 86400));
    }

    /**
     * Backs up the mini calendar by one month
     */
    public function miniCal_previousMonth() {
        $this->set_miniCal($this->get_miniCal() - (30 * 86400));
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
