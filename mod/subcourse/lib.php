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

define('SUBCOURSE_NO_ENROLMENT', 0);
define('SUBCOURSE_META_ENROLMENT', 1);
define('SUBCOURSE_QUAL_ENROLMENT', 2);


/**
 * Library of functions and constants for module subcourse
 */

require_once(dirname(__FILE__).'/exceptions.php');

/**
 * The list of fields to copy from remote grade_item
 * @return array
 */
function subcourse_get_fetched_item_fields() {
    return array('gradetype', 'grademax', 'grademin', 'scaleid');
}

/**
 * Given an object containing all the necessary data, (defined by the form)
 * this function will create a new instance and return the id number of the new
 * instance.
 *
 * @param object $subcourse
 * @return int The id of the newly inserted subcourse record
 */
function subcourse_add_instance($subcourse) {
    global $DB;

    $subcourse->timecreated = time();
    $subcourse->compulsory = isset($subcourse->compulsory) ? 1 : 0;
    $newid = $DB->insert_record("subcourse", $subcourse);

    // create grade_item but do not fetch grades - the context does not exist yet and we can't
    // get users by capability
    try {
        subcourse_grades_update($subcourse->course, $newid, $subcourse->refcourse,
                                $subcourse->name, true);
    } catch (subcourse_localremotescale_exception $e) {
        mtrace($e->getMessage());
    }

    if ($subcourse->addmeta == SUBCOURSE_META_ENROLMENT) {
        // Add a metacourse enrolment instance to the sub course so that it inherits enrolments
        // from this one.
        subcourse_add_meta($subcourse);
    } else if ($subcourse->addmeta == SUBCOURSE_QUAL_ENROLMENT) {
        // Add a qualification enrolment instance to the sub course so that it inherits enrolments
        // from this one.
        subcourse_add_qual($subcourse);
    }

    return $newid;
}

/**
 * Given an object containing all the necessary data, (defined by the form)
 * this function will update an existing instance with new data.
 *
 * @param object $subcourse
 * @return boolean Success/Fail
 */
function subcourse_update_instance($subcourse) {
    global $DB, $COURSE;

    $subcourse->timemodified = time();
    $subcourse->id = $subcourse->instance;
    if (!isset($subcourse->instantredirect)) {
        $subcourse->instantredirect = 0;
    }

    // If we have a form with the refcourse disabled, we won't get that data sent back via
    $existingrecord = $DB->get_record('subcourse', array('id' => $subcourse->id));
    if (!isset($subcourse->refcourse)) {
        $subcourse->refcourse = $existingrecord->refcourse;
    }

    try {
        subcourse_grades_update($subcourse->course, $subcourse->id,
                                $subcourse->refcourse, $subcourse->name);
    } catch (subcourse_localremotescale_exception $e) {
        mtrace($e->getMessage());
    }
    $subcourse->timefetched = time();
    $subcourse->compulsory = isset($subcourse->compulsory) ? 1 : 0;

    $DB->update_record("subcourse", $subcourse);

    // We need to allow toggle of the compulsory field of the enrolment instance
    $existingqual = subcourse_qual_exists($COURSE->id, $subcourse->refcourse);
    $existingmeta = subcourse_meta_exists($COURSE->id, $subcourse->refcourse);

    if ($subcourse->addmeta == SUBCOURSE_NO_ENROLMENT) {
        if ($existingqual) {
            subcourse_remove_qual($subcourse);
        }
        if ($existingmeta) {
            subcourse_remove_meta($subcourse);
        }

    } else if ($subcourse->addmeta == SUBCOURSE_META_ENROLMENT) {
        // Add metacourse enrolment instance to the sub course so that it inherits
        // enrolments from this one. Cannot be removed during update operation.
        if (!$existingmeta) {
            subcourse_add_meta($subcourse);
        }
        if ($existingqual) {
            subcourse_remove_qual($subcourse);
        }
    } else if ($subcourse->addmeta == SUBCOURSE_QUAL_ENROLMENT) {
        // Add metacourse enrolment instance to the sub course so that it inherits
        // enrolments from this one. Cannot be removed during update operation.
        if (!$existingqual) {
            subcourse_add_qual($subcourse);
        }
        if ($existingmeta) {
            subcourse_remove_meta($subcourse);
        }
    }

    return true;
}

/**
 * Removes an instance of meta course enrolment between the current course and the ref course. This
 * assumes that there will only be one meta course enrolment between the two and that it will be
 * created by this module.
 *
 * @param stdClass $subcourse
 * @return bool
 */
function subcourse_remove_meta($subcourse) {
    global $DB;

    if ($instance = $DB->get_record('enrol', array('courseid' => $subcourse->refcourse,
                                                  'enrol' => 'meta',
                                                  'customint1' => $subcourse->course))) {
        $plugin = enrol_get_plugin('meta');
        $plugin->delete_instance($instance);
    }
    return true;
}

/**
 * Removes an instance of meta course enrolment between the current course and the ref course. This
 * assumes that there will only be one meta course enrolment between the two and that it will be
 * created by this module.
 *
 * @param stdClass $subcourse
 * @return bool
 */
function subcourse_remove_qual($subcourse) {
    global $DB;

    if ($instance = $DB->get_record('enrol', array('courseid' => $subcourse->refcourse,
                                                   'enrol' => 'qualification',
                                                   'customint1' => $subcourse->course))) {
        $plugin = enrol_get_plugin('qualification');
        $plugin->delete_instance($instance);
    }
    return true;
}

/**
 * Checks to see if a meta course enrolment already exists for this combination of courses
 *
 * @param int $course
 * @param int $refcourse
 * @return object
 */
function subcourse_qual_exists($course, $refcourse) {
    global $DB;

    $instance = $DB->get_record('enrol', array('courseid' => $refcourse,
                                               'enrol' => 'qualification',
                                               'customint1' => $course));
    return $instance;
}

/**
 * Checks to see if a meta course enrolment already exists for this combination of courses
 *
 * @param int $course
 * @param int $refcourse
 * @return object
 */
function subcourse_meta_exists($course, $refcourse) {
    global $DB;

    $instance = $DB->get_record('enrol', array('courseid' => $refcourse,
                                               'enrol' => 'meta',
                                               'customint1' => $course));
    return $instance;
}

/**
 * Adds a meta enrolment to the refcourse
 *
 * @param $subcourse
 * @return bool
 */
function subcourse_add_meta($subcourse) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/enrol/meta/locallib.php');

    // Make a new enrolment instance
    $enrol = enrol_get_plugin('meta');
    $course = $DB->get_record('course', array('id' => $subcourse->refcourse), '*', MUST_EXIST);
    $enrol->add_instance($course, array('customint1' => $subcourse->course));
    enrol_meta_sync($course->id);
}

/**
 * Adds a qualification enrolment to the subcourse
 *
 * @param $subcourse
 * @return bool
 */
function subcourse_add_qual($subcourse) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/enrol/qualification/locallib.php');

    // Make a new enrolment instance
    $enrol = enrol_get_plugin('qualification');
    $course = $DB->get_record('course', array('id' => $subcourse->refcourse), '*', MUST_EXIST);
    $enrol->add_instance($course, array('customint1' => $subcourse->course,
                                                   'customint2' => $subcourse->compulsory));
    enrol_qualification_sync($course->id);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function subcourse_delete_instance($id) {
    global $DB;

    if (!$subcourse = $DB->get_record("subcourse", array("id" => $id))) {
        return false;
    }

    # Delete any dependent records here #
    $DB->delete_records("subcourse", array("id" => $subcourse->id));

    subcourse_remove_meta($subcourse);
    subcourse_remove_qual($subcourse);

    return true; // throws exceptions now

}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param $course
 * @param $user
 * @param $mod
 * @param $subcourse
 * @return null
 * @todo Finish documenting this function
 */
function subcourse_user_outline($course, $user, $mod, $subcourse) {
    return true;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param $course
 * @param $user
 * @param $mod
 * @param $subcourse
 * @return boolean
 * @todo Finish documenting this function
 */
function subcourse_user_complete($course, $user, $mod, $subcourse) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in subcourse activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @uses $CFG
 * @param $course
 * @param $isteacher
 * @param $timestart
 * @return boolean
 * @todo Finish documenting this function
 */
function subcourse_print_recent_activity($course, $isteacher, $timestart) {
    return false; //  True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 */
function subcourse_cron() {
    global $DB;

    $subcourse_instances = $DB->get_records('subcourse', null, '', 'id, course, refcourse');
    if (empty($subcourse_instances)) {
        return true;
    }
    $updatedids = array();
    echo "Fetching grades from remote gradebooks...\n";
    foreach ($subcourse_instances as $subcourse) {
        $message = "Subcourse $subcourse->id: fetching grades from course $subcourse->refcourse ".
                   "to course $subcourse->course ... ";
        echo $message;
        try {
            subcourse_grades_update($subcourse->course, $subcourse->id, $subcourse->refcourse);
            $updatedids[] = $subcourse->id;
            echo "ok\n";
        } catch (subcourse_localremotescale_exception $e) {
            echo get_string($e->errorcode, 'subcourse')."\n";
        }
    }
    subcourse_update_timefetched($updatedids);

    return true;
}

/**
 * Must return an array of grades for a given instance of this module,
 * indexed by user.  It also returns a maximum allowed grade.
 *
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $subcourseid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 */
function subcourse_grades($subcourseid) {
    global $DB;
    $subcourse = $DB->get_record("subcourse", array("id" => $subcourseid), '', 'id, refcourse');
    $refgrades = subcourse_fetch_refgrades($subcourse->id, $subcourse->refcourse);
    $return = new stdClass();
    $return->grades = $refgrades->grades;
    $return->maxgrade = $refgrades->grademax;

    return $return;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of subcourse. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $subcourseid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function subcourse_get_participants($subcourseid) {
    return false;
}

/**
 * This function returns if a scale is being used by one subcourse
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $subcourseid ID of an instance of this module
 * @param int $scaleid
 * @return mixed
 * @todo Finish documenting this function
 */
function subcourse_scale_used($subcourseid, $scaleid) {
    $return = false;

    return $return;
}

/**
 * Checks if scale is being used by any instance of subcourse.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param int $scaleid
 * @return boolean True if the scale is used by any subcourse
 */
function subcourse_scale_used_anywhere($scaleid) {
    global $DB;
    if ($scaleid and $DB->get_record('subcourse', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}


/**
 * Returns the list of courses in which the user has permission to view the grade book
 *
 * Does not return the id of the current $COURSE and the site course (front page).
 *
 * @param int $currrent What course do we currently have? Don't exclude it.
 * @param int $userid The ID of user for which we want to get the list of courses. Defaults to
 * current $USER id.
 * @access public
 * @return array The list of course records
 */
function subcourse_available_courses($currrent = null, $userid = null) {
    global $COURSE, $USER, $DB;

    $courses = array(); // to be returned
    if (empty($userid)) {
        $userid = $USER->id;
    }
    $fields = 'fullname,shortname,idnumber,category,visible,sortorder';
    $mycourses = get_user_capability_course('moodle/grade:viewall', $userid,
                                            true, $fields, 'sortorder');
    $existingsubcourses = $DB->get_records('subcourse', array('course' => $COURSE->id));

    if ($mycourses) {
        foreach ($mycourses as $mycourse) {
            if ($mycourse->id != $COURSE->id &&
                $mycourse->id != SITEID) {

                foreach ($existingsubcourses as $existingsubcourse) {
                    if ($existingsubcourse->refcourse == $currrent) {
                        // We want to leave the current one in the list if we are editing.
                        continue;
                    }
                    if ($mycourse->id == $existingsubcourse->refcourse) {
                        continue 2;
                    }
                }

                $courses[] = $mycourse;
            }
        }
    }

    return $courses;
}


/**
 * Fetches grade_item info and grades from the referenced course
 *
 * Returned structure is
 *  object(
 *      ->grades = array[userid] of object(->userid ->rawgrade ->feedback ->feedbackformat)
 *      ->grademax
 *      ->grademin
 *      ->itemname
 *      ...
 *  )
 *
 * @access public
 * @param int $subcourseid ID of subcourse instance
 * @param int $refcourseid ID of referenced course
 * @param bool|\boolan $gradeitemonly If true, fetch only grade item info without grades
 * @return object Object containing grades array and gradeitem info
 */
function subcourse_fetch_refgrades($subcourseid, $refcourseid, $gradeitemonly = false) {
    global $CFG;

    $fetchedfields = subcourse_get_fetched_item_fields();

    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    $return = new stdClass();
    $return->grades = array();

    if (empty($refcourseid)) {
        return false;
    }
    $refgradeitem = grade_item::fetch_course_item($refcourseid);

    // get grade_item info
    foreach ($fetchedfields as $property) {
        if (!empty($refgradeitem->$property)) {
            $return->$property = $refgradeitem->$property;
        } else {
            $return->$property = null;
        }
    }

    // if the remote grade_item is non-global scale, do not fetch grades - they can't be used
    if (($refgradeitem->gradetype == GRADE_TYPE_SCALE)
        && (!subcourse_is_global_scale($refgradeitem->scaleid))
    ) {

        $gradeitemonly = true;
        $return->localremotescale = true;
    }

    if (!$gradeitemonly) {
        // get grades
        $cm = get_coursemodule_from_instance("subcourse", $subcourseid);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $users = get_users_by_capability($context, 'mod/subcourse:begraded', 'u.id,u.lastname',
                                         'u.lastname', '', '', '', '', false, true);
        foreach ($users as $user) {
            $grade = new grade_grade(array('itemid' => $refgradeitem->id, 'userid' => $user->id));
            $grade->grade_item =& $refgradeitem;
            $return->grades[$user->id]->userid = $user->id;
            $return->grades[$user->id]->rawgrade = $grade->finalgrade;
            $return->grades[$user->id]->feedback = $grade->feedback;
            $return->grades[$user->id]->feedbackformat = $grade->feedbackformat;
        }
    }

    return $return;
}

/**
 * Create or update grade item and grades for given subcourse
 *
 * @access public
 * @param int $courseid     ID of referencing course (the course containing the instance of
 * subcourse)
 * @param int $subcourseid  ID of subcourse instance
 * @param int $refcourseid  ID of referenced course (the course to take grades from)
 * @param str $itemname     Set the itemname
 * @param bool $gradeitemonly If true, fetch only grade item info without grades
 * @param bool $reset Reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function subcourse_grades_update($courseid, $subcourseid, $refcourseid, $itemname = null,
                                 $gradeitemonly = false, $reset = false) {
    global $CFG;
    $fetchedfields = subcourse_get_fetched_item_fields();

    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    $refgrades = subcourse_fetch_refgrades($subcourseid, $refcourseid, $gradeitemonly);
    if (!empty($refgrades->localremotescale)) {
        // unable to fetch remote grades - local scale is used in the remote course
        throw new subcourse_localremotescale_exception($subcourseid);
    }
    $params = array();

    foreach ($fetchedfields as $property) {
        if (!empty ($refgrades->$property)) {
            $params[$property] = $refgrades->$property;
        }
    }
    if (!empty($itemname)) {
        $params['itemname'] = $itemname;
    }

    $grades = $refgrades->grades;

    if ($reset) {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/subcourse', $courseid, 'mod', 'subcourse', $subcourseid,
                        0, $grades, $params);
}

/**
 * Checks if a remote scale can be re-used, i.e. if it is global (standard, server wide) scale
 *
 * @param mixed $scaleid ID of the scale
 * @access public
 * @return boolean True if scale is global, false if not.
 */
function subcourse_is_global_scale($scaleid) {
    global $DB;

    if (!is_numeric($scaleid)) {
        throw new moodle_exception('errnonnumeric', 'subcourse');
    }

    if (!$DB->get_record('scale', array('id' => $scaleid, 'courseid' => 0), 'id')) {
        // no such scale with courseid ==0
        return false;
    } else {
        // found the global scale
        return true;
    }
}

/**
 * Updates the timefetched timestamp for given subcourses
 *
 * @param array|int $subcourseids ID of subcourse instance or array of IDs
 * @param mixed $time The timestamp, defaults to the current time
 * @access public
 * @uses $CFG
 * @return bool
 */
function subcourse_update_timefetched($subcourseids, $time = null) {
    global $DB;

    if (is_numeric($subcourseids)) {
        $subcourseids = array($subcourseids);
    }
    if (!is_array($subcourseids)) {
        return false;
    }
    if (count($subcourseids) == 0) {
        return false;
    }
    if (empty($time)) {
        $time = time();
    }
    if (!is_numeric($time)) {
        return false;
    }
    $subcourseids = implode(',', $subcourseids);
    list($sql, $params) = $DB->get_in_or_equal($subcourseids);
    $DB->set_field_select('subcourse', 'timefetched', $time, "id $sql", $params);
    return true;
}

/**
 * This will provide summary info about the user's grade in the subcourse below the link on
 * the course/view.php page
 *
 * @param cm_info $cm
 * @return void
 */
function mod_subcourse_cm_info_view(cm_info $cm) {
    global $USER, $CFG, $DB, $OUTPUT, $PAGE;

    // cache the courses where we will show progressbars to reduce DB queries
    static $studentcourses = null;
    static $userenrolments = null;
    static $assmgrcourses = null;
    static $enrolmentinstances = null;

    $html = '';

    $subcourse = $DB->get_record('subcourse', array('id' => $cm->instance));
    $refcourse = $DB->get_record('course', array('id' => $subcourse->refcourse));

    if (is_null($enrolmentinstances)) {
        // Cache enrolment plugins for all subcourses in this course
        $sql = "SELECT enrol.id, enrol.courseid, enrol.enrol, enrol.customint2 AS compulsory,
                       COUNT(ue.id) AS count, enrol.customint1
                  FROM {enrol} enrol
            INNER JOIN {subcourse} subcourse
                    ON enrol.courseid = subcourse.refcourse
             LEFT JOIN {user_enrolments} ue
                    ON ue.enrolid = enrol.id
                 WHERE enrol.status = ".ENROL_INSTANCE_ENABLED."

              GROUP BY enrol.id, enrol.courseid, enrol.enrol, enrol.customint2
              ORDER BY enrol.enrol ASC   ";
        $params = array();
        $params['thiscourseid'] = $subcourse->refcourse;
        $enrolmentinstances = $DB->get_records_sql($sql, $params);
    }

    if (is_null($userenrolments)) { // Fill the cache
        $sql = "SELECT ue.*, enrol.courseid, enrol.enrol, enrol.customint1
                  FROM {user_enrolments} ue
            INNER JOIN {enrol} enrol
                    ON ue.enrolid = enrol.id
                 WHERE ue.userid = :userid ";
        $userenrolments = $DB->get_records_sql($sql, array('userid' => $USER->id));
    }

    if (is_null($studentcourses)) { // Fill the cache
        list($rolesql, $params) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles),
                                                       SQL_PARAMS_NAMED);
        $sql = "SELECT course.id, context.id AS context
                  FROM {course} course
            INNER JOIN {context} context
                    ON course.id = context.instanceid
                       AND context.contextlevel = ".CONTEXT_COURSE."
                 WHERE EXISTS (SELECT 1
                                 FROM {role_assignments} ra
                                WHERE ra.contextid = context.id
                                  AND ra.roleid {$rolesql}
                                  AND ra.userid = :userid) ";
        $params['userid'] = $USER->id;
        $studentcourses = $DB->get_records_sql($sql, $params);
    }

    // Show icons to indicate enrolment status if it is a qualification
    $subcourseenrol = $DB->get_record('enrol', array('enrol' => 'qualification',
                                                     'courseid' => $subcourse->refcourse,
                                                     'customint1' => $cm->course));
    $selfenrol = false;
    $qualenrol = false;
    $metaenrol = false;
    foreach ($enrolmentinstances as $enrolmentinstance) {
        if ($enrolmentinstance->enrol == 'self') {
            $selfenrol = $enrolmentinstance;
        } else if ($enrolmentinstance->enrol == 'qualification') {
            $qualenrol = $enrolmentinstance;
        } else if ($enrolmentinstance->enrol == 'qualification') {
            $metaenrol = $enrolmentinstance;
        }
    }

    // Icons need to share a class to make them the right size
    $html .= html_writer::start_tag('span', array('class' => 'subcourse_icons'));

    $isstudentinparentcourse = in_array($subcourse->course, array_keys($studentcourses));
    $isstudentinsubcourse = in_array($subcourse->refcourse, array_keys($studentcourses));
    $assmgrfilename = $CFG->dirroot.'/blocks/assmgr/classes/assmgr_progress_bar.class.php';
    $assessmentmanagerenabled = false;
    if (file_exists($assmgrfilename)) {
        if ($DB->get_field('block', 'visible', array('name' => 'assmgr'))) {
            $assessmentmanagerenabled = true;
        }
    }

    $isassmgrcourse = false;
    if ($assessmentmanagerenabled) {
        // cache the courses that have an assessmentmanager block
        if (is_null($assmgrcourses)) {
            $sql = "SELECT DISTINCT context.instanceid
                      FROM {context} context
                INNER JOIN {block_instances} blocks
                        ON context.id = blocks.parentcontextid
                     WHERE context.contextlevel = ".CONTEXT_COURSE."
                       AND ".$DB->sql_compare_text('blocks.blockname')." = 'assmgr'";
            $assmgrcourses = $DB->get_records_sql($sql);
        }
        $isassmgrcourse = in_array($subcourse->refcourse, array_keys($assmgrcourses));
    }

    // only show the enrollment and progress stuff to students
    if ($isstudentinparentcourse) {

        $currentenrolmethods = array(); // Tell the user how they are enrolled
        foreach ($userenrolments as $userenrolment) {
            if ($userenrolment->courseid == $refcourse->id) {
                $currentenrolmethods[$userenrolment->enrol] = $userenrolment->enrol;
                // Differentiate between meta enrolments for this course and others
                if ($userenrolment->enrol == 'meta' || $userenrolment->enrol == 'qualification') {
                    if ($userenrolment->customint1 == $subcourse->course) {
                        $currentenrolmethods[$userenrolment->enrol] .= ' (from this course)';
                    } else {
                        $currentenrolmethods[$userenrolment->enrol] .= ' (from a different course)';
                    }
                }
            }
        }

        if ($currentenrolmethods) {
            $enrolledstring = get_string('enrolled', 'subcourse',
                                         implode(', ', $currentenrolmethods));
            $imageoptions = array('src' => $OUTPUT->pix_url('enrolled', 'mod_subcourse'),
                                  'alt' => $enrolledstring,
                                  'title' => $enrolledstring);
            $html .= html_writer::empty_tag('img', $imageoptions);

            // TODO If they are self enrolled, allow them to unenrol from here
            /*
            if (isset($currentenrolmethods['self']) &&
                has_capability('enrol/self:unenrolself', $PAGE->context)) {
                $action = '/enrol/qualification/enrolconfirm.php';
                $html .= html_writer::start_tag('form', array('action' => $action));
                $html .= html_writer::empty_tag('input', array('type' => 'hidden',
                                                               'name' => 'userid',
                                                               'value' => $USER->id));
                $html .= html_writer::empty_tag('input', array('type' => 'hidden',
                                                               'name' => 'enrolid',
                                                               'value' => $subcourseenrol->id));
                $html .= html_writer::empty_tag('input', array('type' => 'submit'));
                $html .= html_writer::end_tag('form');

            }
            */

            // Show icon to go to assessmentmanager if we need to
        }
        /*
        if (empty($currentenrolmethods) && $selfenrol) {// allow self enrol
            // Code to read from tables that define option groups e.g. 2 of these 5 courses
            // will go here.
            $action = '/enrol/qualification/enrolconfirm.php';
            $html .= html_writer::start_tag('form', array('action' => $action));
            $html .= html_writer::empty_tag('input', array('type' => 'hidden',
                                                           'name' => 'userid',
                                                           'value' => $USER->id));
            $html .= html_writer::empty_tag('input', array('type' => 'hidden',
                                                           'name' => 'enrolid',
                                                           'value' => $selfenrol->id));
            $html .= html_writer::empty_tag('input', array('type' => 'submit',
                                                           'value' => get_string('enrol',
                                                                                 'subcourse')));
            $html .= html_writer::end_tag('form');
        }
        */

    } else {
        // Teachers need to see icons showing info about a course's enrolments
        $compulsoryexists = false;
        foreach ($enrolmentinstances as $enrolmentinstance) {

            $bits = new stdClass();
            $bits->count = $enrolmentinstance->count;
            $bits->enrol = $enrolmentinstance->enrol;

            if ($enrolmentinstance->courseid == $subcourse->refcourse) {
                // We have an enrolment for this course
                if ($enrolmentinstance->enrol == 'qualification' ||
                    $enrolmentinstance->enrol == 'meta') {

                    $compulsoryexists = true;

                    $imgstring = get_string('compulsoryqual', 'subcourse', $bits);
                    $imageoptions = array('src' => $OUTPUT->pix_url('enrolled', 'mod_subcourse'),
                                          'alt' => $imgstring,
                                          'title' => $imgstring);
                    $html .= html_writer::empty_tag('img', $imageoptions);

                } else if ($enrolmentinstance->enrol == 'self' && !$compulsoryexists) {
                    // These students have chosen to take the course

                    $imgstring = get_string('optionalqual', 'subcourse', $bits);
                    $imageoptions = array('src' => $OUTPUT->pix_url('orange-triangle',
                                                                    'mod_subcourse'),
                                          'alt' => $imgstring,
                                          'title' => $imgstring);
                    $html .= html_writer::empty_tag('img', $imageoptions);
                }
            }
        }

        // Link to student assessment manager stuff
        if ($isassmgrcourse) {
            $assmgrstring = get_string('gotoassmgr', 'subcourse', $refcourse->fullname);
            $imageoptions = array('src' => $OUTPUT->pix_url('icon', 'block_assmgr'),
                                  'alt' => $assmgrstring,
                                  'title' => $assmgrstring);
            $assmgrimg = html_writer::empty_tag('img', $imageoptions);
            $url = '/blocks/assmgr/actions/list_portfolio_assessments.php?course_id='.
                   $subcourse->refcourse;
            $html .= html_writer::link($url, $assmgrimg);
        }
    }

    // End of the icons div
    $html .= html_writer::end_tag('span');

    // If assessment manager is installed and enabled for the subcourse, show the progress bar
    if ($isassmgrcourse && $isstudentinsubcourse) {

        $html .= html_writer::start_tag('div', array('class' => 'assmgr subcourse_assmgr'));

        // Icon first as a link
        $assmgrstring = get_string('gotoassmgr', 'subcourse', $refcourse->fullname);
        $imageoptions = array('src' => $OUTPUT->pix_url('icon', 'block_assmgr'),
                              'alt' => $assmgrstring,
                              'title' => $assmgrstring);
        $assmgrimg = html_writer::empty_tag('img', $imageoptions);
        $url = '/blocks/assmgr/actions/edit_portfolio.php?course_id='.
               $subcourse->refcourse;
        $html .= html_writer::link($url, $assmgrimg);

        $course = $studentcourses[$subcourse->refcourse];
        // Teachers should not see the progress bar and courses with no assessment manager instance
        // should not display it

        require_once($assmgrfilename);
        $progress = new assmgr_progress_bar();

        $html .= $progress->get_unit_progress($USER->id, $cm->course, false, 'small');
        $html .= html_writer::end_tag('div');
        $html .= html_writer::empty_tag('br');

    }

    require_once($CFG->dirroot.'/lib/gradelib.php');
    require_once($CFG->dirroot.'/grade/querylib.php');
    require_once($CFG->dirroot.'/lib/grade/grade_item.php');
    require_once($CFG->dirroot.'/lib/grade/grade_grade.php');
    $currentgrade = grade_get_course_grade($USER->id, $cm->course);
    $html .= html_writer::empty_tag('br');
    $html .= html_writer::start_tag('span', array('class' => 'subcourse_grade'));
    $html .= get_string('currentgrade', 'subcourse').' '.$currentgrade->str_grade;
    $html .= html_writer::end_tag('span');

    $cm->set_after_link($html);
}

