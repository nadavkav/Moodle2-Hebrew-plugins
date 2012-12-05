<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_bigbluebutton_mod_form extends moodleform_mod {

    function definition() {
      global $CFG;
      $mform = $this->_form;

// Create a list of meeting rooms
     if(isset($CFG->wc_provider)) {
       $provider = $CFG->wc_provider;
     }
     if(isset($CFG->wc_accountid)) {
       $accountid = $CFG->wc_accountid;
     } 
     if(isset($CFG->wc_meetingrooms)) {
       $meetingroom = $CFG->wc_meetingrooms;
     }
     if ($provider=="self") {	// Hosted by customer
				if ($meetingroom=='*') {
          $meetingrooms[0]=rand(10000,99999);
        }
				else {
          $meetingrooms = split(',',$meetingroom); 
          if (sizeof($meetingrooms)==1) {
            $meetingrooms[0]=$meetingroom;
          }
          else {
            $meetingrooms = split(',',$meetingroom);
          }
        }
      }
      else { // Hosted by Dual Code
        $meetingrooms[0]=$accountid."-".rand(10000,99999); 
      }

//-------------------------------------------------------------------------------
        if (sizeof($meetingrooms)==1 && empty($meetingroom)) {
          $mform->addElement('html',get_string('meetingid_empty', 'bigbluebutton'));
        }
				else {
				$mform->addElement('header', 'room', get_string('room', 'bigbluebutton'));
        $mform->addElement('text', 'meetingname', get_string('meetingname', 'bigbluebutton'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('meetingname', null, 'required', null, 'client');
        
        
        if (sizeof($meetingrooms)==1 && !empty($meetingroom)) {
          $mform->addElement('hidden', 'meetingid', $meetingrooms[0]);
        }
        else {
          $options=array();
          for ($i=0;$i<sizeof($meetingrooms);$i++) {
            $options[$i] = $meetingrooms[$i];
          }
          $mform->addElement('select', 'meetingid', get_string('meetingid', 'bigbluebutton'), $options);
          $mform->addRule('meetingid', null, 'required', null, 'client');
        }

        $mform->addElement('password', 'attendeepw', get_string('attendeepw', 'bigbluebutton'), array('size'=>'64'));
        $mform->addRule('attendeepw', null, 'required', null, 'client');

        $mform->addElement('password', 'moderatorpw', get_string('moderatorpw', 'bigbluebutton'), array('size'=>'64'));
        $mform->addRule('moderatorpw', null, 'required', null, 'client');

        $options=array();
        $options[0]  = get_string('newwindow_n', 'bigbluebutton');
        $options[1]  = get_string('newwindow_y', 'bigbluebutton');
        $mform->addElement('select', 'newwindow', get_string('newwindow', 'bigbluebutton'), $options);
        
        $mform->addElement('textarea', 'welcomemsg', get_string('welcomemsg', 'bigbluebutton'), 'wrap="virtual" rows="5" cols="60"');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
		}
} 