<?php  // $Id: showanswers.php,v 1.5 2011/07/29 22:23:50 bdaloukas Exp $
/**
 * This page shows the answers of the current game
 * 
 * @author  bdaloukas
 * @version $Id: showanswers.php,v 1.5 2011/07/29 22:23:50 bdaloukas Exp $
 * @package game
 **/
 
    require_once("../../config.php");
    require_once( "header.php");

    if (!has_capability('mod/game:viewreports', $context)){
		error( get_string( 'only_teachers', 'game'));
	}

    $PAGE->navbar->add(get_string('showanswers', 'game'));

    $action  = optional_param('action', "", PARAM_ALPHANUM);  // action
    if( $action == 'delstats')
        $DB->delete_records('game_repetitions', array('gameid' => $game->id, 'userid' => $USER->id));
    if( $action == 'computestats')
        game_compute_repetitions($game);

    echo '<b>'.get_string('repetitions', 'game').': &nbsp;&nbsp;</b>';
    echo get_string('user').': ';
    game_showusers($game);
    echo " &nbsp;<a href=\"$CFG->wwwroot/mod/game/showanswers.php?q=$q&action=delstats\">".get_string('clearrepetitions','game').'</a>';
    echo " &nbsp;&nbsp;<a href=\"$CFG->wwwroot/mod/game/showanswers.php?q=$q&action=computestats\">".get_string('computerepetitions','game').'</a>';
    echo '<br><br>';

    $existsbook = ($DB->get_record( 'modules', array( 'name' => 'book'), 'id,id'));
    game_showanswers( $game, $existsbook);

    echo $OUTPUT->footer();

function game_compute_repetitions($game){
    global $DB, $USER;

    $DB->delete_records('game_repetitions', array('gameid' => $game->id,'userid' => $USER->id));

    $sql = "INSERT INTO {game_repetitions}( gameid,userid,questionid,glossaryentryid,repetitions) ".
           "SELECT $game->id,$USER->id,questionid,glossaryentryid,COUNT(*) ".
           "FROM {game_queries} WHERE gameid=$game->id AND userid=$USER->id GROUP BY questionid,glossaryentryid";

    if( !$DB->execute( $sql))
        print_error('Problem on computing statistics for repetitions');
}

function game_showusers($game)
{
    global $CFG, $USER;

    $users = array();

    $context = get_context_instance(CONTEXT_COURSE, $game->course);

    if ($courseusers = get_enrolled_users($context)) {
        foreach ($courseusers as $courseuser) {
            $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
        }
    }
    if ($guest = guest_user()) {
        $users[$guest->id] = fullname($guest);
    }
    ?>
        <script type="text/javascript">
            function onselectuser()
            {
                window.location.href = "<?php echo $CFG->wwwroot.'/mod/game/showanswers.php?q='.$game->id.'&userid=';?>" + document.getElementById('menuuser').value;
            }
        </script>
    <?php

    $attributes = 'onchange="javascript:onselectuser();"';
    $name = 'user';
    $id = 'menu'.$name;
    $class = 'menu'.$name;
    $class = 'select ' . $class; /// Add 'select' selector always
    $nothing = get_string("allparticipants");
    $nothingvalue='0';
    $options = $users;
    $selected = optional_param('userid',$USER->id,PARAM_INT);

    $output = '<select id="'. $id .'" class="'. $class .'" name="'. $name .'" '. $attributes .'>' . "\n";
    $output .= '   <option value="'. s($nothingvalue) .'"'. "\n";
    if ($nothingvalue === $selected) {
        $output .= ' selected="selected"';
    }
    $output .= '>'. $nothing .'</option>' . "\n";

    if (!empty($options)) {
        foreach ($options as $value => $label) {
            $output .= '   <option value="'. s($value) .'"';
            if ((string)$value == (string)$selected ||
                    (is_array($selected) && in_array($value, $selected))) {
                $output .= ' selected="selected"';
            }
            if ($label === '') {
                $output .= '>'. $value .'</option>' . "\n";
            } else {
                $output .= '>'. $label .'</option>' . "\n";
            }
        }
    }
    echo $output . '</select>' . "\n";
}

function game_showanswers( $game, $existsbook)
{
    if( $game->gamekind == 'bookquiz' and $existsbook){
        game_showanswers_bookquiz( $game);
        return;
    }
    
    switch( $game->sourcemodule){
    case 'question':
        game_showanswers_question( $game);
        break;
    case 'glossary':
        game_showanswers_glossary( $game);
        break;
    case 'quiz':
        game_showanswers_quiz( $game);
        break;
    }
}

function game_showanswers_appendselect( $game)
{
    switch( $game->gamekind){
    case 'hangman':
    case 'cross':
    case 'crypto':
        return " AND qtype='shortanswer'";
    case 'millionaire':
        return " AND qtype = 'multichoice'";
    case 'sudoku':
    case 'bookquiz':
    case 'snakes':
        return " AND qtype in ('shortanswer', 'truefalse', 'multichoice')";
    }
    
    return '';
}

function game_showanswers_question( $game)
{
    global $DB;

    if( $game->gamekind != 'bookquiz'){
        $select = ' category='.$game->questioncategoryid;

        if( $game->subcategories){
            $cats = question_categorylist( $game->questioncategoryid);
            if( strpos( $cats, ',') > 0){
                $select = ' category in ('.$cats.')';
            }
        }
    }else
    {
        $context = get_context_instance(50, $COURSE->id);
        $select = " contextid in ($context->id)";
        $select2 = '';
        if( $recs = $DB->get_records_select( 'question_categories', $select, null, 'id,id')){
            foreach( $recs as $rec){
                $select2 .= ','.$rec->id;
            }
        }
        $select = ' AND category IN ('.substr( $select2, 1).')';
    }
    
    $select .= ' AND hidden = 0 ';
    $select .= game_showanswers_appendselect( $game);
    
    $showcategories = ($game->gamekind == 'bookquiz');
    $order = ($showcategories ? 'category,questiontext' : 'questiontext');
    game_showanswers_question_select( $game, '{question} q', $select, '*', $order, $showcategories, $game->course);
}


function game_showanswers_quiz( $game)
{
    global $CFG;

	$select = "quiz='$game->quizid' ".
			  ' AND qqi.question=q.id'.
			  ' AND q.hidden=0'.
              showanswers_appendselect( $game);
	$table = '{question} q,{quiz_question_instances} qqi';
	
    game_showanswers_question_select( $game, $table, $select, 'q.*', 'category,questiontext', false, $game->course);
}


function game_showanswers_question_select( $game, $table, $select, $fields='*', $order='questiontext', $showcategoryname=false, $courseid=0)
{
    global $CFG, $DB;

    $sql = "SELECT $fields FROM $table WHERE $select ORDER BY $order";
    if( ($questions = $DB->get_records_sql( $sql)) === false){
        return;
    }

    $table .= ",{game_repetitions} gr";
    $select .= " AND gr.questionid=q.id AND gr.glossaryentryid=0 AND gr.gameid=".$game->id;
    $userid = optional_param('userid',0,PARAM_INT);
    if( $userid)
        $select .= " AND gr.userid=$userid";
    $sql = "SELECT q.id as id,SUM(repetitions) as c FROM {$table} WHERE $select GROUP BY q.id";
    $reps = $DB->get_records_sql( $sql);
	
	$categorynames = array();
	if( $showcategoryname){
	    $select = '';
    	$recs = $DB->get_records( 'question_categories', null, '', '*', 0, 1);
	    foreach( $recs as $rec){
	    	if( array_key_exists( 'course', $rec)){
	    		$select = "course=$courseid";
	    	}else{
	    		$context = get_context_instance(50, $courseid);
	        		$select = " contextid in ($context->id)";
	    	}
	    	break;
    	}

		if( ($categories = $DB->get_records_select( 'question_categories', $select, null, '', 'id,name'))){
			foreach( $categories as $rec){
				$categorynames[ $rec->id] = $rec->name;
			}
		}
	}
    
    echo '<table border="1">';
    echo '<tr><td></td>';
	if( $showcategoryname){
		echo '<td><b>'.get_string( 'categories', 'quiz').'</b></td>';
	}
    echo '<td><b>'.get_string( 'questions', 'quiz').'</b></td>';
    echo '<td><b>'.get_string( 'answers', 'quiz').'</b></td>';
    echo '<td><b>'.get_string( 'feedbacks', 'game').'</b></td>';
    if( $reps)
        echo '<td><b>'.get_string( 'repetitions', 'game').'</b></td>';
    echo "</tr>\r\n";
    $line = 0;
    foreach( $questions as $question){
        echo '<tr>';
        echo '<td>'.(++$line);
        echo '</td>';

		if( $showcategoryname){
			echo '<td>';
			if( array_key_exists( $question->category, $categorynames)){
				echo $categorynames[ $question->category];
			}else{
				echo '&nbsp;';
			}
			echo '</td>';
		}

        echo '<td>';
        echo "<a title=\"Edit\" href=\"$CFG->wwwroot/question/question.php?inpopup=1&amp;id=$question->id&courseid=$courseid\"  target=\"_blank\"><img src=\"$CFG->wwwroot/pix/t/edit.gif\" alt=\"Edit\" /></a> ";
        echo $question->questiontext.'</td>';
        
        switch( $question->qtype){
        case 'shortanswer':
	        $recs = $DB->get_records( 'question_answers', array( 'question' => $question->id), 'fraction DESC', 'id,answer,feedback');
	        if( $recs == false){
	            $rec = false;
	        }else{
	            foreach( $recs as $rec)
	                break;
	        }
	        echo "<td>$rec->answer</td>";
	        if( $rec->feedback == '')
	            $rec->feedback = '&nbsp;';
	        echo "<td>$rec->feedback</td>";
            break;
        case 'multichoice':
        case 'truefalse':
            $recs = $DB->get_records( 'question_answers', array( 'question' => $question->id));
            $feedback = '';
            echo '<td>';
            $i = 0;
            foreach( $recs as $rec){
                if( $i++ > 0)
                    echo '<br>';
		        if( $rec->fraction == 1){
			        echo " <b>$rec->answer</b>";
	                if( $rec->feedback == '')
	                    $feedback .= '<br>';
	                else
                        $feedback .= "<b>$rec->feedback</b><br>";
			        
                }else
                {
			        echo " $rec->answer";
	                if( $rec->feedback == '')
	                    $feedback .= '<br>';
	                else
                        $feedback .= "<br>";
                }
            }
            echo '</td>';
	        if( $feedback == '')
	            $feedback = '&nbsp;';
	        echo "<td>$feedback</td>";
            break;
        default:
            echo "<td>$question->qtype</td>";
            break;
        }

        //Show repetitions
        if( $reps){
            if( array_key_exists( $question->id, $reps)){
                $rep = $reps[ $question->id];
                echo '<td><center>'.$rep->c.'</td>';
            }else
                echo '<td>&nbsp;</td>';
        }

        echo "</tr>\r\n";
    }
    echo "</table><br>\r\n\r\n";
}

function game_showanswers_glossary( $game)
{
    global $CFG, $DB;
    
	$table = '{glossary_entries} ge';
    $select = "glossaryid={$game->glossaryid}";
    if( $game->glossarycategoryid){
		$select .= " AND gec.entryid = ge.id ".
					    " AND gec.categoryid = {$game->glossarycategoryid}";
		$table .= ",{glossary_entries_categories} gec";		
	}
    $sql = "SELECT id,definition,concept FROM $table WHERE $select ORDER BY definition";
    if( ($questions = $DB->get_records_sql( $sql)) === false){
        return;
    }

    //Show repetiotions of questions
    $table = "{glossary_entries} ge, {game_repetitions} gr";
    $select = "glossaryid={$game->glossaryid} AND gr.glossaryentryid=ge.id AND gr.gameid=".$game->id;
    $userid = optional_param('userid',0,PARAM_INT);
    if( $userid)
        $select .= " AND gr.userid=$userid";
        if( $game->glossarycategoryid){
	    $select .= " AND gec.entryid = ge.id ".
		           " AND gec.categoryid = {$game->glossarycategoryid}";
        $table .= ",{glossary_entries_categories} gec";
    }
    $sql = "SELECT ge.id,SUM(repetitions) as c FROM {$table} WHERE $select GROUP BY ge.id";
    $reps = $DB->get_records_sql( $sql);
    
    echo '<table border="1">';
    echo '<tr><td></td>';
    echo '<td><b>'.get_string( 'questions', 'quiz').'</b></td>';
    echo '<td><b>'.get_string( 'answers', 'quiz').'</b></td>';
    if( $reps != false)
        echo '<td><b>'.get_string( 'repetitions', 'game').'</b></td>';
    echo "</tr>\r\n";
    $line = 0;
    foreach( $questions as $question){
        if( $game->param7 == 0){        //Not allowed spaces
            if(!( strpos( $question->concept, ' ') === false))
                continue;
        }
        if( $game->param8 == 0){        //Not allowed -
            if(!( strpos( $question->concept, '-') === false))
                continue;
        }
    
        echo '<tr>';
        echo '<td>'.(++$line);
        echo '</td>';
        
        echo '<td>'.$question->definition.'</td>';
        echo '<td>'.$question->concept.'</td>';
        if( $reps != false){
            if( array_key_exists( $question->id, $reps))
            {
                $rep = $reps[ $question->id];
                echo '<td><center>'.$rep->c.'</td>';
            }else
                echo '<td>&nbsp;</td>';
        }
        echo "</tr>\r\n";
    }
    echo "</table><br>\r\n\r\n";
}

function game_showanswers_bookquiz( $game)
{
    global $CFG;
    
	$select = "gbq.questioncategoryid=q.category ".
			  " AND gbq.gameid = $game->id".
			  " AND bc.id = gbq.chapterid";
	$table = "{question} q,{game_bookquiz_questions} gbq,{book_chapters} bc";
	
    game_showanswers_question_select( $game, $table, $select, "DISTINCT q.*", "bc.pagenum,questiontext");
}
