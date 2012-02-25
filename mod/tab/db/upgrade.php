<?php  //$Id: upgrade.php,v 1.1.8.1 2008/05/01 20:51:20 skodak Exp $

// This file keeps track of upgrades to 
// the label module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php
defined('MOODLE_INTERNAL') || die;

function xmldb_tab_upgrade($oldversion=0) {

  global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager();

	if ($oldversion < 2010120501) {
           //I changed the menu css. So let's upgrade the code
           $sql = "UPDATE {tab} SET menucss = ?";
           $params = array('
		#tab-menu-wrapper {
                    float: left;
                    width: 20%;

		}

                #tabcontent {
                    margin-left:  20%;
                    padding: 0 10px;

		}

		.menutable {
			border: 1px solid #808080;
		}
		.menutitle {
			background:#2647a0 url(../../lib/yui/2.8.1/build/assets/skins/sam/sprite.png) repeat-x left -1400px;
			color:#fff;
		}
		.row {
			background-color: #CFCFCF;
		}
		');

            $DB->execute($sql,$params);
		
		// tab savepoint reached
        upgrade_mod_savepoint(true, 2010120501,'tab');
	}

        if ($oldversion < 2010120900) {
           //This version empplies that the view.php file has been modified
           //No modificsations to the DB have been done


            // tab savepoint reached
            upgrade_mod_savepoint(true, 2010120900,'tab');
	}
	
	    if ($oldversion < 2010120901) {

        // Define field css to be dropped from tab
        $table = new xmldb_table('tab');
        $field = new xmldb_field('css');
		$field2 = new xmldb_field('menucss');

        // Conditionally launch drop field css
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
		// Conditionally launch drop field css
        if ($dbman->field_exists($table, $field2)) {
            $dbman->drop_field($table, $field2);
        }
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2010120901, 'tab');
    }

    if ($oldversion < 2011040200) {

        // Define field pdffile to be added to tab_content
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('pdffile', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'tabcontentorder');
        $field2 = new xmldb_field('urlembed', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'pdffile');

        // Conditionally launch add field pdffile
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011040200, 'tab');
    }

        if ($oldversion < 2011040201) {

        // Rename field externalurl on table tab_content to NEWNAMEGOESHERE
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('urlembed', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'pdffile');

        // Launch rename field externalurl
        $dbman->rename_field($table, $field, 'externalurl');

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011040201, 'tab');
    }

    if ($oldversion < 2011041300) {

        // Rename field externalurl on table tab_content to NEWNAMEGOESHERE
        //Changes where done in the view.php file

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011041300, 'tab');
    }

        if ($oldversion < 2011071100) {

        // Define field id to be dropped from tab_content
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('pdffile');

        // Conditionally launch drop field id
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011071100, 'tab');
    }
        if ($oldversion < 2011071101) {

        // Changing nullability of field tabcontentorder on table tab_content to null
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('tabcontentorder', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, '1', 'tabcontent');

        // Launch change of nullability for field tabcontentorder
        $dbman->change_field_notnull($table, $field);

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011071101, 'tab');
    }
    if ($oldversion < 2011080800) {

        // Fixed two undefined variables

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011080800, 'tab');
    }
    if ($oldversion < 2011081000) {

        // Added PDF embedding

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011081000, 'tab');
    }
	if ($oldversion < 2011081400) {

       	//Fixed course context error 

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011081400, 'tab');
    }
    	if ($oldversion < 2012021400) {

       	//Fixed PDF embedding 

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2012021400, 'tab');
    }


}

?>
