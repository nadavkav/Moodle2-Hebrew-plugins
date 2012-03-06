<?php  // $Id: locallib.php,v 1.33 2010/08/03 20:48:51 bdaloukas Exp $

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page.
}

/**
 * Include those library functions that are also used by core Moodle or other modules
 */
require_once($CFG->dirroot . '/mod/game/lib.php');
/// CONSTANTS ///////////////////////////////////////////////////////////////////

/**#@+
* Options determining how the grades from individual attempts are combined to give
* the overall grade for a user
*/

define( "GAME_GRADEMETHOD_HIGHEST", "1");
define( "GAME_GRADEMETHOD_AVERAGE", "2");
define( "GAME_GRADEMETHOD_FIRST",   "3");
define( "GAME_GRADEMETHOD_LAST",    "4");

$GAME_GRADE_METHOD = array ( GAME_GRADEMETHOD_HIGHEST => get_string("gradehighest", "game"),
                             GAME_GRADEMETHOD_AVERAGE => get_string("gradeaverage", "game"),
                             GAME_GRADEMETHOD_FIRST => get_string("attemptfirst", "game"),
                             GAME_GRADEMETHOD_LAST  => get_string("attemptlast", "game"));                                  

define( "CONST_GAME_TRIES_REPETITION", "3");

/**#@-*/


function game_upper( $str, $lang='')
{
	$textlib = textlib_get_instance();
    $str = $textlib->strtoupper( $str);

    $strings = get_string_manager()->load_component_strings( 'game', $lang);
    if( !isset( $strings[ 'convertfrom']))
        return $str;
    if( !isset( $strings[ 'convertto']))
        return $str;
	
    $from = $strings[ 'convertfrom'];
    $to = $strings[ 'convertto'];
    $len = $textlib->strlen( $from);
    for($i=0; $i < $len; $i++){
        $str = str_replace( $textlib->substr( $from, $i, 1), $textlib->substr( $to, $i, 1), $str);
    }
	
    return $str;
}


function game_showselectcontrol( $name, $a,  $input, $events=''){
	$ret = "<select id=\"$name\" name=\"$name\" $events>";
	foreach( $a as $key => $caption){
		$ret .= '<option value="'.$key.'" ';
		if( $key == $input){
			$ret .= ' selected="selected" ';
		}
		$ret .= '>'.$caption."</option>\r\n";
	}
	$ret .= "</select>\r\n";
	
	return $ret;
}

function game_showcheckbox( $name, $value)
{
	$a = array();
	$a[ 0] = get_string( 'no');
	$a[ 1] = get_string( 'yes');
	
	return game_showselectcontrol( $name, $a, $value);
	
	$ret = '<input type="checkbox" name="'.$name.'"  value="'.$value.'"';
	if( $value == 1)
		$ret .= 'checked="checked"';
	$ret .= '/>';

	return $ret;
}

//used by hangman
function game_question_shortanswer( $game, $allowspaces=false, $use_repetitions=true)
{
	switch( $game->sourcemodule)
	{
	case 'glossary':
		return game_question_shortanswer_glossary( $game, $allowspaces, $use_repetitions);
	case 'quiz':
		return game_question_shortanswer_quiz( $game, $allowspaces, $use_repetitions);
	case 'question':
		return game_question_shortanswer_question( $game, $allowspaces, $use_repetitions);
	}

	return false;
}

//used by hangman
function game_question_shortanswer_glossary( $game, $allowspaces, $use_repetitions)
{
    global $DB;

	if( $game->glossaryid == 0){
		error( get_string( 'must_select_glossary', 'game'));
	}

    $select = "glossaryid={$game->glossaryid}";
	$table = '{glossary_entries} ge';
	if( $game->glossarycategoryid){
		$table .= ',{glossary_entries_categories} gec';
		$select .= ' AND gec.entryid = ge.id '.
					    " AND gec.categoryid = {$game->glossarycategoryid}";
	}
	if( $allowspaces == false){
    	$select .= " AND concept NOT LIKE '% %'  ";
    }

    if( ($id = game_question_selectrandom( $game, $table, $select, 'ge.id', $use_repetitions)) == false)
        return false;
              
    $sql = 'SELECT id, concept as answertext, definition as questiontext, id as glossaryentryid, 0 as questionid, glossaryid, attachment, 0 as answerid'.
           " FROM {glossary_entries} ge WHERE id = $id";
    if( ($rec = $DB->get_record_sql( $sql)) == false)
        return false;
        
    if( $rec->attachment != ''){
        $rec->attachment = "glossary/{$game->glossaryid}/$rec->id/$rec->attachment";
    }
    
    return $rec;
}

//used by hangman
function game_question_shortanswer_quiz( $game, $allowspaces, $use_repetitions)
{
    global $DB;

	if( $game->quizid == 0){
		print_error( get_string( 'must_select_quiz', 'game'));
	}

	$select = "qtype='shortanswer' AND quiz='$game->quizid' ".
					" AND qqi.question=q.id";
	$table = "{question} q,{quiz_question_instances} qqi";
	$fields = "q.id";
	
    if( ($id = game_question_selectrandom( $game, $table, $select, $fields, $use_repetitions)) == false)
        return false;	

	$select = "q.id=$id AND qa.question=$id".
					" AND q.hidden=0 AND qtype='shortanswer'";
	$table = "{question} q,{question_answers} qa";
	$fields = "qa.id as answerid, q.id, q.questiontext as questiontext, ".
	          "qa.answer as answertext, q.id as questionid, ".
	          "0 as glossaryentryid, '' as attachment";
    
    //Maybe there are more answers to one question. I use as correct the one with bigger fraction   
    $sql = "SELECT $fields FROM $table WHERE $select ORDER BY fraction DESC";
	if( ($recs=$DB->get_records_sql( $sql, null, 0, 1)) == false){
	    return false;
	}
	foreach( $recs as $rec){
	    return $rec;
	}
}

//used by hangman
function game_question_shortanswer_question( $game, $allowspaces, $use_repetitions)
{
    global $DB;
	
	if( $game->questioncategoryid == 0){
		print_error( get_string( 'must_select_questioncategory', 'game'));
	}
        		
    $select = 'category='.$game->questioncategoryid;        
    if( $game->subcategories){
        $cats = question_categorylist( $game->questioncategoryid);
        if( strpos( $cats, ',') > 0){
            $select = 'category in ('.$cats.')';
        }
    }
	$select .= " AND qtype='shortanswer'";
	
	$table = '{question} q';
	$fields = 'q.id';

    if( ($id = game_question_selectrandom( $game, $table, $select, $fields, $use_repetitions)) == false)
        return false;	

	$select = "q.id=$id AND qa.question=$id".
					" AND q.hidden=0 AND qtype='shortanswer'";
	$table = "{question} q,{question_answers} qa";
	$fields = "qa.id as answerid, q.id, q.questiontext as questiontext, ".
	          "qa.answer as answertext, q.id as questionid, ".
	          "0 as glossaryentryid, '' as attachment";
    
    //Maybe there are more answers to one question. I use as correct the one with bigger fraction
    $sql = "SELECT $fields FROM $table WHERE $select ORDER BY fraction DESC"; 
	if( ($recs = $DB->get_records_sql( $sql, null, 0, 1)) == false){
	    return false;
	}
	foreach( $recs as $rec){
	    return $rec;
	}
}

//used by millionaire, game_question_shortanswer_quiz, hidden picture
function game_question_selectrandom( $game, $table, $select, $id_fields='id', $use_repetitions=true)
{
    global $DB, $USER; 

    $count = $DB->get_field_sql( "SELECT COUNT(*) FROM $table WHERE $select");
    $min_num = 0;
    $min_id = 0;
    for($i=1; $i <= CONST_GAME_TRIES_REPETITION; $i++){
        $sel = mt_rand(0, $count-1);
	    
        $sql  = "SELECT $id_fields,$id_fields FROM ".$table." WHERE $select";
    	if( ($recs = $DB->get_records_sql( $sql, null, $sel, 1)) == false){
            return false;
        }

        $id = 0;
        foreach( $recs as $rec){
            $id = $rec->id;
        }
        if( $min_id == 0){
            $min_id = $id;
        }
        
        if( $use_repetitions == false){
            return $id;
        }
        
        if( $count == 1){
            break;
        }
                
        $questionid = $glossaryentryid = 0;
        if( $game->sourcemodule == 'glossary')
            $glossaryentryid = $id;
        else
            $questionid = $id;
        
        $a = array( 'gameid' => $game->id, 'userid' => $USER->id, 'questionid' => $questionid, 'glossaryentryid' => $glossaryentryid);
        if( ($rec = $DB->get_record( 'game_repetitions', $a, 'id,repetitions r')) != false){
            if( ($rec->r < $min_num) or ($min_num == 0)){
                $min_num = $rec->r;
                $min_id = $id;
            }
        }else
        {
            $min_id = $questionid;
            break;
        }
  
    }

    if( $game->sourcemodule == 'glossary')
        game_update_repetitions( $game->id, $USER->id, 0, $min_id);
    else
        game_update_repetitions( $game->id, $USER->id, $min_id, 0);
    
    return $min_id;
}

function game_update_repetitions( $gameid, $userid, $questionid, $glossaryentryid){
    global $DB;

    $a = array( 'gameid' => $gameid, 'userid' => $userid, 'questionid' => $questionid, 'glossaryentryid' => $glossaryentryid);
    if( ($rec = $DB->get_record( 'game_repetitions', $a, 'id,repetitions r')) != false){
        $updrec->id = $rec->id;
        $updrec->repetitions = $rec->r + 1;
        if( !$DB->update_record( 'game_repetitions', $updrec)){
            error("Update page: can't update game_repetitions id={$updrec->id}");
        }
    }else
    {
        $newrec->gameid = $gameid;
        $newrec->userid = $userid;
        $newrec->questionid = $questionid;
        $newrec->glossaryentryid = $glossaryentryid;
        $newrec->repetitions = 1;
        
        if( $newrec->questionid == ''){
            $newrec->questionid = 0;
        }
        if( $newrec->glossaryentryid == ''){
            $newrec->glossaryentryid = 0;
        }
            
        if (!$DB->insert_record( 'game_repetitions', $newrec)){
            print_r( $newrec);
            print_error("Insert page: new page game_repetitions not inserted");
        }
    }
}

//used by sudoku
function game_questions_selectrandom( $game, $count=1)
{
	global $DB;

	switch( $game->sourcemodule)
	{
	case 'quiz':

		if( $game->quizid == 0){
			print_error( get_string( 'must_select_quiz', 'game'));
		}
	
		$table = '{question} q, {quiz_question_instances} qqi';
		$select = " qqi.quiz=$game->quizid".
			" AND qqi.question=q.id ".			
			" AND q.qtype in ('shortanswer', 'truefalse', 'multichoice')".
			" AND q.hidden=0";
//todo 'match'
		$field = "q.id as id";
		
		$table2 = 'question';
		$fields2 = 'id as questionid,0 as glossaryentryid,qtype';
		break;
	case 'glossary':
		if( $game->glossaryid == 0){
			error( get_string( 'must_select_glossary', 'game'));
		}	
		$table = '{glossary_entries} ge';
		$select = "glossaryid='$game->glossaryid' ";
		if( $game->glossarycategoryid){
		    $table .= ',{glossary_entries_categories} gec';
		    $select .= " AND gec.entryid = ge.id ".
					    " AND gec.categoryid = {$game->glossarycategoryid}";
		}
		$field = 'ge.id';
		$table2 = 'glossary_entries';
		$fields2 = 'id as glossaryentryid, 0 as questionid';
		break;
	case 'question':
		if( $game->questioncategoryid == 0){
			print_error( get_string( 'must_select_questioncategory', 'game'));
		}
		$table = '{question} q';
		
		//inlcude subcategories
        $select = 'category='.$game->questioncategoryid;        
        if( $game->subcategories){
            $cats = question_categorylist( $game->questioncategoryid);
            if( strpos( $cats, ',') > 0){
                $select = 'category in ('.$cats.')';
            }
        }    		
		
		$select .= " AND q.qtype in ('shortanswer', 'truefalse', 'multichoice') ".
			"AND q.hidden=0";
//todo 'match'
		$field = "id";
		
		$table2 = 'question';
		$fields2 = 'id as questionid,0 as glossaryentryid';
		break;
	default:
		print_error( 'No sourcemodule defined');
		break;
	}

	$ids = game_questions_selectrandom_detail( $table, $select, $field, $count);
	if( $ids === false){
		print_error( get_string( 'no_questions', 'game'));
	}

	if( count( $ids) > 1){
		//randomize the array
		shuffle( $ids);
	}
	
	$ret = array();
	foreach( $ids as $id)
	{
		if( $recquestion = $DB->get_record( $table2, array( 'id' => $id), $fields2)){
			unset( $new);
			$new->questionid = (int )$recquestion->questionid;
			$new->glossaryentryid = (int )$recquestion->glossaryentryid;
			$ret[] = $new;
		}
	}

	return $ret;	
}

//used by game_questions_selectrandom
function game_questions_selectrandom_detail( $table, $select, $id_field="id", $count=1)
{
    global $DB;
		
    $sql = "SELECT $id_field FROM $table WHERE $select";
	if( ($recs=$DB->get_records_sql( $sql)) == false)
        return false;
    	
	//the array contains the ids of all questions
	$a = array();
    foreach( $recs as $rec){
        $a[ $rec->id] = $rec->id;
    }

	if( $count >= count( $a)){
		return $a;
	}else
	{
		$id = array_rand(  $a, $count);
		return ( $count == 1  ? array( $id) : $id);
	}
}

//Tries to detect the language of word
function game_detectlanguage( $word){
    global $CFG;
    
    $langs = get_string_manager()->get_list_of_translations();
    
    foreach( $langs as $lang => $name)
    {
        $strings = get_string_manager()->load_component_strings( 'game', $lang);
        if( isset( $strings[ 'lettersall']))
        {
            $letters = $strings[ 'lettersall'];
            $word2 = game_upper( $word, $lang);

            if( hangman_existall( $word2, $letters))
                return $lang;
       }
    }

    return false;
}

//The words maybe are in two languages e.g. greek or english
//so I try to find the correct one.
function game_getallletters( $word, $lang='')
{
    for(;;)
    {
        $strings = get_string_manager()->load_component_strings( 'game', $lang);
        if( isset( $strings[ 'lettersall']))
        {
            $letters = $strings[ 'lettersall'];
            $word2 = game_upper( $word, $lang);
            if( hangman_existall( $word2, $letters))
                return $letters;
        }

        if( $lang == '')
            break;
        else
            $lang = '';   
    }

    
    return '';
}


function hangman_existall( $str, $strfind)
{
	$textlib = textlib_get_instance();
	
    $n = $textlib->strlen( $str);
    for( $i=0; $i < $n; $i++)
    {
		$pos = $textlib->strpos( $strfind, $textlib->substr( $str, $i, 1));
        if( $pos === false)
            return false;
    }
  
    return true;
}

//used by cross
function game_questions_shortanswer( $game)
{
	switch( $game->sourcemodule)
	{
	case 'glossary':
		$recs = game_questions_shortanswer_glossary( $game);
		break;
	case 'quiz';
		$recs = game_questions_shortanswer_quiz( $game);
		break;
	case 'question';
		$recs = game_questions_shortanswer_question( $game);
		break;
	}

	return $recs;
}

//used by cross
function game_questions_shortanswer_glossary( $game)
{
    global $DB;
    
    $select = "glossaryid={$game->glossaryid}";
    $table = '{glossary_entries} ge';
    if( $game->glossarycategoryid){
		$table .= ',{glossary_entries_categories} gec';
		$select .= ' AND gec.entryid = ge.id '.
					    ' AND gec.categoryid = '.$game->glossarycategoryid;
    }        

    $sql = 'SELECT ge.id, concept as answertext, definition as questiontext, ge.id as glossaryentryid, 0 as questionid, attachment '.
           " FROM $table WHERE $select";

	return $DB->get_records_sql( $sql);

}
//used by cross
function game_questions_shortanswer_quiz( $game)
{
    global $DB;
	
    if( $game->quizid == 0){
        print_error( get_string( 'must_select_quiz', 'game'));
    }	
    		
	$select = "qtype='shortanswer' AND quiz='$game->quizid' ".
					" AND qqi.question=q.id".
					" AND qa.question=q.id".
					" AND q.hidden=0";
	$table = "{question} q,{quiz_question_instances} qqi,{question_answers} qa";
	$fields = "qa.id as qaid, q.id, q.questiontext as questiontext, ".
				   "qa.answer as answertext, q.id as questionid,".
				   " 0 as glossaryentryid,'' as attachment";

	return game_questions_shortanswer_question_fraction( $table, $fields, $select);
}

//used by cross
function game_questions_shortanswer_question( $game)
{
    if( $game->questioncategoryid == 0){
        print_error( get_string( 'must_select_questioncategory', 'game'));
    }
    		
    //include subcategories    		
    $select = 'q.category='.$game->questioncategoryid;        
    if( $game->subcategories){
        $cats = question_categorylist( $game->questioncategoryid);
        if( strpos( $cats, ',') > 0){
            $select = 'q.category in ('.$cats.')';
        }
    }    		
    		
	$select .= " AND qtype='shortanswer' ".
					" AND qa.question=q.id".
					" AND q.hidden=0";
	$table = "{question} q,{question_answers} qa";
	$fields = "qa.id as qaid, q.id, q.questiontext as questiontext, ".
				   "qa.answer as answertext, q.id as questionid";
	
	return game_questions_shortanswer_question_fraction( $table, $fields, $select);
}
	
function game_questions_shortanswer_question_fraction( $table, $fields, $select)
{
    global $DB;
    
	$sql = "SELECT $fields FROM ".$table." WHERE $select ORDER BY fraction DESC";
    
	$recs = $DB->get_records_sql( $sql);
	if( $recs == false){
	    print_error( get_string( 'no_questions', 'game'));
	}
	
	$recs2 = array();
	$map = array();
	foreach( $recs as $rec){
	    unset( $rec2);
	    if( array_key_exists( $rec->questionid, $map)){
	        continue;
	    }
	    $rec2->id = $rec->id;
	    $rec2->questiontext = $rec->questiontext;
	    $rec2->answertext = $rec->answertext;
	    $rec2->questionid = $rec->questionid;
	    $rec2->glossaryentryid = 0;
	    $rec2->attachment = '';
	    $recs2[] = $rec2;
	    
	    $map[ $rec->questionid] = $rec->questionid;
	}

	return $recs2;
}


	function game_setchar( &$s, $pos, $char)
	{
		$ret = "";
		
		$textlib = textlib_get_instance();
	
		if( $pos > 0){
			$ret .= $textlib->substr( $s, 0, $pos);
		}
		
		$s = $ret . $char . $textlib->substr( $s, $pos+1);
	}


	function game_insert_record( $table, $rec)
	{
		global $DB;
		
		if( $DB->get_record($table, array('id' => $rec->id), 'id,id') == false){
    		$sql = 'INSERT INTO {'.$table.'}(id) VALUES('.$rec->id.')';
	    	if( !$DB->execute( $sql)){
	    		print_error( "Cannot insert an empty $table with id=$rec->id");
	    		return false;
	    	}
	    }
		if( isset( $rec->question)){
    		$temp = $rec->question;
	    	$rec->question = addslashes( $rec->question);
	    }
		$ret = $DB->update_record( $table, $rec);

		if( isset( $rec->question)){
    		$rec->question = $temp;
    	}
		
		return $ret;
	}

	//if score is negative doesn't update the record
	//score is between 0 and 1
	function game_updateattempts( $game, $attempt, $score, $finished)
	{
        global $DB, $USER;

	    if( $attempt != false){	    
		    $updrec->id = $attempt->id;
    		$updrec->timelastattempt = time();
    		$updrec->lastip = getremoteaddr();
	    	if( isset( $_SERVER[ 'REMOTE_HOST'])){
	    		$updrec->lastremotehost = $_SERVER[ 'REMOTE_HOST'];
	    	}
	    	else{
	    		$updrec->lastremotehost = gethostbyaddr( $updrec->lastip);
	    	}

	    	if( $score >= 0){
	    		$updrec->score = $score;
	    	}

	    	if( $finished){
	    		$updrec->timefinish = $updrec->timelastattempt;
		    }
		
    		$updrec->attempts = $attempt->attempts + 1;

	    	if( !$DB->update_record( 'game_attempts', $updrec)){
	    		show_error( "game_updateattempts: Can't update game_attempts id=$updrec->id");
	    	}
	    	
            // update grade item and send all grades to gradebook
            game_grade_item_update( $game);
            game_update_grades( $game);    
	    }
		
		//Update table game_grades
		if( $finished){
			game_save_best_score( $game);
		}
	}
	

	function game_updateattempts_maxgrade( $game, $attempt, $grade, $finished)
	{
        global $DB;

		$recgrade = $DB->get_field( 'game_attempts', 'score', array( 'id' => $attempt->id));

		if( $recgrade >  $grade){
			$grade = -1;		//don't touch the grade
		}
		
		game_updateattempts( $game, $attempt, $grade, $finished);
	}

	function game_update_queries( $game, $attempt, $query, $score, $studentanswer, $updatetries=false)
	{
		global $DB, $USER;
		
		if( $query->id != 0){
			$select = "id=$query->id";
		}else
		{
			$select = "attemptid = $attempt->id AND sourcemodule = '{$query->sourcemodule}'";
			switch( $query->sourcemodule)
			{
			case 'quiz':
				$select .= " AND questionid='$query->questionid' ";
				break;
			case 'glossary':
				$select .= " AND glossaryentryid='$query->glossaryentryid'";
				break;
			}		
		}

		if( ($recq = $DB->get_record_select( 'game_queries', $select)) === false)
		{
			unset( $recq);
			$recq->gamekind = $game->gamekind;
			$recq->gameid = $attempt->gameid;
			$recq->userid = $attempt->userid;
			$recq->attemptid = $attempt->id;
			$recq->sourcemodule = $query->sourcemodule;
			$recq->questionid = $query->questionid;
			$recq->glossaryentryid = $query->glossaryentryid;
			if ($updatetries)
				$recq->tries = 1;

			if (!($recq->id = $DB->insert_record( 'game_queries', $recq))){
				print_error( 'Insert page: new page game_queries not inserted');
			}
		}
		
		$updrec->id = $recq->id;
		$updrec->timelastattempt = time();
		
        if( $score >= 0){
            $updrec->score = $score;
        }
		
		if( $studentanswer != ''){
			$updrec->studentanswer = $studentanswer;
		}
		
		if ($updatetries)
			$updrec->tries = $recq->tries + 1;
			
		if (!($DB->update_record( 'game_queries', $updrec))){
			print_error( "game_update_queries: not updated id=$updrec->id");
		}
	}
	

	function game_getattempt( $game, &$detail)
	{
		global $DB, $USER;
		
		$select = "gameid=$game->id AND userid=$USER->id and timefinish=0 ";
		if( $USER->id == 1){
			$key = 'mod/game:instanceid'.$game->id;
			if( array_key_exists( $key, $_SESSION)){
				$select .= ' AND id="'.$_SESSION[ $key].'"';
			}else{
				$select .= ' AND id=-1';
			}
		}

		if( ($recs=$DB->get_records_select( 'game_attempts', $select))){
			foreach( $recs as $attempt){
				if( $USER->id == 1){
					$_SESSION[ $key] = $attempt->id;
				}
				
				$detail = $DB->get_record( 'game_'.$game->gamekind, array( 'id' => $attempt->id));

				return $attempt;
			}
		}
		
		return false;
	}

/**
 * @param integer $gameid the game id.
 * @param integer $userid the userid.
 * @param string $status 'all', 'finished' or 'unfinished' to control
 * @return an array of all the user's attempts at this game. Returns an empty array if there are none.
 */
function game_get_user_attempts( $gameid, $userid, $status = 'finished') {
    global $DB;

    $status_condition = array(
        'all' => '',
        'finished' => ' AND timefinish > 0',
        'unfinished' => ' AND timefinish = 0'
    );
    if ($attempts = $DB->get_records_select( 'game_attempts',
            "gameid = ? AND userid = ? AND preview = 0" . $status_condition[$status],
            array( $gameid, $userid), 'attempt ASC')) {
        return $attempts;
    } else {
        return array();
    }
}


/**
 * Returns an unfinished attempt (if there is one) for the given
 * user on the given game. This function does not return preview attempts.
 *
 * @param integer $gameid the id of the game.
 * @param integer $userid the id of the user.
 *
 * @return mixed the unfinished attempt if there is one, false if not.
 */
function game_get_user_attempt_unfinished( $gameid, $userid) {
    $attempts = game_get_user_attempts( $gameid, $userid, 'unfinished');
    if ($attempts) {
        return array_shift($attempts);
    } else {
        return false;
    }
}

/**
 * Get the best current score for a particular user in a game.
 *
 * @param object $game the game object.
 * @param integer $userid the id of the user.
 * @return float the user's current grade for this game.
 */
function game_get_best_score($game, $userid) {
    global $DB;

    $score = $DB->get_field( 'game_grades', 'score', array( 'gameid' => $game->id, 'userid' => $userid));

    // Need to detect errors/no result, without catching 0 scores.
    if (is_numeric($score)) {
        return $score;
    } else {
        return NULL;
    }
}

function game_get_best_grade($game, $userid) {
    $score = game_get_best_score( $game, $userid);
	
	if( is_numeric( $score)){
		return round( $score * $game->grade, $game->decimalpoints);
	}else
	{
        return NULL;
    }
}


function game_score_to_grade($score, $game) {
    if ($score) {
        return round($score*$game->grade, $game->decimalpoints);
    } else {
        return 0;
    }
}

/**
 * Determine review options
 *
 * @param object $game the game instance.
 * @param object $attempt the attempt in question.
 * @param $context the roles and permissions context,
 *          normally the context for the game module instance.
 *
 * @return object an object with boolean fields responses, scores, feedback,
 *          correct_responses, solutions and general feedback
 */
function game_get_reviewoptions($game, $attempt, $context=null) {

    $options = new stdClass;
    $options->readonly = true;
    // Provide the links to the question review and comment script
    $options->questionreviewlink = '/mod/game/reviewquestion.php';

    if ($context /* && has_capability('mod/game:viewreports', $context) */ and !$attempt->preview) {
        // The teacher should be shown everything except during preview when the teachers
        // wants to see just what the students see
        $options->responses = true;
        $options->scores = true;
        $options->feedback = true;
        $options->correct_responses = true;
        $options->solutions = false;
        $options->generalfeedback = true;
        $options->overallfeedback = true;

        // Show a link to the comment box only for closed attempts
        if ($attempt->timefinish) {
            $options->questioncommentlink = '/mod/game/comment.php';
        }
    } else {
        if (((time() - $attempt->timefinish) < 120) || $attempt->timefinish==0) {
            $game_state_mask = GAME_REVIEW_IMMEDIATELY;
        } else if (!$game->timeclose or time() < $game->timeclose) {
            $game_state_mask = GAME_REVIEW_OPEN;
        } else {
            $game_state_mask = GAME_REVIEW_CLOSED;
        }
        $options->responses = ($game->review & $game_state_mask & GAME_REVIEW_RESPONSES) ? 1 : 0;
        $options->scores = ($game->review & $game_state_mask & GAME_REVIEW_SCORES) ? 1 : 0;
        $options->feedback = ($game->review & $game_state_mask & GAME_REVIEW_FEEDBACK) ? 1 : 0;
        $options->correct_responses = ($game->review & $game_state_mask & GAME_REVIEW_ANSWERS) ? 1 : 0;
        $options->solutions = ($game->review & $game_state_mask & GAME_REVIEW_SOLUTIONS) ? 1 : 0;
        $options->generalfeedback = ($game->review & $game_state_mask & GAME_REVIEW_GENERALFEEDBACK) ? 1 : 0;
        $options->overallfeedback = $attempt->timefinish && ($game->review & $game_state_mask & GAME_REVIEW_FEEDBACK);
    }

    return $options;
}


function game_compute_attempt_layout( $game, &$attempt)
{
    global $DB;

	$ret = '';
	$recs = $DB->get_records_select( 'game_queries', "attemptid=$attempt->id", null, '', 'id,questionid,sourcemodule,glossaryentryid');
	if( $recs){
		foreach( $recs as $rec){
			if( $rec->sourcemodule == 'glossary'){
				$ret .= $rec->glossaryentryid.'G,';
			}else{
				$ret .= $rec->questionid.',';
			}
		}
	}
	
	$attempt->layout = $ret.'0';
}

/**
 * Combines the review options from a number of different game attempts.
 * Returns an array of two ojects, so he suggested way of calling this
 * funciton is:
 * list($someoptions, $alloptions) = game_get_combined_reviewoptions(...)
 *
 * @param object $game the game instance.
 * @param array $attempts an array of attempt objects.
 * @param $context the roles and permissions context,
 *          normally the context for the game module instance.
 *
 * @return array of two options objects, one showing which options are true for
 *          at least one of the attempts, the other showing which options are true
 *          for all attempts.
 */
function game_get_combined_reviewoptions($game, $attempts, $context=null) {
    $fields = array('readonly', 'scores', 'feedback', 'correct_responses', 'solutions', 'generalfeedback', 'overallfeedback');
    $someoptions = new stdClass;
    $alloptions = new stdClass;
    foreach ($fields as $field) {
        $someoptions->$field = false;
        $alloptions->$field = true;
    }
    foreach ($attempts as $attempt) {
        $attemptoptions = game_get_reviewoptions( $game, $attempt, $context);
        foreach ($fields as $field) {
            $someoptions->$field = $someoptions->$field || $attemptoptions->$field;
            $alloptions->$field = $alloptions->$field && $attemptoptions->$field;
        }
    }
    return array( $someoptions, $alloptions);
}

/**
 * Save the overall grade for a user at a game in the game_grades table
 *
 * @param object $quiz The game for which the best grade is to be calculated and then saved.
 * @param integer $userid The userid to calculate the grade for. Defaults to the current user.
 * @return boolean Indicates success or failure.
 */
function game_save_best_score($game) {
    global $DB, $USER;

    // Get all the attempts made by the user
    if (!$attempts = game_get_user_attempts( $game->id, $USER->id)) {
        print_error( 'Could not find any user attempts');
    }

    // Calculate the best grade
    $bestscore = game_calculate_best_score( $game, $attempts);

    // Save the best grade in the database
    if ($grade = $DB->get_record('game_grades', array( 'gameid' => $game->id, 'userid' => $USER->id))) {
        $grade->score = $bestscore;
        $grade->timemodified = time();
        if (!$DB->update_record('game_grades', $grade)) {
            print_error('Could not update best grade');
        }
    } else {
        $grade->gameid = $game->id;
        $grade->userid = $USER->id;
        $grade->score = $bestscore;
        $grade->timemodified = time();
        if (!$DB->insert_record( 'game_grades', $grade)) {
            print_error( 'Could not insert new best grade');
        }
    }

    return true;
}

/**
* Calculate the overall score for a game given a number of attempts by a particular user.
*
* @return double         The overall score
* @param object $game    The game for which the best score is to be calculated
* @param array $attempts An array of all the attempts of the user at the game
*/
function game_calculate_best_score($game, $attempts) {

    switch ($game->grademethod) {

        case GAME_GRADEMETHOD_FIRST:
            foreach ($attempts as $attempt) {
                return $attempt->score;
            }
            break;

        case GAME_GRADEMETHOD_LAST:
            foreach ($attempts as $attempt) {
                $final = $attempt->score;
            }
            return $final;

        case GAME_GRADEMETHOD_AVERAGE:
            $sum = 0;
            $count = 0;
            foreach ($attempts as $attempt) {
                $sum += $attempt->score;
                $count++;
            }
            return (float)$sum/$count;

        default:
        case GAME_GRADEMETHOD_HIGHEST:
            $max = 0;
            foreach ($attempts as $attempt) {
                if ($attempt->score > $max) {
                    $max = $attempt->score;
                }
            }
            return $max;
    }
}

/**
* Return the attempt with the best score for a game
*
* Which attempt is the best depends on $game->grademethod. If the grade
* method is GRADEAVERAGE then this function simply returns the last attempt.
* @return object         The attempt with the best grade
* @param object $game    The game for which the best grade is to be calculated
* @param array $attempts An array of all the attempts of the user at the game
*/
function game_calculate_best_attempt($game, $attempts) {

    switch ($game->grademethod) {

        case GAME_ATTEMPTFIRST:
            foreach ($attempts as $attempt) {
                return $attempt;
            }
            break;

        case GAME_GRADEAVERAGE: // need to do something with it :-)
        case GAME_ATTEMPTLAST:
            foreach ($attempts as $attempt) {
                $final = $attempt;
            }
            return $final;

        default:
        case GAME_GRADEHIGHEST:
            $max = -1;
            foreach ($attempts as $attempt) {
                if ($attempt->sumgrades > $max) {
                    $max = $attempt->sumgrades;
                    $maxattempt = $attempt;
                }
            }
            return $maxattempt;
    }
}


/**
* Loads the most recent state of each question session from the database
*
* For each question the most recent session state for the current attempt
* is loaded from the game_questions table and the question type specific data
*
* @return array           An array of state objects representing the most recent
*                         states of the question sessions.
* @param array $questions The questions for which sessions are to be restored or
*                         created.
* @param object $cmoptions
* @param object $attempt  The attempt for which the question sessions are
*                         to be restored or created.
* @param mixed either the id of a previous attempt, if this attmpt is
*                         building on a previous one, or false for a clean attempt.
*/
function game_get_question_states(&$questions, $cmoptions, $attempt, $lastattemptid = false) {
    global $DB, $QTYPES;

    // get the question ids
    $ids = array_keys( $questions);
    $questionlist = implode(',', $ids);

    $statefields = 'questionid as question, manualcomment, score as grade';

    $sql = "SELECT $statefields".
           "  FROM {game_questions} ".
           " WHERE attemptid = '$attempt->id'".
           "   AND questionid IN ($questionlist)";
    $states = $DB->get_records_sql($sql);
	
    // loop through all questions and set the last_graded states
    foreach ($ids as $i) {	
		// Create the empty question type specific information
        if (!$QTYPES[$questions[$i]->qtype]->create_session_and_responses(
			$questions[$i], $states[$i], $cmoptions, $attempt)) {
				return false;
		}

		$states[$i]->last_graded = clone($states[$i]);
    }
    return $states;
}

function game_sudoku_getquestions( $questionlist)
{
    global $DB;

    // Load the questions
    if (!$questions = $DB->get_records_select( 'question', "id IN ($questionlist)")) {
        print_error( get_string( 'no_questions', 'game'));
    }

    // Load the question type specific information
    if (!get_question_options($questions)) {
        print_error('Could not load question options');
    }
	
    return $questions;
}

function game_filtertext( $text, $courseid){
    $formatoptions->noclean = true;
    $formatoptions->filter = 1;
    $text = trim( format_text( $text, FORMAT_MOODLE, $formatoptions, $courseid));

    $start = '<div class="text_to_html">';
    if( substr( $text, 0, strlen( $start)) == $start){
        if( substr( $text, -6) == '</div>'){
            $text = substr( $text, strlen( $start), -6);
        }
    }
    if( substr( $text, 0, 3) == '<p>'){
        if( substr( $text, -4) == '</p>'){
            $text = substr( $text, 3, -4);
        }
    }
    
    return $text;
}

function game_tojavascriptstring( $text)
{
    $from = array('"',"\r", "\n");
    $to = array('\"', ' ', ' ');
    
    $from[] = '<script ';   $to[] = '<" + "script ';
    $from[] = '</script>';   $to[] = '<" + "/script>';
    
    $text = str_replace( $from, $to, $text);
        
    return $text;
}

function game_repairquestion( $s){
    if( substr( $s, 0, 3) == '<p>'){
        $s = substr( $s, 3);
    } 
    if( substr( $s, -4) == '</p>'){
        $s = substr( $s, 0, -4);
    }
    if( substr( $s, 0, 4) == '<br>'){
        $s = substr( $s, 4);
    }
    if( substr( $s, 0, 6) == '<br />'){
        $s = substr( $s, 6);
    }		
    if( substr( $s, 0, 5) == '<div ' and substr( $s, -6) == '</div>'){
        $pos = strpos( $s, '>');
        if( $pos != false){
            $s = substr( $s, $pos+1);
        }
        $s = substr( $s, 0, -6);
    }
    
    return $s;
}

/**
 * Delete a game attempt.
 */
function game_delete_attempt($attempt, $game) {
    global $DB;

    if (is_numeric($attempt)) {
        if (!$attempt = $DB->get_record('game_attempts', 'id', $attempt)) {
            return;
        }
    }

    if ($attempt->gameid != $game->id) {
        debugging("Trying to delete attempt $attempt->id which belongs to game $attempt->gameid " .
                "but was passed gameid $game->id.");
        return;
    }

    $DB->delete_records('game_attempts', array( 'id' => $attempt->id));
    delete_attempt( $attempt->id);

    // Search game_attempts for other instances by this user.
    // If none, then delete record for this game, this user from game_grades
    // else recalculate best grade

    $userid = $attempt->userid;
    if (!$DB->record_exists('game_attempts', array( 'userid' => $userid, 'gameid' => $game->id))) {
        $DB->delete_records('game_grades', array( 'userid' => $userid, 'gameid' => $game->id));
    } else {
        game_save_best_score( $game);
    }

    game_update_grades( $game, $userid);
}

/**
 * Returns the most recent attempt by a given user on a given game.
 * May be finished, or may not.
 *
 * @param integer $gameid the id of the game.
 * @param integer $userid the id of the user.
 *
 * @return mixed the attempt if there is one, false if not.
 */
function game_get_latest_attempt_by_user($gameid, $userid) {
    global $DB;

    $attempt = $DB->get_records_sql('SELECT qa.* FROM {game_attempts} qa
            WHERE qa.gameid=? AND qa.userid= ? ORDER BY qa.timestart DESC, qa.id DESC', array($gameid, $userid), 0, 1);

    if ($attempt) {
        return array_shift( $attempt);
    } else {
        return false;
    }
}

/**
 * @param int $option one of the values GAME_GRADEHIGHEST, GAME_GRADEAVERAGE, GAME_ATTEMPTFIRST or GAME_ATTEMPTLAST.
 * @return the lang string for that option.
 */
function game_get_grading_option_name($option) {
    if( $option == 0)
        $option = GAME_GRADEHIGHEST;

    $strings = game_get_grading_options();
    return $strings[$option];
}

function game_right_to_left( $lang){
    return ( get_string_manager()->get_string('thisdirection', 'langconfig', NULL, $lang) == 'rtl');
}

function game_compute_reserve_print( $attempt, &$wordrtl, &$reverseprint){
    if( function_exists( 'right_to_left')){
        if( $attempt->language != '')
            $wordrtl = game_right_to_left( $attempt->language);
        else
            $wordrtl = right_to_left();
        $reverseprint = ($wordrtl != right_to_left());
    }else{
        $reverseprint = false;
        $wordrtl = 'ltr';
    }
}

function game_select_from_repetitions( $game, $recs, $need){
    global $DB, $USER;

    $ret = array();

    if( count($recs) <= $need){
        foreach( $recs as $id => $rec)
            $ret[ $id] = 1;
        return $ret;
    }

    $field = ($game->sourcemodule == 'glossary' ? 'glossaryentryid' : 'questionid');

    $countzero = 0;
    foreach( $recs as $rec){
        $a = array( 'gameid' => $game->id, 'userid' => $USER->id, 'questionid' => $rec->questionid, 'glossaryentryid' => $rec->glossaryentryid);
        $id = $rec->$field;
        if( ($rec = $DB->get_record( 'game_repetitions', $a, 'id,repetitions r')) != false){
            $reps[ $id] = $rec->r;
        }else
        {
            $reps[ $id] = 0;
            if( ++$countzero >= $need)
                break;
        }
    }
    asort( $reps);
    foreach( $reps as $id => $r){
        $ret[ $id] = 1;
        if( count( $ret) >= $need)
            break;
    }

    return $ret;
}
