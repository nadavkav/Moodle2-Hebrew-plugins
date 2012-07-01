<?php

/**
 * Structure step to restore one etherpad activity
 */
class restore_etherpad_activity_structure_step extends restore_activity_structure_step {
 
    protected function define_structure() {
 
        $paths = array();

        $paths[] = new restore_path_element('etherpad', '/activity/etherpad');
 
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }
 
    protected function process_etherpad($data) {
        global $DB;
  
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
 
        $data->introformat = $this->apply_date_offset($data->introformat);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
 
        // insert the etherpad record
        $newitemid = $DB->insert_record('etherpad', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }
    
    protected function after_execute() {
        // Add etherpad related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_etherpad', 'intro', null);
    }
}