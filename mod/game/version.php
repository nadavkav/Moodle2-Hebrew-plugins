<?php // $Id: version.php,v 1.47 2011/08/27 05:37:44 bdaloukas Exp $
/**
 * Code fragment to define the version of game
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author 
 * @version $Id: version.php,v 1.47 2011/08/27 05:37:44 bdaloukas Exp $
 * @package game
 **/

$module->version  = 2011082604;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2010000000;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)
