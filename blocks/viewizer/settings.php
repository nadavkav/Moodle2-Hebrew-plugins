<?php
// This file is part of Viewizer block for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin settings
 *
 * @package    block
 * @subpackage viewizer
 * @copyright  2012 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    
    $settings->add(new admin_setting_configtext('viewizerblocktitle', get_string('viewizertitle', 'block_viewizer'),
               get_string('viewizertitledescription', 'block_viewizer'), 'Viewizer', PARAM_TEXT));
    
    //limit_index_body
    $settings->add(new admin_setting_configtext('viewizercoursesperpage', get_string('viewizercoursesperpage', 'block_viewizer'),
                   get_string('viewizerconfigmymaxcourses', 'block_viewizer'), 10, PARAM_INT));
}

?>
