<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function xmldb_block_roomscheduler_install() {
 global $DB;
	$timenow = time();
	$sysctx = get_context_instance(CONTEXT_SYSTEM);


        /// Fully setup the Elluminate Moderator role.
            if (!$mrole = $DB->get_record('role', array('shortname'=>'roomschedulermanager'))) {

                if ($rid = create_role(get_string('roomschedulermanager', 'block_roomscheduler'), 'roomschedulemanager',
                                       get_string('roomschedulermanagerdescription', 'block_roomscheduler'))) {

                    $mrole  = $DB->get_record('role', array('id'=>$rid));
                    assign_capability('block/roomscheduler:manage', CAP_ALLOW, $mrole->id, $sysctx->id);
                
                    //Only assignable at system level
                     set_role_contextlevels($mrole->id, array(CONTEXT_SYSTEM));

                } else {
                    $mrole = $DB->get_record('role', array('shortname'=>'roomschedulermanager'));
                    set_role_contextlevels($mrole->id, array(CONTEXT_SYSTEM));
                }
            }



}
?>
