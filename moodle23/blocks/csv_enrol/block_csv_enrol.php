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

class block_csv_enrol extends block_base {

    function init() {
    	$this->title = get_string('csvenrol','block_csv_enrol');
    }

    function specialization() {
    }

    function applicable_formats() {
        return array('course-view' => true);
    }

    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $CFG, $USER, $PAGE, $OUTPUT, $id;

	 $currentcontext = get_context_instance(CONTEXT_COURSE,$id);

        if ($this->content !== NULL) {
            return $this->content;
        }
        if (empty($this->instance)) {
            return null;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        if (isloggedin() && has_capability('mod/csv_enrol:uploadcsv',$currentcontext,$USER->id)) {   // Show the block

            $renderer = $this->page->get_renderer('block_csv_enrol');
            $this->content->text = $renderer->csv_enrol_tree($currentcontext);

            $this->content->text .= $OUTPUT->single_button(new moodle_url('/blocks/csv_enrol/edit.php',
                array('returnurl'=>$PAGE->url->out(), 'courseid' => $id )),
				get_string('manageuploads','block_csv_enrol'), 'get');

        }
        return $this->content;
    }

}
