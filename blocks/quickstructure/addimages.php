<?php
    require_once(dirname(__FILE__) . '/../../config.php');
    require_once('structurelib.php');    
    require_once('labels_form.php');
    require_login();
    
    global $CFG,$DB;
    
    $cid = required_param('cid',PARAM_INTEGER);   
    $action = optional_param('action','',PARAM_TEXT);   
    $course = $DB->get_record('course', array('id'=>$cid));

    $context = get_context_instance(CONTEXT_COURSE, $cid);   
    
    // must have editing rights for the host course 
    if(!has_capability('moodle/course:update', $context)){  
        print_error("no access allowed!");
    }
    
    $PAGE->set_pagelayout('base');
    $PAGE->set_context($context);
    $PAGE->set_url('/blocks/quickstructure/addimages.php');
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('stageII','block_quickstructure'));

    // store return button for use now and later
    $return = "<form action='{$CFG->wwwroot}/course/view.php' method='GET'/>
                <input type='hidden' name='id' value='{$cid}' />
                <input type='submit' value='" . get_string('return','block_quickstructure') . "' />
            </form>";
    echo $return;
    
    // grab and process data from Part I if arriving here for first time
    $lform = new labels_form('',array('cid'=>$cid));
    if ($fromlform=$lform->get_data())
    { 
        $usemenu = optional_param("usemenu_{$cid}",get_config('blocks/quickstructure',"usemenu_{$cid}"),PARAM_INTEGER);
        set_config("usemenu_$cid",$usemenu,'blocks/quickstructure');

        $numthumbs = optional_param("numthumbs_{$cid}",get_config('blocks/quickstructure',"numthumbs_{$cid}"),PARAM_INTEGER);
        set_config("numthumbs_$cid",$numthumbs,'blocks/quickstructure');

        $thumbwidth = optional_param("thumbwidth_{$cid}",get_config('blocks/quickstructure',"thumbwidth_{$cid}"),PARAM_INTEGER);
        set_config("thumbwidth_$cid",$thumbwidth,'blocks/quickstructure');

        $usethumbs = optional_param("usethumbs_{$cid}",get_config('blocks/quickstructure',"usethumbs_{$cid}"),PARAM_INTEGER);
        set_config("usethumbs_$cid",$usethumbs,'blocks/quickstructure');
        
        $thumbeffects = optional_param("thumbeffects_{$cid}",get_config('blocks/quickstructure',"thumbeffects_{$cid}"),PARAM_INTEGER);
        set_config("thumbeffects_$cid",$thumbeffects,'blocks/quickstructure');

        $uselabels = optional_param("uselabels_{$cid}",get_config('blocks/quickstructure',"uselabels_{$cid}"),PARAM_INTEGER);
        set_config("uselabels_$cid",$uselabels,'blocks/quickstructure');

        $menucolour = optional_param("menucolour_{$cid}",get_config('blocks/quickstructure',"menucolour_{$cid}"),PARAM_TEXT);
        set_config("menucolour_$cid",$menucolour,'blocks/quickstructure');
    }
    
    $sql = "SELECT id, section, visible, summary
                  FROM {$CFG->prefix}course_sections
                 WHERE course = $course->id AND
                       section < ".($course->numsections+1)."
              ORDER BY section";
    
    
                                 
    if ($sections = $DB->get_records_sql($sql)) {
       echo "<table width='90%'>";
       foreach($sections as $section){
            $sect = new qssection($section->summary);  
            if($fromlform){
                $stripbcol=optional_param('colb_' . $section->id,'#000088',PARAM_TEXT);
                $stripfcol=optional_param('colf_' . $section->id,'#ffffff',PARAM_TEXT);
                $t=optional_param('name_' . $section->id,"Section {$section->section}",PARAM_TEXT);
                $new = "<h2 class='qs_header' style='background-color:{$stripbcol};color:{$stripfcol}'>" . strip_tags(str_replace('&',' and ',$t)) . '</h2>' . ($sect->getimage(200)) . ($sect->gettail());          
                $section->summary=$new;
            }
            
            // deal with FILES the old fashioned way - naughty - but interface preferable to moodle 2 file picker
            if(isset($_FILES['image_'.$section->id])){  
                $src = makethumb($context,'course','section',$section->id,$_FILES['image_'.$section->id]['name'],$_FILES['image_'.$section->id]['tmp_name'],200);
                $section->summary = $sect->insertimage("{$CFG->wwwroot}/$src");
                $sect = new qssection($section->summary);
            }                                 
            
            // process delete of thumbnail
            if($action=='delete'){
               $dsection = required_param('section',PARAM_TEXT);
               if($section->id==$dsection){
                   $section->summary = $sect->deleteimage();
                   $sect = new qssection($section->summary);
               }
            }
            
            // now write any changes back to db
            $DB->update_record('course_sections',$section);
            
            $image = $sect->getimage();
            if(!empty($image)){
                $del="<div align=right><form method='POST'>
                        <input type='hidden' name='action' value='delete' />
                        <input type='hidden' name='cid' value='$cid' />
                        <input type='hidden' name='section' value='{$section->id}'/>
                        <input type='submit' name='submit' value='" . get_string('deletethumb','block_quickstructure') . "'/></form></div>";
            }else{
                $del ='';
            }
            echo "<tr>         
                     <td width='100%'><a name='anc_{$section->id}'></a>
                     $section->summary
                     <br/><br/>";
            $form = "<form id='if_{$section->id}' action='#anc_{$section->id}'  enctype='multipart/form-data' method='POST'>
                                    <input type='hidden' name='cid' value='$cid' />
                                    <input type='file' size='70' name='image_{$section->id}' onchange='document.getElementById(\"image{$section->id}\").click();' />
                                    <br/><input type='hidden' name='section' value='{$section->id}'/>
                                    <input style='display:none;clear:both;' type='submit' name='submit' id='image{$section->id}' name='image{$section->id}' value='submit' />
                                    </form>";
            echo "</td></tr><tr><td>$form $del</td></tr>";         
       }
    }
    echo "</table>";
    
    // echo another return to course button 
    echo $return;

    echo $OUTPUT->footer();

function makethumb($context,$component,$filearea,$itemid,$filename,$originalfile,$w){    
    global $CFG;
   
    if (empty($CFG->gdversion)) {
        return false;
    }

    if (!is_file($originalfile)) {
        return false;
    }
    
    $imageinfo = GetImageSize($originalfile);                            
    
    if (empty($imageinfo)) {
        return false;
    }
    
    $image->width  = $imageinfo[0];
    $image->height = $imageinfo[1];
    $image->type   = $imageinfo[2];
    
    $scale = $w / $image->width;
                
    switch ($image->type) {
        case 1: 
            if (function_exists("ImageCreateFromGIF")) {
                $im = ImageCreateFromGIF($originalfile); 
            } else {
                error("GIF not supported on this server");
                exit(0);
            }
            break;
        case 2: 
            if (function_exists("ImageCreateFromJPEG")) {
                $im = ImageCreateFromJPEG($originalfile); 
            } else {
                error("JPEG not supported on this server");
                exit(0);
            }
            break;
        case 3:
            if (function_exists("ImageCreateFromPNG")) {
                $im = ImageCreateFromPNG($originalfile); 
            } else {
                error("PNG not supported on this server");
                exit(0);
            }
            break;
        case 6:
            if (function_exists("ImageCreateFromBMP")) {
                $im = ImageCreateFromBMP($originalfile); 
            } else {
                error("BMP not supported on this server");
                exit(0);
            }
            break;
        default: 
            return false;
    }
    
    if (function_exists('ImagePng')) {
        $imagefnc = 'ImagePng';
        $imageext = '.png';
        $filters = PNG_NO_FILTER;
        $quality = 1;
    } else if (function_exists('ImageJpeg')) {
        $imagefnc = 'ImageJpeg';
        $imageext = '.jpg';
        $filters = null; // not used
        $quality = 90;
    } else {
        debugging('Jpeg and png not supported on this server, please fix server configuration');
        return false;
    }    
    
    $cx = $w;
    $cy = intval($image->height * $scale); 
    
    if (function_exists('ImageCreateTrueColor') and $CFG->gdversion >= 2) {
        $im1 = ImageCreateTrueColor($cx,$cy);
        if ($image->type == IMAGETYPE_PNG and $imagefnc === 'ImagePng') {
            imagealphablending($im1, false);
            $color = imagecolorallocatealpha($im1, 0, 0,  0, 127);
            imagefill($im1, 0, 0,  $color);
            imagesavealpha($im1, true);
            
        }
    } else {
        $im1 = ImageCreate($cx,$cy);
    }
     
    MyImageCopyBicubic($im1, $im, 0, 0, 0, 0, $w, $cy, $image->width, $image->height);


    $fs = get_file_storage();

    $icon = array('contextid'=>$context->id, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'filepath'=>'/');

    ob_start();
    if (!$imagefnc($im1, NULL, $quality, $filters)) {
        // keep old icons
        ob_end_clean();
        return false;
    }
    $data = ob_get_clean();
    ImageDestroy($im1);
    $icon['filename'] = $filename;
    $fs->delete_area_files($context->id, $component, $filearea, $itemid);
    $fs->create_file_from_string($icon, $data);

    return "pluginfile.php/{$context->id}/$component/$filearea/$itemid/$filename";
}
  
function MyImageCopyBicubic ($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
    global $CFG;

    if (function_exists('ImageCopyResampled') and $CFG->gdversion >= 2) {
       return ImageCopyResampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y,
                                 $dst_w, $dst_h, $src_w, $src_h);
    }

    $totalcolors = imagecolorstotal($src_img);
    for ($i=0; $i<$totalcolors; $i++) {
        if ($colors = ImageColorsForIndex($src_img, $i)) {
            ImageColorAllocate($dst_img, $colors['red'], $colors['green'], $colors['blue']);
        }
    }

    $scaleX = ($src_w - 1) / $dst_w;
    $scaleY = ($src_h - 1) / $dst_h;

    $scaleX2 = $scaleX / 2.0;
    $scaleY2 = $scaleY / 2.0;

    for ($j = 0; $j < $dst_h; $j++) {
        $sY = $j * $scaleY;

        for ($i = 0; $i < $dst_w; $i++) {
            $sX = $i * $scaleX;

            $c1 = ImageColorsForIndex($src_img,ImageColorAt($src_img,(int)$sX,(int)$sY+$scaleY2));
            $c2 = ImageColorsForIndex($src_img,ImageColorAt($src_img,(int)$sX,(int)$sY));
            $c3 = ImageColorsForIndex($src_img,ImageColorAt($src_img,(int)$sX+$scaleX2,(int)$sY+$scaleY2));
            $c4 = ImageColorsForIndex($src_img,ImageColorAt($src_img,(int)$sX+$scaleX2,(int)$sY));

            $red = (int) (($c1['red'] + $c2['red'] + $c3['red'] + $c4['red']) / 4);
            $green = (int) (($c1['green'] + $c2['green'] + $c3['green'] + $c4['green']) / 4);
            $blue = (int) (($c1['blue'] + $c2['blue'] + $c3['blue'] + $c4['blue']) / 4);

            $color = ImageColorClosest ($dst_img, $red, $green, $blue);
            ImageSetPixel ($dst_img, $i + $dst_x, $j + $dst_y, $color);
        }
    }
} 

?>