<?php 

function xmldb_assignment_team_upgrade($oldversion){

	global $DB;

	$dbman = $DB->get_manager();

if ($oldversion < 2011013000){
	//add table 'assignment_team'
	$table1 = new XMLDBTable('assignment_team');
	$table1->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_UNSIGNED,  XMLDB_SEQUENCE, 'assignment');
	$table1->add_field('assignment', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_UNSIGNED,  XMLDB_SEQUENCE, 'id', 'name');
	$table1->add_field('name', XMLDB_TYPE_CHAR, '100', XMLDB_NOTNULL, XMLDB_SEQUENCE, 'assignment', 'membershipopen');
	$table1->add_field('membershipopen', XMLDB_TYPE_INTEGER, '1', XMLDB_NOTNULL, XMLDB_UNSIGNED,  XMLDB_SEQUENCE, 'name', 'timemodified');
	$table1->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_UNSIGNED,  XMLDB_SEQUENCE, 'membershipopen');
	$table1->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	$table1->add_key('assignment', XMLDB_KEY_FOREIGN, array('assignment'));

	 if (!$dbman->table_exists($table1)) {
				$dbman->create_table($table1);
			}
        
	//add table 'assignment_team_student'
	$table2 = new XMLDBTable('assignment_team_student');
	$table2->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_UNSIGNED, XMLDB_SEQUENCE, 'true');
	$table2->add_field('student', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_UNSIGNED, XMLDB_SEQUENCE, 'id', 'team');
	$table2->add_field('team', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_UNSIGNED, XMLDB_SEQUENCE, 'student', 'timemodified');
	$table2->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_UNSIGNED, XMLDB_SEQUENCE, 'team');
	$table2->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	$table2->add_key('student', XMLDB_KEY_FOREIGN, array('student'));
	$table2->add_key('team', XMLDB_KEY_FOREIGN, array('team'));
	$table2->add_index('student-team', XMLDB_INDEX_UNIQUE, array('student', 'team'));

        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }
		
		upgrade_plugin_savepoint(true, 2011013000, 'assignment', 'team');
        

	}
		return true;
}