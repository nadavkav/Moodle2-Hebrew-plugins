<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/etherpad/lib.php');


$pagetitle = get_string('modulename', 'etherpad');

$etherpadcfg = get_config('etherpad');

$etherpadsettings = new admin_settingpage('modsettingetherpad', $pagetitle, 'moodle/site:config');


$etherpadsettings->add(new admin_setting_configtext_with_advanced('etherpad/etherpad_apikey',
        'api Key.', '',
        array('value' => $etherpadcfg->etherpad_apikey, 'fix' => false), PARAM_TEXT));
        
$etherpadsettings->add(new admin_setting_configtext_with_advanced('etherpad/etherpad_baseurl',
        'base Url.', '',
        array('value' => $etherpadcfg->etherpad_baseurl, 'fix' => false), PARAM_TEXT));

$ADMIN->add('modsettings', $etherpadsettings);


$settings = NULL;
