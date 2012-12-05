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
require_once('apptForm_Edit.php');

class calendar_month extends calendar {
    /* INSTANCE VARIABLES */

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
    public function __construct($room, $focus_time=null) {
        parent::__construct($room, $focus_time);

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

        global $OUTPUT, $DB, $COURSE, $CFG, $room_obj, $USER, $PAGE;

         $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $PAGE->set_context($context);

        $string = '<div id="' . $this->get_instance() . '">';

        //Mode picker
        $string .= '<div class="calendar_day" style="margin-right:10px;padding:0px;border:0;width:20%;">';
        $string .= '<table style="width:100%;margin:0;padding:0;"><tr>';
        $string .= '<td class="calendar_side_picker" style="border-bottom:0;" onclick="window.location.href=\'' . $CFG->wwwroot . '/blocks/roomscheduler/room.php?course=' . $COURSE->id . '&room=' . $this->room . '&time=' . $this->focus_time . '\';">' . get_string('day', 'block_roomscheduler') . '</td>';
        $string .= '<td class="calendar_side_picker" style="border-bottom:0;" onclick="window.location.href=\'' . $CFG->wwwroot . '/blocks/roomscheduler/room_week.php?course=' . $COURSE->id . '&room=' . $this->room . '&time=' . $this->focus_time . '\';">' . get_string('week', 'block_roomscheduler') . '</td>';
        $string .= '<td class="calendar_side_picker" style="background-color:#DDDDDD;border-bottom:0;" onclick="window.location.href=\'' . $CFG->wwwroot . '/blocks/roomscheduler/room_calendar.php?course=' . $COURSE->id . '&room=' . $this->room . '&time=' . $this->focus_time . '\';">' . get_string('month', 'block_roomscheduler') . '</td>';
        $string .= '</tr></table>';
        $string .= '</div>';

        $string .= '<table style="width:100%;" class="calendar_day">';

        //Month
        $string .= '<tr>';
        $string .= '<th style="font-align:left;">';
        $string .= $OUTPUT->pix_icon('t/left', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'previousMonth\',[]);'));
        $string .= '</th><th colspan="5">';
        $string .= strftime('%B %Y', $this->get_focusTime());
        $string .= '</th><th style="font-align:right;">';
        $string .= $OUTPUT->pix_icon('t/right', '', 'moodle', array('class' => 'calendar_icon', 'onclick' => 'calendar_phpFunction(\'' . $this->get_instance() . '\',\'nextMonth\',[]);'));
        $string .= '</th>';
        $string .= '</tr>';

        //Days of week
        $sunday = strtotime('8/8/2010');    //Use this as a reference point for sunday
        $string .= '<tr class="calendar_minical_daysofweek">';
        for ($day = 0; $day < 7; $day++) {
            $string .= '<th width="14%;">' . strftime('%A', $sunday + (86400 * $day)) . '</th>';
        }
        $string .= '</tr>';

        //Determine which day of the week is the first day of the month
        $month = date('n', $this->get_focusTime());
        $year = date('Y', $this->get_focusTime());
        $dayofweek_first = strftime_dayofweek_compatible(mktime(0, 0, 0, $month, 1, $year));
        $totaldays = date('t', mktime(0, 0, 0, $month, 1, $year));

        //Calculate offset, offset is dayofweek_first (except in the case of sunday)
        if ($dayofweek_first == 7) {
            $dayofweek_first = 0;
        }

        //print 'test: '.strftime('%e');


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
                    $daystamp_end = mktime(0, 0, 0, $month, $count + 1, $year);
                    $string .= '<td style="text-align:right;border:1px solid #DDDDDD;" class="calendar_miniCal_day" onclick="window.location.href=\'room.php?room='.$this->room.'&course='.$COURSE->id.'&time='.$daystamp.'\'" valign="top">';
                    $string .= '<div>';
                    $string .= $count;
                    $string .= '</div>';

                    //Get all reservations for this day

                    $reservations = $DB->get_records_select('roomscheduler_reservations', 'location=\'' . $this->room . '\' AND active=\'1\' AND startdate>=\'' . $daystamp . '\' AND startdate<=\'' . $daystamp_end . '\' ORDER BY startdate ASC');

                    if (count($reservations) > 0) {
                        foreach ($reservations as $reservation) {
                            if ($reservation->categories == '') {
                                $reservation->categories = 'default';
                            }
           
                            $string .= '<div class="calendar_' . $reservation->categories . '_cal">';
                            $starttimedisplay = strftime('%H:%M', $reservation->startdate);
                            $endtimedisplay = strftime('%H:%M', $reservation->enddate);
                            $string .= '&nbsp;' . $starttimedisplay . ' - ' . $endtimedisplay;
                            $string .= ' '.$reservation->subject;
                            if(has_capability('block/roomscheduler:manage', $context)|| $reservation->meetingorganizer == $USER->id){
                            $string .= ' <span style="font-weight:normal;"><a href="" style="color:white;" onclick="calendar_removeAppt(\'' . $reservation->id . '\',\'' . get_string('reservationdelete', 'block_roomscheduler') . '\');">[' . get_string('remove', 'block_roomscheduler') . ']</a></span>';
                            }
                            $string .= '</div>';
                        }
                    }

                    $string .= '</td>';
                    $count++;
                }
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
    public function nextMonth() {
        $this->focus_time = $this->focus_time + (86400 * 30);
    }

    /**
     * Backs up calendar to previous day
     */
    public function previousMonth() {
        $this->focus_time = $this->focus_time - (86400 * 30);
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

    

}

?>
