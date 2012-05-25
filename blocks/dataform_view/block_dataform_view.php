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
 * may obtain.
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

/**
 *
 */
class block_dataform_view extends block_base {

    /**
     *
     */
    function init() {
        $this->title = get_string('pluginname','block_dataform_view');            
    }

    /**
     *
     */
    function specialization() {
        global $CFG;
        
        $this->course = $this->page->course;

        // load userdefined title and make sure it's never empty
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname','block_dataform_view');
        } else {
            $this->title = $this->config->title;
        }

        if (empty($this->config->dataform)) {
            if (!empty($this->config->view)) {
                $this->config->view = null;
            }
            return false;
        }

        if (empty($this->config->view)) {
            return false;
        }
    }

    /**
     *
     */
    function instance_allow_multiple() {
        return true;
    }

    /**
     *
     */
    function get_content() {
        global $CFG, $DB;

        if (empty($this->config->dataform)) {
            $this->content->text   = get_string('configstep1','block_dataform_view');
            $this->content->footer = '';
            return $this->content;
        }

        if (empty($this->config->view)) {
            $this->content->text   = get_string('configstep2','block_dataform_view');
            $this->content->footer = '';
            return $this->content;
        }                

        $dataformid = $this->config->dataform;
        $viewid = $this->config->view;
        $course = $this->page->course;

        // validate dataform and reconfigure if needed
        // (we can get here if the dataform has been deleted)
        require_once("$CFG->dirroot/course/lib.php");
        $modinfo = get_fast_modinfo($course);
        if (!isset($modinfo->instances['dataform'][$dataformid])) {
            $this->config->dataform = 0;
            $this->config->view = 0;
            $this->instance_config_commit();

            $this->content->text   = get_string('configstep1','block_dataform_view');
            $this->content->footer = '';
            return $this->content;
        }

        // validate view and reconfigure if needed
        // (we can get here if the view has been deleted)
        if (!$DB->record_exists('dataform_views', array('id' => $viewid, 'dataid' => $dataformid))) {
            // someone deleted the view after configuration
            $this->config->view = 0;
            $this->instance_config_commit();

            $this->content->text   = get_string('configstep2','block_dataform_view');
            $this->content->footer = '';
            return $this->content;
        }

        // content->text or ->footer has to contain something for the _print_block to be called
        $this->content = new object;
        $this->content->text = '';

        // Set a dataform object with guest autologin
        require_once("$CFG->dirroot/mod/dataform/mod_class.php");
        if ($df = new dataform($dataformid, null, true)) {
            if ($view = $df->get_view_from_id($viewid)) {
                $view->set_page();
                $view->set_content();
                $viewcontent = $view->display(array('tohtml' => true));
                $this->content->text = $viewcontent;
            }
        }

        return $this->content;
    }

    /**
     *
     */
    function hide_header() {
        if (empty($this->config->title)) {
            return true;
        }
        return false;
    }

}
