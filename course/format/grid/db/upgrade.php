<?php
    
function xmldb_format_grid_upgrade($oldversion = 0) {
    $result = true;
    
    if ($result && $oldversion < 2011041802) {

    /// Define table format_grid_summary to be created
        $table = new XMLDBTable('format_grid_summary');

    /// Adding fields to table format_grid_summary
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('show_summary', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, '0');
        $table->addFieldInfo('course_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table format_grid_summary
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for format_grid_summary
        $result = $result && create_table($table);
    }
    
    return $result;    
}
