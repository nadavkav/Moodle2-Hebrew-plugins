<?php

//  BRIGHTALLY CUSTOM CODE
//  Coder: Ted vd Brink
//  Contact: ted.vandenbrink@brightalley.nl
//  Date: 6 juni 2012
//
//  Description: Enrols users into a course by allowing a user to upload an csv file with only email adresses
//  Using this block allows you to use CSV files with only emailaddress
//  After running the upload you can download a txt file that contains a log of the enrolled and failed users.

//  License: GNU General Public License http://www.gnu.org/copyleft/gpl.html

$string['manageuploads'] = 'Manage uploaded files';
$string['pluginname'] = 'Enrol users with CSV';
$string['csvenrol'] = 'Enrol users';
$string['uploadcsv'] = 'Upload your CSV here:';
$string['resultfiles'] = 'Result of your CSV enrolment:';
$string['title'] = 'Enrol users into {$a}';
$string['description'] = 'You can upload your CSV file with email adresses of Moodle users here, so that they can be enrolled into the course "{$a}".';
$string['enrolling'] = 'Enrolling users...';
$string['alreadyenrolled'] = 'User {$a} already enrolled into this course.';
$string['enrollinguser'] = 'Enrolling user {$a}.';
$string['emailnotfound'] = 'Could not find email address {$a}.';
$string['done'] = 'Enrolling done.';
$string['status'] = 'Enrolling done. Result: {$a->success} succeeded or already enrolled, {$a->failed} failed.';
$string['enrolmentlog'] = 'Log of enrolment:';
