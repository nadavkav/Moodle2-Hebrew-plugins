<?php 
//***************************************************	
// Mail notification
//***************************************************	
class eMail {

	function notify( $changelist, $user, $course ){
		$html_message = $this->html_mail( $changelist, $course );
		$text_message = $this->text_mail( $changelist, $course );
		$subject = get_string('mailsubject', 'block_moodle_notifications');
		$subject.= ": ".format_string( $course->fullname, true );
		email_to_user( $user,'', $subject, $text_message, $html_message );
	}


	function html_mail( $changelist, $course ) {
		global $CFG;

		$mailbody = '<head>';

		$mailbody .= '</head>';
		$mailbody .= '<body id="email">';
		$mailbody .= '<div class="header">';
		$mailbody .= get_string('mailsubject', 'block_moodle_notifications').' ';
		$mailbody .= "&laquo; <a target=\"_blank\" href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->fullname</a> &raquo; ";
		$mailbody .= '</div>';
		$mailbody .= '<div class="content">';
		$mailbody .= '<ul>';

		foreach ( $changelist as $change ) {
			$mailbody .='<li>';
			$mailbody .= get_string( $change->action, 'block_moodle_notifications' ).' ';
			$mailbody .= get_string( $change->type, 'block_moodle_notifications' )." : ";
			if ( $change->action != "deleted") {
				$mailbody .="<a href=\"$CFG->wwwroot/mod/$change->type/view.php?id=$change->module_id\">$change->name</a>";
			}
			$mailbody .= '</li>';
		}

		$mailbody .= '</ul>';
		$mailbody .= '</div>';
		$mailbody .= '</body>';

		return $mailbody;
	}
	 
	function text_mail( $changelist, $course ) {
		global $CFG;

		$mailbody = get_string( 'mailsubject', 'block_moodle_notifications' ).': '.$course->fullname.' ';
		$mailbody .= $CFG->wwwroot.'/course/view.php?id='.$course->id."\r\n\r\n";

		foreach ( $changelist as $change ) {
			$mailbody .= "\t".get_string( $change->action, 'block_moodle_notifications' ).' ';
			$mailbody .= "\t".get_string( $change->type, 'block_moodle_notifications' )." : ";
			$mailbody .= $change->name."\r\n";

			if ( $change->action != "deleted") {
				$mailbody .= "\t$CFG->wwwroot/mod/$change->type/view.php?id=$change->module_id\r\n\r\n";
			}
		}
		/*
		print_r("\n");
		print_r("\n");
		print_r("\n");
		print_r($mailbody);
		*/
		return $mailbody;
	}
}
?>
