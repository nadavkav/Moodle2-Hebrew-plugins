<?php  //$Id: settings.php,v 1.1 2009-04-01 21:04:34 jfilip Exp $

global $PAGE;
require_once $CFG->dirroot . '/mod/elluminate/lib.php';
$PAGE->requires->js('/mod/elluminate/elltestconnection.js');

$settings->add(new admin_setting_configtext('elluminate_server', get_string('elluminate_server', 'elluminate'),
                   get_string('configserver', 'elluminate'), 'http://localhost:8080', PARAM_URL));

$settings->add(new admin_setting_configtext('elluminate_adapter', get_string('elluminate_adapter', 'elluminate'),
                   get_string('configadapter', 'elluminate'), 'moodle', PARAM_ALPHA));

$settings->add(new admin_setting_configtext('elluminate_auth_username', get_string('elluminate_auth_username', 'elluminate'),
                   get_string('configauthusername', 'elluminate'), '', PARAM_RAW));

$settings->add(new admin_setting_configpasswordunmask('elluminate_auth_password', get_string('elluminate_auth_password', 'elluminate'),
                   get_string('configauthpassword', 'elluminate'), ''));

$duration    = array();
$duration[0] = get_string('disabled', 'elluminate');

for ($i = 1; $i <= 365; $i++) {
    $duration[$i] = $i;
}           

$boundary_times = array(
    -1  => get_string('choose'),
    0  => '0',
    15 => '15',
    30 => '30',
    45 => '45',
    60 => '60'
);

$max_talkers = array(
	-1 => get_string('choose'),
	1 => '1',
	2 => '2',
	3 => '3',
	4 => '4',
	5 => '5',
	6 => '6'
);

$settings->add(new admin_setting_configselect('elluminate_boundary_default', get_string('elluminate_boundary_default', 'elluminate'),
                   get_string('configboundarydefault', 'elluminate'), ELLUMINATELIVE_BOUNDARY_DEFAULT, $boundary_times));

$settings->add(new admin_setting_configselect('elluminate_pre_populate_moderators', get_string('elluminate_pre_populate_moderators', 'elluminate'),
					get_string('configprepopulatemoderators', 'elluminate'), 0, array(0 => get_string('no'), 1 => get_string('yes')))); 

$settings->add(new admin_setting_configselect('elluminate_permissions_on', get_string('elluminate_permissions_on', 'elluminate'),
					get_string('configpermissionson', 'elluminate'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));
                   
$settings->add(new admin_setting_configselect('elluminate_raise_hand', get_string('elluminate_raise_hand', 'elluminate'),
   					get_string('configraisehand', 'elluminate'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));
   
$settings->add(new admin_setting_configselect('elluminate_all_moderators', get_string('elluminate_all_moderators', 'elluminate'),
   					get_string('configopenchair', 'elluminate'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));
   
$settings->add(new admin_setting_configselect('elluminate_must_be_supervised', get_string('elluminate_must_be_supervised', 'elluminate'),
   					get_string('configmustbesupervised', 'elluminate'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));
   					
$settings->add(new admin_setting_configselect('elluminate_max_talkers', get_string('elluminate_max_talkers', 'elluminate'),
                   get_string('configmaxtalkers', 'elluminate'), ELLUMINATELIVE_MAX_TALKERS, $max_talkers));
                      
$settings->add(new admin_setting_configselect('elluminate_ws_debug', get_string('elluminate_ws_debug', 'elluminate'),
                   get_string('configwsdebug', 'elluminate'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));               
                  
$str = '<center><input type="button" onclick="return elltestConnection(document.getElementById(\'adminsettings\'), \''. $CFG->wwwroot . '\');" value="' .
       get_string('testconnection', 'elluminate') . '" /></center>';       

$settings->add(new admin_setting_heading('elluminate_test', '', $str));



