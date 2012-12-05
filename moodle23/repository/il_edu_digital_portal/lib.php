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
 * repository_il_edu_digital_portal is used to search gadol.edu.gov.il in moodle
 *
 * @since 2.3
 * @package    repository
 * @subpackage il_edu_digital_portal
 * @copyright  CC-BY-SA 2.5
 * @author     Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_il_edu_digital_portal extends repository {

    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
        $this->keyword = optional_param('edp_keyword', '', PARAM_RAW);
        //$this->author = optional_param('merlot_author', '', PARAM_RAW);
        //$this->licensekey = trim(get_config('merlot', 'licensekey'));
    }

    /**
     * Display login screen or not
     *
     * @return boolean
     */
    public function check_login() {
        return !empty($this->keyword);
    }

    /**
     * Doesn't support global search
     *
     * @return boolean
     */
    public function global_search() {
        return false;
    }

    /**
     * Look for a link in merlot.org
     * @param string $search_text
     * @return array
     */
    public function search($search_text, $page = 0) {
        $ret  = array();
        $ret['nologin'] = true;
        $ret['list'] = $this->_get_collection($this->keyword, $this->author);
        return $ret;
    }

    /**
     * Get a list of links
     * @return array
     */
    public function get_listing($path = '', $page = '') {
        $ret  = array();
        $ret['nologin'] = true;
        $ret['list'] = $this->_get_collection($this->keyword);
        return $ret;
    }

    private function _get_collection($keyword) {
        global $OUTPUT;
        include_once('simple_html_dom.php');

        // parsing html page: http://simplehtmldom.sourceforge.net/manual.htm
        $list = array();
        $query = 'http://gadol.edu.gov.il/Pages/SearchResults.aspx?FreeText=' . urlencode($keyword) . '&SortBy=EDUOFFICEITEM&SortDir=ASC#firstContent';
        $content = file_get_html($query);

        $i = 0;
        foreach($content->find('div.SearchResultItem') as $resultitem) {
            $stripad = str_pad((int) $i,2,"0",STR_PAD_LEFT);
            $item['title']       = $resultitem->find('h4.ItemTitle', 0)->plaintext;
            $item['description'] = $resultitem->find('span[id=ctl00_MSO_ContentDiv_PlaceHolderBottom_mySearchResults_gvSearchResults_ctl'.$stripad.'_Description]', 0)->plaintext;
            $item['freeuse']     = $resultitem->find('span[id=ctl00_MSO_ContentDiv_PlaceHolderBottom_mySearchResults_gvSearchResults_ctl'.$stripad.'_FreeUseLabel]', 0)->plaintext;
            $item['classes']     = $resultitem->find('span[id=ctl00_MSO_ContentDiv_PlaceHolderBottom_mySearchResults_gvSearchResults_ctl'.$stripad.'_EduClasses]', 0)->plaintext;
            $item['image']       = $resultitem->find('img[id=ctl00_MSO_ContentDiv_PlaceHolderBottom_mySearchResults_gvSearchResults_ctl'.$stripad.'_BigImage]', 0)->src;

            $ok = preg_match("/ID\=(\d{4})/i",$resultitem->find('h4.ItemTitle', 0)->outertext,$matches);
            $item['id'] = substr($matches[0],3,4);
            if ($item['id']) {
                $item['mlink'] = 'http://gadol.edu.gov.il/TuitionMaterials/Lists/List12/DispForm.aspx?ID='.$item['id'];
                $itemcontent = file_get_html($item['mlink']);
                if (!empty($itemcontent )) {
                    $item['summary'] = $itemcontent->find('div[id=summary]', 0)->plaintext;
                    $preprocessedlink = $itemcontent->find('div[id=ctl00_MSO_ContentDiv_PlaceHolderMain_myViewItem_mainFile]', 0)->innertext;
                    $ok = preg_match('/RedirectUrl=(.+)" /',$preprocessedlink,$matches);
                    $item['linktoresource'] = $matches[1];

                }

            }
            $resultitems[] = $item;
            $i++;

            $list[] = array(
                'title'=>(string)$item['title'].' ('.(string)$item['freeuse'].')',
                //'thumbnail'=>$OUTPUT->pix_url('f/unknown-32')->out(false),
                'thumbnail'=>(string)$item['image'],
                'date'=>userdate((int)$item['date']),
                'size'=>'',
                'source'=>(string)$item['linktoresource']
            );

        }

        return $list;
    }

    /**
     * Define a search form
     *
     * @return array
     */
    public function print_login(){
        $ret = array();
        $search = new stdClass();
        $search->type = 'text';
        $search->id   = 'edp_search';
        $search->name = 'edp_keyword';
        $search->label = get_string('search').': ';
//        $author = new stdClass();
//        $author->type = 'text';
//        $author->id   = 'merlog_author';
//        $author->name = 'merlot_author';
//        $author->label = get_string('author', 'search').': ';

        $ret['login'] = array($search);//, $author);
        $ret['login_btn_label'] = get_string('search');
        $ret['login_btn_action'] = 'search';
        return $ret;
    }

    /**
     * Names of the plugin settings
     *
     * @return array
     */
    public static function get_type_option_names() {
        return array('licensekey', 'pluginname');
    }

    /**
     * Add Plugin settings input to Moodle form
     *
     * @param object $mform
     */
    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);
        $licensekey = get_config('il_edu_digital_portal', 'licensekey');
        if (empty($licensekey)) {
            $licensekey = '';
        }
        $strrequired = get_string('required');
        $mform->addElement('text', 'licensekey', get_string('licensekey', 'repository_il_edu_digital_portal'), array('value'=>$licensekey,'size' => '40'));
        //$mform->addRule('licensekey', $strrequired, 'required', null, 'client');
    }

    /**
     * Support external link only
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }
    public function supported_filetypes() {
        return array('link');
    }
}

