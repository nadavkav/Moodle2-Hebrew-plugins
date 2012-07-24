<?php // $Id: upgrade.php,v 1.6 2009-06-05 20:12:38 jfilip Exp $

/**
 * Database upgrade code.
 *
 * @version $Id: upgrade.php,v 1.6 2009-06-05 20:12:38 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.ca>
 * @author Remote Learner - http://www.remote-learner.net/
 */

    function xmldb_elluminate_upgrade($oldversion = 0) {
        global $CFG, $THEME, $DB;
        
        $dbman = $DB->get_manager(); /// loads ddl manager and xmldb classes

         //  We don't need this anymore. As per Moodle.org to upgrade to 2.0 having 1.9 is a requirement, so checks on old versions can be removed for this version (Moodle Integration 2.0.2)
        //	which is a compatibility release for Moodle 2.2.2.
        ///if ($oldversion < 2006062102) {
        /// This should not be necessary but it's included just in case.
        //    $result = install_from_xmldb_file($CFG->dirroot . '/mod/elluminate/db/install.xml');
        //}

        if ($result && $oldversion < 2009090801) {                                              
            //updates to the elluminate table
            $elluminate_table = new xmldb_table('elluminate');
	        $field = new xmldb_field('meetinginit');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'meetingid');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('groupmode');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'meetinginit');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
			$field = new xmldb_field('groupid');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'groupmode');
            $result = $result && $dbman->add_field($elluminate_table, $field);                
            
            $field = new xmldb_field('groupparentid');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'groupid');
            $result = $result && $dbman->add_field($elluminate_table, $field);        
            
            $field = new xmldb_field('sessionname');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, false, '0', 'groupparentid');
            $result = $result && $dbman->add_field($elluminate_table, $field);        
            
            $field = new xmldb_field('customname');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, false, '0', 'sessionname');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('customdescription');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, false, '0', 'customname');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('timestart');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'customdescription');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('timeend');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'timestart');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('recordingmode');
            $field->set_attributes(XMLDB_TYPE_CHAR, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'timeend');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('boundarytime');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'recordingmode');
            $result = $result && $dbman->add_field($elluminate_table, $field);
	        
	        $field = new xmldb_field('boundarytimedisplay');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'boundarytime');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('chairlist');
            $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, false, null, 'boundarytimedisplay');
            $result = $result && $dbman->add_field($elluminate_table, $field);
            
            $field = new xmldb_field('nonchairlist');
            $field->set_attributes(XMLDB_TYPE_TEXT, 'big', XMLDB_UNSIGNED, null, false, null, 'chairlist');
            $result = $result && $dbman->add_field($elluminate_table, $field);                      
	        
	        //Updates to the recordings table
	        $recordings_table = new xmldb_table('elluminate_recordings');	       
			$field = new xmldb_field('description');
            $field->set_attributes(XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, null, false, '0', 'recordingid');
            $result = $result && $dbman->add_field($recordings_table, $field);
            
            $field = new xmldb_field('visible');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'description');
            $result = $result && $dbman->add_field($recordings_table, $field);
            
            $field = new xmldb_field('groupvisible');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'visible');
            $result = $result && $dbman->add_field($recordings_table, $field);    
            	        	        
	        $table = new xmldb_table('elluminate_session');
	        if ($dbman->table_exists($table)) {
	            $status = $dbman->drop_table($table, true, false);
	        }
	        
	        $table = new xmldb_table('elluminate_users');
	        if ($dbman->table_exists($table)) {
	            $status = $dbman->drop_table($table, true, false);
	        }   

			$table = new xmldb_table('elluminate_preloads');
	        if ($dbman->table_exists($table)) {
	            $status = $dbman->drop_table($table, true, false);
	        }
	        
			install_from_xmldb_file($CFG->dirroot . '/mod/elluminate/db/upgrade.xml');         
            
            $meetings = $DB->get_records('elluminate');
                        
            /// Modify all of the existing meetings, if any.
            if ($result && !empty($meetings)) {
                $timenow = time();
				
                foreach ($meetings as $meeting) {
                /// Update the meeting by storing values from the ELM server in the local DB.
                    if (!$elmmeeting = elluminate_get_meeting_full_response($meeting->meetingid)) {
                        continue;
                    }					
					
                    $meeting->meetinginit = 2;
                    $meeting->groupmode = 0;
                    $meeting->groupid = 0;
                    $meeting->groupparentid = 0;                    
                    $meeting->sessionname = addslashes($meeting->name);
                    $meeting->timestart   = substr($elmmeeting->startTime, 0, -3);
                    $meeting->timeend     = substr($elmmeeting->endTime, 0, -3);
                    $meeting->chairlist     = $elmmeeting->chairList;
                    $meeting->nonchairlist  = $elmmeeting->nonChairList;                    
					$meeting->recordingmode = $elmmeeting->recordingModeType;					
					$meeting->boundarytime = $elmmeeting->boundaryTime;
					$meeting->boundarytimedisplay = 1;
					$meeting->customname = 0;
					$meeting->customdescription = 0;					
								                  
                    $DB->update_record('elluminate', $meeting);                                                                             
                }                      
        	}
        	                                            
	        $recordings = $DB->get_records('elluminate_recordings');    
	        if (!empty($recordings)) {		
	            foreach ($recordings as $recording) {               
	            	$urecording = new stdClass;
	            	$recording->description = '';
	                $recording->visible = '1';
	                $recording->groupvisible = '0';
					$DB->update_record('elluminate_recordings', $urecording);
	            }
	        }
	        upgrade_mod_savepoint(true, 2009090801, 'elluminate');
        }

		if ($oldversion < 2010062500) {
			
			/*
			 * This is put in place to account for Elluminate Sessions that were created using
			 * the 1.0 and 1.1 bridge which do not contain group sessions, however if the course
			 * has either seperate or visible groups set as it's default the 1.6 adapter will attempt
			 * to convert it to a group session which is bad.  We have to force the
			 * group mode of the course_module to be zero which means no groups.
			 */
			if($oldversion <= 2009020501) {
				$module = $DB->get_record('modules', array('name'=>'elluminate'));    
    			$course_modules = $DB->get_records('course_modules', array('module' => $module->id));

				foreach ($course_modules as $course_module) {
					$course_module->groupmode = 0;
					$DB->update_record('course_modules',$course_module);
				}
				upgrade_mod_savepoint(true, 2009020501, 'elluminate');
			}
						
			$table = new xmldb_table('elluminate');
			
			$field = new xmldb_field('sessiontype');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'creator');
            $result = $result && $dbman->add_field($table, $field);
            
            $field = new xmldb_field('groupingid');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'sessiontype');
            $result = $result && $dbman->add_field($table, $field);
            
            $meetings = $DB->get_records('elluminate');
            
            foreach ($meetings as $meeting) {
            	$meeting->groupingid = 0;            	
            	if($meeting->private == true) {
            		$meeting->sessiontype = 1;
            	}            	
            	if($meeting->groupmode > 0) {
            		$meeting->sessiontype = 2;
            	}   
            	
            	$DB->update_record('elluminate', $meeting);      		
            }
            
            $field = new xmldb_field('private');
            $dbman->drop_field($table, $field);         
            
            $recordings_table = new xmldb_table('elluminate_recordings');
			$size_field = new xmldb_field('recordingsize');
            $size_field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, false, '0', 'description');
            $result = $result && $dbman->add_field($recordings_table, $size_field);               
            
			$recordings = $DB->get_records('elluminate_recordings');    		    
		    foreach($recordings as $recording) {
		    	$full_recordings = elluminate_list_recordings($recording->meetingid);
		    	foreach($full_recordings as $full_recording) {
		    		if($full_recording->recordingid == $recording->recordingid) {
		    			$recording->recordingsize = $full_recording->size;
		    			$DB->update_record('elluminate_recordings', $recording);	
		    		}
		    	}
		    }
		    upgrade_mod_savepoint(true, 2010062500, 'elluminate');
		}
		
		if ($result && $oldversion < 2012050211) {
			$table = new xmldb_table('elluminate');
			$max_talkers_field = new xmldb_field('maxtalkers');
			$max_talkers_field -> set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'boundarytimedisplay');
			if (!$dbman->field_exists($table, $max_talkers_field)) {
				$dbman->add_field($table, $max_talkers_field);
			}

			upgrade_mod_savepoint(true, 2012050211, 'elluminate');
		}
        return true;
    }

