<?php  // $Id: showattempts.php,v 1.2 2010/07/26 00:41:16 bdaloukas Exp $
/**
 * This page shows the answers of the current game
 * 
 * @author  bdaloukas
 * @version $Id: showattempts.php,v 1.2 2010/07/26 00:41:16 bdaloukas Exp $
 * @package game
 **/
 
    require_once("../../config.php");
    require_once( "header.php");

    if (!has_capability('mod/game:viewreports', $context)){
		error( get_string( 'only_teachers', 'game'));
	}

    $PAGE->navbar->add(get_string('showattempts', 'game'));

    $action  = optional_param('action', "", PARAM_ALPHANUM);  // action
    if( $action == 'delete'){
        game_ondeleteattempt( $game);
    }
    
    game_showattempts( $game);

    echo $OUTPUT->footer();

    function game_showattempts($game){
        global $CFG, $DB;

        $gamekind = $game->gamekind;
        $update = get_coursemodule_from_instance( 'game', $game->id, $game->course)->id;

        //Here are user attempts
        $table = "{game_attempts} as ga, {user} u, {game} as g";
        $select = "ga.userid=u.id AND ga.gameid={$game->id} AND g.id={$game->id}";
        $fields = "ga.id, u.lastname, u.firstname, ga.attempts,".
          "timestart, timefinish, timelastattempt, score, ga.lastip, ga.lastremotehost";
        $sql = "SELECT COUNT(*) AS c FROM $table WHERE $select";
        $count = $DB->count_records_sql( $sql);
        $limitfrom = 0;
        $maxlines = 20;
        if (array_key_exists( 'limitfrom', $_GET)) {
            $limitfrom = $_GET[ 'limitfrom'];
        }
        $recslimitfrom = $recslimitnum = '';
        if( $count > $maxlines){
            $recslimitfrom = ( $limitfrom ? $limitfrom * $maxlines : '');
            $recslimitnum = $maxlines;

            for($i=0; $i*$maxlines < $count; $i++){
                if( $i == $limitfrom){
                    echo ($i+1).' ';
                }else
                {
                    echo "<A HREF=\"{$CFG->wwwroot}/mod/game/preview.php?action=showattempts&amp;update=$update&amp;q={$game->id}&amp;limitfrom=$i&\">".($i+1)."</a>";
                    echo ' &nbsp;';
                }
            }
            echo "<br>";
        }

        $sql = "SELECT $fields FROM $table WHERE $select ORDER BY timelastattempt DESC,timestart DESC";
        if( ($recs = $DB->get_records_sql( $sql, null, $recslimitfrom, $recslimitnum)) != false){
            echo '<table border="1">';
            echo '<tr><td><b>'.get_string( 'delete').'</td><td><b>'.get_string('user').'</td>';
            echo '<td><b>'.get_string('lastip', 'game').'</b></td>';
            echo '<td><b>'.get_string('timestart', 'game').'</b></td>';
            echo '<td><b>'.get_string('timelastattempt', 'game').'</b></td>';
            echo '<td><b>'.get_string('timefinish', 'game').'</b></td>';
            echo '<td><b>'.get_string('score', 'game').'</b></td>';
            echo '<td><b>'.get_string('attempts', 'game').'</b></td>';
            echo '<td><b>'.get_string('preview', 'game').'</b></td>';
            echo '<td><b>'.get_string('showsolution', 'game').'</b></td>';
            echo "</tr>\r\n";

            foreach( $recs as $rec){
                echo '<tr>';
                echo '<td><center>';
                if( $rec->timefinish == 0){
                    echo "\r\n<a href=\"{$CFG->wwwroot}/mod/game/showattempts.php?attemptid={$rec->id}&amp;q={$game->id}&amp;action=delete\">";
                    echo '<img src="'.$CFG->wwwroot.'/pix/t/delete.gif" alt="'.get_string( 'delete').'" /></a>';
                }
                echo '</center></td>';
                echo '<td><center>'.$rec->firstname. ' '.$rec->lastname.'</center></td>';
                echo '<td><center>'.(strlen( $rec->lastremotehost) > 0 ? $rec->lastremotehost : $rec->lastip).'</center></td>';
                echo '<td><center>'.( $rec->timestart != 0 ? userdate($rec->timestart) : '')."</center></td>\r\n";
                echo '<td><center>'.( $rec->timelastattempt != 0 ? userdate($rec->timelastattempt) : '').'</center></td>';
                echo '<td><center>'.( $rec->timefinish != 0 ? userdate($rec->timefinish) : '').'</center></td>';
                echo '<td><center>'.round($rec->score * 100).'</center></td>';
                echo '<td><center>'.$rec->attempts.'</center></td>';
                echo '<td><center>';
	        	//Preview
	        	if( ($gamekind == 'cross') or ($gamekind == 'sudoku') or ($gamekind == 'hangman') or ($gamekind == 'cryptex')){
	        		echo "\r\n<a href=\"{$CFG->wwwroot}/mod/game/preview.php?action=preview&amp;attemptid={$rec->id}&amp;gamekind=$gamekind";
	        		echo '&amp;update='.$update."&amp;q={$game->id}\">";
                    echo '<img src="'.$CFG->wwwroot.'/pix/t/preview.gif" alt="'.get_string( 'preview', 'game').'" /></a>';
	        	}
                echo '</center></td>';

	    	    //Show solution
                echo '<td><center>';
	    	    if( ($gamekind == 'cross') or ($gamekind == 'sudoku') or ($gamekind == 'hangman') or ($gamekind == 'cryptex') ){
	    		    echo "\r\n<a href=\"{$CFG->wwwroot}/mod/game/preview.php?action=solution&amp;attemptid={$rec->id}&amp;gamekind={$gamekind}&amp;update=$update&amp;solution=1&amp;q={$game->id}\">";
	    		    echo '<img src="'.$CFG->wwwroot.'/pix/t/preview.gif" alt="'.get_string( 'showsolution', 'game').'" /></a>';
    	    	}
                echo '</center></td>';
                echo "</tr>\r\n";
            }
            echo "</table>\r\n";
        }
    }

	function game_ondeleteattempt( $game)
	{
		global $CFG, $DB;

        $attemptid  = required_param('attemptid', PARAM_INT);
		
		$attempt = $DB->get_record( 'game_attempts', array( 'id' => $attemptid));
		$game = $DB->get_record( 'game', array( 'id' => $attempt->gameid));
				
		switch( $game->gamekind)
		{
		case 'bookquiz':
			$DB->delete_records( 'game_bookquiz_chapters', array( 'attemptid' => $attemptid));
			break;
		}
		$DB->delete_records( 'game_queries', array( 'attemptid' => $attemptid));
		$DB->delete_records( 'game_attempts', array( 'id' => $attemptid));
	}

