<?php
// This file is part of Moodle - http://moodle.org/
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
 * Defines lang strings for Quick Find List block
 *
 * @package    block_quickfindlist
 * @copyright  2010 Onwards Taunton's College, UK
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allusers']         = 'Все пользователи'; // All Users
$string['pluginname']         = 'Quickfind List'; // Quickfind List, proper name not to be translated (rus. Быстрый поиск)
$string['blockname']         = 'Быстрый поиск'; // Quickfind List, proper name can be without translation
$string['list']         =  ' Список'; // List
$string['loading']  = 'Загрузка...'; // Loading...
$string['lotsofusers']         = ' ПРЕДУПРЕЖДЕНИЕ: слишком много пользователей ({$a}) возможно замедление работы'; // WARNING: lots of users ({$a}) could get slow

// You cannot have two blocks on the same page without a role configured; Edit this block and select a role.
$string['multiplenorole']         = 'Нельзя использовать два одинаковых блока; Отредактируйте блоки и назначте им роли.';
$string['nousers']         =  'ОШИБКА: Нет пользователей с такого типа (роли)'; // ERROR: No users have that role'
$string['quickfindlist']         = 'Quickfind List'; // Quickfind
$string['quickfindlist:use']         = 'Использовать Quickfind List'; // Use Quickfind List
$string['role']         = 'Для перечисленных пользователей'; // Role for listed people to have

// Page to link to (the person\'s id will be appended to the end).<br />Leave blank for default profile
$string['url']         = 'Ссылка на страницу (id пользователя подставится в конце).<br />Оставьте пустым для профиля по умолчанию';
// User data to display/search,<br />You can use the following placeholders:<ul><li>[[firstname]]</li><li>[[lastname]]</li><li>[[username]]</li></ul>
$string['userfields']         = 'Шаблон информации, отобажаемой о пользователе,<br />Допустимы следующие конструкции:<ul><li>[[firstname]]</li><li>[[lastname]]</li><li>[[username]]</li></ul>';
$string['userfieldsdefault']         = '[[firstname]] [[lastname]]';
