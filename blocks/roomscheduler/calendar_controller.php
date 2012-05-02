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
require_once('calendar.php');

$id = required_param('id',PARAM_TEXT);
$function = required_param('function',PARAM_TEXT);
$params = required_param('params',PARAM_TEXT);

$parameters = explode(',',$params);

$cal = unserialize($_SESSION[$id]);

$no_parameters = sizeof($parameters);

switch($no_parameters){
    case 0:
        $cal->$function();
        break;
    case 1:
        $cal->$function($parameters[0]);
        break;
    case 2:
        $cal->$function($parameters[0],$parameters[1]);
        break;
}

echo $cal;

?>
