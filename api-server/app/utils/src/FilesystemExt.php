<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Utils;

use League\Flysystem\Filesystem;

class FilesystemExt
{
    /**
     * @var string 文件驱动类型
     */
    protected $adapterName;

    /**
     * @var array 文件配置
     */
    protected $config;

    /**
     * @var array
     */
    protected $fileStorageConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->adapterName = config('file.default', '');
        $this->fileStorageConfig = config('file.storage');
        $this->config = $this->fileStorageConfig[$this->adapterName];
    }

    /**
     * 获取文件完整路径.
     * @param string $path 文件路径
     * @return string 完整路径
     */
    public function getFullUrl(string $path, string $fileDriver = ''): string
    {
        if (! $path) {
            return '';
        }

        if (strpos($path, 'http') !== false) {
            return $path;
        }

        if (! empty($fileDriver)) {
            $adapterName = $fileDriver;
        } else {
            $adapterName = $this->adapterName;
        }

        switch ($adapterName) {
            case 'local':
                $documentRoot = rtrim(config('server.settings.document_root', ''), '\\/');
                $uploadRoot = rtrim(realpath($this->config['root']), '/');
                $relativeDir = trim(str_replace($documentRoot, '', $uploadRoot), '/');
                $fullUrl = Url::getApiBaseUrl() . '/' . $relativeDir . '/' . $path;
                break;
            case 'oss':
                $fullUrl = $this->getOssUrl($path);
                break;
            case 'cos':
                $fullUrl = $this->getCosUrl($path);
                break;
            case 's3':
            case 'minio':
                $fullUrl = $this->getS3Url($path);
                break;
            case 'qiniu':
                $fullUrl = $this->getQiNiuUrl($path);
                break;
            default:
                $fullUrl = '';
        }

        return $fullUrl;
    }

    public function getAdapterName(): string
    {
        return $this->adapterName;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function getOssUrl(string $path)
    {
        $config = $this->fileStorageConfig['oss'];
        return sprintf('%s://%s.%s/%s', $config['scheme'], $config['bucket'], $config['endpoint'], $path);
    }

    protected function getCosUrl(string $path)
    {
        $config = $this->fileStorageConfig['cos'];
        return sprintf('%s://%s-%s.cos.%s.myqcloud.com/%s', $config['scheme'], $config['bucket'], $config['app_id'], $config['region'], $path);
    }

    protected function getS3Url(string $path)
    {
        $config = $this->fileStorageConfig['s3'];
        return sprintf('%s://%s.%s.amazonaws.com/%s', $config['scheme'], $config['bucket_name'], $config['region'], $path);
    }

    protected function getQiNiuUrl(string $path)
    {
        $config = $this->fileStorageConfig['qiniu'];
        return sprintf('%s/%s', $config['domain'], $path);
    }
}
