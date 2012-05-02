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
require_once('calendar.php');

/* PARAMETERS */
$course = required_param('course', PARAM_INT);
$room = required_param('room', PARAM_INT);
$time = optional_param('time', time(), PARAM_INT);

$room_obj = $DB->get_record('roomscheduler_rooms', array('id'=>$room));

/* CONTEXT AND SECURITY */
$coursecontext = get_context_instance(CONTEXT_COURSE, $course);
$courseobj = $DB->get_record('course', array('id' => $course));
$personalcontext = get_context_instance(CONTEXT_USER, $USER->id);
require_login();

/* HEADER */
$navlinks[] = array('name' => get_string('pluginname', 'block_roomscheduler'), 'link' => '/blocks/roomscheduler/rooms.php?course=' . $course, 'type' => 'misc');
$navlinks[] = array('name' => $room_obj->name, 'link' => '/blocks/roomscheduler/room_week.php?course='.$course.'&room='.$room_obj->id.'&time='.$time, 'type' => 'misc');
$navigation = build_navigation($navlinks);
$PAGE->set_course($courseobj);
$PAGE->set_url('/blocks/roomscheduler/room_week.php', array('course' => $course, 'room'=>$room));
$PAGE->set_title(get_string('pluginname', 'block_roomscheduler').' - '.$room_obj->name);
$PAGE->set_heading(get_string('pluginname', 'block_roomscheduler').' - '.$room_obj->name);

$PAGE->requires->js('/blocks/roomscheduler/fancybox/jquery.min.js');
$PAGE->requires->js('/blocks/roomscheduler/fancybox/jquery.fancybox-1.3.1.pack.js');
$PAGE->requires->js('/blocks/roomscheduler/fancybox/roomscheduler.js');
$PAGE->requires->css('/blocks/roomscheduler/fancybox/jquery.fancybox-1.3.1.css');
$PAGE->requires->js('/blocks/roomscheduler/roomscheduler.js');
$PAGE->requires->css('/blocks/roomscheduler/style.css');

echo $OUTPUT->header();

/* CONTENT */
echo $OUTPUT->heading($room_obj->name);

echo '<div style="width:90%;margin-left:auto;margin-right:auto;">';

$cal = new calendar_week($room,$course, 8, 17, $time );
echo $cal;
$cal->render_apptForm();

echo '</div>';


/* FOOTER */
echo $OUTPUT->footer();

?>
