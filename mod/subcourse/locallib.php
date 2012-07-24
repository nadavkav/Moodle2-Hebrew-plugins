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
 * This holds class definitions for the subcourse module
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Basic data object to allow renderer to be used. Other methods can be added later
 */
class subcourse implements renderable {

    public $id;

    public $course;

    public $name;

    public $intro;

    public $introformat;

    public $timecreated;

    public $timemodified;

    public $timefetched;

    public $refcourse;

    public $grade;

    public $instantredirect;

    private $fields = array(
        'id',
        'course',
        'name',
        'intro',
        'introformat',
        'timecreated',
        'timemodified',
        'timefetched',
        'refcourse',
        'grade',
        'instantredirect'
    );

    public function __construct($subcourse = 0) {
        global $DB;

        if (!$subcourse) {
            return;
        }

        if (is_int($subcourse)) {
            $subcourse = $DB->get_record('subcourse', array('id' => $subcourse));
        }

        if (!is_object($subcourse)) {
            return;
        }

        foreach ($this->fields as $field) {
            if (isset($subcourse->$field)) {
                $this->$field = $subcourse->$field;
            }

        }
    }

}