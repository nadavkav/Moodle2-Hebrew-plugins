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

/**
 *
 * @since 2.0
 * @package blocks
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_roomscheduler_upgrade($oldversion) {
    global $DB;
$dbman = $DB->get_manager();




 if ($oldversion < 2011150301) {

$timenow = time();
	$sysctx = get_context_instance(CONTEXT_SYSTEM);


        /// Fully setup the Elluminate Moderator role.
            if (!$mrole = $DB->get_record('role', array('shortname'=>'roomschedulermanager'))) {

                if ($rid = create_role(get_string('roomschedulermanager', 'block_roomscheduler'), 'roomschedulemanager',
                                       get_string('roomschedulermanagerdescription', 'block_roomscheduler'))) {

                    $mrole  = $DB->get_record('role', array('id'=>$rid));
                    assign_capability('block/roomscheduler:manage', CAP_ALLOW, $mrole->id, $sysctx->id);
                    set_role_contextlevels($mrole->id, array(CONTEXT_SYSTEM));
                    } else {
                    $mrole = $DB->get_record('role', array('shortname'=>'roomschedulermanager'));
                set_role_contextlevels($mrole->id, array(CONTEXT_SYSTEM));

                }
            }

  upgrade_block_savepoint(true, 2011150301, 'roomscheduler');
    }



    if ($oldversion < 2011140315) {

            // Define field reservable to be added to roomscheduler_rooms
        $table = new xmldb_table('roomscheduler_rooms');
        $field = new xmldb_field('reservable', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'active');

        // Conditionally launch add field reservable
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        upgrade_block_savepoint(true, 2011140315, 'roomscheduler');
    }


    return true;
}