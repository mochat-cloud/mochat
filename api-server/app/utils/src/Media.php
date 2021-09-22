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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use Psr\SimpleCache\CacheInterface;

class Media
{
    /**
     * @Inject
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    /**
     * @Inject
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @Inject
     * @var WeWorkFactory
     */
    protected $weWorkFactory;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * 上传临时图片媒体素材.
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     *
     * @return string
     */
    public function uploadImage($corpId, string $path): string
    {
        return $this->upload($corpId, 'image', $path);
    }

    /**
     * 上传临时语音媒体素材.
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     *
     * @return string
     */
    public function uploadVoice($corpId, string $path): string
    {
        return $this->upload($corpId, 'voice', $path);
    }

    /**
     * 上传临时视频媒体素材.
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     *
     * @return string
     */
    public function uploadVideo($corpId, string $path): string
    {
        return $this->upload($corpId, 'video', $path);
    }

    /**
     * 上传临时文件媒体素材.
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     * @param string $filename 文件名
     *
     * @return string
     */
    public function uploadFile($corpId, string $path, string $filename = ''): string
    {
        return $this->upload($corpId, 'file', $path, $filename);
    }

    /**
     * 上传临时媒体素材.
     *
     * @param int|string $corpId 企业id
     * @param string $type 上传类型
     * @param string $path 上传路径
     * @param string $filename 文件名
     *
     * @return string
     */
    public function upload($corpId, string $type, string $path, string $filename = ''): string
    {
        $mediaId = $this->cache->get($this->getCacheKey($corpId, $path, $filename));
        if (! empty($mediaId)) {
            return $mediaId;
        }

        try {
            $fileContent = $this->filesystem->read($path);
            $tempFile = tempnam(sys_get_temp_dir(), 'Media');
            file_put_contents($tempFile, $fileContent, FILE_USE_INCLUDE_PATH);

            $form = [];
            if (! empty($filename)) {
                $form['filename'] = $filename;
            }

            $wxMediaRes = $this->httpUpload($corpId, $type, $tempFile, $form);
            if ($wxMediaRes['errcode'] != 0) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, sprintf('请求数据：%s 响应结果：%s', $tempFile, json_encode($wxMediaRes)));
            }
            $this->cache->set($this->getCacheKey($corpId, $path, $filename), $wxMediaRes['media_id'], 60 * 60 * 24 * 3 - 300);
            return $wxMediaRes['media_id'];
        } catch (\Throwable $e) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '请求微信上传临时素材失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw $e;
        } finally {
            isset($tempFile) && file_exists($tempFile) && unlink($tempFile);
        }
    }

    protected function getCacheKey($corpId, string $path, string $filename = '')
    {
        return sprintf('mochat:mediaId:%s:%s', (string)$corpId, md5($path . $filename));
    }

    /**
     * 重写上传，增加大文件超时时长
     *
     * @param int|string $corpId
     * @param string $type
     * @param string $path
     * @param array $form
     *
     * @return array
     */
    private function httpUpload($corpId, string $type, string $path, array $form): array
    {
        $weWorkUserApp = $this->weWorkFactory->getUserApp($corpId);

        $multipart = [];
        $headers = [];

        if (isset($form['filename'])) {
            $headers = [
                'Content-Disposition' => 'form-data; name="media"; filename="' . $form['filename'] . '"'
            ];
        }

        $query = [
            'type' => $type
        ];

        $files = [
            'media' => $path,
        ];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
                'headers' => $headers
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $weWorkUserApp->media->request(
            'cgi-bin/media/upload',
            'POST',
            ['query' => $query, 'multipart' => $multipart, 'connect_timeout' => 180, 'timeout' => 180, 'read_timeout' => 180]
        );
    }
}
