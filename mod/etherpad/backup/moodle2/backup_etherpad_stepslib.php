<?php

/**
 * Define the complete etherpad structure for backup, with file and id annotations
 */     
class backup_etherpad_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $etherpad = new backup_nested_element('etherpad', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'timemodified'));
        
        // Define sources
        
        $etherpad->set_source_table('etherpad', array('id' => backup::VAR_ACTIVITYID));
 
        // Return the root element (etherpad), wrapped into standard activity structure
        
        return $this->prepare_activity_structure($etherpad);
    }
}