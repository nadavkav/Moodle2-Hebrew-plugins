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

$params = required_param('params',PARAM_TEXT);

$parameters = explode(',',$params);

$result = recurrence_check($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4]);

if($result){
    echo '<img src="img/failure.gif">&nbsp;';
    echo get_string('conflict','block_roomscheduler');
    echo ' - ';
    echo $result->subject;
    echo '&nbsp;&nbsp;(';
    echo strftime('%e %B %Y %H:%M', $result->startdate);
    echo ' - ';
    echo strftime('%e %B %Y %H:%M', $result->enddate);
    echo ')';
}
else{
    echo '<img src="img/success.gif">&nbsp;';
    echo get_string('noconflict','block_roomscheduler');
}

?>
