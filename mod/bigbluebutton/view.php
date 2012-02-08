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

/// This page prints a particular instance of chat

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/mod/bigbluebutton/lib.php');
require_once($CFG->libdir . '/completionlib.php');

$id   = optional_param('id', 0, PARAM_INT);
$c    = optional_param('c', 0, PARAM_INT);
$edit = optional_param('edit', -1, PARAM_BOOL);

if ($id) {
    if (! $cm = get_coursemodule_from_id('bigbluebutton', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
        print_error('coursemisconf');
    }

    if (! $bigbluebutton = $DB->get_record('bigbluebutton', array('id'=>$cm->instance))) {
        print_error('invalidid', 'bigbluebutton');
    }

} else {

    if (! $bigbluebutton = $DB->get_record('bigbluebutton', array('id'=>$cm->instance))) {
        print_error('coursemisconf');
    }
    if (! $course = $DB->get_record('course', array('id'=>$bigbluebutton->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('bigbluebutton', $bigbluebutton->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_course_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$PAGE->set_context($context);

// show some info for guests
if (isguestuser()) {
    $PAGE->set_title(format_string($bigbluebutton->name));
    echo $OUTPUT->header();
    echo $OUTPUT->confirm('<p>'.get_string('noguests', 'bigbluebutton').'</p>'.get_string('liketologin'),
            get_login_url(), $CFG->wwwroot.'/course/view.php?id='.$course->id);

    echo $OUTPUT->footer();
    exit;
}

add_to_log($course->id, 'bigbluebutton', 'view', "view.php?id=$cm->id", $bigbluebutton->id, $cm->id);

$strenterchat    = get_string('enterchat', 'chat');
$stridle         = get_string('idle', 'chat');
$strcurrentusers = get_string('currentusers', 'chat');
$strnextsession  = get_string('nextsession', 'chat');

$title = $course->shortname . ': ' . format_string($chat->name);

// Initialize $PAGE
$PAGE->set_url('/mod/bigbluebutton/view.php', array('id' => $cm->id));
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

/// Print the page header
echo $OUTPUT->header();

/// Check to see if groups are being used here
$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = groups_get_activity_group($cm, true);
groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/bigbluebutton/view.php?id=$cm->id");

// url parameters
$params = array();
if ($currentgroup) {
    $groupselect = " AND groupid = '$currentgroup'";
    $groupparam = "&amp;groupid=$currentgroup";
    $params['groupid'] = $currentgroup;
} else {
    $groupselect = "";
    $groupparam = "";
}

echo $OUTPUT->heading(format_string($bigbluebutton->name));

if ($bigbluebutton->intro) {
    echo $OUTPUT->box(format_module_intro('bigbluebutton', $bigbluebutton, $cm->id), 'generalbox', 'intro');
}


// START HERE

// Get all of the variables required by Web conference
if(isset($CFG->wc_serverhost)) {
  $ip = $CFG->wc_serverhost;
}
if(isset($CFG->wc_securitysalt)) {
  $securitySalt = $CFG->wc_securitysalt;
}
if(isset($CFG->wc_provider)) {
  $provider = $CFG->wc_provider;
}
if(isset($CFG->wc_accountid)) {
  $accountid = $CFG->wc_accountid;
}
if(isset($CFG->wc_accountpwd)) {
  $accountpwd = $CFG->wc_accountpwd;
}
$serverid = "FAILURE";

// See if Moodle can authenticate against the Web conference server
if ($provider=="dualcode") {
  $serverid = dc_authenticate($accountid,$accountpwd);
}

if ($provider=="self" || $serverid!="FAILURE") {

  $fullname     = urlencode($USER->firstname." ".$USER->lastname);
  $meetingname  = urlencode($bigbluebutton->name);
  $meetingid    = urlencode($bigbluebutton->meetingid);
  $attendeePW   = urlencode($bigbluebutton->attendeepw);
  $moderatorPW  = urlencode($bigbluebutton->moderatorpw);
  $newwindow    = urlencode($bigbluebutton->newwindow);
  $welcomeMsg   = urlencode($bigbluebutton->welcomemsg);
  $logoutURL    = urlencode(wc_currentPageURL());
  $myURL                = wc_currentPageURL();
}

// Create string to see if the meeting is running
if ($provider=="self") {
  $isRunningURL = wc_isMeetingRunningURL($ip,$securitySalt,$meetingid);
}
else {
  $isRunningURL = dc_isMeetingRunningURL($accountid,$accountpwd,$meetingid);
}

// Create the meeting
if ($provider=="self") {
  $createResponse = wc_createMeeting($ip,$securitySalt,$meetingname,$meetingid,$attendeePW,$moderatorPW,$welcomeMsg,$logoutURL);
}
else {
  $createResponse = dc_createMeeting($accountid,$accountpwd,$meetingname,$meetingid,$attendeePW,$moderatorPW,$welcomeMsg,$logoutURL);
}

if ($createResponse=="SUCCESS") {

// Determine whether to launch the session in the same window or a new window	
  $newWindowStr = "";
  if ($newwindow=='1') {$newWindowStr = "target=\"_blank\"";}
	
// Create the links to join the meeting
  if ($provider=="self") {
    $joinURL      = wc_joinMeetingURL($ip,$securitySalt,$fullname,$meetingid,$attendeePW);  // as attendee
    $joinURLasMod = wc_joinMeetingURL($ip,$securitySalt,$fullname,$meetingid,$moderatorPW); // as moderator
  }
  else {
    $joinURL 	  = dc_joinMeetingURL($accountid,$accountpwd,$fullname,$meetingid,$attendeePW);  // as attendee
    $joinURLasMod = dc_joinMeetingURL($accountid,$accountpwd,$fullname,$meetingid,$moderatorPW); // as moderator
  }
}
if (has_capability('mod/bigbluebutton:ismoderator', $context)) {
    echo $OUTPUT->box_start('generalbox', 'enterlink');
    echo '<center>';
    echo '<p>'.get_string('joinmeeting_instructions_mod', 'bigbluebutton').'</p>';
    echo '<br />';
    echo "<a ".$newWindowStr." href='".$joinURL."'>".get_string('joinmeeting_asguest', 'bigbluebutton')."</a>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<a ".$newWindowStr." href='".$joinURLasMod."'>".get_string('joinmeeting_asmoderator', 'bigbluebutton')."</a><br /><br />";
    echo '</center>';
    echo $OUTPUT->box_end();
}

else if (has_capability('mod/bigbluebutton:isattendee', $context)) {

    if ($provider=="self") {
      $isRunning = wc_isMeetingRunning($ip,$securitySalt,$meetingid);
    }
    else {
      $isRunning = dc_isMeetingRunning($accountid,$accountpwd,$meetingid);
    }

if ($isRunning=="false") {
  echo '<center>';
  echo get_string('notrunning', 'bigbluebutton').'<br /><br />';
  echo "<img src='polling.gif' border='0' /><br><br>";
  //echo "(<a ".$newWindowStr." href='".$joinURL."'>".get_string('autorefresh', 'bigbluebutton')."</a>)";
  echo "(".get_string('autorefresh', 'bigbluebutton').")";
  ?>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
  <script type="text/javascript" src="heartbeat.js"></script>
  <script type="text/javascript">
  $(document).ready(function(){
  $.jheartbeat.set({
    url: "<?php echo $CFG->wwwroot ?>/mod/bigbluebutton/asynch_isrunning.php?name=<?php echo $fullname ?>&meetingID=<?php echo $meetingid ?>&password=<?php echo $attendeePW ?>",
    delay: 1500
  }, function () {
  mycallback();
});
});

  function mycallback() {
  // Not elegant, but works around a bug in IE8
    var isMeetingRunning = ($("#HeartBeatDIV").text().search("true") == 0 );
    //alert($("#HeartBeatDIV").text());
    if (isMeetingRunning) {
      //alert("OK");
      window.location = "<?php echo $myURL ?>"; 
    }
  }
  myURL = "<?php echo $CFG->wwwroot ?>/mod/bigbluebutton/asynch_isrunning.php?name=<?php echo $fullname ?>&meetingID=<?php echo $meetingid ?>&password=<?php echo $attendeePW ?>";
  $("#thisismyid").load(myURL);
  </script>
    
  </center>
  <?php
}

else {
    echo $OUTPUT->box_start('generalbox', 'enterlink');
    echo '<center>';
    echo '<p>'.get_string('joinmeeting_instructions', 'bigbluebutton').'</p>';
    echo '<br />';
    echo "<a ".$newWindowStr." href='".$joinURL."'>".get_string('joinmeeting', 'bigbluebutton')."</a>";
    echo '</center>';
    echo $OUTPUT->box_end();
}
}

// END HERE

else {
    echo $OUTPUT->box_start('generalbox', 'notallowenter');
    echo '<p>'.get_string('joinmeeting_instructions', 'bigbluebutton').'</p>';
    echo $OUTPUT->box_end();
}


$completion = new completion_info($course);
$completion->set_module_viewed($cm);

echo $OUTPUT->footer();
