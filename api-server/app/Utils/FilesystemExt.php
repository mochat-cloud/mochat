<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Utils;

use League\Flysystem\Filesystem;
use OSS\OssClient;

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
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem  = $filesystem;
        $this->adapterName = config('file.default', '');
        $this->config      = config('file.storage.' . $this->adapterName, []);
    }

    /**
     * 获取文件完整路径.
     * @param string $path 文件路径
     * @return string 完整路径
     */
    public function getFullUrl(string $path): string
    {
        if (! $path || ! $this->filesystem->has($path)) {
            return '';
        }

        switch ($this->adapterName) {
            case 'local':
                $documentRoot = rtrim(config('server.settings.document_root', ''), '\\/');
                $uploadRoot   = rtrim(realpath($this->config['root']), '/');
                $relativeDir  = str_replace($documentRoot, '', $uploadRoot);
                $fullUrl      = config('framework.app_domain') . $relativeDir . '/' . $path;
                break;
            case 'oss':
                $fullUrl = $this->getOssClient()->signUrl($this->config['bucket'], $path, 60 * 60 * 24);
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

    /**
     * 获取OSS客户端.
     * @throws \OSS\Core\OssException ...
     */
    protected function getOssClient(): OssClient
    {
        return new OssClient(
            $this->config['accessId'],
            $this->config['accessSecret'],
            $this->config['endpoint']
        );
    }
}
