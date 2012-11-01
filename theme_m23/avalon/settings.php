<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	// logo image setting (ltr)
	$name = 'theme_avalon/logocollege';
	$title = get_string('logocollege','theme_avalon');
	$description = get_string('logocollege_desc', 'theme_avalon');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    //$setting = new admin_setting_configfile($name, $title, $description, $CFG->dataroot.'/images/logo.png');
	$settings->add($setting);

    // logo image setting (rtl)
    $name = 'theme_avalon/logocollegertl';
    $title = get_string('logocollegertl','theme_avalon');
    $description = get_string('logocollegertl_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    //$setting = new admin_setting_configfile($name, $title, $description, $CFG->dataroot.'/images/logortl.png');
    $settings->add($setting);

    // logo college footer image setting (ltr)
    $name = 'theme_avalon/logocollegefooter';
    $title = get_string('logocollegefooter','theme_avalon');
    $description = get_string('logocollegefooter_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // logo college footer image setting (rtl)
    $name = 'theme_avalon/logocollegefooterrtl';
    $title = get_string('logocollegefooterrtl','theme_avalon');
    $description = get_string('logocollegefooter_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // banner image setting (ltr)
    $name = 'theme_avalon/topbanner';
    $title = get_string('topbanner','theme_avalon');
    $description = get_string('topbanner_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // banner image setting (rtl)
    $name = 'theme_avalon/topbannerrtl';
    $title = get_string('topbannerrtl','theme_avalon');
    $description = get_string('topbannerrtl_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    //top banner spacer image setting
    $name = 'theme_avalon/topbanner1pix';
    $title = get_string('topbanner1pix','theme_avalon');
    $description = get_string('topbanner1pix_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    //footer banner spacer image setting
    $name = 'theme_avalon/footer1pix';
    $title = get_string('footer1pix','theme_avalon');
    $description = get_string('footer1pix_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // Communication blocks background color setting
	$name = 'theme_avalon/blockbgcolor_communication';
	$title = get_string('linkcolor','theme_avalon');
	$description = get_string('blockbgcolor_communication_desc', 'theme_avalon');
	$default = '#d1ebfb';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

    // Navigation blocks background color setting
    $name = 'theme_avalon/blockbgcolor_navigation';
    $title = get_string('linkcolor','theme_avalon');
    $description = get_string('blockbgcolor_navigation_desc', 'theme_avalon');
    $default = '#dae8b0';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Information blocks background color setting
    $name = 'theme_avalon/blockbgcolor_information';
    $title = get_string('linkcolor','theme_avalon');
    $description = get_string('blockbgcolor_information_desc', 'theme_avalon');
    $default = '#eadd96';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Personal blocks background color setting
    $name = 'theme_avalon/blockbgcolor_personal';
    $title = get_string('linkcolor','theme_avalon');
    $description = get_string('blockbgcolor_personal_desc', 'theme_avalon');
    $default = '#ead5b4';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);


    // link to email service setting
    $name = 'theme_avalon/service_email';
    $title = get_string('serviceemail','theme_avalon');
    $description = get_string('serviceemail_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // link to help service setting
    $name = 'theme_avalon/service_help';
    $title = get_string('servicehelp','theme_avalon');
    $description = get_string('servicehelp_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // link to rss service setting
    $name = 'theme_avalon/service_rss';
    $title = get_string('servicerss','theme_avalon');
    $description = get_string('servicerss_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // link to about service setting
    $name = 'theme_avalon/service_about';
    $title = get_string('serviceabout','theme_avalon');
    $description = get_string('serviceabout_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // link to search service setting
    $name = 'theme_avalon/service_search';
    $title = get_string('servicesearch','theme_avalon');
    $description = get_string('servicesearch_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // link to home service setting
    $name = 'theme_avalon/service_home';
    $title = get_string('servicehome','theme_avalon');
    $description = get_string('servicehome_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // link to support service setting
    $name = 'theme_avalon/service_support';
    $title = get_string('servicesupport','theme_avalon');
    $description = get_string('servicesupport_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // Preset color setting
    $default = array('#12789b','#caf1fe','#90cce0','#edfbff','#00ade6','#008cb8','#004a61','#51300d','#ead5b4');
    for ($i=1; $i<10 ;$i++) {
        $name = 'theme_avalon/color'.$i;
        $title = get_string('color'.$i,'theme_avalon');
        $description = get_string('color'.$i.'_desc', 'theme_avalon');
        $previewconfig = NULL;
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default[$i-1], $previewconfig);
        $settings->add($setting);

    }

    // College system-wide Notice (above Toipcs) setting
    $name = 'theme_avalon/collegenotice';
    $title = get_string('collegenotice', 'theme_avalon');
    $description = get_string('collegenotice_desc', 'theme_avalon');
    $setting = new admin_setting_configtextarea($name, $title , $description, '', PARAM_RAW);
    $settings->add($setting);

    // College About setting
    $name = 'theme_avalon/collegeabout';
    $title = get_string('collegeabout','theme_avalon');
    $description = get_string('collegeabout_desc', 'theme_avalon');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
    $settings->add($setting);

    // College Footer (links) setting
    $name = 'theme_avalon/collegefooter';
    $title = get_string('collegefooter', 'theme_avalon');
    $description = get_string('collegefooter_desc', 'theme_avalon');
    $setting = new admin_setting_configtextarea($name, $title , $description, '', PARAM_RAW);
    $settings->add($setting);

}