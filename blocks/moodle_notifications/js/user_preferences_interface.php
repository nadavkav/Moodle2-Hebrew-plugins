function show_user_preferences_panel() {
	$('#moodle_notifications_user_preferences_trigger').hide();
	$('#moodle_notifications_user_preferences').show();
}

function hide_user_preferences_panel() {
	$('#moodle_notifications_user_preferences_trigger').show();
	$('#moodle_notifications_user_preferences').hide();
}

function save_user_preferences() {
	hide_user_preferences_panel();	
	$.ajax({
		type: "POST",
		url: "<?php echo dirname( dirname($_SERVER['PHP_SELF']) ).'/set_user_preferences.php'; ?>",
		data: $('#user_preferences').serialize()
 });
}
