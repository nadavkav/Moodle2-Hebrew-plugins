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
 * Keeps track of upgrades to the subcourse module.
 */

/**
 * Perform an upgrade from an old version
 *
 * @param int $oldversion Old (installed) version of the module API
 * @access public
 * @return boolean
 */
function xmldb_subcourse_upgrade($oldversion=0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2011111500) {
        // Define field instantredirect to be added to subcourse
        $table = new xmldb_table('subcourse');
        $field = new xmldb_field('instantredirect', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
                                 XMLDB_NOTNULL, null, '0', 'grade');

        // Conditionally launch add field instantredirect
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // subcourse savepoint reached
        upgrade_mod_savepoint(true, 2011111500, 'subcourse');
    }
    return true;
}
