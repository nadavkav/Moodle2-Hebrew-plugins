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

require_once("$CFG->libdir/formslib.php");

class block_csv_enrol_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];

	$mform->addElement('hidden', 'courseid', $data->courseid);

        $mform->addElement('static', 'name', "", get_string('description', 'block_csv_enrol',$data->coursename));
        
    	$mform->addElement('filepicker', 'userfile', get_string('uploadcsv','block_csv_enrol'),
			null, array('accepted_types' => '*.csv'));

        $mform->addElement('filemanager', 'files_filemanager', get_string('resultfiles','block_csv_enrol'),
			null, $options);

        $this->add_action_buttons(true, get_string('savechanges'));

        $this->set_data($data);
    }
}
