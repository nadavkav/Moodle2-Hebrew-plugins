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
 * This file contains general functions for the course format Topic
 *
 * @since 2.0
 * @package moodlecore
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_grid_uses_sections() {
    return true;
}

/**
 * Used to display the course structure for a course where format=topic
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = weeks.
 *
 * @param array $path An array of keys to the course node in the navigation
 * @param stdClass $modinfo The mod info object for the current course
 * @return bool Returns true
 */
function callback_grid_load_content(&$navigation, $course, $coursenode) {
    return $navigation->load_generic_course_sections($course, $coursenode, 'topics');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_grid_definition() {
    return get_string('topic');
}

/**
 * The GET argument variable that is used to identify the section being
 * viewed by the user (if there is one)
 *
 * @return string
 */
function callback_grid_request_key() {
    return 'topic';
}

function callback_grid_get_section_name($course, $section) {
    // We can't add a node without any text
    if (!empty($section->name)) {
        return $section->name;
    } else if ($section->section == 0) {
        return get_string('section0name', 'format_topics');
    } else {
        return get_string('topic').' '.$section->section;
    }
}

/**
 * Declares support for course AJAX features
 *
 * @see course_format_ajax_support()
 * @return stdClass
 */
function callback_grid_ajax_support() {
    $ajaxsupport = new stdClass();
    $ajaxsupport->capable = true;
    $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
    return $ajaxsupport;
}

function grid_format_get_icon($course, $sectionid, $sectionnumber = 0) {
    global $CFG, $DB;

    if (!$sectionid) {
        return false;
    }
    if (! $sectionicon = $DB->get_record('format_grid_icon', array('sectionid' => $sectionid))) {

        $newicon                = new stdClass();
        $newicon->sectionid     = $sectionid;

        if (!$newicon->id = $DB->insert_record('format_grid_icon', $newicon)) {
            error('Could not create icon. Grid format database is not ready. An admin must visit the notification section.');
        }
        $sectionicon = false;
    }

    return $sectionicon;
}

//get section icon, if it doesnt exist create it.
function get_summary_visibility($course) {

    global $CFG, $DB;
    if (! $summary_status = $DB->get_record('format_grid_summary', array('course_id' => $course))) {

        $new_status                = new stdClass();
        $new_status->course_id     = $course;
        $new_status->show_summary  = 1;

        if (!$new_status->id = $DB->insert_record('format_grid_summary', $new_status)) {
            error('Could not set summary status. Grid format database is not ready. An admin must visit the notification section.');
        }
        $summary_status = $new_status;
    }

    return $summary_status;
}

//Checks whether there has been new activity in section $section
function new_activity($section, $course, $mods) {
    global $CFG, $USER, $DB;

    // Check for unread activities. Borrowed from core code in a rush and doesn't
    // work exactly as expected. Needs some work, maybe a rewrite.
    $new_activity = false;
    if (isset($USER->lastcourseaccess[$course->id])) {
        $course->lastaccess = $USER->lastcourseaccess[$course->id];
    } else {
        $course->lastaccess = 0;
    }

    $htmlarray = array();
    $sectionmods = explode(",", $section->sequence);

    if(!empty($htmlarray)) {
        return true;
    }
    //Checks logs to see if section has been updated since last login.
    //This cause semi-unexpected behaviour if you're already logged in when it happens
    //in that it will show up for your current log in AND the following log in.
    $sql = "SELECT url FROM $CFG->prefix"."log WHERE course = :courseid AND time > :lastaccess AND action = :edit";
    $params = array("courseid" => $course->id, "lastaccess"=>$course->lastaccess, "edit"=>"editsection");
    $activity = $DB->get_records_sql($sql, $params);
    foreach($activity as $url_obj) {
        $list = explode('=', $url_obj->url);
        if($section->id == $list[1]) {
            return true;
        }
    }
    return $new_activity;
}

//Alias for get_dom_tree() to maintain sensible function call names.
function parse_dom($html) {
    return get_dom_tree($html); //domparser.php
}

//Attempts to return a 40 character title for the section icon.
//If section names are set, they are used. Otherwise it scans the summary
//for what looks like the first line.
function get_title($section) {

    if($section->name != NULL && strlen(trim($section->name)) != 0) {
        $title = $section->name;
    } else {
        $html = $section->summary;
        $title = scan_tag($html, 0);
    }

    if(strlen($title) > 40) {
        $title = substr($title, 0, 40);
    }

    return $title;

}

function scan_tag($text, $position) {
    $terminal_tags = array('P', 'H1', 'H2', 'H3', 'DIV', 'p', 'h1', 'h2', 'h3', 'div'); //tags that create a newline when they close

    $title = '';


    while(true) {

        if($position >= strlen($text)) {
            return $title;
        }

        //Find the start of the next tag
        $tag_start = strpos($text, '<', $position);
        if($tag_start === false) { //if none, return everything
            return $title . trim(substr($text, $position));
        }

        //Add everything before the tag to the title
        $contents = '';
        if($tag_start > $position) {
            $contents = substr($text, $position, $tag_start-$position);
        }
        $title .= trim($contents);


        //Find the end of that tag
        $tag_end = find_tag_end($text, $tag_start+1);
        if($tag_end === false) { //if none, return what we have so far
            return $title;
        }

        //Determine tag name:
        //is it a closing tag?
        $tag_name;
        if($text{$tag_start+1} == "/") {
            $tag_name = get_tag_name($text, $tag_start+2, $tag_end);
            if(in_array($tag_name, $terminal_tags)) { //check if it a newline tag
                if(strlen($title) != 0) {
                    return $title;
                }
            }
        } else {
            $tag_name = get_tag_name($text, $tag_start+1, $tag_end);
            if($tag_name == 'BR' || $tag_name == 'br') { //the only newline tag that isn't a closing tag

                if(strlen($title) != 0) {
                    return $title;
                }
            }
        }

        $position = $tag_end+1;

    }
    return $title;

}

//Finds the '>' part of a tag. Assumes $start is the character AFTER the '<' character
//Returns the position of the end tag, or false if none is found

function find_tag_end($text, $position) {
    $in_quotes = true;
    while($in_quotes) {
        $end_tag_pos = strpos($text, ">", $position);
        if($end_tag_pos === false) {
            return false;
        }

        //Make sure the '>' isn't within quotes.
        $quotes_end = check_quotes($text, $position, $end_tag_pos);
        if($quotes_end > -1) {
            $position = $quotes_end+1;
        } else if($quotes_end === false) {
            return false;
        } else {
            $in_quotes = false;
        }
    }
    return $end_tag_pos;
}


function check_quotes($text, $position, $limit) {
    //Checks to see if there are open quotes between $start and $limit
    $single_s = strpos($text, '\'', $position);
    $double_s = strpos($text, '"', $position);

    //quotes don't interfere
    if( !$single_s && !$double_s
        || (!$single_s && $double_s > $limit)
        || (!$double_s && $single_s > $limit)
        || ($double_s > $limit && $single_s > $limit)
        ) {
        return -1;
    }

    if(!$single_s || $double_s < $single_s) {
        return strpos($text, '"', $double_s+1);
    }

    if(!$double_s || $single_s < $double_s) {
        return strpos($text, '\'', $single_s+1);
    }

    return -1;
}

function get_tag_name($html, $start, $end) {
    //Finds a tags name. Assumes start is character AFTER the '<' character

    $space_pos = strpos($html, ' ', $start);
    $end_name_pos = $space_pos;
    if($space_pos === false || $end < $space_pos) {
        $end_name_pos = $end;
    }

    $tag_name = substr($html, $start, ($end_name_pos - $start));
    return $tag_name;
}

?>