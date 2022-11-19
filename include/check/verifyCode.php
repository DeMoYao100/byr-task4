<?php
require_once "string.fun.php";



function verfyCode($width=110,$height=40,$type=3,$length=4){
    $img=imagecreate($width,$height);


    $bg = imagecolorallocatealpha($img,0,0,0,10);
    imagealphablending($img, false);
    imagefilledrectangle($img,0,0,$width,$height,$bg);


    $text=buildVerifyString($type,$length);
    $_SESSION['verifyCode']=strtolower($text);


    $i=0;
    $dir=dirname(__FILE__);
    while($i<strlen($text)){
        $size=mt_rand(15,20);
        $angle=mt_rand(-15,15);
        $x=15+$i*mt_rand(15,30);
        $y=25+$i*mt_rand(1,5);
        $color=imagecolorallocate($img,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));

        imagettftext($img,$size,$angle,$x,$y,$color,$dir.'/'.'fonts/'.'ALGER.TTF',$text[$i]);
        $i++;
    }

    $i=0;
    while($i<3){
        imageline($img,mt_rand(0,$width),mt_rand(0,$height),mt_rand(0,$width),mt_rand(0,$height),imagecolorallocate($img,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255)));
        $i++;
    }

    header("Content-Type:image/jpeg");
    imagejpeg($img);
}

//verfyCode();