<?php

/**
 * Makes our changes to the CSS
 *
 * @param string $css
 * @param theme_config $theme
 * @return string 
 */
function avalon_process_css($css, $theme) {

    ////////// Blocks background colors

    // Set the communication blocks background color
    if (!empty($theme->settings->blockbgcolor_communication)) {
        $bgcolor = $theme->settings->blockbgcolor_communication;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_communication($css, $bgcolor);

    // Set the navigation blocks background color
    if (!empty($theme->settings->blockbgcolor_navigation)) {
        $bgcolor = $theme->settings->blockbgcolor_navigation;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_navigation($css, $bgcolor);

    // Set the information blocks background color
    if (!empty($theme->settings->blockbgcolor_information)) {
        $bgcolor = $theme->settings->blockbgcolor_information;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_information($css, $bgcolor);

    // Set the personal blocks background color
    if (!empty($theme->settings->blockbgcolor_personal)) {
        $bgcolor = $theme->settings->blockbgcolor_personal;
    } else {
        $bgcolor = null;
    }
    $css = avalon_set_blockbgcolor_personal($css, $bgcolor);


    ////////// Color presets

    // colors
    for ($i=1;$i<10;$i++) {
        $colorsetting = 'color'.$i;
        if (!empty($theme->settings->$colorsetting)) {
            $color = $theme->settings->$colorsetting;
        } else {
            $color = null;
        }
        //$call_avalon_set_color = 'avalon_set_color'.$i;
        //$css = $call_avalon_set_color($css, $color ,$i);
        $css = avalon_set_color($css, $color ,$i);

    }


    ////////// Images (LOGO)

    // Set the logo image (ltr)
    if (!empty($theme->settings->logocollege)) {
        $logo = $theme->settings->logocollege;
    } else {
        $logo = null;
    }
    $css = avalon_set_logo($css, $logo);

    // Set the logo image (rtl)
    if (!empty($theme->settings->logocollegertl)) {
        $logo = $theme->settings->logocollegertl;
    } else {
        $logo = null;
    }
    $css = avalon_set_logortl($css, $logo);

    // Set the logo college footer image (ltr)
    if (!empty($theme->settings->logocollegefooter)) {
        $logocollegefooter = $theme->settings->logocollegefooter;
    } else {
        $logocollegefooter = null;
    }
    $css = avalon_set_logocollegefooter($css, $logocollegefooter);

    // Set the logo college footer image (rtl)
    if (!empty($theme->settings->logocollegefooterrtl)) {
        $logocollegefooter = $theme->settings->logocollegefooterrtl;
    } else {
        $logocollegefooter = null;
    }
    $css = avalon_set_logocollegefooterrtl($css, $logocollegefooter);

    // Set the background banner image (ltr)
    if (!empty($theme->settings->topbanner)) {
        $banner = $theme->settings->topbanner;
    } else {
        $banner = null;
    }
    $css = avalon_set_topbanner($css, $banner);

    // Set the background banner image (rtl)
    if (!empty($theme->settings->topbannerrtl)) {
        $banner = $theme->settings->topbannerrtl;
    } else {
        $banner = null;
    }
    $css = avalon_set_topbannerrtl($css, $banner);

    //set top banner spacer image url
    if (!empty($theme->settings->topbanner1pix)) {
        $spacer = $theme->settings->topbanner1pix;
    } else {
        $spacer = null;
    }
    $css = avalon_set_topbanner_1pix($css, $spacer);


    //set footer spacer image url
    if (!empty($theme->settings->footer1pix)) {
        $spacer = $theme->settings->footer1pix;
    } else {
        $spacer = null;
    }
    $css = avalon_set_footer_1pix($css, $spacer);


    // Return the CSS
    return $css;
}



/**
 * Sets the link color variable in CSS
 *
 */
function avalon_set_blockbgcolor_communication($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_communication]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_blockbgcolor_navigation($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_navigation]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_blockbgcolor_information($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_information]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_blockbgcolor_personal($css, $bgcolor) {
    $tag = '[[setting:blockbgcolor_personal]]';
    $replacement = $bgcolor;
    if (is_null($replacement)) {
        $replacement = '#32529a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}


////////// Color presets


function avalon_set_color($css, $color ,$i) {
    $colorpresets = array('#12789b','#caf1fe','#90cce0','#edfbff','#00ade6','#008cb8','#004a61','#51300d','#ead5b4');

    $tag = '[[setting:color'.$i.']]';
    $replacement = $color;
    if (is_null($replacement)) {
        $replacement = $colorpresets[$i-1];
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}


////////// Images (LOGO)

function avalon_set_logo($css, $logo) {
	global $OUTPUT;
	$tag = '[[setting:logocollege]]';
	$replacement = "#page-header .collegelogo { background:url($logo) no-repeat; } ";
	if (is_null($logo)) {
 		$replacement = ' ';//$OUTPUT->pix_url('logo_college_default', 'theme');
 	}
	$css = str_replace($tag, $replacement, $css);
	return $css;
}

function avalon_set_logortl($css, $logo) {
    global $OUTPUT;
    $tag = '[[setting:logocollegertl]]';
    $replacement = ".dir-rtl #page-header .collegelogo { background:url($logo) no-repeat; } ";
    if (is_null($logo)) {
        $replacement = ' ';//$OUTPUT->pix_url('logo_college_default_rtl', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_logocollegefooter($css, $logocollegefooter) {
    global $OUTPUT;
    $tag = '[[setting:logocollegefooter]]';
    $replacement = "#page-footer .bottomfar a.college { background:url($logocollegefooter) no-repeat; } ";
    if (is_null($logocollegefooter)) {
        $replacement = ' ';//$OUTPUT->pix_url('logo_college_footer_default', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_logocollegefooterrtl($css, $logocollegefooter) {
    global $OUTPUT;
    $tag = '[[setting:logocollegefooterrtl]]';
    $replacement = ".dir-rtl #page-footer .bottomfar a.college { background:url($logocollegefooter) no-repeat; } ";
    if (is_null($logocollegefooter)) {
        $replacement = ' ';//$OUTPUT->pix_url('logo_college_footer_default_rtl', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbanner($css, $banner) {
    global $OUTPUT;
    $tag = '[[setting:topbanner]]';
    $replacement = "#page-header { background:url($banner) no-repeat 0 0; } ";
    if (is_null($banner)) {
        $replacement = ' ';//$OUTPUT->pix_url('top_banner_default', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbannerrtl($css, $banner) {
    global $OUTPUT;
    $tag = '[[setting:topbannerrtl]]';
    $replacement = ".dir-rtl #page-header { background:url($banner) no-repeat 100% 0; } ";
    if (is_null($banner)) {
        $replacement = ' ';//$OUTPUT->pix_url('top_banner_default_rtl', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_topbanner_1pix($css, $spacer) {
    global $OUTPUT;
    $tag = '[[setting:topbanner1pix]]';
    $replacement = "#page { background:url($spacer) repeat-x; }";
    if (is_null($spacer)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function avalon_set_footer_1pix($css, $spacer) {
    global $OUTPUT;
    $tag = '[[setting:footer1pix]]';
    $replacement = "#page-footer { background:url($spacer) repeat-x; }";
    if (is_null($spacer)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}