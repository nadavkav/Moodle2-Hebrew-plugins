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
 * AJAX handler
 *
 * @package    block
 * @subpackage viewizer
 * @copyright  2012 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/blocks/viewizer/lib.php');

defined('MOODLE_INTERNAL') || die();

$mod = required_param('mod', PARAM_TEXT);
$action = required_param('action', PARAM_TEXT);
$id = required_param('id', PARAM_INT);

if ($mod == 'viewizer') {
    viewizer_important($id, $action);
} else {
    die();
}

?>
