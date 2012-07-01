<?php
/**
* Copyright (C) 2009  Moodlerooms Inc.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
* 
* @copyright  Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
* @license    http://opensource.org/licenses/gpl-3.0.html     GNU Public License
* @author Chris Stones
*/
/**
 * auth_saml Settings
 *
 * @author Chris Stones
 *         based off Mark's code
 * @version $Id$
 * @package auth_saml
 **/

require_once $CFG->libdir.'/adminlib.php';

// Moodle changes the way files are handled. Rather than store files, we can
// store the BASE64 key strings in the database directly, similar to MNET.

$auth = get_auth_plugin('gsaml');
$configs = array();

$configs[] = new admin_setting_configtext('domainname', get_string('domainname','auth_gsaml'), "", '', PARAM_RAW, 30);

// RSA private key
$helpicon = $OUTPUT->help_icon('keys', 'auth_gsaml');
$configs[] = new admin_setting_configtextarea('privatekey', get_string('rsakey','auth_gsaml').' '.$helpicon, get_string('rsakey_desc','auth_gsaml'), null, PARAM_RAW, 80, 20);

// X.509 SSL signing certificate
$configs[] = new admin_setting_configtextarea('certificate', get_string('sslcertificate','auth_gsaml').' '.$helpicon, get_string('sslcertificate_desc','auth_gsaml'), null, PARAM_RAW, 80, 20);

// Provide a Link to Google Settings
$googsettings = get_string('lnktogoogsettings','auth_gsaml');
if (empty($auth->config->domainname)) {
    $auth->config->domainname = '';
    $googsettings = get_string('nodomainyet','auth_gsaml'); 
} 

// Instructions
$a = (object) array('domainname' => $auth->config->domainname, 'googsettings' => $googsettings, 'wwwroot' => $CFG->wwwroot);
$setupinfo = get_string('setupinfo', 'auth_gsaml', $a);
$helpicon = $OUTPUT->help_icon('config_gsaml', 'auth_gsaml');
$configs[] = new admin_setting_heading('info', get_string('setupinstructions','auth_gsaml').' '.$helpicon, $setupinfo);

// Moodle Gadget Info and Set Up
$helpicon = $OUTPUT->help_icon('mgadget' ,'auth_gsaml');
$gadgetinfo = get_string('gadgetinfo', 'auth_gsaml', (object) array('wwwroot' => $CFG->wwwroot));
$configs[] = new admin_setting_heading('moodlegadget', get_string('moodlegadget','auth_gsaml').' '.$helpicon, $gadgetinfo);

// Diagnostics Info and Options
$helpicon = $OUTPUT->help_icon('diagnostics', 'auth_gsaml');
$configs[]  = new admin_setting_heading('diagnostics', "Diagnostics ".$helpicon, get_string('debugoptions', 'auth_gsaml', $CFG->wwwroot));

foreach ($configs as $config) {
    $config->plugin = 'auth/gsaml';
    $settings->add($config);
}

