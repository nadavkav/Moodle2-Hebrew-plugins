<?php 
//***************************************************	
// User class
//***************************************************	

class User {
/*
	function get_all_users_enrolled_in_the_course($course_id) {
		$context = get_context_instance(CONTEXT_COURSE, $course_id);
		$students = get_users_by_capability($context, 'mod/assignment:submit', 'u.id, u.firstname, u.lastname, u.email, u.mailformat');
		return $students;
	}
*/
	function get_all_users_enrolled_in_the_course( $course_id ) {
		$context = get_context_instance( CONTEXT_COURSE, $course_id );
		$all_users = get_users_by_capability( $context, 'mod/assignment:view', 'u.id, u.firstname, u.lastname, u.email, u.mailformat, u.phone2', 'lastname ASC, firstname DESC' );
		$advanced_users = get_users_by_capability( $context, 'moodle/course:create', 'u.id', 'lastname ASC, firstname DESC' );
		// filter advanced users: administrators
		foreach( $advanced_users as $key => $value ) {
			unset( $all_users[$key] );
		}
		return $all_users;
	}

	function get_professor( $course_id ) {
		$context = get_context_instance( CONTEXT_COURSE, $course_id );
		$users = get_users_by_capability( $context, 'moodle/course:managegroups', 'u.id, u.firstname, u.lastname, u.email, u.mailformat, u.phone2', 'lastname ASC, firstname DESC' );
		$advanced_users = get_users_by_capability( $context, 'moodle/course:create', 'u.id', 'lastname ASC, firstname DESC' );
		foreach( $advanced_users as $key => $value ) {
			unset( $users[$key] );
		}
		return current( $users );
	}


	// this function initializes the global user preferences for the current course
	// a new user is enrolled in the course that uses moodle_notifications block 
	function initialize_preferences( $user_id, $course_id, $notify_by_email, $notify_by_sms ) {
		global $DB;
		$user_preferences = new Object();	
		$user_preferences->user_id = $user_id;
		$user_preferences->course_id = $course_id;
		$user_preferences->notify_by_email = $notify_by_email;
		$user_preferences->notify_by_sms = $notify_by_sms;
		return $DB->insert_record( 'block_moodle_notifications_users', $user_preferences );
	}

	function update_preferences( $user_id, $course_id, $notify_by_email, $notify_by_sms ) {
		global $DB;
		$previous_user_preferences = $this->get_preferences( $user_id, $course_id );
		$user_preferences = new Object();	
		$user_preferences->id = $previous_user_preferences->id;
		$user_preferences->user_id = $user_id;
		$user_preferences->course_id = $course_id;
		$user_preferences->notify_by_email = $notify_by_email;
		$user_preferences->notify_by_sms = $notify_by_sms;
		return $DB->update_record( 'block_moodle_notifications_users', $user_preferences );
	}

	function get_preferences( $user_id, $course_id ) {
		global $DB;
		$user_preferences = $DB->get_records_select( 'block_moodle_notifications_users', "course_id=$course_id and user_id=$user_id" ); 
		if( !empty($user_preferences) && is_array($user_preferences) ) {
			return current( $user_preferences );
		} else {
			return null;
		}
	}

	// purge entries of users that have been deleted
	function collect_garbage(){
		global $CFG, $DB;
		$course_list = "(select id from {$CFG->prefix}course)";
		$deleted_users_list = "(select id from {$CFG->prefix}user where deleted=1)";
		$DB->execute( "delete from {$CFG->prefix}block_moodle_notifications_users where course_id not in $course_list" );
		$DB->execute( "delete from {$CFG->prefix}block_moodle_notifications_users where user_id in $deleted_users_list" );
	}
}
?>
