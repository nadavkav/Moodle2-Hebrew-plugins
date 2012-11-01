<?php
/**
 * Collapsed Topics Information
 *
 * A topic based format that solves the issue of the 'Scroll of Death' when a course has many topics. All topics
 * except zero have a toggle that displays that topic. One or more topics can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer by a small
 * code change. Full installation instructions, code adaptions and credits are included in the 'Readme.txt' file.
 *
 * @package    course/format
 * @subpackage moonstone
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2009-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @link       http://docs.moodle.org/en/Collapsed_Topics_course_format
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Used by the Moodle Core for identifing the format and displaying in the list of formats for a course in its settings.
// Possibly legacy to be removed after Moodle 2.0 is stable.
$string['namemoonstone']='תצורת מכללה';
$string['formatmoonstone']='תצורת מכללה';

// Used in format.php
$string['moonstonetoggle']='הקליקו';
$string['moonstonetogglewidth']='width: 28px;';

// Toggle all - Moodle Tracker CONTRIB-3190
$string['moonstoneall']='כל היחידות';
$string['moonstoneopened']='תצוגה';
$string['moonstoneclosed']='הסתרה';

// Moodle 2.0 Enhancement - Moodle Tracker MDL-15252, MDL-21693 & MDL-22056 - http://docs.moodle.org/en/Development:Languages
$string['sectionname'] = 'יחידת הוראה';
$string['pluginname'] = 'תצורת מכללה';
$string['section0name'] = 'מבוא';

// MDL-26105
$string['page-course-view-moonstone'] = 'Any course main page in the collapsed topics format';
$string['page-course-view-moonstone-x'] = 'Any course page in the collapsed topics format';

// Moodle 2.3 Enhancement
$string['hidefromothers'] = 'הסתרת יחידת הוראה';
$string['showfromothers'] = 'תצוגת יחידת הוראה';

// Layout enhancement - Moodle Tracker CONTRIB-3378
$string['formatsettings'] = 'הגדרות תצוגת יחידות־הוראה'; // CONTRIB-3529
$string['setlayout'] = 'תצורה';
$string['setlayout_default'] = 'בררת מחדל';
$string['setlayout_no_toggle_section_x'] = 'ללא בורר יחידת הוראה X';
$string['setlayout_no_section_no'] = 'ללא מספר יחידת ההוראה';
$string['setlayout_no_toggle_section_x_section_no'] = 'ללא כפתור הקליקו לצפיה/הסתרה וללא מספר יחידת הוראה';
$string['setlayout_no_toggle_word'] = 'ללא מילת הקליקו להסתרה/צפיה';
$string['setlayout_no_toggle_word_toggle_section_x'] = 'ללא מילת הקליקו להסתרה/צפיה וללא שם יחידת הוראה';
$string['setlayout_no_toggle_word_toggle_section_x_section_no'] = 'ללא מילת הקליקו להסתרה/צפיה וללא שם או מספר יחידת הוראה';
$string['setlayoutelements'] = 'הגדרות רכיבים';
$string['setlayoutstructure'] = 'הגדרת מבנה עמוד הקורס';
$string['setlayoutstructuretopic']='נושאים';
$string['setlayoutstructureweek']='שבועות - תאריכים';
$string['setlayoutstructurelatweekfirst']='שבוע אחרון, בראש העמוד';
$string['setlayoutstructurecurrenttopicfirst']='שבוע נוכחי, בראש הרשימה';
$string['setlayoutstructureday']='יום';
$string['resetlayout'] = 'אתחול תצוגה'; //CONTRIB-3529
$string['resetalllayout'] = 'אתחול תצוגת כל הקורסים במערכת';

// Colour enhancement - Moodle Tracker CONTRIB-3529
$string['setcolour'] = 'בחירה צבע';
$string['colourrule'] = "Please enter a valid RGB colour, a '#' and then six hexadecimal digits.";
$string['settoggleforegroundcolour'] = 'צבע גופן';
$string['settogglebackgroundcolour'] = 'צבע רקע';
$string['settogglebackgroundhovercolour'] = 'צבע רקע בזמן מעבר סמן העכבר';
$string['resetcolour'] = 'איפוס (אתחול) צבעים בקורס זה';
$string['resetallcolour'] = 'איפוס (אתחול) צבעים בכל הקורסים';

// Columns enhancement
$string['setlayoutcolumns'] = 'מספר עמודות';
$string['one'] = 'אחת';
$string['two'] = 'שתיים';
$string['three'] = 'שלוש';
$string['four'] = 'ארבע';

// Help
$string['setlayoutelements_help']='How much information about the toggles / sections you wish to be displayed.';
$string['setlayoutstructure_help']="The layout structure of the course.  You can choose between:

'Topics' - where each section is presented as a topic in section number order.

'Weeks' - where each section is presented as a week in ascending week order from the start date of the course.

'Latest Week First' - which is the same as weeks but the current week is shown at the top and preceding weeks in decending order are displayed below execpt in editing mode where the structure is the same as 'Weeks'.

'Current Topic First' - which is the same as 'Topics' except that the current topic is shown at the top if it has been set.

'Day' - where each section is presented as a day in ascending day order from the start date of the course.";
$string['setlayout_help'] = 'Contains the settings to do with the layout of the format within the course.';
$string['resetlayout_help'] = 'Resets the layout to the default values in "/course/format/moonstone/config.php" so it will be the same as a course the first time it is in the Collapsed Topics format.';
$string['resetalllayout_help'] = 'Resets the layout to the default values in "/course/format/moonstone/config.php" for all courses so it will be the same as a course the first time it is in the Collapsed Topics format.';
// Moodle Tracker CONTRIB-3529
$string['setcolour_help'] = 'Contains the settings to do with the colour of the format within the course.';
$string['settoggleforegroundcolour_help'] = 'Sets the colour of the text on the toggle.';
$string['settogglebackgroundcolour_help'] = 'Sets the background of the toggle.';
$string['settogglebackgroundhovercolour_help'] = 'Sets the background of the toggle when the mouse moves over it.';
$string['resetcolour_help'] = 'Resets the colours to the default values in "/course/format/moonstone/config.php" so it will be the same as a course the first time it is in the Collapsed Topics format.';
$string['resetallcolour_help'] = 'Resets the colours to the default values in "/course/format/moonstone/config.php" for all courses so it will be the same as a course the first time it is in the Collapsed Topics format.';
// Columns enhancement
$string['setlayoutcolumns_help'] = 'How many columns to use.';

$string['pleaseaddsummary'] = 'אנא הזינו תאור קצר או הנחיה לתלמידים עבור יחידת הוראה זו...';
