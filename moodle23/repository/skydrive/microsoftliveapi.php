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
 * A helper class to access microsoft live resources using the api frm
 * http://msdn.microsoft.com/en-us/library/hh243648.aspx
 *
 * @package    repository
 * @subpackage skydrive
 * @copyright  2012 Lancaster University Network Services Ltd
 * @author     Dan Poltawski <dan.poltawski@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/oauthlib.php');

class microsoft_skydrive extends oauth2_client {
    const SCOPE = 'wl.skydrive';
    const API = 'https://apis.live.net/v5.0';

    public function __construct($clientid, $clientsecret, $returnurl) {
        parent::__construct($clientid, $clientsecret, $returnurl, self::SCOPE);
    }

    protected function use_http_get() {
        return true;
    }

    protected function auth_url() {
        return 'https://oauth.live.com/authorize';
    }

    protected function token_url() {
        return 'https://oauth.live.com/token';
    }

    public function download_file($id, $path) {
        $url = self::API."/${id}/content";
        // Microsoft live redirects to the real download location..
        $this->setopt(array('CURLOPT_FOLLOWLOCATION' => true, 'CURLOPT_MAXREDIRS' => 3));
        $content = $this->get($url);
        file_put_contents($path, $content);
        return array('path'=>$path, 'url'=>$url);
    }

    /**
     * Returns a list of files the user has formated for files api
     *
     * @param string $path the path which we are in
     * @return mixed Array of files formated for fileapoi
     */
    public function get_file_list($path = '') {
        global $OUTPUT;

        if (empty($path)) {
            $url = self::API."/me/skydrive/files/";
        } else {
            $url = self::API."/{$path}/files/";
        }

        $ret = json_decode($this->get($url));

        if (isset($ret->error)) {
            $this->log_out();
            return false;
        }

        $files = array();

        foreach ($ret->data as $file) {
            switch($file->type) {
                case 'folder':
                    $files[] = array(
                        'title' => $file->name,
                        'path' => $file->id,
                        'size' => 0,
                        'date' => strtotime($file->updated_time),
                        'thumbnail' => $OUTPUT->pix_url(file_folder_icon(90))->out(false),
                        'children' => array(),
                    );
                    break;
                case 'photo':
                    $files[] = array(
                        'title' => $file->name,
                        'size' => $file->size,
                        'date' => strtotime($file->updated_time),
                        'thumbnail' => $file->picture,
                        'source' => $file->id,
                        'url' => $file->link,
                    );
                    break;
                case 'video':
                    $files[] = array(
                        'title' => $file->name,
                        'size' => $file->size,
                        'date' => strtotime($file->updated_time),
                        'thumbnail' => $file->picture,
                        'source' => $file->id,
                        'url' => $file->link,
                    );
                    break;
                case 'audio':
                    $files[] = array(
                        'title' => $file->name,
                        'size' => $file->size,
                        'date' => strtotime($file->updated_time),
                        'thumbnail' => $OUTPUT->pix_url(file_extension_icon($file->name, 90))->out(false),
                        'source' => $file->id,
                        'url' => $file->link,
                    );
                    break;
                case 'file':
                    $files[] = array(
                        'title' => $file->name,
                        'size' => $file->size,
                        'date' => strtotime($file->updated_time),
                        'thumbnail' => $OUTPUT->pix_url(file_extension_icon($file->name, 90))->out(false),
                        'source' => $file->id,
                        'url' => $file->link,
                    );
                    break;
            }
        }
        return $files;
    }
}
