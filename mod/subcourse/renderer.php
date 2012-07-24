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
 * Defines the standard HTML output for the subcourse module
 */
class mod_subcourse_renderer extends plugin_renderer_base {

    /**
     * Displays the subcourse on the view.php page
     *
     * @param $subcourse
     * @return string
     */
    public function render_subcourse($subcourse) {
        global $OUTPUT, $CFG, $DB;

        $refcourse = $DB->get_record('course', array('id' => $subcourse->refcourse));

        $outputhtml = '';

        $refcourselink = new stdClass();
        $refcourselink->name = $refcourse->fullname;
        $refcourselink->href = $CFG->wwwroot.'/course/view.php?id='.$refcourse->id;

        $outputhtml .= $OUTPUT->heading(get_string('gotocoursename', 'subcourse',
                                                   $refcourselink), 3);
        $outputhtml .= $OUTPUT->box_start('generalbox', 'fetchinfobox');

        $outputhtml .= html_writer::start_tag('div');
        $outputhtml .= format_text($subcourse->intro);
        $outputhtml .=  html_writer::end_tag('div');

        if (empty($subcourse->timefetched)) {
            $outputhtml .= get_string('lastfetchnever', 'subcourse');
        } else {
            $outputhtml .= get_string('lastfetchtime', 'subcourse',
                                      userdate($subcourse->timefetched));
        }

        $linkurl = '/mod/subcourse/view.php?id='.$subcourse->cm->id;
        $outputhtml .= html_writer::start_tag('form',
                                              array('action' => $linkurl,
                                                    'method' => 'post'));
        $outputhtml .= html_writer::empty_tag('input',
                                              array('type' => 'hidden',
                                                    'name' => 'sesskey',
                                                    'value' => sesskey()));
        $outputhtml .= html_writer::empty_tag('input',
                                              array('type' => 'hidden',
                                                    'name' => 'fetchnow',
                                                    'value' => 1));
        $outputhtml .= html_writer::empty_tag('input',
                                              array('type' => 'submit',
                                                   'value' => get_string('fetchnow', 'subcourse')));
        $outputhtml .= html_writer::end_tag('form');
        $outputhtml .= $OUTPUT->box_end();

        return $outputhtml;

    }
}