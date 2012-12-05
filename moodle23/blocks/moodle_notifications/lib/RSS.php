<?php 
include_once realpath(dirname( __FILE__ ).DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR."common.php";
include_once LIB_DIR."Course.php";
include_once LIB_DIR."User.php";

class RSS {
	function __construct( $course_id ){
		global $CFG, $DB;	
		
		$Course = new Course();
		// if the course is not registered or
		// the course is registered but the block is not active
		//if( !$Course->is_registered($course_id) or !$Course->uses_moodle_notifications_block($course_id) ) {
		if( !$Course->is_registered($course_id) or !$Course->uses_moodle_notifications_block($course_id) ) {
			echo "RSS on this course is not enabled.";
			return;
		}
		$User = new User();
		$teacher = $User->get_professor( $course_id );
		// if no teacher then add a dummy mail address
		if( empty($teacher) ) {
			$teacher->email = "noteacher@inthiscourse.org";
		}

		$course_info = $Course->get_course_info( $course_id );
		//var_dump($course_info); exit;
		$course_registration = $Course->get_registration( $course_id );

		//print_r("here");
		if ( $course_registration->notify_by_rss != 1 ) return;
		// here
		$now = date("D, d M Y H:i:s T");
		$output = "<?xml version=\"1.0\"?>
					<rss version=\"2.0\">
					<channel>
					<title>$course_info->fullname</title>
					<link>$CFG->wwwroot/course/view.php?id=$course_id</link>
					<description>$course_info->summary</description>
					<language>en-us</language>
					<pubDate>$now</pubDate>
					<lastBuildDate>$now</lastBuildDate>
					<docs>$CFG->wwwroot/course/view.php?id=$course_id</docs>
					<managingEditor>$teacher->email</managingEditor>
					<webMaster>helpdesk@elearninglab.org</webMaster>";


		// get the last 20 entries form the block logs

		$logs = $Course->get_logs( $course_id, 20 );

		foreach( $logs as $log ) {
			$output .= "<item>";
			$output .= '<title>'.get_string($log->type, 'block_moodle_notifications').'</title>';
			if($log->action == 'deleted')
				$output .= "<link></link>";
			else
				$output .= "<link>$CFG->wwwroot/mod/$log->type/view.php?id=$log->module_id</link>";

			$output .= "<description>";
			switch( $log->action ) {
				case 'added':	
					$output .= get_string('added', 'block_moodle_notifications').' ';
					break;
				case 'updated':	
					$output .= get_string('updated', 'block_moodle_notifications').' ';
					break;
				case 'deleted':	
					$output .= get_string('deleted', 'block_moodle_notifications').' ';
					break;
			}
			$output .= get_string( $log->type, 'block_moodle_notifications' ).': ';
			$output .= $log->name;
			$output .= "</description>";
			$output .= "</item>";
		}
		$output .= "</channel></rss>";
		header("Content-Type: application/rss+xml");
		echo $output;
	}
}

$course_id = intval( $_GET['id'] );
// if course id not valid exit

if( empty($course_id) ) {
	print_r("Invalid id.");
	exit;
}

$rss = new RSS( $course_id );
/*
*/
?>
