<?PHP  // $Id: view.php,v 1.0 2012/03/28 18:30:00 Serafim Panov Exp $ 

/// This page prints a particular instance of etherpad
/// (Replace etherpad with the name of your module)

    require_once("../../config.php");
    require_once("lib.php");
    require_once("etherpad-lite-client.php");

    $id = optional_param('id', 0, PARAM_INT);    // Course Module ID
    $a  = optional_param('a', NULL, PARAM_TEXT);     // etherpad ID

    if ($id) {
        if (! $cm = $DB->get_record("course_modules", array("id" => $id))) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
            error("Course is misconfigured");
        }
    
        if (! $etherpad = $DB->get_record("etherpad", array("id" => $cm->instance))) {
            error("Course module is incorrect");
        }

    } else {
        if (! $etherpad = $DB->get_record("etherpad", array("id" => $a))) {
            error("Course module is incorrect");
        }
        if (! $course = $DB->get_record("course", array("id" => $etherpad->course))) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("etherpad", $etherpad->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);
    
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Print the page header

    add_to_log($course->id, "etherpad", "view", "view.php?id=$id", "$cm->instance");
    
// Activate epad user
    //etherpad_activate_session();

// Initialize $PAGE, compute blocks
    
    $PAGE->set_url('/mod/etherpad/view.php', array('id' => $id));
    
    $PAGE->requires->js('/mod/etherpad/js/jquery.min.js', true);
    $PAGE->requires->js('/mod/etherpad/js/etherpad.js', true);
    
    $title = $course->shortname . ': ' . format_string($etherpad->name);
    $PAGE->set_title($title);
    $PAGE->set_heading($course->fullname);
    
    echo $OUTPUT->header();
    
/// Print the main part of the page
    
    echo $OUTPUT->box_start('generalbox');
    
    echo "<div align=center>".$etherpad->intro."</div>";
    
    echo $OUTPUT->box_end();

    echo $OUTPUT->box_start('generalbox');
    
    echo "<div align=center>";
    
    echo "<script type=\"text/javascript\">
    jQuery(document).ready(function() {
        jQuery('#ePad').pad({'host': '{$etherpadcfg->etherpad_baseurl}', 'padId':'{$etherpad->padname}', 'baseUrl': '/p/', 'showChat': true, 'rtl': true, 'userName': '{$USER->firstname} {$USER->lastname}','showControls': true,'showLineNumbers': true, 'height': 500});
    });
    </script>";
    
    echo '<div id="ePad"></div>';
    
    echo "</div>";
    
    echo $OUTPUT->box_end();

/// Finish the page
    echo $OUTPUT->footer();

