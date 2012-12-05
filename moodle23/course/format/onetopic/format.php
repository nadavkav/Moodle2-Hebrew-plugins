<?php // $Id: format.php,v 2.0.0.2 2011/03/01 17:10:00 cirano Exp $
//
// You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @since 2.0
 * @package contribution
 * @copyright 2012 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Display the whole course as "tabs"
// Included from "view.php"
// It is based of the "topics" format

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

$topic = optional_param('topic', -1, PARAM_INT);

if ($topic != -1) {
     if (!isloggedin() or isguestuser()) {
         $USER->display[$course->id] = $topic;
         $displaysection = $topic;
    }
    else {
        $displaysection = course_set_display($course->id, $topic);
    }
} 
else {
    if (isset($USER->display[$course->id])) {
        $displaysection = $USER->display[$course->id];
    } else {
        $displaysection = course_set_display($course->id, 0);
    }
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);

$streditsummary  = get_string('editsummary');
$stradd          = get_string('add');
$stractivities   = get_string('activities');
$strshowalltopics = get_string('showalltopics');
$strtopic         = get_string('topic');
$strgroups       = get_string('groups');
$strgroupmy      = get_string('groupmy');
$editing         = $PAGE->user_is_editing();

if ($editing) {
    $strtopichide = get_string('hidetopicfromothers');
    $strtopicshow = get_string('showtopicfromothers');
}

// Print the Your progress icon if the track completion is enabled
$completioninfo = new completion_info($course);
echo $completioninfo->display_help_icon();

echo $OUTPUT->heading(get_string('topicoutline'), 2, 'headingblock header outline');

// Note, an ordered list would confuse - "1" could be the clipboard or summary.
echo "<ul class='topics'>\n";

/// If currently moving a file then show the current clipboard
if (ismoving($course->id)) {
    $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
    $strcancel= get_string('cancel');
    echo '<li class="clipboard">';
    echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.sesskey().'">'.$strcancel.'</a>)';
    echo "</li>\n";
}

//Print tabs
    $section = 0;

    $sectionmenu = array();
    $tabs = array();

    $default_topic = -1;
    while ($section <= $course->numsections) {

        if (empty($sections[$section])) {
            $thissection = new stdClass;
            $thissection->course = $course->id;   // Create a new section structure
            $thissection->section = $section;
            $thissection->name    = null;
            $thissection->summary = '';
            $thissection->summaryformat = FORMAT_HTML;
            $thissection->visible = 1;
            $thissection->id = $DB->insert_record('course_sections', $thissection);
            
            $sections[$section] = $thissection;
        }
        else {
            $thissection = $sections[$section];
        }
        
        $showsection = true;
        if (!$thissection->visible) {
            $showsection = false;
        }
        else if ($section == 0 && !($thissection->summary or $thissection->sequence or $PAGE->user_is_editing())){
            $showsection = false;
        }
        
        if (!$showsection) {
            $showsection = (has_capability('moodle/course:viewhiddensections', $context) or !$course->hiddensections);
        }

        if (isset($displaysection)) {
            if ($showsection) {
                
                if ($default_topic < 0) {
                    $default_topic = $section;
                    
                    if ($displaysection == 0) {
                        $displaysection = $default_topic;
                    }
                }

                $sectionname = get_section_name($course, $thissection);

                if ($displaysection != $section) {
                    $sectionmenu[$section] = $sectionname;
                }
                
                $tabs[] = new tabobject("tab_topic_" . $section, $CFG->wwwroot.'/course/view.php?id='.$course->id . '&topic='.$section,
                '<font style="white-space:nowrap">' . s($sectionname) . "</font>", s($sectionname));
            }
        }
        $section++;
    }

    if (count($tabs) > 0) {
        print_tabs(array($tabs), "tab_topic_" . $displaysection);
    }


    $timenow = time();
    $section = $displaysection;

    if ($section <= $course->numsections) {

        if (!empty($sections[$section])) {
            $thissection = $sections[$section];

        }

        $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

        if ($showsection) {

            $currenttopic = ($course->marker == $section);

            $currenttext = '';
            if (!$thissection->visible) {
                $sectionstyle = ' hidden';
            } else if ($currenttopic) {
                $sectionstyle = ' current';
                $currenttext = get_accesshide(get_string('currenttopic','access'));
            } else {
                $sectionstyle = '';
            }

            echo '<li id="section-'.$section.'" class="section main clearfix'.$sectionstyle.'" >'; //'<div class="left side">&nbsp;</div>';

            echo '<div class="left side" style="display:none">' . $currenttext . $section . '</div>';
            // Note, 'right side' is BEFORE content.
            echo '<div class="right side">&nbsp;</div>';

            echo '<div class="content">';
            if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
                echo get_string('notavailable');
            } else {
                echo '<div class="summary">';
                if ($thissection->summary) {
                    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
                    $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
                    $summaryformatoptions = new stdClass();
                    $summaryformatoptions->noclean = true;
                    $summaryformatoptions->overflowdiv = true;
                    echo format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);
                } else {
                   echo '&nbsp;';
                }
    
                if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                         '<img src="'.$OUTPUT->pix_url('t/edit') . '" class="iconsmall edit" alt="'.$streditsummary.'" /></a><br /><br />';
                }
                echo '</div>';
    
                print_section($course, $thissection, $mods, $modnamesused);
                echo '<br />';
                if ($PAGE->user_is_editing()) {
                    print_section_add_menus($course, $section, $modnames);
                }
            }
    
            echo '</div>';
            echo "</li>\n";
        }
    }

echo "</ul>\n";

if (!empty($sectionmenu)) {
    $select = new single_select(new moodle_url('/course/view.php', array('id'=>$course->id)), 'topic', $sectionmenu);
    $select->label = get_string('jumpto');
    $select->class = 'jumpmenu';
    $select->formid = 'sectionmenu';
    echo $OUTPUT->render($select);
}
