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
 * PoodLLrecording question renderer class.
 *
 * @package    qtype
 * @subpackage poodllrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/filter/poodll/poodllresourcelib.php');
require_once($CFG->dirroot . '/filter/poodll/poodllfilelib.php');

/**
 * Generates the output for poodllrecording questions.
 *
 * @copyright  Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
        $responseoutput = $question->get_format_renderer($this->page);

        // Answer field.
        $step = $qa->get_last_step_with_qt_var('answer');
        if (empty($options->readonly)) {
            $answer = $responseoutput->response_area_input('answer', $qa,
                    $step, 1, $options->context);

        } else {
            $answer = $responseoutput->response_area_read_only('answer', $qa,
                    $step, 1, $options->context);
        }

		
        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => 'answer'));
        $result .= html_writer::end_tag('div');

        return $result;
    }


  
    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
                $question->graderinfo, $question->graderinfo, $qa, 'qtype_poodllrecording',
                'graderinfo', $question->id), array('class' => 'graderinfo'));
    }
}


/**
 * A base class to abstract out the differences between different type of
 * response format.
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_poodllrecording_format_renderer_base extends plugin_renderer_base {
    /**
     * Render the students response when the question is in read-only mode.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response.
     */
    public abstract function response_area_read_only($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * Render the students input area: ie show a recorder
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response for editing.
     */
    public abstract function response_area_input($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * @return string specific class name to add to the input element.
     */
    protected abstract function class_name();
}


/**
 * An poodllrecording format renderer for poodllrecordings for audio
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_audio_renderer extends plugin_renderer_base {
   

    protected function class_name() {
        return 'qtype_poodllrecording_audio';
    }

	//This is not necessary, but when testing it can be handy to display this
	protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_essay_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
	}

    
    
       protected function prepare_response_for_editing($name,
            question_attempt_step $step, $context) {
        return $step->prepare_response_files_draft_itemid_with_text(
                $name, $context->id, $step->get_qt_var($name));
                
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {	
    		global $CFG;
   			//fetch file from storage and figure out URL
			$pathtofile="";
    		$storedfiles=$qa->get_last_qt_files($name,$context->id);
    		foreach ($storedfiles as $sf){
    			$pathtofile=$qa->get_response_file_url($sf);
    			break;
    		}
			
			//$pathtofile= $this->prepare_response($name, $qa, $step, $context);
			//return "path:" . $pathtofile ;
			//return "path:" . $pathtofile . "<br />" . fetchSimpleAudioPlayer('swf',$pathtofile,"http",400,25);
			if($pathtofile!=""){
				 $files = fetchSimpleAudioPlayer('swf',$pathtofile,"http",400,25);
			}else{
				$files = "No recording found";
			}
			return $files;
    }


    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER;
    	$usercontextid=get_context_instance(CONTEXT_USER, $USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);
		
		//prepare the tags for our hidden( or shown ) input
		$inputname = $qa->get_qt_field_name($name);
		//$inputname="answer";
		$inputid =  $inputname . '_id';
		
		//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 'name' => $inputname));
		//this is just for testing purposes so we can see the value the recorder is writing
		//$ret = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,'id'=>$inputid));
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		//our answerformat
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => 1));
	
	
		//the context id $context->id here is wrong, so we just use "5" because it works, why is it wrong ..? J 20120214
		return $ret . fetchAudioRecorderForSubmission('swf','question',$inputid, $usercontextid ,'user','draft',$draftitemid);
		return $ret;
    }
}

/**
 * An poodllrecording format renderer for poodllrecordings for video
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_video_renderer extends qtype_poodllrecording_format_audio_renderer {
    

    protected function class_name() {
        return 'qtype_poodllrecording_video';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
				
			//fetch file from storage and figure out URL
    		$storedfiles=$qa->get_last_qt_files($name,$context->id);
    		foreach ($storedfiles as $sf){
    			$pathtofile=$qa->get_response_file_url($sf);
    			break;
    		}

			return fetchSimpleVideoPlayer('swf',$pathtofile,400,380,"http");
	
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER;
    	$usercontextid=get_context_instance(CONTEXT_USER, $USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);


		$inputname = $qa->get_qt_field_name($name);
		$inputid =  $inputname . '_id';
		
			//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 'name' => $inputname));
		//$ret = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,'id'=>$inputid));
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => FORMAT_PLAIN));

       
		//the context id $context->id here is wrong, so we just use "5" because it works, why is it wrong ..? J 20120214
		return $ret . fetchVideoRecorderForSubmission('swf','question',$inputid, $usercontextid ,'user','draft',$draftitemid);
		return $ret;
		
    }
}

/**
 * An poodllrecording format renderer for poodllrecordings for picture *Not implemented yet Justin 20120214*
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_picture_renderer extends qtype_poodllrecording_format_audio_renderer {
    /**
     * @return string the HTML for the textarea.
     */
    protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_poodllrecording_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
    }

    protected function class_name() {
        return 'qtype_poodllrecording_picture';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return $this->textarea($step->get_qt_var($name), $lines, array('readonly' => 'readonly'));
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        $inputname = $qa->get_qt_field_name($name);
        return $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname)) .
                html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));
    }
}
