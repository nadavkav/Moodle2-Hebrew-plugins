<?php 
// define constants
define( 'ROOT', (realpath(dirname( __FILE__ )) . DIRECTORY_SEPARATOR) );
define( 'LIB_DIR', ROOT . "lib" . DIRECTORY_SEPARATOR );
// include moodle config file
require_once realpath( ROOT . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config.php" );
?>
