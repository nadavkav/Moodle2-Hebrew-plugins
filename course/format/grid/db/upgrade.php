<?php

function xmldb_format_grid_upgrade($oldversion = 0) {

    global $DB;
    $dbman = $DB->get_manager();
    $result = true;

    if ($result && $oldversion < 2011041802) {

    /// Define table course_grid_summary to be created
        $table = new XMLDBTable('course_grid_summary');

    /// Adding fields to table course_grid_summary
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('show_summary', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, '0');
        $table->addFieldInfo('course_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table course_grid_summary
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for course_grid_summary
        $result = $result && create_table($table);

        upgrade_plugin_savepoint(true, '2011041802', 'format', 'grid');
    }

    if ($result && $oldversion < 2012011701) {
        // Rename the tables
        if ($dbman->table_exists('course_grid_icon')) {
            $table = new XMLDBTable('course_grid_icon');
            if (!$dbman->table_exists('format_grid_icon')) {
                $dbman->rename_table($table, 'format_grid_icon');
            } else {
                // May as well tidy up the db
                $dbman->drop_table($table);
            }
        }

        if ($dbman->table_exists('course_grid_summary')) {
            $table = new XMLDBTable('course_grid_summary');
            if (!$dbman->table_exists('format_grid_summary')) {
                $dbman->rename_table($table, 'format_grid_summary');
            } else {
                // May as well tidy up the db
                $dbman->drop_table($table);
            }
        }

        upgrade_plugin_savepoint(true, '2012011701', 'format', 'grid');
    }

    return $result;
}
