<?php
require_once($CFG->libdir.'/formslib.php');
class labels_form extends moodleform
{
   function definition()
   {
        global $CFG,$DB,$OUTPUT,$PAGE;
        
        MoodleQuickForm::registerElementType('colourpopup', "$CFG->libdir/form/colourpopup.php", 'MoodleQuickForm_colourpopup');

                
        $cid     = $this->_customdata['cid'];  
        $course = $DB->get_record('course',array('id'=>$cid));
        $mform =& $this->_form;
        
        $mform->addElement('html','<table><tr><td width="190">' . get_string('background','block_quickstructure') . '</td><td width="190">' . get_string('foreground','block_quickstructure') . '</td><td>&nbsp;</td></tr>');   
        $mform->addElement('html','<tr><td>');
        $mform->addElement('colourpopup','colb','',array('id'=>'colb','tabindex'=>-1,'value'=>'#FFFFFF'));
        $mform->addElement('html','</td><td>');
        $mform->addElement('colourpopup','colf','',array('id'=>'colf','tabindex'=>-1,'value'=>'#000000'));
        $mform->addElement('html','<br/></td><td><i>' . get_string('default','block_quickstructure') . '</i><br/></td></tr>');
        //$mform->registerRule('col','regex','/^#([a-fA-F0-9]{6})$/');
        //$mform->addRule('colb','Enter a valid RGB color - # and then 6 characters','col');
        
        $sql = "SELECT id, section, visible, summary
                  FROM {$CFG->prefix}course_sections
                 WHERE course = $course->id AND
                       section < ".($course->numsections+1)."
              ORDER BY section";
        if ($sections = $DB->get_records_sql($sql)) {
            foreach($sections as $section){
                $sect = new qssection($section->summary);
                $summary = strip_tags($sect->gethead());
                $fg=empty($sect->fcol)?'#000000':strtoupper($sect->fcol);
                $bg=empty($sect->bcol)?'#FFFFFF':strtoupper($sect->bcol);
                $mform->addElement('html','<tr><td width="150">');
                $mform->addElement('colourpopup','colb_'.$section->id,'',array('id'=>'colb_'.$section->id,'tabindex'=>-1,'class'=>'pickerb','value'=>$bg));
                $mform->addElement('html','</td><td width="150">');
                $mform->addElement('colourpopup','colf_'.$section->id,'',array('id'=>'colf_'.$section->id,'tabindex'=>-1,'class'=>'pickerf','value'=>$fg));
                $mform->addElement('html','</td><td>');
                $mform->addElement('text', 'name_'.$section->id, 'Section '.$section->section,array('id'=>'name_'.$section->id,'style'=>"color:$fg;background:$bg;font-size:1.6em;",'value'=>$summary,'class'=>'section','size'=>'60'));
                $mform->addElement('html','</td></tr>');    
            }
            
        }   
 
    $usemenu = get_config('blocks/quickstructure',"usemenu_{$cid}");
    if($usemenu === false){
         $usemenu=1;
    }
    $un='usemenu_'.$cid;
    $options=array(0=>'No',1=>'Yes');
    $mform->addElement('html','<tr><td colspan=3>');
    $mform->addElement('select',$un,get_string('usemenu','block_quickstructure'),$options);
    $mform->setType($un,PARAM_INT);
    $mform->setDefault($un,$usemenu);
    $mform->addElement('html','</td></tr>');
        
    if(!$numthumbs = get_config('blocks/quickstructure',"numthumbs_{$cid}")){
         $numthumbs=6;
    }
    $mform->addElement('html','<tr><td colspan=3>');
    $mform->addElement('text', "numthumbs_{$cid}", get_string('numthumbs','block_quickstructure'), $numthumbs);
    $mform->setType("numthumbs_{$cid}",PARAM_INT);
    $mform->setDefault("numthumbs_{$cid}",$numthumbs);
    $mform->addElement('html','</td></tr>');
    
    if(!$thumbwidth = get_config('blocks/quickstructure',"thumbwidth_{$cid}")){
         $thumbwidth=100;
    } 
    $mform->addElement('html','<tr><td colspan=3>');
    $mform->addElement('text', "thumbwidth_{$cid}", get_string('thumbwidth','block_quickstructure'), $thumbwidth);
    $mform->setType("thumbwidth_{$cid}",PARAM_INT);
    $mform->setDefault("thumbwidth_{$cid}",$thumbwidth);
    $mform->addElement('html','</td></tr>');
           
    $usethumbs = get_config('blocks/quickstructure',"usethumbs_{$cid}");
    if($usethumbs === false){
         $usethumbs=1;
    }
    $mform->addElement('html','<tr><td colspan=3>');
    $mform->addElement('select','usethumbs_'.$cid,get_string('usethumbs','block_quickstructure'),$options);
    $mform->setType('usethumbs_'.$cid, PARAM_INT);
    $mform->setDefault('usethumbs_'.$cid, $usethumbs);
    $mform->addElement('html','</td></tr>');
    
    $thumbeffects = get_config('blocks/quickstructure',"thumbeffects_{$cid}");
    if($thumbeffects === false){
         $thumbeffects=1;
    }
    $mform->addElement('html','<tr><td colspan=3>');
    $mform->addElement('select','thumbeffects_'.$cid,get_string('thumbeffects','block_quickstructure'),$options);
    $mform->setType('thumbeffects_'.$cid, PARAM_INT);
    $mform->setDefault('thumbeffects_'.$cid, $thumbeffects);
    $mform->addElement('html','</td></tr>');
    
    $uselabels = get_config('blocks/quickstructure',"uselabels_{$cid}");
    if($uselabels === false){
         $uselabels=1;
    }
    $mform->addElement('html','<tr><td colspan=3>');
    $mform->addElement('select','uselabels_'.$cid,get_string('uselabels','block_quickstructure'),$options);
    $mform->setType('uselabels_'.$cid, PARAM_INT);
    $mform->setDefault('uselabels_'.$cid, $uselabels);
    $mform->addElement('html','</td></tr>');    
    
    $menubg = get_config('blocks/quickstructure',"menucolour_{$cid}");
    if($menubg === false){
         $menubg='#FFFFFF';
    }
    $mform->addElement('html','<tr><td colspan=3>');
    $mform->addElement('colourpopup','menucolour_'.$cid,get_string('menucolour','block_quickstructure'));
    $mform->setType('menucolour_'.$cid, PARAM_TEXT);
    $mform->setDefault('menucolour_'.$cid, $menubg);
    $mform->addElement('html','<i>' . get_string('bleeding', 'block_quickstructure') . '</i></td></tr>');
    
    $mform->addElement('html','</table>'); 
    $mform->addElement('hidden', 'cid', $cid);
    
    $mform->addElement('submit', 'submitbutton', get_string('savechanges','block_quickstructure'));

   }
}
?>