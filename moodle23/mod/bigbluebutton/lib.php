<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
//                                                                       //
// Copyright (C) 2010 Dual Code Inc. (www.dualcode.com)                  //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License version 2 as     //
// published by the Free Software Foundation.                            //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
// http://www.gnu.org/licenses/old-licenses/gpl-2.0.html                 //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/// Given an object containing all the necessary data,
/// (defined by the form in mod.html) this function
/// will create a new instance and return the id number
/// of the new instance.
function bigbluebutton_add_instance($bigbluebutton) {
    global $DB;

    $bigbluebutton->timemodified = time();
    $bigbluebutton->name =  $bigbluebutton->meetingname;
    $bigbluebutton->autologin   = '1';
    $returnid = $DB->insert_record("bigbluebutton", $bigbluebutton);

    return $returnid;
}


/// Given an object containing all the necessary data,
/// (defined by the form in mod.html) this function
/// will update an existing instance with new data.
function bigbluebutton_update_instance($bigbluebutton) {
        global $DB;

	$bigbluebutton->timemodified = time();
	$bigbluebutton->id = $bigbluebutton->instance;
        $bigbluebutton->name =  $bigbluebutton->meetingname;

        $DB->update_record("bigbluebutton", $bigbluebutton);
/*
        $event = new stdClass();

	if ($event->id = $DB->get_field('event', 'id', array('modulename'=>'bigbluebutton', 'instance'=>$bigbluebutton->id))) {
	  $event->courseid    = $bigbluebutton->course;
	  $event->name	      = $bigbluebutton->name;
	  $event->meetingname = $bigbluebutton->meetingname;
	  $event->meetingid   = $bigbluebutton->meetingid;
	  $event->attendeepw  = $bigbluebutton->attendeepw;
	  $event->moderatorpw = $bigbluebutton->moderatorpw;
	  $event->autologin   = $bigbluebutton->autologin;
	  $event->newwindow   = $bigbluebutton->newwindow;
	  $event->welcomemsg  = $bigbluebutton->welcomemsg;	
	}
*/
	return true;
}



/// Given an ID of an instance of this module,
/// this function will permanently delete the instance
/// and any data that depends on it.
function bigbluebutton_delete_instance($id) {
    global $DB;

    if (! $bigbluebutton = $DB->get_record('bigbluebutton', array('id'=>$id))) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! $DB->delete_records('bigbluebutton', array('id'=>$bigbluebutton->id))) {
        $result = false;
    }

    return $result;
}


// Create string where we check if the meeting is running
function wc_isMeetingRunningURL($myIP,$mySecuritySalt,$myMeetingID) {
	$checkAPI = "/bigbluebutton/api/isMeetingRunning?";
	$queryStr = "meetingID=".$myMeetingID;
	$checksum = sha1('isMeetingRunning'.$queryStr.$mySecuritySalt);
	$secQueryURL = "http://".$myIP.$checkAPI.$queryStr."&checksum=".$checksum;
	
	return $secQueryURL;
}


// Determine if the meeting is already running (e.g. has attendees in it)
function wc_isMeetingRunning($myIP,$mySecuritySalt,$myMeetingID) {
	$secQueryURL = wc_isMeetingRunningURL($myIP,$mySecuritySalt,$myMeetingID);
	$myResponse = file_get_contents($secQueryURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;
	$runningNode = $doc->getElementsByTagName("running");
	$isRunning = $runningNode->item(0)->nodeValue;
	
	return $isRunning;
}


//Create meeting if it's not already running
function wc_createMeeting($myIP,$mySecuritySalt,$myMeetingName,$myMeetingID,$myAttendeePW,$myModeratorPW,$myWelcomeMsg,$myLogoutURL) {
	  $createAPI = "/bigbluebutton/api/create?";
	  $myVoiceBridge = rand(70000,79999);
	  $queryStr = "name=".$myMeetingName."&meetingID=".$myMeetingID."&attendeePW=".$myAttendeePW."&moderatorPW=".$myModeratorPW."&voiceBridge=".$myVoiceBridge."&welcome=".$myWelcomeMsg."&logoutURL=".$myLogoutURL;
    $checksum = sha1('create'.$queryStr.$mySecuritySalt);
	  $secQueryURL = "http://".$myIP.$createAPI.$queryStr."&checksum=".$checksum;
	  $myResponse = file_get_contents($secQueryURL);
	  $doc= new DOMDocument();
	  $doc->loadXML($myResponse);
	  $returnCodeNode = $doc->getElementsByTagName("returncode");
	  $returnCode = $returnCodeNode->item(0)->nodeValue;

	  if ($returnCode=="SUCCESS") {
		return $returnCode;
	  }
	  else {
	    $messageKeyNode = $doc->getElementsByTagName("messageKey");
	    $messageKey = $messageKeyNode->item(0)->nodeValue;
		return $messageKey;
	  }
}


// Create a URL to join the meeting
function wc_joinMeetingURL($myIP,$mySecuritySalt,$myName,$myMeetingID,$myPassword) {
	$joinAPI = "/bigbluebutton/api/join?";
	$queryStr = "fullName=".$myName."&meetingID=".$myMeetingID."&password=".$myPassword;
  $checksum = sha1('join'.$queryStr.$mySecuritySalt);
	$createStr = "http://".$myIP.$joinAPI.$queryStr."&checksum=".$checksum;
	
	return $createStr;
}

// This API is not yet supported in bigbluebutton
function wc_endMeeting() {
	return false;
}

// This API is not yet supported in bigbluebutton
function wc_listAttendees() {
	return false;
}

// This API is not yet supported in bigbluebutton
function wc_getMeetingInfo() {
	return false;
}

// Determine the URL of the current page (for logoutURL)
function wc_currentPageURL() {
  $isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
  $port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
  $port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
  $pageURL = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
  return $pageURL;
}


// Determine the IP/Domain of the current Corporate University
function wc_currentDomain() {
  $currentDomain = $_SERVER["SERVER_NAME"];
  return $currentDomain;
}


//Determine if a new version of the plug-in is available
function wc_needUpgrade() {
  $returnValue = false;
  $installedVersion = "20100805";
  $availableVersion = dc_getVersion();
  if ((int)$installedVersion < (int)$availableVersion) {
	  $returnValue = true;
  }
  return $returnValue;
}


//////////////////////////////////////////////////
//
// The following functions are to communicate 
// with the Dual Code BigBlueButton network
//
//      DO NOT MODIFY THESE FUNCTIONS
//
//////////////////////////////////////////////////


function dc_authenticate($myAccountID,$myAccountPWD) {
	$authenticateURL = "http://bigbluebutton.dualcode.com/api.php?call=authenticate&accountid=".urlencode($myAccountID)."&accountpwd=".urlencode($myAccountPWD)."&version=070";
	
	$myResponse = file_get_contents($authenticateURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;

	if ($returnCode=="SUCCESS") {
	  $serveridNode = $doc->getElementsByTagName("serverid");
	  $serverid = $serveridNode->item(0)->nodeValue;	
	  return $serverid; 
	}
	else {
	  $messageKeyNode = $doc->getElementsByTagName("messageKey");
	  $messageKey = $messageKeyNode->item(0)->nodeValue;
	  return $messageKey;
	}
}

function dc_getVersion() {
	$versionURL = "http://bigbluebutton.dualcode.com/api.php?call=version";
	
	$myResponse = file_get_contents($versionURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;

	if ($returnCode=="SUCCESS") {
	  $versionNode = $doc->getElementsByTagName("version");
	  $version = $versionNode->item(0)->nodeValue;	
	  return $version; 
	}
	else {
	  $messageKeyNode = $doc->getElementsByTagName("messageKey");
	  $messageKey = $messageKeyNode->item(0)->nodeValue;
	  return $messageKey;
	}
}

function dc_getChecksum($myAccountID,$myAccountPWD,$queryStr) {
	$checkSumURL = "http://bigbluebutton.dualcode.com/api.php?call=checksum&queryStr=".urlencode($queryStr)."&version=0.7";
	$myResponse = file_get_contents($checkSumURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;

	if ($returnCode=="SUCCESS") {
	  $checksumNode = $doc->getElementsByTagName("checksum");
	  $checksum = $checksumNode->item(0)->nodeValue;	
	  return $checksum;
	}
	else {
	  return $returnCode;
	}
}

function dc_createMeeting($myAccountID,$myAccountPWD,$myMeetingName,$myMeetingID,$myAttendeePW,$myModeratorPW,$myWelcomeMsg,$myLogoutURL) {
	$myIP= dc_authenticate($myAccountID,$myAccountPWD);
	if ($myIP != "FAILURE") {
	  $createAPI = "/bigbluebutton/api/create?";
	  $myVoiceBridge = rand(70000,79999);
	  $queryStr = "name=".$myMeetingName."&meetingID=".$myMeetingID."&attendeePW=".$myAttendeePW."&moderatorPW=".$myModeratorPW."&voiceBridge=".$myVoiceBridge."&welcome=".$myWelcomeMsg."&logoutURL=".$myLogoutURL;
	  //echo urlencode($queryStr);
    $checksum = dc_getChecksum($myAccountID,$myAccountPWD,urlencode("create".$queryStr));
	  $secQueryURL = "http://".$myIP.$createAPI.$queryStr."&checksum=".$checksum;
	  $myResponse = file_get_contents($secQueryURL);
	  $doc= new DOMDocument();
	  $doc->loadXML($myResponse);
	  $returnCodeNode = $doc->getElementsByTagName("returncode");
	  $returnCode = $returnCodeNode->item(0)->nodeValue;

	  if ($returnCode=="SUCCESS") {
		return $returnCode;
	  }
	  else {
	    $messageKeyNode = $doc->getElementsByTagName("messageKey");
	    $messageKey = $messageKeyNode->item(0)->nodeValue;
		return $messageKey;
	  }
	}
	else {
	  return "FAILURE";
	}
}


// Determine if the meeting is already running (e.g. has attendees in it)
function dc_isMeetingRunning($myAccountID,$myAccountPWD,$myMeetingID) {
	$secQueryURL = dc_isMeetingRunningURL($myAccountID,$myAccountPWD,$myMeetingID);
	$myResponse = file_get_contents($secQueryURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;
	$runningNode = $doc->getElementsByTagName("running");
	$isRunning = $runningNode->item(0)->nodeValue;
	
	return $isRunning;
}

// Create string where we check if the meeting is running
function dc_isMeetingRunningURL($myAccountID,$myAccountPWD,$myMeetingID) {
	$myIP = dc_authenticate($myAccountID,$myAccountPWD);
	if ($myIP != "FAILURE") {
	  $checkAPI = "/bigbluebutton/api/isMeetingRunning?";
	  $queryStr = "meetingID=".$myMeetingID;
	  $checksum = dc_getChecksum($myAccountID,$myAccountPWD,urlencode("isMeetingRunning".$queryStr));
	  $secQueryURL = "http://".$myIP.$checkAPI.$queryStr."&checksum=".$checksum;

      return $secQueryURL;
	}
	else {
	  return "FAILURE";
	}
}


// Create a URL to join the meeting
function dc_joinMeetingURL($myAccountID,$myAccountPWD,$myName,$myMeetingID,$myPassword) {
	$myIP = dc_authenticate($myAccountID,$myAccountPWD);
	if ($myIP != "FAILURE") {
  	  $joinAPI = "/bigbluebutton/api/join?";
	  $queryStr = "fullName=".$myName."&meetingID=".$myMeetingID."&password=".$myPassword;
	  $checksum = dc_getChecksum($myAccountID,$myAccountPWD,urlencode("join".$queryStr));
	  $createStr = "http://".$myIP.$joinAPI.$queryStr."&checksum=".$checksum;

	  return $createStr;
	}
	else {
	  return "FAILURE";
	}
}
?>
