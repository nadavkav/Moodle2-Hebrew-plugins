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
/*
 * @package    block
 * @subpackage forum_aggregator
 * @author     Tõnis Tartes <t6nis20@gmail.com>
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_forum_aggregator extends block_base {
    
    public function init() {
        $this->title = get_string('blocktitle', 'block_forum_aggregator');
    }
    
    public function specialization() {

        global $CFG, $USER, $COURSE;

        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }

    }
    
    public function instance_allow_multiple() {
        return true;
    }
    
    public function instance_allow_config() {
        return true;
    }
    
    public function get_content() {
        
        global $DB, $CFG, $USER, $COURSE;

        //Include needed libraries
        require_once($CFG->dirroot.'/mod/forum/lib.php'); 
        
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        
        $text = '';

        $modinfo = get_fast_modinfo($COURSE); //course mod data

        if (!empty($this->config->forum_id)) {

            foreach ($this->config->forum_id as $key => $value) {
                    
                    //if forum not been made available
                    if ($value == 0) {
                        continue;
                    }
                    
                    if (empty($modinfo->instances['forum'][$key])) {
                        //maybe someone deleted a forum? then skip that value..
                        continue;
                    }
                    //if post in array get the maxpost value
                    if (array_key_exists($value, $this->config->max_posts)) {
                        $max_posts = $this->config->max_posts[$key];
                    }

                    $cm = $modinfo->instances['forum'][$key];

                    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

                    $strftimerecent = get_string('strftimerecent');
                    $strmore = get_string('more', 'forum');
                    
                    //if visible
                    if ($cm->visible == 1) {
                        
                        //show list
                        $text .= "\n<ul class='unlist'>\n";
                        $text .= '<li class="forum_title">'.$cm->name.'</li>';
                        if ( $discussions = forum_get_discussions($cm, 'p.modified DESC', false, -1, $max_posts ) ) {
             
                            foreach ($discussions as $discussion) {

                                $discussion->subject = $discussion->name;

                                $discussion->subject = format_string($discussion->subject, true, $COURSE->id);

                                $text .= '<li class="post">'.
                                         '<div class="head">'.
                                         '<div class="date">'.userdate($discussion->modified, $strftimerecent).'</div>'.
                                         '<div class="name">'.fullname($discussion).'</div></div>'.
                                         '<div>'.$discussion->subject.' '.
                                         '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion.'">'.
                                         $strmore.'...</a></div>'.
                                         "</li>\n";

                            }
                        } else {
                            $text .= '<li class="no_posts">('.get_string('noposts', 'block_forum_aggregator').')</li>';
                        }
                        $text .= "</ul>\n";
                    }
            }
            
        }
        
        $this->content->text = $text;
        
        return $this->content;
    }
    
    public function instance_config_save($data, $nolongerused = false) {

        global $CFG;

        // Default behavior: save all variables as $CFG properties
        if(get_config('forum_aggregator', 'Allow_HTML') == '1') {
            $data->title = strip_tags($data->title);
        }

        return parent::instance_config_save($data, $nolongerused);
    }
}

?>
