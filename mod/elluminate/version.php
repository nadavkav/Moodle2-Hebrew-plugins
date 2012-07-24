<?php // $Id: version.php,v 1.1.2.2 2009/03/18 16:45:55 mchurch Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of Blackboard Collaborate
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->requires = 2010080300;	// Requires this Moodle version
$module->version  = 2012050211;  // The current module version (Date: YYYYMMVERSION#)
$module->cron     = 600;         // Period for cron to check this module (secs)

