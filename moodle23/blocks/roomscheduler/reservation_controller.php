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
require_once('reservations_lib.php');

$function = required_param('function', PARAM_TEXT);
$params = optional_param('params', '', PARAM_TEXT);	//Javascript array (comma delimited)

$parameters = explode(',',$params);

$no_parameters = sizeof($parameters);

switch($no_parameters){
    case 0:
        $function();
        break;
    case 1:
        $function($parameters[0]);
        break;
    case 2:
        $function($parameters[0],$parameters[1]);
        break;
    case 3:
        $function($parameters[0],$parameters[1],$parameters[2]);
        break;
    case 4:
        $function($parameters[0],$parameters[1],$parameters[2],$parameters[3]);
        break;
    case 5:
        $function($parameters[0],$parameters[1],$parameters[2],$parameters[3],$parameters[4]);
        break;
    case 6:
        $function($parameters[0],$parameters[1],$parameters[2],$parameters[3],$parameters[4],$parameters[5]);
        break;
    case 7:
        $function($parameters[0],$parameters[1],$parameters[2],$parameters[3],$parameters[4],$parameters[5],$parameters[6]);
        break;
    case 8:
        $function($parameters[0],$parameters[1],$parameters[2],$parameters[3],$parameters[4],$parameters[5],$parameters[6],$parameters[7]);
        break;
    case 9:
        $function($parameters[0],$parameters[1],$parameters[2],$parameters[3],$parameters[4],$parameters[5],$parameters[6],$parameters[7],$parameters[8]);
        break;
    case 10:
        $function($parameters[0],$parameters[1],$parameters[2],$parameters[3],$parameters[4],$parameters[5],$parameters[6],$parameters[7],$parameters[8],$parameters[9]);
        break;
}

?>
