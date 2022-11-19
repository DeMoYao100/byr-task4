<?php
namespace app\index\controller;

use app\index\model\MinioClient;
use think\Db;
use think\Exception;
use app\index\traits\CommonMethod;
use think\exception\ValidateException;
use think\Log;
use Aws\S3\Exception\S3Exception;

/**
 * 功能概述：
 * 样本管理中心，负责样本上传、样本删除、样本投放、样本投放历史列表、样本下载等功能
 * Class RuleOperation
 * @package app\index\controller
 */
class SampleOperation extends CommonController
{
    //调用接口传递过来的json字符串
    protected $reqContent;

    //json数组
    protected $reqJsonArr;

    //upload file max size
    protected $uploadFileMaxSize = 256 * 1024 * 1024;

    //upload file allowed ext
    protected $uploadFileAllowedExt = ['apk'];

    //upload file allowed MIME type
    protected $uploadFileAllowedMIME = ['application/java-archive'];

    //upload file save temp dir()
    protected $uploadFileSavePath = ROOT_PATH . 'uploads' . DS . "sample_file" . DS;

    //在调用该控制器的其他自定义方法前，会先触发_initialize；连带触发
    public function _initialize()
    {
        parent::_initialize();
        //当前控制器内公用
        $this->reqContent = file_get_contents('php://input');
        $this->reqJsonArr = json_decode($this->reqContent, true);
    }

    /**
     * 样本文件上传(仅支持单文件上传，前端要做多文件上传，需要多次调用该接口)
     */
    public function upload()
    {
        //0.暂存目录创建
        $path = $this->uploadFileSavePath;
        if (!is_dir($path)) {
            $done = mkdir($path);
            if (!$done) {
                Log::write("文件目录生成失败:$path", 'info');
                throw new ValidateException("文件目录生成失败");
            }
        }
        //1.接收文件
        $file = request()->file('upload_file');
        //2.校验文件大小（<=256MB）、扩展名：.apk 、MIME类型： application/vnd.android
        $oldName = $_FILES['upload_file']['name'];
        $info = $file
            ->validate(['size' => $this->uploadFileMaxSize, 'ext' => $this->uploadFileAllowedExt, 'type' => $this->uploadFileAllowedMIME])
            ->rule(function () use ($oldName) {      //自定义生成形如：20210317-155656-12-原文件名的文件名
                $preTimeName = produceDateWithMicrosecond();
                return $preTimeName . '-' . $oldName;
            })
            ->move($this->uploadFileSavePath);
        if (!$info) {
            // 上传失败获取错误信息
            throw new ValidateException($file->getError());
        }

        $infoArr = $info->getInfo();
        $saveFileName = $info->getFilename();
        $ext = $info->getExtension();

        //3.上传文件到minio服务器
        try {
            //实例化minio的客户端
            $minioClient = MinioClient::getInstance();
            $minioConfig = config('minio');

            //判断桶是不是存在；不存在，就创建一个
            if (!$minioClient->doesBucketExist($minioConfig['bucket'])) {
                $minioClient->createBucket(['Bucket' => $minioConfig['bucket']]);
            }

            //上传
            $fullFileName = $path . $saveFileName;
            $minioClient->putObject([
                'Bucket' => $minioConfig['bucket'],
                'Key' => $minioConfig['prefix'] . $saveFileName,   //bucket作为桶的名字，是顶层的文件目录；剩余下级目录的表示，通过Key来实现
                'Body' => fopen($fullFileName, 'r'),
                'ACL' => $minioConfig['acl'],
            ]);
        } catch (S3Exception $e) {
            throw new ValidateException($e->getAwsErrorMessage());
        }
        //5.计算样本文件的md5值返回给前端
        $sampleApkMd5 = md5_file($fullFileName);

        //6.记录上传日志记录
        try {
            $insertData = [
                'sample_hash' => $sampleApkMd5,
                'user_id' => $this->getUserId(),
                'old_name' => $oldName,
                'save_name' => $saveFileName,
                'ext' => $ext,
                'size' => $infoArr['size'],   //单位是字节Byte
                'minio_bucket_name' => $minioConfig['bucket'],
                'minio_prefix' => $minioConfig['prefix'],
                'create_time' => date('Y-m-d H:i:s')
            ];
            Db::table('hl_upload_sample_file_log')->insert($insertData);
        } catch (Exception $e) {
            Log::write($e->getMessage(), 'info');
            throw new ValidateException('系统异常，请稍后再试');
        }

        $frontRes = ["sample_hash" => $sampleApkMd5, "size" => $infoArr['size']];

        return response(buildSuccessResponseJson($frontRes), 200, [], 'json');
    }

    //下载样本接口(从minio下载)
    public function download()
    {
        if (empty($this->reqJsonArr["sample_hash"])) {
            throw new ValidateException('sample_hash不能为空');
        }
        $sampleHash = $this->reqJsonArr["sample_hash"];

        //根据样本hash获取minio_path作为后续的key参数
        $key = Db::table('hl_sample_put_record')->where('sample_hash', $sampleHash)->value('minio_path');
        if (empty($key)) {
            Log::write($sampleHash . '对应的hl_sample_put_record记录的minio_path异常，为空！', 'sql');
            throw new ValidateException('数据异常，请反馈给管理员');
        }
        try {
            $minioClient = MinioClient::getInstance();
            $minioConfig = config('minio');
            // Get the object.
            $result = $minioClient->getObject([
                'Bucket' => $minioConfig['bucket'],
                'Key' => $key
            ]);

            // Display the object in the browser.
            header("Content-Type: {$result['ContentType']}");
            echo $result['Body'];
        } catch (S3Exception $e) {
            Log::write($e->getMessage(), 'notice');
            throw new ValidateException('数据异常，请反馈给管理员');
        }
    }

    //删除已上传，却未投放的样本（只删minio的文件，至于PHP特定目录的暂存文件，通过command命令行脚本实现定时删除一批旧数据）
    public function delSampleApk()
    {
        if (empty($this->reqJsonArr["sample_hash"])) {
            throw new ValidateException('sample_hash不能为空');
        }
        $sampleHash = $this->reqJsonArr["sample_hash"];

        try {
            //检查样本是否存在于已投放列表
            $isExist = Db::table('hl_sample_put_record')->where('sample_hash', $sampleHash)->count();
            if ($isExist) {
                throw new ValidateException('该样本已投放，不能删除！');
            }

            //查询上传日志表
            $uploadFileInfo = Db::table('hl_upload_sample_file_log')
                ->field('minio_bucket_name,minio_prefix,save_name')
                ->where('sample_hash', $sampleHash)
                ->order('id', 'desc')
                ->find();

            if (empty($uploadFileInfo)) {
                Log::write($sampleHash . '对应的hl_upload_sample_file_log记录的minio信息异常，为空！', 'sql');
                throw new ValidateException('数据异常，请反馈给管理员');
            }
        } catch (Exception $e) {
            Log::write($e->getMessage(), 'sql');
            throw new ValidateException('系统异常，请稍后再试');
        }

        //删除minio的文件
        try {
            $minioClient = MinioClient::getInstance();
            $minioClient->deleteObject([
                'Bucket' => $uploadFileInfo['minio_bucket_name'],
                'Key' => $uploadFileInfo['minio_prefix'] . $uploadFileInfo['save_name'],
            ]);
            return response(buildSuccessResponseJson([]), 200, [], 'json');
        } catch (S3Exception $e) {
            Log::write($e->getMessage(), 'notice');
            throw new ValidateException('系统异常，请稍后再试');
        }
    }
}