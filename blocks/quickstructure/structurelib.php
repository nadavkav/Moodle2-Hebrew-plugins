<?php

class qssection {
 
    var $level = 0;
    var $dom = null;
    var $struct=array();
    var $fcol='';
    var $bcol='';
    
    function qssection($xml) {
        $this->sep = "<!-- qs -->";
        $parts = explode($this->sep,$xml);
        if(isset($parts[1])){
            $this->tail=$this->sep . $parts[1];    
            // editor sometimes add tags! strip em all out 
            $head=strip_tags($parts[0],'<h1><h2><h3><h4><h5><h6><img><div>');
        }else{
            $head = '<h2 style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);" class="qs_header"></h2>';    
            $this->tail=$this->sep . $parts[0];    
        }
        $this->dom = new DOMDocument;
        $this->dom->loadXml('<div>' . $head . '</div>');
        $this->process();
    }
    
    function gethead(){
        if($this->level){
            return $this->struct[0]['content'];
        }else{
            return '';
        }    
    }
    function getfullhead(){
        $tag = $this->struct[0]['tag'];
        $a = $this->struct[0]['attr'];
        if(!isset($this->struct[0]['class'])){
            $cls = " class='qs_header' ";    
        }else{
            $cls = '';
        }
        if(empty($a)){
            return "<{$tag}{$cls}>{$this->struct[0]['content']}</{$tag}>";
        }else{
            return "<{$tag}{$cls} $a>{$this->struct[0]['content']}</{$tag}>";        
        }
    }
    
    function gettail(){
        return $this->tail;
    }
    function getimage($width=100,$align='right'){
        if(!empty($this->img)){
            return "<img width='$width' align='$align' src='{$this->img}' />";
        }else{
            return '';
        }
    }
 
    function _flatten($node){
        $result='';
        if($node->nodeType == XML_TEXT_NODE) { 
            $result = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT | ENT_HTML401, ''), 
                                     ENT_COMPAT,'ISO-8859-15');
        } 
        else {
            if($node->hasChildNodes()){
                $children = $node->childNodes;
 
                for($i=0; $i<$children->length; $i++) {
                    $child = $children->item($i);
 
                    if($child->nodeName != '#text') {
                        $t=$child->nodeName;
                        $c .=  $this->_flatten($child);
                            
                        if($child->hasAttributes()) { 
                            $attributes = $child->attributes;
             
                            if(!is_null($attributes)) {
                                foreach ($attributes as $key => $attr) {
                                    $a .= "{$attr->name}='{$attr->value}' ";
                                }
                            }
                        }
                        if(!empty($a)){
                            $result .= "<$t $a>$c</$t>";
                        }else{
                            $result .= "<$t>$c</$t>";
                        }
                        
                    }else if ($child->nodeName == '#text') {
                        $result .= $child->nodeValue;//trim($this->_flatten($child));
                    }
                }
            } 
 
            
        }
        return $result;        
    }
    
    function _process($node,$seq) { 
 
        if($node->nodeType == XML_TEXT_NODE) { 
            $result = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT | ENT_HTML401, ''), 
                                     ENT_COMPAT,'ISO-8859-15');
        } 
        else {
            if($node->hasChildNodes()){
                $children = $node->childNodes;
 
                for($i=0; $i<$children->length; $i++) {
                    $child = $children->item($i);
 
                    if($child->nodeName != '#text') {
                        $p =  $this->_flatten($child);
                        $result[$seq]['tag']=$child->nodeName;
                        $result[$seq]['content'] = $p;
                            
                        if($child->hasAttributes()) { 
                            $attributes = $child->attributes;
             
                            $a = '';
                            if(!is_null($attributes)) {
                                foreach ($attributes as $key => $attr) {
                                    $att = $this->reparse($attr->value);
                                    $result[$seq][$attr->name] = $att;
                                    $a .= "{$attr->name}='{$att}' "; 
                                }
                                $result[$seq]['attr']=$a;
                            }
                        }
                        $seq++;
                        
                    }else if ($child->nodeName == '#text') { 
                        $text = $this->_flatten($child);
                        if (trim($text) != '') {
                            $result[$seq]['text'] = $this->_flatten($child);
                            $seq++; 
                        }
                    }
                }
            } 
 
            
        }
 
        return $result;
    }
 
    function reparse($attr){
        $attrs = explode(';',$attr);
        $out=$sep='';
        foreach($attrs as $a){
            $ps=explode(':',$a);
            if(!isset($ps[1])){
                $out .= "{$sep}{$a}";
            }elseif(trim($ps[0])=='color'||trim($ps[0]=='background-color')){
                $out .= "{$sep}{$ps[0]}:" . tohtmlcol($ps[1]);    
            }else{
                $out .= "{$sep}{$ps[0]}:{$ps[1]}";
            }
            $sep=';';
        }
        return $out;
    }
    
    function process() {
        //dbg();
        $node = $this->dom;
        if($node->hasChildNodes()){
           $children = $node->childNodes;
           for($i=0; $i<$children->length; $i++) {
               $child = $this->_process($children->item($i),0);
           }
        }
        $this->struct = $child;
        $tag=$this->struct[0]['tag'];
        if($tag=='h2'){
           $this->level=2; 
        }elseif($tag=='h1'){
           $this->level=1; 
        }elseif($tag=='h3'){
           $this->level=3; 
        }else{
           $this->level=0; 
        }
        $this->img='';
        if($this->level){
            $ps=explode(';',$this->struct[0]['style']);
            foreach($ps as $p){
               $px=explode(':',$p);
               if($px[0]=='color'){
                   $this->fcol=tohtmlcol($px[1]);
               }elseif($px[0]=='background-color'){
                   $this->bcol=tohtmlcol($px[1]);
               } 
            }
            if(isset($this->struct[1])){
                if($this->struct[1]['tag']=='img'){
                   $this->img = $this->struct[1]['src']; 
                } 
            }
        }    
    }
    function deleteimage(){
        $out=$this->getfullhead();
        $out .= $this->gettail();
        return $out;
    }
    function insertimage($src){
        $out=$this->getfullhead();
        if($this->img){
            $this->img=$src;
            $this->struct[1]['src']=$src;
        }    
        $out .= "<img width='200' align='right' src='$src' />";
        $out .= $this->gettail();
        return $out;
    }
    
}
        
function tohtmlcol($rgb){
    if(substr(trim($rgb),0,4)=='rgb('){
        return rgbstring2html($rgb);
    }else{
        return $rgb;
    }
}        
function rgbstring2html($rs){
    $r=explode('rgb(',$rs);
    $rp = explode(',',trim($r[1],"()"));
    return rgb2html($rp[0],$rp[1],$rp[2]);
}        

function rgb2html($r, $g=-1, $b=-1)
{
    if (is_array($r) && sizeof($r) == 3)
        list($r, $g, $b) = $r;

    $r = intval($r); $g = intval($g);
    $b = intval($b);

    $r = dechex($r<0?0:($r>255?255:$r));
    $g = dechex($g<0?0:($g>255?255:$g));
    $b = dechex($b<0?0:($b>255?255:$b));

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;
    return '#'.$color;
} 

       
?>