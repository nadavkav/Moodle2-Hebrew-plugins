<?php
 
require_once($CFG->dirroot . '/mod/game/backup/moodle2/backup_game_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/mod/game/backup/moodle2/backup_game_settingslib.php'); // Because it exists (optional)
 
/**
 * Game backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_game_activity_task extends backup_activity_task {
 
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
    }
 
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Game only has one structure step
        $this->add_step(new backup_game_activity_structure_step('game_structure', 'game.xml'));        
    }
 
    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;
 
        $base = preg_quote($CFG->wwwroot,"/");
 
        // Link to the list of gamess
        $search="/(".$base."\/mod\/game\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@GAMEINDEX*$2@$', $content);
 
        // Link to game view by moduleid
        $search="/(".$base."\/mod\/game\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@GAMEVIEWBYID*$2@$', $content);
 
        return $content;
    }}


