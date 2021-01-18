<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Tool\AliyunOss;

use Exception;
use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\OssClient;
use Xxtime\Flysystem\Aliyun\OssAdapter as XxtimeOssAdapter;
use Xxtime\Flysystem\Aliyun\Supports;

class OssAdapter extends XxtimeOssAdapter
{
    /**
     * @var OssClient
     */
    protected $oss;

    /**
     * @var string bucket
     */
    protected $bucket;

    /**
     * @var string
     */
    protected $endpoint = 'oss-cn-hangzhou.aliyuncs.com';

    /**
     * @var int 分片上传 - 分片大小 (默认5M)
     */
    protected $piecesPartSize = 5 * 1024 * 1024;

    /**
     * @var bool 分片上传 - 是否MD5校验
     */
    protected $piecesIsMD5 = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $isCName        = false;
        $token          = null;
        $this->supports = new Supports();
        try {
            $this->bucket                                                = $config['bucket'];
            empty($config['endpoint']) ? null : $this->endpoint          = $config['endpoint'];
            empty($config['timeout']) ? $config['timeout']               = 3600 : null;
            empty($config['connectTimeout']) ? $config['connectTimeout'] = 10 : null;

            if (! empty($config['isCName'])) {
                $isCName = true;
            }
            if (! empty($config['token'])) {
                $token = $config['token'];
            }
            $this->oss = new OssClient(
                $config['accessId'],
                $config['accessSecret'],
                $this->endpoint,
                $isCName,
                $token
            );
            $this->oss->setTimeout($config['timeout']);
            $this->oss->setConnectTimeout($config['connectTimeout']);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $path file path
     * @param int $timeout expiration time in seconds
     * @throws \OSS\Core\OssException
     */
    public function getSignedUrl(string $path, int $timeout = 60): string
    {
        try {
            $signedUrl = $this->oss->signUrl($this->bucket, $path, $timeout);
        } catch (OssException $e) {
            throw $e;
        }

        return $signedUrl;
    }

    /**
     * 分片上传.
     * @param string $localPath ...
     * @param string $ossPath ...
     * @throws OssException ...
     * @return bool ...
     */
    public function multipartUpload(string $localPath, string $ossPath): bool
    {
        try {
            // 1.初始化
            $uploadId = $this->oss->initiateMultipartUpload($this->bucket, $ossPath);

            // 2.分片
            $pieces         = $this->oss->generateMultiuploadParts(filesize($localPath), $this->piecesPartSize);
            $uploadPosition = 0;
            $uploadParts    = [];
            $partNum        = 0;
            foreach ($pieces as $i => $piece) {
                $fromPos = $uploadPosition + (int) $piece[$this->oss::OSS_SEEK_TO];
                $toPos   = (int) $piece[$this->oss::OSS_LENGTH] + $fromPos - 1;

                $upOptions = [
                    // 上传文件。
                    $this->oss::OSS_FILE_UPLOAD => $localPath,
                    // 设置分片号。
                    $this->oss::OSS_PART_NUM => ($i + 1),
                    // 指定分片上传起始位置。
                    $this->oss::OSS_SEEK_TO => $fromPos,
                    // 指定文件长度。
                    $this->oss::OSS_LENGTH => $toPos - $fromPos + 1,
                    // 是否开启MD5校验，true为开启。
                    $this->oss::OSS_CHECK_MD5 => $this->piecesIsMD5,
                ];

                // MD5检验
                if ($this->piecesIsMD5) {
                    $upOptions[$this->oss::OSS_CONTENT_MD5] = OssUtil::getMd5SumForFile($localPath, $fromPos, $toPos);
                }

                // 3.上传分片
                $uploadParts[] = [
                    'PartNumber' => ++$partNum,
                    'ETag'       => $this->oss->uploadPart($this->bucket, $ossPath, $uploadId, $upOptions),
                ];
            }

            // 合并分片
            $this->oss->completeMultipartUpload($this->bucket, $ossPath, $uploadId, $uploadParts);
            return true;
        } catch (OssException $e) {
            throw $e;
        }
    }
}
