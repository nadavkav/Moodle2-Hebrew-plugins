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

require('../../config.php');
require_once("$CFG->dirroot/lib/filelib.php");
$relativepath = get_file_argument();
$forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);

global $DB, $CFG, $USER;
// relative path must start with '/'
if (!$relativepath) {
	print_error('invalidargorconf');
} else if ($relativepath[0] != '/') {
	print_error('pathdoesnotstartslash');
}

// extract relative path components
$args = explode('/', ltrim($relativepath, '/'));
  
if (count($args) < 3) { // always at least context, component and filearea
	print_error('invalidarguments');
}

$contextid = (int)array_shift($args);
$component = clean_param(array_shift($args), PARAM_ALPHA);
$filearea  = clean_param(array_shift($args), PARAM_ALPHA);

if ($component!='user' || $filearea!='csvenrol')
        print_error('invalidargorconf');

$filename = array_pop($args);
$filepath = $args ? '/'.implode('/', $args).'/' : '/';
  
//list($context, $course, $cm) = get_context_info_array($contextid);
//print_r($cm);
$fs = get_file_storage();
if (!$file = $fs->get_file($contextid, $component, $filearea, 0, $filepath, $filename) or $file->is_directory()) {
	send_file_not_found();
}

send_stored_file($file, 10*60, 0, true); // download MUST be forced - security!
