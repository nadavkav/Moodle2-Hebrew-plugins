<?php 
include_once realpath( dirname( __FILE__ ).DIRECTORY_SEPARATOR ).DIRECTORY_SEPARATOR."common.php";
include_once LIB_DIR.DIRECTORY_SEPARATOR."User.php";
// print_r($_POST);exit;
// check email preference
$notify_by_email = 0;
if( isset($_POST['notify_by_email']) and $_POST['notify_by_email'] == 1 ) {
	$notify_by_email = 1;
}
// check sms preference
$notify_by_sms = 0;
if( isset($_POST['notify_by_sms']) and $_POST['notify_by_sms'] == 1 ) {
	$notify_by_sms = 1;
}
// check user id
$user_id;
if( isset($_POST['user_id']) ) {
	$user_id = $_POST['user_id'];
} else {
	exit;
}

// check course id
$course_id;
if( isset($_POST['course_id']) ) {
	$course_id = $_POST['course_id'];
} else {
	exit;
}

$User = new User();
$User->update_preferences( $user_id, $course_id, $notify_by_email, $notify_by_sms );
?>
