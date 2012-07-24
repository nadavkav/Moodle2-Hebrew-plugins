<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Microsoft Live Skydrive Repository Plugin
 *
 * @package    repository
 * @subpackage skydrive
 * @copyright  2012 Lancaster University Network Services Ltd
 * @author     Dan Poltawski <dan.poltawski@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('microsoftliveapi.php');

class repository_skydrive extends repository {
    private $skydrive = null;

    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);

        $clientid = get_config('skydrive', 'clientid');
        $secret = get_config('skydrive', 'secret');
        $returnurl = new moodle_url('/repository/repository_callback.php');
        $returnurl->param('callback', 'yes');
        $returnurl->param('repo_id', $this->id);
        $returnurl->param('sesskey', sesskey());

        $this->skydrive = new microsoft_skydrive($clientid, $secret, $returnurl);
        $this->check_login();
    }

    public function check_login() {
        return $this->skydrive->is_logged_in();
    }

    public function print_login($ajax = true) {
        $popup = new stdClass();
        $popup->type = 'popup';
        $url = $this->skydrive->get_login_url();
        $popup->url = $url->out(false);
        return array('login' => array($popup));
    }

    public function get_listing($path='', $page = '') {
        $ret = array();
        $ret['dynload'] = true;
        $ret['nosearch'] = true;
        $ret['list'] = $this->skydrive->get_file_list($path);
        return $ret;
    }

    public function get_file($url, $filename = '') {
        $path = $this->prepare_file($filename);
        return $this->skydrive->download_file($url, $path);
    }

    public static function get_type_option_names() {
        return array('clientid', 'secret', 'pluginname');
    }

    public static function type_config_form($mform, $classname = 'repository') {
        $a = new stdClass;
        $a->callbackurl = google_oauth::callback_url()->out(false);
        $mform->addElement('static', null, '', get_string('oauthinfo', 'repository_skydrive', $a));

        parent::type_config_form($mform);
        $strrequired = get_string('required');
        $mform->addElement('text', 'clientid', get_string('clientid', 'repository_skydrive'));
        $mform->addElement('text', 'secret', get_string('secret', 'repository_skydrive'));
        $mform->addRule('clientid', $strrequired, 'required', null, 'client');
        $mform->addRule('secret', $strrequired, 'required', null, 'client');
    }

    public function logout() {
        $this->skydrive->log_out();
        return $this->print_login();
    }

    public function global_search() {
        return false;
    }

    public function supported_filetypes() {
        return '*';
    }

    public function supported_returntypes() {
        return FILE_INTERNAL;
    }
}
