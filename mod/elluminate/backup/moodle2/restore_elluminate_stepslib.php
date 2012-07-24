<?php

class restore_elluminate_activity_structure_step extends restore_activity_structure_step {
	
	protected function define_structure() {

		$paths = array ();
		$userinfo = $this->get_setting_value('userinfo');

		$paths[] = new restore_path_element('elluminate', '/activity/elluminate');
		$paths[] = new restore_path_element('elluminate_recordings', '/activity/elluminate/recordings/recording');
		$paths[] = new restore_path_element('elluminate_preloads', '/activity/elluminate/preloads/preload');
		if ($userinfo) {
			$paths[] = new restore_path_element('elluminate_attendance', '/activity/elluminate/attendance/attendee');
		}

		// Return the paths wrapped into standard activity structure
		return $this->prepare_activity_structure($paths);
	}

	protected function process_elluminate($data) {
		global $DB;

		$data = (object) $data;
		$oldid = $data->id;
		$data->course = $this->get_courseid();

		// insert the elluminate record
		$newitemid = $DB->insert_record('elluminate', $data);
		// immediately after inserting "activity" record, call this
		$this->apply_activity_instance($newitemid);
	}

	protected function process_elluminate_recordings($data) {
		global $DB;

		$data = (object) $data;
		$oldid = $data->id;
		$data->meetingid = $this->meetingid;
		$newitemid = $DB->insert_record('elluminate_recordings', $data);
	}

	protected function process_elluminate_preloads($data) {
		global $DB;

		$data = (object) $data;
		$oldid = $data->id;

		$data->meetingid = $this->meetingid;
		$newitemid = $DB->insert_record('elluminate_preloads', $data);
	}
	
		protected function process_elluminate_attendance($data) {
		global $DB;

		$data = (object) $data;
		$oldid = $data->id;

		$data->elluminateid = $this->get_new_parentid('elluminate');
		$newitemid = $DB->insert_record('elluminate_attendance', $data);
	}

	protected function after_execute() {
		// Add choice related files, no need to match by itemname (just internally handled context)
		$this->add_related_files('mod_elluminate', 'intro', null);
	}
}

