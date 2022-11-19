<?php
function checkLogin($username,$password){
    global $hhost,$huser,$hpass,$hdatabase;
    $conn = mysqli_connect($hhost, $huser, $hpass, $hdatabase);
    if ($username==null){
        echo("Plz input your username!");
        exit();
    }
    if ($password==null){
        echo("Plz input your password!");
        exit();
    }
    $sql="SELECT password FROM user WHERE id = '{$username}'";
    mysqli_select_db($conn,'test');
    $retval = mysqli_query($conn,$sql);
    $correctPassword = mysqli_fetch_array($retval,MYSQLI_ASSOC);
    if ($correctPassword['password']!==$password or (!$correctPassword) ){
        echo "Your password is incorrect! Plz try again!!!";
        exit();
    }
}