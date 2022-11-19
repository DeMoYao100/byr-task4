<?php
require_once "../../include/common/showMsg.php";
function checkPassword($pass,$passAg){
    if ($pass==null){
        showMsg('???Where is your password? Plz input your password!',"../../front/registered.html");
        exit;
    }
    elseif (null==$passAg){
        showMsg('Plz input your password again',"../../front/registered.html");
        exit;
    }
    elseif($pass!=$passAg){
        echo("The second time you input your password is different from your first time,plz try again!");
        showMsg('The second time you input your password is different from your first time,plz try again!',"../../front/registered.html");
        exit;
    }
}