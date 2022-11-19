<?php
require_once "../../../include/check/string.fun.php";
require '../../../vendor/autoload.php';


$file=isset($_FILES["uploadFile"]['tmp_name'])?$_FILES["uploadFile"]['tmp_name']:null;
$link=buildVerifyString(3,5);
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1',
    'endpoint' => 'http://localhost:9000',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'minioadmin',
        'secret' => 'minioadmin',
    ],
]);


//$filename = substr($_FILES['uploadFile']['name'],0,strrpos($_FILES['uploadFile']['name'],"."));
$filename = $_FILES['uploadFile']['name'];
$bucket = strtolower($link.$filename);

// 该策略设置存储桶为只读
$policyReadOnly = '{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "s3:GetBucketLocation",
        "s3:ListBucket"
      ],
      "Effect": "Allow",
      "Principal": {
        "AWS": [
          "*"
        ]
      },
      "Resource": [
        "arn:aws:s3:::%s"
      ],
      "Sid": ""
    },
    {
      "Action": [
        "s3:GetObject"
      ],
      "Effect": "Allow",
      "Principal": {
        "AWS": [
          "*"
        ]
      },
      "Resource": [
        "arn:aws:s3:::%s/*"
      ],
      "Sid": ""
    }
  ]
}
';


// 如果你想将文件放到指定目录，你只需要修改'arn:aws:s3:::%s/*'为'arn:aws:s3:::%s/folder/*'

// 创建一个存储桶
$result = $s3->createBucket([
    'Bucket' => $bucket,
]);


// 配置策略
$s3->putBucketPolicy([
    'Bucket' => $bucket,
    'Policy' => sprintf($policyReadOnly, $bucket, $bucket),
]);


$path=$_FILES["uploadFile"]["tmp_name"];
$oldName=$_FILES['uploadFile']['name'];

//$test = $s3->uploadPart($path);
//
//$path=ltrim($path,DIRECTORY_SEPARATOR);
//$pathArr=explode(DIRECTORY_SEPARATOR,$path);

$plainUrl = $s3->getObjectUrl($bucket, 'testkey');

$insert = $s3->putObject([
    'Bucket' => $bucket,
    'Key'    => $bucket,
    'Body'   => fopen($file,'r')
]);

print_r("You can download the file with \n".$bucket);




