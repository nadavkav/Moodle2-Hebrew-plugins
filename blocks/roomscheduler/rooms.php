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
require_once('reservations_lib.php');
require_once('rooms_avaliable_form.php');


/* PARAMETERS */
$course = required_param('course', PARAM_INT);

/* CONTEXT AND SECURITY */
$coursecontext = get_context_instance(CONTEXT_COURSE, $course);
$courseobj = $DB->get_record('course', array('id' => $course));
$personalcontext = get_context_instance(CONTEXT_USER, $USER->id);
require_login();

/* HEADER */
$navlinks[] = array('name' => get_string('pluginname', 'block_roomscheduler'), 'link' => '/blocks/roomscheduler/rooms.php?course=' . $course, 'type' => 'misc');
$navigation = build_navigation($navlinks);
$PAGE->set_course($courseobj);
$PAGE->set_url('/blocks/roomscheduler/rooms.php', array('course' => $course));
$PAGE->set_title(get_string('pluginname', 'block_roomscheduler'));
$PAGE->set_heading(get_string('pluginname', 'block_roomscheduler'));

//$PAGE->requires->js('/blocks/roomscheduler/fancybox/jquery.min.js');
$PAGE->requires->js('/blocks/roomscheduler/fancybox/jquery.fancybox-1.3.1.pack.js');
$PAGE->requires->js('/blocks/roomscheduler/fancybox/roomscheduler.js');
$PAGE->requires->css('/blocks/roomscheduler/fancybox/jquery.fancybox-1.3.1.css');
$PAGE->requires->js('/blocks/roomscheduler/roomscheduler.js');
$PAGE->requires->css('/blocks/roomscheduler/style.css');
$PAGE->requires->css('/blocks/roomscheduler/rooms_available.css');
$PAGE->requires->js('/blocks/roomscheduler/rooms_available.js');
//$PAGE->requires->css('/blocks/roomscheduler/calendar/css/smoothness/jquery-ui-1.8.10.custom.css');
//$PAGE->requires->js('/blocks/roomscheduler/calendar/js/jquery-1.4.4.min.js');
//$PAGE->requires->js('/blocks/roomscheduler/calendar/js/jquery-ui-1.8.10.custom.min.js');
print '<link type="text/css" href="calendar/css/smoothness/jquery-ui-1.8.10.custom.css" rel="Stylesheet" />
<script type="text/javascript" src="calendar/js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="calendar/js/jquery-ui-1.8.10.custom.min.js"></script>';

echo $OUTPUT->header();

//Form to find avaliable forms
$Avaliable_Rooms_FORM = new rooms_avaliable_form();
$Avaliable_Rooms_FORM->setCourseID($course);
echo $Avaliable_Rooms_FORM;

/* CONTENT */


echo $OUTPUT->heading(get_string('rooms', 'block_roomscheduler'));

echo '<div style="width:90%;margin-left:auto;margin-right:auto;">';

echo '<div style="width:40%;float:left;">';



echo '<table class="generaltable" style="width:100%;margin-left:auto;margin-right:auto;">';

//avaliable rooms calendar-------------------------------------------------------
if(has_capability('block/roomscheduler:reserve', $coursecontext) || has_capability('block/roomscheduler:manage', $coursecontext)){

echo '<tr>';
echo '<th colspan="1" class="header">' . get_string('avaliableroom', 'block_roomscheduler') . '</th>';
echo '</tr>';

echo '<tr>';
echo '<td  colspan="1" class="cell" colspan="2" style="font-size:10px;">';
//echo '<a onclick="rooms_avaliable_popup(\''.rooms_avaliable_form::apptForm_formName().'\');">';
//echo '&nbsp;' . get_string('avaliableroom', 'block_roomscheduler') . '</a>';

print<<<HERE
<script>
	$(function() {
$( "#datepicker" ).datepicker({
numberOfMonths: 2, showCurrentAtPos: 0,

onSelect:
function(dateText, inst) {
HERE;

print 'var start_date = document.getElementsByName(\''.rooms_avaliable_form::apptForm_formName().'_startTime_date\');';
print 'var end_date = document.getElementsByName(\''.rooms_avaliable_form::apptForm_formName().'_endTime_date\');';

print<<<HERE

   if(start_date && start_date[0]){
start_date[0].value = dateText;
}
if(end_date && end_date[0]){
end_date[0].value = dateText;
}

HERE;
print 'rooms_avaliable_popup(\''.rooms_avaliable_form::apptForm_formName().'\');';
print<<<HERE
}


});
    });

	</script>



<center><div id="datepicker"></div></center>
HERE;




echo '</td>';
echo '</tr></br>';
echo '</table>';
 }//---------------------------------------------------------


 echo '<table class="generaltable" style="width:100%;margin-left:auto;margin-right:auto;">';

echo '<tr>';
echo '<th class="header">' . get_string('room', 'block_roomscheduler') . '</th>';
echo '<th class="header">' . get_string('status', 'block_roomscheduler') . '</th>';
echo '</tr>';

$rooms = $DB->get_records('roomscheduler_rooms', array('active' => 1), 'name ASC');
foreach ($rooms as $room) {
    echo '<tr style="font-size:10px;" class="room_row" onclick="window.location.href=\''.$CFG->wwwroot.'/blocks/roomscheduler/room.php?course='.$course.'&room='.$room->id.'\';" onmouseover="room_preview(\''.$room->id.'\',\''.$course.'\');" onmouseout="room_preview_out();">';
    echo '<td>' . $room->name;
    echo '&nbsp;&nbsp;';

     if(has_capability('block/roomscheduler:manage', $coursecontext)){
    //Edit
    echo $OUTPUT->action_icon('#editroom'.$room->id, new pix_icon('t/edit', get_string('editroom', 'block_roomscheduler')), null, array('class' => 'inline'));

    //Delete
    echo '&nbsp;&nbsp;';
    $url = new moodle_url('/blocks/roomscheduler/room_controller.php', array('action'=>'delete','course'=>$course,'room_id'=>$room->id));
    echo $OUTPUT->action_icon($url, new pix_icon('t/delete', get_string('deleteroom', 'block_roomscheduler')), null, array('onclick' => 'return confirm(\''.get_string('confirmdeleteroom','block_roomscheduler').'\');'));

     }
    echo '</td>';
    echo '<td>';
    if(get_reservation_byTime($room->id, time())){
        echo get_string('reserved','block_roomscheduler');
    }
    else{
        echo get_string('available','block_roomscheduler');
    }
    echo '</td>';
    echo '</tr>';
}

 if(has_capability('block/roomscheduler:manage', $coursecontext)){

//Add room row
echo '<tr>';
echo '<td class="cell" colspan="2" style="font-size:10px;">';
echo '<a href="#newroom" class="inline" title="">';
echo $OUTPUT->pix_icon('t/add', '');
echo '&nbsp;' . get_string('addroom', 'block_roomscheduler') . '</a>';
echo '</td>';
echo '</tr>';
}


 
echo '</table>';
echo '</div>';

 

//Second pane
echo '<div style="width:58%;padding:5px;float:left;height:800px;" id="cal_preview">';
echo '</div>';

echo '</div>';

/*  ADD ROOM FORM   */
echo '<div style="display:none">';
echo roomscheduler_roomForm('newroom', array('action'=>'new','course'=>$course,'reservable'=>1), 'addroom');
echo '</div>';

/*  EDIT ROOM FORM  */
foreach($rooms as $room){
    echo '<div style="display:none">';
    echo roomscheduler_roomForm('editroom'.$room->id, array('action'=>'edit','course'=>$course,'room'=>$room->name,'resources'=>$room->resources,'room_id'=>$room->id,'reservable'=>$room->reservable), 'savechanges');
    echo '</div>';
}



/* FOOTER */
echo $OUTPUT->footer();







/**
 * Render form for room
 *
 * @param string $div_id id attribute for containing div
 * @param object $form associative array containing form values
 * @param string $submit_value string for text on submit button
 * @return string
 */
function roomscheduler_roomForm($div_id, $form, $submit_value) {
    global $CFG;

    $string = '<div id="'.$div_id.'">';
    $string .= '<form action="'.$CFG->wwwroot.'/blocks/roomscheduler/room_controller.php" method="POST" name="'.$div_id.'" onsubmit="return validate_newRoom(this);">';
    //Hidden action field
    $string .= '<input type="hidden" name="action" value="'.$form['action'].'">';
    //Hidden course field
    $string .= '<input type="hidden" name="course" value="'.$form['course'].'">';
    //Hidden room field
    $string .= '<input type="hidden" name="room_id" value="'.$form['room_id'].'">';

    $string .= '<table>';

    //Room name
    $string .= '<tr>';
    $string .= '<th style="text-align:right;" valign="top">'.get_string('room', 'block_roomscheduler').':</th>';
    $string .= '<td><input type="text" id="room" name="room" value="'.$form['room'].'" /><br/>&nbsp;';
    $string .= roomscheduler_error($div_id.'_room_error', 'roomerror');
    $string .= '</td>';
    $string .= '</tr>';

    //Resources
    $string .= '<tr>';
    $string .= '<th style="text-align:right;" valign="top">'.get_string('resources', 'block_roomscheduler').':</th>';
    $string .= '<td><textarea name="resources" id="resources" cols="25">'.$form['resources'].'</textarea>';
    $string .= '<br/><span style="font-size:10px;">'.get_string('resourceshelp', 'block_roomscheduler').'</span></td>';
    $string .= '</tr>';

      //Reserveable!
        $string .= '<tr>';
        $string .= '<th style="text-align:right;">' . get_string('reservable','block_roomscheduler'). ':</th>';
        $string .= '<td>';
        $string .= '<INPUT TYPE="CHECKBOX" NAME="reservable"';
        
        if(isset($form['reservable']) && $form['reservable']){
        $string .='checked="checked"';
        }

    $string .= 'value="1">';
        $string .= '</td></tr>';


    //Button
    $string .= '<tr>';
    $string .= '<th></th>';
    $string .= '<td><input type="submit" value="'.get_string($submit_value, 'block_roomscheduler').'" />';
    $string .= '<td><input type="button" onclick="$.fancybox.close();" value="'.get_string('close', 'block_roomscheduler').'" /></td>';
    $string .= '</tr>';

    $string .= '</table>';
    $string .= '</form>';
    $string .= '</div>';

    return $string;
}

?>
