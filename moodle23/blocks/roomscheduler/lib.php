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

/**
 * Places a hidden error message that can be displayed by javascript.
 * Useful in form validation situations
 *
 * @param string $id html id attribute
 * @param string $errormsg error message identifier (to be accessed in lang file)
 * @return string HTML markup
 */
function roomscheduler_error($id, $errormsg) {
    $string = '<span id="'.$id.'" style="color:red;font-size:10px;display:none;">';
    $string .= get_string($errormsg,'block_roomscheduler');
    $string .= '</span>';

    return $string;
}


/*
 * The php strftime() is dependent on enviroment.
 * The standard function with specified %u doesn't work in a windows enviroment,
 * and some common linux distributions
 *
 * This function simply uses another method if %u format is not supported. Does not
 * guarentee compadibility, but much more reliable
 *
 * @param int $time The time in seconds.
 * @return int $day The day of the week from monday @ 1, to sunday @ 7
 */
function strftime_dayofweek_compatible($time){

        $day = strftime('%u', $time);

        if(!$day){
         $day =  strftime('%w', $time);

         if($day == 0){
           $day = 7;
         }

        }
return $day;
    }


  /*
 * The php strftime() is dependent on enviroment.
 * The standard function with specified %e doesn't work in a windows enviroment,
 * and some common linux distributions
 *
 * This function simply uses another method if %e format is not supported. Does not
 * guarentee compadibility, but much more reliable
 *
 * @param int $time The time in seconds.
 * @return int $day The day of the month.
 */
  function strftime_dayofmonth_compatible($time){

        $day = strftime('%e', $time);

        if(!$day){
         $day =  strftime('%#d', $time);

        }
return $day;
    }


?>
