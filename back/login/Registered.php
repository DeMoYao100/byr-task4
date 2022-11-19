<?php
require_once "../../include/common/common.inc.php";
require_once "../../include/common/showMsg.php";
$username=isset($_POST['username'])?$_POST['username']:null;
$passwd=isset($_POST['password'])?$_POST['password']:null;
$passwdAgain=isset($_POST['passwordAgain'])?$_POST['passwordAgain']:null;
$verifyCode=isset($_POST['verifyCode'])?strtolower($_POST['verifyCode']):null;
$autoFlag=isset($_POST['autoFlag'])?$_POST['autoFlag']:null;
$sessionVerify=isset($_SESSION['verifyCode'])?$_SESSION['verifyCode']:null;

//check whether the register is available
if ($verifyCode!==$sessionVerify){
    showMsg('You robot? Plz input the verifyCode again!',"../../front/registered.html");
    exit;
}

checkPassword($passwd,$passwdAgain);
checkUsername($username);

global $hhost, $huser, $hpass;
$conn = mysqli_connect($hhost, $huser, $hpass);
$sql = "insert into user".
    "(id,password)".
    "values".
    "($username,$passwd);";

mysqli_select_db($conn, "test");
$retval = mysqli_query($conn,$sql);