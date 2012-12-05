<?php

//  BRIGHTALLY CUSTOM CODE
//  Coder: Ted vd Brink
//  Contact: ted.vandenbrink@brightalley.nl
//  Date: 6 juni 2012
//
//  Description: Enrols users into a course by allowing a user to upload an csv file with only email adresses
//  Using this block allows you to use CSV files with only emailaddress
//  After running the upload you can download a txt file that contains a log of the enrolled and failed users.

//  License: GNU General Public License http://www.gnu.org/copyleft/gpl.html

defined('MOODLE_INTERNAL') || die();

class block_csv_enrol_renderer extends plugin_renderer_base {

    /**
     * Prints private files tree view
     * @return string
     */
    public function csv_enrol_tree($context) {
        return $this->render(new csv_enrol_tree($context));
    }

    public function render_csv_enrol_tree(csv_enrol_tree $tree) {
        $module = array('name'=>'block_csv_enrol', 'fullpath'=>'/blocks/csv_enrol/module.js', 'requires'=>array('yui2-treeview'));
        if (empty($tree->dir['subdirs']) && empty($tree->dir['files'])) {
            $html = $this->output->box(get_string('nofilesavailable', 'repository'));
        } else {
            $htmlid = 'csv_enrol_tree_'.uniqid();
            $this->page->requires->js_init_call('M.block_csv_enrol.init_tree', array(false, $htmlid));
            $html = '<div id="'.$htmlid.'">';
            $html .= $this->htmllize_tree($tree, $tree->dir);
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG;
        $yuiconfig = array();
        $yuiconfig['type'] = 'html';

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon("f/folder", $subdir['dirname'], 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.s($subdir['dirname']).'</div> '.$this->htmllize_tree($tree, $subdir).'</li>';
        }
        foreach ($dir['files'] as $file) {
            $url = file_encode_url("$CFG->wwwroot/blocks/csv_enrol/getfile.php", '/'.$tree->context->id.'/user/csvenrol'.$file->get_filepath().$file->get_filename(), true);
            $filename = $file->get_filename();
	    $icon = mimeinfo("icon", $filename);
	    if(strlen($filename)>10) {
		$pi = pathinfo($filename);
		$txt = $pi['filename'];
		$ext = $pi['extension'];
		$filename = substr($filename,0,14).'...'.$ext;
	    }
            $image = $this->output->pix_icon("f/$icon", $filename, 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.html_writer::link($url, $image.'&nbsp;'.$filename).'</div></li>';
        }
        $result .= '</ul>';

        return $result;
    }
}

class csv_enrol_tree implements renderable {
    public $context;
    public $dir;
    public function __construct($context) {
        global $USER;
        $this->context = $context;
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'user', 'csvenrol', 0);
    }
}
