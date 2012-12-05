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

require_once('../../config.php');
require_once('lib.php');

$ip				= $CFG->wc_serverhost;
$securitySalt	= $CFG->wc_securitysalt;
$provider		= $CFG->wc_provider;
$accountid		= $CFG->wc_accountid;
$accountpwd		= $CFG->wc_accountpwd;
$myMeetingID 	= $_REQUEST['meetingID'];

if ($provider=="self") {
  echo wc_isMeetingRunning($ip,$securitySalt,$myMeetingID);
}
else {
  echo dc_isMeetingRunning($accountid,$accountpwd,$myMeetingID);
}

?>