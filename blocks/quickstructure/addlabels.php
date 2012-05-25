<?php
    require_once(dirname(__FILE__) . '/../../config.php');
    require_once('structurelib.php');    
    require_once('labels_form.php');    
    require_login();
    
    global $DB;    
    
    $cid = required_param('cid',PARAM_INTEGER);
    $course = $DB->get_record('course', array('id'=>$cid));

    $context = get_context_instance(CONTEXT_COURSE, $cid);   
    
    if(!has_capability('moodle/course:update', $context)){  
        print_error("no access allowed!");
    }
    //DebugBreak();  
    $PAGE->set_pagelayout('base');
    $PAGE->set_context($context);
    $PAGE->set_url('/blocks/quickstructure/addlabels.php');
    $PAGE->requires->js('/blocks/quickstructure/quickstructure.js');
    $PAGE->requires->css('/blocks/quickstructure/quickstructure.css');
    $PAGE->requires->js_init_call('init_painting'); 
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('stageI','block_quickstructure'));

    $mform = new labels_form('addimages.php',array('cid'=>$cid));
    $mform->display();

    echo '<br/><br/><br/><br/>';
    echo $OUTPUT->footer();   
                                    

?>