<?php
require_once "../../include/common/common.inc.php";
require_once "../../include/common/showMsg.php";
$username=isset($_POST['username'])?$_POST['username']:null;
$passwd=isset($_POST['password'])?$_POST['password']:null;
$verifyCode=isset($_POST['verifyCode'])?strtolower($_POST['verifyCode']):null;
$autoFlag=isset($_POST['autoFlag'])?$_POST['autoFlag']:null;
$sessionVerify=isset($_SESSION['verifyCode'])?$_SESSION['verifyCode']:null;

//check whether the register is available
if ($verifyCode!==$sessionVerify){
    showMsg('You robot? Plz input the verifyCode again!',"../../front/login.html");
    exit;
}

checkLogin($username,$passwd);
header("Location:http://127.0.0.1/include/fileAll/fonts/fileInit.html");
exit();