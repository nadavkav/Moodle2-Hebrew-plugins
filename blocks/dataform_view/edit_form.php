<?php

/**
 * This file is part of the Dataform_view block for Moodle - http://moodle.org/.
 *
 * @package block-dataform_view
 * @copyright 2011 Itamar Tzadok
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * The development of this block used the glossary_random block (2007101509)
 * as a template. To the extent that dataform_view code corresponds to 
 * glossary_random code, certain copyrights on the glossary_random block
 * may obtain, including:
 * @copyright 2009 Tim Hunt
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle. If not, see <http://www.gnu.org/licenses/>.
 */

class block_dataform_view_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $DB;

        // buttons
        //-------------------------------------------------------------------------------
    	$this->add_action_buttons();

        // Fields for editing HTML block title and contents.
        //--------------------------------------------------------------
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('title', 'block_dataform_view'));
        $mform->setDefault('config_title', get_string('pluginname','block_dataform_view'));
        $mform->setType('config_title', PARAM_MULTILANG);

        if ($dataforms = $DB->get_records_menu('dataform', array('course' => $this->block->course->id), 'name', 'id,name')) {        
            foreach($dataforms as $key => $value) {
                $dataforms[$key] = strip_tags(format_string($value, true));
            }
            $dataforms = array(0 => get_string('choosedots')) + $dataforms;
        } else {
            $dataforms = array(0 => get_string('nodataforms', 'block_dataform_view'));
        }    
        $mform->addElement('select', 'config_dataform', get_string('select_dataform', 'block_dataform_view'), $dataforms);

        // and select views to put in dropdown box
        if (!empty($this->block->config->dataform)) {
            if ($views = $DB->get_records_menu('dataform_views', array('dataid' => $this->block->config->dataform), 'name', 'id,name')) {
                foreach($views as $key => $value) {
                    $views[$key] = strip_tags(format_string($value, true));
                }
                $views = array(0 => get_string('choosedots')) + $views;
            } else {
                $views = array(0 => get_string('noviews', 'block_dataform_view'));
            }
            $mform->addElement('select', 'config_view', get_string('select_view', 'block_dataform_view'), $views);
        }
    }
    
    
}
