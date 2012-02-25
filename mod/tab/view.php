<?php  // $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * TAB
 *
 * @author : Patrick Thibaudeau
 * @version $Id: version.php,v 1.0 2007/07/01 16:41:20
 * @package tab
 **/

require("../../config.php");
require_once("lib.php");
require_once("locallib.php");
require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');


$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT); // tab ID

if ($id) {
    if (!$cm = get_coursemodule_from_id("tab", $id)) {
        error("Course Module ID was incorrect");
    }

    if (!$tab = $DB->get_record("tab", array("id" => $cm->instance))) {
        error("Course module is incorrect");
    }

} else {
    if (!$tab = $DB->get_record("tab", array("id" => $a))) {
        error("Course module is incorrect");
    }

    if (!$cm = get_coursemodule_from_instance("tab", $tab->id, $course->id)) {
        error("Course Module ID was incorrect");
    }
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

require_capability('mod/tab:view', $context);
add_to_log($course->id, "tab", "view", "view.php?id=$cm->id", "$tab->id");

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

/// Print the page header

$PAGE->set_url('/mod/tab/view.php', array('id' => $cm->id));
$PAGE->set_title($tab->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($tab);

if ($PAGE->user_allowed_editing()) {
    $buttons = '<table><tr><td><form method="get" action="' . $CFG->wwwroot . '/course/mod.php"><div>' .
               '<input type="hidden" name="update" value="' . $cm->id . '" />' .
               '<input type="submit" value="' . get_string('updatethis', 'tab') . '" /></div></form></td></tr></table>';
    $PAGE->set_button($buttons);
}
//Gather javascripts and css
$PAGE->requires->js('/mod/tab/js/SpryTabbedPanels.js', true);
$PAGE->requires->js('/mod/tab/js/tab.js');
$PAGE->requires->css('/mod/tab/SpryTabbedPanels.css');
$PAGE->requires->css('/mod/tab/styles.css');
echo $OUTPUT->header();

//echo $OUTPUT->heading(format_string($tab->name), 2, 'main', 'pageheading');

$strtabs = get_string("modulenameplural", "tab");
$strtab = get_string("modulename", "tab");

//gather all Tab modules within the course. Needed if display tab menu is selected
//$results = get_coursemodules_in_course('tab', $course->id);
//Must get more information in order to display menu options properly.
//Therefore I cannot use get_coursemodules_in_course
$results = $DB->get_records_sql('SELECT {course_modules}.id as id,{course_modules}.visible as visible, {tab}.name as name, {tab}.taborder as taborder,
                                        {tab}.menuname as menuname FROM ({modules} INNER JOIN {course_modules} ON {modules}.id = {course_modules}.module)
                                        INNER JOIN {tab} ON {course_modules}.instance = {tab}.id WHERE ((({modules}.name)=\'tab\') AND (({course_modules}.course)=' . $course->id . '))
                                        ORDER BY taborder;');


$tabdisplay = '';

if ($tab->displaymenu == 1) {

    $tabdisplay .= "<style>
                        #tabcontent {
                        margin-left:  17%;
                        padding: 0 10px;

                    }</style>";
    $tabdisplay .= '<div id="tab-menu-wrapper">' . "\n";
    $tabdisplay .= '	<ul class="menutable" width="100%" border="0" cellpadding="4">' . "\n";
    $tabdisplay .= '	<li class="menutitle">' . $tab->menuname . '</li>' . "\n";
    $i = 0; ///needed to determine color change on cell
    foreach ($results as $result) { /// foreach
        //only print the tabs that have the same menu name
        if ($result->menuname == $tab->menuname) {
            //only print visible tabs within the menu

            if ($result->visible == 1 || has_capability('moodle/course:update', $coursecontext)) {
                $tabdisplay .= '	<li';
                if ($tab->name == $result->name) { //old code for different color = if ($i % 2) {
                    $tabdisplay .= ' class="row">';
                } else {
                    $tabdisplay .= '>';
                }
                $tabdisplay .= '<a href="view.php?id=' . $result->id . '" >' . $result->name . '</a>';

            }
        }
        $tabdisplay .= '</li>' . "\n";
        $i++;
    }
    $tabdisplay .= '</ul>' . "\n";
    $tabdisplay .= '</div>' . "\n";
}
//print tab content here
$tabdisplay .= '<div id="tabcontent">' . "\n";
$tabdisplay .= '<div id="TabbedPanels1" class="TabbedPanels">' . "\n";
$tabdisplay .= '  <ul class="TabbedPanelsTabGroup">' . "\n";

//-------------------------------Get tabs-----------------------------------------------
$options = $DB->get_records('tab_content', array('tabid' => $tab->id), 'tabcontentorder');
$options = array_values($options);
$i = 0;

foreach (array_keys($options) as $key) {
    $tabdisplay .= '    <li class="TabbedPanelsTab" tabindex="0">' . $options[$key]->tabname . '</li>';
}
$tabdisplay .= '  </ul>' . "\n";
$tabdisplay .= '  <div class="TabbedPanelsContentGroup">' . "\n";

$editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => true);


//Add content
foreach (array_keys($options) as $key) {

    //New conditions now exist. Must verify if embedding a pdf or url
    //Content must change accordingly
    //$pdffile[$key] = $options[$key]->pdffile;


    $externalurl[$key] = $options[$key]->externalurl;
    //Eventually give option for height within the form. Pass this by others, because it could be confusing.
    $iframehieght[$key] = '600px';

    if (!empty($externalurl[$key])) {
                          //todo check url
        if(!preg_match('{https?:\/\/}',$externalurl[$key])){
            $externalurl[$key] = 'http://' . $externalurl[$key];
        }
    } else {

        if (empty($options[$key]->format)) {
            $options[$key]->format = 1;
        }
        $content[$key] = file_rewrite_pluginfile_urls($options[$key]->tabcontent, 'pluginfile.php', $context->id, 'mod_tab', 'content', $options[$key]->id);
        $content[$key] = format_text($content[$key], FORMAT_MOODLE, $editoroptions, $context);
    }
    //Enter into proper div
    //Check for pdf
    if (!empty($externalurl[$key]) && preg_match('/\bpdf\b/i', $externalurl[$key])) {
        debugging("Tabdisplay: Found pdf->external_url=". $externalurl[$key], DEBUG_DEVELOPER);
        $html_content = tab_embed_general(process_urls($externalurl[$key]), '', get_string('embed_fail_msg','tab'). "<a href='$externalurl[$key]' target='_blank' >". get_string('embed_fail_link_text', 'tab') . '</a>', 'application/pdf');
    } elseif(!empty($externalurl[$key])) {
        $html_content = tab_embed_general(process_urls($externalurl[$key]), '', get_string('embed_fail_msg','tab'). "<a href='$externalurl[$key]' target='_blank' >". get_string('embed_fail_link_text', 'tab') . '</a>', 'text/html');
    }
    else{
        $html_content = $content[$key];
    }
    $tabdisplay .= '   <div class="TabbedPanelsContent"><p>' . $html_content . '</p></div>' . "\n";
}
$tabdisplay .= '	</div>' . "\n";
$tabdisplay .= '</div>' . "\n";
$tabdisplay .= '</div>' . "\n";
echo $tabdisplay;

echo $OUTPUT->footer();