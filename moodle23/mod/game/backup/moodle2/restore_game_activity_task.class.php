<?php  // $Id: restore_game_activity_task.class.php,v 1.2 2011/07/26 23:12:55 bdaloukas Exp $
/**
 * @author  bdaloukas
 * @version $Id: restore_game_activity_task.class.php,v 1.2 2011/07/26 23:12:55 bdaloukas Exp $
 * @package game
 **/    

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/game/backup/moodle2/restore_game_stepslib.php'); // Because it exists (must)

/**
 * Game restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_game_activity_task extends restore_activity_task {
 
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }
 
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Game only has one structure step
        $this->add_step(new restore_game_activity_structure_step('game_structure', 'game.xml'));
    }
 
    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();
 
//        $contents[] = new restore_decode_content('game', array('intro'), 'game');
 
        return $contents;
    }
 
    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();
 
        $rules[] = new restore_decode_rule('GAMEVIEWBYID', '/mod/game/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('GAMEINDEX', '/mod/game/index.php?id=$1', 'course');
 
        return $rules;
 
    }
 
    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * Game logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();
 
        $rules[] = new restore_log_rule('game', 'add', 'view.php?id={course_module}', '{game}');
        $rules[] = new restore_log_rule('game', 'update', 'view.php?id={course_module}', '{game}');
        $rules[] = new restore_log_rule('game', 'view', 'view.php?id={course_module}', '{game}');
        $rules[] = new restore_log_rule('game', 'choose', 'view.php?id={course_module}', '{game}');
        $rules[] = new restore_log_rule('game', 'choose again', 'view.php?id={course_module}', '{game}');
        $rules[] = new restore_log_rule('game', 'report', 'report.php?id={course_module}', '{game}');
 
        return $rules;
    }
 
    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();
 
        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('game', 'view all', 'index?id={course}', null,
                                        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('game', 'view all', 'index.php?id={course}', null);
 
        return $rules;
    }
 
    public function after_restore() {
        // Do something at end of restore
        global $DB;

        // Get the blockid
        $gameid = $this->get_activityid();
        
        // Extract Game configdata and update it to point to the new glossary
        $rec = $DB->get_record_select( 'game', 'id='.$gameid, null,
                'id,quizid,glossaryid,glossarycategoryid,questioncategoryid,bookid,glossaryid2,glossarycategoryid2');

        $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'quiz', $rec->quizid);
        if( $ret != false)
            $rec->quizid = $ret->newitemid;
        
        $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'glossary', $rec->glossaryid);
        if( $ret != false)        
            $rec->glossaryid = $ret->newitemid;
        
        $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'glossary_categories', $rec->glossarycategoryid);
        if( $ret != false)
            $rec->glossarycategoryid = $ret->newitemid;
        
        $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'question_categories', $rec->questioncategoryid);
        if( $ret != false)        
            $rec->questioncategoryid = $ret->newitemid;
        
        $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'book', $rec->bookid);
        if( $ret != false)
            $rec->bookid = $ret->newitemid;
        
        $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'glossary', $rec->glossaryid2);
        if( $ret != false)        
            $rec->glossaryid2 = $ret->newitemid;
        
        $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'glossary_categories', $rec->glossarycategoryid);
        if( $ret != false)        
            $rec->glossarycategoryid = $ret->newitemid;
        
        $DB->update_record( 'game', $rec);

        //game_repetitions
        $recs = $DB->get_records_select( 'game_repetitions', 'gameid='.$gameid, null, '',
                'id,questionid,glossaryentryid');
        if( $recs != false){
            foreach( $recs as $rec){
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'question', $rec->questionid);
                if( $ret != false)
                    $rec->questionid = $ret->newitemid;
            
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'glossary_entry', $rec->glossaryentryid);
                if( $ret != false)
                    $rec->glossaryentryid = $ret->newitemid;
                
                $DB->update_record( 'game_repetitions', $rec);
           }
        }
        
        //game_queries
        $recs = $DB->get_records_select( 'game_queries', 'gameid='.$gameid, null, '',
                'id,questionid,glossaryentryid,answerid');
        if( $recs != false){
            foreach( $recs as $rec){
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'question', $rec->questionid);
                if( $ret != false)
                    $rec->questionid = $ret->newitemid;
            
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'glossary_entry', $rec->glossaryentryid);
                if( $ret != false)                
                    $rec->glossaryentryid = $ret->newitemid;
                
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'question_answers', $rec->glossaryentryid);
                if( $ret != false)                
                    $rec->answerid = $ret->newitemid;
                
                $DB->update_record( 'game_queries', $rec);                           
           }
        }

        //bookquiz
        $recs = $DB->get_records_select( 'game_bookquiz', 'id='.$gameid, null, '', 'id,lastchapterid');
        if( $recs != false){
            foreach( $recs as $rec){
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'book_chapters', $rec->lastchapterid);
                if( $ret != false)                
                    $rec->lastchapterid = $ret->newitemid;
                
                $DB->update_record( 'game_bookquiz', $rec);                                           
           }
        }
               
        //bookquiz_chapters
        $sql = "SELECT gbc.* FROM {game_bookquiz_chapters} gbc LEFT JOIN {game_attempts} a ON gbc.attemptid = a.id WHERE a.gameid=$gameid";
        $recs = $DB->get_records_sql( $sql);
        if( $recs != false){
            foreach( $recs as $rec){
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'book_chapters', $rec->chapterid);
                if( $ret != false)                                
                    $rec->chapterid = $ret->newitemid;
                
                $DB->update_record( 'game_bookquiz_chapter', $rec);                                                           
           }
        }        

        //bookquiz_questions
        $recs = $DB->get_records_select( 'game_bookquiz_questions', 'id='.$gameid, null, '', 'id,chapterid,questioncategoryid');
        if( $recs != false){
            foreach( $recs as $rec){
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'book_chapters', $rec->chapterid);
                if( $ret != false)                
                    $rec->chapterid = $ret->newitemid;
                
                $ret = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'book_chapters', $rec->questioncategoryid);
                if( $ret != false)                
                    $rec->questioncategoryid = $ret->newitemid;
                
                $DB->update_record( 'game_bookquiz_questions', $rec);                                                                           
           }
        }
        
    }
}
