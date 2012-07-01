<?php // $Id: ,v 1.0 2012/03/28 16:10:00 Serafim Panov

    require_once("../../config.php");
    require_once("lib.php");

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    
    if (!$course = $DB->get_record("course", array( "id" => $id))) {
        error("Course ID is incorrect");
    }
    
    require_course_login($course);
    
    add_to_log($course->id, "Etherpad", "view all", "index.php?id=$course->id", "");
    
    $PAGE->set_url('/mod/etherpad/index.php', array('id' => $cm->id));
    
    $title = $course->shortname . ': ' . format_string($etherpad->name);
    $PAGE->set_title($title);
    $PAGE->set_heading($course->fullname);

                 
    if (! $displays = get_all_instances_in_course("etherpad", $course)) {
        notice("There are no displays", "../../course/view.php?id=$course->id");
        die;
    }
    
    echo $OUTPUT->header();
    
    echo "<br />";
    
    print_simple_box_start('center', '500', '#ffffff', 10); 

    foreach ($displays as $display) {
        echo '<a href="view.php?id='.$display->coursemodule.'">'.$display->name.'</a><br />';
    }

    print_simple_box_end();

    echo "<br />";

    echo $OUTPUT->footer();

