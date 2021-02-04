<?php


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
        $this->filesystem = $filesystem;
        $this->adapterName = config('file.default', '');
        $this->config = config('file.storage.' . $this->adapterName, []);
    }

    /**
     * 获取文件完整路径
     * @param string $path 文件路径
     * @return string 完整路径
     */
    public function getFullUrl(string $path): string
    {
        if (!$path || !$this->filesystem->has($path)) {
            return '';
        }

        switch ($this->adapterName) {
            case 'local':
                $fullUrl = config('framework.app_domain', '') . '/' . $path;
                break;
            case 'oss':
                $fullUrl = $this->getOssClient()->signUrl($this->config['bucket'], $path, 60 * 60 * 24);
                break;
            default:
                $fullUrl = '';
        }

        return $fullUrl;
    }

    /**
     * 获取OSS客户端
     * @return OssClient
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

    public function getAdapterName(): string
    {
        return $this->adapterName;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}