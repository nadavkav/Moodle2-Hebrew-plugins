<?php  // $Id: attempt.php,v 1.15 2010/08/27 10:21:36 bdaloukas Exp $
/**
 * This page prints a particular attempt of game
 * 
 * @author  bdaloukas
 * @version $Id: attempt.php,v 1.15 2010/08/27 10:21:36 bdaloukas Exp $
 * @package game
 **/    
    require_once( "../../config.php");
    require_once( "lib.php");
    require_once( "locallib.php");

    require_once( "hangman/play.php");
    require_once( "cross/play.php");
    require_once( "cryptex/play.php");
    require_once( "millionaire/play.php");
    require_once( "sudoku/play.php");
    require_once( "bookquiz/play.php");
    require_once( "snakes/play.php");
    require_once( "hiddenpicture/play.php");

    $action  = optional_param('action', "", PARAM_ALPHANUM);  // action
	
    game_show_header( $id, $game, $course);
    game_do_attempt( $id, $game, $action, $course);

    function game_show_header( &$id, &$game, &$course)
    {
        global $DB, $USER, $PAGE, $OUTPUT;

        $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
        $q = optional_param('q',  0, PARAM_INT);  // game ID

        if ($id) {
            if (! $cm = get_coursemodule_from_id('game', $id)) {
                print_error('invalidcoursemodule');
            }
            if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
                print_error('coursemisconf');
            }
            if (! $game = $DB->get_record('game', array('id' => $cm->instance))) {
                print_error('invalidcoursemodule');
            }
        } else {
            if (! $game = $DB->get_record('game', array('id' => $q))) {
                print_error('invalidgameid', 'game');
            }
            if (! $course = $DB->get_record('course', array('id' => $game->course))) {
                print_error('invalidcourseid');
            }
            if (! $cm = get_coursemodule_from_instance('game', $game->id, $course->id)) {
                print_error('invalidcoursemodule');
            }
        }

        /// Check login and get context.
        require_login($course->id, false, $cm);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        require_capability('mod/game:view', $context);

        /// Cache some other capabilites we use several times.
        $canattempt = has_capability('mod/game:attempt', $context);
        $canreviewmine = has_capability('mod/game:reviewmyattempts', $context);

        /// Create an object to manage all the other (non-roles) access rules.
        $timenow = time();
        //$accessmanager = new game_access_manager(game::create($game->id, $USER->id), $timenow);

        /// If no questions have been set up yet redirect to edit.php
        //if (!$game->questions && has_capability('mod/game:manage', $context)) {
        //    redirect($CFG->wwwroot . '/mod/game/edit.php?cmid=' . $cm->id);
        //}

        /// Log this request.
        add_to_log($course->id, 'game', 'view', "view.php?id=$cm->id", $game->id, $cm->id);

        /// Initialize $PAGE, compute blocks
        $PAGE->set_url('/mod/game/view.php', array('id' => $cm->id));

        $edit = optional_param('edit', -1, PARAM_BOOL);
        if ($edit != -1 && $PAGE->user_allowed_editing()) {
            $USER->editing = $edit;
        }

        $PAGE->requires->yui2_lib('event');

        // Note: MDL-19010 there will be further changes to printing header and blocks.
        // The code will be much nicer than this eventually.
        $title = $course->shortname . ': ' . format_string($game->name);

        if ($PAGE->user_allowed_editing() && !empty($CFG->showblocksonmodpages)) {
            $buttons = '<table><tr><td><form method="get" action="view.php"><div>'.
                '<input type="hidden" name="id" value="'.$cm->id.'" />'.
                '<input type="hidden" name="edit" value="'.($PAGE->user_is_editing()?'off':'on').'" />'.
                '<input type="submit" value="'.get_string($PAGE->user_is_editing()?'blockseditoff':'blocksediton').'" /></div></form></td></tr></table>';
            $PAGE->set_button($buttons);
        }

        $PAGE->set_title($title);
        $PAGE->set_heading($course->fullname);

        echo $OUTPUT->header();
    }

    function game_do_attempt( $id, $game, $action, $course)
    {
        global $OUTPUT;

	    $forcenew = optional_param('forcenew', false, PARAM_BOOL); // Teacher has requested new preview
        $continue = false;
/// Print the main part of the page
    	switch( $action)
    	{
    	case 'crosscheck':
    		$attempt = game_getattempt( $game, $detail);
    		$g = game_cross_unpackpuzzle( $_GET[ 'g']);
    		$finishattempt = array_key_exists( 'finishattempt', $_GET);
    		game_cross_continue( $id, $game, $attempt, $detail, $g, $finishattempt);
    		break;
    	case 'crossprint':
    		$attempt = game_getattempt( $game, $detail);
    		game_cross_play( $id, $game, $attempt, $detail, '', true, false, false, true);
    		break;
        case 'sudokucheck':		//the student tries to answer a question
    		$attempt = game_getattempt( $game, $detail);
    		$finishattempt = array_key_exists( 'finishattempt', $_POST);
    		game_sudoku_check_questions( $id, $game, $attempt, $detail, $finishattempt, $course);
            $continue = true;
            break;
        case 'sudokucheckg':		//the student tries to guess a glossaryenry
    		$attempt = game_getattempt( $game, $detail);
    		$endofgame = array_key_exists( 'endofgame', $_GET);
    		$continue = game_sudoku_check_glossaryentries( $id, $game, $attempt, $detail, $endofgame, $course);
            $continue = true;
            break;
        case 'sudokucheckn':	//the user tries to guess a number
    		$attempt = game_getattempt( $game, $detail);
    		$pos = $_GET[ 'pos'];
    		$num = $_GET[ 'num'];
    		game_sudoku_check_number( $id, $game, $attempt, $detail, $pos, $num);
            $continue = true;
            break;
    	case 'cryptexcheck':	//the user tries to guess a question
    		$attempt = game_getattempt( $game, $detail);
    		$q = $_GET[ 'q'];
    		$answer = $_GET[ 'answer'];
    		game_cryptex_check( $id, $game, $attempt, $detail, $q, $answer);
            break;
        case 'bookquizcheck':		//the student tries to answer a question
    		$attempt = game_getattempt( $game, $detail);
    		game_bookquiz_check_questions( $id, $game, $attempt, $detail);
            break;
        case 'snakescheck':		//the student tries to answer a question
    		$attempt = game_getattempt( $game, $detail);
    		game_snakes_check_questions( $id, $game, $attempt, $detail);
            break;
        case 'snakescheckg':		//the student tries to answer a question
    		$attempt = game_getattempt( $game, $detail);
    		game_snakes_check_glossary( $id, $game, $attempt, $detail);
            break;        
        case 'hiddenpicturecheck':		//the student tries to answer a question
	    	$attempt = game_getattempt( $game, $detail);
	    	$finishattempt = array_key_exists( 'finishattempt', $_POST);
	    	$continue = game_hiddenpicture_check_questions( $id, $game, $attempt, $detail, $finishattempt);
            break;
        case 'hiddenpicturecheckg':		//the student tries to guess a glossaryenry
	    	$attempt = game_getattempt( $game, $detail);
	    	$endofgame = array_key_exists( 'endofgame', $_GET);
	    	game_hiddenpicture_check_mainquestion( $id, $game, $attempt, $detail, $endofgame);
            break;
        default:
            $continue = true;
            break;    
	    }
        if( $continue){
            game_create( $game, $id, $forcenew, $course);
        }
/// Finish the page
        echo $OUTPUT->footer();
    }


	function game_create( $game, $id, $forcenew, $course)
	{
		global $USER, $CFG, $DB;
		
		$attempt = game_getattempt( $game, $detail);

		switch( $game->gamekind)
		{
		case 'cross':
			game_cross_continue( $id, $game, $attempt, $detail, '', $forcenew);
			break;
		case 'hangman':
			if( array_key_exists( 'newletter', $_GET))
				$newletter = $_GET[ 'newletter'];
			else
				$newletter = '';
			if( array_key_exists( 'action2', $_GET))
				$action2 = $_GET[ 'action2'];
			else
				$action2 = '';
			game_hangman_continue( $id, $game, $attempt, $detail, $newletter, $action2);
			break;
		case 'millionaire':
			game_millionaire_continue( $id, $game, $attempt, $detail);
			break;
		case 'bookquiz':
			if( array_key_exists( 'chapterid', $_GET))
				$chapterid = (int )$_GET[ 'chapterid'];
			else
				$chapterid = 0;		
			game_bookquiz_continue( $id, $game, $attempt, $detail, $chapterid);
			break;
		case 'sudoku':
			game_sudoku_continue( $id, $game, $attempt, $detail);
			break;
		case 'cryptex':
			game_cryptex_continue( $id, $game, $attempt, $detail, $forcenew);
			break;
		case 'snakes':
			game_snakes_continue( $id, $game, $attempt, $detail);
			break;
		case 'hiddenpicture':
			game_hiddenpicture_continue( $id, $game, $attempt, $detail);
			break;
		case '':
			echo get_string( 'useupdategame', 'game');
			print_continue($CFG->wwwroot . '/course/view.php?id=' . $course->id);
			break;
		default:
			error( "Game {$game->gamekind} not found");
			break;
		}
	}
	
	//inserts a record to game_attempts
	function game_addattempt( $game)
	{
		global $DB, $USER;
		
		$newrec->gamekind = $game->gamekind;
		$newrec->gameid = $game->id;
		$newrec->userid = $USER->id;
		$newrec->timestart = time();
		$newrec->timefinish = 0;
		$newrec->timelastattempt = 0;
		$newrec->preview = 0;
        $params = array( 'gameid' => $game->id, 'userid' => $USER->id);
		$newrec->attempt = $DB->get_field( 'game_attempts', 'max(attempt)', $params) + 1;
		$newrec->score = 0;

		if (!($newid = $DB->insert_record( 'game_attempts', $newrec))){
			error("Insert game_attempts: new rec not inserted");
		}
		
		if( $USER->username == 'guest'){
			$key = 'mod/game:instanceid'.$game->id;
			$_SESSION[ $key] = $newid;
		}

		return $DB->get_record_select( 'game_attempts', 'id='.$newid);
	}
	
	
function game_cross_unpackpuzzle( $g)
{
	$ret = "";
	$textlib = textlib_get_instance();
	
	$len = $textlib->strlen( $g);
	while( $len)
	{
		for( $i=0; $i < $len; $i++)
		{
			$c = $textlib->substr( $g, $i, 1);
			if( $c >= '1' and $c <= '9'){
			    if( $i > 0){
			        //found escape character
			        if(  $textlib->substr( $g, $i-1, 1) == '/'){
			            $g = $textlib->substr( $g, 0, $i-1).$textlib->substr( $g, $i);
			            $i--;
			            $len--;
			            continue;
			        }
			    }
				break;
			}
		}

		if( $i < $len){
			//found the start of a number
			for( $j=$i+1; $j < $len; $j++)
			{
				$c = $textlib->substr( $g, $j, 1);
				if( $c < '0' or $c > '9'){
					break;
				}
			}
			$count = $textlib->substr( $g, $i, $j-$i);
			$ret .= $textlib->substr( $g, 0, $i) . str_repeat( '_', $count);
			
			$g = $textlib->substr( $g, $j);
			$len = $textlib->strlen( $g);
			
		}else
		{
			$ret .= $g;
			break;
		}
	}
	
	return $ret;
}
