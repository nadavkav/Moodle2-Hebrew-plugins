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
require_once("$CFG->dirroot/blocks/csv_enrol/edit_form.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once("$CFG->dirroot/blocks/csv_enrol/locallib.php");

GLOBAL $USER;
require_login();

$courseid = optional_param('courseid', '', PARAM_INT);  
$context = get_context_instance(CONTEXT_COURSE,$courseid);
if (!has_capability('mod/csv_enrol:uploadcsv',$context,$USER->id)) {
    die("Unauthorized.");
}

$title = get_string('csvenrol','block_csv_enrol');
$struser = get_string('user');

$PAGE->set_url('/blocks/csv_enrol/edit.php');
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('user-private-files');

$data = new stdClass();
$options = array('subdirs' => 1, 'maxbytes' => $CFG->userquota, 'maxfiles' => - 1, 'accepted_types' => '*.csv', 'return_types' => FILE_INTERNAL);

file_prepare_standard_filemanager($data, 'files', $options, $context, 'user', 'csvenrol', 0);

$course = $DB->get_record('course', array('id' => $courseid)); //used for coursename
$data->coursename = $course->fullname;
$data->courseid = $courseid;

$mform = new block_csv_enrol_form(null, array('data' => $data, 'options' => $options));

$formdata = $mform->get_data();
//3 options: file uploaded, cancelled, or saved
if ($mform->is_cancelled()) {
   redirect(new moodle_url($CFG->wwwroot.('/course/view.php'), array('id' => $courseid)));  
} else if ($formdata && $mform->get_file_content('userfile')) {
    
    //upload file, store, and process csv
    $content = $mform->get_file_content('userfile'); //save uploaded file
    $fs = get_file_storage();

    //Cleanup old files:
    //First, create target directory:
    if(!$fs->file_exists($context->id, 'user', 'csvenrol', 0, '/', 'History'))
    $fs->create_directory($context->id, 'user', 'csvenrol', 0, '/History/',$USER->id);

    //Second, move all files to created dir
    $areafiles = $fs->get_area_files($context->id, 'user', 'csvenrol',false, "filename", false);
    $filechanges = array("filepath"=>'/History/');
    foreach ($areafiles as $key => $areafile) {
        if($areafile->get_filepath()=="/")
        {
            $fs->create_file_from_storedfile($filechanges, $areafile); //copy file to new location
            $areafile->delete(); //remove old copy
        }
    }

    $filename = "upload_".date("Ymd_His").".csv";
    
    // Prepare file record object
    $fileinfo = array(
	    'contextid' => $context->id, // ID of context
	    'component' => 'user',     // usually = table name
	    'filearea' => 'csvenrol',     // usually = table name
	    'itemid' => 0,               // usually = ID of row in table
	    'filepath' => '/',           // any path beginning and ending in /
	    'filename' => $filename,// any filename
	    'userid' => $USER->id );
    
    // Create file containing uploaded file content
    $newfile = $fs->create_file_from_string($fileinfo, $content);

    // Read CSV and get results
    $log = block_csv_enrol_enrol_users($courseid,$content);

    //save log file, reuse fileinfo from csv file
    $fileinfo['filename'] = "upload_".date("Ymd_His")."_log.txt";
    $newfile = $fs->create_file_from_string($fileinfo, $log);
    
    // Back to main page
    redirect(new moodle_url($CFG->wwwroot.('/course/view.php'),
        array('id' => $courseid))); 
    
} else if ($formdata &&  !$mform->get_file_content('userfile')) {
    
    // Just show the updated filemanager
    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'user', 'csvenrol', 0);
    
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
