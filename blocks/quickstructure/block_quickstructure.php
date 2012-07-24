 <?php

/**
 * Rapidly structure a course with title strips, thumbnails and a menu
 *
 * @package    block_quickstructure
 * @copyright  2011 Iain Checkland <igc@kgv.hk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_quickstructure extends block_base {

    function init() { 
       $this->title = 'QuickStructure';
       $this->version = 2007101509;
    }

    function instance_allow_config() {
        return false;
    }
    
    function instance_config($instance) {
        global $DB;
        parent::instance_config($instance);
        $course = $DB->get_record('course', array('id'=>$this->instance->pageid));
        if (isset($course->format)) {
            if ($course->format == 'topics') {
                $this->title = get_string('topics', 'block_quickstructure');
            } else if ($course->format == 'weeks') {
                $this->title = get_string('weeks', 'block_quickstructure');
            } else {
                $this->title = get_string('blockname', 'block_quickstructure');
            }
        }
    }

    function applicable_formats() {
        return (array('course-view-weeks' => true, 'course-view-topics' => true, 'course-edit-weeks' => true, 'course-edit-topics' => true));
    }

    function get_content() {
        require_once("structurelib.php");
        global $PAGE, $DB, $CFG, $USER, $COURSE;
        
        $highlight = 0;

        if ($this->content !== NULL) {
            return $this->content;
        }
        
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text   = '';

        if (empty($this->instance)) {
            return $this->content;
        }
        
        $course = $this->page->course;
        
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
     
        $usemenu = get_config('blocks/quickstructure',"usemenu_{$course->id}");
        if($usemenu===false){ $usemenu=1; }
        if(!$numthumbs = get_config('blocks/quickstructure',"numthumbs_{$course->id}")){
             $numthumbs=6;
        }
        if(!$thumbwidth = get_config('blocks/quickstructure',"thumbwidth_{$course->id}")){
             $thumbwidth=100;
        }        
        $usethumbs = get_config('blocks/quickstructure',"usethumbs_{$course->id}");
        if($usethumbs===false){ $usethumbs=1; }        
        
        $thumbeffects = get_config('blocks/quickstructure',"thumbeffects_{$course->id}");
        if($thumbeffects===false){ $thumbeffects=1; }        
        
        $uselabels = get_config('blocks/quickstructure',"uselabels_{$course->id}");
        if($uselabels===false){ $uselabels=1; }
        
        $menucolour = get_config('blocks/quickstructure',"menucolour_{$course->id}");
        if($menucolour===false){ $menucolour=''; }
        
        if ($course->format == 'weeks' or $course->format == 'weekscss') {
            $highlight = ceil((time()-$course->startdate)/604800);
            $linktext = get_string('jumptocurrentweek', 'block_quickstructure');
            $sectionname = 'week';
        }
        else if ($course->format == 'topics') {
            $highlight = $course->marker;
            $linktext = get_string('jumptocurrenttopic', 'block_quickstructure');
            $sectionname = 'topic';
        }


        if (!empty($USER->id)) {
            $display = $DB->get_field('course_display', 'display', array('course'=>$course->id, 'userid'=>$USER->id));
        }
        if (!empty($display)) {
            $link = $CFG->wwwroot.'/course/view.php?id='.$course->id.'&amp;'.$sectionname.'=';
        } else {
            $link = '#section-';
        }

        $sql = "SELECT section, visible, summary
                  FROM {$CFG->prefix}course_sections
                 WHERE course = $course->id AND
                       section < ".($course->numsections+1)."
                 ORDER BY section";

        if ($sections = $DB->get_records_sql($sql)) {
            $sep = $text = '';
            $i=0;
            foreach($sections as $section){
                $isvisible = $sections[$i]->visible;
                if (!$isvisible and !has_capability('moodle/course:update', $context)) {
                    continue;
                }
                $style = ($isvisible) ? '' : ' class="dimmed"';
                $sect = new qssection($section->summary);
                $summary= stripslashes(strip_tags($sect->gethead()));
                if(!empty($summary)){
                    $data = "class='qs_fold' id='qs_fold_{$section->section}'";
                    if ($i == $highlight) {
                        $text .= "$sep<a $data href=\"$link$i\"$style><strong>{$summary}</strong></a>\n";
                    } else {
                        $text .= "$sep<a $data href=\"$link$i\"$style>{$summary}</a>\n";
                    }
                }
                $sep='<br/>';
                $i++;
            
            }
            $text .= '';
           
        }
        $this->content->text = $text;
        $this->content->text .= "<br/><br/><input type='checkbox' id='qs_folding' name='qs_folding' checked> <span style='color:#aaa'>use section folding</span>";
        if(has_capability('moodle/course:update', $context)){
            $this->content->footer = "<a href='{$CFG->wwwroot}/blocks/quickstructure/addlabels.php?cid=$course->id&bid={$this->instance->id}'>Edit</a>";
            $menu = resetmenu($course,$numthumbs,$thumbwidth,$usethumbs,$uselabels,$menucolour,$thumbeffects);
        } else {
            $menu = (get_config('blocks/quickstructure','qsmenu_'.$course->id));        
        }      
        
        if($usemenu){
            $this->content->text .= "<br/><input type='checkbox' id='qs_showmenu' name='qs_showmenu' checked> <span style='color:#aaa'>use menu</span>";
            $code="<div id='qs_topmenu'>$menu</div>";
            $PAGE->requires->js('/blocks/quickstructure/quickstructure.js'); 
            $PAGE->requires->css('/blocks/quickstructure/quickstructure.css'); 
            $PAGE->requires->js_init_call('quickstructuremenu', array($code));
        }
        
        $this->content->footer .= "";
        return $this->content;
    }
}


function resetmenu($course,$numthumbs=6,$thumbwidth=100,$usethumbs=1,$uselabels=1,$menucolour='',$thumbeffects=1){    
    global $CFG,$DB;
    require_once("structurelib.php");
    //dbg();
    $sql = "SELECT id, section, visible, summary
                  FROM {$CFG->prefix}course_sections
                 WHERE course = $course->id AND
                       section < ".($course->numsections+1)."
              ORDER BY section";
   
    if ($sections = $DB->get_records_sql($sql)) {
       $count=0;
       $seen=$i=0; 
       $m='';
       $he=$thumbeffects?' qs_hover':'';
       foreach($sections as $section){
          if($section->visible){
            $sect = new qssection($section->summary);
            $summary = (strip_tags($sect->gethead()));
            $image = $sect->getimage($thumbwidth,'center');
            $fg=empty($sect->fcol)?'#000000':$sect->fcol;
            $bg=empty($sect->bcol)?'#ffffff':$sect->bcol;
            $ibg=empty($menucolour)?$bg:$menucolour;
            if($usethumbs){
                $image = $sect->getimage($thumbwidth,'center');
                if(!empty($image)){
                    $menu[] = "<td width='$thumbwidth' class='qs_header' align='center' style='color:{$fg};background-color:{$ibg};'><a class='qs_fold{$he}' id='qs_ifold_{$section->section}' style='color:{$fg};' href='#qs_topmenu'>$image</a></td>";
                    $count++;
                    $seen=$i;
                } else {
                    $menu[] = "<td width='$thumbwidth' class='qs_header' align='center' style='color:{$fg};background-color:{$ibg};'><a class='qs_fold' id='qs_ifold_{$section->section}' style='color:{$fg};' href='#qs_topmenu'><img src='{$CFG->wwwroot}/blocks/quickstructure/pix/nothumb.gif' style='height:{$thumbwidth}px;width:{$thumbwidth}px' /></a></td>";
                }
            }else{
                $menu[]='';
                $count++;
                
            }
            if($uselabels){
                if(empty($summary)){
                    $label[] = "<td width='$thumbwidth' class='qs_header' align='center' style='padding:3px;color:{$fg};background-color:{$ibg};'><a class='qs_fold' id='qs_lfold_{$section->section}' style='color:{$fg};' href='#qs_topmenu'>" . "Section $section->section</a></td>";
                }else{
                    $label[] = "<td width='$thumbwidth' class='qs_header' align='center' style='padding:3px;color:{$fg};background-color:{$ibg};'><a class='qs_fold' id='qs_lfold_{$section->section}' style='color:{$fg};' href='#qs_topmenu'>" . "$summary</a></td>";
                    $seen=$i;
                }
            }
            $i++;
          }
        }
            
        $usedr = array_chunk($menu,$seen+1);
        $rows = array_chunk($usedr[0],$numthumbs);
        if($uselabels){
            $usedl = array_chunk($label,$seen+1);
            $labels = array_chunk($usedl[0],$numthumbs);
        }
        
        foreach($rows as $r){
            if($count){
                if($usethumbs){
                    $m .= "<tr>" . implode('',$r) .  "</tr>" ;
                }
                if($uselabels){
                    $lr = array_shift($labels);
                    $m .= "<tr>" . implode('',$lr) .  "</tr>" ;
                }
            }
        }
        $tm = "<a name='qs_topmenu'></a><table class='qs_center'>$m</table>";
        
        $new = ($tm);
        set_config("qsmenu_{$course->id}",$new,'blocks/quickstructure');
        return $new;
    }
}


?>
