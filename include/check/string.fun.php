<?php


function buildVerifyString($type=3,$length=4){
//    $text="";
    switch($type){
        case 1:
            $text=join(range('0','9'));
            break;
        case 2:
            $text=join(array_merge(range('a','z'),range('A','Z')));
            break;
        case 3:
            $text=join(array_merge(range('0','9'),range('a','z'),range('A','Z')));
            break;
    }
    $text=str_shuffle($text);
    $text=substr($text,0,$length);
    return $text;
}