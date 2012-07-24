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

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2012071101;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2011070100;        // Requires this Moodle version
$plugin->component = 'block_csv_enrol'; // Full name of the plugin (used for diagnostics)
$plugin->maturity  = MATURITY_RC;
$plugin->cron      = 0;
$plugin->release   = '2.x (Build 2012061402)';
