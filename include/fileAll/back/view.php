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

$result = $s3->listObjectVersions([
    'Bucket' => $bucket, // REQUIRED
//            'Delimiter' => '<string>',
//    'EncodingType' => 'url',
//            'ExpectedBucketOwner' => '<string>',
//    'KeyMarker' => '<string>',
//            'MaxKeys' => <integer>,
//    'Prefix' => '<string>',
//    'VersionIdMarker' => '<string>',
]);









//showMsg("111","../../fileAll/fonts/download.html");
print_r("Upload time    :   ".json_encode($result['Versions'][0]["LastModified"])."<br/>");
print_r('The size of the file is '.$result['Versions'][0]['Size'].'  bytes');