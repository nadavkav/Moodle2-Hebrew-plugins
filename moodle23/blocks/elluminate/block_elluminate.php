<?php // $Id: block_elluminate.php,v 1.1.2.2 2009/03/18 16:45:57 mchurch Exp $

/**
 * Blackboard Collaborate block.
 *
 * Allows students to manage their user information on the Blackboard Collaborate
 * server from Moodle and admins/teachers to add students and other users
 * to a remote Blackboard Collaborate server.
 *
 * @version $Id: block_elluminate.php,v 1.1.2.2 2009/03/18 16:45:57 mchurch Exp $
 * @author Justin Filip <jfilip@oktech.ca>
 * @author Remote Learner - http://www.remote-learner.net/
 */


class block_elluminate extends block_base {

	function init() {
		$this->title   = get_string('elluminate', 'block_elluminate');
	}

	function get_content() {
		global $CFG, $USER, $DB, $OUTPUT;
		

		require_once($CFG->dirroot . '/mod/elluminate/lib.php');

		if($this->content !== NULL) {
			return $this->content;
		}
		$this->content        = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';

		if (!isloggedin() || empty($this->instance)) {
			return $this->content;
		}

		if ($recordings = elluminate_recent_recordings()) {
			$this->content->items[] = '<b>' . get_string('recentrecordings', 'block_elluminate') . ':</b>';
			$this->content->icons[] = '';
			$baseurl = new moodle_url('/mod/elluminate/view.php');
			foreach ($recordings as $recording) {
				$elluminate = $DB->get_record('elluminate', array('meetingid'=>$recording->meetingid));				
				$url = new moodle_url($baseurl);
                $url->params(array('a'=>$elluminate->id, 'group'=>$elluminate->groupid));
                $icon = '<img src="'.$OUTPUT->pix_url('icon', 'feedback') . '" class="icon" alt="" />&nbsp;';
                $this->content->text = ' <a href="'.$url->out().'">'.$recording->name.'</a>';
			}
		}

		return $this->content;
	}

}

?>
