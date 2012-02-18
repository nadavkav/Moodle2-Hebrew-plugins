<?php

require_once("$CFG->libdir/formslib.php");

class image_form extends moodleform {
    function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

        // visible elements
        //$mform->addElement('filemanager', 'newfile', get_string('uploadafile'));
        //$mform->addElement('filemanager', 'files_filemanager', get_string('uploadafile'), null, $instance['options']);
        $mform->addElement('filepicker', 'assignment_file', get_string('uploadafile'), null, $instance['options']);

        // hidden params
        $mform->addElement('hidden', 'contextid', $instance['contextid']);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'userid', $instance['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'sectionid', $instance['sectionid']);
        $mform->setType('sectionid', PARAM_INT);         
        $mform->addElement('hidden', 'action', 'uploadfile');
        $mform->setType('action', PARAM_ALPHA);

        // buttons
        $this->add_action_buttons(true, get_string('savechanges', 'admin'));
    }
}


?>
<?php

/*
require_once("$CFG->libdir/formslib.php");
 
class image_form extends moodleform {
 
    function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;

        $course      = $this->_customdata['course']; // this contains the data of this form
        $data      	 = $this->_customdata['data'];
        $options     = $this->_customdata['options'];

         				
 		
		//$mform->addElement(type, id, title_string, ?, options e.g: array('maxbytes' => $maxbytes, 'accepted_types' => '*')); 
		$mform->addElement('filemanager', 'userfile_filemanager', get_string('file'), null, $options);
        $mform->addElement('hidden', 'returnurl', $data->returnurl);
		
        $this->add_action_buttons(true, get_string('savechanges'));
        $this->set_data($data);
        
        
        
*/
/*
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];

        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);
        $mform->addElement('hidden', 'returnurl', $data->returnurl);

        $this->add_action_buttons(true, get_string('savechanges'));

        $this->set_data($data); 
*/       
/*
		
    }                           // Close the function

}                               // Close the class
*/


?>