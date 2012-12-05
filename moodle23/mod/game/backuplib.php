<?php //$Id: backuplib.php,v 1.7 2010/07/26 00:07:12 bdaloukas Exp $
    //This php script contains all the stuff to backup games

//This version uses only the table game
//This is the "graphical" structure of the game mod:
    //To see, put your terminal to 160cc

    //
    //                           game
    //                        (CL,pk->id)
    //                            |
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          SL->site level info
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files
    //
    //-----------------------------------------------------------

    // When we backup a game we also need to backup the questions and possibly
    // the data about student interaction with the questions. The functions to do
    // that are included with the following library

    /*
     * Insert necessary category ids to backup_ids table. Called during backup_check.html
     */
    function game_insert_category_and_question_ids($course, $backup_unique_code, $instances = null) {
        global $DB;

        // Insert categories used by games
        $sql = "SELECT DISTINCT g.questioncategoryid as id
                               FROM {game} g
                               WHERE g.course=$course";
        if( ($recs = $DB->get_records_sql( $sql)) == false){
            return true;
        }
        $status = true;
        $table = 'question_categories';
        foreach( $recs as $rec){
            if( $rec->id == 0){
                continue;
            }
            
            $select = "backup_code='$backup_unique_code' AND table_name='$table' AND old_id = '$rec->id'";
            if( ($found = $DB->get_record_select( 'backup_ids', $select)) != false){
                continue;
            }
                        
            unset( $newrec);
            $newrec->backup_code = $backup_unique_code;
            $newrec->table_name = $table;
            $newrec->old_id = $rec->id;
            $newrec->info = '';
			if (!$DB->insert_record( 'backup_ids', $newrec)){
			    print_object( $newrec);
				print_error("game_insert_category_and_question_ids: Can't insert to backup_ids");
			}
            
        }
        
        return $status;
    }
    
    function game_insert_glossaries_ids($course, $backup_unique_code, $instances = null) {
        global $DB;

        // Insert categories used by games
        $status = $DB->execute("INSERT INTO {backup_ids}
                                   (backup_code, table_name, old_id, info)
                               SELECT DISTINCT $backup_unique_code, 'glossary', g.glossaryid, ''
                               FROM {game} g
                               WHERE g.course=$course", false);

        return $status;
    }

//STEP 2. Backup games and associated structures
    //    (course dependent)

    function game_backup_one_mod($bf,$preferences,$game) {
        global $DB;
    
        $status = true;

        if (is_numeric($game)) {
            $game = $DB->get_record('game','id',$game);
        }
		
        //Start mod
        fwrite ($bf,start_tag( 'MOD', 3, true));
		
        //Print game data
        $game->modtype = 'game';
			
		game_backup_record( $bf, $game, 4);

        $recs = $DB->get_records( 'game_snakes_database');
		game_backup_table( $bf, $recs, 'GAME_SNAKES_DATABASE', 5, 'GAME_SNAKES_DATABASE_RECORD', 6);

        $recs = $DB->get_records( 'game_bookquiz_questions', array( 'gameid' => $game->id));
		game_backup_table( $bf, $recs, 'GAME_BOOKQUIZ_QUESTIONS', 5, 'GAME_BOOKQUIZ_QUESTION', 6);

        $recs = $Db->get_records( 'game_grades', array( 'gameid' => $game->id));
		game_backup_table( $bf, $recs, 'GAME_GRADES', 5, 'GAME_GRADE', 6);
		
		$sql = "SELECT DISTINCT g.questioncategoryid as id,qc.stamp FROM ".
		        " {game} g,{question_categories} qc ".
		        " WHERE g.questioncategoryid = qc.id";
		$recs = $DB->get_records_sql( $sql);
		game_backup_table( $bf, $recs, 'QUESTION_CATEGORIES',5, 'QUESTION_CATEGORY', 6);
		
		game_backup_attempts( $bf,$preferences,$game->id);
		
        //End mod
        $status = fwrite ($bf, end_tag( 'MOD', 3, true));
        
        return $status;
    }

	function game_backup_table( $bf,$recs, $tags, $levels, $tag, $level)
	{
        $status = true;

        //If there are records
        if ($recs != false) {
            //Write start tag
            $status = fwrite ($bf,start_tag( $tags, $levels, true));
            //Iterate over each attempt
            foreach ($recs as $rec) {
                //Start game_snakes_database
                $status = fwrite ($bf, start_tag( $tag, $level, true));
                    game_backup_record( $bf, $rec, $level);
                $status = fwrite ($bf, end_tag( $tag, $level, true));
            }
            
            $status = fwrite ($bf,end_tag( $tags, $levels, true));
        }
        
        return $status;	
    }

	function game_backup_record( $bf, $rec, $level){
        foreach( $rec as $field => $value){
            fwrite ($bf,full_tag( strtoupper( $field), $level, false, $rec->$field));
        }	
	}
	
	function game_backup_attempts( $bf,$preferences,$gameid)
	{
        global $DB;

        $status = true;

        $attempts = $DB->get_records( 'game_attempts', array( 'gameid' => $gameid), 'id');
        //If there are attempts
        if ($attempts) {
            //Write start tag
            $status = fwrite ($bf,start_tag( 'GAME_ATTEMPTS', 5, true));
            //Iterate over each attempt
            foreach ($attempts as $attempt) {
                //Start game_attempt
                $status = fwrite ($bf, start_tag( 'GAME_ATTEMPT', 6, true));
                game_backup_record( $bf, $attempt, 7);
                
                ////////////game_queries
                $queries = $DB->get_records( 'game_queries', array( 'attemptid' => $attempt->id), 'id');
                if( $queries != false){
                    //Write start tag
                    $status = fwrite ($bf,start_tag("GAME_QUERIES",8,true));
                    foreach ($queries as $query) {
                        //Start game_query
                        $status = fwrite ($bf,start_tag("GAME_QUERY",9,true));
                            game_backup_record( $bf, $query, 10);
                        $status = fwrite ($bf,end_tag("GAME_QUERY",9,true));
                   }
                   $status = fwrite ($bf,end_tag("GAME_QUERIES",8,true));                
                }
                
                $names = array( 'game_hangman', 'game_cross', 'game_cryptex', 'game_millionaire', 'game_sudoku', 'game_snakes', 'game_hiddenpicture');
                game_backup_game( $bf, $attempt->id, $names);
                
                //End question instance
                $status = fwrite ($bf,end_tag("GAME_ATTEMPT",6,true));
            }
            //Write end tag
            $status = fwrite ($bf,end_tag("GAME_ATTEMPTS",5,true));
        }
        return $status;	
    }


    function game_backup_game( $bf, $attemptid, $names)
    {
        global $DB;

        foreach( $names as $name){
            if( ($rec = $DB->get_record( $name, array( 'id' => $attemptid))) == false){
                continue;
            }
            
            $uppername = strtoupper( $name);
            $status = fwrite ($bf,start_tag( $uppername,7,true));
                game_backup_record( $bf, $rec, 8);
            $status = fwrite ($bf,end_tag( $uppername,7,true));
        }
    }

    function game_backup_mods($bf,$preferences) {

        global $DB;

        $status = true;

        //Iterate over game table
        $games = $DB->get_records( 'game', array( 'course' => $preferences->backup_course), 'id');
        if ($games) {
            foreach ($games as $game) {
                if (backup_mod_selected($preferences,'game',$game->id)) {
                    $status = game_backup_one_mod( $bf,$preferences,$game);
                }
            }
        }
        return $status;
    }


  ////Return an array of info (name,value)
/// $instances is an array with key = instanceid, value = object (name,id,userdata)
   function game_check_backup_mods($course,$user_data= false,$backup_unique_code,$instances=null) {
        //this function selects all the questions / categories to be backed up.
        game_insert_category_and_question_ids($course, $backup_unique_code, $instances);
        game_insert_glossaries_ids($course, $backup_unique_code, $instances);
   
        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
               $info += game_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
		
        //First the course data
        $info[0][0] = get_string("modulenameplural","game");
        if ($ids = game_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }
		
        return $info;
    }


    function game_check_backup_mods_instances($instance,$backup_unique_code) {
        // the keys in this array need to be unique as they get merged...
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';
                

        return $info;
    }
	
	
// INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of game id
    function game_ids ($course) {

        global $DB;

        return $DB->get_records ( 'game, array( 'course' => $course), 'id, id);
    }
