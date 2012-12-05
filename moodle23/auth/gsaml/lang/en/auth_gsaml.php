<?php
/**
* Copyright (C) 2009  Moodlerooms Inc.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
* 
* @copyright  Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
* @license    http://opensource.org/licenses/gpl-3.0.html     GNU Public License
* @author Chris Stones
*/
$string['domainname']             = 'Domain';
$string['auth_gsamldescription']  = 'This auth plugin enables Moodle to Single Sign on with SAML SPs.';
$string['auth_gsamltitle']        = 'Google SAML Authentication';
$string['pluginname']             = 'Google SAML Authentication';
$string['cert']                   = 'Certificate';
$string['key']                    = 'RSA Key';

// for auth/gsaml/settings.php 
$string['domainname'] = 'Domain Name';
$string['rsakey'] = 'RSA Key';
$string['rsakey_desc'] = 'Paste the plain-text from the RSA key (pem) file here. Note that the SAML service supports RSA signed keys only.';
$string['googleauthconfig'] = 'Google Authentication Configuration';
$string['sslcertificate'] = 'SSL Signing Certificate';
$string['sslcertificate_desc'] = 'Paste the plain-text from the X.509 Certificate file here. Note that this is the same file you will upload to Google.';
$string['sslcertificate_help'] = 'TEST HELP: SSL Certificate';
$string['setupinstructions'] = 'Set Up Instructions';
$string['moodlegadget'] = 'Moodle Gadget';
$string['googlediagnostics'] = 'Google Intergration Diagnostics';
$string['debugoptions'] = 'Once you are done configuring you may relogin in and visit <a href="{$a}/auth/gsaml/diagnostics.php'.'">The Diagnostics Page</a> for confirmation.';

$string['gadgetinfo'] = 'Use the following URL to add the Moodle Gadget to your Google Start Page: <b>{$a->wwwroot}/auth/gsaml/moodlegadget.php</b>';
$string['lnktogoogsettings'] = 'Link to Google Settings';
$string['nodomainyet'] = 'Domain is not set'; 
$string['setupinfo'] = '<ol><li>Set the <b>Domain Name</b> to your google service domain name then click <b>Save Changes</b><br/><br/>
<li>In a new window open Google Apps Control Panel page as admin (<a href="https://www.google.com/a/{$a->domainname}">{$a->googsettings}</a>)<br/><br/>
<li>Click the <b>Advanced tools</b> tab.<br/><br/>
<li>Click the <b>Set up single sign-on (SSO)</b> link next to Authentication.<br/><br/>
<li>First check the <b>Enable Single Sign-on</b> box.<br/><br/>
<li>Now insert this url into the <b>Sign-in page URL</b> text field.<br/><b>{$a->wwwroot}/login/index.php</b><br/><br/>
<li>Insert this url into the <b>Sign-out page URL</b> text field.<br/><b>{$a->wwwroot}/login/logout.php</b><br/><br/>
<li>Insert this url into the <b>Change password URL</b> text field.<br/><b>{$a->wwwroot}/login/change_password.php</b><br/><br/>
<li>Generate and upload a <b>Verification certificate to Google (X.509 certificate containing the public key)</b><br/><br/>
<li>Upload the privatekey and certificate to Moodle as well and then click <b>Save Changes</b></b><br/></ol>';

// Moodle Gadget
$string['tomoodle'] = 'To Moodle';

// errors
$string['errusernotloggedin'] = 'User could not be logged in';
$string['pwdnotvalid'] = 'Password not valid';
$string['samlcodefailed'] = 'SAML Code Failed turn debugging on to find out why';
$string['samlauthcodefailed'] = 'SAML Auth Code Failed turn debugging on for more information';
$string['sixcharmsg'] = 'User Password Must be longer than 6 characters for Google Intergration. Tell your Admin to adjust the site policy settings';

// diagnostics
$string['googsamldiag'] = 'Google SAML Diagnostics';
$string['notadminnoperm'] = 'You are not an Site Admin. You do not have permission to view this information';
$string['gdatanotconfig'] = 'gdata configuration table not set.';
$string['googlesamlconfigdata'] = 'Google SAML Configuration Data';
$string['gsamlconfignotset'] = 'Google SAML configuration has not yet been set';
$string['gdataconfignotset'] = 'gdata config table not set';
$string['gdataconfig'] = 'GData Configuration';
$string['gmailconfig'] = 'GMail Configuration';
$string['componentinstallcheck'] = 'Component Install Precheck';
$string['gdatanotinstalled'] = 'gdata block is not installed\n';
$string['gappsblockinstalled'] = 'GApps Block installed\n';
$string['gmailblocknotinstalled'] = 'gmail block is not installed';
$string['gmailblockinstalled'] = 'GMail Block installed\n';
$string['gdataapitestresults'] = 'GData API Test Results';
$string['trytoinitgdataconnection'] = 'Trying to init a gdata to google connection<br/>';
$string['gsamlsuccess'] = 'Success';
$string['gmailtestresults'] = 'GMail Test Results';
$string['gmailtestwillnotrun'] = 'GMail Test will not run unless Moodle is in DEBUG_DEVELOPER Mode';
$string['obtainemailfeed'] = 'Obtaining email feed for username: ';

// help strings (converted from 1.9 html files)
$string['keys'] = 'Google SSO Keys';
$string['keys_help'] = 'Both Moodle and Google must be able to securely authorize access to important resources. The admin must generate a <b>Verification Certificate</b>. And Upload a X.509 formatted certificate with an embedded public key to google. <a href="http://code.google.com/apis/apps/articles/sso-keygen.html">Google Documenation Regarding Key Generation</a> Note that the SAML Moodle SSO service only uses <b>RSA keys.</b>';

$string['mgadget'] = 'Moodle Gadget Help';
$string['mgadget_help'] = 'This help file provides brief overview of the google gadget for your moodle site.

The Moodle Gadget
* The <em>Moodle Gadget</em> is a gadget that you can embed on your Google Partner start page.

Steps to install onto a Users Start Page
* Login to Moodle
* Click on the <b>Google Partner Page</b> in the <b>Google Services Access</b> block.
* Click on the <b>Add Stuff</b> link
* Click on <b>Add my url</b>
* Enter the moodlegadget URL
* The URL is something like <b>http://www.yourmoodedomain.org/auth/gsaml/moodlegadget.php</b>
*  It can be found in the <b>Google Authentication</b> Settings from the Admin block
* After you copy the url to the url field. Click <b>Add</b>
* Click <b>Back to homepage</b>
* You shoudl know see a block containing a link back to your moodle site.

Considerations (As of BETA release)
* This is the beta prototype for this Google Moodle Gadget. In the future it may be much much more useful. Currently there is a known bug regarding the Gadget not updating immediately upon install. Developers are looking in to it.';

$string['diagnostics'] = 'Google Integration Diagnostics';
$string['diagnostics_help'] = 'The Diagnostics page will reveal information regarding the gmail, gdata and saml connection status. Only administrators have permission to view diagnostic information.

Configuration Table Info
* These tables represnt the current configuration of the Moodle to Google Intergration. Make sure the values are all set. 

GData Connection
* The GData block located in the blocks folder contains the librarys for a varity of Google Services. You must confirm that it is able to connect to the Google Services. You may need to adjust values on the GData Block Settings page.

GMail Block Connection Test
* At the present moment the gmail block will only refresh a users unread messages upon login. You will only be able to run the test here if debugging is turned on.  Support for viewing unread messages in real time is coming.

SAML SSO Status Test
* The SAML status test is an independent check of the SSO authorization process. It has not yet been implemented. With debugging turned on and set to DEBUG_DEVELOPER, error information should be revealed upon login.';

$string['config_gsaml'] = 'Google SSO Configuration';
$string['config_gsaml_help'] = 'Setting up the full Google to Moodle intergration requires a bit of configuration. This help file should guide you through most of the process in getting the Google Authentication Plugin configured as well as the gdata block and the gmail block ready.

Google Apps Status

Preconditions/assumptions
* The following plugins have been installed:
* auth/gsaml
* blocks/gmail
* blocks/gdata
* blocks/gaccess
* blocks/mgadget (optional as of beta)

Steps
* Login to Moodle as an Administrator
* Click <b>Notifications</b> to update block tables
* Enable the <b>Google Authentication</b> plugin from the Manage Plugins admin page
* Now Select Users Authentication goto <b>Google Authentication</b> there should be directions on the page to follow.
* Enter your google partner page domain
* Upload Certificate (for more information on creating this <a href="http://code.google.com/apis/apps/articles/sso-keygen.html">Google Documenation Regarding Key Generation</a> )
* Upload Private Key (for more information on creating this visit <a href="http://code.google.com/apis/apps/articles/sso-keygen.html">Google Documenation Regarding Key Generation</a>)
* Click Save
* Follow the directions for adding the proper urls to the google SSO page
* Click the checkbox on the google site to enable SSO
* On the Google Site be sure to enable the provisioning API or no users will be updated.
* On the Google Site make sure API Provisioning is Enabled
* On the google side you may need to requset more User accounts
* Return to Moodle\'s main page
* Click Edit
* Add GAccess, GMail and the Gdata blocks to the page.
* Click <b>Settings</b> in the <b>Google Apps</b> block. Fill out the configuration information.
* Click the Status link to confirm that your Google Apps block is set up properly.
* Goto Site Administration &gt; Security &gt; Site policies
* Check Password Policy
* Set Password Length to 6 or greater (Required for Google\'s password policy)
* In the Gdata block you need to add users to sync. Do this by clicking the add users to sync link in the <b>Google Apps block</b>. It helps to see the result if you set the gdata block cron to 1 min.
* To Upload those Moodle Users into Google you may run the cron manually by visiting admin/cron.php Results for the sync should appear in the read out. <b>Beware, as of this beta syncing Moodle Users with Google may take a <em>LONG</em> time.</b>

Expected Results
* clicking on any Google Service links or visiting services from the Google Partner page will Authenticate against Moodle.
* User should be logged into Moodle as well as Google Partner Services
* See the <b>Diagnostics Page</b> in the gsaml settings for more information on your set up.

Considerations (As of BETA Version)
* The GMail feed may not be found. This is probabily a because the user passow doesn\'t match googles user password. In the future this won\'t be a problem. For now be sure to sync users with the Google Apps block. E Mail will update upon login. In the future unreadmessages should update in real time.
* The Location of the Google Authentcaion Plugin in the Authetni order is important. As of this moment it needs to override a users auth type when a moodle user changes passwords. This behavior may affect mnet users. A solution for this problem has not yet been found.';  

