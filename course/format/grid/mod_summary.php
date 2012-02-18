<?php // $Id: mod.php,v 1.127.2.3 2009-02-05 13:41:18 stronk7 Exp $

    require("../../../config.php");
    require_once("../../lib.php");
    require_once("lib.php");
    
    require_login();

    $course = optional_param('course', '', PARAM_INT);
    $showsummary    = optional_param('showsummary', 0, PARAM_INT);

    $summary_status = get_summary_visibility($course); //ensure format_grid_summary field status exists
    $DB->set_field("format_grid_summary", "show_summary", $showsummary, array("course_id" => $course, "id" => $summary_status->id));

    redirect("../../view.php?id=$course");

?>