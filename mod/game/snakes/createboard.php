<?php  // $Id: createboard.php,v 1.1 2010/08/29 11:16:21 bdaloukas Exp $

// This files create a board for "Snakes and Ladders"

$file='DSC04418.JPG';
$colsx = 8; $colsy = 8; $board = 'S18-48,L1-20';$ofstop=8; $ofsbottom = 8; $ofsright = 8; $ofsleft = 8;
$dir = $CFG->wwwroot.'/mod/game/snakes/1';

function game_createsnakesboard($file, $dir, $colsx, $colsy, $ofstop, $ofsbottom, $ofsright, $ofsleft)
{
    $im = imagecreatefromjpeg($file);

    $cx = imagesx($im) - $ofsright - $ofsleft;
    $cy = imagesy($im) - $ofstop - $ofsbottom;

    $color = 0xFF0000;
    for( $i=0; $i <= $colsx; $i++)
    {
        imageline( $im, $ofsleft+$i * $cx / $colsx, $ofstop, $ofsleft+$i * $cx / $colsx, $cy+$ofstop, $color);
    }

    for( $i=0; $i <= $colsy; $i++)
    {
        imageline( $im, $ofsleft, $ofstop+$i * $cy / $colsy, $cx+$ofsleft, $ofstop+$i * $cy / $colsy, $color);
    }

    $filenamenumbers=$dir.'/numbers.gif';
    $img_numbers = imageCreateFromgif( $filenamenumbers);
    $size_numbers = getimagesize ($filenamenumbers);

    for( $iy=0; $iy < $colsy; $iy++)
    {
        if( $iy % 2 == 0){
            $inc=false;
            $num = ($colsy-$iy)*$colsy;
        }else
        {
            $inc=true;
            $num = ($colsy-$iy)*$colsy-($colsy-1);
        } 
        $ypos = $iy * $cy / $colsy+$ofstop;
        for( $ix=0; $ix < $colsx; $ix++)
        {
            $xpos = $ix * $cx / $colsx + $ofsleft;
            shownumber( $im, $img_numbers, $num, $xpos, $ypos, $cx/4, $cy/4, $size_numbers);
            $num = ($inc ? $num+1 : $num-1);
        }
    }

    makeboard( $im, $dir, $cx, $cy, $board, $colsx, $colsy, $ofsleft, $ofstop);

    return $im;
}

function computexy( $pos, &$x, &$y, $colsx, $colsy)
{
    $x = ($pos - 1) % $colsx;
    $y = ($colsy-1) - floor( ($pos - 1) / $colsy);
    if($y % 2 == 0)
        $x = ($colsx-1) - $x;
}

function makeboard( $im, $dir, $cx, $cy, $board, $colsx, $colsy, $ofsleft, $ofstop)
{
    $a = explode( ',', $board);
    foreach( $a as $s)
    {    
        if( substr( $s,0,1) == 'L')
            makeboardL( $im, $dir, $cx, $cy, substr( $s, 1), $colsx, $colsy, $ofsleft, $ofstop);
        else
            makeboardS( $im, $dir, $cx, $cy, substr( $s, 1), $colsx, $colsy, $ofsleft, $ofstop);
    }
}

function makeboardL( $im, $dir, $cx, $cy, $s, $colsx, $colsy, $ofsleft, $ofstop)
{
    $pos = strpos( $s, '-');
    $from = substr( $s, 0, $pos);
    $to = substr( $s, $pos+1);

    computexy( $from, $startx, $starty, $colsx, $colsy);
    computexy( $to, $x2, $y2, $colsx, $colsy);
    if( ($x2 < $startx) and ($y2 < $starty))
    {
        $temp = $x2; $x2 = $startx; $startx = $temp;
        $temp = $y2; $y2 = $starty; $starty = $temp;
    }
    $movex = $x2 - $startx;
    $movey = $y2 - $starty;

    $letter = ( $movex * $movey < 0 ? 'b' : 'a');

    $_startx = $startx; $_movex=$movex; $_starty = $starty; $_movey=$movey;

            if( $movex < 0)
            {
                $startx += $movex;
                $movex = -$movex;
            } 
            if( $movey < 0)
            {
                $starty += $movey;
                $movey = -$movey;
            }

    if( $letter == 'b'){
        $file = $dir.'/l'.$letter.$movey.$movex.'.gif';
        if( file_exists( $file)){
            $stamp = game_imagecreatefromgif( $file);
        }else
        {
            $file = $dir.'/la'.$movey.$movex.'.gif';

            $source = game_imagecreatefromgif( $file);
            if( $source != 0)
                $stamp = imagerotate($source, 90, 0);
        }
    }else
    {
        $file = $dir.'/la'.$movex.$movey.'.gif';
        $stamp = game_imagecreatefromgif( $file);
    }
    
    if( $start == 0)
    {
        $startx = $_startx; $movex=$_movex; $starty = $_starty; $movey=$_movey;  
    }

    $dst_x = $startx*$cx/$colsx;
    $dst_y = $starty*$cy/$colsy;
    $dst_w = ($movex+1) * $cx / $colsx;
    $dst_h = ($movey+1) * $cy / $colsy;

    if( $stamp == 0)
        game_printladder( $im, $file, $dst_x+$ofsleft, $dst_y+$ofstop, $dst_w, $dst_h, $cx/$colsx, $cy/$colsy);
    else
        imagecopyresampled( $im, $stamp, $ofsleft+$dst_x, $ofstop+$dst_y, 0, 0, $dst_w, $dst_h, 100*$movex+100, 100*$movey+100);
}

function makeboardS( $im, $dir, $cx, $cy, $s, $colsx, $colsy, $ofsleft, $ofstop)
{
    $pos = strpos( $s, '-');
    $from = substr( $s, 0, $pos);
    $to = substr( $s, $pos+1);

    computexy( $from, $startx, $starty, $colsx, $colsy);
    computexy( $to, $x2, $y2, $colsx, $colsy);
    $swap=0;
    if( ($x2 < $startx) and ($y2 < $starty))
    {
        $temp = $x2; $x2 = $startx; $startx = $temp;
        $temp = $y2; $y2 = $starty; $starty = $temp;
        $swap=1;
    }
    $movex = $x2 - $startx;
    $movey = $y2 - $starty;

    //a*d
    //***
    //b*c
    $stamp = $rotate = 0;
    if( $movex >= 0 and $movey < 0){
        $letter = 'b';
        $file = $dir.'/sa'.$movey.$movex.'.gif';
        $source = game_imagecreatefromgif( $file);
        if( $source != 0)
        {
            $stamp = imagerotate($source, 270, 0);
            $starty += $movey; $movey = -$movey;
        }else
            $rotate = 270;
    }else if( $movex < 0 and $movey < 0){
        $letter = 'c';
        $file = $dir.'/sa'.$movey.$movex.'.gif';
        $source = game_imagecreatefromgif( $file;
        if( $source != 0)
        {
            $stamp = imagerotate($source, 180, 0);
            $startx += $movex; $movex = -$movex;
            $starty += $movey; $movey = -$movey;
        }else
            $rotate = 180;
    }else if( ($movex < 0) and ($movey >= 0)){
        $letter = 'd';
        $file = $dir.'/sa'.$movey.$movex.'.gif';
        $source = game_imagecreatefromgif( $file);
        if( $source != 0)
        {
            $stamp = imagerotate($source, 270, 0);
            $startx += $movex; $movex = -$movex;
        }else
            $rotate=270;
    }else
    {
        $file = $dir.'/sa'.$movex.$movey.'.gif';
        $stamp = game_imagecreatefromgif( $file);
    }

        if( ($swap != 0) and ($stamp == 0))
        {
            $temp = $x2; $x2 = $startx; $startx = $temp;
            $temp = $y2; $y2 = $starty; $starty = $temp;
            $movex = $x2 - $startx;
            $movey = $y2 - $starty;
        }

    $dst_x = $startx*$cx/$colsx;
    $dst_y = $starty*$cy/$colsy;
    $dst_w = ($movex+1) * $cx / $colsx;
    $dst_h = ($movey+1) * $cy / $colsy;

    if( $stamp == 0)
    {
        game_printsnake( $im, $file, $dst_x+$ofsleft, $dst_y+$ofstop, $dst_w, $dst_h, $cx/$colsx, $cy/$colsy);
    }else
        imagecopyresampled( $im, $stamp, $dst_x+$ofsleft, $dst_y+$ofstop, 0, 0, $dst_w, $dst_h, 100*$movex+100, 100*$movey+100);
}

function game_imagecreatefromgif( $file){
    if( file_exists( $file))
        return imagecreatefromgif( $file);

    return 0;
}

function shownumber( $img_handle, $img_numbers, $number, $x1 , $y1, $width, $height, $size_numbers){
    if( $number < 10){
        $width_number = $size_numbers[ 0] / 10;
        $dstX = $x1 + $width / 10;
        $dstY = $y1 + $height / 10;
        $srcX = $number * $size_numbers[ 0] / 10;
        $srcW = $size_numbers[ 0]/10;
        $srcH = $size_numbers[ 1];
        $dstW = $width / 10;
        $dstH = $dstW * $srcH / $srcW;
        imagecopyresampled( $img_handle, $img_numbers, $dstX, $dstY, $srcX, 0, $dstW, $dstH, $srcW, $srcH);
    }else
    {
        $number1 = floor( $number / 10);
        $number2 = $number % 10;
        shownumber( $img_handle, $img_numbers, $number1, $x1-$width/20, $y1, $width, $height, $size_numbers);
        shownumber( $img_handle, $img_numbers, $number2, $x1+$width/20, $y1, $width, $height, $size_numbers);
    }
}

function returnRotatedPoint($x,$y,$cx,$cy,$a)
    {
             // radius using distance formula
            $r = sqrt(pow(($x-$cx),2)+pow(($y-$cy),2));
            // initial angle in relation to center
            $iA = rad2deg(atan2(($y-$cy),($x-$cx)));

            $nx = $r * cos(deg2rad($a + $iA));
            $ny = $r * sin(deg2rad($a + $iA));

    return array("x"=>$cx+$nx,"y"=>$cy+$ny);
    }

function game_printladder( $im, $file, $x, $y, $width, $height, $cellx, $celly)
{
    $color = imagecolorallocate($im, 0, 0, 255);
    $x2 = $x+$width-$cellx/2;
    $y2 = $y+$height-$celly/2;
    $x1 = $x+$cellx/2;
    $y1 = $y+$celly/2;
    imageline( $im, $x1, $y1, $x2, $y2, $color);
    $r = sqrt(pow(($x2-$x1),2)+pow(($y2-$y1),2));
    $mul = 100 / $r;
    $x1 = $x2 - ($x2-$x1) * $mul;
    $y1 = $y2 - ($y2-$y1) * $mul;
    $a = returnRotatedPoint( $x1, $y1, $x2, $y2, 20);
    imageline( $im, $x2, $y2, $a[ 'x'], $a[ 'y'], $color);
    $a = returnRotatedPoint( $x1, $y1, $x2, $y2, -20);
    imageline( $im, $x2, $y2, $a[ 'x'], $a[ 'y'], $color);
}

function game_printsnake( $im, $file, $x, $y, $width, $height, $cellx, $celly)
{
    $color = imagecolorallocate($im, 0, 255, 0);
    $x2 = $x+$width-$cellx/2;
    $y2 = $y+$height-$celly/2;
    $x1 = $x+$cellx/2;
    $y1 = $y+$celly/2;
    imageline( $im, $x1, $y1, $x2, $y2, $color);
    
    $r = sqrt(pow(($x2-$x1),2)+pow(($y2-$y1),2));
    $mul = 100 / $r;
    $x2 = $x1 + ($x2-$x1) * $mul;
    $y2 = $y1 + ($y2-$y1) * $mul;
    $a = returnRotatedPoint( $x1, $y1, $x2, $y2, 80);
    imageline( $im, $x1, $y1, $a[ 'x'], $a[ 'y'], $color);
    $a = returnRotatedPoint( $x1, $y1, $x2, $y2, -80);
    imageline( $im, $x1, $y1, $a[ 'x'], $a[ 'y'], $color);
}
