<?php
require_once "../../include/common/common.inc.php";
//require_once "../common/showMsg.php";

function checkUsername($username){
    global $hhost,$huser,$hpass,$hdatabase;
    $conn = mysqli_connect($hhost, $huser, $hpass,$hdatabase);
    if ($username==null){
        showMsg('Plz input your username!',"../../front/registered.html");
        exit();
    }

    $sql="select * FROM user WHERE id = '{$username}'";
    mysqli_select_db($conn,'test');
    $retval = mysqli_query($conn,$sql);
    if (mysqli_num_rows($retval)>=1){
        showMsg('The username is already been registered! Plz try again.',"../../front/registered.html");
        exit();
    }
}