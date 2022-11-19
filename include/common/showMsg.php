<?php
function showMsg($msg,$path){
    echo "<script>alert('{$msg}')</script>";
    echo "<script>window.location.href='{$path}'</script>";
}