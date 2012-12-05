<?php // $Id$

/**
 * repository_poodll
 * Moodle user can record/play poodll audio/video items
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
 
//Get our poodll resource handling lib
require_once($CFG->dirroot . '/filter/poodll/poodllresourcelib.php');
 
class repository_poodll extends repository {
    /*
     * Begin of File picker API implementation
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        global $CFG, $PAGE;
        
        parent::__construct ($repositoryid, $context, $options);
     
    }
    
    public static function get_instance_option_names() {
    	return array('recording_format');
    }
    
    //2.3 requires static, 2.2 non static, what to do? Justin 20120616 
    // created a 2.3 repo Justin 20120621
    public static function instance_config_form($mform) {
        $recording_format_options = array(
        	get_string('audio', 'repository_poodll'),
        	get_string('video', 'repository_poodll'),
			get_string('snapshot', 'repository_poodll'),
			get_string('mp3recorder', 'repository_poodll')
			
        );
        
        $mform->addElement('select', 'recording_format', get_string('recording_format', 'repository_poodll'), $recording_format_options);
        
        $mform->addRule('recording_format', get_string('required'), 'required', null, 'client');
		
    }
	
	


	//login overrride start
	//*****************************************************************
	//
     // Generate search form
     //
    public function print_login($ajax = true) {
		global $CFG,$PAGE,$USER;

		
		//Init our array
        $ret = array();
		
		//if we are using Paul Nichols MP3 repository or Snap shot we diverge here
		$injectwidget= "";
		switch ($this->options['recording_format']){
			//MP3 Recorder
			case 3000: $injectwidget=$this->fetch_mp3recorder();
					
					//add for 2.3 compatibility Justin 20120622
					 $ret = array('nosearch'=>true, 'norefresh'=>true);
					 
					$ret['upload'] = array('label'=>$injectwidget, 'id'=>'repo-form');
					return $ret;
					break;
			//snapshot case 2
			//possibly drawpad case 4
			case 2000: 
				$iframe = "<input type=\"hidden\"  name=\"upload_filename\" id=\"upload_filename\" value=\"sausages.mp3\"/>";
                $iframe = "<textarea name=\"upload_filedata\" id=\"upload_filedata\" style=\"display:none;\"></textarea>";
				$iframe .= "<div style=\"position:absolute; top:0;left:0;right:0;bottom:0; background-color:#fff;\">";
				$iframe .= "<iframe scrolling=\"no\" frameBorder=\"0\" src=\"{$CFG->wwwroot}/repository/poodll/recorder.php?repo_id={$this->id}\" height=\"350\" width=\"450\"></iframe>"; 
				$iframe .= "</div>";
				$ret['upload'] = array('label'=>$iframe, 'id'=>'repo-form');
				return $ret;
				break;
			default:
				//just fall through to rest of code
		
		}
		
		
		
		//If we are using an iframe based repo
        $search = new stdClass();
        $search->type = 'hidden';
        $search->id   = 'filename';
        $search->name = 's';
       // $search->value = 'winkle.mp3';
        
        //change next button and iframe proportions depending on recorder
        switch($this->options['recording_format']){
        	//video,snapshot
        	case 1: 
			case 2: 	
					$height=350;
					$width=330;
					$button = "<button class=\"fp-login-submit\" style=\"position:relative; top:-200px;\" >Next >>></button>";
					break;
			//audio		
			case 0:
			case 3:
					$height=220;
					$width=450;
					$button = "";
					break;
        }
		$search->label = "<iframe scrolling=\"no\" frameBorder=\"0\" src=\"{$CFG->wwwroot}/repository/poodll/recorder.php?repo_id={$this->id}\" height=\"". $height ."\" width=\"" . $width . "\"></iframe>" . $button; 

		$sort = new stdClass();
        $sort->type = 'hidden';
        $sort->options = array();
        $sort->id = 'poodll_sort';
        $sort->name = 'poodll_sort';
        $sort->label = '';

        $ret['login'] = array($search, $sort);
        $ret['login_btn_label'] = 'Next >>>';
        $ret['login_btn_action'] = 'search';
	
        return $ret;

    }
    

	public function check_login() {
        return !empty($this->keyword);
    }

		
	  
     // Method to get the repository content.
     //
     // @param string $path current path in the repository
     // @param string $page current page in the repository path
     // @return array structure of listing information
     //
    public function get_listing($path='', $page='') {
			return  array();
		
   }
   
   ///
     // Return search results
     // @param string $search_text
     // @return array
     //
     //added $page=0 param for 2.3 compat justin 20120524
    public function search($filename, $page=0) {
        $this->keyword = $filename;
        $ret  = array();
        $ret['nologin'] = true;
		$ret['nosearch'] = true;
        $ret['norefresh'] = true;
        //echo $filename;
		$ret['list'] = $this->fetch_file($filename);
		
        return $ret;
    }
	
	    /**
     * Private method to fetch details on our recorded file
     * @param string $keyword
     * @param int $start
     * @param int $max max results
     * @param string $sort
     * @return array
     */
    private function fetch_file($filename) {
		global $CFG;
	
        $list = array();
		
		//if user did not record anything, or the recording copy failed can out sadly.
		if(!$filename){return $list;}
		
		switch($this->options['recording_format']){
			case 0:
			case 1:
				//set up auto transcoding (mp3) or not
				//The jsp to call is different.
				$jsp="download.jsp";
				$ext = substr($filename,-4); 
				if($ext ==".mp4" || $ext ==".mp3"){
					$jsp = "convert.jsp";
				}
						
				$source="http://" . $CFG->filter_poodll_servername . 
						":" . $CFG->filter_poodll_serverhttpport . "/poodll/" . $jsp. "?poodllserverid=" . 
						$CFG->filter_poodll_serverid . "&filename=" . $filename . "&caller=" . urlencode($CFG->wwwroot);
				break;
			
			//this is the download script for snapshots and direct uploads
			//the upload script is the same file, called from widget directly. Callback posted filename back to form
			case 2:
			case 3:
				$source=$CFG->wwwroot . '/repository/poodll/uploadHandler.php?filename=' . $filename;
				break;
		
		}
        
						

            $list[] = array(
                'title'=>$filename,
                'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/bigicon.png",
                'thumbnail_width'=>330,
                'thumbnail_height'=>115,
                'size'=>'',
                'date'=>'',
                'source'=>$source
            );
       
        return $list;
    }
	

	  /**
     * 
     * @return string
     */
    public function supported_returntypes() {
        return FILE_INTERNAL;
    }
	


    /**
     * Returns the suported returns values.
     * 
     * @return string supported return value
     */
    public function supported_return_value() {
        return 'ref_id';
    }

    /**
     * Returns the suported file types
     *
     * @return array of supported file types and extensions.
     */
    public function supported_filetypes() {
		
		switch($this->options['recording_format']){
			case 0:
			case 1:
				$ret= array('.flv');
				break;
			case 2:
				$ret = array('.jpg');
				break;
			case 3:
				$ret = array('.mp3');
				break;
		}
		return $ret;
    }
	
	   /*
     * Fetch the recorder widget
     */
    public function fetch_recorder() {
        global $USER,$CFG;
     //   $usercontextid = get_context_instance(CONTEXT_USER, $USER->id)->id;
	//	$draftitemid=0;
	//	$ret = '<form name="poodll_repository" action="' . $CFG->wwwroot . '/repository/poodll/recorder.php">';
		switch($this->options['recording_format']){
			case 0:
				$ret = fetchSimpleAudioRecorder('swf','poodllrepository',$USER->id,'filename');
				break;
			case 1:
				$ret = fetchSimpleVideoRecorder('swf','poodllrepository',$USER->id,'filename','','298', '340');
				break;
			case 3:
				//this is the mp3 recorder, but we should not enter this function anyway in that case
				$ret = $this->fetchMP3PostRecorder("filename","apic.jpg", '290','340');
				break;
			case 2:
				$ret = fetchSnapshotCamera("filename","apic.jpg", '290','340');
				break;
		}
		echo $ret;

	}
	
	
	//=====================================================================================
	//Start of  Paul Nichols MP3 Recorder
	//====================================================================================
	
	
	public function fetchMP3PostRecorder($param1,$param2,$param3,$param4){
		 global $CFG;
		// return fetch_mp3recorder();
       //initialize our return string
	   $recorder = "";
	 //  $filename ="pp.mp3";
       
	   //set up params for mp3 recorder
	   $url=$CFG->wwwroot.'/filter/poodll/flash/mp3recorder.swf?gateway=' . $CFG->wwwroot . '/repository/poodll/uploadHandler.php'; // /recorder=mp3/filename=' . $filename;//?filename=' . $filename;
		//$callback = urlencode("(function(a, b){d=parent.document;d.g=d.getElementById;fn=d.g('filename');fn.value=a;fd=d.g('upload_filedata');fd.value=b;f=fn;while(f.tagName!='FORM')f=f.parentNode;f.repo_upload_file.type='text';f.repo_upload_file.value='bogus.mp3';while(f.tagName!='DIV')f=f.nextSibling;f.getElementsByTagName('button')[0].click();})");
		 // $flashvars="&callback={$callback}&forcename=winkle";
		$flashvars="&filename=newaudio";
		  
		  
		//make our insert string
        $recorder = '<div style="position:absolute; top:0;left:0;right:0;bottom:0; background-color:#fff;">
                <input type="hidden"  name="upload_filename" id="upload_filename" value="sausages.mp3"/>
                <textarea name="upload_filedata" id="upload_filedata" style="display:none;"></textarea>

                <div id="onlineaudiorecordersection" style="margin:20% auto; text-align:center;">
                    <object id="onlineaudiorecorder" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="215" height="138">
                        <param name="movie" value="'.$url.$flashvars.'" />
                        <param name="wmode" value="transparent" />
                        <!--[if !IE]>-->
                        <object type="application/x-shockwave-flash" data="'.$url.$flashvars.'" width="215" height="138">
                        <!--<![endif]-->
                        <div>
                                <p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
                        </div>
                        <!--[if !IE]>-->
                        </object>
                        <!--<![endif]-->
                    </object>
                </div>
            </div>';
			
			//return the recorder string
			return $recorder;
	
	}
	

	
	//=====================================================================================
	//End of  Paul Nichols MP3 Recorder
	//====================================================================================
	
}