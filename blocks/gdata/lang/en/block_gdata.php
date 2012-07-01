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
 * @author Mark Nielsen
 */


/**
 * Language entries for Google Data Block
 *
 * @author Mark Nielsen
 * @version $Id$
 * @package block_gdata
 **/

$string['pluginname'] = 'Google User Sync';

$string['addusers'] = 'Add users to sync';
$string['alloweventssetting'] = 'Enable events';
$string['alloweventssettingdesc'] = 'If this setting is enabled, then a Moodle user\'s account will be instantly updated in Google Apps when the user edits his/her account in Moodle\'s standard edit profile interface.  Also, if a Moodle user\'s account is deleted, then the associated Google Apps account will also be instantly deleted.  This only applies to Moodle accounts that are currently being synchronized to Google Apps.  This feature is \"best attempt\" only, so failures will fallback to the cron to perform the synchronization.';
$string['authfailed'] = 'Authentication with Google Apps failed.  Please check your credentials.';
$string['checkstatus'] = 'Check status';
$string['confirmaddusers'] = 'Are you sure you want to add all {$a} users in your search set?';
$string['confirmusers'] = 'Are you sure you want to remove all {$a} users from the sync list?';
$string['connectionsuccess'] = 'Authentication with Google Apps was successful.';
$string['cronexpiresetting'] = 'Cron expire (hours)';
$string['cronexpiresettingdesc'] = 'When the synchronization runs, it locks the cron from being excecuted again until it has finnished.  This setting is used to determine when that lock has expired.  Consider setting this to a high value especially on first runs with a lot of users.';
$string['cronintervalsetting'] = 'Cron interval (minutes)';
$string['cronintervalsettingdesc'] = 'Enter in how often the Moodle to Google Apps synchronization should be executed on the cron.  Enter zero to stop running the synchronization.';
$string['domainsetting'] = 'Google Apps domain';
$string['domainsettingdesc'] = 'This is the domain associated with your Google Apps account. For example, if you login to Google Apps as \'foo@bar.com\', your domain is \'bar.com\'.';
$string['failedtodeletesyncrecord'] = 'Failed to delete block_gdata_gapps record with id = {$a}';
$string['failedtoupdateemail'] = 'Failed to update user\'s email with the one from Google Apps';
$string['failedtoupdatesyncrecord'] = 'Failed to update block_gdata_gapps record with id = {$a}';
$string['gappserror'] = 'Google Apps error: {$a}';
$string['insertfailed'] = 'Insert failed';
$string['invalidparameter'] = 'Programmer\'s error: invalid parameter passed';
$string['lastsync'] = 'Last sync';
$string['missingrequiredconfig'] = 'Missing required global setting: {$a}';
$string['notconfigured'] = 'Global settings have not been configured.  Please configure this plugin.';
$string['nouserfound'] = 'User not found';
$string['nousersfound'] = 'No users need to be synchronized now or there are none to be synchronized';
$string['pagesize'] = 'Page size';
$string['passwordsetting'] = 'Google Apps password';
$string['passwordsettingdesc'] = 'This is the password associated with the above username.';
$string['selectall'] = 'Select all';
$string['selectnone'] = 'Select none';
$string['setfieldfailed'] = 'Set field failed';
$string['settings'] = 'Settings';
$string['status'] = 'Status';
$string['statusaccountcreationerror'] = 'Failed to create Google Apps account';
$string['statuserror'] = 'Error';
$string['statusnever'] = 'Never';
$string['statusok'] = 'OK';
$string['statususernameconflict'] = 'Username conflict';
$string['submitbuttonaddusers'] = 'Add users to sync';
$string['submitbuttonalladdusers'] = 'Add all {$a} users';
$string['submitbuttonallusers'] = 'Remove all {$a} users';
$string['submitbuttonusers'] = 'Remove users from sync';
$string['usedomainemailsetting'] = 'Use Google Apps email';
$string['usedomainemailsettingdesc'] = 'Update Moodle\'s user record with the email from the Google Apps domain.  The update will occur during the Moodle to Google Apps synchronization.';
$string['useralreadyexists'] = 'User already exists in the block_gdata_gapps table';
$string['useralreadyexists'] = 'User already exists';
$string['usernameconflict'] = 'Username conflict: Moodle changed username from {$a->oldusername} to {$a->username}.  {$a->username} is already being synced to Goodle Apps';
$string['usernamesetting'] = 'Google Apps username';
$string['usernamesettingdesc'] = 'This is the username (without domain) used to administer your Google Apps account. For example, if you login to Google Apps as \'foo@bar.com\', your username is \'foo\'.';
$string['userssynced'] = 'Users being synced';
$string['syncnow'] = 'Sync Now';

$string['gapps'] = 'Google Apps';
$string['gapps_help'] = 'This help file provides brief overview of the different features of the Google Apps block.

*Google Apps Status*

* The <em>Status</em> tab provides feedback on whether or not Moodle can connect to Google Apps using the credentials provided in <em>Site Administration &gt; Modules &gt; Blocks &gt; Google Apps</em>. The credentials are the values entered for username, password and domain.

*Users being synced*

* The <em>Users being synced</em> tab provides an interface to view all the users that are currently being synchronized to Google Apps.  Here, you can also review the last time an account was synchronized to Google Apps and the status of the account synchronization.  Synchronization takes place according to the cron settings found in <em>Site Administration &gt; Modules &gt; Blocks &gt; Google Apps</em>.

*Synchronization rules*

* If a user has been deleted in Moodle or has been removed from the synchronization process, then delete the user\'s account in Google Apps.
* If a user has had their username renamed in Moodle then delete the old account using the old username from Google Apps and create a new account in Google Apps with the new Moodle username.
* If a user does not have an account in Google Apps with their Moodle username, then create it.
* Do not allow duplicate usernames to be used for users in Google Apps and in Moodle.
* If the Moodle user has an account in Google Apps then check first name, last name and password for changes in Moodle, then update Google Apps if necessary.
* The password shared by Moodle and Google Apps must be at least 6 characters in length.  Please use <em>Site Administration > Security > Site policies</em> to turn on Password Policy and set Password Length to 6 or more characters.

*Add users to sync*
* The <em>Add users to sync</em> tab provides an interface to bulk add users to the Moodle to Google Apps synchronization.
';

