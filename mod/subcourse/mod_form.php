<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the main subcourse configuration form
 */

require_once('moodleform_mod.php');
require_once($CFG->dirroot.'/mod/subcourse/lib.php'); // defines constants

class mod_subcourse_mod_form extends moodleform_mod {

    public function definition() {

        global $CFG;

        $mform    =& $this->_form;

        // General settings -------------------------------------------------------------
        /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('subcoursename', 'subcourse'),
                           array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        /// Adding the optional "intro" and "introformat" pair of fields
        $this->add_intro_editor(false, get_string('subcourseintro', 'subcourse'));

        // Subcourse information --------------------------------------------------------
        $mform->addElement('header', 'subcoursefieldset', get_string('refcourse', 'subcourse'));

        /// Referenced course selector
        $current = null;
        if (isset($this->current->refcourse) && !empty($this->current->refcourse)) {
            $current = $this->current->refcourse;
        }
        $mycourses = subcourse_available_courses($current);
        $catlist = array();
        $catparents = array();
        make_categories_list($catlist, $catparents);
        $options = array();
        foreach ($mycourses as $mycourse) {
            if (empty($options[$catlist[$mycourse->category]])) {
                $options[$catlist[$mycourse->category]] = array();
            }
            $courselabel = $mycourse->fullname.' ('.$mycourse->shortname.')';
            $options[$catlist[$mycourse->category]][$mycourse->id] = $courselabel;
            if (empty($mycourse->visible)) {
                $hiddenlabel = ' '.get_string('hiddencourse', 'subcourse');
                $options[$catlist[$mycourse->category]][$mycourse->id] .= $hiddenlabel;
            }
        }
        unset($mycourse);
        /**
         * @var $refcourseelement HTML_QuickForm_input
         */
        $refcourseelement = $mform->addElement('selectgroups', 'refcourse',
                                               get_string('refcourselabel', 'subcourse'), $options);
        $mform->addHelpButton('refcourse', 'refcourse', 'subcourse');

        // Instant redirect, or show a page with a description and a link to the course?
        $instantredirect = $mform->addElement('checkbox', 'instantredirect',
                                              get_string('instantredirect', 'subcourse'));

        // Option to add a meta course or qualification enrolment to the other course.
        $metaexists = false;
        $qualexists = false;
        if (!empty($this->current->id)) {
            $metaexists = subcourse_meta_exists($this->current->course, $this->current->refcourse);
            $qualexists = subcourse_qual_exists($this->current->course, $this->current->refcourse);
        }

        // Do we want the course to be one that students must take? Only an option for qualification
        $usequal = false;
        if (file_exists($CFG->dirroot.'/enrol/qualification/lib.php')) {
            if (enrol_is_enabled('qualification')) {
                $usequal = true;
            }
        }

        // If qualification is available, we will use that instead of metacourses, as it's the
        // same thing.
        $addmetaradioitems=array();
        $addmetaradioitems[] = &MoodleQuickForm::createElement('radio', 'addmeta', '',
                                                               get_string('noenrolment',
                                                                          'subcourse'),
                                                               0);
        $addmetaradioitems[] = &MoodleQuickForm::createElement('radio', 'addmeta', '',
                                                               get_string('addmeta',
                                                                          'subcourse'),
                                                               1);
        if ($usequal) {
            $addmetaradioitems[] = &MoodleQuickForm::createElement('radio', 'addmeta', '',
                                                                   get_string('addqual',
                                                                              'subcourse'),
                                                                   2);
        }
        /**
         * @var $addmetaelement HTML_QuickForm_input
         */
        $addmetaelement = $mform->addGroup($addmetaradioitems, 'radioar',
                                           get_string('addenrolment', 'subcourse'),
                                           array('<br />'), false);
        $mform->setDefault('addmeta', 0);

        if ($qualexists || $metaexists) {
            $refcourseelement->updateAttributes(array('disabled' => true));
            $addmetaelement->updateAttributes(array('disabled' => true));
        }

        if ($qualexists) {
            $mform->setDefault('addmeta', 2);
        }
        if ($metaexists) {
            $mform->setDefault('addmeta', 1);
        }

        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

