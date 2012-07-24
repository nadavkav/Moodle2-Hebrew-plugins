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
 * Defines the form for editing block instances
 *
 * @package    block_quickfindlist
 * @copyright  2010 Onwards Taunton's College, UK
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing Quick Find List block instances
 */
class block_quickfindlist_edit_form extends block_edit_form {
    /**
     * Adds block-specific fields to the parent form
     *
     * Allows configuration of the role to be searched, the text to display for each result, and
     * the url the results should link to.
     *
     * @param mixed $mform
     * @access protected
     * @return void
     */
    protected function specific_definition($mform) {
        global $DB;
        global $COURSE;

        if (!empty($this->config->role)) {
            $currentrole = $this->config->role;
        } else {
            $currentrole = null;
        }

        $allusers = new stdClass;
        $allusers->id = -1;
        $allusers->name = get_string('allusers', 'block_quickfindlist');
        $roles = array_merge(array($allusers), $DB->get_records('role'));

        $rolesused = array();

        $select = 'SELECT * ';
        $from = 'FROM {block} AS b
                    JOIN {block_instances} AS bi ON b.name = blockname ';
        $where = 'WHERE name = "quickfindlist"
                    AND pagetypepattern = "?"
                    AND parentcontextid = ?
                    AND bi.id < ?';
        $params = array(
            $this->block->instance->pagetypepattern,
            $this->block->instance->parentcontextid,
            $this->block->instance->id
        );
        if ($blocksonthispage = $DB->get_records_sql($select.$from.$where, $params)) {
            foreach ($blocksonthispage as $block) {
                if ($block->config = unserialize(base64_decode($block->configdata))) {
                    $rolesused[] = $block->config->role;
                }
            }
        }

        $strrole = get_string('role', 'block_quickfindlist');
        $select = HTML_QuickForm::createElement('select', 'config_role', $strrole);

        foreach ($roles as $role) {
            $attributes = array();
            if ($currentrole == $role->id) {
                $attributes['selected'] = 'selected';
            } else if (in_array($role->id, $rolesused)) {
                $attributes['disabled'] = 'disabled';
            }

            $value = $role->id;
            $text = $role->name;

            $params = array($role->id);
            $subselect = 'SELECT COUNT(*) ';
            $subfrom = 'FROM {role_assignments} AS ra
                           JOIN {context} AS c ON c.id = contextid ';
            $subwhere = 'WHERE ra.userid = {user}.id
                           AND ra.roleid = ?';

            if ($COURSE->format != 'site') {
                $params[] = $COURSE->id;
                $subwhere .= ' AND contextlevel = 50 AND instanceid = ?';
            }

            $where = '('.$subselect.$subfrom.$subwhere.') > 0
                AND deleted = 0';

            $usercount = $DB->count_records_select('user', $where, $params);
            if ($usercount > 5000) {
                echo $text .= get_string('lotsofusers', 'block_quickfindlist', $usercount);
            }
            $select->addOption($text, $value, $attributes);
        }

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        $mform->addElement($select);
        $strusefields = get_string('userfields', 'block_quickfindlist');
        $userfieldsdefault = get_string('userfieldsdefault', 'block_quickfindlist');
        $mform->addElement('text', 'config_userfields', $struserfields);
        $mform->setDefault('config_userfields', $userfieldsdefault);
        $mform->addElement('text', 'config_url', get_string('url', 'block_quickfindlist'));
    }
}
