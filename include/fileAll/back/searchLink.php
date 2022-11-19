<?php
require_once "sqlLink.php";
require_once "../../../include/common/showMsg.php";

$link=isset($_POST['downloadFileLink'])?$_POST['downloadFileLink']:null;
checkIfLinkAvail($link);

function checkIfLinkAvail($link){
    global $hhost, $huser, $hpass;
    $conn = mysqli_connect($hhost, $huser, $hpass);
    $sql = "SELECT link FROM linkTest WHERE link = $link";
    connect();
    mysqli_select_db($conn, "test");
    $retval = mysqli_query($conn,$sql);
    if (!$retval){
        showMsg('Your link does not exist! Plz check it again!',"../fonts/download.html");
    }

    $_SESSION['link']=$link;
}


include("../../../include/fileAll/fonts/fileDetail.html");
exit();