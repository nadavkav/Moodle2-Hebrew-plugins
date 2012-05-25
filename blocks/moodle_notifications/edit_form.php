<?php
class block_moodle_notifications_edit_form extends block_edit_form {
    protected function specific_definition( $mform ) {
		global $CFG;
		global $COURSE;

		$Course = new Course();
		$course_notification_setting = $Course->get_registration( $COURSE->id );
        // Fields for editing HTML block title and contents.
		$mform->addElement( 'header', 'configheader', get_string( 'blocksettings', 'block' ) );

		$attributes = array();
		$attributes['disabled'] = 'disabled';
		$attributes['group'] = 'moodle_notifications_settings';

		if( $CFG->block_moodle_notifications_email_channel == 1 ) {
        	$mform->addElement( 'checkbox', 'notify_by_email', get_string('notify_by_email', 'block_moodle_notifications') );
		} else {
        	$mform->addElement( 'advcheckbox', 'notify_by_email', get_string('notify_by_email', 'block_moodle_notifications'), null, $attributes );
		}

		if ( isset($course_notification_setting->notify_by_email) and $course_notification_setting->notify_by_email == 1 ) {
        	$mform->setDefault( 'notify_by_email', 1 );
		}

		if( $CFG->block_moodle_notifications_sms_channel == 1 and class_exists('SMS') ) {
        	$mform->addElement( 'checkbox', 'notify_by_sms', get_string('notify_by_sms', 'block_moodle_notifications') );
		} else {
        	$mform->addElement( 'advcheckbox', 'notify_by_sms', get_string('notify_by_sms', 'block_moodle_notifications'), null, $attributes );
		}

		if ( isset($course_notification_setting->notify_by_sms) and $course_notification_setting->notify_by_sms == 1 ) {
        	$mform->setDefault( 'notify_by_sms', 1 );
		}

		if( $CFG->block_moodle_notifications_rss_channel == 1 ) {
        	$mform->addElement( 'checkbox', 'notify_by_rss', get_string('notify_by_rss', 'block_moodle_notifications') );
		} else {
        	$mform->addElement( 'advcheckbox', 'notify_by_rss', get_string('notify_by_rss', 'block_moodle_notifications'), null, $attributes );
		}

		if ( isset($course_notification_setting->notify_by_rss) and $course_notification_setting->notify_by_rss == 1 ) {
        	$mform->setDefault( 'notify_by_rss', 1 );
		}

		if( 
			$CFG->block_moodle_notifications_email_channel == 1 or 
			$CFG->block_moodle_notifications_sms_channel == 1
		) {
	 		$options = array();
			for( $i=1; $i<25; ++$i ) {
				$options[$i] = $i;
			}
        	$mform->addElement( 'select', 'notification_frequency', get_string('notification_frequency', 'block_moodle_notifications'), $options );
        	$mform->setDefault( 'notification_frequency', $course_notification_setting->notification_frequency/3600 );
		}


        $mform->addElement( 'html', '<br /><div class="qheader">'.get_string('course_configuration_presets_comment', 'block_moodle_notifications').'</div>' );

        $mform->addElement( 'checkbox', 'email_notification_preset', get_string('email_notification_preset', 'block_moodle_notifications') );
		if ( isset($course_notification_setting->email_notification_preset) and $course_notification_setting->email_notification_preset == 1 ) {
        	$mform->setDefault( 'email_notification_preset', 1 );
		} else {
        	$mform->setDefault( 'email_notification_preset', 0 );
		}

        $mform->addElement( 'checkbox', 'sms_notification_preset', get_string('sms_notification_preset', 'block_moodle_notifications') );
		if ( isset($course_notification_setting->sms_notification_preset) and $course_notification_setting->sms_notification_preset == 1 ) {
        	$mform->setDefault( 'sms_notification_preset', 1 );
		} else {
        	$mform->setDefault( 'sms_notification_preset', 0 );
		}
    }

    function set_data( $defaults ) {
		$block_config = new Object();
		$block_config->notify_by_email = file_get_submitted_draft_itemid( 'notify_by_email' );
		$block_config->notify_by_sms = file_get_submitted_draft_itemid( 'notify_by_sms' );
		$block_config->notify_by_rss = file_get_submitted_draft_itemid( 'notify_by_rss' );
		$block_config->notification_frequency = file_get_submitted_draft_itemid( 'notification_frequency' );
		$block_config->email_notification_preset = file_get_submitted_draft_itemid( 'email_notification_preset' );
		$block_config->sms_notification_preset = file_get_submitted_draft_itemid( 'sms_notification_preset' );
        unset( $this->block->config->text );
		parent::set_data( $defaults );
        $this->block->config = $block_config;
	}
}

?>
