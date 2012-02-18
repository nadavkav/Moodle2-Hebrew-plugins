<style type="text/css" media="screen">
/* <![CDATA[ */
    @import url(<?php echo $CFG->wwwroot ?>/course/format/grid/grid.css);
/* ]]> */
</style>

<?php // $Id: format.php

require_once(dirname(__FILE__) . '/lib.php');     // for grid course format.

$streditsummary  = get_string('editsummary');
$stradd          = get_string('add');
$stractivities   = get_string('activities');
$strshowallweeks = get_string('showallweeks');
$strweek         = get_string('week');
$strgroups       = get_string('groups');
$strgroupmy      = get_string('groupmy');
$editing         = $PAGE->user_is_editing();

if ($editing) {
    $strstudents = moodle_strtolower($course->students);
    $strweekhide = get_string('weekhide', '', $strstudents);
    $strweekshow = get_string('weekshow', '', $strstudents);
    $strmoveup   = get_string('moveup');
    $strmovedown = get_string('movedown');
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);

require_js($CFG->wwwroot.'/course/format/grid/jslib.js');
    
/* Internet Explorer min-width fix. (See theme/standard/styles_layout.css: min-width for Firefox.)
   Window width: 800px, Firefox 763px, IE 752px. (Window width: 640px, Firefox 602px, IE 588px.)    
*/
    
?>

<!--[if IE]>
  <style type="text/css">
  .weekscss-format { width: expression(document.body.clientWidth < 800 ? "752px" : "auto"); }
  </style>
<![endif]-->
<?php
/// Layout the whole page as three big columns (was, id="layout-table")
echo '<div class="weekscss-format">';



/// Start main column
$main_column_class = 'class="';
$main_column_class .= '"';
echo '<div id="middle-column" '. $main_column_class .'>'. skip_main_destination();

//print_heading_block($course->fullname . '', '');

$summary_status = get_summary_visibility($course->id);
if($summary_status->show_summary == 1) {
    /// Section 0 gets placed at the top.

    $section = 0;
    $thissection = $sections[$section];
        
    if ($thissection->summary or $thissection->sequence or isediting($course->id)) {
        echo '<ul class="weekscss">'."\n";
        echo '<li id="section-0" class="section main">';
        echo '<div class="right side">&nbsp;</div>';
    
        echo '<div class="content">';
    
        echo '<div class="summary">';
        $summaryformatoptions->noclean = true;
        echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);
    
        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
            echo '<p><a title="'.$streditsummary.'" '.
                 ' href="editsection.php?id='.$thissection->id.'"><img src="'.$OUTPUT->pix_url('t/edit') . '" '.
                 ' class="icon edit" alt="'.$streditsummary.'" /></a></p>';
        }
        echo '</div>';
    
        print_section($course, $thissection, $mods, $modnamesused);
    
        if (isediting($course->id)) {
            print_section_add_menus($course, $section, $modnames);
            echo ' <a title="'.get_string('hide_summary_alt','format_grid').'" href="format/grid/mod_summary.php?sesskey='.sesskey().'&amp;course='.$course->id.'&amp;showsummary=0">'.
                 '<img src="format/grid/images/into_grid.png" alt="'.get_string('hide_summary_alt','format_grid').'" /> '.get_string('hide_summary','format_grid').' </a>';
        }
    
        echo '</div>';
        echo '</li>';
        echo '</ul>';
    }
}

/// Print all of the icons.

echo '<div id="iconContainer">'."\n";
echo '<ul class="icons">'."\n";

$section = 0; //start at 1 to skip the summary block
if($summary_status->show_summary == 1) {
    $section = 1; //or include the summary block if it's in the grid display
}
$sectionmenu = array();

while ($section <= $course->numsections) {

    if (!empty($sections[$section])) {
        $thissection = $sections[$section];
    } else {
        // Create a new section structure
        unset($thissection);
        $thissection->course = $course->id;
        $thissection->section = $section;
        $thissection->summary = 'Course Topic ' . $section ;
        $thissection->visible = 1;
        if (!$thissection->id = insert_record('course_sections', $thissection)) {
            notify('Error inserting new topic!');
        }
        $sections[$section] = $thissection;
    }

    //check if course is visible to user, if so show course
    $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

    if ($showsection) {

        $new_activity = new_activity($thissection, $course, $mods);
        $sectionicon = grid_format_get_icon($course, $thissection->id, $section, $mods);
        $strtitle = "";
        if($section == 0) {
            $strtitle = get_title($thissection);
            if($strtitle == '&nbsp;' || strlen(trim($strtitle)) == 0) {
                $strtitle = get_string('generalinformation','format_grid');
            }
        } else {
            $strtitle = get_title($thissection);
        }

        //Get the module icon

        if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
            $onclickevent = 'select_topic_edit(event, '.$thissection->section.')';
        } else {
            $onclickevent = 'select_topic(event, '.$thissection->section.')';
        }

        echo '<li> <a href="#section-'.$thissection->section.'" class="icon_link" onclick="'. $onclickevent .'"><p class="icon_content">' . $strtitle.'</p>';
        if($new_activity) {
            echo '<img class="new_activity" src="'.$url = $CFG->wwwroot.'/course/format/grid/images/new_activity.png" />';
        }
        echo '<div class="image_holder">';   
        
        
        if($sectionicon && $sectionicon->imagepath) {
            echo '<img src="'.$url = $CFG->wwwroot . '/pluginfile.php/' . $context->id . '/course/section/' . $thissection->id . 
            '/' . $sectionicon->imagepath .'"/>'; 
        } else if($section == 0) {
            echo '<img src="'.$url = $CFG->wwwroot.'/course/format/grid/info.png">';
        }        
/*
        if($sectionicon->imagepath) {    
            echo '<img src="'.$url = $CFG->wwwroot . '/pluginfile.php/' . $context->id . '/course/section/' . $thissection->id . 
        '/' . $sectionicon->imagepath .'"/>'; 
        }
*/
                
        echo "</div></a>";
        if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
            //echo ' <a title="'.get_string('editimage','format_grid').'" href="format/grid/editimage.php?id='.$sectionicon->id.'">'.
            echo ' <a title="'.get_string('editimage','format_grid').'" href="format/grid/editimage.php?sectionid='.$thissection->id.'&contextid='.$context->id.'&userid='.$USER->id.'">'.
                 '<img src="'.$OUTPUT->pix_url('t/edit').'" alt="'.get_string('editimage','format_grid').'" />'.get_string('editimage','format_grid').'</a>';                    
            if($section == 0) {
                echo ' <a title="'.get_string('display_summary_alt','format_grid').'" href="format/grid/mod_summary.php?sesskey='.sesskey().'&amp;course='.$course->id.'&amp;showsummary=1">'.
                     '<img src="format/grid/images/out_of_grid.png" alt="'.get_string('display_summary_alt','format_grid').'" /> '.get_string('display_summary','format_grid').' </a>';
            }
        }
        echo "</li>";

    }        
    $section++;     
}

echo '</ul>'."\n";  
echo '</div>'."\n"; 

// Note, an ordered list would confuse - "1" could be the clipboard or summary.

echo '<div id="shadebox">';
echo '<div id="shadebox_overlay" style="display:none;" onclick="toggle_shadebox();"></div>';
//echo '<div id="shadebox_overlay" style="display:none;"></div>';
echo '<div id="shadebox_content">';
echo '<img id="shadebox_close" style="display: none;" src="'.$CFG->wwwroot.'/course/format/grid/close.png" onclick="toggle_shadebox();">';

echo "<ul class='topics'>\n";

/// If currently moving a file then show the current clipboard
if (ismoving($course->id)) {
    $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
    $strcancel= get_string('cancel');
    echo '<li class="clipboard">';
    echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.sesskey().'">'.$strcancel.'</a>)';
    echo "</li>\n";
}

/// Print Section 0 with general activities
	$section = 0;
	$thissection = $sections[$section];
	unset($sections[0]);
    
    if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing($course->id)) {
        //echo '<ul class="weekscss">'."\n";
        echo '<li id="section-0" class="section main grid_section">';
        echo '<div class="right side">&nbsp;</div>';
    
        echo '<div class="content">';
    
        echo '<div class="summary">';

    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
    $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
    $summaryformatoptions = new stdClass();
    $summaryformatoptions->noclean = true;
    $summaryformatoptions->overflowdiv = true;
    echo format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);

    if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $coursecontext)) {
        echo '<a title="'.$streditsummary.'" '.
             ' href="editsection.php?id='.$thissection->id.'"><img src="'.$OUTPUT->pix_url('t/edit') . '" '.
             ' class="icon edit" alt="'.$streditsummary.'" /></a>';
    }
    echo '</div>';
    
        print_section($course, $thissection, $mods, $modnamesused);
    
        if ($PAGE->user_is_editing($course->id)) {
            print_section_add_menus($course, $section, $modnames);
        }
    
        echo '</div>';
        echo '</li>';
        //echo '</ul>';
    }


/// Now all the normal modules by topic
/// Everything below uses "section" terminology - each "section" is a topic/module. 

$section = 1;
$sectionmenu = array();

while ($section <= $course->numsections) {

    //if section doesnt exist create it. Set $thissection to point to it.
    if (!empty($sections[$section])) {
        $thissection = $sections[$section];

    } else {
        //Section should have been created in the icons section above. If it's empty then its an error.
        unset($thissection);
        notify('Error, section ' . $section . ' not found!');
        $section++;        
        continue;    
    }

    $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);
           
    if ($showsection) {

        $currenttext = '';
        if (!$thissection->visible) {
            $sectionstyle = ' hidden';
        } else {
            $sectionstyle = '';
        }

        echo '<li id="section-'.$section.'" class="section main'.$sectionstyle.' grid_section" >';
        // Note, 'right side' is BEFORE content.
        echo '<div class="right side">';
   
        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
            if ($thissection->visible) {        // Show the hide/show eye
                echo '<a href="view.php?id='.$course->id.'&amp;hide='.$section.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strweekhide.'">'.
                     '<img src="'.$OUTPUT->pix_url('i/hide') . '" class="icon hide" alt="'.$strweekhide.'" /></a>';
            } else {
                echo '<a href="view.php?id='.$course->id.'&amp;show='.$section.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strweekshow.'">'.
                     '<img src="'.$OUTPUT->pix_url('i/show') . '" class="icon hide" alt="'.$strweekshow.'" /></a>';
            }
            if ($section > 1) {                       // Add a arrow to move section up
                echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=-1&amp;sesskey='.sesskey().'#section-'.($section-1).'" title="'.$strmoveup.'">'.
                     '<img src="'.$OUTPUT->pix_url('t/up') . '" class="icon up" alt="'.$strmoveup.'" /></a>';
            }

            if ($section < $course->numsections) {    // Add a arrow to move section down
                echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=1&amp;sesskey='.sesskey().'#section-'.($section+1).'" title="'.$strmovedown.'">'.
                     '<img src="'.$OUTPUT->pix_url('t/down') . '" class="icon down" alt="'.$strmovedown.'" /></a>';
            }
        }   
   
        echo '</div>';

        echo '<div class="content">';
                                
        if (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible) {  //if visible

            if (!is_null($thissection->name)) {
                echo $OUTPUT->heading($thissection->name, 3, 'sectionname');
            }

			echo '<div class="summary">';
            $summaryformatoptions->noclean = true;
            echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

            if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                     '<img src="'.$OUTPUT->pix_url('t/edit').'" class="icon edit" alt="'.$streditsummary.'" /></a>';
            }
            echo '</div>';

            print_section($course, $thissection, $mods, $modnamesused);

            if (isediting($course->id)) {
                print_section_add_menus($course, $section, $modnames);
            }
        } else {
            $strtitle = get_title($thissection->summary);
            echo '<h2>' . $strtitle . '</h2>';
            echo '<p> This section has been hidden </p>';
        }

        echo '</div>';
        echo "</li>\n";
    }

    $section++;
}
echo "</ul>\n";
echo '</div></div>';

if (!empty($sectionmenu)) {
    echo '<div class="jumpmenu">';
    echo popup_form($CFG->wwwroot.'/course/view.php?id='.$course->id.'&amp;', $sectionmenu,
               'sectionmenu', '', get_string('jumpto'), '', '', true);
    echo '</div>';
}

echo '</div>';

echo '</div>';
echo '<div class="clearer"></div>';

if (!(isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id)))) {
    echo '<script> hide_sections(); </script>';
}

?>
