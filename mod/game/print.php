<?php  // $Id: print.php,v 1.6 2011/03/02 14:32:45 bdaloukas Exp $
/**
 * This page export the game to html
 * 
 * @author  bdaloukas
 * @version $Id: print.php,v 1.6 2011/03/02 14:32:45 bdaloukas Exp $
 * @package game
 **/
    require_once("../../config.php");
    require_once("lib.php");
    require_once("locallib.php");
    
    $id = required_param('id', 0, PARAM_INT); // Course Module ID, or
    $gameid = required_param('gameid', 0, PARAM_INT); 

    $game = $DB->get_record( 'game', array( 'id' => $gameid));
    
    require_login( $game->course);
    
    game_print( $game, $id);
        
    function game_print( $game, $update){
        
        if( $game->gamekind == 'cross'){
            game_print_cross( $game, $update);
        }
    }
    
    function game_print_cross( $game, $update)
    {
        require( "cross/play.php");

        $attempt = game_getattempt( $game, &$crossrec); 
        
        game_cross_play( $update, $game, $attempt, $crossrec, '', true, false, false, true, false, false, false);

    }    

?>
