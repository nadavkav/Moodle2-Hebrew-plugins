<?php  // $Id: preview.php,v 1.9 2010/07/26 00:07:13 bdaloukas Exp $
/**
 * This page prints a particular attempt of game
 * 
 * @author  bdaloukas
 * @version $Id: preview.php,v 1.9 2010/07/26 00:07:13 bdaloukas Exp $
 * @package game
 **/
 
    require_once("../../config.php");
    require_once("lib.php");
    require_once("locallib.php");

    require_once( "hangman/play.php");
    require_once( "cross/play.php");
    require_once( "cryptex/play.php");
    require_once( "millionaire/play.php");
    require_once( "sudoku/play.php");
    require_once( "bookquiz/play.php");
	
	//$update = (int )$_GET[ 'update'];
	//$_GET[ 'id'] = $update;
	require_once( "header.php");

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    if (!has_capability('mod/game:viewreports', $context)){
		error( get_string( 'only_teachers', 'game'));
	}
	
    $gamekind  = required_param('gamekind', PARAM_ALPHANUM);
    $update  = required_param('update', PARAM_INT);

    $attemptid = required_param('attemptid',  0, PARAM_INT);
	$attempt = $DB->get_record( 'game_attempts', array('id' => $attemptid));
	$game = $DB->get_record( 'game', array( 'id' => $attempt->gameid));
	$detail = $DB->get_record( 'game_'.$gamekind, array( 'id' => $attemptid));
	if( array_key_exists( 'solution', $_GET)){
		$solution = $_GET[ 'solution'];
	}else
	{
		$solution = 0;
	}

    $PAGE->navbar->add(get_string('preview', 'game'));

	switch( $gamekind)
	{
	case 'cross':
		game_cross_play( $update, $game, $attempt, $detail, '', true, $solution, false, false, false, false, true);
		break;
	case 'sudoku':
		game_sudoku_play( $update, $game, $attempt, $detail, true, $solution);
		break;
	case 'hangman':
		game_hangman_play( $update, $game, $attempt, $detail, true, $solution);
		break;
	case 'cryptex':
		$crossm = $DB->get_record( 'game_cross', array('id' => $attemptid));
		game_cryptex_play( $update, $game, $attempt, $detail, $crossm, false, true, $solution);
		break;
	}

    echo $OUTPUT->footer();
