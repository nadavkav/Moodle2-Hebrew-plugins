<?php // $Id: conntest.php,v 1.1.2.2 2009/03/18 16:45:53 mchurch Exp $

/**
 * A simple Web Services connection test script for the configured Blackboard Collaborate server.
 * 
 * @version $Id: conntest.php,v 1.1.2.2 2009/03/18 16:45:53 mchurch Exp $
 * @author Justin Filip <jfilip@oktech.ca>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';
    
    require_login(SITEID, false);

    if (!$site = get_site()) {
        redirect($CFG->wwwroot);
    }

    $serverurl = required_param('serverURL', PARAM_NOTAGS);
    $serveradapter = required_param('serverAdapter', PARAM_NOTAGS);
    $username  = required_param('authUsername', PARAM_NOTAGS);
    $password  = required_param('authPassword', PARAM_NOTAGS);
    $boundary  = required_param('boundaryDefault', PARAM_NOTAGS);
    $maxtalkers = required_param('maxTalkers', PARAM_NOTAGS);
    $prepopulate  = required_param('prepopulate', PARAM_NOTAGS);
    $wsDebug  = required_param('wsDebug', PARAM_NOTAGS);    

	$PAGE->set_url('/mod/elluminate/participants.php', array('serverURL'=>$serverurl,
															'serverAdapter'=>$serveradapter,
															'authUsername'=>$username,
															'authPassword'=>$password,
															'boundaryDefault'=>$boundary,
															'maxTalkers'=>$maxtalkers,
															'prepopulate'=>$prepopulate,
															'wsDebug'=>$wsDebug));
	
    $strtitle = get_string('elluminateconnectiontest', 'elluminate');

	print_header_simple(format_string($strtitle));
    echo $OUTPUT->box_start('generalbox', 'notice');
	
    if (!elluminate_test_connection($serverurl, $serveradapter, $username, $password, $boundary, $maxtalkers, $prepopulate, $wsDebug)) {
        notify(get_string('connectiontestfailure', 'elluminate'));
    } else {
        notify(get_string('connectiontestsuccessful', 'elluminate'), 'notifysuccess');
    }

    echo '<center><input type="button" onclick="self.close();" value="' . get_string('closewindow') . '" /></center>';

    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();


