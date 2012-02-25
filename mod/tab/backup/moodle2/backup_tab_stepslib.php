<?php
/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */
class backup_tab_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $tab = new backup_nested_element('tab', array('id'), array('name','displaymenu','menuname', 'taborder', 'timemodified'));

        $tab_contents = new backup_nested_element('tab_contents');
        
        $tab_content = new backup_nested_element('tab_content', array('id'), array('tabname',
            'tabcontent', 'tabcontentorder', 'externalurl','contentformat', 'timemodified'));

        // Build the tree
        $tab->add_child($tab_contents);
            $tab_contents->add_child($tab_content);
        // Define sources
        $tab->set_source_table('tab', array('id' => backup::VAR_ACTIVITYID));

            $tab_content->set_source_sql(
                    'SELECT * FROM {tab_content}
                        WHERE tabid = ?',
                    array(backup::VAR_PARENTID));

        // Define id annotations
        //$tab_content->annotate_ids('tabid', 'tabid');

        // Define file annotations
        $tab_content->annotate_files('mod_tab', 'content', 'id');

        // Return the root element (tab), wrapped into standard activity structure
        return $this->prepare_activity_structure($tab);
    }
}
?>
