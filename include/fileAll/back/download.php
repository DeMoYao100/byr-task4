<?php
require '../../../vendor/autoload.php';
require_once  '../../../include/common/showMsg.php';

$link=isset($_POST["downloadFileLink"])?$_POST["downloadFileLink"]:null;
$bucket = strtolower($link);
$fileName=$link;
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region' => 'us-east-1',
    'endpoint' => 'http://localhost:9000',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key' => 'minioadmin',
        'secret' => 'minioadmin',
    ],
]);


// 下载文件的内容
$retrive = $s3->getObject([
    'Bucket' => $bucket,
    'Key' => $bucket,
    'SaveAs' => 'testkey_local'
]);



//echo "dsadsad";
//print_r("Upload time    :   ".json_encode($result['Versions'][0]["LastModified"])."<br/>");
//print_r('The size of the file is '.$result['Versions'][0]['Size'].'  Bytes');
//var_dump(json_encode($result['Versions'][0]["LastModified"]));
//echo gettype($result['Versions'][0]["LastModified"]);
//echo $result;

//header("Content-Type: application/txt");
header("Content-Disposition: attachment; filename=".$fileName);


//返回文件信息
exit();