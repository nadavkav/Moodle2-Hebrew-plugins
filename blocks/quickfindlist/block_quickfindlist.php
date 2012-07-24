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
 * Defines the block_quickfindlist class
 *
 * @package    block_quickfindlist
 * @copyright  2010 Onwards Taunton's College, UK
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *  Class definition for the Quick Find List block.
 */
class block_quickfindlist extends block_base {

    public function init() {
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->title = get_string('quickfindlist', 'block_quickfindlist');
    }


    public function instance_allow_multiple() {
        return true;
    }

    public function preferred_width() {
        // The preferred value is in pixels
        return 180;
    }

    /**
     *  Generates the block's content
     *
     *  Determines the role configured for this instance, and ensures it doesn't conflict with other
     *  instances on the page.
     *  Then displays a form for searching the users who have that role, and if required, the
     *  results from the submitted search.
     */
    public function get_content() {
        global $CFG, $COURSE, $DB;
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->config->role)) {
            $select = 'SELECT * ';
            $from = 'FROM {block} AS b
                        JOIN {block_instances} AS bi ON b.name = blockname ';
            $where = 'WHERE name = "quickfindlist"
                        AND pagetypepattern = "?"
                        AND parentcontextid = ?
                        AND bi.id < ?';
            $params = array(
                $this->instance->pagetypepattern,
                $this->instance->parentcontextid,
                $this->instance->id
            );
            if ($thispageqflblocks = $DB->get_records_sql($select.$from.$where, $params)) {
                foreach ($thispageqflblocks as $thispageqflblock) {
                    //don't give a warning for blocks without a role configured
                    if (@unserialize(base64_decode($thispageqflblock->configdata))->role < 1) {
                        $this->content->text = get_string('multiplenorole', 'block_quickfindlist');
                        return $this->content;
                    }
                }
            }

            $this->config->role = -1;
        }

        if ($role = $DB->get_record('role', array('id' => $this->config->role))) {
            $roleid = $role->id;
            $this->title = $role->name.get_string('list', 'block_quickfindlist');
        } else {
            $roleid = '-1';
            $strallusers = get_string('allusers', 'block_quickfindlist');
            $strlist = get_string('list', 'block_quickfindlist');
            $this->title = $strallusers.$strlist;
        }

        $context_system = get_context_instance(CONTEXT_SYSTEM);

        if (has_capability('block/quickfindlist:use', $context_system)) {
            if (empty($this->config->userfields)) {
                $this->config->userfields = get_string('userfieldsdefault', 'block_quickfindlist');
            }
            if (empty($this->config->url)) {
                $url = new moodle_url('/user/view.php', array('course' => $COURSE->id));
            } else {
                $url = new moodle_url($this->config->url);
            }
            $name = optional_param('quickfindlistsearch'.$roleid, '', PARAM_TEXT);

            $anchor = html_writer::tag('a', '', array('name' => 'quickfindanchor'.$roleid));
            $searchparams = array(
                'id' => 'quickfindlistsearch'.$roleid,
                'name' => 'quickfindlistsearch'.$roleid,
                'class' => 'quickfindlistsearch',
                'autocomplete' => 'off'
            );
            $search = html_writer::empty_tag('input', $searchparams);
            $progressparams = array(
                'id' => 'quickfindprogress'.$roleid,
                'class' => 'quickfindprogress',
                'src' => $this->page->theme->pix_url('i/loading_small', 'moodle'),
                'alt' => get_string('loading', 'block_quickfindlist')
            );
            $progress = html_writer::empty_tag('img', $progressparams);
            $submitparams = array(
                'type' => 'submit',
                'class' => 'submitbutton',
                'name' => 'quickfindsubmit'.$roleid,
                'value' => get_string('search')
            );
            $submit = html_writer::empty_tag('input', $submitparams);
            $formparams = array(
                'id' => 'quickfindform'.$roleid,
                'action' => $this->page->url.'#quickfindanchor'.$roleid,
                'method' => 'post'
            );
            $form = html_writer::tag('form', $search.$progress.$submit, $formparams);

            $quickfindsubmit[$roleid] = optional_param('quickfindsubmit'.$roleid,
                                                       false,
                                                       PARAM_ALPHA);
            $listcontents = '';
            if (!empty($quickfindsubmit[$roleid])) {
                if (!empty($name)) {
                    $params = array("%$name%");
                    $select = 'SELECT id, firstname, lastname, username ';
                    $from = 'FROM {user} AS u ';
                    $where = "WHERE deleted = 0 AND CONCAT(firstname, ' ', lastname) LIKE ? ";
                    if ($this->config->role != -1) {
                        $params[] = $this->config->role;
                        $subselect = 'SELECT COUNT(*) ';
                        $subfrom = 'FROM {role_assignments} AS ra
                                           JOIN {context} AS c ON c.id = contextid ';
                        $subwhere = 'WHERE ra.userid = u.id
                                           AND ra.roleid=?';
                        if ($COURSE->format != 'site') {
                            $params[] = $COURSE->id;
                            $subwhere .= ' AND contextlevel=50 AND instanceid = ?';
                        }
                        $where .= 'AND ('.$subselect.$subfrom.$subwhere.') > 0 ';
                    }
                    $order = 'ORDER BY lastname';

                    if ($people = $DB->get_records_sql($select.$from.$where.$order, $params)) {
                        foreach ($people as $person) {
                            $userstring = str_replace('[[firstname]]',
                                                      $person->firstname,
                                                      $this->config->userfields);
                            $userstring = str_replace('[[lastname]]',
                                                      $person->lastname,
                                                      $userstring);
                            $userstring = str_replace('[[username]]',
                                                      $person->username,
                                                      $userstring);
                            $linkurl = new moodle_url($url, array('id' => $person->id));
                            $link = html_writer::tag('a', $userstring, array('href' => $linkurl));
                            $listcontents .= html_writer::tag('li', $link);
                        }
                    }
                }
            }
            $list = html_writer::tag('ul', $listcontents, array('id' => 'quickfindlist'.$roleid));

            $jsmodule = array(
                'name'  =>  'block_quickfindlist',
                'fullpath'  =>  '/blocks/quickfindlist/module.js',
                'requires'  =>  array('base', 'node', 'json', 'io')
            );
            $jsdata = array(
                $this->config->role,
                $this->config->userfields,
                $url->out(false),
                $COURSE->format,
                $COURSE->id,
                sesskey()
            );
            $this->page->requires->js_init_call('M.block_quickfindlist.init',
                                                $jsdata,
                                                false,
                                                $jsmodule);
            $this->content->footer='';
            $this->content->text = $anchor.$form.$list;
        }

        return $this->content;

    }
}
