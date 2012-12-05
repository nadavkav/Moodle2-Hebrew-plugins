<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('heading0', get_string('general_instructions', 'bigbluebutton'),get_string('config_instructions', 'bigbluebutton')));

    $settings->add(new admin_setting_configselect('wc_provider', get_string('provider', 'bigbluebutton'),
                     get_string('config_provider', 'bigbluebutton'), null, $options));

    $options = array();
    $options['dualcode'] = get_string('hostbydualcode', 'bigbluebutton');
    $options['self']     = get_string('hostmyself', 'bigbluebutton');
    $settings->add(new admin_setting_configselect('wc_provider', get_string('provider', 'bigbluebutton'),
        get_string('config_provider', 'bigbluebutton'), null, $options));

    $settings->add(new admin_setting_heading('heading1', get_string('config_dc', 'bigbluebutton'),get_string('config_hostbydualcode', 'bigbluebutton')));

    $settings->add(new admin_setting_configtext('wc_accountid', get_string('accountid', 'bigbluebutton'),get_string('config_accountid', 'bigbluebutton')));

    $settings->add(new admin_setting_configpasswordunmask('wc_accountpwd', get_string('accountpwd', 'bigbluebutton'),get_string('config_accountpwd', 'bigbluebutton')));

    $settings->add(new admin_setting_heading('heading2', get_string('config_my', 'bigbluebutton'),get_string('config_hostmyself','bigbluebutton')));

    $settings->add(new admin_setting_configtext('wc_serverhost', get_string('http', 'bigbluebutton'),
                       get_string('config_server', 'bigbluebutton')));

    $settings->add(new admin_setting_configpasswordunmask('wc_securitysalt', get_string('securitysalt', 'bigbluebutton'),
                       get_string('config_salt', 'bigbluebutton')));

    $settings->add(new admin_setting_configtext('wc_meetingrooms', get_string('meetingrooms', 'bigbluebutton'),
                       get_string('config_meetingrooms', 'bigbluebutton'),'*' ));


}
