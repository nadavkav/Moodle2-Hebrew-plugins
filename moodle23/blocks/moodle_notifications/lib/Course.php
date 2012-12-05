<?php 
//***************************************************	
// Course registration management
//***************************************************	
class Course {

	function register( $course_id, $starting_time ) {
		global $DB;
		global $CFG;

		$course=new Object();
		$course->course_id = $course_id;
		$course->last_notification_time = $starting_time;
		$course->notify_by_email = 1;
		$course->notify_by_sms = 1;
		$course->notify_by_rss = 1;
		if( isset($CFG->block_moodle_notifications_frequency) ) {
			$course->notification_frequency = $CFG->block_moodle_notifications_frequency * 3600;
		} else {
			$course->notification_frequency = 12 * 3600;
		}

		if ( isset($CFG->block_moodle_notifications_email_notification_preset) ) {
			$course->email_notification_preset = $CFG->block_moodle_notifications_email_notification_preset;
		} else {
			$course->email_notification_preset = 1;
		}

		if ( isset($CFG->block_moodle_notifications_sms_notification_preset) ) {
			$course->sms_notification_preset = $CFG->block_moodle_notifications_sms_notification_preset;
		} else {
			$course->sms_notification_preset = 1;
		}

		return $DB->insert_record( 'block_moodle_notifications_courses', $course );
	}

	function update_last_notification_time( $course_id, $last_notification_time ) {
		global $DB;

		$course=new Object();
		$course->id = $this->get_registration_id( $course_id );
		$course->course_id = $course_id;
		$course->last_notification_time = $last_notification_time;

		return $DB->update_record( 'block_moodle_notifications_courses', $course );
	}

	function update_course_notification_settings( $course_id, $settings ) {
		global $DB;

		$course=new Object();
		$course->id = $this->get_registration_id( $course_id );
		$course->course_id = $course_id;

		$course->notify_by_email = 0;
		if( isset($settings->notify_by_email) and $settings->notify_by_email == 1 ) { $course->notify_by_email = 1; }

		$course->notify_by_sms = 0;
		if( isset($settings->notify_by_sms) and $settings->notify_by_sms == 1 ) { $course->notify_by_sms = 1; }

		$course->notify_by_rss = 0;
		if( isset($settings->notify_by_rss) and $settings->notify_by_rss == 1 ) { $course->notify_by_rss = 1; }

		//var_dump($settings);
		if( isset($settings->notification_frequency) ) {
			$course->notification_frequency = $settings->notification_frequency % 25 * 3600;
		}

		$course->email_notification_preset = 0;
		if( isset($settings->email_notification_preset) and $settings->email_notification_preset == 1 ) { $course->email_notification_preset = 1; }

		$course->sms_notification_preset = 0;
		if( isset($settings->sms_notification_preset) and $settings->sms_notification_preset == 1 ) { $course->sms_notification_preset = 1; }

		return $DB->update_record('block_moodle_notifications_courses', $course);
	}

	function is_registered( $course_id ) {
		$course_registration = $this->get_registration_id( $course_id ); 
		if( !is_null($course_registration) ) {
			return true;
		} else {
			return false;
		}
	}

	function get_registration_id( $course_id ){
		$course_registration = $this->get_registration($course_id);
		if( is_null($course_registration) ) {
			return null;
		} else {
			return $course_registration->id;
		}
	}
	
	function get_registration( $course_id ){
		global $DB;

		$course_registration = $DB->get_records_select( 'block_moodle_notifications_courses', "course_id=$course_id" ); 
		if( isset($course_registration) and is_array($course_registration) and !empty($course_registration)  ) {
			return current($course_registration);
		} else {
			return null;
		}
	}
	
	function get_last_notification_time( $course_id ) {
		global $DB;
	
		$course_registration = $DB->get_records_select( 'block_moodle_notifications_courses', "course_id=$course_id" ); 
		if( isset($course_registration) and is_array($course_registration)  and !empty($course_registration) ) {
			return current($course_registration)->last_notification_time;
		} else {
			return null;
		}
	}

	function uses_moodle_notifications_block( $course_id ) {
		global $DB, $CFG;

		$id = $DB->get_records_sql( "select instanceid from {$CFG->prefix}context where id in (select parentcontextid from {$CFG->prefix}block_instances where blockname = 'moodle_notifications') and instanceid = $course_id" );
		if( empty($id) ) {
			return false;
		} else {
			return true;
		}
	}


	function get_all_courses_using_moodle_notifications_block() {
		global $DB, $CFG;

		// join block_instances, context and course and extract all courses
		// that are using moodle_notifications block
		return $DB->get_records_sql( " select * from {$CFG->prefix}course where id in 
											( select instanceid from {$CFG->prefix}context where id in 
												( select parentcontextid from {$CFG->prefix}block_instances where blockname = 'moodle_notifications' ) );" );
	}
	
	function get_updated_and_deleted_modules( $course_id ){
		global $DB;

		$last_notification_time = $this->get_last_notification_time( $course_id );
		return $DB->get_records_select( 'log', "course=$course_id and action in ('update', 'delete mod') and time > $last_notification_time", null,'cmid,action' );
	}



	function update_log( $course ){
		global $DB;

		$modinfo =& get_fast_modinfo($course);
		foreach($modinfo->cms as $cms => $module) {
			// skip labels, invisible modules and logged modules
			if( $module->modname == 'label' or $module->visible == 0 or $this->is_module_logged($course->id, $module->id, $module->modname) ) continue;

			$new_record = new Object();
			$new_record->course_id = $course->id;
			$new_record->module_id = $module->id;
			$new_record->name = $module->name;
			$new_record->type = $module->modname;
			$new_record->action = 'added';
			$new_record->status = 'pending';
			// if the resource is not visible than
			// mark it as pending and then notify once it is made visible
			$DB->insert_record( 'block_moodle_notifications_log', $new_record );
		}
		// update records
		$course_updates = $this->get_updated_and_deleted_modules( $course->id );

		// if no course updates available then return 
		if( empty($course_updates) ) return;

		foreach($course_updates as $course_update) {
			$log_row = $this->get_log_entry($course_update->cmid);
			// if $log_row is empty than this module has not been registered
			// it is probably invisible
			if ( empty($log_row) ) {
				continue;
			} else {
				$new_record = new Object();
				$new_record->course_id = $log_row->course_id;
				$new_record->module_id = $log_row->module_id;
				$new_record->type = $log_row->type;
				$new_record->status = 'pending';

				if( $course_update->action == 'update' ){
					$new_record->name = $modinfo->cms[$log_row->module_id]->name;
					$new_record->action = 'updated';
				} else if( $course_update->action == 'delete mod' ) {
					$new_record->name = $log_row->name;
					$new_record->action = 'deleted';
				}

				$DB->insert_record( 'block_moodle_notifications_log', $new_record );
			}
		}
		
	}

	function initialize_log( $course ){
		global $DB;

		$modinfo =& get_fast_modinfo( $course );
		// drop all previous records
		$DB->delete_records( 'block_moodle_notifications_log', array('course_id'=>$course->id)  );
		// add new records
		foreach( $modinfo->cms as $cms => $module ) {
			// filter labels and invisible modules
			if( $module->modname == 'label' or $module->visible == 0 ) { continue; }

			$new_record = new Object();
			$new_record->course_id = $course->id;
			$new_record->module_id = $module->id;
			$new_record->name = $module->name;
			$new_record->type = $module->modname;
			$new_record->action = 'added';
			$new_record->status = 'notified';
			// if the resource is not visible than
			// mark it as pending and then notify once it is made visible
			if( $module->visible == '0' ) { $new_record->status = 'pending'; }
			$DB->insert_record( 'block_moodle_notifications_log', $new_record );
		}
	}

	function is_module_logged( $course_id, $module_id, $type ){
		global $DB;

		$log = $DB->get_records_select( 'block_moodle_notifications_log', "course_id = $course_id AND module_id = $module_id AND type = '$type'", null,'id' );
		if(empty($log)) {
			return false;
		} else {
			return true;
		}
	}

	function log_exists( $course_id ){
		global $DB;

		$log = $DB->get_records_select('block_moodle_notifications_log', "course_id = $course_id", null,'id');
		if(empty($log)) {
			return false;
		} else {
			return true;
		}
	}

	function get_log_entry( $module_id ){
		global $DB;

		$entry = $DB->get_records_select( 'block_moodle_notifications_log', "module_id = $module_id" );
		if ( empty($entry) ) {
			return null;	
		} else {
			return  current( $entry );
		}
	}

	function get_logs( $course_id, $limit ){
		global $DB, $CFG;
		$entries = $DB->get_records_sql( "select * from {$CFG->prefix}block_moodle_notifications_log order by id desc limit $limit" );
		if ( empty($entries) ) {
			return null;	
		} else {
			return $entries;
		}
	}

	function get_recent_activities( $course_id ){
		global $DB, $CFG;

		//block_moodle_notifications_log table plus visible field from course_modules
		$subtable = "( select {$CFG->prefix}block_moodle_notifications_log.*, {$CFG->prefix}course_modules.visible 
						from {$CFG->prefix}block_moodle_notifications_log left join {$CFG->prefix}course_modules 
							on ({$CFG->prefix}block_moodle_notifications_log.module_id = {$CFG->prefix}course_modules.id) ) logs_with_visibility";
		// select all modules that are visible and whose status is pending
		$recent_activities = $DB->get_records_sql( "select * from $subtable where course_id = $course_id and status='pending' and (visible = 1 or visible is null)" );
		//print_r($recent_activities);
		// clear all pending notifications
		if(!empty($recent_activities))
			$DB->execute( "update {$CFG->prefix}block_moodle_notifications_log set status = 'notified' 
								where 
									course_id = $course_id and status='pending' 
									and id in ( select id from $subtable where course_id = $course_id and (visible = 1 or visible is null) )" );
		return $recent_activities;
	}

	function get_course_info( $course_id ) { 
		global $CFG, $DB;

		return current( $DB->get_records_sql("select fullname, summary from {$CFG->prefix}course where id = $course_id") );
	}
	
	// purge entries of courses that have been deleted
	function collect_garbage(){
		global $CFG, $DB;

		$course_list = "(select id from {$CFG->prefix}course)";
		$DB->execute( "delete from {$CFG->prefix}block_moodle_notifications_courses where course_id not in $course_list" );	
		$DB->execute( "delete from {$CFG->prefix}block_moodle_notifications_log where course_id not in $course_list" );	
	}

}
?>
