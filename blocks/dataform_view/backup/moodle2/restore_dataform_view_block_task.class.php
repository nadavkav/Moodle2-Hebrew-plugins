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
 * @copyright 2003 Eloy Lafuente (stronk7) {@link http://stronk7.com}
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

class restore_dataform_view_block_task extends restore_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
    }

    public function get_fileareas() {
        return array(); // No associated fileareas
    }

    public function get_configdata_encoded_attributes() {
        return array(); // No special handling of configdata
    }

    /**
     * This function, executed after all the tasks in the plan
     * have been executed, will perform the recode of the
     * target dataform for the block. This must be done here
     * and not in normal execution steps because the dataform
     * may be restored after the block.
     */
    public function after_restore() {
        global $DB;

        // Get the blockid
        $blockid = $this->get_blockid();

        // Extract block configdata and update it to point to the new dataform
        if ($configdata = $DB->get_field('block_instances', 'configdata', array('id' => $blockid))) {
            $config = unserialize(base64_decode($configdata));
            // Get dataform mapping and replace it in config
            if (!empty($config->dataform)) {
                if ($dataformmap = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'dataform', $config->dataform)) {
                    $config->dataform = $dataformmap->newitemid;
                }
            }
            // Get view mapping and replace it in config
            if (!empty($config->view)) {
                if ($viewmap = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'dataform_view', $config->view)) {
                    $config->view = $viewmap->newitemid;
                }
            }
            $configdata = base64_encode(serialize($config));
            $DB->set_field('block_instances', 'configdata', $configdata, array('id' => $blockid));
        }
    }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
}
