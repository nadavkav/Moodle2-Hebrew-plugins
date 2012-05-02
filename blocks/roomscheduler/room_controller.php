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
require_once('lib.php');

$action = required_param('action',PARAM_TEXT);

//New entry into DB
if($action=='new')
{
    $name = required_param('room',PARAM_TEXT);
    $resources = optional_param('resources','',PARAM_TEXT);
    $course = required_param('course',PARAM_INT);
    $reservable = optional_param('reservable',0,PARAM_INT);


    $insertobj = new stdClass();

    $insertobj->name = $name;
    $insertobj->resources = $resources;
    $insertobj->reservable = $reservable;

    $DB->insert_record('roomscheduler_rooms', $insertobj);

    $url = new moodle_url('/blocks/roomscheduler/rooms.php', array('course'=>$course));
    redirect($url);
}
else if($action=='edit')
{
    $name = required_param('room',PARAM_TEXT);
    $resources = optional_param('resources','',PARAM_TEXT);
    $course = required_param('course',PARAM_INT);
    $room = required_param('room_id',PARAM_INT);
    $reservable = optional_param('reservable',0,PARAM_INT);


    $updateobj = new stdClass();
    $updateobj->id = $room;
    $updateobj->name = $name;
    $updateobj->resources = $resources;
    $updateobj->reservable = $reservable;

    $DB->update_record('roomscheduler_rooms', $updateobj);

    $url = new moodle_url('/blocks/roomscheduler/rooms.php', array('course'=>$course));
    redirect($url);
}
else if($action=='delete')
{
    $room = required_param('room_id',PARAM_INT);
    $course = required_param('course',PARAM_INT);

    $updateobj = new stdClass();
    $updateobj->id = $room;
    $updateobj->active = 0;

    $DB->update_record('roomscheduler_rooms', $updateobj);

    $url = new moodle_url('/blocks/roomscheduler/rooms.php', array('course'=>$course));
    redirect($url);
}


?>
